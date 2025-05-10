<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = ['title', 'images', 'price', 'in_stock', 'category_id'];

    protected $casts = [
        'images' => 'array',
        'in_stock' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Обробка отриманих зображень для правильного формування URL
     */
    public function getImagesAttribute($value)
    {
        $images = json_decode($value, true);

        return array_map(function ($image) {
            // Перевірка, чи вже є 'products/' на початку шляху, щоб уникнути дублювання
            $path = $image;
            return Storage::disk('s3')->url($path);
        }, $images);
    }


    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Отримання URL першого зображення для продукту (якщо воно є)
     */
    public function getImageUrlAttribute(): ?string
    {
        // Перевірка на наявність хоча б одного зображення
        return isset($this->images[0]) ? Storage::disk('s3')->url('products/' . $this->images[0]) : null;
    }
}
