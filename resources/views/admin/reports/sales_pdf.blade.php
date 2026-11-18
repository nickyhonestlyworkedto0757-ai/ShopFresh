<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Sales Report {{ $from }} to {{ $to }}</title>
<style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:12px;color:#292524;margin:36px}h1{color:#D64533;font-size:20px;margin-bottom:2px}table{width:100%;border-collapse:collapse;margin-top:18px}th{background:#F5F5F4;text-align:left;padding:8px;border-bottom:2px solid #E7E5E4}td{padding:8px;border-bottom:1px solid #E7E5E4}.muted{color:#78716c;font-size:10px}.grand td{font-weight:bold;border-top:2px solid #292524}</style>
</head>
<body>
@php $s = \App\Support\Settings::all(); @endphp
<h1>{{ $s['store_name'] }} — Sales Report</h1>
<div class="muted">{{ $from }} to {{ $to }} · Generated {{ now()->format('d M Y, H:i') }}</div>
<table>
  <thead><tr><th>Date</th><th>Orders</th><th style="text-align:right">Revenue ({{ $s['currency'] }})</th></tr></thead>
  <tbody>
    @foreach($rows as $row)
      <tr><td>{{ $row->day }}</td><td>{{ $row->orders }}</td><td style="text-align:right">{{ number_format($row->total, 2) }}</td></tr>
    @endforeach
    <tr class="grand"><td>TOTAL</td><td>{{ $totals['orders'] }}</td><td style="text-align:right">{{ number_format($totals['revenue'], 2) }}</td></tr>
  </tbody>
</table>
</body>
</html>
