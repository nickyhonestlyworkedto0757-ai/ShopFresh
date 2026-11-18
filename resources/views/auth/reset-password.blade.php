@extends('layouts.app')
@section('title', 'Set new password — ' . $storeName)
@section('content')
<div class="max-w-md mx-auto px-4 py-14">
  <div class="bg-white rounded-3xl border border-stone-100 shadow-sm p-8">
    <div class="flex items-center gap-3">
      <span class="w-12 h-12 rounded-2xl bg-leaf-100 flex items-center justify-center"><i data-lucide="key-round" class="w-6 h-6 text-leaf-700"></i></span>
      <div>
        <h1 class="font-display text-2xl font-bold">Choose a new password</h1>
        <p class="text-xs text-stone-500">Set a strong password to secure your account</p>
      </div>
    </div>
    @if($errors->any())<div class="mt-4 flex items-center gap-2 bg-brand-50 border border-brand-500 text-brand-700 rounded-xl px-4 py-2 text-sm"><i data-lucide="alert-circle" class="w-5 h-5"></i>{{ $errors->first() }}</div>@endif
    <form action="{{ route('password.update') }}" method="POST" class="mt-6 space-y-4">@csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <div>
        <label for="email" class="text-[11px] uppercase tracking-widest font-bold text-stone-500">Email</label>
        <input id="email" type="email" name="email" required value="{{ old('email', $email) }}" class="mt-1 w-full border border-stone-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-100 rounded-xl px-4 py-3 text-sm outline-none">
      </div>
      <div>
        <label for="password" class="text-[11px] uppercase tracking-widest font-bold text-stone-500">New password</label>
        <div class="relative mt-1">
          <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400"></i>
          <input id="password" type="password" name="password" required autocomplete="new-password" class="w-full border border-stone-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-100 rounded-xl pl-12 pr-4 py-3 text-sm outline-none" placeholder="Minimum 8 characters">
        </div>
      </div>
      <div>
        <label for="password_confirmation" class="text-[11px] uppercase tracking-widest font-bold text-stone-500">Confirm password</label>
        <div class="relative mt-1">
          <i data-lucide="lock-check" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400"></i>
          <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full border border-stone-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-100 rounded-xl pl-12 pr-4 py-3 text-sm outline-none" placeholder="Repeat it">
        </div>
      </div>
      <button class="w-full flex items-center justify-center gap-2 bg-leaf-600 text-white font-bold rounded-xl py-3.5 hover:bg-leaf-700 transition"><i data-lucide="check-circle-2" class="w-5 h-5"></i>Reset password</button>
    </form>
  </div>
</div>
@endsection
