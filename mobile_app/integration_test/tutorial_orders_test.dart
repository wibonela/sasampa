// Tutorial: "Jinsi ya kutengeneza oda na proforma". Run via video/scripts/record.sh tutorial-orders.
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';

import 'support/recorder.dart';

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('tutorial: orders and proforma', (tester) async {
    final r = Recorder(tester);
    await r.startAndLogin(markId: 'warmup');

    // 01 choose products
    await r.tapNav(1);
    r.mark('01-products', 'start');
    await r.hold(2);
    await r.tapText('Azam Juice 1L', after: 2.5);
    await r.tapText('Coca-Cola 500ml', after: 3);
    r.mark('01-products', 'end');

    // 02 choose the customer
    r.mark('02-customer', 'start');
    await tester.tap(find.byIcon(Icons.arrow_forward).last, warnIfMissed: false);
    await r.hold(3);
    await r.tapText('Chagua Mteja', after: 2.5);
    await r.tapText('Baba Juma', after: 3);
    r.mark('02-customer', 'end');

    // 03 save as an order
    r.mark('03-save', 'start');
    await r.hold(1.5);
    await r.tapText('Hifadhi Oda', after: 5);
    await r.tapText('Funga', after: 2);
    r.mark('03-save', 'end');

    // 04 find it in the orders list
    r.mark('04-list', 'start');
    await r.tapNav(3);
    await r.hold(5);
    r.mark('04-list', 'end');

    // 05 open it
    r.mark('05-proforma', 'start');
    await r.tapText('Baba Juma', after: 6);
    await r.hold(2);
    r.mark('05-proforma', 'end');
    await r.hold(1);
  });
}
