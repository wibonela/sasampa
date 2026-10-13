// Drives the app through the launch-video flow while the simulator is recording.
// Run it through video/scripts/record.sh launch (see video/README.md).
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';

import 'support/recorder.dart';

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('launch video flow', (tester) async {
    final r = Recorder(tester);

    // 01 login -> dashboard
    await r.startAndLogin(markId: '01-hook');
    r.mark('01-hook', 'end');

    // 02 sale: add products to the cart
    await r.tapNav(1);
    r.mark('02-sale', 'start');
    await r.hold(1.5);
    await r.tapText('Azam Juice 1L');
    await r.tapText('Coca-Cola 500ml');
    await r.tapText('Coca-Cola 500ml', after: 1.0);
    await r.hold(2.5);
    r.mark('02-sale', 'end');

    // 03 search products
    r.mark('03-scan', 'start');
    await tester.enterText(find.byType(TextField).first, 'Sabuni');
    await r.hold(2.5);
    await tester.enterText(find.byType(TextField).first, '');
    await r.hold(1.2);
    await r.tapText('Vinywaji', after: 2.0);
    await r.tapText('Vyakula', after: 2.0);
    r.mark('03-scan', 'end');

    // 04 pay
    r.mark('04-pay', 'start');
    await tester.tap(find.byIcon(Icons.arrow_forward).last, warnIfMissed: false);
    await r.hold(2.5);
    await r.tapText('Taslimu', after: 1.2);
    final amount = find.byWidgetPredicate(
        (w) => w is TextField && w.decoration?.labelText == 'Kiasi kilicholipwa');
    if (amount.evaluate().isNotEmpty) {
      await tester.enterText(amount, '10000');
      await r.hold(1.5);
    }
    r.dismissKeyboard();
    await r.hold(1.0);
    final complete = find.textContaining('Kamilisha Mauzo').last;
    await tester.ensureVisible(complete);
    await r.hold(1.0);
    await tester.tap(complete, warnIfMissed: false);
    await r.hold(4);
    r.mark('04-pay', 'end');

    // 05 receipt
    r.mark('05-receipt', 'start');
    await r.tapText('Tazama Risiti', after: 5);
    await r.tapText('Funga', after: 1.5);
    r.mark('05-receipt', 'end');

    // 06 dashboard, stock, sales history
    r.mark('06-reports', 'start');
    await r.tapNav(0);
    await r.hold(4);
    await r.tapNav(4);
    await r.tapText('Viwango vya hifadhi na marekebisho', after: 4.5);
    await r.backToMenu();
    await r.tapText('Tazama mauzo yote', after: 4.5);
    await r.backToMenu();
    r.mark('06-reports', 'end');

    // 07 orders
    r.mark('07-orders', 'start');
    await r.tapNav(3);
    await r.hold(3);
    await r.tapText('Baba Juma', after: 5);
    r.mark('07-orders', 'end');
    await r.hold(1);
  });
}
