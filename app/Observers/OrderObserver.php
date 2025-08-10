<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\OrderNotificationService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    private static array $originalStatuses = [];

    /**
     * Handle the Order "updating" event.
     * This fires before the model is saved, so we can capture the old status.
     */
    public function updating(Order $order): void
    {
        if ($order->isDirty('status')) {
            $originalStatus = $order->getOriginal('status');

            self::$originalStatuses[$order->id] = $originalStatus;

            Log::info('Order status is being changed', [
                'order_id' => $order->id,
                'old_status' => $originalStatus,
                'new_status' => $order->status
            ]);
        }
    }

    /**
     * Handle the Order "updated" event.
     * This fires after the model is saved.
     */
    public function updated(Order $order): void
    {
        if (isset(self::$originalStatuses[$order->id])) {
            $originalStatus = self::$originalStatuses[$order->id];

            if ($originalStatus !== $order->status) {
                Log::info('Order status changed, sending notifications', [
                    'order_id' => $order->id,
                    'old_status' => $originalStatus,
                    'new_status' => $order->status
                ]);

                $order->load('items');

                $results = OrderNotificationService::sendStatusUpdate(
                    $order,
                    $originalStatus,
                    $order->status
                );

                Log::info('Status update notifications sent', [
                    'order_id' => $order->id,
                    'results' => $results
                ]);
            }

            unset(self::$originalStatuses[$order->id]);
        }
    }
}
