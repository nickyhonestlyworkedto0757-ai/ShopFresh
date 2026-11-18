import 'package:flutter/material.dart';
import '../api.dart';

const deepGreen = Color(0xFF1B8A4C);

class PreferencesScreen extends StatefulWidget {
  const PreferencesScreen({super.key});
  @override
  State<PreferencesScreen> createState() => _PreferencesScreenState();
}

class _PreferencesScreenState extends State<PreferencesScreen> {
  Map<String, dynamic>? _prefs;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final res = await Api.get('/me/preferences', auth: true);
    if (res is Map) setState(() => _prefs = Map<String, dynamic>.from(res));
  }

  Future<void> _save() async {
    if (_prefs == null) return;
    setState(() => _saving = true);
    await Api.put('/me/preferences', {
      'email_order_updates': _prefs!['email_order_updates'] ?? true,
      'sms_order_updates': _prefs!['sms_order_updates'] ?? false,
      'email_new_products': _prefs!['email_new_products'] ?? true,
      'sms_new_products': _prefs!['sms_new_products'] ?? false,
      'email_promotions': _prefs!['email_promotions'] ?? true,
      'sms_promotions': _prefs!['sms_promotions'] ?? false,
    }, auth: true);
    if (mounted) {
      setState(() => _saving = false);
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Preferences saved')));
    }
  }

  Widget _toggle(String key, String title, String subtitle, IconData icon) {
    return SwitchListTile(
      value: (_prefs?[key] ?? false) as bool,
      onChanged: (v) => setState(() => _prefs?[key] = v),
      title: Text(title, style: const TextStyle(fontWeight: FontWeight.w600)),
      subtitle: Text(subtitle, style: const TextStyle(fontSize: 12)),
      secondary: Icon(icon, color: deepGreen),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Notification preferences')),
      body: _prefs == null
          ? const Center(child: CircularProgressIndicator())
          : ListView(padding: const EdgeInsets.all(8), children: [
              const Padding(padding: EdgeInsets.symmetric(horizontal: 12, vertical: 8), child: Text('ORDER UPDATES', style: TextStyle(fontSize: 11, letterSpacing: 1.5, color: Colors.black45))),
              _toggle('email_order_updates', 'Email order updates', 'Receipts, payment and delivery status', Icons.email_outlined),
              _toggle('sms_order_updates', 'SMS order updates', 'Delivery SMS alerts', Icons.sms_outlined),
              const Padding(padding: EdgeInsets.symmetric(horizontal: 12, vertical: 8), child: Text('PRODUCT & OFFER ALERTS', style: TextStyle(fontSize: 11, letterSpacing: 1.5, color: Colors.black45))),
              _toggle('email_new_products', 'New products by email', 'When new items arrive', Icons.new_releases_outlined),
              _toggle('sms_new_products', 'New products by SMS', '', Icons.new_releases),
              _toggle('email_promotions', 'Promotions by email', 'Discounts and offers', Icons.local_offer_outlined),
              _toggle('sms_promotions', 'Promotions by SMS', '', Icons.local_offer),
              const SizedBox(height: 16),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 12),
                child: FilledButton.icon(
                  style: FilledButton.styleFrom(backgroundColor: deepGreen, padding: const EdgeInsets.all(16)),
                  onPressed: _saving ? null : _save,
                  icon: _saving ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Icon(Icons.save),
                  label: const Text('Save preferences'),
                ),
              ),
            ]),
    );
  }
}
