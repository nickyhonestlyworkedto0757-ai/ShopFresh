<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $order->invoice_number }}</title>
<style>
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 13px; color: #292524; margin: 40px; }
  .header { display: flex; justify-content: space-between; border-bottom: 3px solid #E4572E; padding-bottom: 16px; }
  h1 { margin: 0; color: #D64533; font-size: 26px; }
  table { width: 100%; border-collapse: collapse; margin-top: 24px; }
  th { text-align: left; background: #F5F5F4; padding: 8px; border-bottom: 2px solid #E7E5E4; }
  td { padding: 8px; border-bottom: 1px solid #E7E5E4; }
  .totals { margin-top: 16px; width: 40%; margin-left: auto; }
  .totals td { border: none; padding: 4px 8px; }
  .grand { font-size: 18px; font-weight: bold; border-top: 2px solid #292524 !important; }
  .muted { color: #78716c; font-size: 11px; }
  @media print { body { margin: 20px; } .no-print { display: none; } }
</style>
</head>
<body>
  @php $settings = \App\Support\Settings::all(); @endphp
  <div class="header">
    <div>
      <h1>{{ $settings['store_name'] }}</h1>
      <div class="muted">{{ $settings['store_address'] }}<br>{{ $settings['store_email'] }} · {{ $settings['store_phone'] }}</div>
    </div>
    <div style="text-align:right">
      <div style="font-size:18px;font-weight:bold;">INVOICE</div>
      <div class="muted">{{ $order->invoice_number }}<br>Order: {{ $order->order_number }}<br>{{ $order->created_at->format('d M Y, H:i') }}</div>
    </div>
  </div>

  <p style="margin-top:20px"><strong>Bill to</strong><br>
    {{ $order->customer_name }}<br>
    {{ $order->customer_phone }} · {{ $order->customer_email }}<br>
    {{ $order->shipping_address }}, {{ $order->city }}
  </p>

  <table>
    <thead><tr><th>Item</th><th>Qty</th><th style="text-align:right">Price</th><th style="text-align:right">Total</th></tr></thead>
    <tbody>
      @foreach($order->items as $item)
        <tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td style="text-align:right">{{ number_format($item->price, 2) }}</td><td style="text-align:right">{{ number_format($item->price * $item->quantity, 2) }}</td></tr>
      @endforeach
    </tbody>
  </table>

  <table class="totals">
    <tr><td>Subtotal</td><td style="text-align:right">{{ $settings['currency'] }} {{ number_format($order->subtotal, 2) }}</td></tr>
    <tr><td>Discount</td><td style="text-align:right">− {{ number_format($order->discount, 2) }}</td></tr>
    <tr><td>Delivery</td><td style="text-align:right">{{ number_format($order->delivery_fee, 2) }}</td></tr>
    <tr class="grand"><td>Total</td><td style="text-align:right">{{ $settings['currency'] }} {{ number_format($order->total, 2) }}</td></tr>
    <tr><td class="muted">Payment</td><td class="muted" style="text-align:right">{{ strtoupper($order->payment_method) }} — {{ strtoupper($order->payment_status) }}</td></tr>
  </table>

  <p class="muted" style="margin-top:32px">Thank you for shopping with {{ $settings['store_name'] }}.{{ $order->refund_amount > 0 ? ' Refunded: ' . number_format($order->refund_amount, 2) : '' }}</p>
  @unless($pdf ?? false)
    <button class="no-print" onclick="window.print()" style="margin-top:20px;background:#E4572E;color:#fff;border:0;border-radius:24px;padding:12px 28px;font-weight:bold;font-size:14px;cursor:pointer">Print invoice</button>
  @endunless
</body>
</html>
