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
  Map<String, dynamic>? _insights;
  List<Transaction> _transactions = [];
  bool _isLoading = true;
  bool _isLoadingMore = false;
  String? _error;
  String? _paymentFilter;
  String _searchQuery = '';
  int _currentPage = 1;
  bool _hasMore = true;
  bool _showInsights = true;
  final _searchController = TextEditingController();
  final _scrollController = ScrollController();

  final _currencyFormat = NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);
  final _compactFormat = NumberFormat.compact();

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

      // Load transactions (required)
      final txResponse = await api.getTransactions(
        search: _searchQuery.isNotEmpty ? _searchQuery : null,
        paymentMethod: _paymentFilter,
      );

      setState(() {
        _transactions = (txResponse.data['data'] as List)
            .map((e) => Transaction.fromJson(e))
            .toList();
        _hasMore = _transactions.length >= 20;
        _isLoading = false;
      });

      // Load insights separately (non-blocking)
      try {
        final insightsResponse = await api.getSalesInsights();
        if (mounted) {
          setState(() {
            _insights = insightsResponse.data['data'];
          });
        }
      } catch (_) {
        // Insights failed — page still works with transactions
      }
    } catch (e) {
      setState(() {
        _error = extractErrorMessage(e, 'Failed to load');
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
        title: _searchController.text.isNotEmpty
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
            : Text(l10n.sales),
        centerTitle: _searchController.text.isEmpty,
        actions: [
          if (_searchController.text.isEmpty) ...[
            IconButton(
              icon: Icon(_showInsights ? Icons.list : Icons.insights),
              onPressed: () => setState(() => _showInsights = !_showInsights),
              tooltip: _showInsights ? l10n.allTransactions : l10n.insights,
            ),
            IconButton(
              icon: const Icon(Icons.search),
              onPressed: () {
                setState(() => _searchController.text = ' ');
                _searchController.text = '';
              },
            ),
          ],
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
                        ElevatedButton(onPressed: _loadData, child: Text(l10n.retry)),
                      ],
                    ),
                  )
                : CustomScrollView(
                    controller: _scrollController,
                    slivers: [
                      // Insights section
                      if (_showInsights && _insights != null) ...[
                        _buildPeriodComparison(l10n),
                        _buildTopProductsSection(l10n),
                        _buildPaymentBreakdown(l10n),
                        _buildDiscountStats(l10n),
                        _buildPeakHours(l10n),
                        _buildCustomerInsights(l10n),
                        _buildMarginAlerts(l10n),
                      ],

                      // Section divider
                      SliverToBoxAdapter(
                        child: Padding(
                          padding: const EdgeInsets.fromLTRB(20, 20, 20, 8),
                          child: Row(
                            children: [
                              Text(
                                l10n.allTransactions,
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.textPrimary,
                                ),
                              ),
                              const Spacer(),
                              if (_paymentFilter != null)
                                GestureDetector(
                                  onTap: () {
                                    setState(() => _paymentFilter = null);
                                    _loadData();
                                  },
                                  child: Text(
                                    l10n.all,
                                    style: const TextStyle(
                                      fontSize: 13,
                                      color: AppColors.primary,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ),
                            ],
                          ),
                        ),
                      ),

                      // Quick filters
                      SliverToBoxAdapter(
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
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
                                const SizedBox(width: 8),
                                _buildFilterChip('credit', l10n.credit),
                              ],
                            ),
                          ),
                        ),
                      ),

                      // Transaction list
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

  // ──────────────────── PERIOD COMPARISON ────────────────────

  Widget _buildPeriodComparison(AppLocalizations l10n) {
    final comparison = _insights!['period_comparison'] as Map<String, dynamic>?;
    if (comparison == null) return const SliverToBoxAdapter(child: SizedBox.shrink());

    final today = comparison['today'] as Map<String, dynamic>? ?? {};
    final week = comparison['this_week'] as Map<String, dynamic>? ?? {};
    final month = comparison['this_month'] as Map<String, dynamic>? ?? {};
    final avgTx = _insights!['average_transaction_value'] as Map<String, dynamic>? ?? {};

    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
        child: Column(
          children: [
            // Today + Avg row
            Row(
              children: [
                Expanded(
                  child: _buildStatCard(
                    title: l10n.today,
                    value: _currencyFormat.format((today['total'] ?? 0).toDouble()),
                    subtitle: '${today['count'] ?? 0} ${l10n.sales.toLowerCase()}',
                    change: (today['total_change_pct'] ?? 0).toDouble(),
                    changeLabel: l10n.vsYesterday,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildStatCard(
                    title: l10n.avgSale,
                    value: _currencyFormat.format((avgTx['today'] ?? 0).toDouble()),
                    subtitle: '${l10n.thisMonth}: ${_currencyFormat.format((avgTx['this_month'] ?? 0).toDouble())}',
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            // Week + Month row
            Row(
              children: [
                Expanded(
                  child: _buildStatCard(
                    title: l10n.thisWeek,
                    value: _currencyFormat.format((week['total'] ?? 0).toDouble()),
                    subtitle: '${week['count'] ?? 0} ${l10n.sales.toLowerCase()}',
                    change: (week['total_change_pct'] ?? 0).toDouble(),
                    changeLabel: l10n.vsLastWeek,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildStatCard(
                    title: l10n.thisMonth,
                    value: _currencyFormat.format((month['total'] ?? 0).toDouble()),
                    subtitle: '${month['count'] ?? 0} ${l10n.sales.toLowerCase()}',
                    change: (month['total_change_pct'] ?? 0).toDouble(),
                    changeLabel: l10n.vsLastMonth,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard({
    required String title,
    required String value,
    required String subtitle,
    double? change,
    String? changeLabel,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
          const SizedBox(height: 6),
          Text(
            value,
            style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 4),
          if (change != null && change != 0)
            Row(
              children: [
                Icon(
                  change > 0 ? Icons.arrow_upward : Icons.arrow_downward,
                  size: 12,
                  color: change > 0 ? AppColors.success : AppColors.error,
                ),
                const SizedBox(width: 2),
                Expanded(
                  child: Text(
                    '${change.abs().toStringAsFixed(0)}% ${changeLabel ?? ''}',
                    style: TextStyle(
                      fontSize: 11,
                      color: change > 0 ? AppColors.success : AppColors.error,
                      fontWeight: FontWeight.w500,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            )
          else
            Text(subtitle, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary), maxLines: 1, overflow: TextOverflow.ellipsis),
        ],
      ),
    );
  }

  // ──────────────────── TOP PRODUCTS ────────────────────

  Widget _buildTopProductsSection(AppLocalizations l10n) {
    final topProducts = _insights!['top_products'] as Map<String, dynamic>?;
    final byRevenue = (topProducts?['by_revenue'] as List?) ?? [];
    if (byRevenue.isEmpty) return const SliverToBoxAdapter(child: SizedBox.shrink());

    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2)),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(l10n.topProducts, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
              const SizedBox(height: 4),
              Text(l10n.thisMonth, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
              const SizedBox(height: 12),
              ...byRevenue.take(5).toList().asMap().entries.map((entry) {
                final i = entry.key;
                final product = entry.value as Map<String, dynamic>;
                final name = product['product_name'] ?? '';
                final qty = product['total_quantity'] ?? 0;
                final revenue = (product['total_revenue'] ?? 0).toDouble();
                final maxRevenue = ((byRevenue.first as Map<String, dynamic>)['total_revenue'] ?? 1).toDouble();
                final ratio = maxRevenue > 0 ? revenue / maxRevenue : 0.0;

                return Padding(
                  padding: const EdgeInsets.symmetric(vertical: 5),
                  child: Row(
                    children: [
                      SizedBox(
                        width: 20,
                        child: Text(
                          '${i + 1}',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: i < 3 ? AppColors.primary : AppColors.textSecondary,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500), maxLines: 1, overflow: TextOverflow.ellipsis),
                            const SizedBox(height: 4),
                            ClipRRect(
                              borderRadius: BorderRadius.circular(2),
                              child: LinearProgressIndicator(
                                value: ratio,
                                minHeight: 4,
                                backgroundColor: AppColors.gray5,
                                valueColor: const AlwaysStoppedAnimation<Color>(AppColors.primary),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(_currencyFormat.format(revenue), style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                          Text('$qty sold', style: const TextStyle(fontSize: 10, color: AppColors.textSecondary)),
                        ],
                      ),
                    ],
                  ),
                );
              }),
            ],
          ),
        ),
      ),
    );
  }

  // ──────────────────── PAYMENT BREAKDOWN ────────────────────

  Widget _buildPaymentBreakdown(AppLocalizations l10n) {
    final methods = (_insights!['payment_methods'] as List?) ?? [];
    if (methods.isEmpty) return const SliverToBoxAdapter(child: SizedBox.shrink());

    // Sort by total descending
    final sorted = List<Map<String, dynamic>>.from(
      methods.map((e) => Map<String, dynamic>.from(e as Map)),
    )..sort((a, b) => ((b['total'] ?? 0) as num).compareTo((a['total'] ?? 0) as num));

    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2)),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(l10n.paymentBreakdown, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
              const SizedBox(height: 4),
              Text(l10n.thisMonth, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
              const SizedBox(height: 14),
              ...sorted.map((data) {
                final method = data['method'] ?? '';
                final count = data['count'] ?? 0;
                final pct = (data['percentage'] ?? 0).toDouble();
                final color = _getPaymentMethodColor(method);

                return Padding(
                  padding: const EdgeInsets.symmetric(vertical: 5),
                  child: Row(
                    children: [
                      Container(
                        width: 32,
                        height: 32,
                        decoration: BoxDecoration(
                          color: color.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Icon(_getPaymentMethodIcon(method), color: color, size: 16),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(_getPaymentLabel(method), style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
                            const SizedBox(height: 4),
                            ClipRRect(
                              borderRadius: BorderRadius.circular(2),
                              child: LinearProgressIndicator(
                                value: pct / 100,
                                minHeight: 4,
                                backgroundColor: AppColors.gray5,
                                valueColor: AlwaysStoppedAnimation<Color>(color),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text('${pct.toStringAsFixed(0)}%', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                          Text('$count', style: const TextStyle(fontSize: 10, color: AppColors.textSecondary)),
                        ],
                      ),
                    ],
                  ),
                );
              }),
            ],
          ),
        ),
      ),
    );
  }

  // ──────────────────── DISCOUNT STATS ────────────────────

  Widget _buildDiscountStats(AppLocalizations l10n) {
    final stats = _insights!['discount_stats'] as Map<String, dynamic>?;
    if (stats == null) return const SliverToBoxAdapter(child: SizedBox.shrink());

    final totalDiscount = (stats['total_discounts'] ?? 0).toDouble();
    final discountedCount = stats['discounted_sales_count'] ?? 0;
    final avgDiscount = (stats['avg_discount_per_sale'] ?? 0).toDouble();
    final discountRate = (stats['discount_rate_pct'] ?? 0).toDouble();

    if (totalDiscount == 0) return const SliverToBoxAdapter(child: SizedBox.shrink());

    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2)),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: AppColors.warning.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(Icons.local_offer, color: AppColors.warning, size: 16),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(l10n.discounts, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                  ),
                  Text('${discountRate.toStringAsFixed(1)}%', style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                ],
              ),
              const SizedBox(height: 14),
              Row(
                children: [
                  Expanded(child: _buildMiniStat(_currencyFormat.format(totalDiscount), l10n.givenAway)),
                  Expanded(child: _buildMiniStat('$discountedCount', l10n.discountedSales)),
                  Expanded(child: _buildMiniStat(_currencyFormat.format(avgDiscount), l10n.avgDiscount)),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMiniStat(String value, String label) {
    return Column(
      children: [
        Text(value, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.textPrimary), maxLines: 1, overflow: TextOverflow.ellipsis),
        const SizedBox(height: 2),
        Text(label, style: const TextStyle(fontSize: 10, color: AppColors.textSecondary), textAlign: TextAlign.center, maxLines: 1),
      ],
    );
  }

  // ──────────────────── PEAK HOURS ────────────────────

  Widget _buildPeakHours(AppLocalizations l10n) {
    final peakHours = (_insights!['peak_hours'] as List?) ?? [];
    if (peakHours.isEmpty) return const SliverToBoxAdapter(child: SizedBox.shrink());

    final top5 = peakHours.take(5).toList();
    final maxCount = (top5.first as Map<String, dynamic>)['count'] ?? 1;

    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2)),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: AppColors.accent.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(Icons.schedule, color: AppColors.accent, size: 16),
                  ),
                  const SizedBox(width: 10),
                  Text(l10n.peakHours, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                  const Spacer(),
                  Text(l10n.today, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                ],
              ),
              const SizedBox(height: 14),
              ...top5.map((hour) {
                final data = hour as Map<String, dynamic>;
                final label = data['label'] ?? '';
                final count = data['count'] ?? 0;
                final total = (data['total'] ?? 0).toDouble();
                final ratio = maxCount > 0 ? count / maxCount : 0.0;

                return Padding(
                  padding: const EdgeInsets.symmetric(vertical: 4),
                  child: Row(
                    children: [
                      SizedBox(
                        width: 90,
                        child: Text(label, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                      ),
                      Expanded(
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(2),
                          child: LinearProgressIndicator(
                            value: ratio.toDouble(),
                            minHeight: 6,
                            backgroundColor: AppColors.gray5,
                            valueColor: const AlwaysStoppedAnimation<Color>(AppColors.accent),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      SizedBox(
                        width: 70,
                        child: Text(
                          '$count (${_compactFormat.format(total)})',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500),
                          textAlign: TextAlign.right,
                        ),
                      ),
                    ],
                  ),
                );
              }),
            ],
          ),
        ),
      ),
    );
  }

  // ──────────────────── CUSTOMER INSIGHTS ────────────────────

  Widget _buildCustomerInsights(AppLocalizations l10n) {
    final stats = _insights!['customer_stats'] as Map<String, dynamic>?;
    if (stats == null) return const SliverToBoxAdapter(child: SizedBox.shrink());

    final registered = stats['registered_customer_sales'] ?? 0;
    final walkIns = stats['walk_in_sales'] ?? 0;
    final returningPct = (stats['returning_customer_pct'] ?? 0).toDouble();
    final topCustomers = (stats['top_customers'] as List?) ?? [];

    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2)),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(l10n.customerInsights, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
              const SizedBox(height: 4),
              Text(l10n.thisMonth, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
              const SizedBox(height: 14),
              // Returning vs Walk-ins
              Row(
                children: [
                  Expanded(
                    child: _buildMiniStat('$registered (${returningPct.toStringAsFixed(0)}%)', l10n.returning),
                  ),
                  Expanded(
                    child: _buildMiniStat('$walkIns', l10n.walkIns),
                  ),
                ],
              ),
              if (topCustomers.isNotEmpty) ...[
                const SizedBox(height: 14),
                const Divider(height: 1),
                const SizedBox(height: 10),
                Text(l10n.topCustomers, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: AppColors.textSecondary)),
                const SizedBox(height: 8),
                ...topCustomers.take(3).map((c) {
                  final customer = c as Map<String, dynamic>;
                  final name = customer['customer_name'] ?? '';
                  final total = (customer['total_spent'] ?? 0).toDouble();
                  final count = customer['transaction_count'] ?? 0;

                  return Padding(
                    padding: const EdgeInsets.symmetric(vertical: 4),
                    child: Row(
                      children: [
                        Container(
                          width: 28,
                          height: 28,
                          decoration: BoxDecoration(
                            color: AppColors.primary.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(14),
                          ),
                          child: Center(
                            child: Text(
                              name.isNotEmpty ? name[0].toUpperCase() : '?',
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.primary),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500), maxLines: 1, overflow: TextOverflow.ellipsis),
                              Text('$count ${l10n.transactions.toLowerCase()}', style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                            ],
                          ),
                        ),
                        Text(_currencyFormat.format(total), style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                      ],
                    ),
                  );
                }),
              ],
            ],
          ),
        ),
      ),
    );
  }

  // ──────────────────── MARGIN ALERTS ────────────────────

  Widget _buildMarginAlerts(AppLocalizations l10n) {
    final alerts = (_insights!['low_margin_alerts'] as List?) ?? [];
    if (alerts.isEmpty) return const SliverToBoxAdapter(child: SizedBox.shrink());

    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.error.withValues(alpha: 0.2)),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2)),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: AppColors.error.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(Icons.warning_amber, color: AppColors.error, size: 16),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(l10n.marginAlerts, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              ...alerts.take(5).map((alert) {
                final data = alert as Map<String, dynamic>;
                final name = data['product_name'] ?? '';
                final avgSelling = (data['avg_selling_price'] ?? 0).toDouble();
                final avgCost = (data['avg_cost_price'] ?? 0).toDouble();
                final totalLoss = (data['total_loss'] ?? 0).toDouble();
                final qty = data['total_quantity_sold'] ?? 0;

                return Padding(
                  padding: const EdgeInsets.symmetric(vertical: 5),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500), maxLines: 1, overflow: TextOverflow.ellipsis),
                            Text(
                              '${l10n.soldBelowCost}: ${_currencyFormat.format(avgSelling)} < ${_currencyFormat.format(avgCost)} ($qty pcs)',
                              style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        '-${_currencyFormat.format(totalLoss.abs())}',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.error),
                      ),
                    ],
                  ),
                );
              }),
            ],
          ),
        ),
      ),
    );
  }

  // ──────────────────── TRANSACTION LIST ────────────────────

  List<Widget> _buildGroupedList() {
    final groups = _groupByDate();
    final slivers = <Widget>[];

    for (final entry in groups.entries) {
      slivers.add(
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: Text(
              entry.key,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textSecondary),
            ),
          ),
        ),
      );

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
                      Text(tx.transactionNumber, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                      const Spacer(),
                      Text(time, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
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

  // ──────────────────── HELPERS ────────────────────

  String _getPaymentLabel(String method) {
    switch (method) {
      case 'cash': return 'Cash';
      case 'card': return 'Card';
      case 'mobile': return 'Mobile Money';
      case 'bank_transfer': return 'Bank Transfer';
      case 'credit': return 'Credit';
      default: return method;
    }
  }

  IconData _getPaymentMethodIcon(String method) {
    switch (method) {
      case 'cash': return Icons.payments;
      case 'card': return Icons.credit_card;
      case 'mobile': return Icons.phone_android;
      case 'bank_transfer': return Icons.account_balance;
      case 'credit': return Icons.account_balance_wallet;
      default: return Icons.payment;
    }
  }

  Color _getPaymentMethodColor(String method) {
    switch (method) {
      case 'cash': return AppColors.cash;
      case 'card': return AppColors.card;
      case 'mobile': return AppColors.mobile;
      case 'bank_transfer': return AppColors.bankTransfer;
      case 'credit': return AppColors.warning;
      default: return AppColors.primary;
    }
  }
}
