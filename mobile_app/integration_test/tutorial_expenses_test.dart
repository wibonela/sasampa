// Tutorial: "Matumizi". Run via video/scripts/record.sh tutorial-expenses.
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';

import 'support/recorder.dart';

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('tutorial: expenses', (tester) async {
    final r = Recorder(tester);
    await r.startAndLogin(markId: 'warmup');

    // 01 open expenses
    await r.tapNav(4);
    r.mark('01-open', 'start');
    await r.hold(2);
    await r.tapText('Matumizi', index: 1, after: 5);
    r.mark('01-open', 'end');

    // 02 start a new expense
    r.mark('02-add', 'start');
    await r.hold(1);
    await r.tapText('Ongeza Matumizi', after: 4);
    r.mark('02-add', 'end');

    // 03 fill it in
    r.mark('03-fill', 'start');
    await r.tapText('Chagua', after: 2);
    await r.tapTopmost('Usafiri', after: 2);
    await r.scrollToTop();
    await r.fill('Sugar', 'Usafiri wa mizigo', after: 2);
    await r.scrollToTop();
    await r.fillAt(1, '15000', after: 2);
    await r.scrollToTop();
    await r.fillAt(2, '1', after: 2.5);
    await r.scrollToTop();
    r.dismissKeyboard();
    await r.hold(2);
    r.mark('03-fill', 'end');

    // 04 save and see it in the list
    r.mark('04-save', 'start');
    await r.tapButton('Hifadhi', after: 5);
    await r.hold(2);
    r.mark('04-save', 'end');
  });
}
