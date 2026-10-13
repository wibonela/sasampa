// Shared helpers for the screen-recording tests (see video/README.md).
// Every helper waits in real time so the simulator recording looks natural,
// and prints MARK lines that video/scripts/cut-clips.mjs uses to cut each beat.
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:sasampa_pos/main.dart' as app;

const demoEmail = 'neema@demo.test';
const demoPassword = 'demo1234';

class Recorder {
  Recorder(this.tester);

  final WidgetTester tester;

  /// Real-time wait that keeps the UI pumping (animations, network results).
  Future<void> hold(double seconds) async {
    final end = DateTime.now().add(Duration(milliseconds: (seconds * 1000).round()));
    while (DateTime.now().isBefore(end)) {
      await tester.pump(const Duration(milliseconds: 100));
    }
  }

  void mark(String id, String edge) =>
      // ignore: avoid_print
      print('MARK $id $edge ${DateTime.now().millisecondsSinceEpoch}');

  void _report(String what) {
    final texts = find.byType(Text).evaluate().map((e) => (e.widget as Text).data).toList();
    // ignore: avoid_print
    print('NOT FOUND $what, visible texts: $texts');
  }

  /// Taps a text, scrolling vertically (both ways) if it is not built yet.
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
      _report('"$text"');
    }
    final f = find.text(text).at(index);
    await tester.ensureVisible(f);
    await tester.tap(f, warnIfMissed: false);
    await hold(after);
  }

  /// Taps a bottom-bar tab by position: 0 Nyumbani, 1 Mauzo (POS), 2 Miamala, 3 Oda, 4 Menyu, 5 Mipangilio.
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

  void dismissKeyboard() => FocusManager.instance.primaryFocus?.unfocus();

  /// Starts the app and logs in with the demo account if the login screen shows up.
  Future<void> startAndLogin({required String markId}) async {
    app.main();
    final loginButton = find.widgetWithText(ElevatedButton, 'Ingia');
    final navBar = find.byType(BottomNavigationBar);

    for (var i = 0; i < 300 && loginButton.evaluate().isEmpty && navBar.evaluate().isEmpty; i++) {
      await tester.pump(const Duration(milliseconds: 100));
    }
    if (loginButton.evaluate().isEmpty && navBar.evaluate().isEmpty) _report('login or dashboard (STUCK)');
    await hold(1.5);

    mark(markId, 'start');
    if (loginButton.evaluate().isNotEmpty) {
      await tester.enterText(find.byType(TextField).at(0), demoEmail);
      await hold(0.8);
      await tester.enterText(find.byType(TextField).at(1), demoPassword);
      await hold(1.0);
      await tester.testTextInput.receiveAction(TextInputAction.done);
      await hold(1.5);
      if (loginButton.evaluate().isNotEmpty && navBar.evaluate().isEmpty) {
        await tester.tap(find.text('Ingia').last, warnIfMissed: false);
      }
      for (var i = 0; i < 300 && navBar.evaluate().isEmpty; i++) {
        await tester.pump(const Duration(milliseconds: 100));
      }
      if (navBar.evaluate().isEmpty) _report('dashboard after login (STUCK)');
    }
    await hold(4);
  }
}
