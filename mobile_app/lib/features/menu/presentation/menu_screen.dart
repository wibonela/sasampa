import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
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

  static final List<_MenuSection> _sections = [
    _MenuSection(
      title: 'Quick Actions',
      items: [
        _MenuItem(
          icon: Icons.dashboard_outlined,
          title: 'Dashboard',
          subtitle: 'Overview & statistics',
          route: '/',
          isNative: true,
        ),
        _MenuItem(
          icon: Icons.point_of_sale_outlined,
          title: 'Point of Sale',
          subtitle: 'Process sales',
          route: '/pos',
          isNative: true,
        ),
      ],
    ),
    _MenuSection(
      title: 'Inventory',
      items: [
        _MenuItem(
          icon: Icons.inventory_2_outlined,
          title: 'Products',
          subtitle: 'Manage products',
          route: '/products',
        ),
        _MenuItem(
          icon: Icons.label_outlined,
          title: 'Categories',
          subtitle: 'Product categories',
          route: '/categories',
        ),
        _MenuItem(
          icon: Icons.assessment_outlined,
          title: 'Stock',
          subtitle: 'Stock levels & adjustments',
          route: '/inventory',
          isNative: true,
        ),
      ],
    ),
    _MenuSection(
      title: 'Matumizi',
      items: [
        _MenuItem(
          icon: Icons.wallet_outlined,
          title: 'Expenses',
          subtitle: 'Track operational costs',
          route: '/expenses',
          isNative: true,
        ),
        _MenuItem(
          icon: Icons.folder_outlined,
          title: 'Expense Categories',
          subtitle: 'Organize expense types',
          route: '/expense-categories',
        ),
      ],
    ),
    _MenuSection(
      title: 'Reports',
      items: [
        _MenuItem(
          icon: Icons.receipt_long_outlined,
          title: 'Transactions',
          subtitle: 'View all sales',
          route: '/transactions',
          isNative: true,
        ),
        _MenuItem(
          icon: Icons.bar_chart_outlined,
          title: 'Reports',
          subtitle: 'Sales, products & profit reports',
          route: '/reports',
        ),
      ],
    ),
    _MenuSection(
      title: 'Analytics',
      items: [
        _MenuItem(
          icon: Icons.calculate_outlined,
          title: 'Profit Analysis',
          subtitle: 'Revenue & profit breakdown',
          route: '/analytics/profit',
        ),
        _MenuItem(
          icon: Icons.business_outlined,
          title: 'By Branch',
          subtitle: 'Compare branch performance',
          route: '/analytics/profit/branches',
        ),
        _MenuItem(
          icon: Icons.trending_up_outlined,
          title: 'Trends',
          subtitle: 'Performance over time',
          route: '/analytics/profit/trends',
        ),
      ],
    ),
    _MenuSection(
      title: 'Management',
      items: [
        _MenuItem(
          icon: Icons.people_outlined,
          title: 'Users',
          subtitle: 'Manage staff accounts',
          route: '/users',
          permission: 'manage_users',
        ),
        _MenuItem(
          icon: Icons.store_outlined,
          title: 'Branches',
          subtitle: 'Manage business locations',
          route: '/branches',
          permission: 'manage_branches',
        ),
      ],
    ),
    _MenuSection(
      title: 'Help',
      items: [
        _MenuItem(
          icon: Icons.menu_book_outlined,
          title: 'Documentation',
          subtitle: 'Guides & help articles',
          route: '/docs',
        ),
      ],
    ),
  ];

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authProvider);
    final user = authState.user;

    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: const Text('Menu'),
        centerTitle: true,
      ),
      body: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _sections.length,
        itemBuilder: (context, sectionIndex) {
          final section = _sections[sectionIndex];

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
              child: const Text(
                'WEB',
                style: TextStyle(
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
