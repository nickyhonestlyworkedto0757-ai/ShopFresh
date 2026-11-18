@extends('layouts.app')
@section('title', 'Complete payment — ' . $storeName)
@section('content')
<div class="max-w-lg mx-auto px-4 py-12 text-center" id="payment-box">
  <div id="state-pending">
    <div class="w-20 h-20 bg-leaf-100 rounded-full flex items-center justify-center mx-auto">
      <i data-lucide="smartphone" class="w-10 h-10 text-leaf-600 animate-pulse"></i>
    </div>
    <h1 class="font-display text-2xl font-bold mt-6">Check your phone</h1>
    <p class="text-stone-500 mt-2 text-sm">We sent an M-Pesa payment prompt to <b>{{ $order->payment?->phone ?? $order->customer_phone }}</b> for <b>{{ $currency }} {{ number_format($order->total, 2) }}</b>.<br>Enter your M-Pesa PIN to complete payment for order <b>{{ $order->order_number }}</b>.</p>
    <div class="mt-6 inline-flex items-center gap-2 text-sm text-stone-500"><i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i>Waiting for confirmation...</div>
    <p class="text-[11px] text-stone-400 mt-6">Nothing happening? You can resend the prompt below.</p>
    <form action="{{ route('order.payment-retry', $order) }}" method="POST" class="mt-3 flex max-w-xs mx-auto gap-2">@csrf
      <input name="phone" required value="{{ $order->payment?->phone }}" placeholder="07XXXXXXXX" class="flex-1 border border-stone-200 rounded-xl px-3 py-2 text-sm">
      <button class="flex items-center gap-1 bg-leaf-500 text-white text-sm font-bold rounded-xl px-4 hover:bg-leaf-600"><i data-lucide="refresh-cw" class="w-4 h-4"></i>Resend</button>
    </form>
  </div>

  <div id="state-success" class="hidden">
    <div class="w-20 h-20 bg-leaf-100 rounded-full flex items-center justify-center mx-auto"><i data-lucide="check" class="w-10 h-10 text-leaf-600"></i></div>
    <h1 class="font-display text-2xl font-bold mt-6">Payment received!</h1>
    <p class="text-stone-500 mt-2">Your M-Pesa payment was confirmed. Receipt: <b id="receipt">—</b></p>
    <a id="success-link" href="{{ route('order.success', $order) }}" class="mt-6 inline-flex items-center gap-2 bg-brand-500 text-white font-bold rounded-full px-8 py-3 hover:bg-brand-600">View order<i data-lucide="arrow-right" class="w-5 h-5"></i></a>
  </div>

  <div id="state-failed" class="hidden">
    <div class="w-20 h-20 bg-brand-50 rounded-full flex items-center justify-center mx-auto"><i data-lucide="x" class="w-10 h-10 text-brand-600"></i></div>
    <h1 class="font-display text-2xl font-bold mt-6">Payment not completed</h1>
    <p class="text-stone-500 mt-2" id="fail-reason">The M-Pesa transaction was not successful.</p>
    <form action="{{ route('order.payment-retry', $order) }}" method="POST" class="mt-6 flex max-w-xs mx-auto gap-2">@csrf
      <input name="phone" required value="{{ $order->payment?->phone }}" placeholder="07XXXXXXXX" class="flex-1 border border-stone-200 rounded-xl px-3 py-2 text-sm">
      <button class="flex items-center gap-1 bg-leaf-500 text-white text-sm font-bold rounded-xl px-4 hover:bg-leaf-600"><i data-lucide="refresh-cw" class="w-4 h-4"></i>Retry</button>
    </form>
  </div>
</div>
<script>
(function () {
  const statusUrl = @json(route('order.payment-status', $order));
  const successBox = document.getElementById('state-success');
  const failedBox = document.getElementById('state-failed');
  const pendingBox = document.getElementById('state-pending');
  let attempts = 0;
  const poll = async () => {
    attempts++;
    try {
      const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
      const data = await res.json();
      if (data.payment_status === 'paid') {
        document.getElementById('receipt').textContent = data.receipt_number || 'confirmed';
        pendingBox.classList.add('hidden');
        failedBox.classList.add('hidden');
        successBox.classList.remove('hidden');
        return;
      }
      if (['failed', 'cancelled', 'timeout'].includes(data.payment_record_status)) {
        document.getElementById('fail-reason').textContent = data.result_desc || 'The payment was not completed.';
        pendingBox.classList.add('hidden');
        failedBox.classList.remove('hidden');
        return;
      }
    } catch (e) { /* keep polling */ }
    if (attempts < 60) setTimeout(poll, 4000);
  };
  setTimeout(poll, 3000);
})();
</script>
@endsection
