import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart' hide Category;
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:device_info_plus/device_info_plus.dart';
import 'network/api_client.dart';
import 'storage/secure_storage.dart';
import '../shared/models/user.dart';
import '../shared/models/product.dart';
import '../shared/models/cart.dart';
import '../main.dart' show globalStorage;

// Storage - uses the global instance initialized in main.dart
final secureStorageProvider = Provider<SecureStorage>((ref) {
  // Import globalStorage from main.dart
  return globalStorage;
});

// API Client
final apiClientProvider = Provider<ApiClient>((ref) {
  final storage = ref.watch(secureStorageProvider);
  return ApiClient(storage);
});

// Auth State
class AuthState {
  final User? user;
  final MobileAccess? mobileAccess;
  final bool isLoading;
  final String? error;
  final bool isInitialized; // Track if auth check has completed

  AuthState({
    this.user,
    this.mobileAccess,
    this.isLoading = false,
    this.error,
    this.isInitialized = false,
  });

  bool get isAuthenticated => user != null;
  bool get canUseMobile => mobileAccess?.canUseMobile ?? false;

  AuthState copyWith({
    User? user,
    MobileAccess? mobileAccess,
    bool? isLoading,
    String? error,
    bool? isInitialized,
  }) {
    return AuthState(
      user: user ?? this.user,
      mobileAccess: mobileAccess ?? this.mobileAccess,
      isLoading: isLoading ?? this.isLoading,
      error: error,
      isInitialized: isInitialized ?? this.isInitialized,
    );
  }
}

class AuthNotifier extends StateNotifier<AuthState> {
  final ApiClient _api;
  final SecureStorage _storage;

  AuthNotifier(this._api, this._storage) : super(AuthState());

  Future<bool> login(String email, String password, String deviceName) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final response = await _api.login(email, password, deviceName);
      final data = response.data;

      final user = User.fromJson(data['user']);
      final mobileAccess = MobileAccess.fromJson(data['mobile_access']);

      await _storage.saveToken(data['token']);
      // Cache user data for offline access
      await _storage.saveUserData(jsonEncode({
        'user': data['user'],
        'mobile_access': data['mobile_access'],
      }));
      state = AuthState(user: user, mobileAccess: mobileAccess, isInitialized: true);

