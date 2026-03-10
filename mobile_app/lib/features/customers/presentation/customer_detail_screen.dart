import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';
import '../../../core/utils/error_utils.dart';
import '../../../shared/models/customer.dart';
import 'record_payment_screen.dart';

class CustomerDetailScreen extends ConsumerStatefulWidget {
  final int customerId;
  const CustomerDetailScreen({super.key, required this.customerId});

  @override
  ConsumerState<CustomerDetailScreen> createState() => _CustomerDetailScreenState();
}

class _CustomerDetailScreenState extends ConsumerState<CustomerDetailScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final _currencyFormat = NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);

  Customer? _customer;
  List<Map<String, dynamic>> _transactions = [];
  List<CustomerCreditTransaction> _creditHistory = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final api = ref.read(apiClientProvider);
      final responses = await Future.wait([
        api.getCustomer(widget.customerId),
        api.getCustomerTransactions(widget.customerId),
        api.getCustomerCreditHistory(widget.customerId),
      ]);

      final customerData = responses[0].data['data'];
      final txData = responses[1].data['data'] as List;
      final creditData = responses[2].data['data'] as List;

      setState(() {
        _customer = Customer.fromJson(customerData);
        _transactions = txData.cast<Map<String, dynamic>>();
        _creditHistory = creditData
            .map((e) => CustomerCreditTransaction.fromJson(e))
            .toList();
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _showRecordPayment() async {
    if (_customer == null) return;

    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => RecordPaymentSheet(customer: _customer!),
    );

    if (result == true) {
      _loadData();
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: Text(l10n.customerDetails),
        centerTitle: true,
        actions: [
          if (_customer != null)
            IconButton(
              icon: const Icon(Icons.edit_outlined),
              onPressed: () async {
                final result = await context.push('/customers/edit/${widget.customerId}');
                if (result == true) _loadData();
              },
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _customer == null
              ? Center(child: Text(l10n.failedToLoad))
              : Column(
                  children: [
                    // Profile header
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(20),
                      color: Colors.white,
                      child: Column(
                        children: [
                          CircleAvatar(
                            radius: 30,
                            backgroundColor: AppColors.primary.withValues(alpha: 0.1),
                            child: Text(
                              _customer!.name[0].toUpperCase(),
                              style: const TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: AppColors.primary,
                              ),
                            ),
                          ),
                          const SizedBox(height: 12),
                          Text(
                            _customer!.name,
                            style: const TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _customer!.phone,
                            style: const TextStyle(color: AppColors.textSecondary),
                          ),
                          if (_customer!.email != null) ...[
                            const SizedBox(height: 2),
                            Text(
                              _customer!.email!,
                              style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
                            ),
                          ],
                        ],
                      ),
                    ),

                    // Credit summary card
                    if (_customer!.hasCredit)
                      Container(
                        margin: const EdgeInsets.all(16),
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.primary.withValues(alpha: 0.2)),
                        ),
                        child: Column(
                          children: [
                            Row(
                              children: [
                                Expanded(
                                  child: _buildCreditStat(
                                    l10n.creditLimit,
                                    _currencyFormat.format(_customer!.creditLimit),
                                    AppColors.textPrimary,
                                  ),
                                ),
                                Container(width: 1, height: 40, color: AppColors.divider),
                                Expanded(
                                  child: _buildCreditStat(
                                    l10n.creditBalance,
                                    _currencyFormat.format(_customer!.currentBalance),
                                    _customer!.hasOutstandingBalance
                                        ? AppColors.warning
                                        : AppColors.success,
                                  ),
                                ),
                                Container(width: 1, height: 40, color: AppColors.divider),
                                Expanded(
                                  child: _buildCreditStat(
                                    l10n.availableCredit,
                                    _currencyFormat.format(_customer!.availableCredit),
                                    AppColors.success,
                                  ),
                                ),
                              ],
                            ),
                            if (_customer!.hasOutstandingBalance) ...[
                              const SizedBox(height: 12),
                              SizedBox(
                                width: double.infinity,
                                child: ElevatedButton.icon(
                                  onPressed: _showRecordPayment,
                                  icon: const Icon(Icons.payment, size: 18),
                                  label: Text(l10n.recordPayment),
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),

                    // Tabs
                    Container(
                      color: Colors.white,
                      child: TabBar(
                        controller: _tabController,
                        tabs: [
                          Tab(text: l10n.purchaseHistory),
                          Tab(text: l10n.creditHistory),
                        ],
                      ),
                    ),

                    // Tab content
                    Expanded(
                      child: TabBarView(
                        controller: _tabController,
                        children: [
                          _buildTransactionsTab(l10n),
                          _buildCreditHistoryTab(l10n),
                        ],
                      ),
                    ),
                  ],
                ),
    );
  }

  Widget _buildCreditStat(String label, String value, Color valueColor) {
    return Column(
      children: [
        Text(
          label,
          style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 13,
            color: valueColor,
          ),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }

  Widget _buildTransactionsTab(AppLocalizations l10n) {
    if (_transactions.isEmpty) {
      return Center(child: Text(l10n.noTransactions));
    }
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _transactions.length,
      itemBuilder: (context, index) {
        final tx = _transactions[index];
        return Card(
          margin: const EdgeInsets.only(bottom: 8),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          child: ListTile(
            onTap: () {
              final id = tx['id'];
              if (id != null) context.push('/transactions/$id');
            },
            leading: Icon(
              tx['payment_method'] == 'credit'
                  ? Icons.credit_score
                  : Icons.receipt_long_outlined,
              color: AppColors.primary,
            ),
            title: Text(
              tx['transaction_number'] ?? '',
              style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 14),
            ),
            subtitle: Text(
              tx['created_at_human'] ?? '',
              style: const TextStyle(fontSize: 12),
            ),
            trailing: Text(
              _currencyFormat.format((tx['total'] ?? 0).toDouble()),
              style: const TextStyle(fontWeight: FontWeight.w600),
            ),
          ),
        );
      },
    );
  }

  Widget _buildCreditHistoryTab(AppLocalizations l10n) {
    if (_creditHistory.isEmpty) {
      return Center(child: Text(l10n.noCreditHistory));
    }
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _creditHistory.length,
      itemBuilder: (context, index) {
        final ct = _creditHistory[index];
        final isPositive = ct.isPayment;

        return Card(
          margin: const EdgeInsets.only(bottom: 8),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          child: ListTile(
            leading: CircleAvatar(
              radius: 18,
              backgroundColor: isPositive
                  ? AppColors.success.withValues(alpha: 0.1)
                  : AppColors.warning.withValues(alpha: 0.1),
              child: Icon(
                isPositive ? Icons.arrow_downward : Icons.arrow_upward,
                size: 18,
                color: isPositive ? AppColors.success : AppColors.warning,
              ),
            ),
            title: Text(
              ct.typeLabel,
              style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 14),
            ),
            subtitle: Text(
              ct.createdAtHuman ?? '',
              style: const TextStyle(fontSize: 12),
            ),
            trailing: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  '${isPositive ? '-' : '+'}${_currencyFormat.format(ct.amount.abs())}',
                  style: TextStyle(
                    fontWeight: FontWeight.w600,
                    color: isPositive ? AppColors.success : AppColors.warning,
                  ),
                ),
                Text(
                  '${l10n.creditBalance}: ${_currencyFormat.format(ct.balanceAfter)}',
                  style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
