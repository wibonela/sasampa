import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';
import '../../../core/utils/error_utils.dart';
import '../../../shared/models/transaction.dart';

class TransactionsScreen extends ConsumerStatefulWidget {
  const TransactionsScreen({super.key});

  @override
  ConsumerState<TransactionsScreen> createState() => _TransactionsScreenState();
}

class _TransactionsScreenState extends ConsumerState<TransactionsScreen> {
  List<Transaction> _transactions = [];
  Map<String, dynamic>? _summary;
  bool _isLoading = true;
  bool _isLoadingMore = false;
  String? _error;
  String? _paymentFilter;
  String _searchQuery = '';
  int _currentPage = 1;
  bool _hasMore = true;
  final _searchController = TextEditingController();
  final _scrollController = ScrollController();

  final _currencyFormat = NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);

  @override
  void initState() {
    super.initState();
    _loadData();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _searchController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_hasMore &&
        !_isLoadingMore &&
        _scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
      _loadMore();
    }
  }

  Future<void> _loadMore() async {
    if (_isLoadingMore || !_hasMore) return;
    setState(() => _isLoadingMore = true);

    try {
      final api = ref.read(apiClientProvider);
      final response = await api.getTransactions(
        search: _searchQuery.isNotEmpty ? _searchQuery : null,
        paymentMethod: _paymentFilter,
        page: _currentPage + 1,
      );
      final data = response.data;
      final newItems = (data['data'] as List)
          .map((e) => Transaction.fromJson(e))
          .toList();

      setState(() {
        _transactions.addAll(newItems);
        _currentPage++;
        _hasMore = newItems.length >= 20;
        _isLoadingMore = false;
      });
    } catch (_) {
      setState(() => _isLoadingMore = false);
    }
  }

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _error = null;
      _currentPage = 1;
      _hasMore = true;
    });

    try {
      final api = ref.read(apiClientProvider);

      // Load summary and transactions in parallel
      final results = await Future.wait([
        api.getTransactionSummary(),
        api.getTransactions(
          search: _searchQuery.isNotEmpty ? _searchQuery : null,
          paymentMethod: _paymentFilter,
        ),
      ]);

      final summaryData = results[0].data;
      final txData = results[1].data;

      setState(() {
        _summary = summaryData['data'];
        _transactions = (txData['data'] as List)
            .map((e) => Transaction.fromJson(e))
            .toList();
        _hasMore = _transactions.length >= 20;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = extractErrorMessage(e, 'Failed to load transactions');
        _isLoading = false;
      });
    }
  }

  String _getDateLabel(DateTime date) {
    final l10n = AppLocalizations.of(context)!;
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final dateOnly = DateTime(date.year, date.month, date.day);

    if (dateOnly == today) return l10n.today;
    if (dateOnly == today.subtract(const Duration(days: 1))) return l10n.yesterday;
    return DateFormat('d MMM yyyy').format(date);
  }

  // Group transactions by date
  Map<String, List<Transaction>> _groupByDate() {
    final groups = <String, List<Transaction>>{};
    for (final tx in _transactions) {
      final dt = DateTime.parse(tx.createdAt);
      final key = _getDateLabel(dt);
      groups.putIfAbsent(key, () => []).add(tx);
    }
    return groups;
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: _searchQuery.isNotEmpty || _searchController.text.isNotEmpty
            ? TextField(
                controller: _searchController,
                autofocus: true,
                decoration: InputDecoration(
                  hintText: l10n.searchHint,
                  border: InputBorder.none,
                  suffixIcon: IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () {
                      _searchController.clear();
                      setState(() => _searchQuery = '');
                      _loadData();
                    },
                  ),
                ),
                onSubmitted: (value) {
                  setState(() => _searchQuery = value);
                  _loadData();
                },
                style: const TextStyle(fontSize: 16),
              )
            : Text(l10n.transactions),
        centerTitle: _searchQuery.isEmpty && _searchController.text.isEmpty,
        actions: [
          if (_searchQuery.isEmpty && _searchController.text.isEmpty)
            IconButton(
              icon: const Icon(Icons.search),
              onPressed: () {
                setState(() => _searchController.text = ' ');
                _searchController.text = '';
              },
            ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.error_outline, size: 48, color: AppColors.error),
                        const SizedBox(height: 16),
                        Text(_error!),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadData,
                          child: Text(l10n.retry),
                        ),
                      ],
                    ),
                  )
                : CustomScrollView(
                    controller: _scrollController,
                    slivers: [
                      // Summary Cards
                      if (_summary != null)
                        SliverToBoxAdapter(
                          child: SizedBox(
                            height: 100,
                            child: ListView(
                              scrollDirection: Axis.horizontal,
                              padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                              children: [
                                _buildSummaryCard(
                                  l10n.today,
                                  _currencyFormat.format((_summary!['today_total'] ?? 0).toDouble()),
                                  '${_summary!['today_count'] ?? 0}',
                                  AppColors.primary,
                                ),
                                const SizedBox(width: 12),
                                _buildSummaryCard(
                                  l10n.thisWeek,
                                  _currencyFormat.format((_summary!['week_total'] ?? 0).toDouble()),
                                  '${_summary!['week_count'] ?? 0}',
                                  AppColors.accent,
                                ),
                                const SizedBox(width: 12),
                                _buildSummaryCard(
                                  l10n.thisMonth,
                                  _currencyFormat.format((_summary!['month_total'] ?? 0).toDouble()),
                                  '${_summary!['month_count'] ?? 0}',
                                  AppColors.success,
                                ),
                              ],
                            ),
                          ),
                        ),

                      // Quick filters
                      SliverToBoxAdapter(
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                          child: SingleChildScrollView(
                            scrollDirection: Axis.horizontal,
                            child: Row(
                              children: [
                                _buildFilterChip(null, l10n.all),
                                const SizedBox(width: 8),
                                _buildFilterChip('cash', l10n.cash),
                                const SizedBox(width: 8),
                                _buildFilterChip('card', l10n.card),
                                const SizedBox(width: 8),
                                _buildFilterChip('mobile', l10n.mobileMoney),
                                const SizedBox(width: 8),
                                _buildFilterChip('bank_transfer', l10n.bankTransfer),
                              ],
                            ),
                          ),
                        ),
                      ),

                      // Transactions grouped by date
                      if (_transactions.isEmpty)
                        SliverFillRemaining(
                          child: Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                const Icon(Icons.receipt_long_outlined, size: 64, color: AppColors.gray3),
                                const SizedBox(height: 16),
                                Text(l10n.noTransactions, style: const TextStyle(color: AppColors.textSecondary)),
                              ],
                            ),
                          ),
                        )
                      else
                        ..._buildGroupedList(),

                      if (_isLoadingMore)
                        const SliverToBoxAdapter(
                          child: Padding(
                            padding: EdgeInsets.all(16),
                            child: Center(child: CircularProgressIndicator()),
                          ),
                        ),

                      const SliverToBoxAdapter(child: SizedBox(height: 100)),
                    ],
                  ),
      ),
    );
  }

  List<Widget> _buildGroupedList() {
    final groups = _groupByDate();
    final slivers = <Widget>[];

    for (final entry in groups.entries) {
      // Date header
      slivers.add(
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: Text(
              entry.key,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
            ),
          ),
        ),
      );

      // Transactions for this date
      slivers.add(
        SliverList(
          delegate: SliverChildBuilderDelegate(
            (context, index) => _buildTransactionItem(entry.value[index]),
            childCount: entry.value.length,
          ),
        ),
      );
    }

    return slivers;
  }

  Widget _buildSummaryCard(String label, String amount, String count, Color color) {
    return Container(
      width: 160,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [color, color.withOpacity(0.8)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(label, style: TextStyle(color: Colors.white.withOpacity(0.8), fontSize: 12)),
              Text(count, style: TextStyle(color: Colors.white.withOpacity(0.8), fontSize: 12)),
            ],
          ),
          Text(
            amount,
            style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String? value, String label) {
    final selected = _paymentFilter == value;
    return ChoiceChip(
      label: Text(label),
      selected: selected,
      onSelected: (_) {
        setState(() => _paymentFilter = selected ? null : value);
        _loadData();
      },
      selectedColor: AppColors.primary,
      labelStyle: TextStyle(
        color: selected ? Colors.white : AppColors.textPrimary,
        fontSize: 12,
      ),
      showCheckmark: false,
      visualDensity: VisualDensity.compact,
    );
  }

  Widget _buildTransactionItem(Transaction tx) {
    final isVoided = tx.isVoided;
    final time = DateFormat('HH:mm').format(DateTime.parse(tx.createdAt));

    return GestureDetector(
      onTap: () => context.go('/transactions/${tx.id}'),
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          children: [
            // Payment method icon
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: isVoided
                    ? AppColors.error.withValues(alpha: 0.1)
                    : _getPaymentMethodColor(tx.paymentMethod ?? 'cash').withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(
                isVoided ? Icons.cancel_outlined : _getPaymentMethodIcon(tx.paymentMethod ?? 'cash'),
                color: isVoided ? AppColors.error : _getPaymentMethodColor(tx.paymentMethod ?? 'cash'),
                size: 20,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          tx.customerName ?? AppLocalizations.of(context)!.regularCustomer,
                          style: TextStyle(
                            fontWeight: FontWeight.w600,
                            fontSize: 13,
                            decoration: isVoided ? TextDecoration.lineThrough : null,
                            color: isVoided ? AppColors.textSecondary : AppColors.textPrimary,
                          ),
                          overflow: TextOverflow.ellipsis,
                          maxLines: 1,
                        ),
                      ),
                      if (isVoided) ...[
                        const SizedBox(width: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                          decoration: BoxDecoration(
                            color: AppColors.error.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: const Text('VOID', style: TextStyle(fontSize: 8, fontWeight: FontWeight.w600, color: AppColors.error)),
                        ),
                      ],
                      const SizedBox(width: 8),
                      Text(
                        _currencyFormat.format(tx.total),
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                          decoration: isVoided ? TextDecoration.lineThrough : null,
                          color: isVoided ? AppColors.textSecondary : AppColors.primary,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Text(
                        tx.transactionNumber,
                        style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                      ),
                      const Spacer(),
                      Text(
                        time,
                        style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(width: 4),
            const Icon(Icons.chevron_right, color: AppColors.gray3, size: 18),
          ],
        ),
      ),
    );
  }

  IconData _getPaymentMethodIcon(String method) {
    switch (method) {
      case 'cash':
        return Icons.payments;
      case 'card':
        return Icons.credit_card;
      case 'mobile':
        return Icons.phone_android;
      case 'bank_transfer':
        return Icons.account_balance;
      default:
        return Icons.payment;
    }
  }

  Color _getPaymentMethodColor(String method) {
    switch (method) {
      case 'cash':
        return AppColors.cash;
      case 'card':
        return AppColors.card;
      case 'mobile':
        return AppColors.mobile;
      case 'bank_transfer':
        return AppColors.bankTransfer;
      default:
        return AppColors.primary;
    }
  }
}
