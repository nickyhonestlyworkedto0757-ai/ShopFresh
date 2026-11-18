@extends('layouts.app')
@section('title', 'Log in — ' . $storeName)
@section('content')
<div class="relative overflow-hidden bg-stone-950">
  <div class="absolute inset-0 opacity-40" style="background-image: radial-gradient(circle at 15% 20%, #FBBF24 0, transparent 45%), radial-gradient(circle at 85% 80%, #22A45D 0, transparent 45%)"></div>
  <div class="relative max-w-5xl mx-auto px-4 py-14 md:py-20 grid md:grid-cols-2 gap-10 items-center">
    <div class="text-white text-center md:text-left">
      <div class="flex items-center justify-center md:justify-start gap-3">
        <span class="w-12 h-12 rounded-2xl bg-sun-400 text-stone-950 flex items-center justify-center"><i data-lucide="shopping-basket" class="w-6 h-6"></i></span>
        <span class="text-3xl font-bold">{{ $storeName }}</span>
      </div>
      <h1 class="mt-6 font-display text-4xl md:text-5xl font-bold leading-tight">Welcome <span class="text-sun-400">back</span>.</h1>
      <p class="mt-3 text-stone-300">Log in to track your orders, manage your cart and check out faster.</p>
      <ul class="mt-6 space-y-2 text-sm text-stone-400">
        <li class="flex items-center gap-2"><i data-lucide="package" class="w-5 h-5 text-leaf-500"></i> Live order tracking</li>
        <li class="flex items-center gap-2"><i data-lucide="bell" class="w-5 h-5 text-leaf-500"></i> Order &amp; delivery notifications</li>
        <li class="flex items-center gap-2"><i data-lucide="shield-check" class="w-5 h-5 text-leaf-500"></i> Secure checkout (M-Pesa · COD · Card)</li>
      </ul>
    </div>

    <div class="bg-white rounded-3xl shadow-2xl border border-stone-100 p-8 md:p-10 w-full">
      <h2 class="font-display text-2xl font-bold text-stone-900">Sign in</h2>
      <p class="text-sm text-stone-500 mt-1">Use your account email and password.</p>
      <form action="{{ route('login') }}" method="POST" class="mt-6 space-y-5">@csrf
        <div>
          <label for="email" class="text-[11px] uppercase tracking-widest font-bold text-stone-500">Email</label>
          <div class="relative mt-1">
            <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400"></i>
            <input id="email" type="email" name="email" required value="{{ old('email') }}" autocomplete="email" autofocus class="w-full border border-stone-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-100 rounded-xl pl-12 pr-4 py-3 text-sm placeholder-stone-400 outline-none" placeholder="you@example.com">
          </div>
        </div>
        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="text-[11px] uppercase tracking-widest font-bold text-stone-500">Password</label>
            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-leaf-700 hover:underline">Forgot password?</a>
          </div>
          <div class="relative mt-1">
            <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400"></i>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="w-full border border-stone-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-100 rounded-xl pl-12 pr-4 py-3 text-sm placeholder-stone-400 outline-none" placeholder="••••••••">
          </div>
        </div>
        <label class="flex items-center gap-2 text-sm text-stone-600"><input type="checkbox" name="remember" class="accent-leaf-600"> Remember me</label>
        <button class="w-full flex items-center justify-center gap-2 bg-leaf-600 text-white font-bold rounded-xl py-3.5 hover:bg-leaf-700 transition shadow-lg shadow-leaf-600/20"><i data-lucide="log-in" class="w-5 h-5"></i>Log in</button>
      </form>
      <div class="flex items-center gap-3 my-5"><div class="flex-1 border-t border-stone-200"></div><span class="text-[10px] uppercase tracking-widest text-stone-400">or</span><div class="flex-1 border-t border-stone-200"></div></div>
      <p class="text-sm text-center text-stone-500">New here? <a href="{{ route('register') }}" class="text-brand-600 font-bold hover:underline">Create an account</a></p>
    </div>
  </div>
</div>
@endsection
