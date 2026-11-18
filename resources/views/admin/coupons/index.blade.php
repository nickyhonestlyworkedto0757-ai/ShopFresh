@extends('admin.layout')
@section('title', 'Coupons — ShopFresh Admin')
@section('content')
<div class="flex justify-between items-center"><h1 class="text-2xl font-bold">Coupons</h1><a href="{{ route('admin.coupons.create') }}" class="flex items-center gap-1 bg-brand-500 text-white font-bold rounded-full px-5 py-2 text-sm"><i data-lucide="plus" class="w-4 h-4"></i>New coupon</a></div>
<div class="bg-white rounded-2xl border border-stone-200 overflow-hidden mt-4 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 text-left"><tr><th class="p-3">Code</th><th class="p-3">Discount</th><th class="p-3">Min. purchase</th><th class="p-3">Used</th><th class="p-3">Expires</th><th class="p-3">Active</th><th></th></tr></thead>
    <tbody>
      @forelse($coupons as $coupon)
        <tr class="border-t border-stone-100"><td class="p-3 font-mono font-bold">{{ $coupon->code }}</td><td class="p-3">{{ $coupon->type === 'percent' ? $coupon->value . '%' : $currency . ' ' . number_format($coupon->value, 2) }}</td><td class="p-3">{{ $coupon->min_purchase > 0 ? $currency . ' ' . number_format($coupon->min_purchase, 2) : 'None' }}</td><td class="p-3">{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / ' . $coupon->usage_limit : '' }}</td><td class="p-3">{{ $coupon->expires_at?->format('d M Y') ?? '—' }}</td><td class="p-3">@if($coupon->is_active)<i data-lucide="check-circle-2" class="w-5 h-5 text-leaf-600"></i>@else<i data-lucide="x-circle" class="w-5 h-5 text-stone-300"></i>@endif</td><td class="p-3 text-right whitespace-nowrap"><a href="{{ route('admin.coupons.edit', $coupon) }}" class="inline-flex items-center gap-1 text-leaf-700 font-semibold"><i data-lucide="pencil" class="w-4 h-4"></i>Edit</a><form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="inline-flex items-center gap-1 text-brand-600 font-semibold ml-3"><i data-lucide="trash-2" class="w-4 h-4"></i>Delete</button></form></td></tr>
      @empty
        <tr><td colspan="6" class="p-6 text-center text-stone-400">No coupons yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $coupons->links() }}</div>
@endsection
