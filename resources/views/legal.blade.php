@extends('layouts.app')
@section('title', 'Legal & Help — ' . $storeName)
@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
  <h1 class="font-display text-3xl font-bold">Legal &amp; Help Center</h1>
  <p class="text-sm text-stone-500 mt-1">Everything you need to know about ordering, delivery, returns and your data.</p>

  <section id="about" class="mt-8">
    <h2 class="text-xl font-bold flex items-center gap-2"><i data-lucide="store" class="w-6 h-6 text-brand-500"></i>About us</h2>
    <p class="mt-2 text-stone-600 leading-relaxed">{{ $storeName }} delivers fresh groceries, fruits and pantry staples to your door. We source directly from farms and trusted suppliers, and we process every order with the same care you would expect from your neighbourhood market — just faster and more convenient.</p>
  </section>

  <section id="how-to-order" class="mt-8">
    <h2 class="text-xl font-bold flex items-center gap-2"><i data-lucide="shopping-cart" class="w-6 h-6 text-brand-500"></i>How to order</h2>
    <ol class="mt-2 text-stone-600 leading-relaxed list-decimal list-inside space-y-1">
      <li>Create an account or log in.</li>
      <li>Browse products or search by name.</li>
      <li>Add items to your cart and go to checkout.</li>
      <li>Enter your delivery address and choose a delivery zone.</li>
      <li>Pay online (M-Pesa or card) or choose Cash on Delivery.</li>
      <li>We deliver and you receive a confirmation.</li>
    </ol>
  </section>

  <section id="delivery" class="mt-8">
    <h2 class="text-xl font-bold flex items-center gap-2"><i data-lucide="truck" class="w-6 h-6 text-brand-500"></i>Shipping &amp; delivery</h2>
    <p class="mt-2 text-stone-600 leading-relaxed">Same-day delivery is available in supported areas, with delivery fees shown at checkout per zone. Orders placed before the day's cut-off are delivered the same day; later orders arrive the next business day. If we cannot reach you, the order is returned to our store within 24 hours and may incur a redelivery fee.</p>
  </section>

  <section id="returns" class="mt-8">
    <h2 class="text-xl font-bold flex items-center gap-2"><i data-lucide="undo-2" class="w-6 h-6 text-brand-500"></i>Returns &amp; refunds policy</h2>
    <ul class="mt-2 text-stone-600 leading-relaxed list-disc list-inside space-y-1">
      <li>Report damaged or incorrect items within 24 hours of delivery with your order number and photos.</li>
      <li>Perishables that have been opened or tampered with cannot be returned.</li>
      <li>Approved refunds are returned to the original payment method within 3 business days.</li>
      <li>Cash on Delivery orders are refunded via M-Pesa to the phone number on the order.</li>
    </ul>
  </section>

  <section id="privacy" class="mt-8">
    <h2 class="text-xl font-bold flex items-center gap-2"><i data-lucide="shield-check" class="w-6 h-6 text-brand-500"></i>Privacy notice</h2>
    <p class="mt-2 text-stone-600 leading-relaxed">We collect only the data needed to process your orders: name, email, phone number, delivery address and payment references. We never store your M-Pesa PIN or card details. Your data is not sold to third parties. Newsletter subscribers may unsubscribe at any time — every email contains an opt-out note. To request deletion of your account and data, contact us at {{ \App\Support\Settings::get('store_email') ?: 'support@shopfresh.test' }}.</p>
  </section>

  <section id="terms" class="mt-8">
    <h2 class="text-xl font-bold flex items-center gap-2"><i data-lucide="file-text" class="w-6 h-6 text-brand-500"></i>Terms and conditions</h2>
    <ul class="mt-2 text-stone-600 leading-relaxed list-disc list-inside space-y-1">
      <li>Prices are in {{ \App\Support\Settings::get('currency', 'KES') }} and may change without notice. Promotions are valid while stocks last.</li>
      <li>Product images are for illustration; actual appearance may vary slightly.</li>
      <li>We may cancel orders affected by obvious pricing errors or stock shortages, with a full refund.</li>
      <li>By using this site you accept these terms, our delivery policy and our privacy notice.</li>
    </ul>
  </section>
</div>
@endsection
