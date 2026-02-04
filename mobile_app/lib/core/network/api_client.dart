import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../storage/secure_storage.dart';

class ApiClient {
  // Production server
  static const String baseUrl = 'https://sasampa.com/api/v1';

  late final Dio _dio;
  final SecureStorage _storage;

  ApiClient(this._storage) {
    _dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ));

    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        // Add auth token
        final token = await _storage.getToken();
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }

        // Add device ID (create if doesn't exist)
        final deviceId = await _storage.getOrCreateDeviceId();
        options.headers['X-Device-ID'] = deviceId;

        // Add app version
        options.headers['X-App-Version'] = '1.0.0';

        if (kDebugMode) {
          print('REQUEST[${options.method}] => PATH: ${options.path}');
        }
        handler.next(options);
      },
      onResponse: (response, handler) {
        if (kDebugMode) {
          print('RESPONSE[${response.statusCode}] => DATA: ${response.data}');
        }
        handler.next(response);
      },
      onError: (error, handler) {
        if (kDebugMode) {
          print('ERROR[${error.response?.statusCode}] => MESSAGE: ${error.message}');
        }
        handler.next(error);
      },
    ));
  }

  // Auth
  Future<Response> login(String email, String password, String deviceName) {
    return _dio.post('/auth/login', data: {
      'email': email,
      'password': password,
      'device_name': deviceName,
    });
  }

  Future<Response> loginWithPin(String email, String pin, String deviceName) {
    return _dio.post('/auth/login/pin', data: {
      'email': email,
      'pin': pin,
      'device_name': deviceName,
    });
  }

  Future<Response> logout() {
    return _dio.post('/auth/logout');
  }

  Future<Response> getUser() {
    return _dio.get('/auth/user');
  }

  Future<Response> setPin(String pin, String currentPassword) {
    return _dio.post('/auth/pin', data: {
      'pin': pin,
      'current_password': currentPassword,
    });
  }

  Future<Response> changePin(String currentPin, String newPin) {
    return _dio.post('/auth/pin/change', data: {
      'current_pin': currentPin,
      'new_pin': newPin,
    });
  }

  Future<Response> changePassword(String currentPassword, String newPassword, String confirmPassword) {
    return _dio.post('/auth/password', data: {
      'current_password': currentPassword,
      'password': newPassword,
      'password_confirmation': confirmPassword,
    });
  }

  // Mobile Access
  Future<Response> getMobileAccessStatus() {
    return _dio.get('/mobile-access/status');
  }

  Future<Response> requestMobileAccess(String reason, int expectedDevices) {
    return _dio.post('/mobile-access/request', data: {
      'request_reason': reason,
      'expected_devices': expectedDevices,
    });
  }

  Future<Response> registerDevice({
    required String deviceIdentifier,
    String? deviceName,
    String? deviceModel,
    String? osVersion,
    String? appVersion,
    String? pushToken,
  }) {
    return _dio.post('/mobile-access/register-device', data: {
      'device_identifier': deviceIdentifier,
      'device_name': deviceName,
      'device_model': deviceModel,
      'os_version': osVersion,
      'app_version': appVersion,
      'push_token': pushToken,
    });
  }

  // Products
  Future<Response> getProducts({
    String? search,
    int? categoryId,
    String? barcode,
    int page = 1,
    int perPage = 50,
  }) {
    return _dio.get('/pos/products', queryParameters: {
      if (search != null) 'search': search,
      if (categoryId != null) 'category_id': categoryId,
      if (barcode != null) 'barcode': barcode,
      'page': page,
      'per_page': perPage,
    });
  }

  Future<Response> getProduct(String idOrBarcode) {
    return _dio.get('/pos/products/$idOrBarcode');
  }

  Future<Response> scanBarcode(String barcode) {
    return _dio.get('/pos/products/scan/$barcode');
  }

  Future<Response> getCategories() {
    return _dio.get('/pos/categories');
  }

  Future<Response> getLowStockProducts() {
    return _dio.get('/pos/products/low-stock');
  }

  // POS
  Future<Response> checkout({
    required List<Map<String, dynamic>> items,
    required String paymentMethod,
    required double amountPaid,
    String? customerName,
    String? customerPhone,
    String? customerTin,
    double? discountAmount,
    String? notes,
    String? offlineId,
  }) {
    return _dio.post('/pos/checkout', data: {
      'items': items,
      'payment_method': paymentMethod,
      'amount_paid': amountPaid,
      if (customerName != null) 'customer_name': customerName,
      if (customerPhone != null) 'customer_phone': customerPhone,
      if (customerTin != null) 'customer_tin': customerTin,
      if (discountAmount != null) 'discount_amount': discountAmount,
      if (notes != null) 'notes': notes,
      if (offlineId != null) 'offline_id': offlineId,
    });
  }

  Future<Response> voidTransaction(int transactionId, String reason) {
    return _dio.post('/pos/transactions/$transactionId/void', data: {
      'reason': reason,
    });
  }

  Future<Response> getReceipt(int transactionId) {
    return _dio.get('/pos/transactions/$transactionId/receipt');
  }

  // Transactions
  Future<Response> getTransactions({
    String? status,
    String? paymentMethod,
    String? dateFrom,
    String? dateTo,
    String? search,
    int page = 1,
    int perPage = 20,
  }) {
    return _dio.get('/pos/transactions', queryParameters: {
      if (status != null) 'status': status,
      if (paymentMethod != null) 'payment_method': paymentMethod,
      if (dateFrom != null) 'date_from': dateFrom,
      if (dateTo != null) 'date_to': dateTo,
      if (search != null) 'search': search,
      'page': page,
      'per_page': perPage,
    });
  }

  Future<Response> getTransaction(int id) {
    return _dio.get('/pos/transactions/$id');
  }

  Future<Response> getTodayTransactions() {
    return _dio.get('/pos/transactions/today');
  }

  Future<Response> getMyTransactions({String? date}) {
    return _dio.get('/pos/transactions/mine', queryParameters: {
      if (date != null) 'date': date,
    });
  }

  // Inventory
  Future<Response> getInventory({
    bool? lowStock,
    bool? outOfStock,
    int? categoryId,
    String? search,
    int page = 1,
    int perPage = 50,
  }) {
    return _dio.get('/inventory', queryParameters: {
      if (lowStock != null) 'low_stock': lowStock,
      if (outOfStock != null) 'out_of_stock': outOfStock,
      if (categoryId != null) 'category_id': categoryId,
      if (search != null) 'search': search,
      'page': page,
      'per_page': perPage,
    });
  }

  Future<Response> getInventorySummary() {
    return _dio.get('/inventory/summary');
  }

  Future<Response> adjustStock(int productId, String type, int quantity, String? reason) {
    return _dio.post('/inventory/$productId/adjust', data: {
      'type': type,
      'quantity': quantity,
      if (reason != null) 'reason': reason,
    });
  }

  // Reports
  Future<Response> getDashboard() {
    return _dio.get('/reports/dashboard');
  }

  Future<Response> getSalesReport({
    String period = 'today',
    String? dateFrom,
    String? dateTo,
  }) {
    return _dio.get('/reports/sales', queryParameters: {
      'period': period,
      if (dateFrom != null) 'date_from': dateFrom,
      if (dateTo != null) 'date_to': dateTo,
    });
  }

  // Sync
  Future<Response> pullSync({String? since, List<String>? include}) {
    return _dio.get('/sync/pull', queryParameters: {
      if (since != null) 'since': since,
      if (include != null) 'include': include,
    });
  }

  Future<Response> pushSync(List<Map<String, dynamic>> transactions) {
    return _dio.post('/sync/push', data: {
      'transactions': transactions,
    });
  }

  Future<Response> getSyncStatus() {
    return _dio.get('/sync/status');
  }

  // Expenses (Matumizi)
  Future<Response> getExpenses({
    String? search,
    int? categoryId,
    String? dateFrom,
    String? dateTo,
    String? paymentMethod,
    int page = 1,
    int perPage = 20,
  }) {
    return _dio.get('/expenses', queryParameters: {
      if (search != null) 'search': search,
      if (categoryId != null) 'category_id': categoryId,
      if (dateFrom != null) 'date_from': dateFrom,
      if (dateTo != null) 'date_to': dateTo,
      if (paymentMethod != null) 'payment_method': paymentMethod,
      'page': page,
      'per_page': perPage,
    });
  }

  Future<Response> getTodayExpenses() {
    return _dio.get('/expenses/today');
  }

  Future<Response> getExpenseCategories() {
    return _dio.get('/expenses/categories');
  }

  Future<Response> createExpenseCategory(String name, String? description) {
    return _dio.post('/expenses/categories', data: {
      'name': name,
      if (description != null) 'description': description,
    });
  }

  Future<Response> getExpenseSummary({String? dateFrom, String? dateTo}) {
    return _dio.get('/expenses/summary', queryParameters: {
      if (dateFrom != null) 'date_from': dateFrom,
      if (dateTo != null) 'date_to': dateTo,
    });
  }

  Future<Response> createExpense({
    required int categoryId,
    required String description,
    required double amount,
    required double quantity,
    String? unit,
    required String expenseDate,
    String? referenceNumber,
    String? supplier,
    required String paymentMethod,
    String? notes,
  }) {
    return _dio.post('/expenses', data: {
      'expense_category_id': categoryId,
      'description': description,
      'amount': amount,
      'quantity': quantity,
      if (unit != null) 'unit': unit,
      'expense_date': expenseDate,
      if (referenceNumber != null) 'reference_number': referenceNumber,
      if (supplier != null) 'supplier': supplier,
      'payment_method': paymentMethod,
      if (notes != null) 'notes': notes,
    });
  }

  Future<Response> getExpense(int id) {
    return _dio.get('/expenses/$id');
  }

  Future<Response> updateExpense(int id, Map<String, dynamic> data) {
    return _dio.put('/expenses/$id', data: data);
  }

  Future<Response> deleteExpense(int id) {
    return _dio.delete('/expenses/$id');
  }

  // Store Settings
  Future<Response> getSettings() {
    return _dio.get('/settings');
  }

  Future<Response> updateSettings(Map<String, dynamic> settings) {
    return _dio.put('/settings', data: settings);
  }

  Future<Response> uploadLogo(String filePath) async {
    final formData = FormData.fromMap({
      'logo': await MultipartFile.fromFile(filePath),
    });
    return _dio.post('/settings/logo', data: formData);
  }

  Future<Response> removeLogo() {
    return _dio.delete('/settings/logo');
  }
}
