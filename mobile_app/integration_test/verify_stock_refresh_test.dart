// Stock set to 0 / restored on the server while the app is open: the POS must follow it.
// Needs the watcher in the run script (reads the flag file named by --dart-define=NET_FLAG).
import 'dart:io';

import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';

import 'support/recorder.dart';

const _flag = String.fromEnvironment('NET_FLAG');
const _badge = 'Bidhaa Zimeisha'; // Swahili "Out of Stock"

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('POS refreshes stock when opened', (tester) async {
    final r = Recorder(tester);
    Future<void> server(String s) async {
      File(_flag).writeAsStringSync(s);
      await r.hold(3);
    }

    await r.startAndLogin(markId: 'login');
    await r.tapNav(1);
    await r.hold(2);
    final before = find.text(_badge).evaluate().length;
    // ignore: avoid_print
    print('CHECK baseline out-of-stock badges: $before');

    await r.tapNav(0);
    await server('zero');
    await r.tapNav(1);
    await r.hold(3);
    final zeroed = find.text(_badge).evaluate().length;
    // ignore: avoid_print
    print('CHECK after stock set to 0 on server: $zeroed');
    expect(zeroed, before + 1, reason: 'POS should show the new out-of-stock item');

    await r.tapNav(0);
    await server('restore');
    await r.tapNav(1);
    await r.hold(3);
    final restored = find.text(_badge).evaluate().length;
    // ignore: avoid_print
    print('CHECK after stock restored on server: $restored');
    expect(restored, before, reason: 'POS should clear the badge once stock is back');
  });
}
