import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../../app/theme/colors.dart';

class ProfitBreakdownWidget extends StatefulWidget {
  final Map<String, dynamic>? dashboardData;

  const ProfitBreakdownWidget({super.key, required this.dashboardData});

  @override
  State<ProfitBreakdownWidget> createState() => _ProfitBreakdownWidgetState();
}

class _ProfitBreakdownWidgetState extends State<ProfitBreakdownWidget>
    with SingleTickerProviderStateMixin {
  static final _currencyFormat =
      NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);
  static final _compactFormat =
      NumberFormat.compactCurrency(symbol: 'TZS ', decimalDigits: 0);

  bool _showToday = true;
  late AnimationController _animController;
  late Animation<double> _barAnimation;

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 800),
    );
    _barAnimation = CurvedAnimation(
      parent: _animController,
      curve: Curves.easeOutCubic,
    );
    _animController.forward();
  }

  @override
  void dispose() {
    _animController.dispose();
    super.dispose();
  }

  void _togglePeriod() {
    setState(() => _showToday = !_showToday);
    _animController.reset();
    _animController.forward();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final today = widget.dashboardData?['today'] as Map<String, dynamic>? ?? {};
    final month =
        widget.dashboardData?['this_month'] as Map<String, dynamic>? ?? {};

    final data = _showToday ? today : month;

    final revenue =
        (data[_showToday ? 'sales_total' : 'revenue'] ?? 0).toDouble();
    final expenses = (data['expenses'] ?? 0).toDouble();
    final cogs = (data['cogs'] ?? 0).toDouble();
    final grossProfit = _showToday ? revenue - cogs : (data['gross_profit'] ?? 0).toDouble();
    final netProfit = (data['net_profit'] ?? 0).toDouble();
    final profitMargin = _showToday
        ? (revenue > 0 ? (netProfit / revenue * 100) : 0.0)
        : (data['profit_margin'] ?? 0).toDouble();

    // For the stacked bar: total outflow = cogs + expenses
    final totalOutflow = cogs + expenses;
    final double maxBar = math.max(revenue, totalOutflow);

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
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
            // Header with toggle
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  l10n?.profitBreakdown ?? 'Profit Breakdown',
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: AppColors.textPrimary,
                  ),
                ),
                _buildPeriodToggle(l10n),
              ],
            ),

            const SizedBox(height: 20),

            // Net Profit Hero
            _buildNetProfitHero(netProfit, profitMargin, l10n),

            const SizedBox(height: 20),

            // Visual bar breakdown
            _buildVisualBreakdown(
              revenue: revenue,
              cogs: cogs,
              expenses: expenses,
              maxBar: maxBar,
              l10n: l10n,
            ),

            const SizedBox(height: 16),

            // Breakdown legend/details
            _buildBreakdownDetails(
              revenue: revenue,
              cogs: cogs,
              grossProfit: grossProfit,
              expenses: expenses,
              netProfit: netProfit,
              l10n: l10n,
              showCogs: !_showToday || cogs > 0,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPeriodToggle(AppLocalizations? l10n) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.gray6,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _buildToggleTab(
            label: l10n?.today ?? 'Today',
            isActive: _showToday,
            onTap: () {
              if (!_showToday) _togglePeriod();
            },
          ),
          _buildToggleTab(
            label: l10n?.thisMonth ?? 'Month',
            isActive: !_showToday,
            onTap: () {
              if (_showToday) _togglePeriod();
            },
          ),
        ],
      ),
    );
  }

  Widget _buildToggleTab({
    required String label,
    required bool isActive,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
        decoration: BoxDecoration(
          color: isActive ? Colors.white : Colors.transparent,
          borderRadius: BorderRadius.circular(7),
          boxShadow: isActive
              ? [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.06),
                    blurRadius: 4,
                    offset: const Offset(0, 1),
                  ),
                ]
              : null,
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 13,
            fontWeight: isActive ? FontWeight.w600 : FontWeight.w500,
            color: isActive ? AppColors.textPrimary : AppColors.textSecondary,
          ),
        ),
      ),
    );
  }

  Widget _buildNetProfitHero(
      double netProfit, double profitMargin, AppLocalizations? l10n) {
    final isPositive = netProfit >= 0;
    final heroColor = isPositive ? AppColors.success : AppColors.error;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            heroColor.withValues(alpha: 0.08),
            heroColor.withValues(alpha: 0.03),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: heroColor.withValues(alpha: 0.2),
        ),
      ),
      child: Row(
        children: [
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: heroColor.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(
              isPositive
                  ? Icons.trending_up_rounded
                  : Icons.trending_down_rounded,
              color: heroColor,
              size: 26,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  l10n?.netProfit ?? 'Net Profit',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                    color: heroColor.withValues(alpha: 0.8),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  _currencyFormat.format(netProfit),
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.w800,
                    color: heroColor,
                  ),
                ),
              ],
            ),
          ),
          if (profitMargin != 0)
            Container(
              padding:
                  const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: heroColor.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                '${profitMargin.toStringAsFixed(1)}%',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: heroColor,
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildVisualBreakdown({
    required double revenue,
    required double cogs,
    required double expenses,
    required double maxBar,
    required AppLocalizations? l10n,
  }) {
    return AnimatedBuilder(
      animation: _barAnimation,
      builder: (context, _) {
        final progress = _barAnimation.value;
        final revenueRatio =
            maxBar > 0 ? (revenue / maxBar) * progress : 0.0;
        final cogsRatio = maxBar > 0 ? (cogs / maxBar) * progress : 0.0;
        final expensesRatio =
            maxBar > 0 ? (expenses / maxBar) * progress : 0.0;

        return Column(
          children: [
            // Revenue bar
            _buildBarRow(
              label: l10n?.revenue ?? 'Revenue',
              value: _compactFormat.format(revenue * progress),
              ratio: revenueRatio,
              color: AppColors.primary,
              icon: Icons.arrow_upward_rounded,
            ),
            const SizedBox(height: 10),
            // Costs bar (COGS + Expenses stacked)
            _buildStackedBarRow(
              label: l10n?.totalCosts ?? 'Total Costs',
              value: _compactFormat.format((cogs + expenses) * progress),
              segments: [
                _BarSegment(
                    ratio: cogsRatio,
                    color: AppColors.warning),
                _BarSegment(
                    ratio: expensesRatio,
                    color: AppColors.error),
              ],
              icon: Icons.arrow_downward_rounded,
            ),
          ],
        );
      },
    );
  }

  Widget _buildBarRow({
    required String label,
    required String value,
    required double ratio,
    required Color color,
    required IconData icon,
  }) {
    return Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(icon, size: 14, color: color),
                const SizedBox(width: 4),
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                    color: AppColors.textSecondary,
                  ),
                ),
              ],
            ),
            Text(
              value,
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: color,
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(4),
          child: SizedBox(
            height: 10,
            child: Stack(
              children: [
                Container(
                  decoration: BoxDecoration(
                    color: AppColors.gray5,
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
                FractionallySizedBox(
                  widthFactor: ratio.clamp(0.0, 1.0),
                  child: Container(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [color, color.withValues(alpha: 0.7)],
                      ),
                      borderRadius: BorderRadius.circular(4),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildStackedBarRow({
    required String label,
    required String value,
    required List<_BarSegment> segments,
    required IconData icon,
  }) {
    return Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(icon, size: 14, color: AppColors.error),
                const SizedBox(width: 4),
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                    color: AppColors.textSecondary,
                  ),
                ),
              ],
            ),
            Text(
              value,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: AppColors.error,
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(4),
          child: SizedBox(
            height: 10,
            child: Stack(
              children: [
                Container(
                  decoration: BoxDecoration(
                    color: AppColors.gray5,
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
                Row(
                  children: [
                    for (final seg in segments)
                      Flexible(
                        flex: ((seg.ratio * 1000).round()).clamp(0, 1000),
                        child: Container(
                          decoration: BoxDecoration(
                            color: seg.color,
                          ),
                        ),
                      ),
                    Flexible(
                      flex: ((1.0 -
                                  segments.fold<double>(
                                      0, (sum, s) => sum + s.ratio)) *
                              1000)
                          .round()
                          .clamp(0, 1000),
                      child: const SizedBox.shrink(),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
        // Legend for stacked bar
        const SizedBox(height: 6),
        Row(
          children: [
            _buildLegendDot(AppColors.warning,
                AppLocalizations.of(context)?.costOfGoods ?? 'COGS'),
            const SizedBox(width: 16),
            _buildLegendDot(AppColors.error,
                AppLocalizations.of(context)?.expenses ?? 'Expenses'),
          ],
        ),
      ],
    );
  }

  Widget _buildLegendDot(Color color, String label) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 8,
          height: 8,
          decoration: BoxDecoration(
            color: color,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 4),
        Text(
          label,
          style: const TextStyle(
            fontSize: 11,
            color: AppColors.textSecondary,
          ),
        ),
      ],
    );
  }

  Widget _buildBreakdownDetails({
    required double revenue,
    required double cogs,
    required double grossProfit,
    required double expenses,
    required double netProfit,
    required AppLocalizations? l10n,
    required bool showCogs,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.gray6.withValues(alpha: 0.6),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        children: [
          _buildDetailRow(
            l10n?.revenue ?? 'Revenue',
            _currencyFormat.format(revenue),
            color: AppColors.primary,
          ),
          if (showCogs) ...[
            const SizedBox(height: 8),
            _buildDetailRow(
              l10n?.costOfGoods ?? 'Cost of Goods',
              '- ${_currencyFormat.format(cogs)}',
              color: AppColors.warning,
            ),
            const SizedBox(height: 8),
            _buildDetailRow(
              l10n?.grossProfit ?? 'Gross Profit',
              _currencyFormat.format(grossProfit),
              isBold: true,
            ),
          ],
          const SizedBox(height: 8),
          _buildDetailRow(
            l10n?.expenses ?? 'Expenses',
            '- ${_currencyFormat.format(expenses)}',
            color: AppColors.error,
          ),
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: Divider(
              height: 1,
              color: AppColors.gray3.withValues(alpha: 0.5),
            ),
          ),
          _buildDetailRow(
            l10n?.netProfit ?? 'Net Profit',
            _currencyFormat.format(netProfit),
            isBold: true,
            color: netProfit >= 0 ? AppColors.success : AppColors.error,
          ),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value,
      {bool isBold = false, Color? color}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 13,
            fontWeight: isBold ? FontWeight.w600 : FontWeight.w500,
            color: AppColors.textSecondary,
          ),
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: 13,
            fontWeight: isBold ? FontWeight.w700 : FontWeight.w600,
            color: color ?? AppColors.textPrimary,
          ),
        ),
      ],
    );
  }
}

class _BarSegment {
  final double ratio;
  final Color color;

  const _BarSegment({required this.ratio, required this.color});
}
