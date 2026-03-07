import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';
import '../../../core/utils/error_utils.dart';
import '../../../shared/models/transaction.dart';

class OrdersScreen extends ConsumerStatefulWidget {
  const OrdersScreen({super.key});

  @override
  ConsumerState<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends ConsumerState<OrdersScreen> {
  List<Transaction> _orders = [];
  bool _isLoading = true;
  bool _isLoadingMore = false;
  String? _error;
  String _statusFilter = 'pending';
  String _searchQuery = '';
  int _currentPage = 1;
  bool _hasMore = true;
  final _searchController = TextEditingController();
  final _scrollController = ScrollController();
  final _currencyFormat = NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);

  @override
  void initState() {
    super.initState();
    _loadOrders();
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
      final response = await api.getOrders(
        status: _statusFilter == 'all' ? null : _statusFilter,
        search: _searchQuery.isNotEmpty ? _searchQuery : null,
        page: _currentPage + 1,
      );
      final data = response.data;
      final newItems = (data['data'] as List)
          .map((e) => Transaction.fromJson(e))
          .toList();

      setState(() {
        _orders.addAll(newItems);
        _currentPage++;
        _hasMore = newItems.length >= 20;
        _isLoadingMore = false;
      });
    } catch (_) {
      setState(() => _isLoadingMore = false);
    }
  }

  Future<void> _loadOrders() async {
    setState(() {
      _isLoading = true;
      _error = null;
      _currentPage = 1;
      _hasMore = true;
    });

    try {
      final api = ref.read(apiClientProvider);
      final response = await api.getOrders(
        status: _statusFilter == 'all' ? null : _statusFilter,
        search: _searchQuery.isNotEmpty ? _searchQuery : null,
      );
      final data = response.data;

      setState(() {
        _orders = (data['data'] as List)
            .map((e) => Transaction.fromJson(e))
            .toList();
        _hasMore = _orders.length >= 20;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = extractErrorMessage(e, 'Failed to load orders');
        _isLoading = false;
      });
    }
  }

  Color _getStatusColor(String status) {
    return switch (status) {
      'pending' => Colors.orange,
      'completed' => AppColors.success,
      'cancelled' => AppColors.error,
      _ => AppColors.textSecondary,
    };
  }

  String _getStatusLabel(String status) {
    final l10n = AppLocalizations.of(context)!;
    return switch (status) {
      'pending' => l10n.pending,
      'completed' => l10n.completed,
      'cancelled' => l10n.cancelled,
      _ => status,
    };
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
                      _loadOrders();
                    },
                  ),
                ),
                onSubmitted: (value) {
                  setState(() => _searchQuery = value);
                  _loadOrders();
                },
                style: const TextStyle(fontSize: 16),
              )
            : Text(l10n.orders),
        centerTitle: _searchController.text.isEmpty,
        actions: [
          if (_searchController.text.isEmpty)
            IconButton(
              icon: const Icon(Icons.search),
              onPressed: () {
                setState(() => _searchController.text = ' ');
                _searchController.text = '';
              },
            ),
        ],
      ),
      body: Column(
        children: [
          // Filter tabs
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              children: [
                _buildFilterChip('pending', l10n.pending),
                const SizedBox(width: 8),
                _buildFilterChip('all', l10n.all),
                const SizedBox(width: 8),
                _buildFilterChip('completed', l10n.completed),
                const SizedBox(width: 8),
                _buildFilterChip('cancelled', l10n.cancelled),
              ],
            ),
          ),
          // Orders list
          Expanded(
            child: RefreshIndicator(
              onRefresh: _loadOrders,
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
                                onPressed: _loadOrders,
                                child: Text(l10n.retry),
                              ),
                            ],
                          ),
                        )
                      : _orders.isEmpty
                          ? Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const Icon(Icons.assignment_outlined, size: 64, color: AppColors.gray3),
                                  const SizedBox(height: 16),
                                  Text(
                                    l10n.noOrders,
                                    style: const TextStyle(color: AppColors.textSecondary),
                                  ),
                                ],
                              ),
                            )
                          : ListView.builder(
                              controller: _scrollController,
                              padding: const EdgeInsets.all(16),
                              itemCount: _orders.length + (_isLoadingMore ? 1 : 0),
                              itemBuilder: (context, index) {
                                if (index == _orders.length) {
                                  return const Padding(
                                    padding: EdgeInsets.all(16),
                                    child: Center(child: CircularProgressIndicator()),
                                  );
                                }
                                return _buildOrderItem(_orders[index]);
                              },
                            ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String value, String label) {
    final selected = _statusFilter == value;
    return ChoiceChip(
      label: Text(label),
      selected: selected,
      onSelected: (_) {
        setState(() => _statusFilter = value);
        _loadOrders();
      },
      selectedColor: AppColors.primary,
      labelStyle: TextStyle(
        color: selected ? Colors.white : AppColors.textPrimary,
        fontSize: 13,
      ),
      showCheckmark: false,
    );
  }

  Widget _buildOrderItem(Transaction order) {
    final statusColor = _getStatusColor(order.status);

    return GestureDetector(
      onTap: () async {
        await context.push('/orders/${order.id}');
        _loadOrders();
      },
      child: Container(
        margin: const EdgeInsets.only(bottom: 8),
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
                color: statusColor.withOpacity(0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(
                Icons.assignment_outlined,
                color: statusColor,
                size: 20,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          order.transactionNumber,
                          style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 12),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: statusColor.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          _getStatusLabel(order.status),
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                            color: statusColor,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        _currencyFormat.format(order.total),
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                          color: AppColors.primary,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      if (order.customerName != null) ...[
                        Icon(Icons.person_outline, size: 12, color: AppColors.textSecondary),
                        const SizedBox(width: 2),
                        Expanded(
                          child: Text(
                            order.customerName!,
                            style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                      Text(
                        order.createdAtHuman ?? '',
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
}
