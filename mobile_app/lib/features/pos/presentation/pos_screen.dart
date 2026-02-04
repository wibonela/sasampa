import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';
import '../../../shared/models/product.dart';
import 'checkout_sheet.dart';

class POSScreen extends ConsumerStatefulWidget {
  const POSScreen({super.key});

  @override
  ConsumerState<POSScreen> createState() => _POSScreenState();
}

class _POSScreenState extends ConsumerState<POSScreen> {
  final _searchController = TextEditingController();
  final _currencyFormat = NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);

  @override
  void initState() {
    super.initState();
    // Load data after frame to avoid Riverpod errors
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadDataIfNeeded();
    });
  }

  Future<void> _loadDataIfNeeded() async {
    final productsState = ref.read(productsProvider);
    // Only load if products are empty (not already loaded)
    if (productsState.products.isEmpty) {
      await _loadData();
    }
  }

  Future<void> _loadData() async {
    final productsNotifier = ref.read(productsProvider.notifier);
    await Future.wait([
      productsNotifier.loadProducts(),
      productsNotifier.loadCategories(),
    ]);
  }

  void _addToCart(Product product) {
    if (!product.isInStock) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Product is out of stock'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    ref.read(cartProvider.notifier).addProduct(product);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('${product.name} added to cart'),
        duration: const Duration(seconds: 1),
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  void _showCheckout() {
    final cart = ref.read(cartProvider);
    if (cart.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Cart is empty'),
          backgroundColor: AppColors.warning,
        ),
      );
      return;
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => const CheckoutSheet(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final productsState = ref.watch(productsProvider);
    final cart = ref.watch(cartProvider);

    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: const Text('Point of Sale'),
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.qr_code_scanner),
            onPressed: () {
              // TODO: Implement barcode scanner
            },
          ),
        ],
      ),
      body: Column(
        children: [
          // Search Bar
          Container(
            color: Colors.white,
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: TextField(
              controller: _searchController,
              onChanged: (value) {
                ref.read(productsProvider.notifier).setSearchQuery(value);
              },
              decoration: InputDecoration(
                hintText: 'Search products...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _searchController.clear();
                          ref.read(productsProvider.notifier).setSearchQuery('');
                        },
                      )
                    : null,
              ),
            ),
          ),

          // Categories
          if (productsState.categories.isNotEmpty)
            Container(
              height: 44,
              color: Colors.white,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 12),
                itemCount: productsState.categories.length + 1,
                itemBuilder: (context, index) {
                  if (index == 0) {
                    return _buildCategoryChip(
                      label: 'All',
                      isSelected: productsState.selectedCategoryId == null,
                      onTap: () => ref.read(productsProvider.notifier).setCategory(null),
                    );
                  }
                  final category = productsState.categories[index - 1];
                  return _buildCategoryChip(
                    label: category.name,
                    isSelected: productsState.selectedCategoryId == category.id,
                    onTap: () => ref.read(productsProvider.notifier).setCategory(category.id),
                  );
                },
              ),
            ),

          const SizedBox(height: 8),

          // Products Grid
          Expanded(
            child: productsState.isLoading
                ? const Center(child: CircularProgressIndicator())
                : productsState.filteredProducts.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.inventory_2_outlined, size: 64, color: AppColors.gray3),
                            const SizedBox(height: 16),
                            Text(
                              productsState.searchQuery.isNotEmpty
                                  ? 'No products found'
                                  : 'No products available',
                              style: const TextStyle(color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _loadData,
                        child: GridView.builder(
                          padding: const EdgeInsets.all(12),
                          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            childAspectRatio: 0.8,
                            crossAxisSpacing: 12,
                            mainAxisSpacing: 12,
                          ),
                          itemCount: productsState.filteredProducts.length,
                          itemBuilder: (context, index) {
                            final product = productsState.filteredProducts[index];
                            return _buildProductCard(product);
                          },
                        ),
                      ),
          ),
        ],
      ),

      // Cart FAB
      floatingActionButton: cart.isNotEmpty
          ? GestureDetector(
              onTap: _showCheckout,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                decoration: BoxDecoration(
                  color: AppColors.primary,
                  borderRadius: BorderRadius.circular(30),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.primary.withOpacity(0.3),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        '${cart.itemCount}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Text(
                      _currencyFormat.format(cart.total),
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    const SizedBox(width: 8),
                    const Icon(Icons.arrow_forward, color: Colors.white),
                  ],
                ),
              ),
            )
          : null,
      floatingActionButtonLocation: FloatingActionButtonLocation.centerFloat,
    );
  }

  Widget _buildCategoryChip({
    required String label,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4),
      child: FilterChip(
        label: Text(label),
        selected: isSelected,
        onSelected: (_) => onTap(),
        selectedColor: AppColors.primary.withOpacity(0.15),
        checkmarkColor: AppColors.primary,
        labelStyle: TextStyle(
          color: isSelected ? AppColors.primary : AppColors.textSecondary,
          fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
        ),
      ),
    );
  }

  Widget _buildProductCard(Product product) {
    final cartItem = ref.read(cartProvider).getItem(product.id);
    final inCart = cartItem != null;

    return GestureDetector(
      onTap: () => _addToCart(product),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: inCart ? Border.all(color: AppColors.primary, width: 2) : null,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Image / Placeholder
            Expanded(
              child: Container(
                decoration: BoxDecoration(
                  color: AppColors.gray6,
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
                ),
                child: Stack(
                  children: [
                    Center(
                      child: product.imageUrl != null
                          ? Image.network(
                              product.imageUrl!,
                              fit: BoxFit.cover,
                              errorBuilder: (_, __, ___) => const Icon(
                                Icons.inventory_2_outlined,
                                size: 40,
                                color: AppColors.gray3,
                              ),
                            )
                          : const Icon(
                              Icons.inventory_2_outlined,
                              size: 40,
                              color: AppColors.gray3,
                            ),
                    ),
                    // Stock Badge
                    if (!product.isInStock)
                      Positioned(
                        top: 8,
                        right: 8,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.error,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: const Text(
                            'Out of Stock',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      )
                    else if (product.isLowStock)
                      Positioned(
                        top: 8,
                        right: 8,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.warning,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            'Low: ${product.stock}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ),
                    // Cart quantity
                    if (inCart)
                      Positioned(
                        top: 8,
                        left: 8,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.primary,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            '${cartItem.quantity}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
              ),
            ),
            // Details
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontWeight: FontWeight.w500,
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    _currencyFormat.format(product.sellingPrice),
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 15,
                      color: AppColors.primary,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
