@extends('layouts.app')
@section('title', 'Notification preferences — ' . $storeName)
@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
  <h1 class="font-display text-3xl font-bold flex items-center gap-3"><i data-lucide="bell-ring" class="w-8 h-8"></i>Notification preferences</h1>
  <p class="text-sm text-stone-500 mt-1">Choose how we contact you. Order-related reminders are separate from marketing.</p>

  <div class="bg-white rounded-2xl border border-stone-100 p-6 mt-6">
    <h2 class="font-bold flex items-center gap-2"><i data-lucide="smartphone" class="w-5 h-5"></i>Phone verification</h2>
    @if($phoneVerified)
      <p class="text-sm text-leaf-700 mt-2 flex items-center gap-2"><i data-lucide="check-circle-2" class="w-5 h-5"></i>Phone verified: {{ auth()->user()->phone }}</p>
    @else
      <form action="{{ route('account.phone.send') }}" method="POST" class="mt-3 flex flex-wrap gap-2 items-end">@csrf
        <div class="flex-1 min-w-[180px]"><label class="text-xs font-semibold text-stone-500">Phone number</label><input name="phone" required value="{{ auth()->user()->phone }}" placeholder="07XXXXXXXX" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2 text-sm"></div>
        <button class="flex items-center gap-1 bg-leaf-500 text-white text-sm font-bold rounded-xl px-4 py-2 hover:bg-leaf-600"><i data-lucide="send" class="w-4 h-4"></i>Send code</button>
      </form>
      <form action="{{ route('account.phone.verify') }}" method="POST" class="mt-2 flex gap-2 items-end max-w-xs">@csrf
        <div class="flex-1"><label class="text-xs font-semibold text-stone-500">Enter 6-digit code</label><input name="code" required pattern="[0-9]{6}" placeholder="123456" class="mt-1 w-full border border-stone-200 rounded-xl px-3 py-2 text-sm tracking-widest text-center"></div>
        <button class="flex items-center gap-1 bg-stone-900 text-white text-sm font-bold rounded-xl px-4 py-2"><i data-lucide="check" class="w-4 h-4"></i>Verify</button>
      </form>
    @endif
  </div>

  <form action="{{ route('account.preferences.update') }}" method="POST" class="bg-white rounded-2xl border border-stone-100 p-6 mt-4">@csrf @method('PUT')
    <h2 class="font-bold flex items-center gap-2"><i data-lucide="mail" class="w-5 h-5"></i>Order updates <span class="text-xs font-normal text-stone-400">(order receipts, delivery status)</span></h2>
    <label class="flex items-center gap-2 text-sm mt-2"><input type="checkbox" name="email_order_updates" value="1" @checked($prefs->email_order_updates)> Email order updates</label>
    <label class="flex items-center gap-2 text-sm mt-1"><input type="checkbox" name="sms_order_updates" value="1" @checked($prefs->sms_order_updates)> SMS order updates</label>

    <h2 class="font-bold flex items-center gap-2 mt-6"><i data-lucide="megaphone" class="w-5 h-5"></i>Product announcements</h2>
    <label class="flex items-center gap-2 text-sm mt-2"><input type="checkbox" name="email_new_products" value="1" @checked($prefs->email_new_products)> Email new product alerts</label>
    <label class="flex items-center gap-2 text-sm mt-1"><input type="checkbox" name="sms_new_products" value="1" @checked($prefs->sms_new_products)> SMS new product alerts</label>

    <h2 class="font-bold flex items-center gap-2 mt-6"><i data-lucide="tag" class="w-5 h-5"></i>Offers &amp; promotions</h2>
    <label class="flex items-center gap-2 text-sm mt-2"><input type="checkbox" name="email_promotions" value="1" @checked($prefs->email_promotions)> Email promotions and offers</label>
    <label class="flex items-center gap-2 text-sm mt-1"><input type="checkbox" name="sms_promotions" value="1" @checked($prefs->sms_promotions)> SMS promotions and offers</label>

    <button class="mt-6 flex items-center gap-2 bg-leaf-500 text-white font-bold rounded-full px-6 py-2 hover:bg-leaf-600"><i data-lucide="save" class="w-4 h-4"></i>Save preferences</button>
  </form>

  <div class="bg-white rounded-2xl border border-stone-100 p-6 mt-4">
    <h2 class="font-bold flex items-center gap-2"><i data-lucide="history" class="w-5 h-5"></i>Recent notifications sent to you</h2>
    <table class="w-full text-xs mt-2">
      <thead class="text-left text-stone-500"><tr><th class="py-1">When</th><th>Channel</th><th>Type</th><th>Status</th></tr></thead>
      <tbody>
        @forelse($logs as $log)
          <tr class="border-t border-stone-100"><td class="py-2">{{ $log->created_at->format('d M, H:i') }}</td><td class="uppercase">{{ $log->channel }}</td><td>{{ str_replace('_', ' ', $log->type) }}</td><td class="{{ $log->status === 'failed' ? 'text-brand-600' : 'text-leaf-700' }}">{{ $log->status }}</td></tr>
        @empty
          <tr><td colspan="4" class="py-4 text-center text-stone-400">No notifications yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
