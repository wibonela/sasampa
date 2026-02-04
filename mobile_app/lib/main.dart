import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'app/router/app_router.dart';
import 'app/theme/theme.dart';
import 'app/theme/colors.dart';
import 'core/providers.dart';
import 'core/storage/secure_storage.dart';

// Global storage instance - initialized before app starts
SecureStorage? _globalStorage;

SecureStorage get globalStorage {
  _globalStorage ??= SecureStorage();
  return _globalStorage!;
}

void main() async {
  // Ensure Flutter is ready
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize storage with error handling
  try {
    _globalStorage = SecureStorage();
    final initialized = await _globalStorage!.init();
    if (initialized) {
      debugPrint('MAIN: Storage initialized successfully');
    } else {
      debugPrint('MAIN: Storage initialization returned false');
    }
  } catch (e, stack) {
    debugPrint('MAIN: Storage init error - $e');
    debugPrint('MAIN: Stack trace - $stack');
    // Create a fresh instance even if init failed
    _globalStorage = SecureStorage();
  }

  // Set preferred orientations
  try {
    await SystemChrome.setPreferredOrientations([
      DeviceOrientation.portraitUp,
      DeviceOrientation.portraitDown,
    ]);
  } catch (e) {
    debugPrint('MAIN: Orientation error - $e');
  }

  // Set system UI overlay style
  try {
    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: Brightness.dark,
        systemNavigationBarColor: Colors.white,
        systemNavigationBarIconBrightness: Brightness.dark,
      ),
    );
  } catch (e) {
    debugPrint('MAIN: SystemUI error - $e');
  }

  // Run app with error boundary
  runApp(const ProviderScope(child: SasampaApp()));
}

class SasampaApp extends ConsumerStatefulWidget {
  const SasampaApp({super.key});

  @override
  ConsumerState<SasampaApp> createState() => _SasampaAppState();
}

class _SasampaAppState extends ConsumerState<SasampaApp> {
  bool _isInitializing = true;
  bool _hasError = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    // Schedule initialization after first frame
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _initializeApp();
    });
  }

  Future<void> _initializeApp() async {
    if (!mounted) return;

    try {
      // Check if user is already logged in (with timeout)
      await ref.read(authProvider.notifier).checkAuth().timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          debugPrint('AUTH_CHECK: Timeout after 15 seconds');
        },
      );
    } catch (e, stack) {
      debugPrint('AUTH_CHECK: Error - $e');
      debugPrint('AUTH_CHECK: Stack - $stack');
      // Don't set error state - just continue to login screen
    }

    if (mounted) {
      setState(() {
        _isInitializing = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    // Show error screen if critical error
    if (_hasError) {
      return MaterialApp(
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light,
        home: ErrorScreen(
          message: _errorMessage ?? 'An error occurred',
          onRetry: () {
            setState(() {
              _hasError = false;
              _isInitializing = true;
            });
            _initializeApp();
          },
        ),
      );
    }

    // Show splash screen while initializing
    if (_isInitializing) {
      return MaterialApp(
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light,
        home: const SplashScreen(),
      );
    }

    // Main app with router
    final router = ref.watch(routerProvider);

    return MaterialApp.router(
      title: 'Sasampa POS',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light,
      routerConfig: router,
    );
  }
}

class SplashScreen extends StatelessWidget {
  const SplashScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // App Logo/Icon
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                color: AppColors.primary,
                borderRadius: BorderRadius.circular(24),
              ),
              child: const Icon(
                Icons.point_of_sale,
                size: 50,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'Sasampa POS',
              style: TextStyle(
                fontSize: 28,
                fontWeight: FontWeight.bold,
                color: AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Point of Sale',
              style: TextStyle(
                fontSize: 16,
                color: AppColors.textSecondary,
              ),
            ),
            const SizedBox(height: 48),
            const SizedBox(
              width: 24,
              height: 24,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                valueColor: AlwaysStoppedAnimation(AppColors.primary),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class ErrorScreen extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;

  const ErrorScreen({
    super.key,
    required this.message,
    required this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.error_outline,
                size: 64,
                color: AppColors.error,
              ),
              const SizedBox(height: 24),
              const Text(
                'Something went wrong',
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 12),
              Text(
                message,
                style: const TextStyle(
                  fontSize: 14,
                  color: AppColors.textSecondary,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 32),
              ElevatedButton(
                onPressed: onRetry,
                child: const Text('Try Again'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
