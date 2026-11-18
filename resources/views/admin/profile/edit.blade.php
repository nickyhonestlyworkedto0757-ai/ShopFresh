@extends('admin.layout')
@section('title', 'My Profile — Admin')
@section('content')
<h1 class="text-2xl font-bold">My profile</h1>
<p class="text-sm text-stone-500 dark:text-stone-400 mt-1">Update your name, email address and password. Essential for keeping the store admin secure.</p>
<form action="{{ route('admin.profile.update') }}" method="POST" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6 mt-4 grid sm:grid-cols-2 gap-4 max-w-2xl">@csrf @method('PUT')
  <div class="sm:col-span-2 text-xs font-bold uppercase tracking-widest text-stone-400 border-b border-stone-100 dark:border-stone-800 pb-2">Account</div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Full name</label><input name="name" required value="{{ old('name', $user->name) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Email</label><input type="email" name="email" required value="{{ old('email', $user->email) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>

  <div class="sm:col-span-2 text-xs font-bold uppercase tracking-widest text-stone-400 border-b border-stone-100 dark:border-stone-800 pb-2 pt-2">Change password <span class="normal-case text-stone-400 font-normal">(leave blank to keep current)</span></div>
  <div><label class="text-xs font-semibold text-stone-500">Current password</label><input type="password" name="current_password" autocomplete="current-password" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div></div>
  <div><label class="text-xs font-semibold text-stone-500">New password</label><input type="password" name="password" autocomplete="new-password" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500">Confirm new password</label><input type="password" name="password_confirmation" autocomplete="new-password" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>

  <div class="sm:col-span-2 flex gap-3"><button class="flex items-center gap-2 bg-leaf-500 text-white font-bold rounded-full px-6 py-2 hover:bg-leaf-600"><i data-lucide="save" class="w-4 h-4"></i>Save changes</button><a href="{{ route('admin.dashboard') }}" class="px-6 py-2 text-stone-500">Cancel</a></div>
</form>
@endsection
