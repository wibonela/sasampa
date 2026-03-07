import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';
import '../../../core/services/receipt_service.dart';
import '../../../shared/models/transaction.dart';

class TransactionDetailScreen extends ConsumerStatefulWidget {
  final int transactionId;

  const TransactionDetailScreen({super.key, required this.transactionId});

  @override
  ConsumerState<TransactionDetailScreen> createState() => _TransactionDetailScreenState();
}

class _TransactionDetailScreenState extends ConsumerState<TransactionDetailScreen> {
  Transaction? _transaction;
  bool _isLoading = true;
  String? _error;

  final _currencyFormat = NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);
  final _dateFormat = DateFormat('MMM dd, yyyy • hh:mm a');

  @override
  void initState() {
    super.initState();
    _loadTransaction();
  }

  Future<void> _loadTransaction() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final api = ref.read(apiClientProvider);
      final response = await api.getTransaction(widget.transactionId);
      setState(() {
        _transaction = Transaction.fromJson(response.data['data']);
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'failedToLoad';
        _isLoading = false;
      });
    }
  }

  Future<void> _voidTransaction() async {
    final reason = await showDialog<String>(
      context: context,
      builder: (context) => _VoidDialog(),
    );

    if (reason == null) return;

    try {
      final api = ref.read(apiClientProvider);
      await api.voidTransaction(widget.transactionId, reason);
      await _loadTransaction();
      if (mounted) {
        final l10n = AppLocalizations.of(context)!;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(l10n.transactionVoided),
            backgroundColor: AppColors.success,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        final l10n = AppLocalizations.of(context)!;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(l10n.failedToVoid),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<void> _printReceipt() async {
    if (_transaction == null) return;

    try {
      // Fetch full receipt data from API (includes company logo, etc.)
      final api = ref.read(apiClientProvider);
      final response = await api.getReceipt(widget.transactionId);
      final receiptData = response.data['data'];

      await ReceiptService.printReceiptFromApi(receiptData);
    } catch (e) {
      if (mounted) {
        final l10n = AppLocalizations.of(context)!;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(l10n.failedToPrint),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<void> _shareReceipt() async {
    if (_transaction == null) return;

    try {
      // Fetch full receipt data from API (includes company logo, etc.)
      final api = ref.read(apiClientProvider);
      final response = await api.getReceipt(widget.transactionId);
      final receiptData = response.data['data'];

      await ReceiptService.shareReceiptFromApi(receiptData);
    } catch (e) {
      if (mounted) {
        final l10n = AppLocalizations.of(context)!;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(l10n.failedToShare),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: Text(_transaction?.transactionNumber ?? l10n.transaction),
        actions: [
          if (_transaction != null && !_transaction!.isVoided)
            PopupMenuButton(
              itemBuilder: (context) => [
                PopupMenuItem(
                  value: 'void',
                  child: Row(
                    children: [
                      const Icon(Icons.cancel_outlined, color: AppColors.error),
                      const SizedBox(width: 8),
                      Text(l10n.voidTransaction),
                    ],
                  ),
                ),
                PopupMenuItem(
                  value: 'print',
                  child: Row(
                    children: [
                      const Icon(Icons.print_outlined),
                      const SizedBox(width: 8),
                      Text(l10n.printReceipt),
                    ],
                  ),
                ),
                PopupMenuItem(
                  value: 'share',
                  child: Row(
                    children: [
                      const Icon(Icons.share_outlined),
                      const SizedBox(width: 8),
                      Text(l10n.shareReceipt),
                    ],
                  ),
                ),
              ],
              onSelected: (value) async {
                if (value == 'void') {
                  _voidTransaction();
                } else if (value == 'print') {
                  await _printReceipt();
                } else if (value == 'share') {
                  await _shareReceipt();
                }
              },
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline, size: 48, color: AppColors.error),
                      const SizedBox(height: 16),
                      Text(l10n.failedToLoad),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadTransaction,
                        child: Text(l10n.retry),
                      ),
                    ],
                  ),
                )
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      // Status Card
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: _transaction!.isVoided
                              ? AppColors.error.withOpacity(0.1)
                              : AppColors.success.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Column(
                          children: [
                            Icon(
                              _transaction!.isVoided ? Icons.cancel : Icons.check_circle,
                              size: 48,
                              color: _transaction!.isVoided ? AppColors.error : AppColors.success,
                            ),
                            const SizedBox(height: 12),
                            Text(
                              _transaction!.isVoided ? l10n.voided : l10n.completed,
                              style: TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                                color: _transaction!.isVoided ? AppColors.error : AppColors.success,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              _currencyFormat.format(_transaction!.total),
                              style: TextStyle(
                                fontSize: 32,
                                fontWeight: FontWeight.bold,
                                color: _transaction!.isVoided
                                    ? AppColors.textSecondary
                                    : AppColors.textPrimary,
                                decoration: _transaction!.isVoided ? TextDecoration.lineThrough : null,
                              ),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Details Card
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              l10n.details,
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            const Divider(height: 24),
                            _buildDetailRow(l10n.transactionNumber, _transaction!.transactionNumber),
                            _buildDetailRow(l10n.date, _dateFormat.format(DateTime.parse(_transaction!.createdAt))),
                            _buildDetailRow(l10n.payment, _transaction!.paymentMethodLabel),
                            if (_transaction!.cashierName != null)
                              _buildDetailRow(l10n.cashier, _transaction!.cashierName!),
                            if (_transaction!.customerName != null)
                              _buildDetailRow(l10n.customer, _transaction!.customerName!),
                            if (_transaction!.branchName != null)
                              _buildDetailRow(l10n.branch, _transaction!.branchName!),
                          ],
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Items Card
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '${l10n.items} (${_transaction!.items.length})',
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            const Divider(height: 24),
                            ..._transaction!.items.map((item) => Padding(
                                  padding: const EdgeInsets.only(bottom: 12),
                                  child: Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              item.productName,
                                              style: const TextStyle(fontWeight: FontWeight.w500),
                                            ),
                                            Text(
                                              '${_currencyFormat.format(item.unitPrice)} x ${item.quantity}',
                                              style: const TextStyle(
                                                fontSize: 13,
                                                color: AppColors.textSecondary,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      Text(
                                        _currencyFormat.format(item.lineTotal),
                                        style: const TextStyle(fontWeight: FontWeight.w500),
                                      ),
                                    ],
                                  ),
                                )),
                          ],
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Totals Card
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Column(
                          children: [
                            _buildTotalRow(l10n.subtotal, _transaction!.subtotal),
                            _buildTotalRow(l10n.tax, _transaction!.taxAmount),
                            if (_transaction!.discountAmount > 0)
                              _buildTotalRow(l10n.discount, -_transaction!.discountAmount, isDiscount: true),
                            const Divider(height: 16),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  l10n.total,
                                  style: const TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                Text(
                                  _currencyFormat.format(_transaction!.total),
                                  style: const TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.primary,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            _buildTotalRow(l10n.amountPaid, _transaction!.amountPaid),
                            if (_transaction!.changeGiven > 0)
                              _buildTotalRow(l10n.change, _transaction!.changeGiven, isChange: true),
                          ],
                        ),
                      ),

                      const SizedBox(height: 100),
                    ],
                  ),
                ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(color: AppColors.textSecondary)),
          Text(value, style: const TextStyle(fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }

  Widget _buildTotalRow(String label, double value, {bool isDiscount = false, bool isChange = false}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              color: isDiscount || isChange ? AppColors.success : AppColors.textSecondary,
            ),
          ),
          Text(
            '${isDiscount ? '-' : ''}${_currencyFormat.format(value.abs())}',
            style: TextStyle(
              fontWeight: FontWeight.w500,
              color: isDiscount || isChange ? AppColors.success : null,
            ),
          ),
        ],
      ),
    );
  }
}

class _VoidDialog extends StatefulWidget {
  @override
  State<_VoidDialog> createState() => _VoidDialogState();
}

class _VoidDialogState extends State<_VoidDialog> {
  final _controller = TextEditingController();

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    return AlertDialog(
      title: Text(l10n.voidTransaction),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(l10n.voidConfirm),
          const SizedBox(height: 16),
          TextField(
            controller: _controller,
            decoration: InputDecoration(
              labelText: l10n.voidReason,
              hintText: l10n.voidReasonHint,
            ),
            maxLines: 2,
          ),
        ],
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: Text(l10n.cancel),
        ),
        ElevatedButton(
          onPressed: () {
            if (_controller.text.trim().isEmpty) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text(l10n.pleaseEnterReason)),
              );
              return;
            }
            Navigator.pop(context, _controller.text.trim());
          },
          style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
          child: Text(l10n.voidTransaction),
        ),
      ],
    );
  }
}
