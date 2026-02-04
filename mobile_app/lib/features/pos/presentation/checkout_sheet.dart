import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';
import '../../../core/services/receipt_service.dart';

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
  String? _error;

  final _currencyFormat = NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);

  @override
  void dispose() {
    _customerNameController.dispose();
    _customerPhoneController.dispose();
    _amountPaidController.dispose();
    _tinController.dispose();
    super.dispose();
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
        paymentMethod: 'cash',
        amountPaid: amountPaid,
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
        _error = 'Failed to process sale. Please try again.';
        _isProcessing = false;
      });
    }
  }

  Widget _buildReceiptDialog(BuildContext dialogContext, Map<String, dynamic> transaction) {
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
                _buildInfoRow('Receipt #:', transaction['transaction_number'] ?? ''),
                _buildInfoRow('Date:', dateStr),
                _buildInfoRow('Time:', timeStr),
                _buildInfoRow('Cashier:', cashier?['name'] ?? authState.user?.name ?? ''),
                if (transaction['customer_name'] != null)
                  _buildInfoRow('Customer:', transaction['customer_name']),
                if (transaction['customer_tin'] != null)
                  _buildInfoRow('TIN:', transaction['customer_tin']),

                const SizedBox(height: 8),
                _buildDashedDivider(),
                const SizedBox(height: 8),

                // ===== ITEMS HEADER =====
                Row(
                  children: const [
                    Expanded(flex: 5, child: Text('Item', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12))),
                    Expanded(flex: 2, child: Text('Qty', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12), textAlign: TextAlign.center)),
                    Expanded(flex: 3, child: Text('Amount', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12), textAlign: TextAlign.right)),
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
                _buildTotalRow('Subtotal:', 'TZS ${NumberFormat('#,###').format((transaction['subtotal'] ?? 0).toDouble())}'),
                if ((transaction['tax_amount'] ?? 0) > 0)
                  _buildTotalRow('VAT:', 'TZS ${NumberFormat('#,###').format((transaction['tax_amount'] ?? 0).toDouble())}'),
                if ((transaction['discount_amount'] ?? 0) > 0)
                  _buildTotalRow('Discount:', '-TZS ${NumberFormat('#,###').format((transaction['discount_amount'] ?? 0).toDouble())}'),
                const SizedBox(height: 4),
                _buildTotalRow(
                  'TOTAL:',
                  'TZS ${NumberFormat('#,###').format((transaction['total'] ?? 0).toDouble())}',
                  isBold: true,
                  fontSize: 14,
                ),

                const SizedBox(height: 8),
                _buildDashedDivider(),
                const SizedBox(height: 8),

                // ===== PAYMENT =====
                _buildInfoRow('Payment:', 'Cash'),
                _buildInfoRow('Amount Paid:', 'TZS ${NumberFormat('#,###').format((transaction['amount_paid'] ?? 0).toDouble())}'),
                if ((transaction['change_given'] ?? 0) > 0)
                  _buildInfoRow('Change:', 'TZS ${NumberFormat('#,###').format((transaction['change_given'] ?? 0).toDouble())}', isBold: true),

                const SizedBox(height: 8),
                _buildDashedDivider(),
                const SizedBox(height: 16),

                // ===== FOOTER =====
                const Text(
                  'Thank you for your purchase!',
                  style: TextStyle(fontSize: 12),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 4),
                const Text(
                  'Karibu tena / Welcome again',
                  style: TextStyle(fontSize: 12),
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
                const Text(
                  'Powered by Sasampa POS | sasampa.com',
                  style: TextStyle(fontSize: 10, color: AppColors.textSecondary),
                  textAlign: TextAlign.center,
                ),

                const SizedBox(height: 20),

                // Action buttons
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () async {
                          final authState = ref.read(authProvider);
                          await ReceiptService.printReceipt(
                            transaction: transaction,
                            companyName: authState.user?.company?.name,
                            cashierName: authState.user?.name,
                          );
                        },
                        icon: const Icon(Icons.print, size: 18),
                        label: const Text('Print'),
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
                        label: const Text('Share'),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () => Navigator.pop(dialogContext),
                    child: const Text('Close'),
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

  void _showQuantityDialog(int productId, int currentQuantity) {
    final controller = TextEditingController(text: '$currentQuantity');
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Enter Quantity'),
        content: TextField(
          controller: controller,
          keyboardType: TextInputType.number,
          autofocus: true,
          decoration: const InputDecoration(
            labelText: 'Quantity',
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              final qty = int.tryParse(controller.text) ?? currentQuantity;
              if (qty > 0) {
                ref.read(cartProvider.notifier).updateQuantity(productId, qty);
              }
              Navigator.pop(context);
            },
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  Widget _buildSuccessDialog(BuildContext dialogContext, Map<String, dynamic> transaction) {
    final change = (transaction['change_given'] ?? 0).toDouble();

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
            const Text(
              'Sale Complete!',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              transaction['transaction_number'] ?? '',
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
                      const Text('Total'),
                      Text(
                        _currencyFormat.format((transaction['total'] ?? 0).toDouble()),
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  if (change > 0) ...[
                    const Divider(height: 16),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Change',
                          style: TextStyle(
                            color: AppColors.success,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        Text(
                          _currencyFormat.format(change),
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
            const SizedBox(height: 24),
            Row(
              children: [
                Expanded(
                  child: SizedBox(
                    height: 48,
                    child: OutlinedButton(
                      onPressed: () => Navigator.pop(dialogContext, false),
                      child: const Text('Close'),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: SizedBox(
                    height: 48,
                    child: ElevatedButton(
                      onPressed: () => Navigator.pop(dialogContext, true),
                      child: const Text('View Receipt'),
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

  @override
  Widget build(BuildContext context) {
    final cart = ref.watch(cartProvider);
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
                const Expanded(
                  child: Text(
                    'Checkout',
                    style: TextStyle(
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
                            const Text('Subtotal'),
                            Text(_currencyFormat.format(cart.subtotal)),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Tax'),
                            Text(_currencyFormat.format(cart.taxAmount)),
                          ],
                        ),
                        if (cart.discountAmount > 0) ...[
                          const SizedBox(height: 8),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text('Discount', style: TextStyle(color: AppColors.success)),
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
                            const Text(
                              'Total',
                              style: TextStyle(
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

                  // Amount Paid
                  TextField(
                    controller: _amountPaidController,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      labelText: 'Amount Paid',
                      prefixText: 'TZS ',
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Customer Info (optional)
                  ExpansionTile(
                    title: const Text('Customer Info (Optional)'),
                    tilePadding: EdgeInsets.zero,
                    children: [
                      TextField(
                        controller: _customerNameController,
                        decoration: const InputDecoration(
                          labelText: 'Customer Name',
                          prefixIcon: Icon(Icons.person_outline),
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: _customerPhoneController,
                        keyboardType: TextInputType.phone,
                        decoration: const InputDecoration(
                          labelText: 'Phone Number',
                          prefixIcon: Icon(Icons.phone_outlined),
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: _tinController,
                        keyboardType: TextInputType.number,
                        decoration: const InputDecoration(
                          labelText: 'TIN Number',
                          prefixIcon: Icon(Icons.badge_outlined),
                        ),
                      ),
                    ],
                  ),

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
                          : Text('Complete Sale - ${_currencyFormat.format(cart.total)}'),
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
