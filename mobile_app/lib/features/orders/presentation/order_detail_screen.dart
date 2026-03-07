import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';
import '../../../core/services/receipt_service.dart';
import '../../../shared/models/transaction.dart';
import 'convert_order_sheet.dart';

class OrderDetailScreen extends ConsumerStatefulWidget {
  final int orderId;

  const OrderDetailScreen({super.key, required this.orderId});

  @override
  ConsumerState<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends ConsumerState<OrderDetailScreen> {
  Transaction? _order;
  bool _isLoading = true;
  String? _error;

  final _currencyFormat = NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);
  final _dateFormat = DateFormat('MMM dd, yyyy • hh:mm a');

  @override
  void initState() {
    super.initState();
    _loadOrder();
  }

  Future<void> _loadOrder() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final api = ref.read(apiClientProvider);
      final response = await api.getOrder(widget.orderId);
      setState(() {
        _order = Transaction.fromJson(response.data['data']);
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Failed to load order';
        _isLoading = false;
      });
    }
  }

  Future<void> _convertToSale() async {
    if (_order == null) return;

    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => ConvertOrderSheet(order: _order!),
    );

    if (result == true) {
      _loadOrder();
    }
  }

  Future<void> _cancelOrder() async {
    final l10n = AppLocalizations.of(context)!;
    final controller = TextEditingController();

    final reason = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(l10n.cancel),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: controller,
              decoration: InputDecoration(
                labelText: l10n.cancelReason,
                hintText: l10n.cancelReasonHint,
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
            onPressed: () => Navigator.pop(context, controller.text.trim()),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            child: Text(l10n.confirm),
          ),
        ],
      ),
    );

    if (reason == null) return;

    try {
      final api = ref.read(apiClientProvider);
      await api.cancelOrder(widget.orderId, reason: reason.isNotEmpty ? reason : null);
      await _loadOrder();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(l10n.orderCancelled),
            backgroundColor: AppColors.success,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(l10n.failedToProcess),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<void> _shareProforma() async {
    if (_order == null) return;

    try {
      final api = ref.read(apiClientProvider);
      final response = await api.getProformaReceipt(widget.orderId);
      final proformaData = response.data['data'];

      await ReceiptService.shareProformaFromApi(proformaData);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(AppLocalizations.of(context)!.failedToProcess),
            backgroundColor: AppColors.error,
          ),
        );
      }
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

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: Text(_order?.transactionNumber ?? l10n.orders),
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
                      Text(_error!),
                      const SizedBox(height: 16),
                      ElevatedButton(onPressed: _loadOrder, child: Text(l10n.retry)),
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
                          color: _getStatusColor(_order!.status).withOpacity(0.1),
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Column(
                          children: [
                            Icon(
                              _order!.isPending
                                  ? Icons.schedule
                                  : _order!.isCompleted
                                      ? Icons.check_circle
                                      : Icons.cancel,
                              size: 48,
                              color: _getStatusColor(_order!.status),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              _order!.isPending
                                  ? l10n.pending
                                  : _order!.isCompleted
                                      ? l10n.completed
                                      : l10n.cancelled,
                              style: TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                                color: _getStatusColor(_order!.status),
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              _currencyFormat.format(_order!.total),
                              style: const TextStyle(
                                fontSize: 32,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Actions for pending orders
                      if (_order!.isPending) ...[
                        Row(
                          children: [
                            Expanded(
                              child: SizedBox(
                                height: 48,
                                child: ElevatedButton.icon(
                                  onPressed: _convertToSale,
                                  icon: const Icon(Icons.check_circle_outline),
                                  label: Text(l10n.convertToSale),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: _shareProforma,
                                icon: const Icon(Icons.share, size: 18),
                                label: Text(l10n.shareProforma),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: _cancelOrder,
                                icon: const Icon(Icons.cancel_outlined, size: 18, color: AppColors.error),
                                label: Text(l10n.cancel, style: const TextStyle(color: AppColors.error)),
                                style: OutlinedButton.styleFrom(
                                  side: const BorderSide(color: AppColors.error),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                      ],

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
                            Text(l10n.details, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                            const Divider(height: 24),
                            _buildDetailRow(l10n.orderNumber, _order!.transactionNumber),
                            _buildDetailRow(l10n.date, _dateFormat.format(DateTime.parse(_order!.createdAt))),
                            if (_order!.validUntil != null)
                              _buildDetailRow(l10n.validUntil, DateFormat('dd/MM/yyyy').format(DateTime.parse(_order!.validUntil!))),
                            if (_order!.customerName != null)
                              _buildDetailRow(l10n.customerName, _order!.customerName!),
                            if (_order!.customerPhone != null)
                              _buildDetailRow(l10n.customerPhone, _order!.customerPhone!),
                            if (_order!.cashierName != null)
                              _buildDetailRow('Cashier', _order!.cashierName!),
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
                              '${l10n.items} (${_order!.items.length})',
                              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                            ),
                            const Divider(height: 24),
                            ..._order!.items.map((item) => Padding(
                                  padding: const EdgeInsets.only(bottom: 12),
                                  child: Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(item.productName, style: const TextStyle(fontWeight: FontWeight.w500)),
                                            Text(
                                              '${_currencyFormat.format(item.unitPrice)} x ${item.quantity}',
                                              style: const TextStyle(fontSize: 13, color: AppColors.textSecondary),
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
                            _buildTotalRow(l10n.subtotal, _order!.subtotal),
                            _buildTotalRow(l10n.tax, _order!.taxAmount),
                            if (_order!.discountAmount > 0)
                              _buildTotalRow(l10n.discount, -_order!.discountAmount, isDiscount: true),
                            const Divider(height: 16),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(l10n.total, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                                Text(
                                  _currencyFormat.format(_order!.total),
                                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.primary),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),

                      if (_order!.notes != null && _order!.notes!.isNotEmpty) ...[
                        const SizedBox(height: 16),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Notes', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                              const SizedBox(height: 8),
                              Text(_order!.notes!, style: const TextStyle(color: AppColors.textSecondary)),
                            ],
                          ),
                        ),
                      ],

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

  Widget _buildTotalRow(String label, double value, {bool isDiscount = false}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(color: isDiscount ? AppColors.success : AppColors.textSecondary)),
          Text(
            '${isDiscount ? '-' : ''}${_currencyFormat.format(value.abs())}',
            style: TextStyle(fontWeight: FontWeight.w500, color: isDiscount ? AppColors.success : null),
          ),
        ],
      ),
    );
  }
}
