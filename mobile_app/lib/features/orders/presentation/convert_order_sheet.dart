import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';
import '../../../core/services/receipt_service.dart';
import '../../../core/utils/error_utils.dart';
import '../../../shared/models/transaction.dart';

class ConvertOrderSheet extends ConsumerStatefulWidget {
  final Transaction order;

  const ConvertOrderSheet({super.key, required this.order});

  @override
  ConsumerState<ConvertOrderSheet> createState() => _ConvertOrderSheetState();
}

class _ConvertOrderSheetState extends ConsumerState<ConvertOrderSheet> {
  final _amountPaidController = TextEditingController();
  bool _isProcessing = false;
  String _selectedPaymentMethod = 'cash';
  String? _error;

  final _currencyFormat = NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);

  @override
  void initState() {
    super.initState();
    _amountPaidController.text = widget.order.total.toStringAsFixed(0);
  }

  @override
  void dispose() {
    _amountPaidController.dispose();
    super.dispose();
  }

  Future<void> _convert() async {
    double amountPaid = double.tryParse(_amountPaidController.text) ?? widget.order.total;

    setState(() {
      _isProcessing = true;
      _error = null;
    });

    try {
      final api = ref.read(apiClientProvider);
      final response = await api.convertOrderToSale(
        widget.order.id,
        paymentMethod: _selectedPaymentMethod,
        amountPaid: amountPaid,
      );

      final data = response.data;

      if (mounted) {
        // Show success dialog
        final showReceipt = await showDialog<bool>(
          context: context,
          barrierDismissible: false,
          builder: (ctx) => _buildSuccessDialog(ctx, data['data']),
        );

        if (showReceipt == true && mounted) {
          await _showReceiptOptions(data['data']);
        }

        if (mounted) {
          Navigator.pop(context, true);
        }
      }
    } catch (e) {
      setState(() {
        _error = extractErrorMessage(e, 'Failed to convert order.');
        _isProcessing = false;
      });
    }
  }

  Future<void> _showReceiptOptions(Map<String, dynamic> transaction) async {
    try {
      final api = ref.read(apiClientProvider);
      final response = await api.getReceipt(transaction['id']);
      final receiptData = response.data['data'];

      if (mounted) {
        await showDialog(
          context: context,
          builder: (ctx) => AlertDialog(
            title: Text(AppLocalizations.of(context)!.receipt),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                ListTile(
                  leading: const Icon(Icons.print),
                  title: Text(AppLocalizations.of(context)!.printReceipt),
                  onTap: () async {
                    Navigator.pop(ctx);
                    await ReceiptService.printReceiptFromApi(receiptData);
                  },
                ),
                ListTile(
                  leading: const Icon(Icons.share),
                  title: Text(AppLocalizations.of(context)!.shareReceipt),
                  onTap: () async {
                    Navigator.pop(ctx);
                    await ReceiptService.shareReceiptFromApi(receiptData);
                  },
                ),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: Text(AppLocalizations.of(context)!.close),
              ),
            ],
          ),
        );
      }
    } catch (_) {}
  }

  Widget _buildSuccessDialog(BuildContext ctx, Map<String, dynamic> transaction) {
    final l10n = AppLocalizations.of(context)!;
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
              decoration: const BoxDecoration(color: AppColors.success, shape: BoxShape.circle),
              child: const Icon(Icons.check, color: Colors.white, size: 32),
            ),
            const SizedBox(height: 16),
            Text(l10n.orderConverted, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold), textAlign: TextAlign.center),
            const SizedBox(height: 8),
            Text(transaction['transaction_number'] ?? '', style: const TextStyle(color: AppColors.textSecondary)),
            if (change > 0) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(color: AppColors.gray6, borderRadius: BorderRadius.circular(8)),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(l10n.change, style: const TextStyle(color: AppColors.success, fontWeight: FontWeight.w600)),
                    Text(_currencyFormat.format(change), style: const TextStyle(color: AppColors.success, fontWeight: FontWeight.bold, fontSize: 18)),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 24),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(ctx, false),
                    child: Text(l10n.close),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () => Navigator.pop(ctx, true),
                    child: Text(l10n.viewReceipt),
                  ),
                ),
              ],
            ),
          ],
        ),
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
      labelStyle: TextStyle(color: selected ? Colors.white : AppColors.textPrimary, fontSize: 13),
      showCheckmark: false,
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final bottomInset = MediaQuery.of(context).viewInsets.bottom;

    return Container(
      constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.7),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            margin: const EdgeInsets.only(top: 12),
            width: 40,
            height: 4,
            decoration: BoxDecoration(color: AppColors.gray4, borderRadius: BorderRadius.circular(2)),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: Text(l10n.convertToSale, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                ),
                IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(context)),
              ],
            ),
          ),
          const Divider(height: 1),
          Flexible(
            child: SingleChildScrollView(
              padding: EdgeInsets.only(left: 16, right: 16, bottom: 16 + bottomInset),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const SizedBox(height: 16),

                  // Order summary
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
                            Text(l10n.orderNumber),
                            Text(widget.order.transactionNumber, style: const TextStyle(fontWeight: FontWeight.w600)),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(l10n.customerName),
                            Text(widget.order.customerName ?? '', style: const TextStyle(fontWeight: FontWeight.w500)),
                          ],
                        ),
                        const Divider(height: 16),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(l10n.total, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
                            Text(
                              _currencyFormat.format(widget.order.total),
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: AppColors.primary),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Payment Method
                  Text(l10n.paymentMethod, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    children: [
                      _buildPaymentChip('cash', l10n.cash, Icons.payments),
                      _buildPaymentChip('card', l10n.card, Icons.credit_card),
                      _buildPaymentChip('mobile', l10n.mobileMoney, Icons.phone_android),
                      _buildPaymentChip('bank_transfer', l10n.bankTransfer, Icons.account_balance),
                    ],
                  ),

                  const SizedBox(height: 16),

                  // Amount Paid
                  TextField(
                    controller: _amountPaidController,
                    keyboardType: TextInputType.number,
                    decoration: InputDecoration(labelText: l10n.amountPaid, prefixText: 'TZS '),
                  ),

                  if (_error != null)
                    Container(
                      margin: const EdgeInsets.only(top: 16),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppColors.error.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(_error!, style: const TextStyle(color: AppColors.error)),
                    ),

                  const SizedBox(height: 24),

                  SizedBox(
                    height: 54,
                    child: ElevatedButton(
                      onPressed: _isProcessing ? null : _convert,
                      child: _isProcessing
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(strokeWidth: 2, valueColor: AlwaysStoppedAnimation(Colors.white)),
                            )
                          : Text('${l10n.convertToSale} - ${_currencyFormat.format(widget.order.total)}'),
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
