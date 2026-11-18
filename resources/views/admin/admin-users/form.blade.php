@extends('admin.layout')
@section('title', ($admin->exists ? 'Edit' : 'New') . ' admin — Admin')
@section('content')
<h1 class="text-2xl font-bold">{{ $admin->exists ? 'Edit' : 'New' }} admin</h1>
<form action="{{ $admin->exists ? route('admin.admin-users.update', $admin) : route('admin.admin-users.store') }}" method="POST" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6 mt-4 grid sm:grid-cols-2 gap-4 max-w-2xl">@csrf
  @if($admin->exists) @method('PUT') @endif
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Full name *</label><input name="name" required value="{{ old('name', $admin->name) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500">Email *</label><input type="email" name="email" required value="{{ old('email', $admin->email) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500">Phone</label><input name="phone" value="{{ old('phone', $admin->phone) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500">Role *</label><select name="role" required class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2">@foreach($roles as $value => $label)<option value="{{ $value }}" @selected(old('role', $admin->role) === $value)>{{ $label }}</option>@endforeach</select></div>
  @if($admin->exists)<div class="flex items-end"><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_blocked" value="1" @checked(old('is_blocked', $admin->is_blocked))> Block this account</label></div><div></div>@endif
  <div><label class="text-xs font-semibold text-stone-500">Password{{ $admin->exists ? ' (leave blank to keep)' : ' *' }}</label><input type="password" name="password" {{ $admin->exists ? '' : 'required' }} class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500">Confirm password</label><input type="password" name="password_confirmation" {{ $admin->exists ? '' : 'required' }} class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div class="sm:col-span-2 flex gap-3"><button class="flex items-center gap-2 bg-leaf-500 text-white font-bold rounded-full px-6 py-2 hover:bg-leaf-600"><i data-lucide="save" class="w-4 h-4"></i>Save</button><a href="{{ route('admin.admin-users.index') }}" class="px-6 py-2 text-stone-500">Cancel</a></div>
</form>
@endsection
