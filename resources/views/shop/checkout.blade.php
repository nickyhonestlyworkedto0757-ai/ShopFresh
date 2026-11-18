@extends('layouts.app')
@section('title', 'Checkout — ' . $storeName)
@section('content')
@php
$selectedZone = (int) old('delivery_zone_id', 0);
$fee = $defaultFee;
foreach ($zones as $zone) { if ($zone->id === $selectedZone) { $fee = (float) $zone->fee; } }
$total = max(0, $subtotal - $discount) + $fee;
@endphp
<div class="max-w-5xl mx-auto px-4 py-8 grid md:grid-cols-5 gap-6">
  <form action="{{ route('checkout.place') }}" method="POST" class="md:col-span-3 bg-white rounded-2xl border border-stone-100 p-6 space-y-4">@csrf
    <h1 class="font-display text-2xl font-bold flex items-center gap-2"><i data-lucide="credit-card" class="w-7 h-7"></i>Checkout</h1>
    <div class="grid sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Full name</label><input name="customer_name" required value="{{ auth()->user()->name }}" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2"></div>
      <div><label class="text-xs font-semibold text-stone-500">Email</label><input type="email" name="customer_email" required value="{{ auth()->user()->email }}" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2"></div>
      <div><label class="text-xs font-semibold text-stone-500">Phone</label><input name="customer_phone" required value="{{ auth()->user()->phone ?? '' }}" placeholder="07xx xxx xxx" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2"></div>
      <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Delivery address</label><input name="shipping_address" required class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2" placeholder="Street, building, apartment"></div>
      <div><label class="text-xs font-semibold text-stone-500">City</label><input name="city" required class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2" placeholder="Nairobi"></div>
      <div><label class="text-xs font-semibold text-stone-500">Delivery zone</label>
        <select name="delivery_zone_id" id="zone-select" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2">
          <option value="" data-fee="{{ $defaultFee }}" @selected(!$selectedZone)>Standard delivery — {{ $currency }} {{ number_format($defaultFee, 2) }}</option>
          @foreach($zones as $zone)
            <option value="{{ $zone->id }}" data-fee="{{ $zone->fee }}" @selected($selectedZone === $zone->id)>{{ $zone->name }} — {{ $currency }} {{ number_format($zone->fee, 2) }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div>
      <div class="text-xs font-semibold text-stone-500 mb-2">Payment method</div>
      <div class="grid grid-cols-3 gap-2 text-center text-sm">
        @foreach(['cod' => ['banknote', 'Cash on Delivery'], 'mpesa' => ['smartphone', 'M-Pesa'], 'card' => ['credit-card', 'Card']] as $val => [$icon, $label])
          <label class="border border-stone-200 rounded-2xl p-3 cursor-pointer has-[:checked]:border-leaf-500 has-[:checked]:bg-leaf-50 flex flex-col items-center gap-1">
            <input type="radio" name="payment_method" value="{{ $val }}" class="sr-only" {{ $val === 'cod' ? 'checked' : '' }}>
            <i data-lucide="{{ $icon }}" class="w-6 h-6"></i><span class="font-semibold text-xs">{{ $label }}</span>
          </label>
        @endforeach
      </div>
    </div>
    <div><label class="text-xs font-semibold text-stone-500">Order notes (optional)</label><textarea name="notes" rows="2" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2"></textarea></div>
    <button class="w-full flex items-center justify-center gap-2 bg-brand-500 text-white font-bold rounded-full py-4 text-lg hover:bg-brand-600"><i data-lucide="lock" class="w-5 h-5"></i>Place order — {{ $currency }} <span id="btn-total">{{ number_format($total, 2) }}</span></button>
  </form>

  <div class="md:col-span-2 space-y-4">
    <div class="bg-white rounded-2xl border border-stone-100 p-6">
      <h2 class="font-bold">Order summary</h2>
      <div class="mt-3 space-y-2 max-h-64 overflow-auto">
        @foreach($items as $item)
          <div class="flex justify-between text-sm"><span>{{ $item['qty'] }} × {{ $item['product']->name }}</span><span class="font-semibold">{{ number_format($item['line'], 2) }}</span></div>
        @endforeach
      </div>
      <form action="{{ route('checkout.coupon') }}" method="POST" class="mt-4 flex">@csrf
        <input name="code" placeholder="Coupon code" class="flex-1 border border-stone-200 rounded-l-xl px-3 py-2 text-sm uppercase">
        <button class="flex items-center gap-1 bg-sun-400 text-stone-900 font-bold px-4 rounded-r-xl text-sm"><i data-lucide="ticket" class="w-4 h-4"></i>Apply</button>
      </form>
      @if($coupon)<div class="mt-2 text-xs text-leaf-700 font-semibold flex justify-between"><span class="flex items-center gap-1"><i data-lucide="check-circle-2" class="w-4 h-4"></i>Coupon {{ $coupon->code }} applied</span><form action="{{ route('checkout.coupon.remove') }}" method="POST">@csrf @method('DELETE')<button class="text-brand-600">Remove</button></form></div>@endif
      <div class="border-t my-3"></div>
      <div class="flex justify-between text-sm"><span>Subtotal</span><span>{{ number_format($subtotal, 2) }}</span></div>
      <div class="flex justify-between text-sm text-leaf-600"><span>Discount</span><span>− {{ number_format($discount, 2) }}</span></div>
      <div class="flex justify-between text-sm text-stone-500"><span>Delivery</span><span id="sum-fee">{{ number_format($fee, 2) }}</span></div>
      <div class="border-t my-2"></div>
      <div class="flex justify-between font-black text-xl"><span>Total</span><span>{{ $currency }} <span id="sum-total">{{ number_format($total, 2) }}</span></span></div>
    </div>
  </div>
</div>
<script>
  const subtotal = {{ $subtotal }}, discount = {{ $discount }};
  const zoneSelect = document.getElementById('zone-select');
  const updateTotals = () => {
    const fee = parseFloat(zoneSelect.selectedOptions[0]?.dataset.fee || '0');
    const total = Math.max(0, subtotal - discount) + fee;
    document.getElementById('sum-fee').textContent = fee.toFixed(2);
    document.getElementById('sum-total').textContent = total.toFixed(2);
    document.getElementById('btn-total').textContent = total.toFixed(2);
  };
  zoneSelect?.addEventListener('change', updateTotals);
</script>
@endsection
