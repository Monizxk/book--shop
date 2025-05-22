<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = ['title', 'images', 'price', 'is_on_sale', 'in_stock', 'category_id'];

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
//    public function getImagesAttribute($value)
//    {
//        $images = json_decode($value, true);
//
//        return array_map(function ($image) {
//            $path = $image;
//            return Storage::disk('public')->url($path);
//        }, $images);
//    }


    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Отримання URL першого зображення для продукту (якщо воно є)
     */
    public function getImageUrlAttribute(): ?string
    {
        return isset($this->images[0])
            ? (Str::startsWith($this->images[0], 'http')
                ? $this->images[0]
                : Storage::disk('public')->url($this->images[0]))
            : null;
    }
}
