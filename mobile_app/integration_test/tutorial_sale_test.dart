// Tutorial: "Jinsi ya kufanya mauzo". Slower pace than the launch video.
// Run it through video/scripts/record.sh tutorial-sale (see video/README.md).
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';

import 'support/recorder.dart';

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('tutorial: make a sale', (tester) async {
    final r = Recorder(tester);

    // Start from the dashboard (login happens before the first step is marked).
    await r.startAndLogin(markId: 'warmup');

    // 01 open the sales screen
    r.mark('01-open', 'start');
    await r.hold(2.5);
    await r.tapNav(1);
    await r.hold(3);
    r.mark('01-open', 'end');

    // 02 add products
    r.mark('02-add', 'start');
    await r.hold(1.5);
    await r.tapText('Azam Juice 1L', after: 2.5);
    await r.tapText('Coca-Cola 500ml', after: 2.5);
    await r.tapText('Coca-Cola 500ml', after: 3);
    r.mark('02-add', 'end');

    // 03 review the cart
    r.mark('03-cart', 'start');
    await tester.tap(find.byIcon(Icons.arrow_forward).last, warnIfMissed: false);
    await r.hold(3);
    await tester.tap(find.byIcon(Icons.add_circle_outline).first, warnIfMissed: false);
    await r.hold(2.5);
    await tester.tap(find.byIcon(Icons.remove_circle_outline).first, warnIfMissed: false);
    await r.hold(2.5);
    r.mark('03-cart', 'end');

    // 04 pay
    r.mark('04-pay', 'start');
    await r.tapText('Taslimu', after: 2);
    final amount = find.byWidgetPredicate(
        (w) => w is TextField && w.decoration?.labelText == 'Kiasi kilicholipwa');
    if (amount.evaluate().isNotEmpty) {
      await tester.enterText(amount, '10000');
      await r.hold(2.5);
    }
    r.dismissKeyboard();
    await r.hold(1.0);
    final complete = find.textContaining('Kamilisha Mauzo').last;
    await tester.ensureVisible(complete);
    await r.hold(1.5);
    await tester.tap(complete, warnIfMissed: false);
    await r.hold(5);
    r.mark('04-pay', 'end');

    // 05 receipt
    r.mark('05-receipt', 'start');
    await r.tapText('Tazama Risiti', after: 6);
    await r.tapText('Funga', after: 2);
    r.mark('05-receipt', 'end');
    await r.hold(1);
  });
}
