import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../shared/models/customer.dart';
import '../data/customer_providers.dart';

class CustomersScreen extends ConsumerStatefulWidget {
  const CustomersScreen({super.key});

  @override
  ConsumerState<CustomersScreen> createState() => _CustomersScreenState();
}

class _CustomersScreenState extends ConsumerState<CustomersScreen> {
  final _searchController = TextEditingController();
  final _currencyFormat = NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(customersProvider.notifier).loadCustomers(refresh: true);
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _onSearch(String query) {
    ref.read(customersProvider.notifier).loadCustomers(
      search: query.isNotEmpty ? query : null,
      refresh: true,
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final state = ref.watch(customersProvider);

    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: Text(l10n.customers),
        centerTitle: true,
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final result = await context.push('/customers/add');
          if (result == true) {
            ref.read(customersProvider.notifier).loadCustomers(refresh: true);
          }
        },
        child: const Icon(Icons.person_add),
      ),
      body: Column(
        children: [
          // Search bar
          Container(
            color: Colors.white,
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              onChanged: _onSearch,
              decoration: InputDecoration(
                hintText: l10n.searchCustomers,
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _searchController.clear();
                          _onSearch('');
                        },
                      )
                    : null,
                filled: true,
                fillColor: AppColors.gray6,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
          ),

          // Summary
          if (!state.isLoading && state.customers.isNotEmpty)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Row(
                children: [
                  Text(
                    '${state.total} ${l10n.customers.toLowerCase()}',
                    style: const TextStyle(
                      color: AppColors.textSecondary,
                      fontSize: 13,
                    ),
                  ),
                ],
              ),
            ),

          // List
          Expanded(
            child: state.isLoading && state.customers.isEmpty
                ? const Center(child: CircularProgressIndicator())
                : state.error != null && state.customers.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(l10n.failedToLoad),
                            const SizedBox(height: 8),
                            ElevatedButton(
                              onPressed: () => ref
                                  .read(customersProvider.notifier)
                                  .loadCustomers(refresh: true),
                              child: Text(l10n.retry),
                            ),
                          ],
                        ),
                      )
                    : state.customers.isEmpty
                        ? Center(
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.people_outlined, size: 64, color: AppColors.gray3),
                                const SizedBox(height: 16),
                                Text(l10n.noCustomers),
                                const SizedBox(height: 8),
                                ElevatedButton.icon(
                                  onPressed: () => context.push('/customers/add'),
                                  icon: const Icon(Icons.person_add),
                                  label: Text(l10n.addCustomer),
                                ),
                              ],
                            ),
                          )
                        : RefreshIndicator(
                            onRefresh: () => ref
                                .read(customersProvider.notifier)
                                .loadCustomers(
                                  search: _searchController.text.isNotEmpty
                                      ? _searchController.text
                                      : null,
                                  refresh: true,
                                ),
                            child: ListView.builder(
                              padding: const EdgeInsets.symmetric(horizontal: 16),
                              itemCount: state.customers.length,
                              itemBuilder: (context, index) {
                                final customer = state.customers[index];
                                return _buildCustomerCard(customer);
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }

  Widget _buildCustomerCard(Customer customer) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: ListTile(
        onTap: () async {
          final result = await context.push('/customers/${customer.id}');
          if (result == true) {
            ref.read(customersProvider.notifier).loadCustomers(refresh: true);
          }
        },
        leading: CircleAvatar(
          backgroundColor: AppColors.primary.withValues(alpha: 0.1),
          child: Text(
            customer.name.isNotEmpty ? customer.name[0].toUpperCase() : '?',
            style: const TextStyle(
              color: AppColors.primary,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
        title: Text(
          customer.name,
          style: const TextStyle(fontWeight: FontWeight.w500),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(customer.phone, style: const TextStyle(fontSize: 13)),
            if (customer.hasOutstandingBalance)
              Text(
                '${AppLocalizations.of(context)!.creditBalance}: ${_currencyFormat.format(customer.currentBalance)}',
                style: const TextStyle(
                  fontSize: 12,
                  color: AppColors.warning,
                  fontWeight: FontWeight.w500,
                ),
              ),
          ],
        ),
        trailing: customer.hasCredit
            ? Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.primary.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  AppLocalizations.of(context)!.credit,
                  style: const TextStyle(
                    fontSize: 11,
                    color: AppColors.primary,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              )
            : null,
      ),
    );
  }
}
