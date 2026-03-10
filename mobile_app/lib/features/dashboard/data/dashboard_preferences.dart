import 'dart:convert';

enum DashboardWidgetId {
  todayStats,
  quickActions,
  lowStockAlert,
  recentTransactions,
  weeklySummary,
  topProducts,
  profitBreakdown,
}

enum DashboardLayout { classic, analytics, compact }

class DashboardPreferences {
  final DashboardLayout layout;
  final List<DashboardWidgetId> widgetOrder;
  final Set<DashboardWidgetId> hiddenWidgets;
  final int defaultTabIndex;

  const DashboardPreferences({
    this.layout = DashboardLayout.classic,
    this.widgetOrder = const [
      DashboardWidgetId.todayStats,
      DashboardWidgetId.profitBreakdown,
      DashboardWidgetId.quickActions,
      DashboardWidgetId.lowStockAlert,
      DashboardWidgetId.recentTransactions,
    ],
    this.hiddenWidgets = const {},
    this.defaultTabIndex = 0,
  });

  factory DashboardPreferences.classic() => const DashboardPreferences();

  factory DashboardPreferences.analytics() => const DashboardPreferences(
        layout: DashboardLayout.analytics,
        widgetOrder: [
          DashboardWidgetId.todayStats,
          DashboardWidgetId.profitBreakdown,
          DashboardWidgetId.weeklySummary,
          DashboardWidgetId.topProducts,
          DashboardWidgetId.recentTransactions,
        ],
      );

  factory DashboardPreferences.compact() => const DashboardPreferences(
        layout: DashboardLayout.compact,
        widgetOrder: [
          DashboardWidgetId.todayStats,
          DashboardWidgetId.quickActions,
        ],
        hiddenWidgets: {
          DashboardWidgetId.lowStockAlert,
          DashboardWidgetId.recentTransactions,
          DashboardWidgetId.weeklySummary,
          DashboardWidgetId.topProducts,
        },
      );

  DashboardPreferences copyWith({
    DashboardLayout? layout,
    List<DashboardWidgetId>? widgetOrder,
    Set<DashboardWidgetId>? hiddenWidgets,
    int? defaultTabIndex,
  }) {
    return DashboardPreferences(
      layout: layout ?? this.layout,
      widgetOrder: widgetOrder ?? this.widgetOrder,
      hiddenWidgets: hiddenWidgets ?? this.hiddenWidgets,
      defaultTabIndex: defaultTabIndex ?? this.defaultTabIndex,
    );
  }

  Map<String, dynamic> toJson() => {
        'layout': layout.index,
        'widgetOrder': widgetOrder.map((w) => w.index).toList(),
        'hiddenWidgets': hiddenWidgets.map((w) => w.index).toList(),
        'defaultTabIndex': defaultTabIndex,
      };

  factory DashboardPreferences.fromJson(Map<String, dynamic> json) {
    return DashboardPreferences(
      layout: DashboardLayout.values[json['layout'] as int? ?? 0],
      widgetOrder: (json['widgetOrder'] as List<dynamic>?)
              ?.map((i) => DashboardWidgetId.values[i as int])
              .toList() ??
          const [
            DashboardWidgetId.todayStats,
            DashboardWidgetId.profitBreakdown,
            DashboardWidgetId.quickActions,
            DashboardWidgetId.lowStockAlert,
            DashboardWidgetId.recentTransactions,
          ],
      hiddenWidgets: (json['hiddenWidgets'] as List<dynamic>?)
              ?.map((i) => DashboardWidgetId.values[i as int])
              .toSet() ??
          const {},
      defaultTabIndex: json['defaultTabIndex'] as int? ?? 0,
    );
  }

  String serialize() => jsonEncode(toJson());

  factory DashboardPreferences.deserialize(String json) {
    return DashboardPreferences.fromJson(jsonDecode(json) as Map<String, dynamic>);
  }
}
