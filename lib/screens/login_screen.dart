import 'package:flutter/material.dart';
import '../api.dart';

const brandRed = Color(0xFFE4572E);
const deepGreen = Color(0xFF1B8A4C);

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _email = TextEditingController();
  final _password = TextEditingController();
  bool _busy = false;

  Future<void> _login() async {
    setState(() => _busy = true);
    final ok = await Api.login(_email.text.trim(), _password.text);
    setState(() => _busy = false);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ok ? 'Logged in!' : 'Invalid credentials')));
    if (ok) Navigator.pop(context, true);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Login')),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(children: [
          const CircleAvatar(radius: 40, backgroundColor: brandRed, child: Icon(Icons.shopping_basket, color: Colors.white, size: 40)),
          const SizedBox(height: 24),
          TextField(controller: _email, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(labelText: 'Email', border: OutlineInputBorder(), prefixIcon: Icon(Icons.email_outlined))),
          const SizedBox(height: 12),
          TextField(controller: _password, obscureText: true, decoration: const InputDecoration(labelText: 'Password', border: OutlineInputBorder(), prefixIcon: Icon(Icons.lock_outline))),
          const SizedBox(height: 20),
          SizedBox(width: double.infinity, child: FilledButton.icon(
            style: FilledButton.styleFrom(backgroundColor: deepGreen, padding: const EdgeInsets.all(16)),
            onPressed: _busy ? null : _login,
            icon: _busy ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Icon(Icons.login),
            label: const Text('Log in'),
          )),
        ]),
      ),
    );
  }
}
