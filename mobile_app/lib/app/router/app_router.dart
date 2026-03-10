import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/providers.dart';
import '../../features/auth/presentation/login_screen.dart';
import '../../features/auth/presentation/register_screen.dart';
import '../../features/auth/presentation/verify_email_screen.dart';
import '../../features/auth/presentation/business_details_screen.dart';
import '../../features/auth/presentation/onboarding_complete_screen.dart';
import '../../features/mobile_access/presentation/mobile_access_screen.dart';
import '../../features/dashboard/presentation/dashboard_screen.dart';
import '../../features/pos/presentation/pos_screen.dart';
import '../../features/transactions/presentation/transactions_screen.dart';
import '../../features/transactions/presentation/transaction_detail_screen.dart';
import '../../features/settings/presentation/settings_screen.dart';
import '../../features/settings/presentation/store_settings_screen.dart';
import '../../features/settings/presentation/dashboard_customization_screen.dart';
import '../../features/expenses/presentation/expenses_screen.dart';
import '../../features/expenses/presentation/add_expense_screen.dart';
import '../../features/expenses/presentation/expense_summary_screen.dart';
import '../../features/inventory/presentation/inventory_screen.dart';
import '../../features/menu/presentation/menu_screen.dart';
import '../../features/orders/presentation/orders_screen.dart';
import '../../features/orders/presentation/order_detail_screen.dart';
import '../../shared/widgets/main_scaffold.dart';
import '../../shared/widgets/webview_screen.dart';

bool _hasRedirectedToDefaultTab = false;

final routerProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authProvider);
  final dashboardPrefsState = ref.watch(dashboardPrefsProvider);

  return GoRouter(
    initialLocation: '/login',
    redirect: (context, state) {
      final isLoggedIn = authState.isAuthenticated;
      final isInitialized = authState.isInitialized;
      final location = state.matchedLocation;
      final isLoginRoute = location == '/login';
      final isRegisterRoute = location == '/register';
      final isOnboardingRoute = location == '/verify-email' ||
          location.startsWith('/verify-email/') ||
          location == '/business-details' ||
          location == '/onboarding-complete';
      final isMobileAccessRoute = location == '/mobile-access';
      final isPublicRoute = isLoginRoute || isRegisterRoute;

      if (!isInitialized) {
        return null;
      }

      // Not logged in - reset redirect flag and allow login/register/onboarding only
      if (!isLoggedIn && !isPublicRoute && !isOnboardingRoute) {
        _hasRedirectedToDefaultTab = false;
        return '/login';
      }

      // Logged in but on login/register page - check mobile access
      if (isLoggedIn && isPublicRoute) {
        if (!authState.canUseMobile) {
          return '/mobile-access';
        }
        // Check default tab redirect
        if (!_hasRedirectedToDefaultTab && dashboardPrefsState.isLoaded) {
          _hasRedirectedToDefaultTab = true;
          final tabIndex = dashboardPrefsState.prefs.defaultTabIndex;
          if (tabIndex != 0) {
            const tabRoutes = ['/', '/pos', '/transactions', '/menu', '/settings'];
            return tabRoutes[tabIndex];
          }
        }
        return '/';
      }

      // Logged in but no mobile access - allow onboarding routes
      if (isLoggedIn && !authState.canUseMobile && !isMobileAccessRoute && !isOnboardingRoute) {
        return '/mobile-access';
      }

      // Default tab redirect on first navigation to home
      if (isLoggedIn && location == '/' && !_hasRedirectedToDefaultTab && dashboardPrefsState.isLoaded) {
        _hasRedirectedToDefaultTab = true;
        final tabIndex = dashboardPrefsState.prefs.defaultTabIndex;
        if (tabIndex != 0) {
          const tabRoutes = ['/', '/pos', '/transactions', '/menu', '/settings'];
          return tabRoutes[tabIndex];
        }
      }

      return null;
    },
    routes: [
      GoRoute(
        path: '/login',
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: '/register',
        builder: (context, state) => const RegisterScreen(),
      ),
      GoRoute(
        path: '/verify-email',
        builder: (context, state) => const VerifyEmailScreen(),
      ),
      // Deep link route for email verification — redirects are handled by main.dart
      GoRoute(
        path: '/verify-email/:id/:hash',
        redirect: (context, state) => '/verify-email',
      ),
      GoRoute(
        path: '/business-details',
        builder: (context, state) => const BusinessDetailsScreen(),
      ),
      GoRoute(
        path: '/onboarding-complete',
        builder: (context, state) => const OnboardingCompleteScreen(),
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
          GoRoute(
            path: '/orders',
            builder: (context, state) => const OrdersScreen(),
          ),
          GoRoute(
            path: '/orders/:id',
            builder: (context, state) {
              final id = int.parse(state.pathParameters['id']!);
              return OrderDetailScreen(orderId: id);
            },
          ),
          GoRoute(
            path: '/store-settings',
            builder: (context, state) => const StoreSettingsScreen(),
          ),
          GoRoute(
            path: '/dashboard-customization',
            builder: (context, state) => const DashboardCustomizationScreen(),
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
      ),
    ],
  );
});
