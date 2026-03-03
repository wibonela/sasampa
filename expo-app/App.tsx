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
  ScrollView,
  RefreshControl,
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
  const [errorMessage, setErrorMessage] = useState('');

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
    // Check if it's a network issue
    const netState = await NetInfo.fetch();
    if (!netState.isConnected) {
      setAppState('offline');
    } else {
      setAppState('error');
      setErrorMessage('Unable to load Sasampa. Please try again.');
    }
  }, []);

  const onHttpError = useCallback((syntheticEvent: any) => {
    const { nativeEvent } = syntheticEvent;
    if (nativeEvent.statusCode >= 500) {
      setAppState('error');
      setErrorMessage('Server is temporarily unavailable. Please try again shortly.');
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

  // Injected JavaScript for better mobile experience
  const injectedJS = `
    (function() {
      // Prevent overscroll bounce
      document.body.style.overscrollBehavior = 'none';

      // Ensure proper viewport
      var meta = document.querySelector('meta[name="viewport"]');
      if (!meta) {
        meta = document.createElement('meta');
        meta.name = 'viewport';
        document.head.appendChild(meta);
      }
      meta.content = 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no';

      // Set theme-color for Android system bars
      var themeColor = document.querySelector('meta[name="theme-color"]');
      if (!themeColor) {
        themeColor = document.createElement('meta');
        themeColor.name = 'theme-color';
        themeColor.content = '#ffffff';
        document.head.appendChild(themeColor);
      }

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
            <Text style={styles.errorIcon}>&#128268;</Text>
            <Text style={styles.errorTitle}>No Internet Connection</Text>
            <Text style={styles.errorText}>
              Please check your connection and try again.
            </Text>
            <TouchableOpacity style={styles.retryButton} onPress={handleRetry}>
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
            <Text style={styles.errorIcon}>&#9888;&#65039;</Text>
            <Text style={styles.errorTitle}>Something Went Wrong</Text>
            <Text style={styles.errorText}>{errorMessage}</Text>
            <TouchableOpacity style={styles.retryButton} onPress={handleRetry}>
              <Text style={styles.retryButtonText}>Try Again</Text>
            </TouchableOpacity>
          </View>
        </SafeAreaView>
      </SafeAreaProvider>
    );
  }

  return (
    <SafeAreaProvider>
      <SafeAreaView style={styles.container}>
        <StatusBar
          barStyle="dark-content"
          backgroundColor="transparent"
          translucent={Platform.OS === 'android'}
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
          allowsBackForwardNavigationGestures={true}
          allowsInlineMediaPlayback={true}
          mediaPlaybackRequiresUserAction={false}
          sharedCookiesEnabled={true}
          cacheEnabled={true}
          injectedJavaScript={injectedJS}
          // Android cookie & session handling
          thirdPartyCookiesEnabled={true}
          setSupportMultipleWindows={false}
          mixedContentMode="compatibility"
          // Set User-Agent so Laravel treats it as a real browser
          userAgent="Mozilla/5.0 (Linux; Android 14; Mobile) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36 SasampaApp/1.0"
          // Android-specific performance props
          textZoom={100}
          overScrollMode="never"
          pullToRefreshEnabled={Platform.OS === 'android'}
          renderLoading={() => (
            <View style={styles.loadingContainer}>
              <ActivityIndicator size="large" color="#1a1a1a" />
            </View>
          )}
        />

        {appState === 'loading' && (
          <View style={styles.splashContainer}>
            <View style={styles.logoContainer}>
              <View style={styles.logo}>
                <Text style={styles.logoText}>S</Text>
              </View>
              <Text style={styles.appName}>Sasampa POS</Text>
              <Text style={styles.tagline}>Point of Sale</Text>
            </View>
            <ActivityIndicator size="large" color="#1a1a1a" style={styles.spinner} />
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
    backgroundColor: '#f5f5f5',
  },
  logoContainer: {
    alignItems: 'center',
  },
  logo: {
    width: 100,
    height: 100,
    borderRadius: 24,
    backgroundColor: '#1a1a1a',
    justifyContent: 'center',
    alignItems: 'center',
  },
  logoText: {
    fontSize: 48,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  appName: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#1a1a1a',
    marginTop: 24,
  },
  tagline: {
    fontSize: 16,
    color: '#666666',
    marginTop: 8,
  },
  spinner: {
    marginTop: 48,
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 32,
    backgroundColor: '#f5f5f5',
  },
  errorIcon: {
    fontSize: 48,
    marginBottom: 16,
  },
  errorTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#1a1a1a',
    marginBottom: 8,
    textAlign: 'center',
  },
  errorText: {
    fontSize: 16,
    color: '#666666',
    textAlign: 'center',
    marginBottom: 32,
    lineHeight: 22,
  },
  retryButton: {
    backgroundColor: '#1a1a1a',
    paddingHorizontal: 32,
    paddingVertical: 14,
    borderRadius: 12,
  },
  retryButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '600',
  },
});
