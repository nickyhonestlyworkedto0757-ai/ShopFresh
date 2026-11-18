import 'package:flutter/material.dart';
import '../api.dart';
import '../cart.dart';
import 'login_screen.dart';
import 'catalog_screen.dart';

const brandRed = Color(0xFFE4572E);
const leafGreen = Color(0xFF22A45D);
const deepGreen = Color(0xFF1B8A4C);
const sunYellow = Color(0xFFFBBF24);

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<dynamic> _featured = [];
  List<dynamic> _categories = [];
  List<dynamic> _banners = [];
  String _storeName = 'ShopFresh';
  List<dynamic> _sections = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final products = await Api.get('/products');
      final categories = await Api.get('/categories');
      final banners = await Api.get('/banners');
      final settings = await Api.get('/settings');
      final homepage = await Api.get('/homepage');
      setState(() {
        _featured = (products['data'] as List? ?? []).where((p) => p['is_featured'] == true).toList();
        _categories = categories is List ? categories : [];
        _banners = banners is List ? banners : [];
        _sections = homepage is List ? homepage : [];
        if (settings is Map && settings['store_name'] != null) _storeName = settings['store_name'];
        _loading = false;
      });
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  IconData _categoryIcon(String slug) {
    switch (slug) {
      case 'fruits':
        return Icons.apple;
      case 'vegetables':
        return Icons.eco;
      case 'dairy-and-eggs':
        return Icons.egg_alt;
      case 'bakery':
        return Icons.bakery_dining;
      case 'pantry':
        return Icons.inventory_2;
      case 'beverages':
        return Icons.local_cafe;
      default:
        return Icons.category;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(children: const [
          CircleAvatar(backgroundColor: brandRed, child: Icon(Icons.shopping_basket, color: Colors.white)),
          SizedBox(width: 8),
          Text(_storeName, style: TextStyle(fontWeight: FontWeight.w800)),
        ]),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  if (_banners.isNotEmpty) ...[
                    _BannerSlider(banners: _banners),
                    const SizedBox(height: 16),
                  ] else ...[
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(colors: [Color(0xFFE4572E), Color(0xFFB93A2B)]),
                        borderRadius: BorderRadius.circular(24),
                      ),
                      child: const Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        Chip(backgroundColor: sunYellow, label: Text('FARM FRESH, DAILY', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 10))),
                        SizedBox(height: 8),
                        Text('Freshness delivered\nto your doorstep.', style: TextStyle(color: Colors.white, fontSize: 28, fontWeight: FontWeight.w900, height: 1.1)),
                        SizedBox(height: 6),
                        Text('Crisp vegetables, ripe fruit, pantry staples — same-day.', style: TextStyle(color: Colors.white70)),
                      ]),
                    ),
                  ],
                  for (final entry in _sections)
                    _SectionBlock(entry: entry as Map<String, dynamic>),
                ],
              ),
            ),
    );
  }
}

class _SectionBlock extends StatelessWidget {
  final Map<String, dynamic> entry;
  const _SectionBlock({required this.entry});

  @override
  Widget build(BuildContext context) {
    final section = entry['section'] as Map<String, dynamic>?;
    if (section == null) return const SizedBox.shrink();
    final mode = section['mode'];
    if (mode == 'categories') {
      final cats = (entry['categories'] as List?) ?? [];
      if (cats.isEmpty) return const SizedBox.shrink();
      return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        const SizedBox(height: 16),
        Text(section['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 18)),
        const SizedBox(height: 8),
        SizedBox(
          height: 90,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: cats.length,
            separatorBuilder: (_, __) => const SizedBox(width: 8),
            itemBuilder: (_, i) {
              final c = cats[i];
              return ActionChip(
                avatar: Icon(_iconForSlug(c['slug']?.toString() ?? ''), color: const Color(0xFF1B8A4C)),
                label: Text(c['name'] ?? ''),
                onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => CatalogScreen(category: c['slug'], title: c['name']))),
              );
            },
          ),
        ),
      ]);
    }
    final products = (entry['products'] as List?) ?? [];
    if (products.isEmpty) return const SizedBox.shrink();
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      const SizedBox(height: 16),
      Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
        Expanded(child: Text(section['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 18))),
        TextButton(
          onPressed: () {
            final mode = section['mode'];
            final value = section['value'];
            if (mode == 'category' && value != null) {
              Navigator.push(context, MaterialPageRoute(builder: (_) => CatalogScreen(category: value, title: section['title'] ?? 'Deals')));
            } else {
              Navigator.push(context, MaterialPageRoute(builder: (_) => CatalogScreen(title: section['title'] ?? 'Shop')));
            }
          },
          child: const Row(mainAxisSize: MainAxisSize.min, children: [Text('See all'), Icon(Icons.chevron_right, size: 18)]),
        ),
      ]),
      const SizedBox(height: 8),
      SizedBox(
        height: 240,
        child: ListView.separated(
          scrollDirection: Axis.horizontal,
          itemCount: products.length,
          separatorBuilder: (_, __) => const SizedBox(width: 12),
          itemBuilder: (_, i) => SizedBox(width: 160, child: ProductCard(product: products[i])),
        ),
      ),
    ]);
  }

  static IconData _iconForSlug(String slug) {
    switch (slug) {
      case 'fruits': return Icons.apple;
      case 'vegetables': return Icons.eco;
      case 'dairy-and-eggs': return Icons.egg_alt;
      case 'bakery': return Icons.bakery_dining;
      case 'pantry': return Icons.inventory_2;
      case 'beverages': return Icons.local_cafe;
      case 'laptop': return Icons.laptop_mac;
      default: return Icons.category;
    }
  }
}

