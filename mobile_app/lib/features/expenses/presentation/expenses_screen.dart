import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';

class ExpensesScreen extends ConsumerStatefulWidget {
  const ExpensesScreen({super.key});

  @override
  ConsumerState<ExpensesScreen> createState() => _ExpensesScreenState();
}

class _ExpensesScreenState extends ConsumerState<ExpensesScreen> {
  bool _isLoading = true;
  List<Map<String, dynamic>> _expenses = [];
  Map<String, dynamic> _summary = {};
  List<Map<String, dynamic>> _categories = [];
  int? _selectedCategoryId;
  final _currencyFormat = NumberFormat('#,###');

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final api = ref.read(apiClientProvider);

      // Load categories, expenses, and today's summary in parallel
      final results = await Future.wait([
        api.getExpenseCategories(),
        api.getTodayExpenses(),
      ]);

      final categoriesResponse = results[0];
      final todayResponse = results[1];

      setState(() {
        _categories = List<Map<String, dynamic>>.from(
          categoriesResponse.data['data'] ?? [],
        );
        _expenses = List<Map<String, dynamic>>.from(
          todayResponse.data['data'] ?? [],
        );
        _summary = todayResponse.data['summary'] ?? {};
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to load expenses: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<void> _deleteExpense(int id) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Expense'),
        content: const Text('Are you sure you want to delete this expense?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            child: const Text('Delete'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      final api = ref.read(apiClientProvider);
      await api.deleteExpense(id);
      _loadData();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Expense deleted'),
            backgroundColor: AppColors.success,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to delete: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: const Text('Matumizi (Expenses)'),
        centerTitle: true,
        actions: [
          IconButton(
            onPressed: () => context.push('/expenses/summary'),
            icon: const Icon(Icons.pie_chart_outline),
            tooltip: 'Summary',
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: CustomScrollView(
                slivers: [
                  // Summary Cards
                  SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Row(
                        children: [
                          Expanded(
                            child: _buildSummaryCard(
                              'Today\'s Expenses',
                              'TZS ${_currencyFormat.format(_summary['total_amount'] ?? 0)}',
                              Icons.wallet_outlined,
                              AppColors.error,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: _buildSummaryCard(
                              'Records',
                              '${_summary['total_count'] ?? 0}',
                              Icons.receipt_long_outlined,
                              AppColors.primary,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),

                  // Category Filter
                  if (_categories.isNotEmpty)
                    SliverToBoxAdapter(
                      child: SizedBox(
                        height: 44,
                        child: ListView.builder(
                          scrollDirection: Axis.horizontal,
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          itemCount: _categories.length + 1,
                          itemBuilder: (context, index) {
                            if (index == 0) {
                              return Padding(
                                padding: const EdgeInsets.only(right: 8),
                                child: FilterChip(
                                  label: const Text('All'),
                                  selected: _selectedCategoryId == null,
                                  onSelected: (_) {
                                    setState(() => _selectedCategoryId = null);
                                    _loadData();
                                  },
                                ),
                              );
                            }
                            final category = _categories[index - 1];
                            return Padding(
                              padding: const EdgeInsets.only(right: 8),
                              child: FilterChip(
                                label: Text(category['name']),
                                selected: _selectedCategoryId == category['id'],
                                onSelected: (_) {
                                  setState(() => _selectedCategoryId = category['id']);
                                  _loadData();
                                },
                              ),
                            );
                          },
                        ),
                      ),
                    ),

                  const SliverToBoxAdapter(child: SizedBox(height: 16)),

                  // Section Header
                  SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Text(
                        'Today\'s Expenses',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ),
                  ),

                  const SliverToBoxAdapter(child: SizedBox(height: 8)),

                  // Expenses List
                  _expenses.isEmpty
                      ? SliverToBoxAdapter(
                          child: Center(
                            child: Padding(
                              padding: const EdgeInsets.all(40),
                              child: Column(
                                children: [
                                  Icon(
                                    Icons.wallet_outlined,
                                    size: 64,
                                    color: AppColors.gray4,
                                  ),
                                  const SizedBox(height: 16),
                                  const Text(
                                    'No expenses recorded today',
                                    style: TextStyle(
                                      color: AppColors.textSecondary,
                                      fontSize: 16,
                                    ),
                                  ),
                                  const SizedBox(height: 16),
                                  ElevatedButton.icon(
                                    onPressed: () => context.push('/expenses/add'),
                                    icon: const Icon(Icons.add),
                                    label: const Text('Add Expense'),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        )
                      : SliverList(
                          delegate: SliverChildBuilderDelegate(
                            (context, index) {
                              final expense = _expenses[index];
                              return _buildExpenseItem(expense);
                            },
                            childCount: _expenses.length,
                          ),
                        ),

                  const SliverToBoxAdapter(child: SizedBox(height: 100)),
                ],
              ),
            ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          final result = await context.push('/expenses/add');
          if (result == true) {
            _loadData();
          }
        },
        icon: const Icon(Icons.add),
        label: const Text('Add Expense'),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
      ),
    );
  }

  Widget _buildSummaryCard(
    String title,
    String value,
    IconData icon,
    Color color,
  ) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 20, color: color),
              const SizedBox(width: 8),
              Text(
                title,
                style: TextStyle(
                  fontSize: 13,
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildExpenseItem(Map<String, dynamic> expense) {
    return Dismissible(
      key: Key('expense_${expense['id']}'),
      direction: DismissDirection.endToStart,
      background: Container(
        color: AppColors.error,
        alignment: Alignment.centerRight,
        padding: const EdgeInsets.only(right: 20),
        child: const Icon(Icons.delete, color: Colors.white),
      ),
      confirmDismiss: (_) async {
        _deleteExpense(expense['id']);
        return false;
      },
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
        ),
        child: ListTile(
          contentPadding: const EdgeInsets.all(12),
          leading: Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: AppColors.error.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(
              Icons.remove_circle_outline,
              color: AppColors.error,
            ),
          ),
          title: Text(
            expense['description'],
            style: const TextStyle(fontWeight: FontWeight.w600),
          ),
          subtitle: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 4),
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: AppColors.gray6,
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      expense['category']['name'],
                      style: const TextStyle(fontSize: 11),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: _getPaymentColor(expense['payment_method']).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      expense['payment_method_label'],
                      style: TextStyle(
                        fontSize: 11,
                        color: _getPaymentColor(expense['payment_method']),
                      ),
                    ),
                  ),
                ],
              ),
              if (expense['supplier'] != null) ...[
                const SizedBox(height: 4),
                Text(
                  expense['supplier'],
                  style: TextStyle(
                    fontSize: 12,
                    color: AppColors.textSecondary,
                  ),
                ),
              ],
            ],
          ),
          trailing: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                'TZS ${_currencyFormat.format(expense['total'])}',
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  color: AppColors.error,
                ),
              ),
              Text(
                expense['created_at_human'] ?? '',
                style: TextStyle(
                  fontSize: 11,
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),
          onTap: () async {
            final result = await context.push('/expenses/edit/${expense['id']}');
            if (result == true) {
              _loadData();
            }
          },
        ),
      ),
    );
  }

  Color _getPaymentColor(String method) {
    return switch (method) {
      'cash' => AppColors.success,
      'mobile' => Colors.blue,
      'card' => Colors.purple,
      'bank' => Colors.orange,
      _ => AppColors.textSecondary,
    };
  }
}
