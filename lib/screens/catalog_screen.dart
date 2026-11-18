import 'package:flutter/material.dart';
import '../api.dart';
import 'home_screen.dart';

class CatalogScreen extends StatefulWidget {
  final String? category;
  final String title;
  const CatalogScreen({super.key, this.category, this.title = 'Shop'});

  @override
  State<CatalogScreen> createState() => _CatalogScreenState();
}

class _CatalogScreenState extends State<CatalogScreen> {
  List<dynamic> _products = [];
  bool _loading = true;
  String _query = '';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final params = <String, String>{};
      if (widget.category != null) params['category'] = widget.category!;
      if (_query.isNotEmpty) params['q'] = _query;
      final qs = params.entries.map((e) => '${e.key}=${Uri.encodeComponent(e.value)}').join('&');
      final res = await Api.get('/products${qs.isNotEmpty ? '?$qs' : ''}');
      setState(() { _products = res['data'] ?? []; _loading = false; });
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final body = Column(children: [
      Padding(
        padding: const EdgeInsets.fromLTRB(12, 12, 12, 0),
        child: TextField(
          decoration: InputDecoration(
            hintText: 'Search products...',
            prefixIcon: const Icon(Icons.search),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(32)),
            filled: true, fillColor: Colors.white, isDense: true,
          ),
          onSubmitted: (v) { _query = v; _load(); },
        ),
      ),
      Expanded(
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _products.isEmpty
                ? const Center(child: Text('No products found'))
                : GridView.builder(
                    padding: const EdgeInsets.all(12),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 2, childAspectRatio: 0.72, crossAxisSpacing: 12, mainAxisSpacing: 12),
                    itemCount: _products.length,
                    itemBuilder: (_, i) => ProductCard(product: _products[i]),
                  ),
      ),
    ]);

    if (widget.category != null) {
      return Scaffold(appBar: AppBar(title: Text(widget.title)), body: body);
    }
    return body;
  }
}
