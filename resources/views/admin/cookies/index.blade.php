@extends('admin.layout')
@section('title', 'Cookie Consent — Admin')
@section('content')
<h1 class="text-2xl font-bold">Cookie consent</h1>
<p class="text-sm text-stone-500 dark:text-stone-400 mt-1">Manage cookie categories, descriptions and see how customers respond.</p>

<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mt-4">
  @foreach([['Total consents', $total, 'cookie'], ['Accept all', $acceptAll, 'check-check'], ['Functional', $functional, 'sliders-horizontal'], ['Analytics', $analytics, 'bar-chart-3'], ['Marketing', $marketing, 'megaphone']] as [$label, $value, $icon])
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4"><i data-lucide="{{ $icon }}" class="w-6 h-6 text-leaf-600"></i><div class="text-2xl font-black mt-1">{{ number_format($value) }}</div><div class="text-[11px] text-stone-500">{{ $label }}</div></div>
  @endforeach
</div>
@if($total > 0)
  <p class="text-xs text-stone-400 mt-2">{{ round($acceptAll / $total * 100, 1) }}% accept all</p>
@endif

<form action="{{ route('admin.cookies.update') }}" method="POST" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6 mt-4 grid gap-4">@csrf @method('PUT')
  @foreach(['essential' => ['Essential', 'lock', false], 'functional' => ['Functional', 'sliders-horizontal', true], 'analytics' => ['Analytics', 'bar-chart-3', true], 'marketing' => ['Marketing', 'megaphone', true]] as $key => [$label, $icon, $editable])
    <div><label class="text-xs font-semibold text-stone-500"><i data-lucide="{{ $icon }}" class="inline w-4 h-4"></i> {{ $label }} description</label>
      @if($editable)<textarea name="cookie_{{ $key }}_desc" rows="2" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm">{{ $descriptions[$key] }}</textarea>@else<textarea disabled rows="2" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-800/50 rounded-xl px-3 py-2 text-sm text-stone-500">{{ $descriptions[$key] }}</textarea>@endif
    </div>
  @endforeach
  <div><label class="text-xs font-semibold text-stone-500">Consent cookie lifetime (days)</label><input type="number" min="1" max="3650" name="cookie_lifetime" required value="{{ $lifetime }}" class="mt-1 w-40 border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"><p class="text-[11px] text-stone-400 mt-1">After this period, customers are asked again.</p></div>
  <div><button class="flex items-center gap-2 bg-leaf-500 text-white font-bold rounded-full px-6 py-2 hover:bg-leaf-600"><i data-lucide="save" class="w-4 h-4"></i>Save cookie settings</button></div>
</form>

<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4">
  <h2 class="font-bold p-4 border-b border-stone-100 dark:border-stone-800">Recent consent records</h2>
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">When</th><th class="p-3">User</th><th class="p-3">Essential</th><th class="p-3">Functional</th><th class="p-3">Analytics</th><th class="p-3">Marketing</th></tr></thead>
    <tbody>
      @forelse($recent as $consent)
        <tr class="border-t border-stone-100 dark:border-stone-800"><td class="p-3 text-xs text-stone-500">{{ $consent->consented_at->format('d M, H:i') }}</td><td class="p-3">{{ $consent->user_id ? 'User #' . $consent->user_id : 'Guest' }}</td><td class="p-3">Always on</td><td class="p-3">{{ $consent->functional ? 'Yes' : 'No' }}</td><td class="p-3">{{ $consent->analytics ? 'Yes' : 'No' }}</td><td class="p-3">{{ $consent->marketing ? 'Yes' : 'No' }}</td></tr>
      @empty
        <tr><td colspan="6" class="p-6 text-center text-stone-400">No consent records yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
