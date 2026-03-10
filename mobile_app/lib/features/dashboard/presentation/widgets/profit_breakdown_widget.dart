import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../../app/theme/colors.dart';

class ProfitBreakdownWidget extends StatelessWidget {
  final Map<String, dynamic>? dashboardData;

  const ProfitBreakdownWidget({super.key, required this.dashboardData});

  static final _currencyFormat =
      NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final today = dashboardData?['today'] as Map<String, dynamic>? ?? {};
    final month =
        dashboardData?['this_month'] as Map<String, dynamic>? ?? {};

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.divider),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              l10n?.profitBreakdown ?? 'Profit Breakdown',
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 16),

            // Today section
            Text(
              l10n?.today ?? 'Today',
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
            ),
            const SizedBox(height: 8),
            _buildRow(
              l10n?.revenue ?? 'Revenue',
              _currencyFormat.format(
                  (today['sales_total'] ?? 0).toDouble()),
            ),
            _buildRow(
              l10n?.expenses ?? 'Expenses',
              '- ${_currencyFormat.format((today['expenses'] ?? 0).toDouble())}',
              valueColor: AppColors.error,
            ),
            const Divider(height: 16),
            _buildRow(
              l10n?.netProfit ?? 'Net Profit',
              _currencyFormat
                  .format((today['net_profit'] ?? 0).toDouble()),
              isBold: true,
              valueColor: (today['net_profit'] ?? 0).toDouble() >= 0
                  ? AppColors.success
                  : AppColors.error,
            ),

            const SizedBox(height: 20),

            // This Month section
            Text(
              l10n?.thisMonth ?? 'This Month',
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
            ),
            const SizedBox(height: 8),
            _buildRow(
              l10n?.revenue ?? 'Revenue',
              _currencyFormat.format(
                  (month['revenue'] ?? 0).toDouble()),
            ),
            _buildRow(
              l10n?.costOfGoods ?? 'Cost of Goods',
              '- ${_currencyFormat.format((month['cogs'] ?? 0).toDouble())}',
              valueColor: AppColors.textSecondary,
            ),
            _buildRow(
              l10n?.grossProfit ?? 'Gross Profit',
              _currencyFormat.format(
                  (month['gross_profit'] ?? 0).toDouble()),
            ),
            _buildRow(
              l10n?.expenses ?? 'Expenses',
              '- ${_currencyFormat.format((month['expenses'] ?? 0).toDouble())}',
              valueColor: AppColors.error,
            ),
            const Divider(height: 16),
            _buildRow(
              l10n?.netProfit ?? 'Net Profit',
              _currencyFormat
                  .format((month['net_profit'] ?? 0).toDouble()),
              isBold: true,
              valueColor: (month['net_profit'] ?? 0).toDouble() >= 0
                  ? AppColors.success
                  : AppColors.error,
            ),

            // Profit margin
            if ((month['profit_margin'] ?? 0) != 0)
              Padding(
                padding: const EdgeInsets.only(top: 8),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: (month['profit_margin'] ?? 0).toDouble() >= 0
                            ? AppColors.success.withValues(alpha: 0.1)
                            : AppColors.error.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        '${(month['profit_margin'] ?? 0)}% ${l10n?.margin ?? 'margin'}',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color:
                              (month['profit_margin'] ?? 0).toDouble() >= 0
                                  ? AppColors.success
                                  : AppColors.error,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildRow(String label, String value,
      {bool isBold = false, Color? valueColor}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: 14,
              fontWeight: isBold ? FontWeight.w600 : FontWeight.normal,
              color: AppColors.textPrimary,
            ),
          ),
          Text(
            value,
            style: TextStyle(
              fontSize: 14,
              fontWeight: isBold ? FontWeight.bold : FontWeight.w500,
              color: valueColor ?? AppColors.textPrimary,
            ),
          ),
        ],
      ),
    );
  }
}
