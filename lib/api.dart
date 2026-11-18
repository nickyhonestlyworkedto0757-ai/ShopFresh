import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

// Android emulator: http://10.0.2.2:8000/api
// iOS simulator / desktop: http://127.0.0.1:8000/api
// Physical device: use your machine's LAN IP, e.g. http://192.168.1.10:8000/api
const String apiBase = 'http://10.0.2.2:8000/api';

class Api {
  static Future<String?> token() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('api_token');
  }

  static Future<bool> isLoggedIn() async => (await token()) != null;

  static Future<Map<String, String>> headers({bool auth = false}) async {
    final h = {'Accept': 'application/json'};
    if (auth) {
      final t = await token();
      if (t != null) h['Authorization'] = 'Bearer $t';
    }
    return h;
  }

  static Future<dynamic> get(String path, {bool auth = false}) async {
    final res = await http.get(Uri.parse('$apiBase$path'), headers: await headers(auth: auth));
    return jsonDecode(res.body);
  }

  static Future<dynamic> put(String path, Map<String, dynamic> body, {bool auth = false}) async {
    final res = await http.put(Uri.parse('$apiBase$path'),
        headers: {...await headers(auth: auth), 'Content-Type': 'application/json'},
        body: jsonEncode(body));
    return jsonDecode(res.body);
  }

  static Future<dynamic> post(String path, Map<String, dynamic> body, {bool auth = false}) async {
    final res = await http.post(Uri.parse('$apiBase$path'),
        headers: {...await headers(auth: auth), 'Content-Type': 'application/json'},
        body: jsonEncode(body));
    return jsonDecode(res.body);
  }

  static Future<bool> login(String email, String password) async {
    final res = await post('/auth/login', {'email': email, 'password': password});
    if (res is Map && res['token'] != null) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('api_token', res['token']);
      return true;
    }
    return false;
  }

  static Future<bool> register(String name, String email, String password) async {
    final res = await post('/auth/register', {'name': name, 'email': email, 'password': password});
    if (res is Map && res['token'] != null) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('api_token', res['token']);
      return true;
    }
    return false;
  }

  static Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('api_token');
  }
}
