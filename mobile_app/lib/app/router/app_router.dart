import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/providers.dart';
import '../../features/auth/presentation/login_screen.dart';
import '../../features/mobile_access/presentation/mobile_access_screen.dart';
import '../../features/dashboard/presentation/dashboard_screen.dart';
import '../../features/pos/presentation/pos_screen.dart';
import '../../features/transactions/presentation/transactions_screen.dart';
import '../../features/transactions/presentation/transaction_detail_screen.dart';
import '../../features/settings/presentation/settings_screen.dart';
import '../../features/settings/presentation/store_settings_screen.dart';
import '../../features/expenses/presentation/expenses_screen.dart';
import '../../features/expenses/presentation/add_expense_screen.dart';
import '../../features/expenses/presentation/expense_summary_screen.dart';
import '../../features/inventory/presentation/inventory_screen.dart';
import '../../features/menu/presentation/menu_screen.dart';
import '../../shared/widgets/main_scaffold.dart';
import '../../shared/widgets/webview_screen.dart';

final routerProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authProvider);

  return GoRouter(
    initialLocation: '/login',
    redirect: (context, state) {
      final isLoggedIn = authState.isAuthenticated;
      final isInitialized = authState.isInitialized;
      final isLoginRoute = state.matchedLocation == '/login';
      final isMobileAccessRoute = state.matchedLocation == '/mobile-access';

      // Wait for auth check to complete before redirecting
      // This prevents the race condition where router redirects to login
      // before the auth check has finished loading cached credentials
      if (!isInitialized) {
        // Stay on current route until auth is initialized
        return null;
      }

      // Not logged in - go to login
      if (!isLoggedIn && !isLoginRoute) {
        return '/login';
      }

      // Logged in but on login page - check mobile access
      if (isLoggedIn && isLoginRoute) {
        if (!authState.canUseMobile) {
          return '/mobile-access';
        }
        return '/';
      }

      // Logged in but no mobile access - go to mobile access screen
      if (isLoggedIn && !authState.canUseMobile && !isMobileAccessRoute) {
        return '/mobile-access';
      }

      return null;
    },
    routes: [
      GoRoute(
        path: '/login',
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: '/mobile-access',
        builder: (context, state) => const MobileAccessScreen(),
      ),
      ShellRoute(
        builder: (context, state, child) => MainScaffold(child: child),
        routes: [
          GoRoute(
            path: '/',
            builder: (context, state) => const DashboardScreen(),
          ),
          GoRoute(
            path: '/pos',
            builder: (context, state) => const POSScreen(),
          ),
          GoRoute(
            path: '/transactions',
            builder: (context, state) => const TransactionsScreen(),
            routes: [
              GoRoute(
                path: ':id',
                builder: (context, state) {
                  final id = int.parse(state.pathParameters['id']!);
                  return TransactionDetailScreen(transactionId: id);
                },
              ),
            ],
          ),
          GoRoute(
            path: '/menu',
            builder: (context, state) => const MenuScreen(),
          ),
          GoRoute(
            path: '/settings',
            builder: (context, state) => const SettingsScreen(),
          ),
          GoRoute(
            path: '/expenses',
            builder: (context, state) => const ExpensesScreen(),
          ),
          GoRoute(
            path: '/inventory',
            builder: (context, state) => const InventoryScreen(),
          ),
        ],
      ),
      // Standalone screens (pushed on top, no bottom nav)
      GoRoute(
        path: '/store-settings',
        builder: (context, state) => const StoreSettingsScreen(),
      ),
      GoRoute(
        path: '/expenses/add',
        builder: (context, state) => const AddExpenseScreen(),
      ),
      GoRoute(
        path: '/expenses/edit/:id',
        builder: (context, state) {
          final id = int.parse(state.pathParameters['id']!);
          return AddExpenseScreen(expenseId: id);
        },
      ),
      GoRoute(
        path: '/expenses/summary',
        builder: (context, state) => const ExpenseSummaryScreen(),
      ),
      // WebView screen for web-only features
      GoRoute(
        path: '/webview',
        builder: (context, state) {
          final path = state.uri.queryParameters['path'] ?? '/dashboard';
          final title = state.uri.queryParameters['title'] ?? 'Sasampa';
          return WebViewScreen(webPath: path, title: title);
        },
      ),
    ],
  );
});
