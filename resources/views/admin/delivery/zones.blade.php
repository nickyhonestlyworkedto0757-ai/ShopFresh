@extends('admin.layout')
@section('title', 'Delivery Zones — Admin')
@section('content')
<h1 class="text-2xl font-bold">Delivery zones</h1>
<p class="text-sm text-stone-500 dark:text-stone-400 mt-1">Set delivery fees per area. Customers pick a zone at checkout; the fee is added to their order automatically.</p>

<form action="{{ route('admin.delivery.zones.store') }}" method="POST" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-4 mt-4 flex flex-wrap gap-3 items-end max-w-2xl">@csrf
  <div class="flex-1 min-w-[160px]"><label class="text-xs font-semibold text-stone-500">Zone name</label><input name="name" required placeholder="Nairobi CBD" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm"></div>
  <div class="w-36"><label class="text-xs font-semibold text-stone-500">Fee ({{ $currency }})</label><input type="number" step="0.01" min="0" name="fee" required placeholder="150" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm"></div>
  <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label>
  <button class="flex items-center gap-1 bg-leaf-500 text-white font-bold rounded-full px-5 py-2 hover:bg-leaf-600"><i data-lucide="plus" class="w-4 h-4"></i>Add zone</button>
</form>

<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">Zone</th><th class="p-3">Fee</th><th class="p-3">Status</th><th></th></tr></thead>
    <tbody>
      @forelse($zones as $zone)
        <tr class="border-t border-stone-100 dark:border-stone-800">
          <td colspan="4" class="p-3">
            <div class="flex flex-wrap gap-2 items-center">
              <form action="{{ route('admin.delivery.zones.update', $zone) }}" method="POST" class="flex flex-wrap gap-2 items-center flex-1">@csrf @method('PATCH')
                <input name="name" value="{{ $zone->name }}" class="border border-stone-200 dark:border-stone-700 bg-transparent rounded-lg px-2 py-1 text-sm flex-1 min-w-[140px]">
                <input type="number" step="0.01" min="0" name="fee" value="{{ $zone->fee }}" class="border border-stone-200 dark:border-stone-700 bg-transparent rounded-lg px-2 py-1 text-sm w-28">
                <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="is_active" value="1" @checked($zone->is_active)> Active</label>
                <button type="submit" class="text-leaf-700 dark:text-leaf-100 font-semibold inline-flex items-center gap-1"><i data-lucide="save" class="w-4 h-4"></i>Save</button>
              </form>
              <form action="{{ route('admin.delivery.zones.destroy', $zone) }}" method="POST" onsubmit="return confirm('Delete zone?')">@csrf @method('DELETE')
                <button class="text-brand-600 font-semibold inline-flex items-center gap-1"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="p-6 text-center text-stone-400">No zones yet. Add your delivery areas above.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $zones->links() }}</div>
@endsection
