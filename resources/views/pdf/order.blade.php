<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order #{{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1;
        }

        .container {
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            border: 1px solid #aaa;
            padding: 2px;
            vertical-align: top;
        }

        .section-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .text {
            font-size: 10px;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }
        }
    </style>
</head>
<body>
<table>
    <tr>
        <td width="33%">
            <div class="section-title">Замовлення #{{ $order->order_number }}</div>
            <div class="text">
                Статус: {{ $order->status }}<br>
                Дата: {{ $order->created_at->format('d.m.Y H:i') }}
            </div>
        </td>
        <td width="33%">
            <div class="section-title">Інформація про клієнта</div>
            <div class="text">
                {{ $order->full_name }}<br>
                {{ $order->email }}<br>
                {{ $order->phone }}
            </div>
        </td>
        <td width="34%">
            <div class="section-title">Адреса доставки</div>
            <div class="text">
                Місто: {{ $order->city ?? 'Не вказано' }}<br>
                Відділення НП: {{ $order->post_office ?? 'Не вказано' }}
            </div>
        </td>
    </tr>
</table>

<br>

<table>
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

<br>

<table>
    <tr>
        <td><strong>Разом:</strong> {{ number_format($order->subtotal, 2) }} грн</td>
        <td><strong>Доставка:</strong> {{ number_format($order->delivery_cost, 2) }} грн</td>
        <td><strong>Загальна сума:</strong> {{ number_format($order->total, 2) }} грн</td>
    </tr>
</table>
</body>
</html>
