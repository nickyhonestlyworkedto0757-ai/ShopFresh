<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Payments Report</title>
<style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:11px;color:#292524;margin:32px}h1{color:#D64533;font-size:18px;margin-bottom:2px}table{width:100%;border-collapse:collapse;margin-top:16px}th{background:#F5F5F4;text-align:left;padding:6px;border-bottom:2px solid #E7E5E4;font-size:10px}td{padding:6px;border-bottom:1px solid #E7E5E4;font-size:10px}.muted{color:#78716c;font-size:9px}.summary{margin-top:14px;font-size:11px}</style>
</head>
<body>
@php $s = \App\Support\Settings::all(); @endphp
<h1>{{ $s['store_name'] }} — Payments Report</h1>
<div class="muted">Generated {{ now()->format('d M Y, H:i') }} · {{ $summary['count'] }} transactions · Paid {{ $s['currency'] }} {{ number_format($summary['paid'], 2) }} · Pending {{ $s['currency'] }} {{ number_format($summary['pending'], 2) }} · Failed {{ $summary['failed'] }}</div>
<table>
  <thead><tr><th>Date</th><th>Order</th><th>Customer</th><th>Method</th><th>Amount</th><th>Receipt</th><th>Status</th></tr></thead>
  <tbody>
    @foreach($payments as $p)
      <tr><td>{{ $p->created_at->format('Y-m-d H:i') }}</td><td>{{ $p->order?->order_number ?? $p->order_id }}</td><td>{{ $p->order?->customer_name }}</td><td>{{ strtoupper($p->method) }}</td><td>{{ number_format($p->amount, 2) }}</td><td>{{ $p->receipt_number ?? '—' }}</td><td>{{ strtoupper($p->status) }}</td></tr>
    @endforeach
  </tbody>
</table>
</body>
</html>
