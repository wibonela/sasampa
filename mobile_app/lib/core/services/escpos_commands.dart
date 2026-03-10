import 'dart:typed_data';
import 'package:intl/intl.dart';

enum PaperSize { mm58, mm80 }

class EscPosCommands {
  static final _currencyFormat = NumberFormat('#,###');

  static int _charsPerLine(PaperSize size) => size == PaperSize.mm58 ? 32 : 48;

  static List<int> _initPrinter() => [0x1B, 0x40]; // ESC @
  static List<int> _setAlignCenter() => [0x1B, 0x61, 0x01]; // ESC a 1
  static List<int> _setAlignLeft() => [0x1B, 0x61, 0x00]; // ESC a 0
  static List<int> _setAlignRight() => [0x1B, 0x61, 0x02]; // ESC a 2
  static List<int> _setBoldOn() => [0x1B, 0x45, 0x01]; // ESC E 1
  static List<int> _setBoldOff() => [0x1B, 0x45, 0x00]; // ESC E 0
  static List<int> _setDoubleHeight() => [0x1D, 0x21, 0x01]; // GS ! 1
  static List<int> _setNormalSize() => [0x1D, 0x21, 0x00]; // GS ! 0
  static List<int> _feedLines(int n) => [0x1B, 0x64, n]; // ESC d n
  static List<int> _cutPaper() => [0x1D, 0x56, 0x00]; // GS V 0 (full cut)

  static List<int> _printText(String text) {
    return [...text.codeUnits, 0x0A]; // text + LF
  }

  static String _buildDivider(PaperSize size) {
    return '-' * _charsPerLine(size);
  }

  static String _padRow(String left, String right, PaperSize size) {
    final maxWidth = _charsPerLine(size);
    final space = maxWidth - left.length - right.length;
    if (space < 1) return '$left $right';
    return '$left${' ' * space}$right';
  }

  static String _padThreeCol(String col1, String col2, String col3, int c1, int c2, int c3) {
    final s1 = col1.length > c1 ? col1.substring(0, c1) : col1.padRight(c1);
    final s2 = col2.length > c2 ? col2.substring(0, c2) : col2.padLeft(c2);
    final s3 = col3.length > c3 ? col3.substring(0, c3) : col3.padLeft(c3);
    return '$s1$s2$s3';
  }

