<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class OrderNotificationService
{
    /**
     * Send order confirmation emails to customer and seller
     */
    public static function sendOrderConfirmation(Order $order): array
    {
        $results = [
            'customer_email_sent' => false,
            'seller_email_sent' => false,
            'errors' => []
        ];

        try {
            $results['customer_email_sent'] = self::sendCustomerEmail($order);

            if (self::areSellerNotificationsEnabled()) {
                $results['seller_email_sent'] = self::sendSellerEmail($order);
            }

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error('Order confirmation error: ' . $e->getMessage());
        }

        return $results;
    }

    /**
     * Send order status update emails to customer and seller
     */
    public static function sendStatusUpdate(Order $order, string $oldStatus, string $newStatus): array
    {
        $results = [
            'customer_email_sent' => false,
            'seller_email_sent' => false,
            'errors' => []
        ];

        try {
            $results['customer_email_sent'] = self::sendCustomerStatusEmail($order, $oldStatus, $newStatus);

            if (self::areSellerNotificationsEnabled()) {
                $results['seller_email_sent'] = self::sendSellerStatusEmail($order, $oldStatus, $newStatus);
            }

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error('Status update error: ' . $e->getMessage());
        }

        return $results;
    }

    /**
     * Send confirmation email to customer using direct PHP mail()
     */
    private static function sendCustomerEmail(Order $order): bool
    {
        try {
            $subject = "Підтвердження замовлення #{$order->order_number}";
            $message = self::generateOrderConfirmationMessage($order, false);
            $headers = self::getEmailHeaders();

            $result = mail($order->email, $subject, $message, $headers);

            Log::info('Customer email sent', [
                'order_id' => $order->id,
                'email' => $order->email,
                'result' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to send customer email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification email to seller using direct PHP mail()
     */
    private static function sendSellerEmail(Order $order): bool
    {
        try {
            $sellerEmail = self::getSellerEmail();

            if (empty($sellerEmail)) {
                return false;
            }

            $subject = "Нове замовлення #{$order->order_number}";
            $message = self::generateOrderConfirmationMessage($order, true);
            $headers = self::getEmailHeaders();

            $result = mail($sellerEmail, $subject, $message, $headers);

            Log::info('Seller email sent', [
                'order_id' => $order->id,
                'email' => $sellerEmail,
                'result' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to send seller email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send status update email to customer
     */
    private static function sendCustomerStatusEmail(Order $order, string $oldStatus, string $newStatus): bool
    {
        try {
            $subject = "Оновлення статусу замовлення #{$order->order_number}";
            $message = self::generateStatusUpdateMessage($order, $oldStatus, $newStatus, false);
            $headers = self::getEmailHeaders();

            $result = mail($order->email, $subject, $message, $headers);

            Log::info('Customer status email sent', [
                'order_id' => $order->id,
                'email' => $order->email,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'result' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to send customer status email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send status update email to seller
     */
    private static function sendSellerStatusEmail(Order $order, string $oldStatus, string $newStatus): bool
    {
        try {
            $sellerEmail = self::getSellerEmail();

            if (empty($sellerEmail)) {
                return false;
            }

            $subject = "Статус замовлення #{$order->order_number} змінено";
            $message = self::generateStatusUpdateMessage($order, $oldStatus, $newStatus, true);
            $headers = self::getEmailHeaders();

            $result = mail($sellerEmail, $subject, $message, $headers);

            Log::info('Seller status email sent', [
                'order_id' => $order->id,
                'email' => $sellerEmail,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'result' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to send seller status email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate email headers for better deliverability
     */
    private static function getEmailHeaders(): string
    {
        $fromEmail = 'desperateope2017@gmail.com';
        $fromName = 'BookPrint';

        $headers = "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "Return-Path: {$fromEmail}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";

        return $headers;
    }

    /**
     * Generate order confirmation message using Blade template
     */
    private static function generateOrderConfirmationMessage(Order $order, bool $isForSeller): string
    {
        $order->load('items.product');

        return view('emails.order_confirmation', [
            'order' => $order,
            'isForSeller' => $isForSeller,
        ])->render();
    }

    /**
     * Generate status update message using Blade template
     */
    private static function generateStatusUpdateMessage(Order $order, string $oldStatus, string $newStatus, bool $isForSeller): string
    {
        return view('emails.order_status_update', [
            'order' => $order,
            'oldStatus' => $oldStatus,
            'newStatus' => $newStatus,
            'isForSeller' => $isForSeller,
        ])->render();
    }

    /**
     * Get status display name
     */
    private static function getStatusDisplayName(string $status): string
    {
        return match($status) {
            'pending' => 'Очікує підтвердження',
            'confirmed' => 'Підтверджено',
            'processing' => 'В обробці',
            'shipped' => 'Відправлено',
            'delivered' => 'Доставлено',
            'cancelled' => 'Скасовано',
            default => ucfirst($status)
        };
    }

    /**
     * Get delivery method name
     */
    private static function getDeliveryMethodName(string $method): string
    {
        return match($method) {
            'novaPoshta' => 'Нова Пошта',
            'ukrPoshta' => 'Укрпошта',
            'pickup' => 'Самовивіз',
            default => $method
        };
    }

    /**
     * Get payment method name
     */
    private static function getPaymentMethodName(string $method): string
    {
        return match($method) {
            'cardOnline' => 'Оплата картою онлайн',
            'cash' => 'Готівкою при отриманні',
            'bankTransfer' => 'Банківський переказ',
            default => $method
        };
    }

    /**
     * Get seller email from settings
     */
    public static function getSellerEmail(): ?string
    {
        return Setting::getValue('order_notification_email');
    }

    /**
     * Check if seller notifications are enabled
     */
    public static function areSellerNotificationsEnabled(): bool
    {
        return Setting::getValue('order_notification_enabled', '1') === '1';
    }

    /**
     * Set seller email in settings
     */
    public static function setSellerEmail(string $email): void
    {
        Setting::setValue('order_notification_email', $email);
    }

    /**
     * Enable or disable seller notifications
     */
    public static function setSellerNotificationsEnabled(bool $enabled): void
    {
        Setting::setValue('order_notification_enabled', $enabled ? '1' : '0');
    }
}
