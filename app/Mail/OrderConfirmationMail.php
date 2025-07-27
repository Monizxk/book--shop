<?php
namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public bool $isForSeller = false
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isForSeller
            ? "Новый заказ #{$this->order->id}"
            : "Подтверждение заказа #{$this->order->id}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_confirmation',
            with: [
                'order' => $this->order,
                'isForSeller' => $this->isForSeller,
            ]
        );
    }
}
