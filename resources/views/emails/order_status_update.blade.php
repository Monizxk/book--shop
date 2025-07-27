<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Оновлення статусу замовлення #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, 'DejaVu Sans', sans-serif;
            font-size: 15px;
            color: #222;
            background: #f8f8f8;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 32px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 32px 24px;
        }
        h2 {
            color: #088178;
            margin-bottom: 16px;
        }
        .status-update {
            background: #f0f9ff;
            border-left: 4px solid #088178;
            padding: 16px;
            margin: 24px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            margin: 0 4px;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-processing { background: #d4edda; color: #155724; }
        .status-shipped { background: #cce5ff; color: #004085; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        .section-title {
            font-weight: bold;
            margin-top: 24px;
            margin-bottom: 8px;
            color: #333;
        }
        .order-table, .order-table th, .order-table td {
            border: 1px solid #e0e0e0;
            border-collapse: collapse;
        }
        .order-table {
            width: 100%;
            margin-bottom: 16px;
        }
        .order-table th, .order-table td {
            padding: 8px 6px;
            text-align: left;
        }
        .order-summary {
            margin-top: 16px;
            font-size: 16px;
        }
        .footer {
            margin-top: 32px;
            color: #888;
            font-size: 13px;
            text-align: center;
        }
        .status-info {
            margin: 16px 0;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .status-description {
            margin-top: 8px;
            color: #555;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="email-container">
    @if($isForSeller)
        <h2>Статус замовлення #{{ $order->order_number }} змінено</h2>
        <p>Статус замовлення від клієнта <strong>{{ $order->full_name }}</strong> було оновлено.</p>
    @else
        <h2>Оновлення статусу вашого замовлення</h2>
        <p>Вітаємо, <strong>{{ $order->full_name }}</strong>!</p>
        <p>Статус вашого замовлення <strong>#{{ $order->order_number }}</strong> було оновлено.</p>
    @endif

    <div class="status-update">
        <div style="font-size: 18px; margin-bottom: 12px;">
            <span class="status-badge status-{{ $oldStatus }}">
                {{ App\Mail\OrderStatusUpdateMail::getStatusDisplayName($oldStatus) }}
            </span>
            →
            <span class="status-badge status-{{ $newStatus }}">
                {{ App\Mail\OrderStatusUpdateMail::getStatusDisplayName($newStatus) }}
            </span>
        </div>
    </div>

    <div class="status-info">
        <strong>Поточний статус:</strong>
        <span class="status-badge status-{{ $newStatus }}">
            {{ App\Mail\OrderStatusUpdateMail::getStatusDisplayName($newStatus) }}
        </span>

        <div class="status-description">
            @switch($newStatus)
                @case('pending')
                    Ваше замовлення прийнято і очікує підтвердження нашими менеджерами.
                    @break
                @case('confirmed')
                    Замовлення підтверджено! Ми почали його обробку.
                    @break
                @case('processing')
                    Ваше замовлення зараз обробляється і готується до відправки.
                    @break
                @case('shipped')
                    Замовлення відправлено! Найближчим часом ви отримаєте трек-номер для відстеження.
                    @break
                @case('delivered')
                    Ваше замовлення успішно доставлено. Дякуємо за покупку!
                    @break
                @case('cancelled')
                    Замовлення було скасовано. Якщо у вас є питання, зв'яжіться з нами.
                    @break
                @default
                    Статус замовлення оновлено.
            @endswitch
        </div>
    </div>

    <div class="section-title">Деталі замовлення</div>
    <table>
        <tr>
            <td><strong>Номер замовлення:</strong></td>
            <td>#{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td><strong>Дата створення:</strong></td>
            <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Загальна сума:</strong></td>
            <td>{{ number_format($order->total, 2) }} грн</td>
        </tr>
    </table>

    @if(!$isForSeller)
        <div class="section-title">Склад замовлення</div>
        <table class="order-table">
            <thead>
            <tr>
                <th>Назва</th>
                <th>Кільк.</th>
                <th>Ціна</th>
                <th>Сума</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price, 2) }} грн</td>
                    <td>{{ number_format($item->quantity * $item->price, 2) }} грн</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="order-summary">
            <strong>Разом:</strong> {{ number_format($order->subtotal, 2) }} грн
            <br><strong>Доставка:</strong> {{ number_format($order->delivery_cost, 2) }} грн
            <br><strong>Загальна сума:</strong> {{ number_format($order->total, 2) }} грн
        </div>
    @endif

    <div class="footer">
        @if($isForSeller)
            Це автоматичне повідомлення про зміну статусу замовлення.<br>
            Перевірте деталі в адмін-панелі.
        @else
            Якщо у вас є питання щодо замовлення — просто відповідайте на цей лист.<br>
            Дякуємо, що обрали наш магазин!
        @endif
    </div>
</div>
</body>
</html>
