@extends('admin.layout')
@section('title', 'Admin Users — Admin')
@section('content')
<div class="flex justify-between items-center gap-4"><h1 class="text-2xl font-bold">Admin users & roles</h1><a href="{{ route('admin.admin-users.create') }}" class="flex items-center gap-1 bg-brand-500 text-white font-bold rounded-full px-5 py-2 text-sm hover:bg-brand-600"><i data-lucide="user-plus" class="w-4 h-4"></i>New admin</a></div>
<p class="text-sm text-stone-500 dark:text-stone-400 mt-1">Super Admins manage everything. Product Managers handle the catalog and marketing. Order Managers handle orders, payments and delivery. Support can view and update orders and manage customers.</p>
<div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden mt-4 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-stone-50 dark:bg-stone-800/50 text-left"><tr><th class="p-3">Name</th><th class="p-3">Role</th><th class="p-3">Phone</th><th class="p-3">Status</th><th></th></tr></thead>
    <tbody>
      @foreach($admins as $admin)
        <tr class="border-t border-stone-100 dark:border-stone-800 {{ $admin->is_blocked ? 'opacity-60' : '' }}">
          <td class="p-3"><div class="font-semibold">{{ $admin->name }}</div><div class="text-[11px] text-stone-400">{{ $admin->email }}</div></td>
          <td class="p-3"><span class="text-[10px] font-bold uppercase rounded-full px-2 py-0.5 {{ $admin->role === 'super_admin' ? 'bg-sun-100 text-stone-800' : 'bg-leaf-100 text-leaf-700' }}">{{ str_replace('_', ' ', $admin->role) }}</span></td>
          <td class="p-3">{{ $admin->phone ?? '—' }}</td>
          <td class="p-3">{{ $admin->is_blocked ? 'Blocked' : 'Active' }}</td>
          <td class="p-3 text-right whitespace-nowrap">
            <a href="{{ route('admin.admin-users.edit', $admin) }}" class="inline-flex items-center gap-1 text-leaf-700 dark:text-leaf-100 font-semibold"><i data-lucide="pencil" class="w-4 h-4"></i></a>
            @if($admin->id !== auth()->id())
              <form action="{{ route('admin.admin-users.destroy', $admin) }}" method="POST" class="inline" onsubmit="return confirm('Delete this admin?')">@csrf @method('DELETE')<button class="inline-flex items-center gap-1 text-brand-600 font-semibold ml-2"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form>
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
<div class="mt-4">{{ $admins->links() }}</div>
@endsection
