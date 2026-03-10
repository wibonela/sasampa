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
  // Local editing copies — changes only propagate on "Apply"
  late DashboardLayout _layout;
  late List<DashboardWidgetId> _widgetOrder;
  late Set<DashboardWidgetId> _hiddenWidgets;
  late int _defaultTabIndex;
  bool _isDirty = false;

  @override
  void initState() {
    super.initState();
    final prefs = ref.read(dashboardPrefsProvider).prefs;
    _layout = prefs.layout;
    _widgetOrder = List.from(prefs.widgetOrder);
    _hiddenWidgets = Set.from(prefs.hiddenWidgets);
    _defaultTabIndex = prefs.defaultTabIndex;
  }

  void _markDirty() {
    if (!_isDirty) setState(() => _isDirty = true);
  }

  void _applyChanges() {
    final notifier = ref.read(dashboardPrefsProvider.notifier);
    final newPrefs = DashboardPreferences(
      layout: _layout,
      widgetOrder: _widgetOrder,
      hiddenWidgets: _hiddenWidgets,
      defaultTabIndex: _defaultTabIndex,
    );
    notifier.updatePreferences(newPrefs);
    setState(() => _isDirty = false);
    final l10n = AppLocalizations.of(context);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(l10n?.changesSaved ?? 'Changes saved'),
        backgroundColor: AppColors.success,
        behavior: SnackBarBehavior.floating,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  void _applyPreset(DashboardLayout layout) {
    final DashboardPreferences preset;
    switch (layout) {
      case DashboardLayout.classic:
        preset = DashboardPreferences.classic();
      case DashboardLayout.analytics:
        preset = DashboardPreferences.analytics();
      case DashboardLayout.compact:
        preset = DashboardPreferences.compact();
    }
    setState(() {
      _layout = preset.layout;
      _widgetOrder = List.from(preset.widgetOrder);
      _hiddenWidgets = Set.from(preset.hiddenWidgets);
    });
    _markDirty();
  }

  void _resetToDefault() {
    _applyPreset(DashboardLayout.classic);
    setState(() => _defaultTabIndex = 0);
  }

  Future<bool> _onWillPop() async {
    if (!_isDirty) return true;
    final l10n = AppLocalizations.of(context);
    final result = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(l10n?.unsavedChanges ?? 'Unsaved Changes'),
        content: Text(l10n?.unsavedChangesMessage ??
            'You have unsaved changes. Would you like to apply them before leaving?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(l10n?.discard ?? 'Discard'),
          ),
          FilledButton(
            onPressed: () {
              _applyChanges();
              Navigator.pop(ctx, true);
            },
            child: Text(l10n?.apply ?? 'Apply'),
          ),
        ],
      ),
    );
    return result ?? false;
  }

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

  String _widgetDescription(DashboardWidgetId id, AppLocalizations? l10n) {
    switch (id) {
      case DashboardWidgetId.todayStats:
        return l10n?.todayStatsDesc ?? 'Sales, orders & revenue for today';
      case DashboardWidgetId.quickActions:
        return l10n?.quickActionsDesc ?? 'Fast access to common tasks';
      case DashboardWidgetId.lowStockAlert:
        return l10n?.lowStockAlertDesc ?? 'Products running low';
      case DashboardWidgetId.recentTransactions:
        return l10n?.recentTransactionsDesc ?? 'Latest sales activity';
      case DashboardWidgetId.weeklySummary:
        return l10n?.weeklySummaryDesc ?? '7-day performance chart';
      case DashboardWidgetId.topProducts:
        return l10n?.topProductsDesc ?? 'Best selling products';
      case DashboardWidgetId.profitBreakdown:
        return l10n?.profitBreakdownDesc ?? 'Revenue, costs & profit analysis';
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);

    final tabLabels = [
      l10n?.home ?? 'Home',
      l10n?.pos ?? 'POS',
      l10n?.sales ?? 'Sales',
      l10n?.menu ?? 'Menu',
      l10n?.settings ?? 'Settings',
    ];

    final visibleCount =
        _widgetOrder.where((w) => !_hiddenWidgets.contains(w)).length;

    return PopScope(
      canPop: !_isDirty,
      onPopInvokedWithResult: (didPop, _) async {
        if (!didPop) {
          final shouldPop = await _onWillPop();
          if (shouldPop && mounted) Navigator.of(context).pop();
        }
      },
      child: Scaffold(
        backgroundColor: AppColors.backgroundSecondary,
        appBar: AppBar(
          title: Text(l10n?.customizeDashboard ?? 'Customize Dashboard'),
          centerTitle: true,
          actions: [
            if (_isDirty)
              TextButton(
                onPressed: _applyChanges,
                child: Text(
                  l10n?.apply ?? 'Apply',
                  style: const TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 16,
                  ),
                ),
              ),
          ],
        ),
        body: Column(
          children: [
            Expanded(
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Layout Presets
                  _buildSectionHeader(
                    l10n?.dashboardLayout ?? 'Dashboard Layout',
                    l10n?.chooseLayoutPreset ??
                        'Choose a preset to quickly configure your dashboard',
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      _buildPresetCard(
                        label: l10n?.layoutClassic ?? 'Classic',
                        icon: Icons.dashboard_rounded,
                        description: l10n?.classicDesc ?? 'Balanced overview',
                        isActive: _layout == DashboardLayout.classic,
                        onTap: () => _applyPreset(DashboardLayout.classic),
                      ),
                      const SizedBox(width: 10),
                      _buildPresetCard(
                        label: l10n?.layoutAnalytics ?? 'Analytics',
                        icon: Icons.analytics_rounded,
                        description: l10n?.analyticsDesc ?? 'Data focused',
                        isActive: _layout == DashboardLayout.analytics,
                        onTap: () => _applyPreset(DashboardLayout.analytics),
                      ),
                      const SizedBox(width: 10),
                      _buildPresetCard(
                        label: l10n?.layoutCompact ?? 'Compact',
                        icon: Icons.view_compact_rounded,
                        description: l10n?.compactDesc ?? 'Minimal view',
                        isActive: _layout == DashboardLayout.compact,
                        onTap: () => _applyPreset(DashboardLayout.compact),
                      ),
                    ],
                  ),

                  const SizedBox(height: 28),

                  // Widget List
                  _buildSectionHeader(
                    l10n?.dashboardWidgets ?? 'Dashboard Widgets',
                    '$visibleCount ${l10n?.widgetsVisible ?? 'visible'} · ${l10n?.dragToReorder ?? 'Drag to reorder'}',
                  ),
                  const SizedBox(height: 12),
                  Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.04),
                          blurRadius: 8,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: ReorderableListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: _widgetOrder.length,
                      onReorder: (oldIndex, newIndex) {
                        setState(() {
                          if (newIndex > oldIndex) newIndex--;
                          final item = _widgetOrder.removeAt(oldIndex);
                          _widgetOrder.insert(newIndex, item);
                        });
                        _markDirty();
                      },
                      proxyDecorator: (child, index, animation) {
                        return AnimatedBuilder(
                          animation: animation,
                          builder: (context, child) => Material(
                            elevation: 4,
                            borderRadius: BorderRadius.circular(10),
                            color: Colors.white,
                            child: child,
                          ),
                          child: child,
                        );
                      },
                      itemBuilder: (context, index) {
                        final widgetId = _widgetOrder[index];
                        final isHidden = _hiddenWidgets.contains(widgetId);

                        return Container(
                          key: ValueKey(widgetId),
                          decoration: index < _widgetOrder.length - 1
                              ? const BoxDecoration(
                                  border: Border(
                                    bottom: BorderSide(
                                        color: AppColors.divider, width: 0.5),
                                  ),
                                )
                              : null,
                          child: ListTile(
                            contentPadding: const EdgeInsets.symmetric(
                                horizontal: 16, vertical: 4),
                            leading: Container(
                              width: 40,
                              height: 40,
                              decoration: BoxDecoration(
                                color: isHidden
                                    ? AppColors.gray5
                                    : AppColors.primary.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Icon(
                                _widgetIcon(widgetId),
                                color: isHidden
                                    ? AppColors.gray2
                                    : AppColors.primary,
                                size: 20,
                              ),
                            ),
                            title: Text(
                              _widgetLabel(widgetId, l10n),
                              style: TextStyle(
                                fontWeight: FontWeight.w600,
                                fontSize: 15,
                                color: isHidden
                                    ? AppColors.textSecondary
                                    : AppColors.textPrimary,
                              ),
                            ),
                            subtitle: Text(
                              _widgetDescription(widgetId, l10n),
                              style: TextStyle(
                                fontSize: 12,
                                color: isHidden
                                    ? AppColors.gray2
                                    : AppColors.textSecondary,
                              ),
                            ),
                            trailing: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Switch.adaptive(
                                  value: !isHidden,
                                  onChanged: (_) {
                                    setState(() {
                                      if (isHidden) {
                                        _hiddenWidgets.remove(widgetId);
                                      } else {
                                        _hiddenWidgets.add(widgetId);
                                      }
                                    });
                                    _markDirty();
                                  },
                                  activeTrackColor: AppColors.primary,
                                ),
                                const Icon(Icons.drag_handle,
                                    color: AppColors.gray3, size: 20),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ),

                  const SizedBox(height: 28),

                  // Default Landing Page
                  _buildSectionHeader(
                    l10n?.defaultLandingPage ?? 'Default Landing Page',
                    l10n?.landingPageDesc ??
                        'Screen shown when you open the app',
                  ),
                  const SizedBox(height: 12),
                  Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.04),
                          blurRadius: 8,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Column(
                      children: List.generate(tabLabels.length, (index) {
                        final isSelected = _defaultTabIndex == index;
                        final icons = [
                          Icons.home_rounded,
                          Icons.point_of_sale_rounded,
                          Icons.receipt_long_rounded,
                          Icons.restaurant_menu_rounded,
                          Icons.settings_rounded,
                        ];
                        return Column(
                          children: [
                            ListTile(
                              leading: Icon(
                                icons[index],
                                color: isSelected
                                    ? AppColors.primary
                                    : AppColors.textSecondary,
                                size: 22,
                              ),
                              title: Text(
                                tabLabels[index],
                                style: TextStyle(
                                  fontWeight: isSelected
                                      ? FontWeight.w600
                                      : FontWeight.w500,
                                  color: isSelected
                                      ? AppColors.primary
                                      : AppColors.textPrimary,
                                ),
                              ),
                              trailing: isSelected
                                  ? const Icon(Icons.check_circle,
                                      color: AppColors.primary, size: 22)
                                  : null,
                              onTap: () {
                                setState(() => _defaultTabIndex = index);
                                _markDirty();
                              },
                            ),
                            if (index < tabLabels.length - 1)
                              const Divider(
                                  height: 0.5, indent: 56, endIndent: 16),
                          ],
                        );
                      }),
                    ),
                  ),

                  const SizedBox(height: 28),

                  // Reset to Default
                  SizedBox(
                    height: 50,
                    child: OutlinedButton.icon(
                      onPressed: _resetToDefault,
                      icon: const Icon(Icons.restart_alt_rounded, size: 20),
                      label: Text(l10n?.resetToDefault ?? 'Reset to Default'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.textSecondary,
                        side: const BorderSide(color: AppColors.gray3),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ),

                  const SizedBox(height: 32),
                ],
              ),
            ),

            // Sticky bottom Apply button
            if (_isDirty)
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(
                  color: Colors.white,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.08),
                      blurRadius: 8,
                      offset: const Offset(0, -2),
                    ),
                  ],
                ),
                child: SafeArea(
                  top: false,
                  child: SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: FilledButton.icon(
                      onPressed: _applyChanges,
                      icon: const Icon(Icons.check_rounded, size: 20),
                      label: Text(
                        l10n?.applyChanges ?? 'Apply Changes',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: FilledButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title, String subtitle) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: AppColors.textPrimary,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          subtitle,
          style: const TextStyle(
            fontSize: 13,
            color: AppColors.textSecondary,
          ),
        ),
      ],
    );
  }

  Widget _buildPresetCard({
    required String label,
    required IconData icon,
    required String description,
    required bool isActive,
    required VoidCallback onTap,
  }) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 8),
          decoration: BoxDecoration(
            color: isActive
                ? AppColors.primary.withValues(alpha: 0.08)
                : Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: isActive ? AppColors.primary : AppColors.divider,
              width: isActive ? 2 : 1,
            ),
            boxShadow: isActive
                ? [
                    BoxShadow(
                      color: AppColors.primary.withValues(alpha: 0.15),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ]
                : [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.04),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
          ),
          child: Column(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: isActive
                      ? AppColors.primary.withValues(alpha: 0.15)
                      : AppColors.gray6,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  icon,
                  color:
                      isActive ? AppColors.primary : AppColors.textSecondary,
                  size: 24,
                ),
              ),
              const SizedBox(height: 10),
              Text(
                label,
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: isActive ? FontWeight.w700 : FontWeight.w600,
                  color:
                      isActive ? AppColors.primary : AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                description,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 11,
                  color: isActive
                      ? AppColors.primary.withValues(alpha: 0.8)
                      : AppColors.textSecondary,
                ),
              ),
              if (isActive) ...[
                const SizedBox(height: 6),
                Container(
                  width: 6,
                  height: 6,
                  decoration: const BoxDecoration(
                    color: AppColors.primary,
                    shape: BoxShape.circle,
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
