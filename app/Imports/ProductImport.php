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

        // Очистка всех ключей от лишних пробелов и нормализация
        $cleanedRow = [];
        foreach ($row as $key => $value) {
            $cleanedKey = trim($key);
            $cleanedRow[$cleanedKey] = $value;
        }

        // Явно удаляем id из данных
        unset($cleanedRow['id']);

        $title = $this->getValue($cleanedRow, ['назва', 'nazva', 'title', 'name', 'product_name']);
        $price = $this->getValue($cleanedRow, ['ціна', 'cina', 'price', 'cost', 'amount']);

        if (empty($title)) {
            return null;
        }

        $parsedPrice = $this->parsePrice($price ?? 0);

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

        try {
            // Используем create вместо new + save для избежания проблем с ID
            $product = Product::create($productData);
            return $product;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getValue(array $row, array $possibleKeys)
    {

        foreach ($possibleKeys as $key) {
            // Проверяем точное совпадение ключа
            if (isset($row[$key]) && $this->isValidValue($row[$key])) {
                $value = trim($row[$key]);
                return $value;
            }

            // Проверяем совпадение с нижним регистром
            $lowerKey = mb_strtolower($key, 'UTF-8');
            if (isset($row[$lowerKey]) && $this->isValidValue($row[$lowerKey])) {
                $value = trim($row[$lowerKey]);
                return $value;
            }

            // Проверяем среди всех ключей строки (для случаев с разной кодировкой)
            foreach ($row as $rowKey => $rowValue) {
                if (mb_strtolower($rowKey, 'UTF-8') === $lowerKey && $this->isValidValue($rowValue)) {
                    $value = trim($rowValue);
                    return $value;
                }
            }
        }

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

        if (is_null($priceValue) || $priceValue === '') {
            return 0.0;
        }

        // Если уже число
        if (is_numeric($priceValue)) {
            $price = floatval($priceValue);
            return $price;
        }

        // Если строка, очищаем от лишних символов
        $price = preg_replace('/[^\d.,]/', '', $priceValue);
        $price = str_replace(',', '.', $price);
        $parsedPrice = floatval($price);

        return $parsedPrice;
    }

    private function determineCategoryId($title)
    {

        if ($this->defaultCategoryId) {
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
                return $category->id;
            }
        }

        $defaultCategory = Category::firstOrCreate(['name' => 'Загальне']);
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
