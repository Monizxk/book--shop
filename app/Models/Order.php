<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
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

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model and generate order number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = $order->generateOrderNumber();
            }
        });
    }

    /**
     * Generate a unique order number
     */
    public function generateOrderNumber()
    {
        do {
            $orderNumber = 'ORD-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Get the order items as a collection
     */
    public function getOrderItemsAttribute()
    {
        return collect($this->items);
    }

    /**
     * Calculate subtotal from items
     */
    public function calculateSubtotal()
    {
        return collect($this->items)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
    }

    /**
     * Calculate delivery cost based on delivery method
     */
    public function calculateDeliveryCost()
    {
        switch ($this->delivery_method) {
            case 'novaPoshta':
                return 50.00;
            case 'ukrPoshta':
                return 30.00;
            case 'selfPickup':
                return 0.00;
            default:
                return 0.00;
        }
    }

    /**
     * Calculate total (subtotal + delivery)
     */
    public function calculateTotal()
    {
        return $this->calculateSubtotal() + $this->calculateDeliveryCost();
    }

    /**
     * Scope for filtering orders by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for recent orders
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get formatted order total
     */
    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 2) . ' ₴';
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Update order status
     */
    public function updateStatus($status)
    {
        $this->update(['status' => $status]);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

}
