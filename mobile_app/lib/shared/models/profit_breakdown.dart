class ProfitBreakdown {
  final ProfitPeriod period;
  final ProfitSummary summary;
  final List<TrendPoint> trend;
  final List<ExpenseCategoryShare> expensesByCategory;
  final List<BranchProfit> branches;
  final List<ProductProfit> topProductsByProfit;

  ProfitBreakdown({
    required this.period,
    required this.summary,
    required this.trend,
    required this.expensesByCategory,
    required this.branches,
    required this.topProductsByProfit,
  });

  factory ProfitBreakdown.fromJson(Map<String, dynamic> json) {
    return ProfitBreakdown(
      period: ProfitPeriod.fromJson(json['period'] as Map<String, dynamic>),
      summary: ProfitSummary.fromJson(json['summary'] as Map<String, dynamic>),
      trend: (json['trend'] as List? ?? [])
          .map((e) => TrendPoint.fromJson(e as Map<String, dynamic>))
          .toList(),
      expensesByCategory: (json['expenses_by_category'] as List? ?? [])
          .map((e) => ExpenseCategoryShare.fromJson(e as Map<String, dynamic>))
          .toList(),
      branches: (json['branches'] as List? ?? [])
          .map((e) => BranchProfit.fromJson(e as Map<String, dynamic>))
          .toList(),
      topProductsByProfit: (json['top_products_by_profit'] as List? ?? [])
          .map((e) => ProductProfit.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class ProfitPeriod {
  final String key;
  final String from;
  final String to;
  final String label;

  ProfitPeriod({
    required this.key,
    required this.from,
    required this.to,
    required this.label,
  });

  factory ProfitPeriod.fromJson(Map<String, dynamic> j) => ProfitPeriod(
        key: j['key'] as String? ?? 'month',
        from: j['from'] as String? ?? '',
        to: j['to'] as String? ?? '',
        label: j['label'] as String? ?? '',
      );
}

class ProfitSummary {
  final double revenue;
  final double cogs;
  final double grossProfit;
  final double operatingExpenses;
  final double netProfit;
  final double grossMargin;
  final double netMargin;
  final int transactionsCount;
  final bool includeExpenses;

  ProfitSummary({
    required this.revenue,
    required this.cogs,
    required this.grossProfit,
    required this.operatingExpenses,
    required this.netProfit,
    required this.grossMargin,
    required this.netMargin,
    required this.transactionsCount,
    required this.includeExpenses,
  });

  factory ProfitSummary.fromJson(Map<String, dynamic> j) => ProfitSummary(
        revenue: _toDouble(j['revenue']),
        cogs: _toDouble(j['cogs']),
        grossProfit: _toDouble(j['gross_profit']),
        operatingExpenses: _toDouble(j['operating_expenses']),
        netProfit: _toDouble(j['net_profit']),
        grossMargin: _toDouble(j['gross_margin']),
        netMargin: _toDouble(j['net_margin']),
        transactionsCount: (j['transactions_count'] as num? ?? 0).toInt(),
        includeExpenses: j['include_expenses'] as bool? ?? true,
      );
}

class TrendPoint {
  final String bucket;
  final double revenue;
  final double cogs;
  final double expenses;
  final double grossProfit;
  final double netProfit;

  TrendPoint({
    required this.bucket,
    required this.revenue,
    required this.cogs,
    required this.expenses,
    required this.grossProfit,
    required this.netProfit,
  });

  factory TrendPoint.fromJson(Map<String, dynamic> j) => TrendPoint(
        bucket: j['bucket']?.toString() ?? '',
        revenue: _toDouble(j['revenue']),
        cogs: _toDouble(j['cogs']),
        expenses: _toDouble(j['expenses']),
        grossProfit: _toDouble(j['gross_profit']),
        netProfit: _toDouble(j['net_profit']),
      );
}

class ExpenseCategoryShare {
  final int? id;
  final String name;
  final double amount;
  final double percentage;

  ExpenseCategoryShare({
    required this.id,
    required this.name,
    required this.amount,
    required this.percentage,
  });

  factory ExpenseCategoryShare.fromJson(Map<String, dynamic> j) =>
      ExpenseCategoryShare(
        id: (j['id'] as num?)?.toInt(),
        name: j['name'] as String? ?? 'Uncategorized',
        amount: _toDouble(j['amount']),
        percentage: _toDouble(j['percentage']),
      );
}

class BranchProfit {
  final int id;
  final String name;
  final double revenue;
  final double grossProfit;
  final double operatingExpenses;
  final double netProfit;
  final double netMargin;

  BranchProfit({
    required this.id,
    required this.name,
    required this.revenue,
    required this.grossProfit,
    required this.operatingExpenses,
    required this.netProfit,
    required this.netMargin,
  });

  factory BranchProfit.fromJson(Map<String, dynamic> j) => BranchProfit(
        id: (j['id'] as num).toInt(),
        name: j['name'] as String? ?? '',
        revenue: _toDouble(j['revenue']),
        grossProfit: _toDouble(j['gross_profit']),
        operatingExpenses: _toDouble(j['operating_expenses']),
        netProfit: _toDouble(j['net_profit']),
        netMargin: _toDouble(j['net_margin']),
      );
}

class ProductProfit {
  final int productId;
  final String name;
  final double quantity;
  final double revenue;
  final double cogs;
  final double grossProfit;
  final double margin;

  ProductProfit({
    required this.productId,
    required this.name,
    required this.quantity,
    required this.revenue,
    required this.cogs,
    required this.grossProfit,
    required this.margin,
  });

  factory ProductProfit.fromJson(Map<String, dynamic> j) => ProductProfit(
        productId: (j['product_id'] as num?)?.toInt() ?? 0,
        name: j['name'] as String? ?? '',
        quantity: _toDouble(j['quantity']),
        revenue: _toDouble(j['revenue']),
        cogs: _toDouble(j['cogs']),
        grossProfit: _toDouble(j['gross_profit']),
        margin: _toDouble(j['margin']),
      );
}

double _toDouble(dynamic v) {
  if (v == null) return 0.0;
  if (v is num) return v.toDouble();
  return double.tryParse(v.toString()) ?? 0.0;
}

class ProfitFilters {
  final String period;
  final String? dateFrom;
  final String? dateTo;
  final int? branchId;
  final bool includeExpenses;
  final List<int> expenseCategoryIds;

  const ProfitFilters({
    this.period = 'month',
    this.dateFrom,
    this.dateTo,
    this.branchId,
    this.includeExpenses = true,
    this.expenseCategoryIds = const [],
  });

  ProfitFilters copyWith({
    String? period,
    String? dateFrom,
    String? dateTo,
    int? branchId,
    bool clearBranchId = false,
    bool? includeExpenses,
    List<int>? expenseCategoryIds,
  }) {
    return ProfitFilters(
      period: period ?? this.period,
      dateFrom: dateFrom ?? this.dateFrom,
      dateTo: dateTo ?? this.dateTo,
      branchId: clearBranchId ? null : (branchId ?? this.branchId),
      includeExpenses: includeExpenses ?? this.includeExpenses,
      expenseCategoryIds: expenseCategoryIds ?? this.expenseCategoryIds,
    );
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is ProfitFilters &&
          other.period == period &&
          other.dateFrom == dateFrom &&
          other.dateTo == dateTo &&
          other.branchId == branchId &&
          other.includeExpenses == includeExpenses &&
          _listEq(other.expenseCategoryIds, expenseCategoryIds);

  @override
  int get hashCode => Object.hash(
        period,
        dateFrom,
        dateTo,
        branchId,
        includeExpenses,
        Object.hashAll(expenseCategoryIds),
      );
}

bool _listEq(List<int> a, List<int> b) {
  if (a.length != b.length) return false;
  for (var i = 0; i < a.length; i++) {
    if (a[i] != b[i]) return false;
  }
  return true;
}
