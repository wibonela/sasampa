// Tutorial: "Wateja na mikopo". Run via video/scripts/record.sh tutorial-customers.
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';

import 'support/recorder.dart';

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('tutorial: customers and credit', (tester) async {
    final r = Recorder(tester);
    await r.startAndLogin(markId: 'warmup');

    // 01 open the customers list
    await r.tapNav(4);
    r.mark('01-list', 'start');
    await r.hold(2);
    await r.tapText('Simamia wateja na mikopo', index: 1, after: 5);
    r.mark('01-list', 'end');

    // 02 add a customer
    r.mark('02-add', 'start');
    await r.tapIcon(Icons.person_add, after: 2.5);
    await r.fill('Jina la mteja', 'Mama Rehema');
    await r.fill('Namba ya simu', '0712345678');
    await r.fill('Kikomo cha Mkopo', '30000', after: 2);
    r.dismissKeyboard();
    await r.hold(1.5);
    await r.tapButton('Ongeza Mteja', after: 4);
    r.mark('02-add', 'end');

    // 03 open a customer who owes money
    r.mark('03-detail', 'start');
    await r.tapText('Baba Juma', after: 6);
    r.mark('03-detail', 'end');

    // 04 record a payment
    r.mark('04-payment', 'start');
    await r.tapButton('Rekodi Malipo', after: 3);
    await r.fill('Kiasi', '5000', after: 2);
    r.dismissKeyboard();
    await r.hold(1.5);
    await r.tapButton('Rekodi Malipo', index: -1, after: 5);
    await r.hold(3);
    r.mark('04-payment', 'end');
    await r.hold(1);
  });
}
