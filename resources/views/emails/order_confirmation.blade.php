<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Підтвердження замовлення #{{ $order->order_number }}</title>
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
    </style>
</head>
<body>
<div class="email-container">
    @if($isForSeller)
        <h2>Нове замовлення #{{ $order->order_number }}</h2>
        <p>Отримано нове замовлення від клієнта. Деталі замовлення:</p>
    @else
    <h2>Дякуємо за покупку!</h2>
    <p>Ваше замовлення <strong>#{{ $order->order_number }}</strong> прийнято. Нижче — деталі вашого замовлення:</p>
    @endif

    <div class="section-title">Інформація про замовлення</div>
    <table>
        <tr>
            <td><strong>Статус:</strong></td>
            <td>{{ $order->status }}</td>
        </tr>
        <tr>
            <td><strong>Дата:</strong></td>
            <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
        </tr>
    </table>

    <div class="section-title">Дані покупця</div>
    <table>
        <tr>
            <td><strong>Ім'я:</strong></td>
            <td>{{ $order->full_name }}</td>
        </tr>
        <tr>
            <td><strong>Email:</strong></td>
            <td>{{ $order->email }}</td>
        </tr>
        <tr>
            <td><strong>Телефон:</strong></td>
            <td>{{ $order->phone }}</td>
        </tr>
    </table>

    <div class="section-title">Адреса доставки</div>
    <table>
        <tr>
            <td><strong>Місто:</strong></td>
            <td>{{ $order->city ?? 'Не вказано' }}</td>
        </tr>
        <tr>
            <td><strong>Відділення НП:</strong></td>
            <td>{{ $order->post_office ?? 'Не вказано' }}</td>
        </tr>
    </table>

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

    <div class="footer">
        @if($isForSeller)
            Це автоматичне повідомлення про нове замовлення.<br>
            Перевірте деталі замовлення в адмін-панелі.
        @else
        Якщо у вас є питання — просто відповідайте на цей лист.<br>
        Дякуємо, що обрали наш магазин!
        @endif
    </div>
</div>
</body>
</html>