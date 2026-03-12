import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../app/theme/colors.dart';

class MainScaffold extends StatelessWidget {
  final Widget child;

  const MainScaffold({super.key, required this.child});

  int _getCurrentIndex(BuildContext context) {
    final location = GoRouterState.of(context).matchedLocation;
    if (location == '/') return 0;
    if (location == '/pos') return 1;
    if (location.startsWith('/transactions')) return 2;
    if (location.startsWith('/orders')) return 3;
    if (location == '/menu') return 4;
    if (location.startsWith('/expenses')) return 4;
    if (location.startsWith('/inventory')) return 4;
    if (location.startsWith('/customers')) return 4;
    if (location == '/settings') return 5;
    if (location.startsWith('/store-settings')) return 5;
    if (location.startsWith('/dashboard-customization')) return 5;
    if (location.startsWith('/printer-setup')) return 5;
    if (location.startsWith('/webview')) return 0;
    return 0;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: child,
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          border: Border(
            top: BorderSide(color: AppColors.divider, width: 0.5),
          ),
        ),
        child: BottomNavigationBar(
          currentIndex: _getCurrentIndex(context),
          type: BottomNavigationBarType.fixed,
          onTap: (index) {
            switch (index) {
              case 0:
                context.go('/');
                break;
              case 1:
                context.go('/pos');
                break;
              case 2:
                context.go('/transactions');
                break;
              case 3:
                context.go('/orders');
                break;
              case 4:
                context.go('/menu');
                break;
              case 5:
                context.go('/settings');
                break;
            }
          },
          items: [
            BottomNavigationBarItem(
              icon: const Icon(Icons.home_outlined),
              activeIcon: const Icon(Icons.home),
              label: AppLocalizations.of(context)?.home ?? 'Home',
            ),
            BottomNavigationBarItem(
              icon: const Icon(Icons.point_of_sale_outlined),
              activeIcon: const Icon(Icons.point_of_sale),
              label: AppLocalizations.of(context)?.pos ?? 'POS',
            ),
            BottomNavigationBarItem(
              icon: const Icon(Icons.receipt_long_outlined),
              activeIcon: const Icon(Icons.receipt_long),
              label: AppLocalizations.of(context)?.sales ?? 'Sales',
            ),
            BottomNavigationBarItem(
              icon: const Icon(Icons.assignment_outlined),
              activeIcon: const Icon(Icons.assignment),
              label: AppLocalizations.of(context)?.orders ?? 'Orders',
            ),
            BottomNavigationBarItem(
              icon: const Icon(Icons.apps_outlined),
              activeIcon: const Icon(Icons.apps),
              label: AppLocalizations.of(context)?.menu ?? 'Menu',
            ),
            BottomNavigationBarItem(
              icon: const Icon(Icons.settings_outlined),
              activeIcon: const Icon(Icons.settings),
              label: AppLocalizations.of(context)?.settings ?? 'Settings',
            ),
          ],
        ),
      ),
    );
  }
}
