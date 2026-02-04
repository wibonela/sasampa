// Basic app widget test
//
// This is a placeholder test. Add more specific tests as features are developed.

import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:sasampa_pos/main.dart';

void main() {
  testWidgets('App launches successfully', (WidgetTester tester) async {
    // Build our app and trigger a frame.
    await tester.pumpWidget(
      const ProviderScope(
        child: SasampaApp(),
      ),
    );

    // Verify the app builds without errors
    expect(find.byType(SasampaApp), findsOneWidget);
  });
}
