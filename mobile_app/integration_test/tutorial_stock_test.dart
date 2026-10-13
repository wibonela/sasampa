// Tutorial: "Hifadhi ya bidhaa". Run via video/scripts/record.sh tutorial-stock.
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';

import 'support/recorder.dart';

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('tutorial: stock', (tester) async {
    final r = Recorder(tester);
    await r.startAndLogin(markId: 'warmup');

    // 01 open stock levels
    await r.tapNav(4);
    r.mark('01-open', 'start');
    await r.hold(2);
    await r.tapText('Viwango vya hifadhi na marekebisho', after: 5);
    r.mark('01-open', 'end');

    // 02 low stock tab
    r.mark('02-low', 'start');
    await tester.tap(find.textContaining('Bidhaa Zinazopungua ('), warnIfMissed: false);
    await r.hold(5);
    r.mark('02-low', 'end');

    // 03 add stock to a product
    r.mark('03-adjust', 'start');
    await r.tapText('Sukari 1kg', after: 3);
    await r.fill('Idadi', '24', after: 2);
    r.dismissKeyboard();
    await r.hold(1.5);
    await r.tapText('Hifadhi Marekebisho', after: 3);
    r.mark('03-adjust', 'end');

    // 04 check the result
    r.mark('04-result', 'start');
    await r.hold(2);
    await tester.tap(find.textContaining('Zote (').first, warnIfMissed: false);
    await r.hold(5);
    r.mark('04-result', 'end');
    await r.hold(1);
  });
}
