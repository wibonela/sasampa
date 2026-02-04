class Transaction {
  final int id;
  final String transactionNumber;
  final String status;
  final double subtotal;
  final double taxAmount;
  final double discountAmount;
  final double total;
  final String paymentMethod;
  final double amountPaid;
  final double changeGiven;
  final String? customerName;
  final String? customerPhone;
  final String? customerTin;
  final String? notes;
  final List<TransactionItem> items;
  final String? cashierName;
  final String? branchName;
  final String createdAt;
  final String? createdAtHuman;

  Transaction({
    required this.id,
    required this.transactionNumber,
    required this.status,
    required this.subtotal,
    required this.taxAmount,
    required this.discountAmount,
    required this.total,
    required this.paymentMethod,
    required this.amountPaid,
    required this.changeGiven,
    this.customerName,
    this.customerPhone,
    this.customerTin,
    this.notes,
    this.items = const [],
    this.cashierName,
    this.branchName,
    required this.createdAt,
    this.createdAtHuman,
  });

  factory Transaction.fromJson(Map<String, dynamic> json) {
    return Transaction(
      id: json['id'],
      transactionNumber: json['transaction_number'],
      status: json['status'],
      subtotal: (json['subtotal'] ?? 0).toDouble(),
      taxAmount: (json['tax_amount'] ?? 0).toDouble(),
      discountAmount: (json['discount_amount'] ?? 0).toDouble(),
      total: (json['total'] ?? 0).toDouble(),
      paymentMethod: json['payment_method'],
      amountPaid: (json['amount_paid'] ?? 0).toDouble(),
      changeGiven: (json['change_given'] ?? 0).toDouble(),
      customerName: json['customer_name'],
      customerPhone: json['customer_phone'],
      customerTin: json['customer_tin'],
      notes: json['notes'],
      items: json['items'] != null
          ? (json['items'] as List).map((e) => TransactionItem.fromJson(e)).toList()
          : [],
      cashierName: json['cashier'] is Map ? json['cashier']['name'] : json['cashier'],
      branchName: json['branch'] is Map ? json['branch']['name'] : json['branch'],
      createdAt: json['created_at'],
      createdAtHuman: json['created_at_human'],
    );
  }

  bool get isCompleted => status == 'completed';
  bool get isVoided => status == 'voided';

  String get paymentMethodLabel {
    switch (paymentMethod) {
      case 'cash':
        return 'Cash';
      case 'card':
        return 'Card';
      case 'mobile':
        return 'Mobile Money';
      case 'bank_transfer':
        return 'Bank Transfer';
      default:
        return paymentMethod;
    }
  }
}

class TransactionItem {
  final int id;
  final int productId;
  final String productName;
  final int quantity;
  final double unitPrice;
  final double taxRate;
  final double taxAmount;
  final double subtotal;

  TransactionItem({
    required this.id,
    required this.productId,
    required this.productName,
    required this.quantity,
    required this.unitPrice,
    required this.taxRate,
    required this.taxAmount,
    required this.subtotal,
  });

  factory TransactionItem.fromJson(Map<String, dynamic> json) {
    return TransactionItem(
      id: json['id'],
      productId: json['product_id'],
      productName: json['product_name'],
      quantity: json['quantity'],
      unitPrice: (json['unit_price'] ?? 0).toDouble(),
      taxRate: (json['tax_rate'] ?? 0).toDouble(),
      taxAmount: (json['tax_amount'] ?? 0).toDouble(),
      subtotal: (json['subtotal'] ?? 0).toDouble(),
    );
  }

  double get lineTotal => unitPrice * quantity;
}
