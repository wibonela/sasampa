import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'app/router/app_router.dart';
import 'app/theme/theme.dart';
import 'app/theme/colors.dart';
import 'core/providers.dart';
import 'core/storage/secure_storage.dart';

// Global storage instance to avoid reinitialization issues
late final SecureStorage globalStorage;

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize storage first
  try {
    await SharedPreferences.getInstance();
    globalStorage = SecureStorage();
    await globalStorage.init();
    print('MAIN: Storage initialized successfully');
  } catch (e) {
    print('MAIN: Storage init error - $e');
  }

  // Set preferred orientations
  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);

  // Set system UI overlay style
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.dark,
      systemNavigationBarColor: Colors.white,
      systemNavigationBarIconBrightness: Brightness.dark,
    ),
  );

  runApp(const ProviderScope(child: SasampaApp()));
}

class SasampaApp extends ConsumerStatefulWidget {
  const SasampaApp({super.key});

  @override
  ConsumerState<SasampaApp> createState() => _SasampaAppState();
}

class _SasampaAppState extends ConsumerState<SasampaApp> {
  bool _isInitializing = true;

  @override
  void initState() {
    super.initState();
    // Use addPostFrameCallback to avoid modifying providers during build
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _initializeApp();
    });
  }

  Future<void> _initializeApp() async {
    try {
      // Check if user is already logged in (with timeout)
      await ref.read(authProvider.notifier).checkAuth()
        .timeout(const Duration(seconds: 10), onTimeout: () {
          print('AUTH_CHECK: Timeout after 10 seconds');
        });
    } catch (e) {
      print('AUTH_CHECK: Error - $e');
    }
    if (mounted) {
      setState(() => _isInitializing = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    // Show splash screen while initializing
    if (_isInitializing) {
      return MaterialApp(
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light,
        home: const SplashScreen(),
      );
    }

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