  /// Build receipt bytes from receipt data (same format as checkout_sheet response)
  static Uint8List buildReceiptBytes(
    Map<String, dynamic> transaction, {
    PaperSize paperSize = PaperSize.mm80,
    String? companyName,
    String? companyPhone,
    String? companyAddress,
  }) {
    final bytes = <int>[];
    final width = _charsPerLine(paperSize);
    final items = transaction['items'] as List? ?? [];

    // Column widths for items
    final c1 = (width * 0.5).floor(); // Item name
    final c2 = (width * 0.15).floor(); // Qty
    final c3 = width - c1 - c2; // Amount

    // Init
    bytes.addAll(_initPrinter());

    // Header
    bytes.addAll(_setAlignCenter());
    bytes.addAll(_setBoldOn());
    bytes.addAll(_setDoubleHeight());
    bytes.addAll(_printText(companyName ?? 'SASAMPA POS'));
    bytes.addAll(_setNormalSize());
    bytes.addAll(_setBoldOff());

    if (companyAddress != null) bytes.addAll(_printText(companyAddress));
    if (companyPhone != null) bytes.addAll(_printText('Tel: $companyPhone'));

    // TIN / VRN
    final companyTin = transaction['company_tin'] as String?;
    final companyVrn = transaction['company_vrn'] as String?;
    if (companyTin != null && companyTin.isNotEmpty) {
      bytes.addAll(_printText('TIN: $companyTin'));
    }
    if (companyVrn != null && companyVrn.isNotEmpty) {
      bytes.addAll(_printText('VRN: $companyVrn'));
    }

    bytes.addAll(_printText(''));
    bytes.addAll(_setAlignLeft());
    bytes.addAll(_printText(_buildDivider(paperSize)));

    // Transaction info
    final txNumber = transaction['transaction_number'] ?? '';
    bytes.addAll(_printText('Receipt #: $txNumber'));

    // Parse date
    final createdAt = transaction['created_at']?.toString() ?? '';
    if (createdAt.length >= 19) {
      try {
        final dt = DateTime.parse(createdAt);
        bytes.addAll(_printText('Date: ${DateFormat('dd/MM/yyyy').format(dt)}'));
        bytes.addAll(_printText('Time: ${DateFormat('HH:mm').format(dt)}'));
      } catch (_) {}
    }

    final cashierName = transaction['cashier'] is Map
        ? transaction['cashier']['name']
        : transaction['cashier'];
    if (cashierName != null) bytes.addAll(_printText('Cashier: $cashierName'));

    if (transaction['customer_name'] != null) {
      bytes.addAll(_printText('Customer: ${transaction['customer_name']}'));
    }
    if (transaction['customer_tin'] != null) {
      bytes.addAll(_printText('TIN: ${transaction['customer_tin']}'));
    }

    bytes.addAll(_printText(_buildDivider(paperSize)));

    // Items header
    bytes.addAll(_setBoldOn());
    bytes.addAll(_printText(_padThreeCol('Item', 'Qty', 'Amount', c1, c2, c3)));
    bytes.addAll(_setBoldOff());
    bytes.addAll(_printText(_buildDivider(paperSize)));

    // Items
    for (final item in items) {
      final name = item['product_name'] ?? item['name'] ?? '';
      final qty = '${item['quantity'] ?? 0}';
      final unitPrice = (item['unit_price'] ?? 0).toDouble();
      final subtotal = (item['subtotal'] ?? 0).toDouble();

      // Name line (may truncate)
      final displayName = name.length > c1 ? name.substring(0, c1) : name;
      bytes.addAll(_printText(
        _padThreeCol(displayName, qty, 'TZS ${_currencyFormat.format(subtotal)}', c1, c2, c3),
      ));
      // Unit price sub-line
      bytes.addAll(_printText('  @ TZS ${_currencyFormat.format(unitPrice)}'));
    }

    bytes.addAll(_printText(_buildDivider(paperSize)));

    // Totals
    final subtotal = (transaction['subtotal'] ?? 0).toDouble();
    final taxAmount = (transaction['tax_amount'] ?? 0).toDouble();
    final discountAmount = (transaction['discount_amount'] ?? 0).toDouble();
    final total = (transaction['total'] ?? 0).toDouble();

    bytes.addAll(_printText(_padRow('Subtotal:', 'TZS ${_currencyFormat.format(subtotal)}', paperSize)));
    if (taxAmount > 0) {
      bytes.addAll(_printText(_padRow('VAT:', 'TZS ${_currencyFormat.format(taxAmount)}', paperSize)));
    }
    if (discountAmount > 0) {
      bytes.addAll(_printText(_padRow('Discount:', '-TZS ${_currencyFormat.format(discountAmount)}', paperSize)));
    }

    bytes.addAll(_setBoldOn());
    bytes.addAll(_printText(_padRow('TOTAL:', 'TZS ${_currencyFormat.format(total)}', paperSize)));
    bytes.addAll(_setBoldOff());

    bytes.addAll(_printText(_buildDivider(paperSize)));

    // Payment
    final paymentMethod = transaction['payment_method'] ?? 'cash';
    final methodLabel = switch (paymentMethod) {
      'cash' => 'Cash',
      'card' => 'Card',
      'mobile' => 'Mobile Money',
      'bank_transfer' => 'Bank Transfer',
      'credit' => 'Credit',
      _ => paymentMethod.toString(),
    };

    bytes.addAll(_printText(_padRow('Payment:', methodLabel, paperSize)));

    if (paymentMethod != 'credit') {
      final amountPaid = (transaction['amount_paid'] ?? 0).toDouble();
      final changeGiven = (transaction['change_given'] ?? 0).toDouble();
      bytes.addAll(_printText(_padRow('Paid:', 'TZS ${_currencyFormat.format(amountPaid)}', paperSize)));
      if (changeGiven > 0) {
        bytes.addAll(_printText(_padRow('Change:', 'TZS ${_currencyFormat.format(changeGiven)}', paperSize)));
      }
    }

    bytes.addAll(_printText(_buildDivider(paperSize)));

    // Fiscal Data
    final fiscalReceiptNumber = transaction['fiscal_receipt_number'] as String?;
    final fiscalVerificationCode = transaction['fiscal_verification_code'] as String?;
    final fiscalQrCode = transaction['fiscal_qr_code'] as String?;
    if (fiscalReceiptNumber != null && fiscalReceiptNumber.isNotEmpty) {
      bytes.addAll(_setAlignLeft());
      bytes.addAll(_printText(_buildDivider(paperSize)));
      bytes.addAll(_setBoldOn());
      bytes.addAll(_printText('FISCAL RECEIPT'));
      bytes.addAll(_setBoldOff());
      bytes.addAll(_printText(_padRow('Fiscal #:', fiscalReceiptNumber, paperSize)));
      if (fiscalVerificationCode != null && fiscalVerificationCode.isNotEmpty) {
        bytes.addAll(_printText(_padRow('Verify:', fiscalVerificationCode, paperSize)));
      }
      // QR Code via ESC/POS: GS ( k
      if (fiscalQrCode != null && fiscalQrCode.isNotEmpty) {
        bytes.addAll(_setAlignCenter());
        bytes.addAll(_printQrCode(fiscalQrCode));
      }
      bytes.addAll(_printText(_buildDivider(paperSize)));
    }

    // Footer
    bytes.addAll(_setAlignCenter());
    bytes.addAll(_printText(''));
    bytes.addAll(_printText('Thank you for your purchase!'));
    bytes.addAll(_printText('Karibu tena / Welcome again'));
    bytes.addAll(_printText(''));
    bytes.addAll(_printText(txNumber));
    bytes.addAll(_printText(''));
    bytes.addAll(_printText('Powered by Sasampa POS'));
    bytes.addAll(_printText('sasampa.com'));

    // Feed and cut
    bytes.addAll(_feedLines(4));
    bytes.addAll(_cutPaper());

    return Uint8List.fromList(bytes);
  }

  /// Generate QR code using ESC/POS GS ( k commands
  static List<int> _printQrCode(String data) {
    final bytes = <int>[];
    final dataBytes = data.codeUnits;
    final storeLen = dataBytes.length + 3;
    final pL = storeLen % 256;
    final pH = storeLen ~/ 256;

    // GS ( k - QR Code: Select model 2
    bytes.addAll([0x1D, 0x28, 0x6B, 0x04, 0x00, 0x31, 0x41, 0x32, 0x00]);
    // GS ( k - Set QR code size (module size = 4)
    bytes.addAll([0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x43, 0x04]);
    // GS ( k - Set error correction level (L = 48)
    bytes.addAll([0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x45, 0x30]);
    // GS ( k - Store QR code data
    bytes.addAll([0x1D, 0x28, 0x6B, pL, pH, 0x31, 0x50, 0x30, ...dataBytes]);
    // GS ( k - Print QR code
    bytes.addAll([0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x51, 0x30]);
    // Line feed after QR
    bytes.add(0x0A);

    return bytes;
  }
}
