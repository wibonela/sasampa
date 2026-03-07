import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';
import '../../../core/utils/error_utils.dart';

class InventoryScreen extends ConsumerStatefulWidget {
  const InventoryScreen({super.key});

  @override
  ConsumerState<InventoryScreen> createState() => _InventoryScreenState();
}

class _InventoryScreenState extends ConsumerState<InventoryScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = true;
  bool _isLoadingMore = false;
  List<Map<String, dynamic>> _products = [];
  Map<String, dynamic>? _summary;
  String _searchQuery = '';
  int _currentPage = 1;
  bool _hasMore = true;
  final _searchController = TextEditingController();
  final _scrollController = ScrollController();
  final _currencyFormat = NumberFormat('#,###');

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _tabController.addListener(_onTabChanged);
    _scrollController.addListener(_onScroll);
    _loadData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_tabController.index == 0 &&
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
      final response = await api.getInventory(
        lowStock: _tabController.index == 1 ? true : null,
        outOfStock: _tabController.index == 2 ? true : null,
        search: _searchQuery.isNotEmpty ? _searchQuery : null,
        page: _currentPage + 1,
      );

      final newItems = List<Map<String, dynamic>>.from(
        response.data['data'] ?? [],
      );

      setState(() {
        _products.addAll(newItems);
        _currentPage++;
        _hasMore = newItems.length >= 50;
        _isLoadingMore = false;
      });
    } catch (_) {
      setState(() => _isLoadingMore = false);
    }
  }

  void _onTabChanged() {
    if (!_tabController.indexIsChanging) {
      _loadData();
    }
  }

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _currentPage = 1;
      _hasMore = true;
    });
    try {
      final api = ref.read(apiClientProvider);

      // Load summary
      final summaryResponse = await api.getInventorySummary();

      // Load products based on current tab
      final productsResponse = await api.getInventory(
        lowStock: _tabController.index == 1 ? true : null,
        outOfStock: _tabController.index == 2 ? true : null,
        search: _searchQuery.isNotEmpty ? _searchQuery : null,
      );

      setState(() {
        _summary = summaryResponse.data['data'] as Map<String, dynamic>?;
        _products = List<Map<String, dynamic>>.from(
          productsResponse.data['data'] ?? [],
        );
        _hasMore = _tabController.index == 0 && _products.length >= 50;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(extractErrorMessage(e, 'Failed to load inventory')),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<void> _adjustStock(Map<String, dynamic> product) async {
    final quantityController = TextEditingController();
    String adjustmentType = 'add';
    String? reason;

    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        final l10n = AppLocalizations.of(context)!;
        final reasons = [
          ('Stock In', l10n.reasonStockIn),
          ('Stock Out', l10n.reasonStockOut),
          ('Damaged', l10n.reasonDamaged),
          ('Lost', l10n.reasonLost),
          ('Returned', l10n.reasonReturned),
          ('Correction', l10n.reasonCorrection),
          ('Other', l10n.reasonOther),
        ];
        return StatefulBuilder(
          builder: (context, setModalState) => Padding(
            padding: EdgeInsets.only(
              left: 20,
              right: 20,
              top: 20,
              bottom: MediaQuery.of(context).viewInsets.bottom + 20,
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        l10n.adjustStock,
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                      ),
                    ),
                    IconButton(
                      onPressed: () => Navigator.pop(context),
                      icon: const Icon(Icons.close),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  product['product_name']?.toString() ?? product['name']?.toString() ?? l10n.unknownProduct,
                  style: const TextStyle(
                    fontSize: 16,
                    color: AppColors.textSecondary,
                  ),
                ),
                Text(
                  '${l10n.currentStock}: ${product['quantity'] ?? product['stock'] ?? 0}',
                  style: TextStyle(
                    fontSize: 14,
                    color: AppColors.textSecondary,
                  ),
                ),
                const SizedBox(height: 20),

                // Adjustment Type
                Row(
                  children: [
                    Expanded(
                      child: GestureDetector(
                        onTap: () => setModalState(() => adjustmentType = 'add'),
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 16),
                          decoration: BoxDecoration(
                            color: adjustmentType == 'add'
                                ? AppColors.success.withValues(alpha: 0.1)
                                : AppColors.gray6,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                              color: adjustmentType == 'add'
                                  ? AppColors.success
                                  : Colors.transparent,
                              width: 2,
                            ),
                          ),
                          child: Column(
                            children: [
                              Icon(
                                Icons.add_circle_outline,
                                color: adjustmentType == 'add'
                                    ? AppColors.success
                                    : AppColors.gray3,
                              ),
                              const SizedBox(height: 4),
                              Text(
                                l10n.addStock,
                                style: TextStyle(
                                  fontWeight: adjustmentType == 'add'
                                      ? FontWeight.w600
                                      : FontWeight.normal,
                                  color: adjustmentType == 'add'
                                      ? AppColors.success
                                      : AppColors.textPrimary,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: GestureDetector(
                        onTap: () =>
                            setModalState(() => adjustmentType = 'subtract'),
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 16),
                          decoration: BoxDecoration(
                            color: adjustmentType == 'subtract'
                                ? AppColors.error.withValues(alpha: 0.1)
                                : AppColors.gray6,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                              color: adjustmentType == 'subtract'
                                  ? AppColors.error
                                  : Colors.transparent,
                              width: 2,
                            ),
                          ),
                          child: Column(
                            children: [
                              Icon(
                                Icons.remove_circle_outline,
                                color: adjustmentType == 'subtract'
                                    ? AppColors.error
                                    : AppColors.gray3,
                              ),
                              const SizedBox(height: 4),
                              Text(
                                l10n.removeStock,
                                style: TextStyle(
                                  fontWeight: adjustmentType == 'subtract'
                                      ? FontWeight.w600
                                      : FontWeight.normal,
                                  color: adjustmentType == 'subtract'
                                      ? AppColors.error
                                      : AppColors.textPrimary,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 20),

                // Quantity
                TextField(
                  controller: quantityController,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(
                    labelText: l10n.quantity,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
                const SizedBox(height: 16),

                // Reason
                DropdownButtonFormField<String>(
                  value: reason,
                  decoration: InputDecoration(
                    labelText: l10n.voidReason,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  items: reasons.map((r) {
                    return DropdownMenuItem(value: r.$1, child: Text(r.$2));
                  }).toList(),
                  onChanged: (v) => setModalState(() => reason = v),
                ),
                const SizedBox(height: 24),

                // Save Button
                SizedBox(
                  width: double.infinity,
                  height: 50,
                  child: ElevatedButton(
                    onPressed: () {
                      if (quantityController.text.isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text(l10n.enterQuantity)),
                        );
                        return;
                      }
                      Navigator.pop(context, true);
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: adjustmentType == 'add'
                          ? AppColors.success
                          : AppColors.error,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: Text(l10n.saveAdjustment),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );

    if (result == true && quantityController.text.isNotEmpty) {
      try {
        final api = ref.read(apiClientProvider);
        final productId = product['product_id'] ?? product['id'];
        // Map UI type to API type
        final apiType = adjustmentType == 'add' ? 'received' : 'damaged';
        await api.adjustStock(
          productId,
          apiType,
          int.parse(quantityController.text),
          reason,
        );

        if (mounted) {
          final l10n = AppLocalizations.of(context)!;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(l10n.stockAdjusted),
              backgroundColor: AppColors.success,
            ),
          );
          _loadData();
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(extractErrorMessage(e, 'Failed to adjust stock')),
              backgroundColor: AppColors.error,
            ),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: Text(l10n.inventory),
        centerTitle: true,
        bottom: TabBar(
          controller: _tabController,
          tabs: [
            Tab(text: '${l10n.all} (${_summary?['total_products'] ?? 0})'),
            Tab(text: '${l10n.lowStock} (${_summary?['low_stock_count'] ?? 0})'),
            Tab(text: '${l10n.outOfStock} (${_summary?['out_of_stock_count'] ?? 0})'),
          ],
        ),
      ),
      body: Column(
        children: [
          // Summary Cards
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Row(
              children: [
                Expanded(
                  child: _buildStatCard(
                    l10n.totalValue,
                    'TZS ${_currencyFormat.format(_summary?['total_value'] ?? 0)}',
                    Icons.inventory_2_outlined,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildStatCard(
                    l10n.totalItems,
                    '${_summary?['total_stock'] ?? 0}',
                    Icons.numbers,
                  ),
                ),
              ],
            ),
          ),

          // Search Bar
          Container(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: l10n.searchProducts,
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _searchQuery.isNotEmpty
                    ? IconButton(
                        onPressed: () {
                          _searchController.clear();
                          setState(() => _searchQuery = '');
                          _loadData();
                        },
                        icon: const Icon(Icons.clear),
                      )
                    : null,
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
              ),
              onSubmitted: (value) {
                setState(() => _searchQuery = value);
                _loadData();
              },
            ),
          ),

          // Product List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : RefreshIndicator(
                    onRefresh: _loadData,
                    child: _products.isEmpty
                        ? Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.inventory_2_outlined,
                                  size: 64,
                                  color: AppColors.gray4,
                                ),
                                const SizedBox(height: 16),
                                Text(
                                  _tabController.index == 1
                                      ? l10n.noLowStockItems
                                      : _tabController.index == 2
                                          ? l10n.noOutOfStockItems
                                          : l10n.noProductsFound,
                                  style: const TextStyle(
                                    color: AppColors.textSecondary,
                                    fontSize: 16,
                                  ),
                                ),
                              ],
                            ),
                          )
                        : ListView.builder(
                            controller: _scrollController,
                            padding: const EdgeInsets.symmetric(horizontal: 16),
                            itemCount: _products.length + (_isLoadingMore ? 1 : 0),
                            itemBuilder: (context, index) {
                              if (index == _products.length) {
                                return const Padding(
                                  padding: EdgeInsets.all(16),
                                  child: Center(child: CircularProgressIndicator()),
                                );
                              }
                              final product = _products[index];
                              return _buildProductItem(product);
                            },
                          ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.gray6,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          Icon(icon, color: AppColors.primary, size: 24),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 12,
                    color: AppColors.textSecondary,
                  ),
                ),
                Text(
                  value,
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 14,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildProductItem(Map<String, dynamic> product) {
    final l10n = AppLocalizations.of(context)!;
    // Handle both inventory API (quantity, product_name) and products API (stock, name)
    final stock = product['quantity'] ?? product['stock'] ?? 0;
    final isLowStock = product['is_low_stock'] ?? false;
    final isOutOfStock = (product['is_out_of_stock'] ?? false) || stock <= 0;
    final name = product['product_name']?.toString() ?? product['name']?.toString() ?? l10n.unknownProduct;
    final sku = product['sku']?.toString() ?? 'N/A';
    final imageUrl = product['image_url']?.toString();
    final sellingPrice = product['selling_price'] ?? 0;

    Color stockColor = AppColors.success;
    if (isOutOfStock) {
      stockColor = AppColors.error;
    } else if (isLowStock) {
      stockColor = AppColors.warning;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.all(12),
        leading: Container(
          width: 50,
          height: 50,
          decoration: BoxDecoration(
            color: AppColors.gray6,
            borderRadius: BorderRadius.circular(8),
            image: imageUrl != null && imageUrl.isNotEmpty
                ? DecorationImage(
                    image: NetworkImage(imageUrl),
                    fit: BoxFit.cover,
                  )
                : null,
          ),
          child: imageUrl == null || imageUrl.isEmpty
              ? const Icon(Icons.inventory_2, color: AppColors.gray3)
              : null,
        ),
        title: Text(
          name,
          style: const TextStyle(fontWeight: FontWeight.w600),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text(
              'SKU: $sku',
              style: TextStyle(
                fontSize: 12,
                color: AppColors.textSecondary,
              ),
            ),
            Text(
              'TZS ${_currencyFormat.format(sellingPrice)}',
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
        trailing: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
              decoration: BoxDecoration(
                color: stockColor.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                '$stock',
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  color: stockColor,
                ),
              ),
            ),
            const SizedBox(height: 4),
            Text(
              isOutOfStock
                  ? l10n.outOfStock
                  : isLowStock
                      ? l10n.lowStock
                      : l10n.inStock,
              style: TextStyle(
                fontSize: 11,
                color: stockColor,
              ),
            ),
          ],
        ),
        onTap: () => _adjustStock(product),
      ),
    );
  }
}
