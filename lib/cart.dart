import 'package:flutter/material.dart';

class CartItem {
  final int productId;
  final String name;
  final String? imageUrl;
  final double price;
  int qty;
  CartItem({required this.productId, required this.name, required this.imageUrl, required this.price, this.qty = 1});
}

class CartModel extends ChangeNotifier {
  final Map<int, CartItem> _items = {};

  List<CartItem> get items => _items.values.toList();
  int get count => _items.values.fold(0, (s, i) => s + i.qty);
  double get subtotal => _items.values.fold(0, (s, i) => s + i.price * i.qty);

  void add(Map<String, dynamic> product) {
    final id = product['id'] as int;
    final price = double.tryParse(product['sale_price']?.toString() ?? '') ??
        double.tryParse(product['price'].toString()) ?? 0;
    if (_items.containsKey(id)) {
      _items[id]!.qty++;
    } else {
      _items[id] = CartItem(productId: id, name: product['name'], imageUrl: product['image_url'] as String?, price: price);
    }
    notifyListeners();
  }

  void setQty(int productId, int qty) {
    if (qty <= 0) {
      _items.remove(productId);
    } else {
      _items[productId]?.qty = qty;
    }
    notifyListeners();
  }

  void clear() {
    _items.clear();
    notifyListeners();
  }
}

final cart = CartModel();
