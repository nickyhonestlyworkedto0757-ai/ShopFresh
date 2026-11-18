import 'package:flutter/material.dart';
import '../api.dart';
import '../cart.dart';

const leafGreen = Color(0xFF22A45D);
const deepGreen = Color(0xFF1B8A4C);
const brandRed = Color(0xFFE4572E);

enum _PayState { idle, promptSent, polling, success, failed }

class MpesaPaymentScreen extends StatefulWidget {
  final int orderId;
  final String orderNumber;
  final double total;
  final String currency;
  const MpesaPaymentScreen({super.key, required this.orderId, required this.orderNumber, required this.total, this.currency = 'KES'});

  @override
  State<MpesaPaymentScreen> createState() => _MpesaPaymentScreenState();
}

class _MpesaPaymentScreenState extends State<MpesaPaymentScreen> {
  final _phone = TextEditingController();
  _PayState _state = _PayState.idle;
  String _message = '';
  String? _receipt;

  Future<void> _pay() async {
    final raw = _phone.text.trim();
    if (raw.isEmpty) {
      _snack('Enter the M-Pesa phone number');
      return;
    }
    setState(() => _state = _PayState.promptSent);
    final res = await Api.post('/payments/mpesa/stk-push', {'order_id': widget.orderId, 'phone': raw}, auth: true);
    if (res is Map && res['ok'] == true) {
      setState(() {
        _state = _PayState.polling;
        _message = res['message'] ?? 'STK push sent. Enter your M-Pesa PIN.';
      });
      _poll();
    } else {
      setState(() {
        _state = _PayState.failed;
        _message = res is Map ? (res['message'] ?? 'Could not send STK push.').toString() : 'Could not send STK push.';
      });
    }
  }

  Future<void> _poll() async {
    for (var attempt = 0; attempt < 40; attempt++) {
      await Future.delayed(const Duration(seconds: 3));
      if (!mounted) return;
      try {
        final res = await Api.get('/orders/${widget.orderId}/payment-status', auth: true);
        if (res is Map) {
          final paymentStatus = res['payment_status'];
          final record = res['payment_record_status'];
          if (paymentStatus == 'paid') {
            setState(() {
              _state = _PayState.success;
              _receipt = res['receipt_number'] as String?;
            });
            return;
          }
          if (record == 'failed' || record == 'cancelled' || record == 'timeout') {
            setState(() {
              _state = _PayState.failed;
              _message = res['result_desc'] as String? ?? 'The payment was not completed.';
            });
            return;
          }
        }
      } catch (_) {
        // keep polling — network hiccup is fine
      }
    }
    if (mounted && _state == _PayState.polling) {
      setState(() {
        _state = _PayState.failed;
        _message = 'Timed out waiting for M-Pesa confirmation. If you completed the payment, it will reflect shortly.';
      });
    }
  }

  void _snack(String msg) => ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('M-Pesa payment')),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(color: const Color(0xFFEDFBF1), borderRadius: BorderRadius.circular(20)),
            child: Column(children: [
              const Icon(Icons.smartphone, size: 40, color: deepGreen),
              const SizedBox(height: 6),
              Text('Order ${widget.orderNumber}', style: const TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 2),
              Text('${widget.currency} ${widget.total.toStringAsFixed(2)}', style: const TextStyle(fontSize: 28, fontWeight: FontWeight.w900, color: brandRed)),
            ]),
          ),
          const SizedBox(height: 16),
          if (_state == _PayState.idle || _state == _PayState.failed || _state == _PayState.promptSent) ...[
            TextField(
              controller: _phone,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(
                labelText: 'M-Pesa phone number',
                hintText: '07XX XXX XXX  ·  01XX XXX XXX  ·  2547XXXXXXXX',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.phone_android),
              ),
            ),
            const SizedBox(height: 16),
          ],
          if (_state == _PayState.polling) ...[
            const SizedBox(height: 12),
            const Center(child: CircularProgressIndicator(color: leafGreen)),
            const SizedBox(height: 12),
            Text(_message, textAlign: TextAlign.center, style: const TextStyle(color: Colors.black54)),
            const SizedBox(height: 6),
            const Text('Waiting for confirmation from M-Pesa...', textAlign: TextAlign.center, style: TextStyle(fontSize: 12, color: Colors.black38)),
          ],
          if (_state == _PayState.failed) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(color: const Color(0xFFFDECEA), borderRadius: BorderRadius.circular(16)),
              child: Row(children: [
                const Icon(Icons.error_outline, color: brandRed),
                const SizedBox(width: 8),
                Expanded(child: Text(_message, style: const TextStyle(color: brandRed))),
              ]),
            ),
            const SizedBox(height: 16),
          ],
          if (_state == _PayState.success) ...[
            const SizedBox(height: 8),
            const Icon(Icons.check_circle, size: 72, color: leafGreen),
            const SizedBox(height: 12),
            const Text('Payment successful!', textAlign: TextAlign.center, style: TextStyle(fontSize: 24, fontWeight: FontWeight.w900)),
            if (_receipt != null) Text('Receipt: $_receipt', textAlign: TextAlign.center, style: const TextStyle(fontFamily: 'monospace', color: Colors.black54)),
            const SizedBox(height: 16),
            FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: deepGreen, padding: const EdgeInsets.all(16)),
              onPressed: () => Navigator.of(context).popUntil((r) => r.isFirst),
              icon: const Icon(Icons.home),
              label: const Text('Back to shop'),
            ),
          ],
          const Spacer(),
          if (_state != _PayState.polling && _state != _PayState.success)
            FilledButton.icon(
              style: FilledButton.styleFrom(backgroundColor: deepGreen, padding: const EdgeInsets.all(18)),
              onPressed: _state == _PayState.promptSent ? null : _pay,
              icon: const Icon(Icons.lock_open),
              label: Text(_state == _PayState.failed ? 'Retry payment' : 'Pay now'),
            ),
        ]),
      ),
    );
  }
}
