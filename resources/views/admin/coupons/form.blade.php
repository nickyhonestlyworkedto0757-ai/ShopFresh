@extends('admin.layout')
@section('title', ($coupon->exists ? 'Edit' : 'New') . ' coupon')
@section('content')
<h1 class="text-2xl font-bold">{{ $coupon->exists ? 'Edit' : 'New' }} coupon</h1>
<form action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" method="POST" class="bg-white rounded-2xl border border-stone-200 p-6 mt-4 grid sm:grid-cols-2 gap-4 max-w-2xl">@csrf
  @if($coupon->exists) @method('PUT') @endif
  <div><label class="text-xs font-semibold text-stone-500">Code</label><input name="code" required value="{{ old('code', $coupon->code) }}" placeholder="FRESH10" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2 uppercase"></div>
  <div><label class="text-xs font-semibold text-stone-500">Type</label><select name="type" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2"><option value="percent" @selected(old('type', $coupon->type) === 'percent')>Percent (%)</option><option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Fixed (KES)</option></select></div>
  <div><label class="text-xs font-semibold text-stone-500">Value</label><input type="number" step="0.01" min="0" name="value" required value="{{ old('value', $coupon->value) }}" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500">Minimum purchase ({{ $currency }})</label><input type="number" step="0.01" min="0" name="min_purchase" value="{{ old('min_purchase', $coupon->min_purchase ?? 0) }}" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500">Usage limit (optional)</label><input type="number" min="1" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500">Expires at (optional)</label><input type="date" name="expires_at" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d')) }}" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2"></div>
  <div class="flex items-end"><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active ?? true))> Active</label></div>
  <div class="sm:col-span-2 flex gap-3"><button class="flex items-center gap-2 bg-leaf-500 text-white font-bold rounded-full px-6 py-2"><i data-lucide="save" class="w-4 h-4"></i>Save</button><a href="{{ route('admin.coupons.index') }}" class="px-6 py-2 text-stone-500">Cancel</a></div>
</form>
@endsection
