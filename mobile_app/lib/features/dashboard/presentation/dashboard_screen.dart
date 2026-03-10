import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';
import '../data/dashboard_preferences.dart';
import 'widgets/today_stats_widget.dart';
import 'widgets/quick_actions_widget.dart';
import 'widgets/low_stock_widget.dart';
import 'widgets/weekly_summary_widget.dart';
import 'widgets/top_products_widget.dart';
import 'widgets/profit_breakdown_widget.dart';

class DashboardScreen extends ConsumerStatefulWidget {
  const DashboardScreen({super.key});

  @override
  ConsumerState<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends ConsumerState<DashboardScreen> {
  Map<String, dynamic>? _dashboardData;
  bool _isLoading = true;
  String? _error;

  final _currencyFormat = NumberFormat.currency(symbol: 'TZS ', decimalDigits: 0);

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadDashboard();
    });
  }

  Future<void> _loadDashboard() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final api = ref.read(apiClientProvider);
      final response = await api.getDashboard();
      setState(() {
        _dashboardData = response.data['data'];
        _isLoading = false;
      });
    } catch (e) {
      String errorMsg = AppLocalizations.of(context)?.failedToLoadDashboard ?? 'Failed to load dashboard';
      if (e is DioException) {
        final response = e.response;
        if (response != null && response.data is Map<String, dynamic>) {
          final data = response.data as Map<String, dynamic>;
          errorMsg = data['message'] ?? errorMsg;
        } else if (e.type == DioExceptionType.connectionError ||
            e.type == DioExceptionType.connectionTimeout) {
          errorMsg = 'No internet connection';
        }
      }
      setState(() {
        _error = errorMsg;
        _isLoading = false;
      });
    }
  }

  Widget _buildWidgetForId(DashboardWidgetId id) {
    switch (id) {
      case DashboardWidgetId.todayStats:
        return TodayStatsWidget(dashboardData: _dashboardData);
      case DashboardWidgetId.quickActions:
        return const QuickActionsWidget();
      case DashboardWidgetId.lowStockAlert:
        return LowStockWidget(dashboardData: _dashboardData);
      case DashboardWidgetId.recentTransactions:
        return _buildRecentTransactions();
      case DashboardWidgetId.weeklySummary:
        return WeeklySummaryWidget(dashboardData: _dashboardData);
      case DashboardWidgetId.topProducts:
        return TopProductsWidget(dashboardData: _dashboardData);
      case DashboardWidgetId.profitBreakdown:
        return ProfitBreakdownWidget(dashboardData: _dashboardData);
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final user = authState.user;
    final prefsState = ref.watch(dashboardPrefsProvider);
    final prefs = prefsState.prefs;

    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: _loadDashboard,
          child: CustomScrollView(
            slivers: [
              // Header (always visible)
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '${AppLocalizations.of(context)?.hello ?? 'Hello'}, ${user?.name.split(' ').first ?? 'User'}',
                              style: const TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              user?.company?.name ?? '',
                              style: const TextStyle(
                                fontSize: 15,
                                color: AppColors.textSecondary,
                              ),
                            ),
                          ],
                        ),
                      ),
                      _buildCompanyAvatar(user),
                    ],
                  ),
                ),
              ),

              // Loading / Error / Content
              if (_isLoading)
                const SliverFillRemaining(
                  child: Center(child: CircularProgressIndicator()),
                )
              else if (_error != null)
                SliverFillRemaining(
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.error_outline, size: 48, color: AppColors.error),
                        const SizedBox(height: 16),
                        Text(_error!, style: const TextStyle(color: AppColors.textSecondary)),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadDashboard,
                          child: Text(AppLocalizations.of(context)?.retry ?? 'Retry'),
                        ),
                      ],
                    ),
                  ),
                )
              else ...[
                // Dynamic widgets based on preferences
                for (final widgetId in prefs.widgetOrder)
                  if (!prefs.hiddenWidgets.contains(widgetId)) ...[
                    SliverToBoxAdapter(child: _buildWidgetForId(widgetId)),
                    const SliverToBoxAdapter(child: SizedBox(height: 16)),
                  ],

                const SliverToBoxAdapter(child: SizedBox(height: 84)),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildRecentTransactions() {
    final transactions = _dashboardData?['recent_transactions'] as List? ?? [];
    if (transactions.isEmpty) return const SizedBox.shrink();

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                AppLocalizations.of(context)?.recentTransactions ?? 'Recent Transactions',
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textPrimary,
                ),
              ),
              TextButton(
                onPressed: () => context.go('/transactions'),
                child: Text(AppLocalizations.of(context)?.all ?? 'See All'),
              ),
            ],
          ),
        ),
        ...transactions.map((tx) => _buildTransactionItem(tx as Map<String, dynamic>)),
      ],
    );
  }

  Widget _buildCompanyAvatar(dynamic user) {
    final logoUrl = user?.company?.logo;
    final companyName = user?.company?.name ?? '';
    final userName = user?.name ?? '';
    final initial = companyName.isNotEmpty
        ? companyName[0].toUpperCase()
        : (userName.isNotEmpty ? userName[0].toUpperCase() : 'U');

    Widget initialWidget() => Container(
      width: 48,
      height: 48,
      decoration: BoxDecoration(
        color: AppColors.primary.withValues(alpha: 0.1),
        shape: BoxShape.circle,
      ),
      child: Center(
        child: Text(
          initial,
          style: const TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: AppColors.primary,
          ),
        ),
      ),
    );

    if (logoUrl != null && logoUrl.toString().isNotEmpty) {
      return ClipOval(
        child: SizedBox(
          width: 48,
          height: 48,
          child: Image.network(
            logoUrl.toString(),
            fit: BoxFit.cover,
            errorBuilder: (_, __, ___) => initialWidget(),
          ),
        ),
      );
    }

    return initialWidget();
  }

  Widget _buildTransactionItem(Map<String, dynamic> tx) {
    final isVoided = tx['status'] == 'voided';

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: isVoided
                  ? AppColors.error.withValues(alpha: 0.1)
                  : AppColors.success.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              isVoided ? Icons.cancel_outlined : Icons.check_circle_outline,
              color: isVoided ? AppColors.error : AppColors.success,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  tx['transaction_number'] ?? '',
                  style: TextStyle(
                    fontWeight: FontWeight.w600,
                    color: isVoided ? AppColors.textSecondary : AppColors.textPrimary,
                    decoration: isVoided ? TextDecoration.lineThrough : null,
                  ),
                ),
                Text(
                  tx['created_at_human'] ?? '',
                  style: const TextStyle(
                    fontSize: 13,
                    color: AppColors.textSecondary,
                  ),
                ),
              ],
            ),
          ),
          Text(
            _currencyFormat.format((tx['total'] ?? 0).toDouble()),
            style: TextStyle(
              fontWeight: FontWeight.w600,
              color: isVoided ? AppColors.textSecondary : AppColors.textPrimary,
              decoration: isVoided ? TextDecoration.lineThrough : null,
            ),
          ),
        ],
      ),
    );
  }
}
