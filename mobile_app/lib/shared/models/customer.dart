class Customer {
  final int id;
  final String name;
  final String phone;
  final String? email;
  final String? tin;
  final String? address;
  final double creditLimit;
  final double currentBalance;
  final double availableCredit;
  final String? notes;
  final bool isActive;
  final String createdAt;

  Customer({
    required this.id,
    required this.name,
    required this.phone,
    this.email,
    this.tin,
    this.address,
    required this.creditLimit,
    required this.currentBalance,
    required this.availableCredit,
    this.notes,
    required this.isActive,
    required this.createdAt,
  });

  factory Customer.fromJson(Map<String, dynamic> json) {
    return Customer(
      id: json['id'],
      name: json['name'],
      phone: json['phone'],
      email: json['email'],
      tin: json['tin'],
      address: json['address'],
      creditLimit: (json['credit_limit'] ?? 0).toDouble(),
      currentBalance: (json['current_balance'] ?? 0).toDouble(),
      availableCredit: (json['available_credit'] ?? 0).toDouble(),
      notes: json['notes'],
      isActive: json['is_active'] ?? true,
      createdAt: json['created_at'] ?? '',
    );
  }

  bool get hasCredit => creditLimit > 0;
  bool get hasOutstandingBalance => currentBalance > 0;
}

class CustomerCreditTransaction {
  final int id;
  final String type;
  final String typeLabel;
  final double amount;
  final double balanceBefore;
  final double balanceAfter;
  final String? paymentMethod;
  final String? reference;
  final String? notes;
  final String userName;
  final int? transactionId;
  final String createdAt;
  final String? createdAtHuman;

  CustomerCreditTransaction({
    required this.id,
    required this.type,
    required this.typeLabel,
    required this.amount,
    required this.balanceBefore,
    required this.balanceAfter,
    this.paymentMethod,
    this.reference,
    this.notes,
    required this.userName,
    this.transactionId,
    required this.createdAt,
    this.createdAtHuman,
  });

  factory CustomerCreditTransaction.fromJson(Map<String, dynamic> json) {
    return CustomerCreditTransaction(
      id: json['id'],
      type: json['type'],
      typeLabel: json['type_label'] ?? json['type'],
      amount: (json['amount'] ?? 0).toDouble(),
      balanceBefore: (json['balance_before'] ?? 0).toDouble(),
      balanceAfter: (json['balance_after'] ?? 0).toDouble(),
      paymentMethod: json['payment_method'],
      reference: json['reference'],
      notes: json['notes'],
      userName: json['user']?['name'] ?? '',
      transactionId: json['transaction_id'],
      createdAt: json['created_at'] ?? '',
      createdAtHuman: json['created_at_human'],
    );
  }

  bool get isPayment => type == 'payment';
  bool get isCreditSale => type == 'sale_on_credit';
  bool get isAdjustment => type == 'adjustment';
}
