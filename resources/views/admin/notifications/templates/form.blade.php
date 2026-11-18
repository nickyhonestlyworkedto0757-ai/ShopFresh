@extends('admin.layout')
@section('title', ($template->exists ? 'Edit' : 'New') . ' template — Admin')
@section('content')
<h1 class="text-2xl font-bold">{{ $template->exists ? 'Edit' : 'New' }} template</h1>
<form action="{{ $template->exists ? route('admin.notifications.templates.update', $template) : route('admin.notifications.templates.store') }}" method="POST" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6 mt-4 grid sm:grid-cols-2 gap-4 max-w-2xl">@csrf
  @if($template->exists) @method('PUT') @endif
  <div><label class="text-xs font-semibold text-stone-500">Name *</label><input name="name" required value="{{ old('name', $template->name) }}" placeholder="New product alert" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div><label class="text-xs font-semibold text-stone-500">Key *</label><input name="key" required value="{{ old('key', $template->key) }}" placeholder="new_product_alert" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2 font-mono text-sm"></div>
  <div><label class="text-xs font-semibold text-stone-500">Channel</label><select name="channel" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2">@foreach(['both' => 'Email + SMS', 'email' => 'Email only', 'sms' => 'SMS only'] as $v => $l)<option value="{{ $v }}" @selected(old('channel', $template->channel) === $v)>{{ $l }}</option>@endforeach</select></div>
  <div class="flex items-end"><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active ?? true))> Active</label></div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Subject (email)</label><input name="subject" value="{{ old('subject', $template->subject) }}" placeholder="[name] — new on the shelves!" class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2"></div>
  <div class="sm:col-span-2"><label class="text-xs font-semibold text-stone-500">Body *</label><textarea name="body" rows="5" required class="mt-1 w-full border border-stone-200 dark:border-stone-700 bg-transparent rounded-xl px-3 py-2">{{ old('body', $template->body) }}</textarea><p class="text-[11px] text-stone-400 mt-1">Placeholders: @{{name}}, @{{order_number}}, @{{product_name}}, @{{total}}, @{{link}}</p></div>
  <div class="sm:col-span-2 flex gap-3"><button class="flex items-center gap-2 bg-leaf-500 text-white font-bold rounded-full px-6 py-2 hover:bg-leaf-600"><i data-lucide="save" class="w-4 h-4"></i>Save template</button><a href="{{ route('admin.notifications.templates') }}" class="px-6 py-2 text-stone-500">Cancel</a></div>
</form>
@endsection
