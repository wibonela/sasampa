import { useRef, useState, useCallback, useEffect } from 'react';
import {
  StyleSheet,
  View,
  ActivityIndicator,
  StatusBar,
  Platform,
  BackHandler,
  Text,
  TouchableOpacity,
  Linking,
} from 'react-native';
import { SafeAreaView, SafeAreaProvider } from 'react-native-safe-area-context';
import { WebView, WebViewNavigation } from 'react-native-webview';
import NetInfo from '@react-native-community/netinfo';
import * as SplashScreen from 'expo-splash-screen';

// Keep splash screen visible while loading
SplashScreen.preventAutoHideAsync();

const APP_URL = 'https://sasampa.com';

type AppState = 'loading' | 'ready' | 'offline' | 'error';

export default function App() {
  const webViewRef = useRef<WebView>(null);
  const [appState, setAppState] = useState<AppState>('loading');
  const [canGoBack, setCanGoBack] = useState(false);

  // Monitor network connectivity
  useEffect(() => {
    const unsubscribe = NetInfo.addEventListener((state) => {
      if (!state.isConnected && appState === 'ready') {
        setAppState('offline');
      }
    });
    return () => unsubscribe();
  }, [appState]);

  const onLoadEnd = useCallback(async () => {
    setAppState('ready');
    await SplashScreen.hideAsync();
  }, []);

  const onError = useCallback(async () => {
    await SplashScreen.hideAsync();
    const netState = await NetInfo.fetch();
    if (!netState.isConnected) {
      setAppState('offline');
    } else {
      setAppState('error');
    }
  }, []);

  const onHttpError = useCallback((syntheticEvent: any) => {
    const { nativeEvent } = syntheticEvent;
    if (nativeEvent.statusCode >= 500) {
      setAppState('error');
    }
  }, []);

  const handleRetry = useCallback(async () => {
    const netState = await NetInfo.fetch();
    if (!netState.isConnected) {
      setAppState('offline');
      return;
    }
    setAppState('loading');
    webViewRef.current?.reload();
  }, []);

  // Handle external URLs (tel:, mailto:, whatsapp:, etc.)
  const onShouldStartLoadWithRequest = useCallback((request: any) => {
    const { url } = request;
    if (
      url.startsWith('tel:') ||
      url.startsWith('mailto:') ||
      url.startsWith('whatsapp:') ||
      url.startsWith('sms:') ||
      url.startsWith('intent:')
    ) {
      Linking.openURL(url).catch(() => {});
      return false;
    }
    // Allow navigation within sasampa.com
    if (url.startsWith(APP_URL) || url.startsWith('about:')) {
      return true;
    }
    // Open external links in browser
    if (url.startsWith('http://') || url.startsWith('https://')) {
      Linking.openURL(url).catch(() => {});
      return false;
    }
    return true;
  }, []);

  // Handle Android back button
  useEffect(() => {
    if (Platform.OS === 'android') {
      const subscription = BackHandler.addEventListener('hardwareBackPress', () => {
        if (canGoBack && webViewRef.current) {
          webViewRef.current.goBack();
          return true;
        }
        return false;
      });
      return () => subscription.remove();
    }
  }, [canGoBack]);

  // Injected JavaScript to make WebView feel like a native app
  const injectedJS = `
    (function() {
      // ===== Make it feel native, not like a browser =====

      // 1. Prevent ALL overscroll/bounce behavior
      document.body.style.overscrollBehavior = 'none';
      document.documentElement.style.overscrollBehavior = 'none';

      // 2. Inject CSS to disable web-browser behaviors
      var style = document.createElement('style');
      style.textContent = \`
        /* Disable text selection on non-input elements */
        *, *::before, *::after {
          -webkit-user-select: none;
          user-select: none;
          -webkit-touch-callout: none;
          -webkit-tap-highlight-color: transparent;
        }
        /* Allow selection in form inputs and textareas */
        input, textarea, [contenteditable="true"] {
          -webkit-user-select: text;
          user-select: text;
        }
        /* Hide scrollbars but keep scrolling */
        ::-webkit-scrollbar { display: none; }
        * { -ms-overflow-style: none; scrollbar-width: none; }
        /* Smooth momentum scrolling */
        body, .main-content, .sidebar-nav {
          -webkit-overflow-scrolling: touch;
        }
        /* Hide the web mobile header - the native app has its own status bar */
        .mobile-header {
          display: none !important;
        }
        /* Adjust main content padding since we hid the mobile header */
        @media (max-width: 992px) {
          .main-content {
            padding-top: 16px !important;
          }
        }
        /* Hide Sanduku feedback widget inside the app */
        .sanduku-fab, .sanduku-widget, #sanduku-fab, #sanduku-widget,
        [class*="sanduku"] { display: none !important; }
        /* Prevent long-press context menu on images/links */
        img, a { -webkit-touch-callout: none; }
        /* Make the body not bouncy */
        html, body {
          position: fixed;
          width: 100%;
          height: 100%;
          overflow: hidden;
        }
        .main-content {
          position: fixed;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          overflow-y: auto;
          -webkit-overflow-scrolling: touch;
        }
        /* Guest layout (login/register pages) - make scrollable too */
        body[style*="display: flex"] {
          position: relative !important;
          overflow: auto !important;
        }
      \`;
      document.head.appendChild(style);

      // 3. Ensure proper viewport (no zoom)
      var meta = document.querySelector('meta[name="viewport"]');
      if (!meta) {
        meta = document.createElement('meta');
        meta.name = 'viewport';
        document.head.appendChild(meta);
      }
      meta.content = 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover';

      // 4. Prevent pinch-to-zoom
      document.addEventListener('gesturestart', function(e) { e.preventDefault(); }, { passive: false });

      // 5. Prevent pull-to-refresh on iOS (handled natively)
      var lastY = 0;
      document.addEventListener('touchstart', function(e) {
        lastY = e.touches[0].clientY;
      }, { passive: true });

      document.addEventListener('touchmove', function(e) {
        var scrollTop = document.querySelector('.main-content')?.scrollTop || 0;
        var currentY = e.touches[0].clientY;
        if (scrollTop <= 0 && currentY > lastY) {
          e.preventDefault();
        }
      }, { passive: false });

      true;
    })();
  `;

  // Offline screen
  if (appState === 'offline') {
    return (
      <SafeAreaProvider>
        <SafeAreaView style={styles.container}>
          <StatusBar barStyle="dark-content" backgroundColor="#ffffff" />
          <View style={styles.errorContainer}>
            <View style={styles.errorIconCircle}>
              <Text style={styles.errorIconText}>&#128268;</Text>
            </View>
            <Text style={styles.errorTitle}>No Internet Connection</Text>
            <Text style={styles.errorText}>
              Please check your connection and try again.
            </Text>
            <TouchableOpacity style={styles.retryButton} onPress={handleRetry} activeOpacity={0.7}>
              <Text style={styles.retryButtonText}>Retry</Text>
            </TouchableOpacity>
          </View>
        </SafeAreaView>
      </SafeAreaProvider>
    );
  }

  // Error screen
  if (appState === 'error') {
    return (
      <SafeAreaProvider>
        <SafeAreaView style={styles.container}>
          <StatusBar barStyle="dark-content" backgroundColor="#ffffff" />
          <View style={styles.errorContainer}>
            <View style={styles.errorIconCircle}>
              <Text style={styles.errorIconText}>&#9888;&#65039;</Text>
            </View>
            <Text style={styles.errorTitle}>Something Went Wrong</Text>
            <Text style={styles.errorText}>
              Unable to load Sasampa. Please try again.
            </Text>
            <TouchableOpacity style={styles.retryButton} onPress={handleRetry} activeOpacity={0.7}>
              <Text style={styles.retryButtonText}>Try Again</Text>
            </TouchableOpacity>
          </View>
        </SafeAreaView>
      </SafeAreaProvider>
    );
  }

  return (
    <SafeAreaProvider>
      <SafeAreaView style={styles.container} edges={['top']}>
        <StatusBar
          barStyle="dark-content"
          backgroundColor="#ffffff"
          translucent={false}
        />

        <WebView
          ref={webViewRef}
          source={{ uri: APP_URL }}
          style={styles.webview}
          onLoadEnd={onLoadEnd}
          onError={onError}
          onHttpError={onHttpError}
          onNavigationStateChange={(navState: WebViewNavigation) =>
            setCanGoBack(navState.canGoBack)
          }
          onShouldStartLoadWithRequest={onShouldStartLoadWithRequest}
          javaScriptEnabled={true}
          domStorageEnabled={true}
          startInLoadingState={true}
          allowsInlineMediaPlayback={true}
          mediaPlaybackRequiresUserAction={false}
          sharedCookiesEnabled={true}
          cacheEnabled={true}
          injectedJavaScript={injectedJS}
          // === Native feel props ===
          bounces={false}
          overScrollMode="never"
          scrollEnabled={true}
          showsHorizontalScrollIndicator={false}
          showsVerticalScrollIndicator={false}
          allowsBackForwardNavigationGestures={false}
          decelerationRate="normal"
          contentInsetAdjustmentBehavior="never"
          automaticallyAdjustContentInsets={false}
          // Android cookie & session handling
          thirdPartyCookiesEnabled={true}
          setSupportMultipleWindows={false}
          mixedContentMode="compatibility"
          textZoom={100}
          // User agent identifies this as the native app
          userAgent={
            Platform.OS === 'ios'
              ? 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 SasampaApp/1.0'
              : 'Mozilla/5.0 (Linux; Android 14; Mobile) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36 SasampaApp/1.0'
          }
          renderLoading={() => (
            <View style={styles.loadingContainer}>
              <ActivityIndicator size="large" color="#E53E3E" />
            </View>
          )}
        />

        {appState === 'loading' && (
          <View style={styles.splashContainer}>
            <View style={styles.logoContainer}>
              <View style={styles.logo}>
                <View style={styles.logoGrid}>
                  <View style={styles.logoRow}>
                    <View style={styles.logoDot} />
                    <View style={styles.logoDot} />
                    <View style={styles.logoDot} />
                  </View>
                  <View style={styles.logoRow}>
                    <View style={styles.logoDot} />
                    <View style={styles.logoDot} />
                    <View style={styles.logoDot} />
                  </View>
                  <View style={styles.logoBar} />
                </View>
              </View>
              <Text style={styles.appName}>Sasampa</Text>
              <Text style={styles.tagline}>Point of Sale</Text>
            </View>
            <ActivityIndicator size="large" color="#ffffff" style={styles.spinner} />
          </View>
        )}
      </SafeAreaView>
    </SafeAreaProvider>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#ffffff',
  },
  webview: {
    flex: 1,
    backgroundColor: '#ffffff',
  },
  loadingContainer: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#ffffff',
  },
  splashContainer: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#E53E3E',
  },
  logoContainer: {
    alignItems: 'center',
  },
  logo: {
    width: 100,
    height: 100,
    borderRadius: 24,
    backgroundColor: 'rgba(255,255,255,0.2)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  logoGrid: {
    alignItems: 'center',
    gap: 6,
  },
  logoRow: {
    flexDirection: 'row',
    gap: 6,
  },
  logoDot: {
    width: 16,
    height: 16,
    borderRadius: 4,
    backgroundColor: '#ffffff',
  },
  logoBar: {
    width: 54,
    height: 16,
    borderRadius: 4,
    backgroundColor: '#ffffff',
  },
  appName: {
    fontSize: 32,
    fontWeight: '700',
    color: '#ffffff',
    marginTop: 24,
    letterSpacing: -0.5,
  },
  tagline: {
    fontSize: 16,
    color: 'rgba(255,255,255,0.8)',
    marginTop: 6,
  },
  spinner: {
    marginTop: 48,
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 32,
    backgroundColor: '#ffffff',
  },
  errorIconCircle: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: '#FEE2E2',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  errorIconText: {
    fontSize: 36,
  },
  errorTitle: {
    fontSize: 22,
    fontWeight: '700',
    color: '#1D1D1F',
    marginBottom: 8,
    textAlign: 'center',
  },
  errorText: {
    fontSize: 16,
    color: '#86868B',
    textAlign: 'center',
    marginBottom: 32,
    lineHeight: 22,
  },
  retryButton: {
    backgroundColor: '#E53E3E',
    paddingHorizontal: 40,
    paddingVertical: 14,
    borderRadius: 12,
  },
  retryButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '600',
  },
});
