class Transaction {
  final int id;
  final String transactionNumber;
  final String type;
  final String status;
  final double subtotal;
  final double taxAmount;
  final double discountAmount;
  final double total;
  final String? paymentMethod;
  final double amountPaid;
  final double changeGiven;
  final int? customerId;
  final String? customerName;
  final String? customerPhone;
  final String? customerTin;
  final String? notes;
  final List<TransactionItem> items;
  final String? cashierName;
  final String? branchName;
  final String createdAt;
  final String? createdAtHuman;
  final String? validUntil;
  final String? fiscalReceiptNumber;
  final String? fiscalVerificationCode;
  final String? fiscalQrCode;
  final bool fiscalSubmitted;
  final String? whatsappReceiptStatus;

  Transaction({
    required this.id,
    required this.transactionNumber,
    this.type = 'sale',
    required this.status,
    required this.subtotal,
    required this.taxAmount,
    required this.discountAmount,
    required this.total,
    this.paymentMethod,
    required this.amountPaid,
    required this.changeGiven,
    this.customerId,
    this.customerName,
    this.customerPhone,
    this.customerTin,
    this.notes,
    this.items = const [],
    this.cashierName,
    this.branchName,
    required this.createdAt,
    this.createdAtHuman,
    this.validUntil,
    this.fiscalReceiptNumber,
    this.fiscalVerificationCode,
    this.fiscalQrCode,
    this.fiscalSubmitted = false,
    this.whatsappReceiptStatus,
  });

  factory Transaction.fromJson(Map<String, dynamic> json) {
    return Transaction(
      id: json['id'],
      transactionNumber: json['transaction_number'],
      type: json['type'] ?? 'sale',
      status: json['status'],
      subtotal: (json['subtotal'] ?? 0).toDouble(),
      taxAmount: (json['tax_amount'] ?? 0).toDouble(),
      discountAmount: (json['discount_amount'] ?? 0).toDouble(),
      total: (json['total'] ?? 0).toDouble(),
      paymentMethod: json['payment_method'],
      amountPaid: (json['amount_paid'] ?? 0).toDouble(),
      changeGiven: (json['change_given'] ?? 0).toDouble(),
      customerId: json['customer_id'],
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
      validUntil: json['valid_until'],
      fiscalReceiptNumber: json['fiscal']?['receipt_number'],
      fiscalVerificationCode: json['fiscal']?['verification_code'],
      fiscalQrCode: json['fiscal']?['qr_code'],
      fiscalSubmitted: json['fiscal']?['submitted'] ?? false,
      whatsappReceiptStatus: json['whatsapp_receipt_status'],
    );
  }

  bool get isOrder => type == 'order';
  bool get isCompleted => status == 'completed';
  bool get isVoided => status == 'voided';
  bool get isPending => status == 'pending';
  bool get isCancelled => status == 'cancelled';

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
      case 'credit':
        return 'Credit';
      default:
        return paymentMethod ?? '-';
    }
  }

  /// Convert to receipt data format for PDF generation
  Map<String, dynamic> toReceiptData() {
    return {
      'id': id,
      'transaction_number': transactionNumber,
      'status': status,
      'subtotal': subtotal,
      'tax_amount': taxAmount,
      'discount_amount': discountAmount,
      'total': total,
      'payment_method': paymentMethod,
      'amount_paid': amountPaid,
      'change_given': changeGiven,
      'customer_name': customerName,
      'customer_phone': customerPhone,
      'customer_tin': customerTin,
      'notes': notes,
      'cashier': {'name': cashierName},
      'branch': branchName,
      'created_at': createdAt,
      'items': items.map((item) => {
        'id': item.id,
        'product_id': item.productId,
        'product_name': item.productName,
        'quantity': item.quantity,
        'unit_price': item.unitPrice,
        'tax_rate': item.taxRate,
        'tax_amount': item.taxAmount,
        'subtotal': item.subtotal,
      }).toList(),
    };
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
