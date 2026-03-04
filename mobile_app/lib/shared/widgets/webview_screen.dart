import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../app/theme/colors.dart';
import '../../core/providers.dart';

class WebViewScreen extends ConsumerStatefulWidget {
  final String webPath;
  final String title;

  const WebViewScreen({
    super.key,
    required this.webPath,
    required this.title,
  });

  @override
  ConsumerState<WebViewScreen> createState() => _WebViewScreenState();
}

class _WebViewScreenState extends ConsumerState<WebViewScreen> {
  static const String _baseUrl = 'https://sasampa.com';

  WebViewController? _controller;
  bool _isLoading = true;
  bool _hasError = false;

  @override
  void initState() {
    super.initState();
    _initWebView();
  }

  Future<void> _initWebView() async {
    final storage = ref.read(secureStorageProvider);
    final token = await storage.getToken();

    if (token == null) {
      setState(() {
        _hasError = true;
        _isLoading = false;
      });
      return;
    }

    final redirectPath = widget.webPath;
    final loginUrl =
        '$_baseUrl/mobile-token-login?token=$token&redirect=$redirectPath';

    final controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (_) {
            if (mounted) setState(() => _isLoading = true);
          },
          onPageFinished: (_) {
            // Inject backup JS to hide sidebar/header in case CSS approach fails
            _controller?.runJavaScript('''
              document.body.classList.add('in-app');
              var style = document.createElement('style');
              style.textContent = `
                .sidebar, .mobile-header, .mobile-bottom-nav, .sidebar-overlay { display: none !important; }
                .main-content { margin-left: 0 !important; padding-top: 16px !important; }
              `;
              document.head.appendChild(style);
            ''');
            if (mounted) setState(() => _isLoading = false);
          },
          onWebResourceError: (error) {
            if (mounted) {
              setState(() {
                _hasError = true;
                _isLoading = false;
              });
            }
          },
          onNavigationRequest: (request) {
            final uri = Uri.parse(request.url);
            // Keep sasampa.com URLs in-app
            if (uri.host == 'sasampa.com' || uri.host == 'www.sasampa.com') {
              return NavigationDecision.navigate;
            }
            // Open external links in browser
            launchUrl(uri, mode: LaunchMode.externalApplication);
            return NavigationDecision.prevent;
          },
        ),
      )
      ..setBackgroundColor(Colors.white);

    setState(() => _controller = controller);
    controller.loadRequest(Uri.parse(loginUrl));
  }

  Future<void> _retry() async {
    setState(() {
      _hasError = false;
      _isLoading = true;
      _controller = null;
    });
    await _initWebView();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: Text(widget.title),
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: _hasError
          ? _buildErrorState()
          : Stack(
              children: [
                if (_controller != null)
                  WebViewWidget(controller: _controller!),
                if (_isLoading)
                  const Center(
                    child: CircularProgressIndicator(
                      color: AppColors.primary,
                    ),
                  ),
              ],
            ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.wifi_off_rounded,
              size: 64,
              color: AppColors.gray3,
            ),
            const SizedBox(height: 16),
            const Text(
              'Failed to load page',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Please check your internet connection and try again.',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: AppColors.textSecondary,
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _retry,
              icon: const Icon(Icons.refresh),
              label: const Text('Retry'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 12,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
