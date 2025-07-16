<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\Importable;

class ProductImport implements ToModel, WithHeadingRow, WithChunkReading, SkipsEmptyRows
{
    use Importable;

    private $defaultCategoryId;

    public function __construct($defaultCategoryId = null)
    {
        $this->defaultCategoryId = $defaultCategoryId;
    }

    public function model(array $row)
    {
        \Log::info('Processing row: ' . json_encode($row));
        \Log::info('Raw row keys: ' . json_encode(array_keys($row)));

        // Очистка всех ключей от лишних пробелов и нормализация
        $cleanedRow = [];
        foreach ($row as $key => $value) {
            $cleanedKey = trim($key);
            $cleanedRow[$cleanedKey] = $value;
        }

        \Log::info('Cleaned row keys: ' . json_encode(array_keys($cleanedRow)));
        \Log::info('Cleaned row data: ' . json_encode($cleanedRow));

        // Явно удаляем id из данных
        unset($cleanedRow['id']);

        $title = $this->getValue($cleanedRow, ['назва', 'nazva', 'title', 'name', 'product_name']);
        $price = $this->getValue($cleanedRow, ['ціна', 'cina', 'price', 'cost', 'amount']);

        \Log::info('Extracted title: ' . ($title ?? 'null'));
        \Log::info('Extracted price before parsing: ' . ($price ?? 'null'));

        if (empty($title)) {
            \Log::error('Empty title for row: ' . json_encode($row));
            return null;
        }

        $parsedPrice = $this->parsePrice($price ?? 0);
        \Log::info('Parsed price: ' . $parsedPrice);

        $categoryId = $this->determineCategoryId($title);

        $productData = [
            'title' => $title,
            'price' => $parsedPrice,
            'category_id' => $categoryId,
            'in_stock' => true,
            'hidden' => false,
            'is_on_sale' => false,
            'is_on_way' => false,
        ];

        \Log::info('Creating product: ' . json_encode($productData));

        try {
            // Используем create вместо new + save для избежания проблем с ID
            $product = Product::create($productData);
            \Log::info('Product created: ' . $product->id);
            return $product;
        } catch (\Exception $e) {
            \Log::error('Failed to create product: ' . $e->getMessage());
            \Log::error('Product data: ' . json_encode($productData));
            return null;
        }
    }

    private function getValue(array $row, array $possibleKeys)
    {
        \Log::info('Available row keys: ' . json_encode(array_keys($row)));

        foreach ($possibleKeys as $key) {
            // Проверяем точное совпадение ключа
            if (isset($row[$key]) && $this->isValidValue($row[$key])) {
                $value = trim($row[$key]);
                \Log::info('Found valid value for exact key ' . $key . ': ' . $value);
                return $value;
            }

            // Проверяем совпадение с нижним регистром
            $lowerKey = mb_strtolower($key, 'UTF-8');
            if (isset($row[$lowerKey]) && $this->isValidValue($row[$lowerKey])) {
                $value = trim($row[$lowerKey]);
                \Log::info('Found valid value for lowercase key ' . $lowerKey . ': ' . $value);
                return $value;
            }

            // Проверяем среди всех ключей строки (для случаев с разной кодировкой)
            foreach ($row as $rowKey => $rowValue) {
                if (mb_strtolower($rowKey, 'UTF-8') === $lowerKey && $this->isValidValue($rowValue)) {
                    $value = trim($rowValue);
                    \Log::info('Found valid value for matched key ' . $rowKey . ': ' . $value);
                    return $value;
                }
            }
        }

        \Log::warning('No valid value found for keys: ' . json_encode($possibleKeys));
        return null;
    }

    private function isValidValue($value)
    {
        return !is_null($value) &&
            (is_string($value) ? strlen(trim($value)) > 0 : true) &&
            (is_numeric($value) ? $value >= 0 : true);
    }

    private function parsePrice($priceValue)
    {
        \Log::info('Raw price value: ' . json_encode($priceValue));

        if (is_null($priceValue) || $priceValue === '') {
            \Log::warning('Price value is null or empty, defaulting to 0');
            return 0.0;
        }

        // Если уже число
        if (is_numeric($priceValue)) {
            $price = floatval($priceValue);
            \Log::info('Numeric price parsed: ' . $price);
            return $price;
        }

        // Если строка, очищаем от лишних символов
        $price = preg_replace('/[^\d.,]/', '', $priceValue);
        $price = str_replace(',', '.', $price);
        $parsedPrice = floatval($price);

        \Log::info('String price parsed: ' . $parsedPrice);
        return $parsedPrice;
    }

    private function determineCategoryId($title)
    {
        \Log::info('Determining category for title: ' . $title);

        if ($this->defaultCategoryId) {
            \Log::info('Using default category ID: ' . $this->defaultCategoryId);
            return $this->defaultCategoryId;
        }

        $title = mb_strtolower($title, 'UTF-8');
        $categoryMappings = [
            'academy' => 'Academy Stars',
            'beehive' => 'Academy Stars',
            'english' => 'English',
            'математика' => 'Математика',
            'ukrainian' => 'Українська мова',
            'історія' => 'Історія',
        ];

        foreach ($categoryMappings as $keyword => $categoryName) {
            if (str_contains($title, $keyword)) {
                $category = Category::firstOrCreate(['name' => $categoryName]);
                \Log::info('Category found/created: ' . $categoryName . ' (ID: ' . $category->id . ')');
                return $category->id;
            }
        }

        $defaultCategory = Category::firstOrCreate(['name' => 'Загальне']);
        \Log::info('Using default category: Загальне (ID: ' . $defaultCategory->id . ')');
        return $defaultCategory->id;
    }

    public function chunkSize(): int
    {
        return 10;
    }

    public function headingRow(): int
    {
        return 1;
    }
}
