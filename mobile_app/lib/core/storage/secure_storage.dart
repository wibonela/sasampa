import 'package:shared_preferences/shared_preferences.dart';
import 'package:uuid/uuid.dart';

class SecureStorage {
  static const String _tokenKey = 'auth_token';
  static const String _userKey = 'user_data';
  static const String _deviceIdKey = 'device_id';
  static const String _lastSyncKey = 'last_sync';
  static const String _rememberEmailKey = 'remember_email';

  late SharedPreferences _prefs;
  bool _initialized = false;

  Future<void> init() async {
    if (!_initialized) {
      _prefs = await SharedPreferences.getInstance();
      _initialized = true;
    }
  }

  // Token
  Future<void> saveToken(String token) async {
    await init();
    await _prefs.setString(_tokenKey, token);
  }

  Future<String?> getToken() async {
    await init();
    return _prefs.getString(_tokenKey);
  }

  Future<void> clearToken() async {
    await init();
    await _prefs.remove(_tokenKey);
  }

  // User Data
  Future<void> saveUserData(String userData) async {
    await init();
    await _prefs.setString(_userKey, userData);
  }

  Future<String?> getUserData() async {
    await init();
    return _prefs.getString(_userKey);
  }

  Future<void> clearUserData() async {
    await init();
    await _prefs.remove(_userKey);
  }

  // Device ID
  Future<String> getOrCreateDeviceId() async {
    await init();
    String? deviceId = _prefs.getString(_deviceIdKey);
    if (deviceId == null) {
      deviceId = const Uuid().v4();
      await _prefs.setString(_deviceIdKey, deviceId);
    }
    return deviceId;
  }

  Future<String?> getDeviceId() async {
    await init();
    return _prefs.getString(_deviceIdKey);
  }

  // Last Sync
  Future<void> saveLastSync(String timestamp) async {
    await init();
    await _prefs.setString(_lastSyncKey, timestamp);
  }

  Future<String?> getLastSync() async {
    await init();
    return _prefs.getString(_lastSyncKey);
  }

  // Remember Email
  Future<void> saveRememberEmail(String email) async {
    await init();
    await _prefs.setString(_rememberEmailKey, email);
  }

  Future<String?> getRememberEmail() async {
    await init();
    return _prefs.getString(_rememberEmailKey);
  }

  Future<void> clearRememberEmail() async {
    await init();
    await _prefs.remove(_rememberEmailKey);
  }

  // Clear All
  Future<void> clearAll() async {
    await init();
    await _prefs.remove(_tokenKey);
    await _prefs.remove(_userKey);
    // Keep device ID
    // Keep remember email
  }

  // Check if logged in
  Future<bool> isLoggedIn() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }
}
