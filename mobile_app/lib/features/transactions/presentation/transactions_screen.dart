import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
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
  String _filter = 'today';
  String _searchQuery = '';
  int _currentPage = 1;
  bool _hasMore = true;
  final _searchController = TextEditingController();
  final _scrollController = ScrollController();

  final _currencyFormat = NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);

  @override
  void initState() {
    super.initState();
    _loadTransactions();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _searchController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_filter == 'all' &&
        _hasMore &&
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

  Future<void> _loadTransactions() async {
    setState(() {
      _isLoading = true;
      _error = null;
      _currentPage = 1;
      _hasMore = true;
    });

    try {
      final api = ref.read(apiClientProvider);
      final response = _filter == 'today'
          ? await api.getTodayTransactions()
          : await api.getTransactions(
              search: _searchQuery.isNotEmpty ? _searchQuery : null,
            );

      final data = response.data;

      setState(() {
        _transactions = (data['data'] as List)
            .map((e) => Transaction.fromJson(e))
            .toList();
        _summary = data['summary'];
        _hasMore = _filter == 'all' && _transactions.length >= 20;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = extractErrorMessage(e, 'Failed to load transactions');
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: _searchQuery.isNotEmpty || _searchController.text.isNotEmpty
            ? TextField(
                controller: _searchController,
                autofocus: true,
                decoration: InputDecoration(
                  hintText: 'Search by number, customer...',
                  border: InputBorder.none,
                  suffixIcon: IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () {
                      _searchController.clear();
                      setState(() => _searchQuery = '');
                      _loadTransactions();
                    },
                  ),
                ),
                onSubmitted: (value) {
                  setState(() => _searchQuery = value);
                  _loadTransactions();
                },
                style: const TextStyle(fontSize: 16),
              )
            : const Text('Transactions'),
        centerTitle: _searchQuery.isEmpty && _searchController.text.isEmpty,
        actions: [
          if (_searchQuery.isEmpty && _searchController.text.isEmpty)
            IconButton(
              icon: const Icon(Icons.search),
              onPressed: () {
                setState(() {
                  _searchController.text = '';
                  _filter = 'all';
                });
              },
            ),
          PopupMenuButton<String>(
            icon: const Icon(Icons.filter_list),
            onSelected: (value) {
              setState(() => _filter = value);
              _loadTransactions();
            },
            itemBuilder: (context) => [
              PopupMenuItem(
                value: 'today',
                child: Row(
                  children: [
                    if (_filter == 'today') const Icon(Icons.check, size: 18),
                    if (_filter == 'today') const SizedBox(width: 8),
                    const Text('Today'),
                  ],
                ),
              ),
              PopupMenuItem(
                value: 'all',
                child: Row(
                  children: [
                    if (_filter == 'all') const Icon(Icons.check, size: 18),
                    if (_filter == 'all') const SizedBox(width: 8),
                    const Text('All Time'),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadTransactions,
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
                          onPressed: _loadTransactions,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  )
                : CustomScrollView(
                    controller: _scrollController,
                    slivers: [
                      // Summary Card
                      if (_summary != null)
                        SliverToBoxAdapter(
                          child: Container(
                            margin: const EdgeInsets.all(16),
                            padding: const EdgeInsets.all(20),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [AppColors.primary, AppColors.accent],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  _filter == 'today' ? "Today's Sales" : 'Total Sales',
                                  style: TextStyle(
                                    color: Colors.white.withValues(alpha:0.8),
                                    fontSize: 14,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  _currencyFormat.format((_summary!['total_sales'] ?? 0).toDouble()),
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 28,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 16),
                                Row(
                                  children: [
                                    _buildSummaryItem(
                                      'Completed',
                                      '${_summary!['completed_transactions'] ?? 0}',
                                    ),
                                    const SizedBox(width: 24),
                                    _buildSummaryItem(
                                      'Voided',
                                      '${_summary!['voided_transactions'] ?? 0}',
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ),

                      // Transactions List
                      if (_transactions.isEmpty)
                        const SliverFillRemaining(
                          child: Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.receipt_long_outlined, size: 64, color: AppColors.gray3),
                                SizedBox(height: 16),
                                Text(
                                  'No transactions yet',
                                  style: TextStyle(color: AppColors.textSecondary),
                                ),
                              ],
                            ),
                          ),
                        )
                      else
                        SliverList(
                          delegate: SliverChildBuilderDelegate(
                            (context, index) {
                              final tx = _transactions[index];
                              return _buildTransactionItem(tx);
                            },
                            childCount: _transactions.length,
                          ),
                        ),

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

  Widget _buildSummaryItem(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            color: Colors.white.withValues(alpha:0.7),
            fontSize: 12,
          ),
        ),
        Text(
          value,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
      ],
    );
  }

  Widget _buildTransactionItem(Transaction tx) {
    final isVoided = tx.isVoided;

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
                    : _getPaymentMethodColor(tx.paymentMethod).withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(
                isVoided ? Icons.cancel_outlined : _getPaymentMethodIcon(tx.paymentMethod),
                color: isVoided ? AppColors.error : _getPaymentMethodColor(tx.paymentMethod),
                size: 20,
              ),
            ),
            const SizedBox(width: 10),
            // Transaction info - takes remaining space
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          tx.transactionNumber,
                          style: TextStyle(
                            fontWeight: FontWeight.w600,
                            fontSize: 12,
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
                          child: const Text(
                            'VOID',
                            style: TextStyle(
                              fontSize: 8,
                              fontWeight: FontWeight.w600,
                              color: AppColors.error,
                            ),
                          ),
                        ),
                      ],
                      const SizedBox(width: 8),
                      // Amount on same row
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
                      Expanded(
                        child: Text(
                          tx.createdAtHuman ?? '',
                          style: const TextStyle(
                            fontSize: 11,
                            color: AppColors.textSecondary,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      Text(
                        tx.paymentMethodLabel,
                        style: const TextStyle(
                          fontSize: 10,
                          color: AppColors.textSecondary,
                        ),
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
