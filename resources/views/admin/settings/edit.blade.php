@extends('admin.layout')
@section('title', 'Settings — Admin')
@section('content')
<h1 class="text-2xl font-bold">Store settings</h1>
<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6 mt-4 grid sm:grid-cols-2 gap-4 max-w-3xl">@csrf @method('PUT')
  <div class="sm:col-span-2 text-xs font-bold uppercase tracking-widest text-stone-400 border-b border-stone-100 dark:border-stone-800 pb-2">Staff access control</div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Approved admin emails</label>
    <textarea name="admin_approved_emails" rows="4" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 font-mono text-xs" placeholder="one@email.com&#10;another@email.com">{{ str_replace(',', "\n", old('admin_approved_emails', $settings['admin_approved_emails'] ?? '')) }}</textarea>
    <p class="text-[11px] text-stone-400 mt-1">Only these emails can unlock the admin area. Newly created staff accounts are added automatically. One email per line (comma-separated also works).</p>
  </div>

  <div class="sm:col-span-2 text-xs font-bold uppercase tracking-widest text-stone-400 border-b border-stone-100 dark:border-stone-800 pb-2 pt-2">Branding</div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Store name</label><input name="store_name" required value="{{ old('store_name', $settings['store_name']) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Store logo</label>
    <div class="mt-1 flex items-center gap-4">
      <div class="w-16 h-16 rounded-xl bg-leaf-50 dark:bg-stone-800 flex items-center justify-center overflow-hidden shrink-0">
        @if($settings['store_logo'])<img src="{{ asset('storage/' . $settings['store_logo']) }}" alt="Logo" class="h-full w-full object-cover">@else<i data-lucide="image" class="w-7 h-7 text-leaf-600"></i>@endif
      </div>
      <input type="file" name="store_logo" accept="image/jpeg,image/png,image/webp,image/svg+xml" class="text-xs">
    </div>
  </div>

  <div class="sm:col-span-2 text-xs font-bold uppercase tracking-widest text-stone-400 border-b border-stone-100 dark:border-stone-800 pb-2 pt-2">Contact & currency</div>
  <div><label class="text-xs font-semibold text-stone-500">Store email</label><input type="email" name="store_email" value="{{ old('store_email', $settings['store_email']) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500">Store phone</label><input name="store_phone" value="{{ old('store_phone', $settings['store_phone']) }}" placeholder="+254 700 000 000" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Store address</label><input name="store_address" value="{{ old('store_address', $settings['store_address']) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500">Currency</label><input name="currency" required value="{{ old('currency', $settings['currency']) }}" placeholder="KES" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500">Default delivery fee ({{ $settings['currency'] }})</label><input type="number" step="0.01" min="0" name="delivery_fee" required value="{{ old('delivery_fee', $settings['delivery_fee']) }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"><p class="text-[11px] text-stone-400 mt-1">Used when no delivery zone is selected. Manage zones under Delivery Zones.</p></div>

  <div class="sm:col-span-2 text-xs font-bold uppercase tracking-widest text-stone-400 border-b border-stone-100 dark:border-stone-800 pb-2 pt-2">Top promo bar</div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Favicon (browser tab icon)</label>
    <div class="mt-1 flex items-center gap-4">
      <div class="w-12 h-12 rounded-lg bg-leaf-50 dark:bg-stone-800 flex items-center justify-center overflow-hidden shrink-0">
        @if($settings['favicon'])<img src="{{ asset('storage/' . $settings['favicon']) }}" alt="Favicon" class="h-full w-full object-contain">@else<i data-lucide="monitor" class="w-6 h-6 text-leaf-600"></i>@endif
      </div>
      <div class="text-xs text-stone-500">
        <input type="file" name="favicon" accept="image/jpeg,image/png,image/webp,image/x-icon,.ico,image/svg+xml" class="text-xs">
        @if($settings['favicon'])<label class="mt-2 flex items-center gap-2"><input type="checkbox" name="remove_favicon" value="1"> Remove icon</label>@endif
        <p class="mt-1">Recommended: square, 64 × 64 px (PNG, SVG or ICO). Max 4MB.</p>
      </div>
    </div>
  </div>

  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Promo strip image (shown above the header, e.g. Black Friday)</label>
    <div class="mt-1 flex items-center gap-4">
      <div class="w-64 h-14 rounded-xl bg-leaf-50 dark:bg-stone-800 flex items-center justify-center overflow-hidden shrink-0">
        @if($settings['promo_image'])<img src="{{ asset('storage/' . $settings['promo_image']) }}" alt="Promo bar" class="h-full w-full object-cover">@else<i data-lucide="megaphone" class="w-7 h-7 text-leaf-600"></i>@endif
      </div>
      <div class="text-xs text-stone-500">
        <input type="file" name="promo_image" accept="image/jpeg,image/png,image/webp" class="text-xs">
        @if($settings['promo_image'])<label class="mt-2 flex items-center gap-2"><input type="checkbox" name="remove_promo_image" value="1"> Remove image</label>@endif
        <p class="mt-1">Recommended: 1920 × 64 px, JPG/WebP, under 500KB.</p>
      </div>
    </div>
    <div class="mt-3"><label class="text-xs font-semibold text-stone-500">Promo link (optional)</label><input name="promo_link" value="{{ old('promo_link', $settings['promo_link'] ?? '') }}" placeholder="/shop or https://..." class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  </div>

  <div class="sm:col-span-2 text-xs font-bold uppercase tracking-widest text-stone-400 border-b border-stone-100 dark:border-stone-800 pb-2 pt-2">Social links</div>
  @foreach(['social_facebook' => 'Facebook URL', 'social_instagram' => 'Instagram URL', 'social_twitter' => 'X / Twitter URL', 'social_tiktok' => 'TikTok URL'] as $key => $label)
    <div><label class="text-xs font-semibold text-stone-500">{{ $label }}</label><input name="{{ $key }}" value="{{ old($key, $settings[$key] ?? '') }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  @endforeach

  <div class="sm:col-span-2 text-xs font-bold uppercase tracking-widest text-stone-400 border-b border-stone-100 dark:border-stone-800 pb-2 pt-2 flex items-center justify-between">M-Pesa payments (Daraja)
    @if(app(\App\Services\MpesaService::class)->isConfigured())<span class="flex items-center gap-1 text-[11px] font-semibold text-leaf-700 dark:text-leaf-100"><i data-lucide="check-circle-2" class="w-4 h-4"></i>Configured</span>@else<span class="flex items-center gap-1 text-[11px] font-semibold text-brand-600"><i data-lucide="alert-circle" class="w-4 h-4"></i>Not configured</span>@endif
  </div>
  <div><label class="text-xs font-semibold text-stone-500">Environment</label><select name="mpesa_env" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"><option value="sandbox" @selected(($settings['mpesa_env'] ?? 'sandbox') === 'sandbox')>Sandbox (testing)</option><option value="production" @selected(($settings['mpesa_env'] ?? '') === 'production')>Production (live)</option></select></div>
  <div><label class="text-xs font-semibold text-stone-500">Shortcode</label><input name="mpesa_shortcode" value="{{ old('mpesa_shortcode', $settings['mpesa_shortcode'] ?? '') }}" placeholder="174379" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Consumer Key</label><input name="mpesa_consumer_key" value="{{ old('mpesa_consumer_key', $settings['mpesa_consumer_key'] ?? '') }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 font-mono text-xs"></div>
  <div><label class="text-xs font-semibold text-stone-500">Consumer Secret</label><input type="password" name="mpesa_consumer_secret" autocomplete="new-password" placeholder="{{ ($settings['mpesa_consumer_secret'] ?? '') ? '••••••••••••  (leave blank to keep)' : 'Enter secret' }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 font-mono text-xs"></div>
  <div><label class="text-xs font-semibold text-stone-500">Passkey</label><input type="password" name="mpesa_passkey" autocomplete="new-password" placeholder="{{ ($settings['mpesa_passkey'] ?? '') ? '••••••••••••  (leave blank to keep)' : 'Enter passkey' }}" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 font-mono text-xs"></div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Callback URL</label><input name="mpesa_callback_url" value="{{ old('mpesa_callback_url', $settings['mpesa_callback_url'] ?? '') }}" placeholder="https://yourdomain.com/api/payments/mpesa/callback" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 font-mono text-xs"><p class="text-[11px] text-stone-400 mt-1">Must be a public HTTPS URL — Daraja posts payment confirmations here. For local testing use ngrok (e.g. https://abc123.ngrok.io/api/payments/mpesa/callback).</p></div>

  <div class="sm:col-span-2 text-xs font-bold uppercase tracking-widest text-stone-400 border-b border-stone-100 dark:border-stone-800 pb-2 pt-2">Notifications</div>
  @foreach(['notify_new_order' => 'New order placed', 'notify_new_customer' => 'New customer registered', 'notify_low_stock' => 'Low stock warning', 'notify_payment' => 'M-Pesa payment received'] as $key => $label)
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="{{ $key }}" value="1" @checked(($settings[$key] ?? '1') === '1')> {{ $label }}</label>
  @endforeach

  <div class="sm:col-span-2 text-xs font-bold uppercase tracking-widest text-stone-400 border-b border-stone-100 dark:border-stone-800 pb-2 pt-2">Test M-Pesa
    <span class="normal-case text-[11px] text-stone-400 font-normal">(Sends a real STK push using the config above — enter a small amount)</span>
  </div>
  <div class="sm:col-span-2">
    <form action="{{ route('admin.settings.mpesa-test') }}" method="POST" class="flex flex-wrap gap-2 items-end">@csrf
      <div><label class="text-xs font-semibold text-stone-500">Phone</label><input name="phone" required placeholder="07XXXXXXXX" class="mt-1 block border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm w-44"></div>
      <div><label class="text-xs font-semibold text-stone-500">Amount (KES)</label><input type="number" step="0.01" min="1" name="amount" required value="1" class="mt-1 block border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm w-28"></div>
      <button class="flex items-center gap-2 bg-leaf-500 text-white font-bold rounded-full px-5 py-2 hover:bg-leaf-600"><i data-lucide="send" class="w-4 h-4"></i>Send test STK</button>
    </form>
    <p class="text-[11px] text-stone-400 mt-2">Save your credentials first — the test uses saved settings.</p>
  </div>

  <div class="sm:col-span-2 flex gap-3 pt-2 border-t border-stone-100 dark:border-stone-800"><button class="flex items-center gap-2 bg-leaf-500 text-white font-bold rounded-full px-6 py-2 hover:bg-leaf-600"><i data-lucide="save" class="w-4 h-4"></i>Save settings</button></div>
</form>
@endsection
