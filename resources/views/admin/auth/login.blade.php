<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin sign in — {{ $storeName }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>tailwind.config={theme:{extend:{colors:{brand:{500:'#E4572E',600:'#D64533'},sun:{400:'#FBBF24'},leaf:{500:'#22A45D',600:'#1B8A4C'}}}}}</script>
</head>
<body class="min-h-screen bg-stone-950 flex items-center justify-center p-4">
<div class="w-full max-w-4xl grid md:grid-cols-2 bg-stone-900 rounded-3xl overflow-hidden shadow-2xl border border-stone-800">
  <div class="hidden md:flex flex-col justify-between p-8 bg-gradient-to-br from-stone-900 via-stone-900 to-stone-800 text-stone-300 relative">
    <div class="flex items-center gap-2 text-white font-bold text-xl"><i data-lucide="shield-check" class="w-6 h-6 text-sun-400"></i>{{ $storeName }} <span class="text-sun-400">Admin</span></div>
    <div>
      <h1 class="text-3xl font-bold text-white leading-tight">Store operations,<br>in one place.</h1>
      <p class="mt-3 text-sm text-stone-400">Orders, payments, inventory, promotions, reports and staff management — restricted to authorised personnel.</p>
      <div class="flex gap-4 mt-6 text-xs text-stone-500">
        <span class="flex items-center gap-1"><i data-lucide="package" class="w-4 h-4"></i>Inventory</span>
        <span class="flex items-center gap-1"><i data-lucide="receipt" class="w-4 h-4"></i>Orders</span>
        <span class="flex items-center gap-1"><i data-lucide="smartphone" class="w-4 h-4"></i>M-Pesa</span>
      </div>
    </div>
    <p class="text-[11px] text-stone-600">Unauthorised access is prohibited and monitored.</p>
  </div>
  <div class="p-8">
    <div class="md:hidden flex items-center gap-2 text-white font-bold text-xl mb-6"><i data-lucide="shield-check" class="w-6 h-6 text-sun-400"></i>Admin sign in</div>
    <h2 class="text-white text-2xl font-bold hidden md:block">Staff sign in</h2>
    <p class="text-sm text-stone-500 mt-1">Use your staff credentials. Customer accounts cannot sign in here.</p>
    @if($errors->any())<div class="mt-4 flex items-center gap-2 bg-brand-500/10 border border-brand-500 text-red-300 rounded-xl px-4 py-2 text-sm"><i data-lucide="alert-circle" class="w-5 h-5"></i>{{ $errors->first() }}</div>@endif
    <form action="{{ route('admin.login.post') }}" method="POST" class="mt-6 space-y-4">@csrf
      <div><label class="text-xs font-semibold text-stone-500">Staff email</label><input type="email" name="email" required autofocus value="{{ old('email') }}" class="mt-1 w-full bg-stone-800 border border-stone-700 text-white rounded-xl px-3 py-3 focus:outline-none focus:ring-2 focus:ring-sun-400" placeholder="you@store.com"></div>
      <div><label class="text-xs font-semibold text-stone-500">Password</label><input type="password" name="password" required class="mt-1 w-full bg-stone-800 border border-stone-700 text-white rounded-xl px-3 py-3 focus:outline-none focus:ring-2 focus:ring-sun-400" placeholder="••••••••"></div>
      <label class="flex items-center gap-2 text-sm text-stone-400"><input type="checkbox" name="remember"> Remember me</label>
      <button class="w-full flex items-center justify-center gap-2 bg-leaf-500 hover:bg-leaf-600 text-white font-bold rounded-xl py-3"><i data-lucide="log-in" class="w-5 h-5"></i>Sign in to admin</button>
    </form>
    <a href="{{ route('home') }}" class="mt-6 flex items-center justify-center gap-1 text-xs text-stone-500 hover:text-stone-300"><i data-lucide="arrow-left" class="w-4 h-4"></i>Back to store</a>
  </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
