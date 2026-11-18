@extends('admin.layout')
@section('title', 'Payment #' . $payment->id . ' — Admin')
@section('content')
<a href="{{ route('admin.payments.index') }}" class="inline-flex items-center gap-1 text-sm text-brand-600 font-semibold"><i data-lucide="arrow-left" class="w-4 h-4"></i>Payments</a>
<div class="grid lg:grid-cols-2 gap-4 mt-3">
  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6">
    <div class="flex items-center justify-between">
      <h1 class="font-bold text-xl">Payment record #{{ $payment->id }}</h1>
      <span class="text-xs font-bold uppercase rounded-full px-3 py-1 {{ $payment->status === 'paid' ? 'bg-leaf-100 text-leaf-700' : (in_array($payment->status, ['failed','cancelled','timeout']) ? 'bg-brand-50 text-brand-600' : 'bg-sun-100 text-stone-700') }}">{{ $payment->status }}</span>
    </div>
    <div class="border-t border-stone-100 dark:border-stone-800 my-4"></div>
    <dl class="text-sm space-y-2">
      @foreach([
        ['Order', $payment->order ? $payment->order->order_number : '#' . $payment->order_id],
        ['Customer', $payment->order?->customer_name],
        ['Method', strtoupper($payment->method)],
        ['Amount', $currency . ' ' . number_format($payment->amount, 2)],
        ['Phone', $payment->phone ?? $payment->order?->customer_phone ?? '—'],
        ['Provider', $payment->provider ?? '—'],
        ['Checkout Request ID', $payment->checkout_request_id ?? '—'],
        ['Merchant Request ID', $payment->merchant_request_id ?? '—'],
        ['M-Pesa Receipt', $payment->receipt_number ?? '—'],
        ['Result code', $payment->result_code ?? '—'],
        ['Result description', $payment->result_desc ?? '—'],
        ['Callback received', $payment->callback_received_at?->format('d M Y, H:i:s') ?? 'Not yet'],
        ['Verified at', $payment->verified_at?->format('d M Y, H:i:s') ?? '—'],
        ['Created', $payment->created_at->format('d M Y, H:i:s')],
      ] as [$label, $value])
        <div class="flex justify-between gap-4"><dt class="text-stone-500">{{ $label }}</dt><dd class="font-medium text-right break-all font-mono text-xs {{ in_array($label, ['M-Pesa Receipt', 'Checkout Request ID']) ? 'font-bold' : '' }}">{{ $value }}</dd></div>
      @endforeach
    </dl>
    @if($payment->method === 'mpesa' && $payment->status === 'pending')
      <form action="{{ route('admin.payments.query', $payment) }}" method="POST" class="mt-4">@csrf
        <button class="w-full flex items-center justify-center gap-2 border border-leaf-500 text-leaf-700 dark:text-leaf-100 font-bold rounded-full py-2"><i data-lucide="refresh-cw" class="w-4 h-4"></i>Query M-Pesa status now</button>
      </form>
    @endif
  </div>

  <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6 flex flex-col">
    <h2 class="font-bold flex items-center gap-2"><i data-lucide="braces" class="w-5 h-5"></i>Callback payload</h2>
    @if($payment->callback_payload)
      <pre class="mt-3 text-[11px] leading-relaxed bg-stone-950 text-stone-100 rounded-xl p-4 overflow-auto flex-1 max-h-[32rem]">{{ json_encode(json_decode($payment->callback_payload), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
      <p class="text-[11px] text-stone-400 mt-2">Raw payload received from Safaricom for this transaction.</p>
    @else
      <div class="flex-1 flex flex-col items-center justify-center text-stone-400 py-12">
        <i data-lucide="hourglass" class="w-12 h-12 mb-3"></i>
        <p class="text-sm">No callback received yet.</p>
        <p class="text-xs mt-1 max-w-xs text-center">If the customer paid, Daraja will POST to <code class="bg-stone-100 dark:bg-stone-800 rounded px-1">/api/payments/mpesa/callback</code>. Ensure MPESA_CALLBACK_URL is publicly reachable.</p>
      </div>
    @endif
  </div>
</div>
@endsection