      // Auto-register device if mobile access is approved
      if (mobileAccess.canUseMobile) {
        await _autoRegisterDevice();
      }

      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: _getErrorMessage(e));
      return false;
    }
  }

  Future<bool> loginWithPin(String email, String pin, String deviceName) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final response = await _api.loginWithPin(email, pin, deviceName);
      final data = response.data;

      final user = User.fromJson(data['user']);
      final mobileAccess = MobileAccess.fromJson(data['mobile_access']);

      await _storage.saveToken(data['token']);
      // Cache user data for offline access
      await _storage.saveUserData(jsonEncode({
        'user': data['user'],
        'mobile_access': data['mobile_access'],
      }));
      state = AuthState(user: user, mobileAccess: mobileAccess, isInitialized: true);

      // Auto-register device if mobile access is approved
      if (mobileAccess.canUseMobile) {
        await _autoRegisterDevice();
      }

      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: _getErrorMessage(e));
      return false;
    }
  }

  Future<void> _autoRegisterDevice() async {
    try {
      final deviceId = await _storage.getOrCreateDeviceId();
      final deviceInfo = DeviceInfoPlugin();

      String deviceName = 'Unknown Device';
      String deviceModel = '';
      String osVersion = '';

      if (Platform.isIOS) {
        final info = await deviceInfo.iosInfo;
        deviceName = info.name;
        deviceModel = info.model;
        osVersion = 'iOS ${info.systemVersion}';
      } else if (Platform.isAndroid) {
        final info = await deviceInfo.androidInfo;
        deviceName = '${info.brand} ${info.model}';
        deviceModel = info.model;
        osVersion = 'Android ${info.version.release}';
      }

      debugPrint('DEVICE_REG: Registering device $deviceId as $deviceName');
      final response = await _api.registerDevice(
        deviceIdentifier: deviceId,
        deviceName: deviceName,
        deviceModel: deviceModel,
        osVersion: osVersion,
        appVersion: '1.0.0',
      );
      debugPrint('DEVICE_REG: Success - ${response.data}');
    } catch (e) {
      debugPrint('DEVICE_REG: Failed - $e');
      // Continue anyway - device registration is best effort
    }
  }

  Future<void> logout() async {
    try {
      await _api.logout();
    } catch (_) {}
    await _storage.clearAll();
    state = AuthState(isInitialized: true);
  }

  Future<bool> refreshUser() async {
    try {
      final response = await _api.getUser().timeout(
        const Duration(seconds: 10),
        onTimeout: () => throw Exception('Network timeout'),
      );
      final data = response.data;

      final user = User.fromJson(data['user']);
      final mobileAccess = MobileAccess.fromJson(data['mobile_access']);

      // Cache user data for offline access
      await _storage.saveUserData(jsonEncode({
        'user': data['user'],
        'mobile_access': data['mobile_access'],
      }));

      state = AuthState(user: user, mobileAccess: mobileAccess, isInitialized: true);

      // Auto-register device if mobile access is approved
      if (mobileAccess.canUseMobile) {
        await _autoRegisterDevice();
      }
      return true;
    } catch (e) {
      debugPrint('AUTH_REFRESH: Error - $e');
      // Check if it's an authentication error (401/403) vs network error
      final errorStr = e.toString();
      if (errorStr.contains('401') || errorStr.contains('403') || errorStr.contains('Unauthenticated')) {
        // Token is invalid - clear and force re-login
        debugPrint('AUTH_REFRESH: Token invalid, clearing auth');
        await _storage.clearAll();
        state = AuthState(isInitialized: true);
        return false;
      }
      // Network error - keep user logged in if they have a token
      // Don't clear storage, just mark as initialized
      debugPrint('AUTH_REFRESH: Network error, keeping session');
      state = state.copyWith(isInitialized: true);
      return false;
    }
  }

  Future<void> checkAuth() async {
    try {
      debugPrint('AUTH_CHECK: Starting');

      // Ensure storage is initialized
      await _storage.init();

      final isLoggedIn = await _storage.isLoggedIn();
      debugPrint('AUTH_CHECK: isLoggedIn=$isLoggedIn');

      if (isLoggedIn) {
        // Try to refresh user from API
        final refreshed = await refreshUser();
        if (!refreshed) {
          // If refresh failed but we have a token, try to load cached user data
          final cachedUserData = await _storage.getUserData();
          if (cachedUserData != null && cachedUserData.isNotEmpty) {
            try {
              final userData = _parseJson(cachedUserData);
              if (userData['user'] != null) {
                final user = User.fromJson(userData['user']);
                final mobileAccess = userData['mobile_access'] != null
                    ? MobileAccess.fromJson(userData['mobile_access'])
                    : null;
                state = AuthState(
                  user: user,
                  mobileAccess: mobileAccess,
                  isInitialized: true
                );
                debugPrint('AUTH_CHECK: Loaded cached user data');
              } else {
                state = AuthState(isInitialized: true);
              }
            } catch (e) {
              debugPrint('AUTH_CHECK: Failed to parse cached data - $e');
              state = AuthState(isInitialized: true);
            }
          } else {
            debugPrint('AUTH_CHECK: No cached data available');
            // We have a token but no cached data and can't reach server
            // Keep the session valid, user can retry
            state = state.copyWith(isInitialized: true);
          }
        }
      } else {
        state = AuthState(isInitialized: true);
      }
      debugPrint('AUTH_CHECK: Complete, isAuthenticated=${state.isAuthenticated}');
    } catch (e, stack) {
      debugPrint('AUTH_CHECK: Error - $e');
      debugPrint('AUTH_CHECK: Stack - $stack');
      // On critical error, mark as initialized but not logged in
      state = AuthState(isInitialized: true);
    }
  }

  Map<String, dynamic> _parseJson(String jsonStr) {
    try {
      if (jsonStr.isEmpty) return {};
      final decoded = jsonDecode(jsonStr);
      if (decoded is Map<String, dynamic>) {
        return decoded;
      }
      return {};
    } catch (e) {
      debugPrint('JSON_PARSE: Error - $e');
      return {};
    }
  }

  void updateMobileAccess(MobileAccess mobileAccess) {
    state = state.copyWith(mobileAccess: mobileAccess);
  }

  String _getErrorMessage(dynamic error) {
    // Handle Dio errors
    if (error.toString().contains('DioException')) {
      // Check for common error codes
      if (error.toString().contains('422')) {
        return 'Invalid email or password. Please try again.';
      }
      if (error.toString().contains('401')) {
        return 'Invalid credentials. Please check your email and password.';
      }
      if (error.toString().contains('403')) {
        return 'Access denied. Your account may be deactivated.';
      }
      if (error.toString().contains('404')) {
        return 'Service not found. Please try again later.';
      }
      if (error.toString().contains('500')) {
        return 'Server error. Please try again later.';
      }
      if (error.toString().contains('SocketException') ||
          error.toString().contains('connection')) {
        return 'No internet connection. Please check your network.';
      }
    }
    return 'An error occurred. Please try again.';
  }
}

