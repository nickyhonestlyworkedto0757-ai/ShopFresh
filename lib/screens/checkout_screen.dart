import 'package:flutter/material.dart';
import '../api.dart';
import '../cart.dart';
import 'login_screen.dart';
import 'mpesa_payment_screen.dart';

const leafGreen = Color(0xFF22A45D);
const deepGreen = Color(0xFF1B8A4C);
const brandRed = Color(0xFFE4572E);

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});
  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _address = TextEditingController();
  final _city = TextEditingController(text: 'Nairobi');
  final _coupon = TextEditingController();
  String _payment = 'cod';
  bool _busy = false;
  List<dynamic> _zones = [];
  int? _zoneId;

  @override
  void initState() {
    super.initState();
    _loadZones();
  }

  Future<void> _loadZones() async {
    try {
      final res = await Api.get('/delivery-zones');
      if (res is List && mounted) {
        setState(() => _zones = res);
      }
    } catch (_) {}
  }

  double get _deliveryFee {
    final zone = _zones.cast<Map<String, dynamic>?>().firstWhere((z) => z?['id'] == _zoneId, orElse: () => null);
    return zone == null ? 0 : double.tryParse(zone['fee'].toString()) ?? 0;
  }

  Future<void> _placeOrder() async {
    if (_name.text.isEmpty || _email.text.isEmpty || _phone.text.isEmpty || _address.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please fill in all required fields')));
      return;
    }
    if (!await Api.isLoggedIn()) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please log in to place your order')));
      await Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
      return;
    }
    setState(() => _busy = true);
    final res = await Api.post('/orders', {
      'customer_name': _name.text,
      'customer_email': _email.text,
      'customer_phone': _phone.text,
      'shipping_address': _address.text,
      'city': _city.text,
      'payment_method': _payment,
      if (_zoneId != null) 'delivery_zone_id': _zoneId,
      if (_coupon.text.isNotEmpty) 'coupon_code': _coupon.text.toUpperCase(),
      'items': cart.items.map((i) => {'product_id': i.productId, 'quantity': i.qty}).toList(),
    }, auth: true);
    setState(() => _busy = false);
    if (res is Map && res['order_number'] != null) {
      final orderId = res['id'] as int;
      final orderNumber = res['order_number'] as String;
      final total = double.tryParse(res['total'].toString()) ?? cart.subtotal;
      final method = _payment;
      cart.clear();
      if (!mounted) return;
      if (method == 'mpesa') {
        Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => MpesaPaymentScreen(orderId: orderId, orderNumber: orderNumber, total: total)));
        return;
      }
      showDialog(
        context: context,
        builder: (_) => AlertDialog(
          icon: const Icon(Icons.check_circle, color: leafGreen, size: 48),
          title: const Text('Order placed!'),
          content: Text('Order $orderNumber confirmed. Total: KES ${total.toStringAsFixed(0)}${method == 'cod' ? ' — pay on delivery.' : ''}'),
          actions: [TextButton(onPressed: () { Navigator.of(context)..pop()..pop(); }, child: const Text('Done'))],
        ),
      );
    } else {
      final msg = res is Map ? (res['message'] ?? res.toString()) : 'Something went wrong';
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg.toString())));
    }
  }

  @override
  Widget build(BuildContext context) {
    final field = (TextEditingController c, String label, {TextInputType? type}) => Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextField(controller: c, keyboardType: type, decoration: InputDecoration(labelText: label, border: const OutlineInputBorder())),
    );
    final paymentOptions = const {
      'cod': (Icons.payments_outlined, 'Cash on Delivery'),
      'mpesa': (Icons.smartphone, 'M-Pesa'),
      'card': (Icons.credit_card, 'Card'),
    };
    return Scaffold(
      appBar: AppBar(title: const Text('Checkout')),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        field(_name, 'Full name'),
        field(_email, 'Email', type: TextInputType.emailAddress),
        field(_phone, 'Phone', type: TextInputType.phone),
        field(_address, 'Delivery address'),
        field(_city, 'City'),
        field(_coupon, 'Coupon code (optional)'),
        if (_zones.isNotEmpty) ...[
          const Text('Delivery zone', style: TextStyle(fontWeight: FontWeight.bold)),
          const SizedBox(height: 4),
          DropdownButtonFormField<int?>(
            value: _zoneId,
            decoration: const InputDecoration(border: OutlineInputBorder(), isDense: true),
            hint: const Text('Standard delivery'),
            items: [
              const DropdownMenuItem(value: null, child: Text('Standard delivery')),
              for (final z in _zones)
                DropdownMenuItem(value: z['id'] as int, child: Text('${z['name']} — KES ${z['fee']}')),
            ],
            onChanged: (v) => setState(() => _zoneId = v),
          ),
          const SizedBox(height: 16),
        ],
        const Text('Payment method', style: TextStyle(fontWeight: FontWeight.bold)),
        const SizedBox(height: 8),
        Wrap(spacing: 8, children: [
          for (final entry in paymentOptions.entries)
            ChoiceChip(
              avatar: Icon(entry.value.$1, size: 18),
              label: Text(entry.value.$2),
              selected: _payment == entry.key,
              onSelected: (_) => setState(() => _payment = entry.key),
            ),
        ]),
        const SizedBox(height: 16),
        Text('Delivery: KES ${_deliveryFee.toStringAsFixed(0)}', style: const TextStyle(fontSize: 13, color: Colors.black54)),
        Text('Total: KES ${(cart.subtotal + _deliveryFee).toStringAsFixed(0)}', style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
        const SizedBox(height: 12),
        FilledButton.icon(
          style: FilledButton.styleFrom(backgroundColor: deepGreen, padding: const EdgeInsets.all(16)),
          onPressed: _busy ? null : _placeOrder,
          icon: _busy
              ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
              : const Icon(Icons.lock),
          label: const Text('Place order'),
        ),
      ]),
    );
  }
}
