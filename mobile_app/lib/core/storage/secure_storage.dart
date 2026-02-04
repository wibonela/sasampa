import 'package:shared_preferences/shared_preferences.dart';
import 'package:uuid/uuid.dart';

class SecureStorage {
  static const String _tokenKey = 'auth_token';
  static const String _userKey = 'user_data';
  static const String _deviceIdKey = 'device_id';
  static const String _lastSyncKey = 'last_sync';
  static const String _rememberEmailKey = 'remember_email';

  SharedPreferences? _prefs;
  bool _initialized = false;
  bool _initializationFailed = false;

  /// Initialize storage. Safe to call multiple times.
  Future<bool> init() async {
    if (_initialized) return true;
    if (_initializationFailed) return false;

    try {
      _prefs = await SharedPreferences.getInstance();
      _initialized = true;
      return true;
    } catch (e) {
      print('SecureStorage: Failed to initialize - $e');
      _initializationFailed = true;
      return false;
    }
  }

  /// Ensure storage is ready before any operation
  Future<bool> _ensureInitialized() async {
    if (_initialized && _prefs != null) return true;
    return await init();
  }

  // Token
  Future<void> saveToken(String token) async {
    if (!await _ensureInitialized()) return;
    try {
      await _prefs!.setString(_tokenKey, token);
    } catch (e) {
      print('SecureStorage: Failed to save token - $e');
    }
  }

  Future<String?> getToken() async {
    if (!await _ensureInitialized()) return null;
    try {
      return _prefs!.getString(_tokenKey);
    } catch (e) {
      print('SecureStorage: Failed to get token - $e');
      return null;
    }
  }

  Future<void> clearToken() async {
    if (!await _ensureInitialized()) return;
    try {
      await _prefs!.remove(_tokenKey);
    } catch (e) {
      print('SecureStorage: Failed to clear token - $e');
    }
  }

  // User Data
  Future<void> saveUserData(String userData) async {
    if (!await _ensureInitialized()) return;
    try {
      await _prefs!.setString(_userKey, userData);
    } catch (e) {
      print('SecureStorage: Failed to save user data - $e');
    }
  }

  Future<String?> getUserData() async {
    if (!await _ensureInitialized()) return null;
    try {
      return _prefs!.getString(_userKey);
    } catch (e) {
      print('SecureStorage: Failed to get user data - $e');
      return null;
    }
  }

  Future<void> clearUserData() async {
    if (!await _ensureInitialized()) return;
    try {
      await _prefs!.remove(_userKey);
    } catch (e) {
      print('SecureStorage: Failed to clear user data - $e');
    }
  }

  // Device ID
  Future<String> getOrCreateDeviceId() async {
    if (!await _ensureInitialized()) {
      // Return a temporary ID if storage fails
      return const Uuid().v4();
    }
    try {
      String? deviceId = _prefs!.getString(_deviceIdKey);
      if (deviceId == null || deviceId.isEmpty) {
        deviceId = const Uuid().v4();
        await _prefs!.setString(_deviceIdKey, deviceId);
      }
      return deviceId;
    } catch (e) {
      print('SecureStorage: Failed to get/create device ID - $e');
      return const Uuid().v4();
    }
  }

  Future<String?> getDeviceId() async {
    if (!await _ensureInitialized()) return null;
    try {
      return _prefs!.getString(_deviceIdKey);
    } catch (e) {
      print('SecureStorage: Failed to get device ID - $e');
      return null;
    }
  }

  // Last Sync
  Future<void> saveLastSync(String timestamp) async {
    if (!await _ensureInitialized()) return;
    try {
      await _prefs!.setString(_lastSyncKey, timestamp);
    } catch (e) {
      print('SecureStorage: Failed to save last sync - $e');
    }
  }

  Future<String?> getLastSync() async {
    if (!await _ensureInitialized()) return null;
    try {
      return _prefs!.getString(_lastSyncKey);
    } catch (e) {
      print('SecureStorage: Failed to get last sync - $e');
      return null;
    }
  }

  // Remember Email
  Future<void> saveRememberEmail(String email) async {
    if (!await _ensureInitialized()) return;
    try {
      await _prefs!.setString(_rememberEmailKey, email);
    } catch (e) {
      print('SecureStorage: Failed to save email - $e');
    }
  }

  Future<String?> getRememberEmail() async {
    if (!await _ensureInitialized()) return null;
    try {
      return _prefs!.getString(_rememberEmailKey);
    } catch (e) {
      print('SecureStorage: Failed to get email - $e');
      return null;
    }
  }

  Future<void> clearRememberEmail() async {
    if (!await _ensureInitialized()) return;
    try {
      await _prefs!.remove(_rememberEmailKey);
    } catch (e) {
      print('SecureStorage: Failed to clear email - $e');
    }
  }

  // Clear All
  Future<void> clearAll() async {
    if (!await _ensureInitialized()) return;
    try {
      await _prefs!.remove(_tokenKey);
      await _prefs!.remove(_userKey);
      // Keep device ID
      // Keep remember email
    } catch (e) {
      print('SecureStorage: Failed to clear all - $e');
    }
  }

  // Check if logged in
  Future<bool> isLoggedIn() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }

  // Check if storage is working
  bool get isInitialized => _initialized;
}
