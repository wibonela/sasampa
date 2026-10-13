// Drives the app through the launch-video flow while the simulator is recording.
// Run against a local demo server (see video/README.md):
//   flutter test integration_test/demo_recording_test.dart -d <simulator> \
//     --dart-define=API_BASE_URL=http://127.0.0.1:8099/api/v1
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';
import 'package:sasampa_pos/main.dart' as app;

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('launch video flow', (tester) async {
    // Real-time wait that keeps the UI pumping (animations, network results).
    Future<void> hold(double seconds) async {
      final end = DateTime.now().add(Duration(milliseconds: (seconds * 1000).round()));
      while (DateTime.now().isBefore(end)) {
        await tester.pump(const Duration(milliseconds: 100));
      }
    }

    void mark(String id, String edge) =>
        // ignore: avoid_print
        print('MARK $id $edge ${DateTime.now().millisecondsSinceEpoch}');

    Future<void> tapText(String text, {int index = 0, double after = 1.5}) async {
      for (var i = 0; i < 30 && find.text(text).evaluate().length <= index; i++) {
        await tester.pump(const Duration(milliseconds: 100));
      }
      final vertical = find.byWidgetPredicate((w) => w is Scrollable && w.axis == Axis.vertical);
      for (final dy in [-300.0, 300.0]) {
        for (var i = 0; i < 12 && find.text(text).evaluate().length <= index && vertical.evaluate().isNotEmpty; i++) {
          await tester.drag(vertical.last, Offset(0, dy), warnIfMissed: false);
          await hold(0.4);
        }
      }
      if (find.text(text).evaluate().length <= index) {
        final texts = find.byType(Text).evaluate().map((e) => (e.widget as Text).data).toList();
        // ignore: avoid_print
        print('NOT FOUND "$text", visible texts: $texts');
      }
      final f = find.text(text).at(index);
      await tester.ensureVisible(f);
      await tester.tap(f, warnIfMissed: false);
      await hold(after);
    }

    Future<void> tapNav(int index) async {
      final bar = find.byType(BottomNavigationBar);
      final items = find.descendant(of: bar, matching: find.byType(InkResponse));
      await tester.tap(items.at(index));
      await hold(1.8);
    }

    Future<void> backToMenu() async {
      if (find.byType(BottomNavigationBar).evaluate().isNotEmpty) {
        await tapNav(4);
      } else {
        await tester.pageBack();
        await hold(1.5);
      }
    }

    app.main();

    final loginButton = find.widgetWithText(ElevatedButton, 'Ingia');
    final navBar = find.byType(BottomNavigationBar);
    // Wait for the splash/auth check to land on either login or the dashboard.
    for (var i = 0; i < 300 && loginButton.evaluate().isEmpty && navBar.evaluate().isEmpty; i++) {
      await tester.pump(const Duration(milliseconds: 100));
    }
    if (loginButton.evaluate().isEmpty && navBar.evaluate().isEmpty) {
      final texts = find.byType(Text).evaluate().map((e) => (e.widget as Text).data).toList();
      // ignore: avoid_print
      print('STUCK, visible texts: $texts');
    }
    await hold(1.5);

    // ---- 01 login -> dashboard
    mark('01-hook', 'start');
    if (loginButton.evaluate().isNotEmpty) {
      await tester.enterText(find.byType(TextField).at(0), 'neema@demo.test');
      await hold(0.8);
      await tester.enterText(find.byType(TextField).at(1), 'demo1234');
      await hold(1.0);
      await tester.testTextInput.receiveAction(TextInputAction.done);
      await hold(1.5);
      if (loginButton.evaluate().isNotEmpty && navBar.evaluate().isEmpty) {
        await tester.tap(find.text('Ingia').last, warnIfMissed: false);
      }
      for (var i = 0; i < 300 && navBar.evaluate().isEmpty; i++) {
        await tester.pump(const Duration(milliseconds: 100));
      }
      if (navBar.evaluate().isEmpty) {
        final texts = find.byType(Text).evaluate().map((e) => (e.widget as Text).data).toList();
        // ignore: avoid_print
        print('LOGIN STUCK, visible texts: $texts');
      }
    }
    await hold(4);
    mark('01-hook', 'end');

    // ---- 02 sale: add products to the cart
    await tapNav(1);
    mark('02-sale', 'start');
    await hold(1.5);
    await tapText('Azam Juice 1L');
    await tapText('Coca-Cola 500ml');
    await tapText('Coca-Cola 500ml', after: 1.0);
    await hold(2.5);
    mark('02-sale', 'end');

    // ---- 03 search products
    mark('03-scan', 'start');
    await tester.enterText(find.byType(TextField).first, 'Sabuni');
    await hold(2.5);
    await tester.enterText(find.byType(TextField).first, '');
    await hold(1.2);
    await tapText('Vinywaji', after: 2.0);
    await tapText('Vyakula', after: 2.0);
    mark('03-scan', 'end');

    // ---- 04 pay
    mark('04-pay', 'start');
    await tester.tap(find.byIcon(Icons.arrow_forward).last, warnIfMissed: false);
    await hold(2.5);
    await tapText('Taslimu', after: 1.2);
    final amount = find.byWidgetPredicate((w) => w is TextField && w.decoration?.labelText == 'Kiasi kilicholipwa');
    if (amount.evaluate().isNotEmpty) {
      await tester.enterText(amount, '10000');
      await hold(1.5);
    }
    FocusManager.instance.primaryFocus?.unfocus();
    await hold(1.0);
    final complete = find.textContaining('Kamilisha Mauzo').last;
    await tester.ensureVisible(complete);
    await hold(1.0);
    await tester.tap(complete, warnIfMissed: false);
    await hold(4);
    mark('04-pay', 'end');

    // ---- 05 receipt
    mark('05-receipt', 'start');
    await tapText('Tazama Risiti', after: 5);
    await tapText('Funga', after: 1.5);
    mark('05-receipt', 'end');

    // ---- 06 dashboard, stock, sales history
    mark('06-reports', 'start');
    await tapNav(0);
    await hold(4);
    await tapNav(4);
    await tapText('Viwango vya hifadhi na marekebisho', after: 4.5);
    await backToMenu();
    await tapText('Tazama mauzo yote', after: 4.5);
    await backToMenu();
    mark('06-reports', 'end');

    // ---- 07 orders
    mark('07-orders', 'start');
    await tapNav(3);
    await hold(3);
    await tapText('Baba Juma', after: 5);
    mark('07-orders', 'end');
    await hold(1);
  });
}
