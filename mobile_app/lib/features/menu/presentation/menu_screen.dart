import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';

class _MenuItem {
  final IconData icon;
  final String title;
  final String subtitle;
  final String route;
  final bool isNative;
  final String? permission;

  const _MenuItem({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.route,
    this.isNative = false,
    this.permission,
  });
}

class _MenuSection {
  final String title;
  final List<_MenuItem> items;

  const _MenuSection({required this.title, required this.items});
}

class MenuScreen extends ConsumerWidget {
  const MenuScreen({super.key});

  List<_MenuSection> _buildSections(AppLocalizations l10n) {
    return [
      _MenuSection(
        title: l10n.quickActions,
        items: [
          _MenuItem(
            icon: Icons.dashboard_outlined,
            title: l10n.dashboard,
            subtitle: l10n.overview,
            route: '/',
            isNative: true,
          ),
          _MenuItem(
            icon: Icons.point_of_sale_outlined,
            title: l10n.pointOfSale,
            subtitle: l10n.pos,
            route: '/pos',
            isNative: true,
          ),
        ],
      ),
      _MenuSection(
        title: l10n.orders,
        items: [
          _MenuItem(
            icon: Icons.assignment_outlined,
            title: l10n.orders,
            subtitle: l10n.manageOrders,
            route: '/orders',
            isNative: true,
          ),
        ],
      ),
      _MenuSection(
        title: l10n.customers,
        items: [
          _MenuItem(
            icon: Icons.people_outlined,
            title: l10n.customers,
            subtitle: l10n.manageCustomers,
            route: '/customers',
            isNative: true,
          ),
        ],
      ),
      _MenuSection(
        title: l10n.inventory,
        items: [
          _MenuItem(
            icon: Icons.inventory_2_outlined,
            title: l10n.products,
            subtitle: l10n.manageProducts,
            route: '/products',
          ),
          _MenuItem(
            icon: Icons.label_outlined,
            title: l10n.categories,
            subtitle: l10n.productCategories,
            route: '/categories',
          ),
          _MenuItem(
            icon: Icons.assessment_outlined,
            title: l10n.stock,
            subtitle: l10n.stockLevels,
            route: '/inventory',
            isNative: true,
          ),
        ],
      ),
      _MenuSection(
        title: l10n.expenses,
        items: [
          _MenuItem(
            icon: Icons.wallet_outlined,
            title: l10n.expenses,
            subtitle: l10n.trackCosts,
            route: '/expenses',
            isNative: true,
          ),
          _MenuItem(
            icon: Icons.folder_outlined,
            title: l10n.expenseCategories,
            subtitle: l10n.organizeExpenses,
            route: '/expense-categories',
          ),
        ],
      ),
      _MenuSection(
        title: l10n.reports,
        items: [
          _MenuItem(
            icon: Icons.receipt_long_outlined,
            title: l10n.transactions,
            subtitle: l10n.viewAllSales,
            route: '/transactions',
            isNative: true,
          ),
          _MenuItem(
            icon: Icons.bar_chart_outlined,
            title: l10n.reports,
            subtitle: l10n.salesReports,
            route: '/reports',
          ),
        ],
      ),
      _MenuSection(
        title: l10n.analytics,
        items: [
          _MenuItem(
            icon: Icons.calculate_outlined,
            title: l10n.profitAnalysis,
            subtitle: l10n.revenueProfit,
            route: '/analytics/profit',
          ),
          _MenuItem(
            icon: Icons.business_outlined,
            title: l10n.byBranch,
            subtitle: l10n.compareBranch,
            route: '/analytics/profit/branches',
          ),
          _MenuItem(
            icon: Icons.trending_up_outlined,
            title: l10n.trends,
            subtitle: l10n.performanceTrends,
            route: '/analytics/profit/trends',
          ),
        ],
      ),
      _MenuSection(
        title: l10n.management,
        items: [
          _MenuItem(
            icon: Icons.people_outlined,
            title: l10n.users,
            subtitle: l10n.manageStaff,
            route: '/users',
            permission: 'manage_users',
          ),
          _MenuItem(
            icon: Icons.store_outlined,
            title: l10n.branches,
            subtitle: l10n.manageLocations,
            route: '/branches',
            permission: 'manage_branches',
          ),
        ],
      ),
      _MenuSection(
        title: l10n.help,
        items: [
          _MenuItem(
            icon: Icons.menu_book_outlined,
            title: l10n.documentation,
            subtitle: l10n.guidesHelp,
            route: '/docs',
          ),
        ],
      ),
    ];
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context)!;
    final authState = ref.watch(authProvider);
    final user = authState.user;
    final sections = _buildSections(l10n);

    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: Text(l10n.menu),
        centerTitle: true,
      ),
      body: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: sections.length,
        itemBuilder: (context, sectionIndex) {
          final section = sections[sectionIndex];

          // Filter items by permission
          final visibleItems = section.items.where((item) {
            if (item.permission == null) return true;
            return user?.hasPermission(item.permission!) ?? false;
          }).toList();

          if (visibleItems.isEmpty) return const SizedBox.shrink();

          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (sectionIndex > 0) const SizedBox(height: 24),
              Text(
                section.title,
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
                child: Column(
                  children: [
                    for (int i = 0; i < visibleItems.length; i++) ...[
                      if (i > 0) const Divider(height: 1, indent: 56),
                      _buildMenuItem(context, visibleItems[i]),
                    ],
                  ],
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildMenuItem(BuildContext context, _MenuItem item) {
    return ListTile(
      onTap: () {
        if (item.isNative) {
          context.go(item.route);
        } else {
          context.push(
            '/webview?path=${Uri.encodeComponent(item.route)}&title=${Uri.encodeComponent(item.title)}',
          );
        }
      },
      leading: Container(
        width: 40,
        height: 40,
        decoration: BoxDecoration(
          color: AppColors.primary.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Icon(item.icon, color: AppColors.primary, size: 20),
      ),
      title: Text(
        item.title,
        style: const TextStyle(fontWeight: FontWeight.w500),
      ),
      subtitle: Text(
        item.subtitle,
        style: const TextStyle(fontSize: 13, color: AppColors.textSecondary),
      ),
      trailing: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (!item.isNative)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: AppColors.gray5,
                borderRadius: BorderRadius.circular(4),
              ),
              child: Text(
                AppLocalizations.of(context)!.web,
                style: const TextStyle(
                  fontSize: 9,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textSecondary,
                ),
              ),
            ),
          const SizedBox(width: 4),
          const Icon(Icons.chevron_right, color: AppColors.gray3),
        ],
      ),
    );
  }
}
