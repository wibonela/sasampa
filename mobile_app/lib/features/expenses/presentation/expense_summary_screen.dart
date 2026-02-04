import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';

class ExpenseSummaryScreen extends ConsumerStatefulWidget {
  const ExpenseSummaryScreen({super.key});

  @override
  ConsumerState<ExpenseSummaryScreen> createState() => _ExpenseSummaryScreenState();
}

class _ExpenseSummaryScreenState extends ConsumerState<ExpenseSummaryScreen> {
  bool _isLoading = true;
  Map<String, dynamic>? _summary;
  DateTime _dateFrom = DateTime.now().subtract(const Duration(days: 30));
  DateTime _dateTo = DateTime.now();
  final _currencyFormat = NumberFormat('#,###');

  @override
  void initState() {
    super.initState();
    _loadSummary();
  }

  Future<void> _loadSummary() async {
    setState(() => _isLoading = true);
    try {
      final api = ref.read(apiClientProvider);
      final response = await api.getExpenseSummary(
        dateFrom: DateFormat('yyyy-MM-dd').format(_dateFrom),
        dateTo: DateFormat('yyyy-MM-dd').format(_dateTo),
      );

      setState(() {
        _summary = response.data['data'] as Map<String, dynamic>;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to load summary: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<void> _selectDateRange() async {
    final picked = await showDateRangePicker(
      context: context,
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now(),
      initialDateRange: DateTimeRange(start: _dateFrom, end: _dateTo),
    );

    if (picked != null) {
      setState(() {
        _dateFrom = picked.start;
        _dateTo = picked.end;
      });
      _loadSummary();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: const Text('Expense Summary'),
        centerTitle: true,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadSummary,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Date Range Selector
                  GestureDetector(
                    onTap: _selectDateRange,
                    child: Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.calendar_today, color: AppColors.primary),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'Period',
                                  style: TextStyle(
                                    fontSize: 13,
                                    color: AppColors.textSecondary,
                                  ),
                                ),
                                Text(
                                  '${DateFormat('dd MMM yyyy').format(_dateFrom)} - ${DateFormat('dd MMM yyyy').format(_dateTo)}',
                                  style: const TextStyle(
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const Icon(Icons.edit, size: 20, color: AppColors.gray3),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Total Summary
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          AppColors.error,
                          AppColors.error.withValues(alpha: 0.8),
                        ],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Total Expenses',
                          style: TextStyle(
                            color: Colors.white70,
                            fontSize: 14,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'TZS ${_currencyFormat.format(_summary?['totals']?['amount'] ?? 0)}',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 32,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          '${_summary?['totals']?['count'] ?? 0} records',
                          style: const TextStyle(
                            color: Colors.white70,
                            fontSize: 14,
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 24),

                  // By Category
                  const Text(
                    'BY CATEGORY',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: AppColors.textSecondary,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      children: [
                        ...(_summary?['by_category'] as List? ?? []).map((item) {
                          final total = _summary?['totals']?['amount'] ?? 1;
                          final percentage = total > 0
                              ? (item['total'] / total * 100).toStringAsFixed(1)
                              : '0';
                          return Column(
                            children: [
                              ListTile(
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 16,
                                  vertical: 4,
                                ),
                                title: Text(item['name']),
                                subtitle: Padding(
                                  padding: const EdgeInsets.only(top: 8),
                                  child: ClipRRect(
                                    borderRadius: BorderRadius.circular(4),
                                    child: LinearProgressIndicator(
                                      value: total > 0 ? item['total'] / total : 0,
                                      backgroundColor: AppColors.gray6,
                                      valueColor: AlwaysStoppedAnimation(
                                        AppColors.error.withValues(alpha: 0.7),
                                      ),
                                    ),
                                  ),
                                ),
                                trailing: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  crossAxisAlignment: CrossAxisAlignment.end,
                                  children: [
                                    Text(
                                      'TZS ${_currencyFormat.format(item['total'])}',
                                      style: const TextStyle(
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                    Text(
                                      '$percentage%',
                                      style: TextStyle(
                                        fontSize: 12,
                                        color: AppColors.textSecondary,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const Divider(height: 1),
                            ],
                          );
                        }),
                        if ((_summary?['by_category'] as List?)?.isEmpty ?? true)
                          const Padding(
                            padding: EdgeInsets.all(24),
                            child: Text(
                              'No expenses in this period',
                              style: TextStyle(color: AppColors.textSecondary),
                            ),
                          ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 24),

                  // By Payment Method
                  const Text(
                    'BY PAYMENT METHOD',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: AppColors.textSecondary,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      children: [
                        ...(_summary?['by_payment_method'] as List? ?? []).map((item) {
                          final methodLabel = switch (item['method']) {
                            'cash' => 'Cash',
                            'mobile' => 'Mobile Money',
                            'card' => 'Card',
                            'bank' => 'Bank Transfer',
                            _ => item['method'],
                          };
                          final icon = switch (item['method']) {
                            'cash' => Icons.money,
                            'mobile' => Icons.phone_android,
                            'card' => Icons.credit_card,
                            'bank' => Icons.account_balance,
                            _ => Icons.payment,
                          };
                          return Column(
                            children: [
                              ListTile(
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 16,
                                  vertical: 4,
                                ),
                                leading: Container(
                                  width: 40,
                                  height: 40,
                                  decoration: BoxDecoration(
                                    color: AppColors.primary.withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Icon(icon, color: AppColors.primary, size: 20),
                                ),
                                title: Text(methodLabel),
                                subtitle: Text('${item['count']} transactions'),
                                trailing: Text(
                                  'TZS ${_currencyFormat.format(item['total'])}',
                                  style: const TextStyle(
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ),
                              const Divider(height: 1),
                            ],
                          );
                        }),
                        if ((_summary?['by_payment_method'] as List?)?.isEmpty ?? true)
                          const Padding(
                            padding: EdgeInsets.all(24),
                            child: Text(
                              'No expenses in this period',
                              style: TextStyle(color: AppColors.textSecondary),
                            ),
                          ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 32),
                ],
              ),
            ),
    );
  }
}
