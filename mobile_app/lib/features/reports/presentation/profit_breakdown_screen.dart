import 'dart:math' as math;

import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';
import '../../../shared/models/profit_breakdown.dart';
import '../data/profit_breakdown_provider.dart';

class ProfitBreakdownScreen extends ConsumerStatefulWidget {
  const ProfitBreakdownScreen({super.key});

  @override
  ConsumerState<ProfitBreakdownScreen> createState() =>
      _ProfitBreakdownScreenState();
}

class _ProfitBreakdownScreenState extends ConsumerState<ProfitBreakdownScreen> {
  static final _currencyFormat =
      NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);
  static final _compactFormat =
      NumberFormat.compactCurrency(symbol: 'TZS ', decimalDigits: 0);

  List<Map<String, dynamic>> _expenseCategories = [];
  bool _bootstrapped = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      await hydrateProfitIncludeExpenses(ref);
      await _loadAuxiliaryFilters();
      if (mounted) setState(() => _bootstrapped = true);
    });
  }

  Future<void> _loadAuxiliaryFilters() async {
    final api = ref.read(apiClientProvider);
    try {
      final catRes = await api.getExpenseCategories();
      _expenseCategories =
          List<Map<String, dynamic>>.from(catRes.data['data'] ?? const []);
    } catch (_) {
      _expenseCategories = [];
    }
  }

  @override
  Widget build(BuildContext context) {
    final filters = ref.watch(profitFiltersProvider);
    final asyncReport = ref.watch(profitBreakdownProvider(filters));

    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: const Text('Profit Breakdown'),
        centerTitle: true,
      ),
      body: !_bootstrapped
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: () async {
                ref.invalidate(profitBreakdownProvider(filters));
                await ref.read(profitBreakdownProvider(filters).future);
              },
              child: CustomScrollView(
                slivers: [
                  SliverToBoxAdapter(child: _buildFilters(filters)),
                  asyncReport.when(
                    data: (report) => SliverToBoxAdapter(
                      child: _buildContent(report, filters),
                    ),
                    loading: () => const SliverFillRemaining(
                      hasScrollBody: false,
                      child: Center(child: CircularProgressIndicator()),
                    ),
                    error: (e, _) => SliverFillRemaining(
                      hasScrollBody: false,
                      child: Center(
                        child: Padding(
                          padding: const EdgeInsets.all(24),
                          child: Text('Failed to load: $e',
                              textAlign: TextAlign.center),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
    );
  }

  Widget _buildFilters(ProfitFilters filters) {
    const periods = [
      ('today', 'Today'),
      ('week', 'Week'),
      ('month', 'Month'),
      ('quarter', 'Quarter'),
      ('year', 'Year'),
    ];

    return Container(
      color: Colors.white,
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: periods
                  .map((p) => _buildPeriodChip(p.$1, p.$2, filters))
                  .toList(),
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: SwitchListTile.adaptive(
                  contentPadding: EdgeInsets.zero,
                  dense: true,
                  title: const Text('Subtract expenses from net profit',
                      style: TextStyle(fontSize: 13)),
                  subtitle: Text(
                    filters.includeExpenses
                        ? 'Net Profit = Revenue − COGS − Expenses'
                        : 'Net Profit = Revenue − COGS',
                    style: const TextStyle(
                        fontSize: 11, color: AppColors.textSecondary),
                  ),
                  value: filters.includeExpenses,
                  onChanged: (v) async {
                    ref
                        .read(profitFiltersProvider.notifier)
                        .setIncludeExpenses(v);
                    await persistProfitIncludeExpenses(ref, v);
                  },
                ),
              ),
            ],
          ),
          if (_expenseCategories.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 4),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    _buildCategoryChip(null, 'All categories', filters),
                    const SizedBox(width: 6),
                    ..._expenseCategories.expand((c) => [
                          _buildCategoryChip(
                              c['id'] as int?, c['name'] as String, filters),
                          const SizedBox(width: 6),
                        ]),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildPeriodChip(String key, String label, ProfitFilters filters) {
    final selected = filters.period == key;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: ChoiceChip(
        label: Text(label),
        selected: selected,
        onSelected: (_) {
          ref.read(profitFiltersProvider.notifier).setPeriod(key);
        },
      ),
    );
  }

  Widget _buildCategoryChip(int? id, String label, ProfitFilters filters) {
    final selected = id == null
        ? filters.expenseCategoryIds.isEmpty
        : filters.expenseCategoryIds.contains(id);
    return FilterChip(
      label: Text(label, style: const TextStyle(fontSize: 12)),
      selected: selected,
      onSelected: (_) {
        if (id == null) {
          ref.read(profitFiltersProvider.notifier).setExpenseCategories([]);
        } else {
          ref.read(profitFiltersProvider.notifier).toggleExpenseCategory(id);
        }
      },
    );
  }

  Widget _buildContent(ProfitBreakdown report, ProfitFilters filters) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildPeriodHeader(report.period),
          const SizedBox(height: 16),
          _buildSummaryCards(report.summary),
          const SizedBox(height: 16),
          if (report.trend.isNotEmpty) ...[
            _buildTrendCard(report.trend, filters.includeExpenses),
            const SizedBox(height: 16),
          ],
          if (report.expensesByCategory.isNotEmpty) ...[
            _buildExpenseCategoriesCard(
                report.expensesByCategory, report.summary.operatingExpenses),
            const SizedBox(height: 16),
          ],
          if (report.branches.isNotEmpty) ...[
            _buildBranchComparisonCard(report.branches),
            const SizedBox(height: 16),
          ],
          if (report.topProductsByProfit.isNotEmpty) ...[
            _buildTopProductsCard(report.topProductsByProfit),
            const SizedBox(height: 16),
          ],
        ],
      ),
    );
  }

  Widget _buildPeriodHeader(ProfitPeriod period) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          period.label,
          style: const TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: AppColors.textPrimary,
          ),
        ),
        Text(
          '${period.from} → ${period.to}',
          style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
        ),
      ],
    );
  }

  Widget _buildSummaryCards(ProfitSummary s) {
    return _Card(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _heroNetProfit(s),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                  child: _kpiTile('Revenue', s.revenue, AppColors.primary)),
              const SizedBox(width: 8),
              Expanded(
                  child:
                      _kpiTile('COGS', s.cogs, AppColors.warning, signed: -1)),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: _kpiTile(
                  'Gross Profit',
                  s.grossProfit,
                  s.grossProfit >= 0 ? AppColors.success : AppColors.error,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _kpiTile(
                  'Operating Expenses',
                  s.operatingExpenses,
                  AppColors.error,
                  signed: -1,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _heroNetProfit(ProfitSummary s) {
    final positive = s.netProfit >= 0;
    final color = positive ? AppColors.success : AppColors.error;
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [color.withValues(alpha: 0.10), color.withValues(alpha: 0.04)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Row(
        children: [
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(
              positive ? Icons.trending_up_rounded : Icons.trending_down_rounded,
              color: color,
              size: 26,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Net Profit',
                    style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                        color: color.withValues(alpha: 0.8))),
                const SizedBox(height: 2),
                Text(_currencyFormat.format(s.netProfit),
                    style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.w800,
                        color: color)),
                Text(
                  s.includeExpenses
                      ? 'Revenue − COGS − Expenses'
                      : 'Revenue − COGS',
                  style: const TextStyle(
                      fontSize: 11, color: AppColors.textSecondary),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text(
              '${s.netMargin.toStringAsFixed(1)}%',
              style: TextStyle(
                  fontSize: 16, fontWeight: FontWeight.w700, color: color),
            ),
          ),
        ],
      ),
    );
  }

  Widget _kpiTile(String label, double value, Color color, {int signed = 1}) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.gray6,
        borderRadius: BorderRadius.circular(10),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label,
              style: const TextStyle(
                  fontSize: 11, color: AppColors.textSecondary)),
          const SizedBox(height: 4),
          Text(
            (signed < 0 ? '- ' : '') + _compactFormat.format(value.abs()),
            style: TextStyle(
                fontSize: 16, fontWeight: FontWeight.w700, color: color),
          ),
        ],
      ),
    );
  }

  Widget _buildTrendCard(List<TrendPoint> trend, bool includeExpenses) {
    final maxY = trend
        .map((t) => math.max(t.revenue, t.cogs))
        .fold<double>(0, math.max);
    final spotsRevenue = <FlSpot>[];
    final spotsProfit = <FlSpot>[];
    for (var i = 0; i < trend.length; i++) {
      spotsRevenue.add(FlSpot(i.toDouble(), trend[i].revenue));
      spotsProfit.add(FlSpot(
          i.toDouble(),
          includeExpenses ? trend[i].netProfit : trend[i].grossProfit));
    }

    return _Card(
      title: 'Trend',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            height: 200,
            child: LineChart(
              LineChartData(
                minY: 0,
                maxY: maxY <= 0 ? 1 : maxY * 1.15,
                gridData: const FlGridData(show: false),
                titlesData: FlTitlesData(
                  show: true,
                  topTitles: const AxisTitles(
                      sideTitles: SideTitles(showTitles: false)),
                  rightTitles: const AxisTitles(
                      sideTitles: SideTitles(showTitles: false)),
                  leftTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      reservedSize: 44,
                      getTitlesWidget: (v, _) => Text(
                        _compactFormat.format(v),
                        style: const TextStyle(fontSize: 10),
                      ),
                    ),
                  ),
                  bottomTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      reservedSize: 22,
                      interval: math.max(1, (trend.length / 6).ceilToDouble()),
                      getTitlesWidget: (v, _) {
                        final i = v.toInt();
                        if (i < 0 || i >= trend.length) {
                          return const SizedBox.shrink();
                        }
                        final b = trend[i].bucket;
                        final short = b.length >= 10 ? b.substring(5) : b;
                        return Padding(
                          padding: const EdgeInsets.only(top: 4),
                          child: Text(short,
                              style: const TextStyle(fontSize: 9)),
                        );
                      },
                    ),
                  ),
                ),
                borderData: FlBorderData(show: false),
                lineBarsData: [
                  LineChartBarData(
                    spots: spotsRevenue,
                    isCurved: true,
                    color: AppColors.primary,
                    barWidth: 2,
                    dotData: const FlDotData(show: false),
                  ),
                  LineChartBarData(
                    spots: spotsProfit,
                    isCurved: true,
                    color: AppColors.success,
                    barWidth: 2,
                    dotData: const FlDotData(show: false),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              _legendDot(AppColors.primary, 'Revenue'),
              const SizedBox(width: 16),
              _legendDot(AppColors.success,
                  includeExpenses ? 'Net Profit' : 'Gross Profit'),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildExpenseCategoriesCard(
      List<ExpenseCategoryShare> shares, double totalExpenses) {
    return _Card(
      title: 'Operating Expenses by Category',
      child: Column(
        children: shares
            .take(8)
            .map((c) => _categoryRow(c, totalExpenses))
            .toList(),
      ),
    );
  }

  Widget _categoryRow(ExpenseCategoryShare c, double total) {
    final pct = total > 0 ? (c.amount / total).clamp(0.0, 1.0) : 0.0;
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(c.name,
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
              Text(_currencyFormat.format(c.amount),
                  style: const TextStyle(
                      fontSize: 13, fontWeight: FontWeight.w600)),
            ],
          ),
          const SizedBox(height: 6),
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(
              value: pct,
              minHeight: 6,
              backgroundColor: AppColors.gray5,
              valueColor: const AlwaysStoppedAnimation(AppColors.warning),
            ),
          ),
          const SizedBox(height: 2),
          Text('${c.percentage.toStringAsFixed(1)}% of expenses',
              style: const TextStyle(
                  fontSize: 11, color: AppColors.textSecondary)),
        ],
      ),
    );
  }

  Widget _buildBranchComparisonCard(List<BranchProfit> branches) {
    final maxProfit = branches
        .map((b) => b.netProfit.abs())
        .fold<double>(0, math.max);

    return _Card(
      title: 'Branch Comparison',
      child: Column(
        children: branches.map((b) {
          final ratio = maxProfit > 0
              ? (b.netProfit.abs() / maxProfit).clamp(0.0, 1.0)
              : 0.0;
          final color =
              b.netProfit >= 0 ? AppColors.success : AppColors.error;
          return Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(b.name,
                        style: const TextStyle(
                            fontSize: 13, fontWeight: FontWeight.w500)),
                    Text(_currencyFormat.format(b.netProfit),
                        style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: color)),
                  ],
                ),
                const SizedBox(height: 6),
                ClipRRect(
                  borderRadius: BorderRadius.circular(4),
                  child: LinearProgressIndicator(
                    value: ratio,
                    minHeight: 6,
                    backgroundColor: AppColors.gray5,
                    valueColor: AlwaysStoppedAnimation(color),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  'Revenue ${_compactFormat.format(b.revenue)} • Margin ${b.netMargin.toStringAsFixed(1)}%',
                  style: const TextStyle(
                      fontSize: 11, color: AppColors.textSecondary),
                ),
              ],
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildTopProductsCard(List<ProductProfit> products) {
    return _Card(
      title: 'Top Products by Profit',
      child: Column(
        children: List.generate(products.length, (i) {
          final p = products[i];
          return Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: Row(
              children: [
                Container(
                  width: 24,
                  height: 24,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    color: AppColors.gray6,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text('${i + 1}',
                      style: const TextStyle(
                          fontSize: 11, fontWeight: FontWeight.w700)),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(p.name,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                              fontSize: 13, fontWeight: FontWeight.w600)),
                      Text(
                        '${p.quantity.toStringAsFixed(0)} sold • ${p.margin.toStringAsFixed(1)}% margin',
                        style: const TextStyle(
                            fontSize: 11, color: AppColors.textSecondary),
                      ),
                    ],
                  ),
                ),
                Text(
                  _currencyFormat.format(p.grossProfit),
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: p.grossProfit >= 0
                        ? AppColors.success
                        : AppColors.error,
                  ),
                ),
              ],
            ),
          );
        }),
      ),
    );
  }

  Widget _legendDot(Color color, String label) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 8,
          height: 8,
          decoration: BoxDecoration(
              color: color, borderRadius: BorderRadius.circular(2)),
        ),
        const SizedBox(width: 6),
        Text(label,
            style:
                const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
      ],
    );
  }
}

class _Card extends StatelessWidget {
  final String? title;
  final Widget child;

  const _Card({this.title, required this.child});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (title != null) ...[
            Text(title!,
                style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                    color: AppColors.textPrimary)),
            const SizedBox(height: 12),
          ],
          child,
        ],
      ),
    );
  }
}
