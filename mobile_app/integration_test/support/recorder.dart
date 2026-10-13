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
    final end = DateTime.now().add(
      Duration(milliseconds: (seconds * 1000).round()),
    );
    while (DateTime.now().isBefore(end)) {
      await tester.pump(const Duration(milliseconds: 100));
    }
  }

  void mark(String id, String edge) =>
      // ignore: avoid_print
      print('MARK $id $edge ${DateTime.now().millisecondsSinceEpoch}');

  void _report(String what) {
    final texts = find
        .byType(Text)
        .evaluate()
        .map((e) => (e.widget as Text).data)
        .toList();
    // ignore: avoid_print
    print('NOT FOUND $what, visible texts: $texts');
  }

  /// Debug helper: prints every visible text (and hint) so scripts can be written against the real screen.
  void dump(String where) {
    final texts = find
        .byType(Text)
        .evaluate()
        .map((e) => (e.widget as Text).data)
        .toList();
    final fields = find.byType(TextField).evaluate().toList().asMap().entries.map((
      e,
    ) {
      final w = e.value.widget as TextField;
      return '#${e.key}[label=${w.decoration?.labelText} hint=${w.decoration?.hintText} value="${w.controller?.text}"]';
    }).toList();
    // ignore: avoid_print
    print('DUMP $where: $texts FIELDS: $fields');
  }

  /// Taps a text, scrolling vertically (both ways) if it is not built yet.
  Future<void> tapText(String text, {int index = 0, double after = 1.5}) async {
    for (var i = 0; i < 30 && find.text(text).evaluate().length <= index; i++) {
      await tester.pump(const Duration(milliseconds: 100));
    }
    final vertical = find.byWidgetPredicate(
      (w) => w is Scrollable && w.axis == Axis.vertical,
    );
    for (final dy in [-300.0, 300.0]) {
      for (
        var i = 0;
        i < 12 &&
            find.text(text).evaluate().length <= index &&
            vertical.evaluate().isNotEmpty;
        i++
      ) {
        await tester.drag(vertical.last, Offset(0, dy), warnIfMissed: false);
        await hold(0.4);
      }
    }
    if (find.text(text).evaluate().length <= index) {
      _report('"$text"');
    }
    final f = find.text(text).at(index);
    await tester.ensureVisible(f);
    await _safeTap(f);
    await hold(after);
  }

  bool _present(Finder f) {
    try {
      return f.evaluate().isNotEmpty;
    } catch (_) {
      return false; // an indexed finder throws when the widget scrolled out of the tree
    }
  }

  /// Taps a widget. Near the bottom bar a centre tap could land on the bar, so tap the top of its row instead.
  Future<void> _safeTap(Finder target) async {
    final screen = tester.view.physicalSize / tester.view.devicePixelRatio;
    final center = tester.getCenter(target);
    if (center.dy <= screen.height - 150) {
      await tester.tap(target, warnIfMissed: false);
      return;
    }
    final row = find.ancestor(of: target, matching: find.byType(InkWell));
    final rect = _present(row)
        ? tester.getRect(row.first)
        : tester.getRect(target);
    await tester.tapAt(Offset(rect.left + 60, rect.top + 14));
  }

  /// Taps a button (elevated, outlined, text...) by its label, for labels that also appear as titles.
  Future<void> tapButton(
    String label, {
    int index = 0,
    double after = 1.5,
  }) async {
    final f = find.ancestor(
      of: find.text(label),
      matching: find.byWidgetPredicate((w) => w is ButtonStyleButton),
    );
    for (var i = 0; i < 30 && f.evaluate().isEmpty; i++) {
      await tester.pump(const Duration(milliseconds: 100));
    }
    final count = f.evaluate().length;
    if (count == 0 || index >= count) {
      _report('button "$label"');
      return;
    }
    // A negative index counts from the end: -1 is the top-most button (for example inside a sheet).
    final target = f.at(index < 0 ? count + index : index);
    await tester.ensureVisible(target);
    await _safeTap(target);
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

  /// Types into the text field whose label or hint contains [labelContains].
  Future<void> fill(
    String labelContains,
    String value, {
    double after = 1.2,
  }) async {
    final field = find.byWidgetPredicate((w) {
      if (w is! TextField) return false;
      final d = w.decoration;
      return (d?.labelText ?? '').contains(labelContains) ||
          (d?.hintText ?? '').contains(labelContains);
    });
    if (field.evaluate().isEmpty) {
      _report('field "$labelContains"');
      return;
    }
    await tester.ensureVisible(field.first);
    await tester.enterText(field.first, value);
    await hold(after);
  }

  /// Types into the n-th text field on screen, for forms whose labels are separate Text widgets.
  Future<void> fillAt(int index, String value, {double after = 1.2}) async {
    final fields = find.byType(TextField);
    if (fields.evaluate().length <= index) {
      _report('field #$index');
      return;
    }
    await tester.ensureVisible(fields.at(index));
    await tester.enterText(fields.at(index), value);
    await hold(after);
  }

  /// Scrolls the current screen back to the top (lazily built forms only keep visible fields in the tree).
  Future<void> scrollToTop() async {
    dismissKeyboard(); // with the keyboard up the viewport is small and fields below are not built
    await hold(0.8);
    final vertical = find.byWidgetPredicate(
      (w) => w is Scrollable && w.axis == Axis.vertical,
    );
    for (var i = 0; i < 4 && vertical.evaluate().isNotEmpty; i++) {
      await tester.drag(
        vertical.last,
        const Offset(0, 700),
        warnIfMissed: false,
      );
      await hold(0.3);
    }
  }

  /// Taps the top-most widget with this text (for example a dropdown item shown over a screen with the same label).
  Future<void> tapTopmost(String text, {double after = 1.5}) async {
    for (var i = 0; i < 30 && find.text(text).evaluate().isEmpty; i++) {
      await tester.pump(const Duration(milliseconds: 100));
    }
    if (find.text(text).evaluate().isEmpty) {
      _report('"$text"');
      return;
    }
    await tester.tap(find.text(text).last, warnIfMissed: false);
    await hold(after);
  }

  /// Taps the first widget that has the given icon.
  Future<void> tapIcon(
    IconData icon, {
    int index = 0,
    double after = 1.5,
  }) async {
    final f = find.byIcon(icon);
    if (f.evaluate().length <= index) {
      _report('icon $icon');
      return;
    }
    await tester.ensureVisible(f.at(index));
    await tester.tap(f.at(index), warnIfMissed: false);
    await hold(after);
  }

  void dismissKeyboard() => FocusManager.instance.primaryFocus?.unfocus();

  /// Starts the app and logs in with the demo account if the login screen shows up.
  Future<void> startAndLogin({required String markId}) async {
    app.main();
    final loginButton = find.widgetWithText(ElevatedButton, 'Ingia');
    final navBar = find.byType(BottomNavigationBar);

    for (
      var i = 0;
      i < 300 && loginButton.evaluate().isEmpty && navBar.evaluate().isEmpty;
      i++
    ) {
      await tester.pump(const Duration(milliseconds: 100));
    }
    if (loginButton.evaluate().isEmpty && navBar.evaluate().isEmpty)
      _report('login or dashboard (STUCK)');
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
