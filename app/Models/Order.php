<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'address',
        'status',
        'payment_method',
        'delivery_method',
        'total_amount',
        'tracking_number',
        'notes',
    ];

    protected $casts = [
        'status_history' => 'array',
    ];
    
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

}