final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  final api = ref.watch(apiClientProvider);
  final storage = ref.watch(secureStorageProvider);
  return AuthNotifier(api, storage);
});

// Cart State
class CartNotifier extends StateNotifier<Cart> {
  CartNotifier() : super(Cart());

  void addProduct(Product product, {int quantity = 1}) {
    state.addItem(product, quantity: quantity);
    state = state.copyWith();
  }

  void removeProduct(int productId) {
    state.removeItem(productId);
    state = state.copyWith();
  }

  void updateQuantity(int productId, int quantity) {
    state.updateQuantity(productId, quantity);
    state = state.copyWith();
  }

  void incrementQuantity(int productId) {
    state.incrementQuantity(productId);
    state = state.copyWith();
  }

  void decrementQuantity(int productId) {
    state.decrementQuantity(productId);
    state = state.copyWith();
  }

  void setDiscount(double amount) {
    state.setDiscount(amount);
    state = state.copyWith();
  }

  void clearCart() {
    state = Cart();
  }
}

final cartProvider = StateNotifierProvider<CartNotifier, Cart>((ref) {
  return CartNotifier();
});

// Products State
class ProductsState {
  final List<Product> products;
  final List<Category> categories;
  final bool isLoading;
  final String? error;
  final int? selectedCategoryId;
  final String searchQuery;

  ProductsState({
    this.products = const [],
    this.categories = const [],
    this.isLoading = false,
    this.error,
    this.selectedCategoryId,
    this.searchQuery = '',
  });

  List<Product> get filteredProducts {
    var filtered = products;

    if (selectedCategoryId != null) {
      filtered = filtered.where((p) => p.category?.id == selectedCategoryId).toList();
    }

    if (searchQuery.isNotEmpty) {
      final query = searchQuery.toLowerCase();
      filtered = filtered.where((p) {
        return p.name.toLowerCase().contains(query) ||
            (p.sku?.toLowerCase().contains(query) ?? false) ||
            (p.barcode?.toLowerCase().contains(query) ?? false);
      }).toList();
    }

    return filtered;
  }

  ProductsState copyWith({
    List<Product>? products,
    List<Category>? categories,
    bool? isLoading,
    String? error,
    int? selectedCategoryId,
    String? searchQuery,
    bool clearCategory = false,
  }) {
    return ProductsState(
      products: products ?? this.products,
      categories: categories ?? this.categories,
      isLoading: isLoading ?? this.isLoading,
      error: error,
      selectedCategoryId: clearCategory ? null : (selectedCategoryId ?? this.selectedCategoryId),
      searchQuery: searchQuery ?? this.searchQuery,
    );
  }
}

class ProductsNotifier extends StateNotifier<ProductsState> {
  final ApiClient _api;

  ProductsNotifier(this._api) : super(ProductsState());

  Future<void> loadProducts() async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final response = await _api.getProducts(perPage: 100);
      final data = response.data;
      final products = (data['data'] as List)
          .map((e) => Product.fromJson(e))
          .toList();

      state = state.copyWith(products: products, isLoading: false);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<void> loadCategories() async {
    try {
      final response = await _api.getCategories();
      final data = response.data;
      final categories = (data['data'] as List)
          .map((e) => Category.fromJson(e))
          .toList();

      state = state.copyWith(categories: categories);
    } catch (e) {
      // Silently fail for categories
    }
  }

  void setCategory(int? categoryId) {
    if (categoryId == state.selectedCategoryId) {
      state = state.copyWith(clearCategory: true);
    } else {
      state = state.copyWith(selectedCategoryId: categoryId);
    }
  }

  void setSearchQuery(String query) {
    state = state.copyWith(searchQuery: query);
  }

  void clearFilters() {
    state = state.copyWith(clearCategory: true, searchQuery: '');
  }

  Future<Product?> scanBarcode(String barcode) async {
    try {
      final response = await _api.scanBarcode(barcode);
      return Product.fromJson(response.data['data']);
    } catch (e) {
      return null;
    }
  }
}

final productsProvider = StateNotifierProvider<ProductsNotifier, ProductsState>((ref) {
  final api = ref.watch(apiClientProvider);
  return ProductsNotifier(api);
});
