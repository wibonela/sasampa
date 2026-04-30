import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/providers.dart';
import '../../../shared/models/profit_breakdown.dart';

final profitBreakdownProvider = FutureProvider.autoDispose
    .family<ProfitBreakdown, ProfitFilters>((ref, filters) async {
  final api = ref.watch(apiClientProvider);
  final response = await api.getProfitBreakdown(
    period: filters.period,
    dateFrom: filters.dateFrom,
    dateTo: filters.dateTo,
    branchId: filters.branchId,
    includeExpenses: filters.includeExpenses,
    expenseCategoryIds: filters.expenseCategoryIds,
  );
  final data = response.data['data'] as Map<String, dynamic>;
  return ProfitBreakdown.fromJson(data);
});

class ProfitFiltersNotifier extends StateNotifier<ProfitFilters> {
  ProfitFiltersNotifier(super.state);

  void setPeriod(String period, {String? dateFrom, String? dateTo}) {
    state = state.copyWith(
      period: period,
      dateFrom: dateFrom,
      dateTo: dateTo,
    );
  }

  void setBranch(int? branchId) {
    state = state.copyWith(
      branchId: branchId,
      clearBranchId: branchId == null,
    );
  }

  void setIncludeExpenses(bool include) {
    state = state.copyWith(includeExpenses: include);
  }

  void setExpenseCategories(List<int> ids) {
    state = state.copyWith(expenseCategoryIds: ids);
  }

  void toggleExpenseCategory(int id) {
    final current = List<int>.from(state.expenseCategoryIds);
    if (current.contains(id)) {
      current.remove(id);
    } else {
      current.add(id);
    }
    state = state.copyWith(expenseCategoryIds: current);
  }
}

const _includeExpensesKey = 'profit_include_expenses';

final profitFiltersProvider =
    StateNotifierProvider<ProfitFiltersNotifier, ProfitFilters>((ref) {
  return ProfitFiltersNotifier(const ProfitFilters());
});

/// Loads the persisted include-expenses preference once and applies it to the
/// filter state. Call from initState of the screen.
Future<void> hydrateProfitIncludeExpenses(WidgetRef ref) async {
  final storage = ref.read(secureStorageProvider);
  final raw = await storage.getString(_includeExpensesKey);
  if (raw == null) return;
  final include = raw == '1' || raw.toLowerCase() == 'true';
  ref.read(profitFiltersProvider.notifier).setIncludeExpenses(include);
}

Future<void> persistProfitIncludeExpenses(WidgetRef ref, bool include) async {
  final storage = ref.read(secureStorageProvider);
  await storage.saveString(_includeExpensesKey, include ? '1' : '0');
}
