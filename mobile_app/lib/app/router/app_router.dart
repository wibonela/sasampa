import 'package:flutter/foundation.dart';
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
import '../../features/customers/presentation/customers_screen.dart';
import '../../features/customers/presentation/add_customer_screen.dart';
import '../../features/customers/presentation/customer_detail_screen.dart';
import '../../features/expenses/presentation/expenses_screen.dart';
import '../../features/expenses/presentation/add_expense_screen.dart';
import '../../features/expenses/presentation/expense_summary_screen.dart';
import '../../features/inventory/presentation/inventory_screen.dart';
import '../../features/menu/presentation/menu_screen.dart';
import '../../features/orders/presentation/orders_screen.dart';
import '../../features/orders/presentation/order_detail_screen.dart';
import '../../features/settings/presentation/efd_settings_screen.dart';
import '../../features/settings/presentation/printer_setup_screen.dart';
import '../../features/settings/presentation/whatsapp_settings_screen.dart';
import '../../shared/widgets/main_scaffold.dart';
import '../../shared/widgets/webview_screen.dart';

/// Notifier that triggers GoRouter redirect re-evaluation when auth state changes.
/// This avoids recreating the entire GoRouter instance.
class _AuthChangeNotifier extends ChangeNotifier {
  _AuthChangeNotifier(Ref ref) {
    _subscription = ref.listen(authProvider, (previous, next) {
      // Only notify when meaningful auth changes happen (not loading states)
      if (previous?.isAuthenticated != next.isAuthenticated ||
          previous?.isInitialized != next.isInitialized ||
          previous?.canUseMobile != next.canUseMobile ||
          previous?.user?.needsOnboarding != next.user?.needsOnboarding) {
        notifyListeners();
      }
    });
  }

  late final ProviderSubscription<AuthState> _subscription;

  @override
  void dispose() {
    _subscription.close();
    super.dispose();
  }
}

final _authChangeNotifierProvider = ChangeNotifierProvider<_AuthChangeNotifier>((ref) {
  return _AuthChangeNotifier(ref);
});

bool _hasRedirectedToDefaultTab = false;

final routerProvider = Provider<GoRouter>((ref) {
  final notifier = ref.watch(_authChangeNotifierProvider);

  return GoRouter(
    initialLocation: '/login',
    refreshListenable: notifier,
    redirect: (context, state) {
      // Read current state at redirect time (not captured at creation time)
      final authState = ref.read(authProvider);
      final dashboardPrefsState = ref.read(dashboardPrefsProvider);

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

      // Logged in - check if user still needs onboarding
      final user = authState.user;
      final needsOnboarding = user != null && user.needsOnboarding;
      final onboardingRoute = user?.onboardingRoute;

      // Logged in but on login/register page - redirect to correct destination
      if (isLoggedIn && isPublicRoute) {
        // If user needs onboarding, send them to the correct step
        if (needsOnboarding && onboardingRoute != null) {
          return onboardingRoute;
        }
        if (!authState.canUseMobile) {
          return '/mobile-access';
        }
        // Check default tab redirect
        if (!_hasRedirectedToDefaultTab && dashboardPrefsState.isLoaded) {
          _hasRedirectedToDefaultTab = true;
          final tabIndex = dashboardPrefsState.prefs.defaultTabIndex;
          if (tabIndex != 0) {
            const tabRoutes = ['/', '/pos', '/transactions', '/orders', '/menu', '/settings'];
            return tabRoutes[tabIndex];
          }
        }
        return '/';
      }

      // Logged in and needs onboarding - only allow the correct onboarding step
      if (isLoggedIn && needsOnboarding && onboardingRoute != null) {
        if (!isOnboardingRoute) {
          return onboardingRoute;
        }
        return null; // Allow navigation within onboarding routes
      }

      // Logged in, onboarding done, but no mobile access - allow onboarding routes or mobile-access
      if (isLoggedIn && !authState.canUseMobile && !isMobileAccessRoute && !isOnboardingRoute) {
        return '/mobile-access';
      }

      // Default tab redirect on first navigation to home
      if (isLoggedIn && location == '/' && !_hasRedirectedToDefaultTab && dashboardPrefsState.isLoaded) {
        _hasRedirectedToDefaultTab = true;
        final tabIndex = dashboardPrefsState.prefs.defaultTabIndex;
        if (tabIndex != 0) {
          const tabRoutes = ['/', '/pos', '/transactions', '/orders', '/menu', '/settings'];
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
            path: '/customers',
            builder: (context, state) => const CustomersScreen(),
          ),
          GoRoute(
            path: '/customers/add',
            builder: (context, state) => const AddCustomerScreen(),
          ),
          GoRoute(
            path: '/customers/edit/:id',
            builder: (context, state) {
              final id = int.parse(state.pathParameters['id']!);
              return AddCustomerScreen(customerId: id);
            },
          ),
          GoRoute(
            path: '/customers/:id',
            builder: (context, state) {
              final id = int.parse(state.pathParameters['id']!);
              return CustomerDetailScreen(customerId: id);
            },
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
          GoRoute(
            path: '/printer-setup',
            builder: (context, state) => const PrinterSetupScreen(),
          ),
          GoRoute(
            path: '/efd-settings',
            builder: (context, state) => const EfdSettingsScreen(),
          ),
          GoRoute(
            path: '/whatsapp-settings',
            builder: (context, state) => const WhatsAppSettingsScreen(),
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