class _BannerSlider extends StatefulWidget {
  final List<dynamic> banners;
  const _BannerSlider({required this.banners});

  @override
  State<_BannerSlider> createState() => _BannerSliderState();
}

class _BannerSliderState extends State<_BannerSlider> {
  final _controller = PageController();
  int _index = 0;

  @override
  void initState() {
    super.initState();
    Future.doWhile(() async {
      await Future.delayed(const Duration(seconds: 5));
      if (!mounted || widget.banners.length < 2) return false;
      final next = (_index + 1) % widget.banners.length;
      _controller.animateToPage(next, duration: const Duration(milliseconds: 400), curve: Curves.easeInOut);
      return true;
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(children: [
      SizedBox(
        height: 175,
        child: PageView.builder(
          controller: _controller,
          itemCount: widget.banners.length,
          onPageChanged: (i) => setState(() => _index = i),
          itemBuilder: (_, i) {
            final b = widget.banners[i];
            return ClipRRect(
              borderRadius: BorderRadius.circular(24),
              child: Stack(fit: StackFit.expand, children: [
                Image.network(b['image_url'], fit: BoxFit.cover, width: double.infinity,
                    errorBuilder: (_, __, ___) => const ColoredBox(color: Color(0xFFE4572E))),
                Container(decoration: const BoxDecoration(gradient: LinearGradient(colors: [Colors.black54, Colors.transparent]))),
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisAlignment: MainAxisAlignment.center, children: [
                    if (b['title'] != null && b['title'].toString().isNotEmpty)
                      Text(b['title'], style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w900)),
                    if (b['subtitle'] != null && b['subtitle'].toString().isNotEmpty)
                      Text(b['subtitle'], style: const TextStyle(color: sunYellow, fontWeight: FontWeight.w600)),
                  ]),
                ),
              ]),
            );
          },
        ),
      ),
      const SizedBox(height: 8),
      Row(mainAxisAlignment: MainAxisAlignment.center, children: [
        for (var i = 0; i < widget.banners.length; i++)
          Container(
            margin: const EdgeInsets.symmetric(horizontal: 4),
            width: _index == i ? 16 : 8,
            height: 8,
            decoration: BoxDecoration(color: _index == i ? brandRed : Colors.black26, borderRadius: BorderRadius.circular(4)),
          ),
      ]),
    ]);
  }
}

class ProductCard extends StatelessWidget {
  final Map<String, dynamic> product;
  const ProductCard({super.key, required this.product});

  static Future<void> addToCart(BuildContext context, Map<String, dynamic> product) async {
    if (!await Api.isLoggedIn()) {
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please log in to add items to your cart')));
      Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
      return;
    }
    cart.add(product);
    if (context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('${product['name']} added to cart'), duration: const Duration(seconds: 1)));
    }
  }

  @override
  Widget build(BuildContext context) {
    final sale = product['sale_price'] != null ? double.tryParse(product['sale_price'].toString()) : null;
    final price = double.parse(product['price'].toString());
    final imageUrl = product['image_url'] as String?;
    return Card(
      clipBehavior: Clip.antiAlias,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      child: Column(children: [
        Expanded(
          child: imageUrl != null
              ? Image.network(imageUrl, width: double.infinity, fit: BoxFit.cover, errorBuilder: (_, __, ___) => const ColoredBox(color: Color(0xFFEDFBF1), child: Center(child: Icon(Icons.shopping_basket, size: 48, color: deepGreen))))
              : const ColoredBox(color: Color(0xFFEDFBF1), child: Center(child: Icon(Icons.shopping_basket, size: 48, color: deepGreen))),
        ),
        Padding(
          padding: const EdgeInsets.all(8),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(product['name'], maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              Text('KES ${(sale ?? price).toStringAsFixed(0)}', style: const TextStyle(fontWeight: FontWeight.w900, color: brandRed)),
              IconButton(
                icon: const Icon(Icons.add_circle, color: leafGreen, size: 30),
                onPressed: () => addToCart(context, product),
              ),
            ]),
          ]),
        ),
      ]),
    );
  }
}
