import 'package:flutter/material.dart';
import 'cart.dart';
import 'screens/home_screen.dart';
import 'screens/catalog_screen.dart';
import 'screens/cart_screen.dart';
import 'screens/orders_screen.dart';

void main() {
  runApp(const ShopFreshApp());
}

const brandRed = Color(0xFFE4572E);
const sunYellow = Color(0xFFFBBF24);
const leafGreen = Color(0xFF22A45D);
const cream = Color(0xFFFFFDF7);

class ShopFreshApp extends StatelessWidget {
  const ShopFreshApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'ShopFresh',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(seedColor: brandRed, primary: brandRed, secondary: leafGreen, surface: cream),
        scaffoldBackgroundColor: cream,
        appBarTheme: const AppBarTheme(backgroundColor: Colors.white, foregroundColor: Colors.black87, elevation: 0.5),
      ),
      home: const MainShell(),
    );
  }
}

class MainShell extends StatefulWidget {
  const MainShell({super.key});
  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> {
  int _index = 0;
  final _screens = const [HomeScreen(), CatalogScreen(), CartScreen(), OrdersScreen()];

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: cart,
      builder: (context, _) => Scaffold(
        body: _screens[_index],
        bottomNavigationBar: NavigationBar(
          selectedIndex: _index,
          onDestinationSelected: (i) => setState(() => _index = i),
          backgroundColor: Colors.white,
          destinations: [
            const NavigationDestination(icon: Icon(Icons.home_outlined), label: 'Home'),
            const NavigationDestination(icon: Icon(Icons.search), label: 'Shop'),
            NavigationDestination(
              icon: Badge(isLabelVisible: cart.count > 0, label: Text('${cart.count}'), child: const Icon(Icons.shopping_cart_outlined)),
              label: 'Cart',
            ),
            const NavigationDestination(icon: Icon(Icons.receipt_long_outlined), label: 'Orders'),
          ],
        ),
      ),
    );
  }
}
