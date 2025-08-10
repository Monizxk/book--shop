<?php
namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdateMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus,
        public bool $isForSeller = false
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isForSeller
            ? "Статус замовлення #{$this->order->order_number} змінено"
            : "Оновлення статусу замовлення #{$this->order->order_number}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_status_update',
            with: [
                'order' => $this->order,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'isForSeller' => $this->isForSeller,
            ]
        );
    }

    public static function getStatusDisplayName(string $status): string
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
}
