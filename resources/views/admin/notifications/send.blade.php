@extends('admin.layout')
@section('title', 'Send Notification — Admin')
@section('content')
<a href="{{ route('admin.notifications.index') }}" class="inline-flex items-center gap-1 text-sm text-brand-600 font-semibold"><i data-lucide="arrow-left" class="w-4 h-4"></i>Notifications</a>
<div class="flex items-center justify-between flex-wrap gap-2 mt-2">
  <h1 class="text-2xl font-bold">Send notification</h1>
  <p class="text-[11px] text-stone-400">Customers receive messages only if they opted in for that type. Product &amp; promo blasts respect individual preferences.</p>
</div>

<form action="{{ route('admin.notifications.send.post') }}" method="POST" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6 mt-4 grid gap-5">@csrf
  <div>
    <label class="text-xs font-semibold text-stone-500">Audience *</label>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-2 mt-1 text-sm">
      @foreach(['all' => ['users', 'All customers'], 'selected' => ['user-check', 'Selected customers'], 'promotions' => ['megaphone', 'Promotional subscribers'], 'new_products' => ['sparkles', 'New-product subscribers']] as $val => [$icon, $label])
        <label class="border border-stone-200 dark:border-stone-700 rounded-2xl p-3 cursor-pointer flex flex-col items-center gap-1 has-[:checked]:border-leaf-500 has-[:checked]:bg-leaf-50 dark:has-[:checked]:bg-leaf-700/20">
          <input type="radio" name="audience" value="{{ $val }}" class="sr-only" {{ $val === 'all' ? 'checked' : '' }}>
          <i data-lucide="{{ $icon }}" class="w-6 h-6"></i><span class="text-xs font-semibold text-center">{{ $label }}</span>
        </label>
      @endforeach
    </div>
  </div>

  <div id="selected-block" class="hidden">
    <label class="text-xs font-semibold text-stone-500">Select customers *</label>
    <input id="customer-filter" placeholder="Type to filter by name, email or phone..." class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 text-sm">
    <div class="max-h-64 overflow-auto border border-stone-200 dark:border-stone-700 rounded-2xl mt-2">
      <table class="w-full text-sm" id="customer-table">
        @foreach($customers as $customer)
          <tr class="border-t border-stone-100 dark:border-stone-800 customer-row"><td class="p-2 w-8"><input type="checkbox" name="selected_customers[]" value="{{ $customer->id }}"></td><td class="p-2"><div class="font-medium">{{ $customer->name }}</div><div class="text-[11px] text-stone-400">{{ $customer->email }} · {{ $customer->phone ?? 'no phone' }}</div></td></tr>
        @endforeach
      </table>
    </div>
    <div class="mt-2">{{ count($customers) }} customers shown. Select the ones to notify above.</div>
  </div>

  <div class="grid sm:grid-cols-2 gap-4">
    <div><label class="text-xs font-semibold text-stone-500">Channel *</label><select name="channel" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"><option value="email">Email only</option><option value="sms">SMS only</option><option value="both">Email + SMS</option></select></div>
    <div><label class="text-xs font-semibold text-stone-500">Type *</label><select name="type" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"><option value="custom">Custom / announcement</option><option value="promotion">Promotional offer</option><option value="new_product">New product</option></select></div>
  </div>

  <div><label class="text-xs font-semibold text-stone-500">Subject <span class="text-stone-400 font-normal">(email)</span></label><input name="subject" placeholder="e.g. Fresh tomatoes restocked!" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500">Message *</label><textarea name="body" rows="5" required placeholder="Hi there, fresh tomatoes are back in stock!" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></textarea>
    @if($templates->isNotEmpty())<div class="mt-2 flex flex-wrap gap-2"><span class="text-[11px] text-stone-500 self-center">Templates:</span>@foreach($templates as $template)<button type="button" class="template-btn text-[11px] border border-leaf-500 text-leaf-700 dark:text-leaf-100 rounded-full px-3 py-1" data-subject="{{ $template->subject }}" data-body="{{ $template->body }}">{{ $template->name }}</button>@endforeach</div>@endif
  </div>

  <div class="flex items-center justify-between flex-wrap gap-3">
    <p class="text-[11px] text-stone-400">SMS costs apply to SMS sends (Africa's Talking charges ~KES 0.5/message). Sent messages will appear in History.</p>
    <button class="flex items-center gap-2 bg-leaf-500 text-white font-bold rounded-full px-8 py-3 hover:bg-leaf-600"><i data-lucide="send" class="w-5 h-5"></i>Send now</button>
  </div>
</form>
<script>
  const radio = (n) => document.querySelector(`input[name=audience][value=${n}]`);
  const selectedBlock = document.getElementById('selected-block');
  const toggle = () => selectedBlock.classList.toggle('hidden', !radio('selected').checked);
  document.querySelectorAll('input[name=audience]').forEach(r => r.addEventListener('change', toggle));
  toggle();
  document.querySelectorAll('.template-btn').forEach(btn => btn.addEventListener('click', () => {
    document.querySelector('input[name=subject]').value = btn.dataset.subject || '';
    document.querySelector('textarea[name=body]').value = btn.dataset.body || '';
  }));
  document.getElementById('customer-filter')?.addEventListener('input', (e) => {
    const needle = e.target.value.toLowerCase();
    document.querySelectorAll('.customer-row').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(needle) ? '' : 'none';
    });
  });
</script>
@endsection
