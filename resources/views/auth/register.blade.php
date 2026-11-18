@extends('layouts.app')
@section('title', 'Create account — ' . $storeName)
@section('content')
<div class="relative overflow-hidden bg-stone-950">
  <div class="absolute inset-0 opacity-40" style="background-image: radial-gradient(circle at 85% 15%, #22A45D 0, transparent 45%), radial-gradient(circle at 15% 85%, #FBBF24 0, transparent 45%)"></div>
  <div class="relative max-w-5xl mx-auto px-4 py-14 md:py-20 grid md:grid-cols-2 gap-10 items-center">
    <div class="text-white text-center md:text-left">
      <div class="flex items-center justify-center md:justify-start gap-3">
        <span class="w-12 h-12 rounded-2xl bg-sun-400 text-stone-950 flex items-center justify-center"><i data-lucide="user-plus" class="w-6 h-6"></i></span>
        <span class="text-3xl font-bold">{{ $storeName }}</span>
      </div>
      <h1 class="mt-6 font-display text-4xl md:text-5xl font-bold leading-tight">Join <span class="text-sun-400">{{ $storeName }}</span> today.</h1>
      <p class="mt-3 text-stone-300">One account, every order — track deliveries, save addresses and never miss a Flash Sale.</p>
      <ul class="mt-6 space-y-2 text-sm text-stone-400">
        <li class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-5 h-5 text-leaf-500"></i> Flash sale alerts</li>
        <li class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-5 h-5 text-leaf-500"></i> Secure M-Pesa checkout</li>
        <li class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-5 h-5 text-leaf-500"></i> Priority same-day delivery</li>
      </ul>
    </div>
    <div class="bg-white rounded-3xl shadow-2xl border border-stone-100 p-8 md:p-10 w-full">
      <h2 class="font-display text-2xl font-bold text-stone-900">Create account</h2>
      <p class="text-sm text-stone-500 mt-1">It takes less than a minute.</p>
      <form action="{{ route('register') }}" method="POST" class="mt-6 space-y-4">@csrf
        <div>
          <label for="name" class="text-[11px] uppercase tracking-widest font-bold text-stone-500">Full name</label>
          <input id="name" name="name" required value="{{ old('name') }}" autocomplete="name" autofocus class="mt-1 w-full border border-stone-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-100 rounded-xl px-4 py-3 text-sm outline-none" placeholder="Jordan Otieno">
        </div>
        <div>
          <label for="email" class="text-[11px] uppercase tracking-widest font-bold text-stone-500">Email</label>
          <input id="email" type="email" name="email" required value="{{ old('email') }}" autocomplete="email" class="mt-1 w-full border border-stone-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-100 rounded-xl px-4 py-3 text-sm outline-none" placeholder="you@example.com">
        </div>
        <div>
          <label for="phone" class="text-[11px] uppercase tracking-widest font-bold text-stone-500">Phone (M-Pesa)</label>
          <input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" class="mt-1 w-full border border-stone-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-100 rounded-xl px-4 py-3 text-sm outline-none" placeholder="07XXXXXXXX">
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label for="password" class="text-[11px] uppercase tracking-widest font-bold text-stone-500">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="mt-1 w-full border border-stone-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-100 rounded-xl px-4 py-3 text-sm outline-none" placeholder="Min 8 characters">
          </div>
          <div>
            <label for="password_confirmation" class="text-[11px] uppercase tracking-widest font-bold text-stone-500">Confirm</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="mt-1 w-full border border-stone-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-100 rounded-xl px-4 py-3 text-sm outline-none" placeholder="Repeat">
          </div>
        </div>
        <button class="w-full flex items-center justify-center gap-2 bg-leaf-600 text-white font-bold rounded-xl py-3.5 hover:bg-leaf-700 transition shadow-lg shadow-leaf-600/20"><i data-lucide="user-plus" class="w-5 h-5"></i>Create account</button>
      </form>
      <p class="text-sm text-center text-stone-500 mt-5">Already have an account? <a href="{{ route('login') }}" class="text-brand-600 font-bold hover:underline">Log in</a></p>
    </div>
  </div>
</div>
@endsection
