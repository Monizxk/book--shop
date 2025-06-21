<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Setting;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'delivery_method',
        'city',
        'post_office',
        'payment_method',
        'comment',
        'subtotal',
        'delivery_cost',
        'total',
        'status',
        'order_number'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = $order->generateOrderNumber();
            }
        });
    }

    public function generateOrderNumber()
    {
        do {
            $orderNumber = 'ORD-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Calculate subtotal from relationship items
     */
    public function calculateSubtotal()
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    /**
     * Calculate delivery cost based on delivery method
     */
    public function calculateDeliveryCost()
    {
        $baseDeliveryCost = (float) Setting::getValue('delivery_cost', 250);

        switch ($this->delivery_method) {
            case 'novaPoshta':
                return $baseDeliveryCost;
            case 'ukrPoshta':
                return $baseDeliveryCost * 0.6;
            case 'selfPickup':
                return 0.00;
            default:
                return $baseDeliveryCost;
        }
    }

    public function calculateTotal()
    {
        return $this->calculateSubtotal() + $this->calculateDeliveryCost();
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 2) . ' ₴';
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
