<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ImportProducts implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    use Importable;

    private $defaultCategoryId;

    public function __construct($defaultCategoryId = null)
    {
        $this->defaultCategoryId = $defaultCategoryId;
    }

    public function model(array $row)
    {
        $title = trim($row['назва'] ?? $row['title'] ?? '');

        $price = $this->parsePrice($row['ціна'] ?? $row['price'] ?? 0);

        $categoryId = $this->determineCategoryId($title);

        return new Product([
            'title' => $title,
            'price' => $price,
            'category_id' => $categoryId,
            'in_stock' => true,
            'hidden' => false,
            'is_on_sale' => false,
            'is_on_way' => false,
        ]);
    }

    private function parsePrice($priceValue)
    {
        $price = preg_replace('/[^\d.,]/', '', $priceValue);

        $price = str_replace(',', '.', $price);

        return floatval($price);
    }

    private function determineCategoryId($title)
    {
        if ($this->defaultCategoryId) {
            return $this->defaultCategoryId;
        }

        $title = strtolower($title);

        $categoryMappings = [
            'academy' => 'Academy Stars',
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

    public function rules(): array
    {
        return [
            'назва' => 'required|string|max:255',
            'ціна' => 'required|numeric|min:0',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'назва.required' => 'Назва продукту є обов\'язковою',
            'ціна.required' => 'Ціна є обов\'язковою',
            'ціна.numeric' => 'Ціна повинна бути числом',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
