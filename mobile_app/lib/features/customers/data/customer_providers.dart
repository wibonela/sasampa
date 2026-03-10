import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';
import '../../../core/providers.dart';
import '../../../shared/models/customer.dart';

class CustomersState {
  final List<Customer> customers;
  final bool isLoading;
  final String? error;
  final int currentPage;
  final int lastPage;
  final int total;

  CustomersState({
    this.customers = const [],
    this.isLoading = false,
    this.error,
    this.currentPage = 1,
    this.lastPage = 1,
    this.total = 0,
  });

  CustomersState copyWith({
    List<Customer>? customers,
    bool? isLoading,
    String? error,
    int? currentPage,
    int? lastPage,
    int? total,
  }) {
    return CustomersState(
      customers: customers ?? this.customers,
      isLoading: isLoading ?? this.isLoading,
      error: error,
      currentPage: currentPage ?? this.currentPage,
      lastPage: lastPage ?? this.lastPage,
      total: total ?? this.total,
    );
  }

  bool get hasMore => currentPage < lastPage;
}

class CustomersNotifier extends StateNotifier<CustomersState> {
  final ApiClient _api;

  CustomersNotifier(this._api) : super(CustomersState());

  Future<void> loadCustomers({String? search, bool refresh = false}) async {
    if (refresh) {
      state = CustomersState(isLoading: true);
    } else {
      state = state.copyWith(isLoading: true, error: null);
    }

    try {
      final response = await _api.getCustomers(
        search: search,
        page: 1,
        perPage: 50,
      );
      final data = response.data;
      final customers = (data['data'] as List)
          .map((e) => Customer.fromJson(e))
          .toList();
      final meta = data['meta'] as Map<String, dynamic>? ?? {};

      state = CustomersState(
        customers: customers,
        isLoading: false,
        currentPage: meta['current_page'] ?? 1,
        lastPage: meta['last_page'] ?? 1,
        total: meta['total'] ?? customers.length,
      );
    } catch (e) {
      debugPrint('CUSTOMERS: Error loading - $e');
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<void> loadMore({String? search}) async {
    if (!state.hasMore || state.isLoading) return;

    state = state.copyWith(isLoading: true);
    try {
      final response = await _api.getCustomers(
        search: search,
        page: state.currentPage + 1,
        perPage: 50,
      );
      final data = response.data;
      final newCustomers = (data['data'] as List)
          .map((e) => Customer.fromJson(e))
          .toList();
      final meta = data['meta'] as Map<String, dynamic>? ?? {};

      state = state.copyWith(
        customers: [...state.customers, ...newCustomers],
        isLoading: false,
        currentPage: meta['current_page'] ?? state.currentPage + 1,
        lastPage: meta['last_page'] ?? state.lastPage,
        total: meta['total'] ?? state.total,
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }
}

final customersProvider =
    StateNotifierProvider<CustomersNotifier, CustomersState>((ref) {
  final api = ref.watch(apiClientProvider);
  return CustomersNotifier(api);
});
