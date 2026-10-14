// Drives the real app (demo data) through the screens used for App Store artwork.
// A host script takes simulator screenshots continuously; MARK lines say which moment is which.
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';

import 'support/recorder.dart';

const _lang = String.fromEnvironment('LANG', defaultValue: 'en');
const _flag = String.fromEnvironment('NET_FLAG');
bool get _en => _lang == 'en';

String get _cash => _en ? 'Cash' : 'Taslimu';
String get _complete => _en ? 'Complete Sale' : 'Kamilisha Mauzo';
String get _profit => _en ? 'Profit Breakdown' : 'Uchambuzi wa Faida';
String get _allTime => 'All time'; // the report chips are not localised yet

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('store artwork screens', (tester) async {
    final r = Recorder(tester);
    Future<void> net(String s) async {
      File(_flag).writeAsStringSync(s);
      await r.hold(4);
    }

    await r.startAndLogin(markId: 'login');

    if (_en) {
      await r.tapNav(5);
      await r.tapText('Kiswahili', after: 2); // the language row toggles to English
    }

    // dashboard with the all-time card
    await r.tapNav(0);
    await r.hold(3.5);
    r.mark('shot-dashboard', 'at');
    await r.hold(1.5);

    // sales screen with a cart
    await r.tapNav(1);
    await r.hold(3);
    await r.tapText('Azam Juice 1L', after: 1.2);
    await r.tapText('Coca-Cola 500ml', after: 1.2);
    await r.tapText('Coca-Cola 500ml', after: 1.5);
    r.mark('shot-pos', 'at');
    await r.hold(1.5);

    // go offline and sell
    await net('down');
    await tester.tap(find.byIcon(Icons.arrow_forward).last, warnIfMissed: false);
    await r.hold(2.5);
    await r.tapText(_cash, after: 1.5);
    r.dismissKeyboard();
    await r.hold(1);
    r.mark('shot-checkout', 'at');
    await r.hold(1.5);
    final complete = find.textContaining(_complete).last;
    await tester.ensureVisible(complete);
    await tester.tap(complete, warnIfMissed: false);
    await r.hold(5);
    r.mark('shot-offline', 'at');
    await r.hold(1.5);
    await tester.tap(find.byType(ElevatedButton).last, warnIfMissed: false);
    await r.hold(2.5);
    r.mark('shot-banner', 'at');
    await r.hold(1.5);

    // dashboard while still offline: cached numbers + the unsent-sale banner
    await r.tapNav(0);
    await r.hold(2);
    await tester.drag(find.byType(Scrollable).first, const Offset(0, 400), warnIfMissed: false); // pull to refresh (offline)
    await r.hold(4);
    r.mark('shot-dash-offline', 'at');
    await r.hold(1.5);

    // back online: banner clears
    await net('up');
    for (var i = 0; i < 90 && find.textContaining('waiting to send').evaluate().isNotEmpty; i++) {
      await r.hold(1);
    }

    // profit report, all time
    await r.tapNav(0);
    await r.hold(2);
    await r.tapText(_profit, after: 3);
    await r.tapText(_allTime, after: 3.5);
    r.mark('shot-report', 'at');
    await r.hold(1.5);
  });
}
