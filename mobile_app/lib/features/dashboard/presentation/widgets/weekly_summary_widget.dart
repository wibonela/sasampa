import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../../app/theme/colors.dart';

class WeeklySummaryWidget extends StatelessWidget {
  final Map<String, dynamic>? dashboardData;

  const WeeklySummaryWidget({super.key, required this.dashboardData});

  static final _currencyFormat = NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final weeklyData = dashboardData?['weekly'] as Map<String, dynamic>?;
    final dailyTotals = weeklyData?['daily_totals'] as List<dynamic>? ?? [];
    final weeklyTotal = (weeklyData?['total'] ?? 0).toDouble();

    // Find max value for bar scaling
    double maxValue = 0;
    for (final day in dailyTotals) {
      final value = (day['total'] ?? 0).toDouble();
      if (value > maxValue) maxValue = value;
    }
    if (maxValue == 0) maxValue = 1;

    final dayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  l10n?.weeklySales ?? 'Weekly Sales',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textPrimary,
                  ),
                ),
                Text(
                  _currencyFormat.format(weeklyTotal),
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: AppColors.primary,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            if (dailyTotals.isEmpty)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(16),
                  child: Text(
                    'No weekly data available',
                    style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                  ),
                ),
              )
            else
              SizedBox(
                height: 120,
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: List.generate(
                    dailyTotals.length > 7 ? 7 : dailyTotals.length,
                    (index) {
                      final day = dailyTotals[index];
                      final value = (day['total'] ?? 0).toDouble();
                      final barHeight = (value / maxValue) * 80;
                      final label = index < dayLabels.length ? dayLabels[index] : '';

                      return Expanded(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            Text(
                              value > 0
                                  ? NumberFormat.compact().format(value)
                                  : '',
                              style: const TextStyle(
                                fontSize: 9,
                                color: AppColors.textSecondary,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Container(
                              height: barHeight < 4 && value > 0 ? 4 : barHeight,
                              margin: const EdgeInsets.symmetric(horizontal: 4),
                              decoration: BoxDecoration(
                                color: value > 0
                                    ? AppColors.primary.withOpacity(0.7)
                                    : AppColors.gray3.withOpacity(0.3),
                                borderRadius: BorderRadius.circular(4),
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              label,
                              style: const TextStyle(
                                fontSize: 11,
                                color: AppColors.textSecondary,
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
