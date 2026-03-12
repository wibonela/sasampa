import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';
import '../../../core/services/escpos_commands.dart';
import '../../../core/services/printer_preferences.dart';
import '../../../core/services/printer_providers.dart';
import '../../../core/services/receipt_service.dart';
import '../../../core/utils/error_utils.dart';
import '../../../core/network/api_client.dart';
import '../../../shared/models/customer.dart';

class CheckoutSheet extends ConsumerStatefulWidget {
  const CheckoutSheet({super.key});

  @override
  ConsumerState<CheckoutSheet> createState() => _CheckoutSheetState();
}

class _CheckoutSheetState extends ConsumerState<CheckoutSheet> {
  final _customerNameController = TextEditingController();
  final _customerPhoneController = TextEditingController();
  final _amountPaidController = TextEditingController();
  final _tinController = TextEditingController();

  bool _isProcessing = false;
  bool _showCustomerInfo = false;
  String _selectedPaymentMethod = 'cash';
  String? _error;
  Customer? _selectedCustomer;
  double _lastSyncedTotal = 0;

  final _currencyFormat = NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final cart = ref.read(cartProvider);
      _lastSyncedTotal = cart.total;
      _amountPaidController.text = cart.total.toStringAsFixed(0);
    });
  }

  @override
  void dispose() {
    _customerNameController.dispose();
    _customerPhoneController.dispose();
    _amountPaidController.dispose();
    _tinController.dispose();
    super.dispose();
  }

  Future<void> _saveAsOrder() async {
    final cart = ref.read(cartProvider);
    final customerName = _customerNameController.text.trim();
    final l10n = AppLocalizations.of(context);

    if (customerName.isEmpty) {
      setState(() {
        _showCustomerInfo = true;
        _error = l10n?.customerNameRequired ?? 'Customer name is required for orders';
      });
      return;
    }

    setState(() {
      _isProcessing = true;
      _error = null;
    });

    try {
      final api = ref.read(apiClientProvider);
      final response = await api.createOrder(
        items: cart.toCheckoutItems(),
        customerName: customerName,
        customerId: _selectedCustomer?.id,
        customerPhone: _customerPhoneController.text.trim().isNotEmpty
            ? _customerPhoneController.text.trim()
            : null,
        customerTin: _tinController.text.trim().isNotEmpty
            ? _tinController.text.trim()
            : null,
        discountAmount: cart.discountAmount > 0 ? cart.discountAmount : null,
      );

      final data = response.data;

      // Clear cart
      ref.read(cartProvider.notifier).clearCart();

      if (mounted) {
        final showProforma = await showDialog<bool>(
          context: context,
          barrierDismissible: false,
          builder: (dialogContext) => Dialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 64, height: 64,
                    decoration: const BoxDecoration(color: AppColors.success, shape: BoxShape.circle),
                    child: const Icon(Icons.check, color: Colors.white, size: 32),
                  ),
                  const SizedBox(height: 16),
                  Text(l10n?.orderSaved ?? 'Order saved!', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  Text(data['data']?['transaction_number'] ?? '', style: const TextStyle(color: AppColors.textSecondary)),
                  const SizedBox(height: 24),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () => Navigator.pop(dialogContext, false),
                          child: Text(l10n?.close ?? 'Close'),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: ElevatedButton(
                          onPressed: () => Navigator.pop(dialogContext, true),
                          child: Text(l10n?.shareProforma ?? 'Share Proforma'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        );

        if (showProforma == true && mounted) {
          try {
            final orderId = data['data']?['id'];
            if (orderId != null) {
              final api = ref.read(apiClientProvider);
              final proformaResponse = await api.getProformaReceipt(orderId);
              await ReceiptService.shareProformaFromApi(proformaResponse.data['data']);
            }
          } catch (_) {}
        }

        if (mounted) {
          Navigator.pop(context);
        }
      }
    } catch (e) {
      setState(() {
        _error = extractErrorMessage(e, 'Failed to save order.');
        _isProcessing = false;
      });
    }
  }

  Future<void> _processCheckout() async {
    final cart = ref.read(cartProvider);

    // Get amount paid (no minimum validation - allow any amount)
    double amountPaid = double.tryParse(_amountPaidController.text) ?? cart.total;

    setState(() {
      _isProcessing = true;
      _error = null;
    });

    try {
      final api = ref.read(apiClientProvider);
      final response = await api.checkout(
        items: cart.toCheckoutItems(),
        paymentMethod: _selectedPaymentMethod,
        amountPaid: _selectedPaymentMethod == 'credit' ? 0 : amountPaid,
        customerId: _selectedCustomer?.id,
        customerName: _customerNameController.text.trim().isNotEmpty
            ? _customerNameController.text.trim()
            : null,
        customerPhone: _customerPhoneController.text.trim().isNotEmpty
            ? _customerPhoneController.text.trim()
            : null,
        customerTin: _tinController.text.trim().isNotEmpty
            ? _tinController.text.trim()
            : null,
        discountAmount: cart.discountAmount > 0 ? cart.discountAmount : null,
      );

      final data = response.data;

      // Clear cart
      ref.read(cartProvider.notifier).clearCart();

      // Auto-print after sale if enabled
      final printerPrefs = ref.read(printerPrefsProvider).prefs;
      if (printerPrefs.autoPrintAfterSale && data['data'] != null) {
        final transaction = data['data'];
        if (printerPrefs.printerType == PrinterType.bluetooth) {
          final btService = ref.read(bluetoothPrinterServiceProvider);
          if (btService.isConnected) {
            final paperSize = printerPrefs.paperSize == 'mm58'
                ? PaperSize.mm58
                : PaperSize.mm80;
            final authState = ref.read(authProvider);
            await ReceiptService.bluetoothPrintSimpleReceipt(
              transaction: transaction,
              btService: btService,
              paperSize: paperSize,
              companyName: authState.user?.company?.name,
            );
          }
        }
      }

      if (mounted) {
        // Show success dialog first (while sheet is still mounted)
        // Returns true if user wants to see receipt, false otherwise
        // ignore: use_build_context_synchronously
        final showReceipt = await showDialog<bool>(
          context: context,
          barrierDismissible: false,
          builder: (dialogContext) => _buildSuccessDialog(dialogContext, data['data']),
        );

        // If user wants to see receipt, show it before closing
        if (showReceipt == true && mounted) {
          // ignore: use_build_context_synchronously
          await showDialog(
            context: context,
            builder: (ctx) => _buildReceiptDialog(ctx, data['data']),
          );
        }

        // Then close the sheet
        if (mounted) {
          // ignore: use_build_context_synchronously
          Navigator.pop(context);
        }
      }
    } catch (e) {
      setState(() {
        _error = extractErrorMessage(e, 'Failed to process sale. Please try again.');
        _isProcessing = false;
      });
    }
  }

  Widget _buildReceiptDialog(BuildContext dialogContext, Map<String, dynamic> transaction) {
    final l10n = AppLocalizations.of(dialogContext)!;
    final items = transaction['items'] as List? ?? [];
    final authState = ref.read(authProvider);
    final company = authState.user?.company;
    final cashier = transaction['cashier'];

    // Parse date
    String dateStr = '';
    String timeStr = '';
    final createdAt = transaction['created_at']?.toString() ?? '';
    if (createdAt.length >= 19) {
      try {
        final dt = DateTime.parse(createdAt);
        dateStr = DateFormat('dd/MM/yyyy').format(dt);
        timeStr = DateFormat('HH:mm').format(dt);
      } catch (_) {
        dateStr = createdAt.substring(0, 10);
        timeStr = createdAt.substring(11, 16);
      }
    }

    return Dialog(
      backgroundColor: Colors.white,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      child: Container(
        width: double.maxFinite,
        constraints: const BoxConstraints(maxWidth: 320),
        child: SingleChildScrollView(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // ===== HEADER =====
                if (company?.logo != null) ...[
                  ClipOval(
                    child: Image.network(
                      company!.logo!,
                      width: 50,
                      height: 50,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => const SizedBox.shrink(),
                    ),
                  ),
                  const SizedBox(height: 8),
                ],
                Text(
                  company?.name ?? 'SASAMPA POS',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 4),

                // Dashed divider
                _buildDashedDivider(),
                const SizedBox(height: 8),

                // ===== TRANSACTION INFO =====
                _buildInfoRow('${l10n.receipt} #:', transaction['transaction_number'] ?? ''),
                _buildInfoRow('${l10n.date}:', dateStr),
                _buildInfoRow('${l10n.time}:', timeStr),
                _buildInfoRow('${l10n.cashier}:', cashier?['name'] ?? authState.user?.name ?? ''),
                if (transaction['customer_name'] != null)
                  _buildInfoRow('${l10n.customer}:', transaction['customer_name']),
                if (transaction['customer_tin'] != null)
                  _buildInfoRow('TIN:', transaction['customer_tin']),

                const SizedBox(height: 8),
                _buildDashedDivider(),
                const SizedBox(height: 8),

                // ===== ITEMS HEADER =====
                Row(
                  children: [
                    Expanded(flex: 5, child: Text(l10n.item, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12))),
                    Expanded(flex: 2, child: Text(l10n.quantity, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12), textAlign: TextAlign.center)),
                    Expanded(flex: 3, child: Text(l10n.amount, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12), textAlign: TextAlign.right)),
                  ],
                ),
                _buildDashedDivider(),
                const SizedBox(height: 4),

                // ===== ITEMS =====
                ...items.map((item) {
                  final unitPrice = (item['unit_price'] ?? 0).toDouble();
                  final qty = item['quantity'] ?? 0;
                  final subtotal = (item['subtotal'] ?? 0).toDouble();
                  return Padding(
                    padding: const EdgeInsets.symmetric(vertical: 4),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          flex: 5,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                item['product_name'] ?? '',
                                style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 12),
                              ),
                              Text(
                                '@ TZS ${NumberFormat('#,###').format(unitPrice)}',
                                style: const TextStyle(color: AppColors.textSecondary, fontSize: 10),
                              ),
                            ],
                          ),
                        ),
                        Expanded(
                          flex: 2,
                          child: Text('$qty', style: const TextStyle(fontSize: 12), textAlign: TextAlign.center),
                        ),
                        Expanded(
                          flex: 3,
                          child: Text(
                            'TZS ${NumberFormat('#,###').format(subtotal)}',
                            style: const TextStyle(fontSize: 12),
                            textAlign: TextAlign.right,
                          ),
                        ),
                      ],
                    ),
                  );
                }),

                const SizedBox(height: 8),
                _buildDashedDivider(),
                const SizedBox(height: 8),

                // ===== TOTALS =====
                _buildTotalRow('${l10n.subtotal}:', 'TZS ${NumberFormat('#,###').format((transaction['subtotal'] ?? 0).toDouble())}'),
                if ((transaction['tax_amount'] ?? 0) > 0)
                  _buildTotalRow('${l10n.vat}:', 'TZS ${NumberFormat('#,###').format((transaction['tax_amount'] ?? 0).toDouble())}'),
                if ((transaction['discount_amount'] ?? 0) > 0)
                  _buildTotalRow('${l10n.discount}:', '-TZS ${NumberFormat('#,###').format((transaction['discount_amount'] ?? 0).toDouble())}'),
                const SizedBox(height: 4),
                _buildTotalRow(
                  '${l10n.total}:',
                  'TZS ${NumberFormat('#,###').format((transaction['total'] ?? 0).toDouble())}',
                  isBold: true,
                  fontSize: 14,
                ),

                const SizedBox(height: 8),
                _buildDashedDivider(),
                const SizedBox(height: 8),

                // ===== PAYMENT =====
                _buildInfoRow('${l10n.payment}:', _paymentMethodLabel(transaction['payment_method'] ?? 'cash', l10n)),
                _buildInfoRow('${l10n.amountPaid}:', 'TZS ${NumberFormat('#,###').format((transaction['amount_paid'] ?? 0).toDouble())}'),
                if ((transaction['change_given'] ?? 0) > 0)
                  _buildInfoRow('${l10n.change}:', 'TZS ${NumberFormat('#,###').format((transaction['change_given'] ?? 0).toDouble())}', isBold: true),

                const SizedBox(height: 8),
                _buildDashedDivider(),
                const SizedBox(height: 16),

                // ===== FOOTER =====
                Text(
                  l10n.thankYou,
                  style: const TextStyle(fontSize: 12),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 4),
                Text(
                  l10n.welcomeAgain,
                  style: const TextStyle(fontSize: 12),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 12),

                // Receipt number box
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.black),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    transaction['transaction_number'] ?? '',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500),
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  l10n.poweredBy,
                  style: const TextStyle(fontSize: 10, color: AppColors.textSecondary),
                  textAlign: TextAlign.center,
                ),

                const SizedBox(height: 20),

                // Action buttons
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () async {
                          final prefsState = ref.read(printerPrefsProvider);
                          final prefs = prefsState.prefs;
                          final authState = ref.read(authProvider);

                          if (prefs.printerType == PrinterType.bluetooth) {
                            final btService = ref.read(bluetoothPrinterServiceProvider);
                            if (btService.isConnected) {
                              final paperSize = prefs.paperSize == 'mm58'
                                  ? PaperSize.mm58
                                  : PaperSize.mm80;
                              await ReceiptService.bluetoothPrintSimpleReceipt(
                                transaction: transaction,
                                btService: btService,
                                paperSize: paperSize,
                                companyName: authState.user?.company?.name,
                              );
                              return;
                            }
                          }
                          await ReceiptService.printReceipt(
                            transaction: transaction,
                            companyName: authState.user?.company?.name,
                            cashierName: authState.user?.name,
                          );
                        },
                        icon: const Icon(Icons.print, size: 18),
                        label: Text(l10n.print),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () async {
                          final authState = ref.read(authProvider);
                          await ReceiptService.shareReceipt(
                            transaction: transaction,
                            companyName: authState.user?.company?.name,
                            cashierName: authState.user?.name,
                          );
                        },
                        icon: const Icon(Icons.share, size: 18),
                        label: Text(l10n.share),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () => Navigator.pop(dialogContext),
                    child: Text(l10n.close),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildDashedDivider() {
    return Row(
      children: List.generate(
        40,
        (index) => Expanded(
          child: Container(
            color: index % 2 == 0 ? Colors.grey : Colors.transparent,
            height: 1,
          ),
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value, {bool isBold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        children: [
          SizedBox(
            width: 80,
            child: Text(label, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
          ),
          Expanded(
            child: Text(
              value,
              style: TextStyle(fontSize: 11, fontWeight: isBold ? FontWeight.bold : FontWeight.normal),
              textAlign: TextAlign.right,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTotalRow(String label, String value, {bool isBold = false, double fontSize = 12}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(fontSize: fontSize, fontWeight: isBold ? FontWeight.bold : FontWeight.normal)),
          Text(value, style: TextStyle(fontSize: fontSize, fontWeight: isBold ? FontWeight.bold : FontWeight.normal)),
        ],
      ),
    );
  }

  Widget _buildPaymentChip(String value, String label, IconData icon) {
    final selected = _selectedPaymentMethod == value;
    return ChoiceChip(
      label: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: selected ? Colors.white : AppColors.textSecondary),
          const SizedBox(width: 4),
          Text(label),
        ],
      ),
      selected: selected,
      onSelected: (_) => setState(() => _selectedPaymentMethod = value),
      selectedColor: AppColors.primary,
      labelStyle: TextStyle(
        color: selected ? Colors.white : AppColors.textPrimary,
        fontSize: 13,
      ),
      showCheckmark: false,
    );
  }

  String _paymentMethodLabel(String method, AppLocalizations l10n) {
    return switch (method) {
      'cash' => l10n.cash,
      'card' => l10n.card,
      'mobile' => l10n.mobileMoney,
      'bank_transfer' => l10n.bankTransfer,
      'credit' => l10n.credit,
      _ => method,
    };
  }

  Future<void> _showCustomerSearch() async {
    final l10n = AppLocalizations.of(context)!;
    final searchController = TextEditingController();
    List<Customer> results = [];
    List<Customer> recentCustomers = [];
    bool searching = false;
    bool loadingRecent = true;

    // Pre-load recent customers
    try {
      final api = ref.read(apiClientProvider);
      final response = await api.getCustomers(perPage: 20);
      final data = response.data['data'] as List;
      recentCustomers = data.map((e) => Customer.fromJson(e)).toList();
    } catch (_) {}
    loadingRecent = false;

    if (!mounted) return;

    final selected = await showModalBottomSheet<Customer>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (ctx, setSheetState) {
            final displayList = searchController.text.length >= 2 ? results : recentCustomers;
            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(ctx).viewInsets.bottom,
              ),
              child: Container(
                constraints: BoxConstraints(
                  maxHeight: MediaQuery.of(ctx).size.height * 0.6,
                ),
                padding: const EdgeInsets.all(20),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Center(
                      child: Container(
                        width: 40, height: 4,
                        decoration: BoxDecoration(
                          color: AppColors.gray4,
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      l10n.selectCustomer,
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: searchController,
                      autofocus: false,
                      decoration: InputDecoration(
                        hintText: l10n.searchCustomers,
                        prefixIcon: const Icon(Icons.search),
                        filled: true,
                        fillColor: AppColors.gray6,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: BorderSide.none,
                        ),
                      ),
                      onChanged: (q) async {
                        if (q.length < 2) {
                          setSheetState(() => results = []);
                          return;
                        }
                        setSheetState(() => searching = true);
                        try {
                          final api = ref.read(apiClientProvider);
                          final response = await api.searchCustomers(q);
                          final data = response.data['data'] as List;
                          setSheetState(() {
                            results = data.map((e) => Customer.fromJson(e)).toList();
                            searching = false;
                          });
                        } catch (_) {
                          setSheetState(() => searching = false);
                        }
                      },
                    ),
                    const SizedBox(height: 8),
                    // Add new customer link
                    TextButton.icon(
                      onPressed: () async {
                        Navigator.pop(ctx);
                        // Quick-create: just fill in the name/phone fields
                        setState(() => _showCustomerInfo = true);
                      },
                      icon: const Icon(Icons.person_add, size: 18),
                      label: Text(l10n.addCustomer),
                    ),
                    if (searching || loadingRecent)
                      const Padding(
                        padding: EdgeInsets.all(16),
                        child: Center(child: CircularProgressIndicator()),
                      ),
                    if (!searching && !loadingRecent && displayList.isNotEmpty)
                      Flexible(
                        child: ListView.builder(
                          shrinkWrap: true,
                          itemCount: displayList.length,
                          itemBuilder: (ctx, i) {
                            final c = displayList[i];
                            return ListTile(
                              onTap: () => Navigator.pop(ctx, c),
                              leading: CircleAvatar(
                                radius: 18,
                                backgroundColor: AppColors.primary.withValues(alpha: 0.1),
                                child: Text(
                                  c.name[0].toUpperCase(),
                                  style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold),
                                ),
                              ),
                              title: Text(c.name, style: const TextStyle(fontWeight: FontWeight.w500)),
                              subtitle: Text(c.phone, style: const TextStyle(fontSize: 13)),
                              trailing: c.hasCredit
                                  ? Text(
                                      l10n.credit,
                                      style: const TextStyle(fontSize: 11, color: AppColors.primary),
                                    )
                                  : null,
                            );
                          },
                        ),
                      ),
                    if (!searching && !loadingRecent && displayList.isEmpty)
                      Padding(
                        padding: const EdgeInsets.all(24),
                        child: Column(
                          children: [
                            Icon(Icons.people_outline, size: 40, color: AppColors.gray3),
                            const SizedBox(height: 8),
                            Text(
                              l10n.noCustomers,
                              textAlign: TextAlign.center,
                              style: const TextStyle(color: AppColors.textSecondary),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              searchController.text.isEmpty
                                  ? 'Add customers from the Customers menu'
                                  : '',
                              textAlign: TextAlign.center,
                              style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );

    if (selected != null) {
      setState(() {
        _selectedCustomer = selected;
        _customerNameController.text = selected.name;
        _customerPhoneController.text = selected.phone;
        _tinController.text = selected.tin ?? '';
        _showCustomerInfo = true;
      });
    }
  }

  void _showQuantityDialog(int productId, int currentQuantity) {
    final l10n = AppLocalizations.of(context)!;
    final controller = TextEditingController(text: '$currentQuantity');
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(l10n.enterQuantity),
        content: TextField(
          controller: controller,
          keyboardType: TextInputType.number,
          autofocus: true,
          decoration: InputDecoration(
            labelText: l10n.quantity,
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(l10n.cancel),
          ),
          ElevatedButton(
            onPressed: () {
              final qty = int.tryParse(controller.text) ?? currentQuantity;
              if (qty > 0) {
                ref.read(cartProvider.notifier).updateQuantity(productId, qty);
              }
              Navigator.pop(context);
            },
            child: Text(l10n.ok),
          ),
        ],
      ),
    );
  }

  Widget _buildSuccessDialog(BuildContext dialogContext, Map<String, dynamic> transaction) {
    final l10n = AppLocalizations.of(dialogContext)!;
    final change = (transaction['change_given'] ?? 0).toDouble();
    final waStatus = transaction['whatsapp_receipt_status'];

    return _SuccessDialogContent(
      l10n: l10n,
      transaction: transaction,
      change: change,
      waStatus: waStatus,
      currencyFormat: _currencyFormat,
      apiClient: ref.read(apiClientProvider),
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final cart = ref.watch(cartProvider);

    // Auto-sync amount paid with total when cart changes via +/- buttons
    if (cart.total != _lastSyncedTotal) {
      final currentAmount = double.tryParse(_amountPaidController.text) ?? 0;
      // Only auto-update if user hasn't manually changed the amount
      if (currentAmount == _lastSyncedTotal || currentAmount == 0) {
        _amountPaidController.text = cart.total.toStringAsFixed(0);
      }
      _lastSyncedTotal = cart.total;
    }

    final bottomInset = MediaQuery.of(context).viewInsets.bottom;

    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.9,
      ),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Handle
          Container(
            margin: const EdgeInsets.only(top: 12),
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: AppColors.gray4,
              borderRadius: BorderRadius.circular(2),
            ),
          ),

          // Header
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: Text(
                    l10n.checkout,
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
          ),

          const Divider(height: 1),

          // Content
          Expanded(
            child: SingleChildScrollView(
              padding: EdgeInsets.only(
                left: 16,
                right: 16,
                bottom: 16 + bottomInset,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const SizedBox(height: 16),

                  // Cart Items
                  ...cart.items.map((item) => Container(
                        margin: const EdgeInsets.only(bottom: 8),
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: AppColors.gray6,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Row(
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    item.product.name,
                                    style: const TextStyle(fontWeight: FontWeight.w500),
                                  ),
                                  Text(
                                    '${_currencyFormat.format(item.product.sellingPrice)} x ${item.quantity}',
                                    style: const TextStyle(
                                      fontSize: 13,
                                      color: AppColors.textSecondary,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            // Quantity controls
                            Row(
                              children: [
                                IconButton(
                                  icon: const Icon(Icons.remove_circle_outline),
                                  onPressed: () {
                                    ref.read(cartProvider.notifier).decrementQuantity(item.product.id);
                                  },
                                  iconSize: 20,
                                  visualDensity: VisualDensity.compact,
                                ),
                                GestureDetector(
                                  onTap: () => _showQuantityDialog(item.product.id, item.quantity),
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: AppColors.primary.withOpacity(0.1),
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(
                                      '${item.quantity}',
                                      style: const TextStyle(
                                        fontWeight: FontWeight.w600,
                                        color: AppColors.primary,
                                      ),
                                    ),
                                  ),
                                ),
                                IconButton(
                                  icon: const Icon(Icons.add_circle_outline),
                                  onPressed: () {
                                    ref.read(cartProvider.notifier).incrementQuantity(item.product.id);
                                  },
                                  iconSize: 20,
                                  visualDensity: VisualDensity.compact,
                                ),
                              ],
                            ),
                            const SizedBox(width: 8),
                            Text(
                              _currencyFormat.format(item.subtotal),
                              style: const TextStyle(fontWeight: FontWeight.w600),
                            ),
                          ],
                        ),
                      )),

                  const SizedBox(height: 16),

                  // Totals
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withOpacity(0.05),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.primary.withOpacity(0.2)),
                    ),
                    child: Column(
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(l10n.subtotal),
                            Text(_currencyFormat.format(cart.subtotal)),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(l10n.tax),
                            Text(_currencyFormat.format(cart.taxAmount)),
                          ],
                        ),
                        if (cart.discountAmount > 0) ...[
                          const SizedBox(height: 8),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(l10n.discount, style: const TextStyle(color: AppColors.success)),
                              Text(
                                '-${_currencyFormat.format(cart.discountAmount)}',
                                style: const TextStyle(color: AppColors.success),
                              ),
                            ],
                          ),
                        ],
                        const Divider(height: 16),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              l10n.total,
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 18,
                              ),
                            ),
                            Text(
                              _currencyFormat.format(cart.total),
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 18,
                                color: AppColors.primary,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Payment Method
                  Text(
                    l10n.paymentMethod,
                    style: const TextStyle(
                      fontWeight: FontWeight.w600,
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    children: [
                      _buildPaymentChip('cash', l10n.cash, Icons.payments),
                      _buildPaymentChip('card', l10n.card, Icons.credit_card),
                      _buildPaymentChip('mobile', l10n.mobileMoney, Icons.phone_android),
                      _buildPaymentChip('bank_transfer', l10n.bankTransfer, Icons.account_balance),
                      if (_selectedCustomer != null && _selectedCustomer!.hasCredit)
                        _buildPaymentChip('credit', l10n.credit, Icons.credit_score),
                    ],
                  ),

                  const SizedBox(height: 16),

                  // Amount Paid (hide for credit)
                  if (_selectedPaymentMethod != 'credit')
                    TextField(
                      controller: _amountPaidController,
                      keyboardType: TextInputType.number,
                      decoration: InputDecoration(
                        labelText: l10n.amountPaid,
                        prefixText: 'TZS ',
                      ),
                    ),

                  // Credit info
                  if (_selectedPaymentMethod == 'credit' && _selectedCustomer != null)
                    Container(
                      margin: const EdgeInsets.only(top: 8),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withOpacity(0.05),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: AppColors.primary.withOpacity(0.2)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(l10n.availableCredit, style: const TextStyle(fontSize: 13)),
                          Text(
                            _currencyFormat.format(_selectedCustomer!.availableCredit),
                            style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary),
                          ),
                        ],
                      ),
                    ),

                  const SizedBox(height: 12),

                  // Customer selection
                  if (_selectedCustomer != null)
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppColors.success.withOpacity(0.05),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: AppColors.success.withOpacity(0.3)),
                      ),
                      child: Row(
                        children: [
                          CircleAvatar(
                            radius: 16,
                            backgroundColor: AppColors.primary.withValues(alpha: 0.1),
                            child: Text(
                              _selectedCustomer!.name[0].toUpperCase(),
                              style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(_selectedCustomer!.name, style: const TextStyle(fontWeight: FontWeight.w500)),
                                Text(_selectedCustomer!.phone, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                              ],
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.close, size: 18),
                            onPressed: () {
                              setState(() {
                                _selectedCustomer = null;
                                _customerNameController.clear();
                                _customerPhoneController.clear();
                                _tinController.clear();
                                if (_selectedPaymentMethod == 'credit') {
                                  _selectedPaymentMethod = 'cash';
                                }
                              });
                            },
                          ),
                        ],
                      ),
                    )
                  else
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: _showCustomerSearch,
                            icon: const Icon(Icons.person_search, size: 18),
                            label: Text(l10n.selectCustomer),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: AppColors.primary,
                              side: const BorderSide(color: AppColors.primary),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        GestureDetector(
                          onTap: () => setState(() => _showCustomerInfo = !_showCustomerInfo),
                          child: Row(
                            children: [
                              Icon(
                                _showCustomerInfo ? Icons.remove_circle_outline : Icons.add_circle_outline,
                                size: 16,
                                color: AppColors.textSecondary,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                _showCustomerInfo ? l10n.hideCustomerInfo : l10n.addCustomerInfo,
                                style: const TextStyle(
                                  color: AppColors.textSecondary,
                                  fontSize: 12,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),

                  if (_showCustomerInfo && _selectedCustomer == null) ...[
                    const SizedBox(height: 12),
                    TextField(
                      controller: _customerNameController,
                      decoration: InputDecoration(
                        labelText: l10n.customerName,
                        prefixIcon: const Icon(Icons.person_outline),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _customerPhoneController,
                      keyboardType: TextInputType.phone,
                      decoration: InputDecoration(
                        labelText: l10n.phoneNumber,
                        prefixIcon: const Icon(Icons.phone_outlined),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _tinController,
                      keyboardType: TextInputType.number,
                      decoration: InputDecoration(
                        labelText: l10n.tinNumber,
                        prefixIcon: const Icon(Icons.badge_outlined),
                      ),
                    ),
                  ],

                  // Error
                  if (_error != null)
                    Container(
                      margin: const EdgeInsets.only(top: 16),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppColors.error.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        _error!,
                        style: const TextStyle(color: AppColors.error),
                      ),
                    ),

                  const SizedBox(height: 24),

                  // Submit Button
                  SizedBox(
                    height: 54,
                    child: ElevatedButton(
                      onPressed: _isProcessing ? null : _processCheckout,
                      child: _isProcessing
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                valueColor: AlwaysStoppedAnimation(Colors.white),
                              ),
                            )
                          : Text('${l10n.completeSale} - ${_currencyFormat.format(cart.total)}'),
                    ),
                  ),

                  const SizedBox(height: 8),

                  // Save as Order Button
                  SizedBox(
                    height: 48,
                    child: OutlinedButton.icon(
                      onPressed: _isProcessing ? null : _saveAsOrder,
                      icon: const Icon(Icons.assignment_outlined),
                      label: Text(l10n.saveAsOrder),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.primary,
                        side: const BorderSide(color: AppColors.primary),
                      ),
                    ),
                  ),

                  const SizedBox(height: 16),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

}

/// Success dialog with WhatsApp receipt support
class _SuccessDialogContent extends StatefulWidget {
  final AppLocalizations l10n;
  final Map<String, dynamic> transaction;
  final double change;
  final String? waStatus;
  final NumberFormat currencyFormat;
  final ApiClient apiClient;

  const _SuccessDialogContent({
    required this.l10n,
    required this.transaction,
    required this.change,
    this.waStatus,
    required this.currencyFormat,
    required this.apiClient,
  });

  @override
  State<_SuccessDialogContent> createState() => _SuccessDialogContentState();
}

class _SuccessDialogContentState extends State<_SuccessDialogContent> {
  String? _waStatus;
  bool _isSendingWhatsApp = false;

  @override
  void initState() {
    super.initState();
    _waStatus = widget.waStatus;
  }

  Future<void> _sendWhatsAppReceipt({String? phone}) async {
    final txId = widget.transaction['id'];
    final customerPhone = phone ?? widget.transaction['customer_phone'];

    if (customerPhone == null && phone == null) {
      // Show phone input dialog
      final enteredPhone = await _showPhoneInputDialog();
      if (enteredPhone == null || enteredPhone.isEmpty) return;
      return _sendWhatsAppReceipt(phone: enteredPhone);
    }

    setState(() => _isSendingWhatsApp = true);
    try {
      await widget.apiClient.sendWhatsAppReceipt(txId, phone: phone ?? customerPhone);
      setState(() {
        _waStatus = 'pending';
        _isSendingWhatsApp = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(widget.l10n.whatsappReceiptSent),
            backgroundColor: AppColors.success,
          ),
        );
      }
    } catch (e) {
      setState(() {
        _waStatus = 'failed';
        _isSendingWhatsApp = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(widget.l10n.receiptFailed),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<String?> _showPhoneInputDialog() {
    final controller = TextEditingController();
    return showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(widget.l10n.enterCustomerPhone),
        content: TextField(
          controller: controller,
          keyboardType: TextInputType.phone,
          decoration: InputDecoration(
            labelText: widget.l10n.phoneNumber,
            hintText: '+255...',
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(widget.l10n.cancel),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, controller.text.trim()),
            child: Text(widget.l10n.confirm),
          ),
        ],
      ),
    );
  }

  Widget _buildWhatsAppRow() {
    final l10n = widget.l10n;

    // WhatsApp receipts - Coming Soon
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: AppColors.warning.withOpacity(0.08),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          const Icon(Icons.chat_outlined, size: 16, color: Color(0xFF25D366)),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              l10n.whatsappComingSoon,
              style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
            decoration: BoxDecoration(
              color: AppColors.warning.withOpacity(0.15),
              borderRadius: BorderRadius.circular(4),
            ),
            child: Text(
              l10n.comingSoon,
              style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.warning),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = widget.l10n;

    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: const BoxDecoration(
                color: AppColors.success,
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.check, color: Colors.white, size: 32),
            ),
            const SizedBox(height: 16),
            Text(
              l10n.saleComplete,
              style: const TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              widget.transaction['transaction_number'] ?? '',
              style: const TextStyle(color: AppColors.textSecondary),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.gray6,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(l10n.total),
                      Text(
                        widget.currencyFormat.format((widget.transaction['total'] ?? 0).toDouble()),
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  if (widget.change > 0) ...[
                    const Divider(height: 16),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          l10n.change,
                          style: const TextStyle(
                            color: AppColors.success,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        Text(
                          widget.currencyFormat.format(widget.change),
                          style: const TextStyle(
                            color: AppColors.success,
                            fontWeight: FontWeight.bold,
                            fontSize: 18,
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 12),
            _buildWhatsAppRow(),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: SizedBox(
                    height: 48,
                    child: OutlinedButton(
                      onPressed: () => Navigator.pop(context, false),
                      child: Text(l10n.close),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: SizedBox(
                    height: 48,
                    child: ElevatedButton(
                      onPressed: () => Navigator.pop(context, true),
                      child: Text(l10n.viewReceipt),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

