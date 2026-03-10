import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';
import '../../dashboard/data/dashboard_preferences.dart';

class DashboardCustomizationScreen extends ConsumerStatefulWidget {
  const DashboardCustomizationScreen({super.key});

  @override
  ConsumerState<DashboardCustomizationScreen> createState() =>
      _DashboardCustomizationScreenState();
}

class _DashboardCustomizationScreenState
    extends ConsumerState<DashboardCustomizationScreen> {

  String _widgetLabel(DashboardWidgetId id, AppLocalizations? l10n) {
    switch (id) {
      case DashboardWidgetId.todayStats:
        return l10n?.todayStatsWidget ?? "Today's Statistics";
      case DashboardWidgetId.quickActions:
        return l10n?.quickActionsWidget ?? 'Quick Actions';
      case DashboardWidgetId.lowStockAlert:
        return l10n?.lowStockAlertWidget ?? 'Low Stock Alert';
      case DashboardWidgetId.recentTransactions:
        return l10n?.recentTransactionsWidget ?? 'Recent Transactions';
      case DashboardWidgetId.weeklySummary:
        return l10n?.weeklySummaryWidget ?? 'Weekly Summary';
      case DashboardWidgetId.topProducts:
        return l10n?.topProductsWidget ?? 'Top Products';
      case DashboardWidgetId.profitBreakdown:
        return l10n?.profitBreakdownWidget ?? 'Profit Breakdown';
    }
  }

  IconData _widgetIcon(DashboardWidgetId id) {
    switch (id) {
      case DashboardWidgetId.todayStats:
        return Icons.trending_up;
      case DashboardWidgetId.quickActions:
        return Icons.flash_on;
      case DashboardWidgetId.lowStockAlert:
        return Icons.warning_amber_rounded;
      case DashboardWidgetId.recentTransactions:
        return Icons.receipt_long;
      case DashboardWidgetId.weeklySummary:
        return Icons.bar_chart;
      case DashboardWidgetId.topProducts:
        return Icons.star;
      case DashboardWidgetId.profitBreakdown:
        return Icons.account_balance_wallet;
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final prefsState = ref.watch(dashboardPrefsProvider);
    final prefs = prefsState.prefs;
    final notifier = ref.read(dashboardPrefsProvider.notifier);

    final tabLabels = [
      l10n?.home ?? 'Home',
      l10n?.pos ?? 'POS',
      l10n?.sales ?? 'Sales',
      l10n?.menu ?? 'Menu',
      l10n?.settings ?? 'Settings',
    ];

    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: Text(l10n?.customizeDashboard ?? 'Customize Dashboard'),
        centerTitle: true,
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Layout Presets
          Text(
            l10n?.dashboardLayout ?? 'Dashboard Layout',
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: AppColors.textSecondary,
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              _buildPresetCard(
                label: l10n?.layoutClassic ?? 'Classic',
                icon: Icons.dashboard,
                isActive: prefs.layout == DashboardLayout.classic,
                onTap: () => notifier.applyPreset(DashboardLayout.classic),
              ),
              const SizedBox(width: 8),
              _buildPresetCard(
                label: l10n?.layoutAnalytics ?? 'Analytics',
                icon: Icons.analytics,
                isActive: prefs.layout == DashboardLayout.analytics,
                onTap: () => notifier.applyPreset(DashboardLayout.analytics),
              ),
              const SizedBox(width: 8),
              _buildPresetCard(
                label: l10n?.layoutCompact ?? 'Compact',
                icon: Icons.view_compact,
                isActive: prefs.layout == DashboardLayout.compact,
                onTap: () => notifier.applyPreset(DashboardLayout.compact),
              ),
            ],
          ),

          const SizedBox(height: 24),

          // Widget List
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                l10n?.dashboardWidgets ?? 'Dashboard Widgets',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textSecondary,
                ),
              ),
              Text(
                l10n?.dragToReorder ?? 'Drag to reorder',
                style: const TextStyle(
                  fontSize: 12,
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
            ),
            clipBehavior: Clip.antiAlias,
            child: ReorderableListView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: prefs.widgetOrder.length,
              onReorder: (oldIndex, newIndex) {
                final order = List<DashboardWidgetId>.from(prefs.widgetOrder);
                if (newIndex > oldIndex) newIndex--;
                final item = order.removeAt(oldIndex);
                order.insert(newIndex, item);
                notifier.reorderWidgets(order);
              },
              itemBuilder: (context, index) {
                final widgetId = prefs.widgetOrder[index];
                final isHidden = prefs.hiddenWidgets.contains(widgetId);

                return Container(
                  key: ValueKey(widgetId),
                  decoration: index < prefs.widgetOrder.length - 1
                      ? const BoxDecoration(
                          border: Border(
                            bottom: BorderSide(color: AppColors.divider, width: 0.5),
                          ),
                        )
                      : null,
                  child: ListTile(
                    leading: Icon(
                      _widgetIcon(widgetId),
                      color: isHidden ? AppColors.textSecondary : AppColors.primary,
                      size: 20,
                    ),
                    title: Text(
                      _widgetLabel(widgetId, l10n),
                      style: TextStyle(
                        fontWeight: FontWeight.w500,
                        color: isHidden ? AppColors.textSecondary : AppColors.textPrimary,
                      ),
                    ),
                    trailing: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Switch(
                          value: !isHidden,
                          onChanged: (_) => notifier.toggleWidget(widgetId),
                          activeTrackColor: AppColors.primary,
                        ),
                        const Icon(Icons.drag_handle, color: AppColors.gray3),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),

          const SizedBox(height: 24),

          // Default Landing Page
          Text(
            l10n?.defaultLandingPage ?? 'Default Landing Page',
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: AppColors.textSecondary,
            ),
          ),
          const SizedBox(height: 8),
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
            ),
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<int>(
                value: prefs.defaultTabIndex,
                isExpanded: true,
                items: List.generate(
                  tabLabels.length,
                  (index) => DropdownMenuItem(
                    value: index,
                    child: Text(tabLabels[index]),
                  ),
                ),
                onChanged: (value) {
                  if (value != null) {
                    notifier.setDefaultTab(value);
                  }
                },
              ),
            ),
          ),

          const SizedBox(height: 32),

          // Reset to Default
          SizedBox(
            height: 50,
            child: OutlinedButton(
              onPressed: () => notifier.applyPreset(DashboardLayout.classic),
              style: OutlinedButton.styleFrom(
                foregroundColor: AppColors.textSecondary,
                side: const BorderSide(color: AppColors.gray3),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: Text(l10n?.resetToDefault ?? 'Reset to Default'),
            ),
          ),

          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _buildPresetCard({
    required String label,
    required IconData icon,
    required bool isActive,
    required VoidCallback onTap,
  }) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 16),
          decoration: BoxDecoration(
            color: isActive ? AppColors.primary.withValues(alpha: 0.1) : Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isActive ? AppColors.primary : AppColors.divider,
              width: isActive ? 2 : 1,
            ),
          ),
          child: Column(
            children: [
              Icon(
                icon,
                color: isActive ? AppColors.primary : AppColors.textSecondary,
              ),
              const SizedBox(height: 8),
              Text(
                label,
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: isActive ? FontWeight.w600 : FontWeight.w500,
                  color: isActive ? AppColors.primary : AppColors.textSecondary,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
