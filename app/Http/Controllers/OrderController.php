<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\SettingsController;
use App\Models\Order;
use App\Models\OrderItem; // Добавлен импорт
use App\Models\Product; // Добавлен импорт Product
use App\Models\Setting;
use App\Services\OrderNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    protected $settingsController;

    public function __construct(SettingsController $settingsController)
    {
        $this->settingsController = $settingsController;
    }
    public function index()
    {
        try {
            $orders = Order::with('items.product')->orderBy('created_at', 'desc')->get();

            // Add formatted totals
            $orders = $orders->map(function ($order) {
                $order->formatted_total = number_format($order->total, 2) . ' ₴';
                $order->formatted_subtotal = number_format($order->subtotal, 2) . ' ₴';
                $order->formatted_delivery_cost = number_format($order->delivery_cost, 2) . ' ₴';
                return $order;
            });

            return response()->json([
                'success' => true,
                'data' => $orders,
                'count' => $orders->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch orders: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function exportPdf(Order $order)
    {
        $pdf = Pdf::loadView('pdf.order', ['order' => $order])
            ->setPaper('a4', 'landscape'); // альбомний формат A4

        return $pdf->download("order-{$order->id}.pdf");
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        try {
            Log::info('Order creation request:', $request->all());

            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'delivery_method' => 'required|in:novaPoshta,ukrPoshta,selfPickup',
                'city' => 'nullable|string|max:255',
                'post_office' => 'nullable|string|max:255',
                'payment_method' => 'required|in:cashOnDelivery,cardOnline',
                'comment' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.name' => 'nullable|string|max:255',
            ]);

            $orderData = $validated;
            unset($orderData['items']);

            $order = new Order();
            $order->fill($orderData);

            // Calculate subtotal from items
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            $order->subtotal = $subtotal;
            // Використовуємо метод з SettingsController
            $order->delivery_cost = $this->settingsController->getDeliveryCostValue();
            $order->total = $order->subtotal + $order->delivery_cost;
            $order->status = 'pending';

            Log::info('Order calculations:', [
                'delivery_method' => $order->delivery_method,
                'subtotal' => $order->subtotal,
                'delivery_cost' => $order->delivery_cost,
                'total' => $order->total
            ]);

            $order->save();

            // Create order items
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $product->title,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            $order->load('items.product');

            $order->formatted_total = number_format($order->total, 2) . ' ₴';
            $order->formatted_subtotal = number_format($order->subtotal, 2) . ' ₴';
            $order->formatted_delivery_cost = number_format($order->delivery_cost, 2) . ' ₴';

            // Send order confirmation emails
            $emailResults = OrderNotificationService::sendOrderConfirmation($order);

            Log::info('Order created successfully:', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'delivery_method' => $order->delivery_method,
                'subtotal' => $order->subtotal,
                'delivery_cost' => $order->delivery_cost,
                'total' => $order->total,
                'email_results' => $emailResults
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'subtotal' => $order->formatted_subtotal,
                    'delivery_cost' => $order->formatted_delivery_cost,
                    'total' => $order->formatted_total,
                    'order' => $order
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order'
            ], 500);
        }
    }

//    private function getDeliveryCostValue()
//    {
//        $cost = Setting::getValue('delivery_cost', 250);
//        Log::info('Delivery cost from settings: ' . $cost);
//        return (float) $cost;
//    }
    /**
     * Display the specified order.
     */
    public function show($id)
    {
        try {
            $order = Order::with(['items' => function($query) {
                $query->with('product');
            }])->findOrFail($id);

            // Add formatted totals
            $order->formatted_total = number_format($order->total, 2) . ' ₴';
            $order->formatted_subtotal = number_format($order->subtotal, 2) . ' ₴';
            $order->formatted_delivery_cost = number_format($order->delivery_cost, 2) . ' ₴';

            // Transform items to include formatted data
            $order->items = $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'formatted_price' => number_format($item->price, 2) . ' ₴',
                    'total' => $item->price * $item->quantity,
                    'formatted_total' => number_format($item->price * $item->quantity, 2) . ' ₴',
                    'current_product' => $item->product ? [
                        'id' => $item->product->id,
                        'title' => $item->product->title,
                        'price' => $item->product->price,
                        'formatted_price' => number_format($item->product->price, 2) . ' ₴'
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch order: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified order status.
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled'
            ]);

            $order = Order::findOrFail($id);
            $order->updateStatus($validated['status']);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel the specified order.
     */
    public function cancel($id)
    {
        try {
            $order = Order::findOrFail($id);

            if (!$order->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be cancelled in current status'
                ], 400);
            }

            $order->updateStatus('cancelled');

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
