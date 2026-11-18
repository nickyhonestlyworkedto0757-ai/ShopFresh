import 'package:flutter/material.dart';
import '../cart.dart';
import 'checkout_screen.dart';

const deepGreen = Color(0xFF1B8A4C);
const brandRed = Color(0xFFE4572E);

class CartScreen extends StatelessWidget {
  const CartScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: cart,
      builder: (context, _) {
        final items = cart.items;
        return Scaffold(
          appBar: AppBar(title: const Text('Your cart')),
          body: items.isEmpty
              ? const Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                  Icon(Icons.shopping_basket_outlined, size: 64, color: Colors.black26),
                  SizedBox(height: 12),
                  Text('Your cart is empty'),
                ]))
              : Column(children: [
                  Expanded(
                    child: ListView.builder(
                      padding: const EdgeInsets.all(12),
                      itemCount: items.length,
                      itemBuilder: (_, i) {
                        final item = items[i];
                        return Card(
                          child: ListTile(
                            leading: ClipRRect(
                              borderRadius: BorderRadius.circular(12),
                              child: item.imageUrl != null
                                  ? Image.network(item.imageUrl!, width: 56, height: 56, fit: BoxFit.cover,
                                      errorBuilder: (_, __, ___) => const SizedBox(width: 56, height: 56, child: ColoredBox(color: Color(0xFFEDFBF1), child: Icon(Icons.shopping_basket, color: deepGreen))))
                                  : const SizedBox(width: 56, height: 56, child: ColoredBox(color: Color(0xFFEDFBF1), child: Icon(Icons.shopping_basket, color: deepGreen))),
                            ),
                            title: Text(item.name),
                            subtitle: Text('KES ${item.price.toStringAsFixed(0)}'),
                            trailing: Row(mainAxisSize: MainAxisSize.min, children: [
                              IconButton(icon: const Icon(Icons.remove_circle_outline), onPressed: () => cart.setQty(item.productId, item.qty - 1)),
                              Text('${item.qty}', style: const TextStyle(fontWeight: FontWeight.bold)),
                              IconButton(icon: const Icon(Icons.add_circle_outline), onPressed: () => cart.setQty(item.productId, item.qty + 1)),
                            ]),
                          ),
                        );
                      },
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(children: [
                      Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                        const Text('Total', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                        Text('KES ${cart.subtotal.toStringAsFixed(0)}', style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
                      ]),
                      const SizedBox(height: 12),
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton.icon(
                          style: FilledButton.styleFrom(backgroundColor: brandRed, padding: const EdgeInsets.all(16)),
                          onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const CheckoutScreen())),
                          icon: const Icon(Icons.arrow_forward),
                          label: const Text('Proceed to checkout'),
                        ),
                      ),
                    ]),
                  ),
                ]),
        );
      },
    );
  }
}
