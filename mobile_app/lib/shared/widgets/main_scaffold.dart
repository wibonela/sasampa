import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../app/theme/colors.dart';
import '../../core/providers.dart';
import '../../core/offline/sync_service.dart';

class MainScaffold extends ConsumerWidget {
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
  Widget build(BuildContext context, WidgetRef ref) {
    final sync = ref.watch(syncProvider);
    final showBanner = !sync.online || sync.pending > 0 || sync.failed > 0;
    return Scaffold(
      body: Column(
        children: [
          if (showBanner) _SyncBanner(state: sync),
          Expanded(
            // the banner already sits under the status bar
            child: showBanner
                ? MediaQuery.removePadding(context: context, removeTop: true, child: child)
                : child,
          ),
        ],
      ),
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


/// Shows connection / unsent-sales status above every main screen.
class _SyncBanner extends ConsumerWidget {
  final SyncState state;
  const _SyncBanner({required this.state});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final notifier = ref.read(syncProvider.notifier);
    final hasFailed = state.failed > 0;

    final String text;
    final Color color;
    if (hasFailed) {
      text = '${state.failed} sale${state.failed == 1 ? '' : 's'} could not be recorded - tap to review';
      color = AppColors.error;
    } else if (state.pending > 0) {
      text = state.syncing
          ? 'Sending ${state.pending} offline sale${state.pending == 1 ? '' : 's'}...'
          : '${state.pending} sale${state.pending == 1 ? '' : 's'} waiting to send${state.online ? ' - tap to retry' : ''}';
      color = AppColors.warning;
    } else {
      text = 'You are offline - sales will be saved on this phone';
      color = AppColors.gray1;
    }

    return Material(
      color: color,
      child: SafeArea(
        bottom: false,
        child: InkWell(
          onTap: () {
            if (hasFailed) {
              _showFailed(context, ref);
            } else {
              notifier.sync();
            }
          },
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
            child: Row(
              children: [
                Icon(state.online ? Icons.sync : Icons.cloud_off, size: 16, color: Colors.white),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(text, style: const TextStyle(color: Colors.white, fontSize: 12.5, fontWeight: FontWeight.w600)),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _showFailed(BuildContext context, WidgetRef ref) {
    final notifier = ref.read(syncProvider.notifier);
    showModalBottomSheet<void>(
      context: context,
      showDragHandle: true,
      builder: (ctx) => Consumer(builder: (ctx, ref, _) {
        final sales = ref.watch(syncProvider).failedSales;
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Sales that could not be recorded',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                const SizedBox(height: 4),
                const Text('These were made offline but the server refused them. Retry after fixing the cause, or discard.',
                    style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                const SizedBox(height: 8),
                if (sales.isEmpty) const Padding(padding: EdgeInsets.all(16), child: Text('Nothing to review.')),
                for (final s in sales)
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    title: Text('TZS ${s.total.toStringAsFixed(0)} · ${s.createdAt.toString().substring(0, 16)}'),
                    subtitle: Text(s.lastError ?? 'Unknown error', style: const TextStyle(color: AppColors.error, fontSize: 12)),
                    trailing: IconButton(
                      icon: const Icon(Icons.delete_outline),
                      onPressed: () => notifier.discard(s.offlineId),
                    ),
                  ),
                if (sales.isNotEmpty)
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () {
                        notifier.retryFailed();
                        Navigator.pop(ctx);
                      },
                      child: const Text('Retry all'),
                    ),
                  ),
              ],
            ),
          ),
        );
      }),
    );
  }
}
