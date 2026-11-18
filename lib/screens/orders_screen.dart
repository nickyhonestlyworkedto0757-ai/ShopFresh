import 'package:flutter/material.dart';
import '../api.dart';
import '../cart.dart';
import 'login_screen.dart';
import 'preferences_screen.dart';

const deepGreen = Color(0xFF1B8A4C);

class OrdersScreen extends StatefulWidget {
  const OrdersScreen({super.key});
  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen> {
  List<dynamic>? _orders;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    if (!await Api.isLoggedIn()) {
      if (mounted) setState(() => _orders = null);
      return;
    }
    try {
      final res = await Api.get('/orders', auth: true);
      if (res is Map && res['message'] == 'Unauthenticated.') {
        setState(() => _orders = null);
      } else {
        setState(() => _orders = res is List ? res : []);
      }
    } catch (_) {
      setState(() => _orders = []);
    }
  }

  Future<void> _logout() async {
    await Api.logout();
    cart.clear();
    if (mounted) setState(() => _orders = null);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My orders'),
        actions: [
          IconButton(icon: const Icon(Icons.notifications_none), onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PreferencesScreen())), tooltip: 'Notification preferences'),
          IconButton(icon: const Icon(Icons.refresh), onPressed: _load, tooltip: 'Refresh'),
          IconButton(icon: const Icon(Icons.logout), onPressed: _logout, tooltip: 'Log out'),
        ],
      ),
      body: _orders == null
          ? Center(child: FilledButton.icon(
              onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen())).then((_) => _load()),
              icon: const Icon(Icons.login),
              label: const Text('Log in to view orders'),
            ))
          : _orders!.isEmpty
              ? const Center(child: Text('No orders yet'))
              : ListView.builder(
                  padding: const EdgeInsets.all(12),
                  itemCount: _orders!.length,
                  itemBuilder: (_, i) {
                    final o = _orders![i];
                    return Card(
                      child: ListTile(
                        leading: const Icon(Icons.local_shipping, color: deepGreen),
                        title: Text(o['order_number'], style: const TextStyle(fontWeight: FontWeight.bold)),
                        subtitle: Text('${o['status']} · ${o['payment_method'].toString().toUpperCase()}'),
                        trailing: Text('KES ${o['total']}', style: const TextStyle(fontWeight: FontWeight.w800)),
                      ),
                    );
                  },
                ),
    );
  }
}
