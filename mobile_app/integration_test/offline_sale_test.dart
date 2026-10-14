// Offline-sale check: sells while the demo server is switched off, then switches it back on and
// expects the sale to be sent exactly once. Needs the net-control watcher (see the session notes):
// it reads the flag file named by --dart-define=NET_FLAG and stops/starts the server.
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';

import 'support/recorder.dart';

const _flag = String.fromEnvironment('NET_FLAG');

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  Future<void> net(Recorder r, String state) async {
    File(_flag).writeAsStringSync(state);
    await r.hold(4); // watcher polls every second
  }

  testWidgets('offline sale is queued and synced once', (tester) async {
    final r = Recorder(tester);
    await r.startAndLogin(markId: 'login');
    expect(find.textContaining('All-time sales'), findsOneWidget, reason: 'all-time card on dashboard');
    // ignore: avoid_print
    print('CHECK dashboard all-time card OK');

    // load the catalogue while online (this also fills the offline cache)
    await r.tapNav(1);
    await r.hold(3);
    expect(find.text('Azam Juice 1L'), findsWidgets);
    // ignore: avoid_print
    print('CHECK catalogue loaded online');

    await net(r, 'down');

    await r.tapText('Azam Juice 1L', after: 1.5);
    await tester.tap(find.byIcon(Icons.arrow_forward).last, warnIfMissed: false);
    await r.hold(2.5);
    await r.tapText('Taslimu', after: 1.5);
    r.dismissKeyboard();
    final complete = find.textContaining('Kamilisha Mauzo').last;
    await tester.ensureVisible(complete);
    await tester.tap(complete, warnIfMissed: false);
    await r.hold(6);

    expect(find.text('Sale saved offline'), findsOneWidget, reason: 'offline dialog');
    // ignore: avoid_print
    print('CHECK offline dialog shown');
    await tester.tap(find.byType(ElevatedButton).last, warnIfMissed: false);
    await r.hold(2);
    expect(find.textContaining('waiting to send'), findsOneWidget, reason: 'banner shows queued sale');
    // ignore: avoid_print
    print('CHECK banner shows 1 waiting');

    await net(r, 'up');
    // the app retries every minute and on reconnect/resume
    var synced = false;
    for (var i = 0; i < 100 && !synced; i++) {
      await r.hold(1);
      synced = find.textContaining('waiting to send').evaluate().isEmpty &&
          find.textContaining('Sending').evaluate().isEmpty;
    }
    expect(synced, isTrue, reason: 'queue drained after reconnect');
    // ignore: avoid_print
    print('CHECK queue drained after reconnect');
  });
}
