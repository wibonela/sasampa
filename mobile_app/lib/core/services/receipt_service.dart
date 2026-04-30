import 'dart:typed_data';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import 'package:intl/intl.dart';
import 'package:http/http.dart' as http;
import '../network/api_client.dart';
import 'bluetooth_printer_service.dart';
import 'escpos_commands.dart';
import 'printer_preferences.dart';
import 'printer_providers.dart';

class ReceiptService {
  static final _currencyFormat = NumberFormat('#,###');

  /// Fetch the server-rendered receipt PDF.
  /// Server is the single source of truth for receipt design — same bytes
  /// the web download produces. Use this for share/system-print flows so
  /// receipts look identical across web and mobile.
  static Future<Uint8List> fetchServerReceiptPdf(ApiClient api, int transactionId) async {
    final response = await api.getReceiptPdf(transactionId);
    return Uint8List.fromList(response.data!);
  }

  /// Print the server-rendered receipt PDF via the system print dialog.
  static Future<void> printServerReceiptPdf({
    required ApiClient api,
    required int transactionId,
    required String txNumber,
  }) async {
    final bytes = await fetchServerReceiptPdf(api, transactionId);
    await Printing.layoutPdf(
      onLayout: (PdfPageFormat format) async => bytes,
      name: 'Receipt_$txNumber',
    );
  }

  /// Share the server-rendered receipt PDF.
  static Future<void> shareServerReceiptPdf({
    required ApiClient api,
    required int transactionId,
    required String txNumber,
  }) async {
    final bytes = await fetchServerReceiptPdf(api, transactionId);
    await Printing.sharePdf(
      bytes: bytes,
      filename: 'Receipt_$txNumber.pdf',
    );
  }

  /// Generate PDF receipt from API receipt data format
  /// This expects the format returned by /pos/transactions/{id}/receipt
  static Future<Uint8List> generateReceiptPdfFromApi(Map<String, dynamic> receiptData) async {
    final pdf = pw.Document();

    final company = receiptData['company'] as Map<String, dynamic>? ?? {};
    final branch = receiptData['branch'] as Map<String, dynamic>?;
    final transaction = receiptData['transaction'] as Map<String, dynamic>? ?? {};
    final customer = receiptData['customer'] as Map<String, dynamic>? ?? {};
    final items = receiptData['items'] as List? ?? [];
    final totals = receiptData['totals'] as Map<String, dynamic>? ?? {};
    final payment = receiptData['payment'] as Map<String, dynamic>? ?? {};

    // Try to load company logo
    pw.ImageProvider? logoImage;
    final logoUrl = company['logo'] as String?;
    if (logoUrl != null && logoUrl.isNotEmpty) {
      try {
        final response = await http.get(Uri.parse(logoUrl));
        if (response.statusCode == 200) {
          logoImage = pw.MemoryImage(response.bodyBytes);
        }
      } catch (_) {
        // Ignore logo errors
      }
    }

    pdf.addPage(
      pw.Page(
        pageFormat: PdfPageFormat.roll80,
        build: (pw.Context context) {
          return pw.Column(
            mainAxisSize: pw.MainAxisSize.min,
            crossAxisAlignment: pw.CrossAxisAlignment.center,
            children: [
              // Company Logo
              if (logoImage != null)
                pw.Container(
                  width: 50,
                  height: 50,
                  child: pw.Image(logoImage, fit: pw.BoxFit.contain),
                ),
              if (logoImage != null) pw.SizedBox(height: 8),

              // Company Name
              pw.Text(
                company['name'] ?? 'SASAMPA POS',
                style: pw.TextStyle(
                  fontSize: 14,
                  fontWeight: pw.FontWeight.bold,
                ),
                textAlign: pw.TextAlign.center,
              ),

              // Company Address
              if (company['address'] != null)
                pw.Text(
                  company['address'],
                  style: const pw.TextStyle(fontSize: 8),
                  textAlign: pw.TextAlign.center,
                ),

              // Company Phone
              if (company['phone'] != null)
                pw.Text(
                  'Tel: ${company['phone']}',
                  style: const pw.TextStyle(fontSize: 8),
                  textAlign: pw.TextAlign.center,
                ),

              // TIN / VRN
              if (company['tin'] != null && (company['tin'] as String).isNotEmpty)
                pw.Text(
                  'TIN: ${company['tin']}',
                  style: const pw.TextStyle(fontSize: 8),
                  textAlign: pw.TextAlign.center,
                ),
              if (company['vrn'] != null && (company['vrn'] as String).isNotEmpty)
                pw.Text(
                  'VRN: ${company['vrn']}',
                  style: const pw.TextStyle(fontSize: 8),
                  textAlign: pw.TextAlign.center,
                ),

              // Branch Name
              if (branch != null && branch['name'] != null)
                pw.Text(
                  'Branch: ${branch['name']}',
                  style: const pw.TextStyle(fontSize: 8),
                  textAlign: pw.TextAlign.center,
                ),

              pw.SizedBox(height: 8),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 8),

              // Transaction Info
              _buildInfoRow('Receipt #:', transaction['number'] ?? ''),
              _buildInfoRow('Date:', transaction['date'] ?? ''),
              _buildInfoRow('Time:', transaction['time'] ?? ''),
              _buildInfoRow('Cashier:', transaction['cashier'] ?? ''),
              if (customer['name'] != null)
                _buildInfoRow('Customer:', customer['name']),
              if (customer['tin'] != null)
                _buildInfoRow('TIN:', customer['tin']),

              pw.SizedBox(height: 8),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 4),

              // Items header
              pw.Row(
                children: [
                  pw.Expanded(
                    flex: 5,
                    child: pw.Text('Item', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 9)),
                  ),
                  pw.Expanded(
                    flex: 2,
                    child: pw.Text('Qty', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 9), textAlign: pw.TextAlign.center),
                  ),
                  pw.Expanded(
                    flex: 3,
                    child: pw.Text('Amount', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 9), textAlign: pw.TextAlign.right),
                  ),
                ],
              ),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 4),

              // Items
              ...items.map((item) {
                final unitPrice = (item['unit_price'] ?? 0).toDouble();
                final qty = item['quantity'] ?? 0;
                final subtotal = (item['subtotal'] ?? 0).toDouble();
                return pw.Padding(
                  padding: const pw.EdgeInsets.symmetric(vertical: 2),
                  child: pw.Row(
                    crossAxisAlignment: pw.CrossAxisAlignment.start,
                    children: [
                      pw.Expanded(
                        flex: 5,
                        child: pw.Column(
                          crossAxisAlignment: pw.CrossAxisAlignment.start,
                          children: [
                            pw.Text(item['name'] ?? item['product_name'] ?? '', style: const pw.TextStyle(fontSize: 9)),
                            pw.Text('@ TZS ${_currencyFormat.format(unitPrice)}', style: const pw.TextStyle(fontSize: 7, color: PdfColors.grey700)),
                          ],
                        ),
                      ),
                      pw.Expanded(
                        flex: 2,
                        child: pw.Text('$qty', style: const pw.TextStyle(fontSize: 9), textAlign: pw.TextAlign.center),
                      ),
                      pw.Expanded(
                        flex: 3,
                        child: pw.Text('TZS ${_currencyFormat.format(subtotal)}', style: const pw.TextStyle(fontSize: 9), textAlign: pw.TextAlign.right),
                      ),
                    ],
                  ),
                );
              }),

              pw.SizedBox(height: 8),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 8),

              // Totals
              _buildTotalRow('Subtotal:', 'TZS ${_currencyFormat.format((totals['subtotal'] ?? 0).toDouble())}'),
              if ((totals['tax'] ?? 0) > 0)
                _buildTotalRow('VAT:', 'TZS ${_currencyFormat.format((totals['tax'] ?? 0).toDouble())}'),
              if ((totals['discount'] ?? 0) > 0)
                _buildTotalRow('Discount:', '-TZS ${_currencyFormat.format((totals['discount'] ?? 0).toDouble())}'),
              pw.SizedBox(height: 4),
              _buildTotalRow('TOTAL:', 'TZS ${_currencyFormat.format((totals['total'] ?? 0).toDouble())}', isBold: true),

              pw.SizedBox(height: 8),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 8),

              // Payment
              _buildInfoRow('Payment:', payment['method_label'] ?? 'Cash'),
              _buildInfoRow('Amount Paid:', 'TZS ${_currencyFormat.format((payment['amount_paid'] ?? 0).toDouble())}'),
              if ((payment['change'] ?? 0) > 0)
                _buildInfoRow('Change:', 'TZS ${_currencyFormat.format((payment['change'] ?? 0).toDouble())}'),

              pw.SizedBox(height: 8),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 16),

              // Fiscal Data Section
              if (receiptData['fiscal'] != null) ...[
                pw.SizedBox(height: 4),
                pw.Divider(thickness: 0.5),
                pw.SizedBox(height: 4),
                pw.Text('FISCAL RECEIPT', style: pw.TextStyle(fontSize: 8, fontWeight: pw.FontWeight.bold)),
                pw.SizedBox(height: 2),
                _buildInfoRow('Fiscal #:', (receiptData['fiscal'] as Map)['receipt_number'] ?? ''),
                _buildInfoRow('Verify:', (receiptData['fiscal'] as Map)['verification_code'] ?? ''),
                if ((receiptData['fiscal'] as Map)['qr_code'] != null &&
                    ((receiptData['fiscal'] as Map)['qr_code'] as String).isNotEmpty)
                  pw.Container(
                    margin: const pw.EdgeInsets.only(top: 8),
                    alignment: pw.Alignment.center,
                    child: pw.BarcodeWidget(
                      barcode: pw.Barcode.qrCode(),
                      data: (receiptData['fiscal'] as Map)['qr_code'],
                      width: 80,
                      height: 80,
                    ),
                  ),
              ],

              pw.SizedBox(height: 8),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 16),

              // Footer
              pw.Text('Thank you for your purchase!', style: const pw.TextStyle(fontSize: 10)),
              pw.SizedBox(height: 4),
              pw.Text('Karibu tena / Welcome again', style: const pw.TextStyle(fontSize: 10)),
              pw.SizedBox(height: 12),

              // Receipt number box
              pw.Container(
                padding: const pw.EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                decoration: pw.BoxDecoration(
                  border: pw.Border.all(color: PdfColors.black),
                ),
                child: pw.Text(
                  transaction['number'] ?? '',
                  style: pw.TextStyle(fontSize: 10, fontWeight: pw.FontWeight.bold),
                ),
              ),
              pw.SizedBox(height: 8),
              pw.Text('Powered by Sasampa POS', style: const pw.TextStyle(fontSize: 8, color: PdfColors.grey700)),
              pw.Text('sasampa.com', style: const pw.TextStyle(fontSize: 8, color: PdfColors.grey700)),
            ],
          );
        },
      ),
    );

    return pdf.save();
  }

  /// Generate PDF receipt from simple transaction data (for checkout)
  static Future<Uint8List> generateReceiptPdf({
    required Map<String, dynamic> transaction,
    String? companyName,
    String? companyLogo,
    String? cashierName,
  }) async {
    final pdf = pw.Document();

    final items = transaction['items'] as List? ?? [];

    // Parse date
    String dateStr = '';
    String timeStr = '';
    final createdAt = transaction['created_at']?.toString() ?? '';
    if (createdAt.length >= 19) {
      try {
        final dt = DateTime.parse(createdAt);
        dateStr = DateFormat('dd/MM/yyyy').format(dt);
        timeStr = DateFormat('HH:mm').format(dt);
      } catch (_) {
        dateStr = createdAt.length >= 10 ? createdAt.substring(0, 10) : '';
        timeStr = createdAt.length >= 16 ? createdAt.substring(11, 16) : '';
      }
    }

    pdf.addPage(
      pw.Page(
        pageFormat: PdfPageFormat.roll80,
        build: (pw.Context context) {
          return pw.Column(
            mainAxisSize: pw.MainAxisSize.min,
            crossAxisAlignment: pw.CrossAxisAlignment.center,
            children: [
              // Header
              pw.Text(
                companyName ?? 'SASAMPA POS',
                style: pw.TextStyle(
                  fontSize: 14,
                  fontWeight: pw.FontWeight.bold,
                ),
              ),
              pw.SizedBox(height: 8),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 8),

              // Transaction Info
              _buildInfoRow('Receipt #:', transaction['transaction_number'] ?? ''),
              _buildInfoRow('Date:', dateStr),
              _buildInfoRow('Time:', timeStr),
              _buildInfoRow('Cashier:', cashierName ?? transaction['cashier']?['name'] ?? ''),
              if (transaction['customer_name'] != null)
                _buildInfoRow('Customer:', transaction['customer_name']),
              if (transaction['customer_tin'] != null)
                _buildInfoRow('TIN:', transaction['customer_tin']),

              pw.SizedBox(height: 8),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 4),

              // Items header
              pw.Row(
                children: [
                  pw.Expanded(
                    flex: 5,
                    child: pw.Text('Item', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 9)),
                  ),
                  pw.Expanded(
                    flex: 2,
                    child: pw.Text('Qty', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 9), textAlign: pw.TextAlign.center),
                  ),
                  pw.Expanded(
                    flex: 3,
                    child: pw.Text('Amount', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 9), textAlign: pw.TextAlign.right),
                  ),
                ],
              ),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 4),

              // Items
              ...items.map((item) {
                final unitPrice = (item['unit_price'] ?? 0).toDouble();
                final qty = item['quantity'] ?? 0;
                final subtotal = (item['subtotal'] ?? 0).toDouble();
                return pw.Padding(
                  padding: const pw.EdgeInsets.symmetric(vertical: 2),
                  child: pw.Row(
                    crossAxisAlignment: pw.CrossAxisAlignment.start,
                    children: [
                      pw.Expanded(
                        flex: 5,
                        child: pw.Column(
                          crossAxisAlignment: pw.CrossAxisAlignment.start,
                          children: [
                            pw.Text(item['product_name'] ?? '', style: const pw.TextStyle(fontSize: 9)),
                            pw.Text('@ TZS ${_currencyFormat.format(unitPrice)}', style: const pw.TextStyle(fontSize: 7, color: PdfColors.grey700)),
                          ],
                        ),
                      ),
                      pw.Expanded(
                        flex: 2,
                        child: pw.Text('$qty', style: const pw.TextStyle(fontSize: 9), textAlign: pw.TextAlign.center),
                      ),
                      pw.Expanded(
                        flex: 3,
                        child: pw.Text('TZS ${_currencyFormat.format(subtotal)}', style: const pw.TextStyle(fontSize: 9), textAlign: pw.TextAlign.right),
                      ),
                    ],
                  ),
                );
              }),

              pw.SizedBox(height: 8),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 8),

              // Totals
              _buildTotalRow('Subtotal:', 'TZS ${_currencyFormat.format((transaction['subtotal'] ?? 0).toDouble())}'),
              if ((transaction['tax_amount'] ?? 0) > 0)
                _buildTotalRow('VAT:', 'TZS ${_currencyFormat.format((transaction['tax_amount'] ?? 0).toDouble())}'),
              if ((transaction['discount_amount'] ?? 0) > 0)
                _buildTotalRow('Discount:', '-TZS ${_currencyFormat.format((transaction['discount_amount'] ?? 0).toDouble())}'),
              pw.SizedBox(height: 4),
              _buildTotalRow('TOTAL:', 'TZS ${_currencyFormat.format((transaction['total'] ?? 0).toDouble())}', isBold: true),

              pw.SizedBox(height: 8),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 8),

              // Payment
              _buildInfoRow('Payment:', 'Cash'),
              _buildInfoRow('Amount Paid:', 'TZS ${_currencyFormat.format((transaction['amount_paid'] ?? 0).toDouble())}'),
              if ((transaction['change_given'] ?? 0) > 0)
                _buildInfoRow('Change:', 'TZS ${_currencyFormat.format((transaction['change_given'] ?? 0).toDouble())}'),

              pw.SizedBox(height: 8),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 16),

              // Footer
              pw.Text('Thank you for your purchase!', style: const pw.TextStyle(fontSize: 10)),
              pw.SizedBox(height: 4),
              pw.Text('Karibu tena / Welcome again', style: const pw.TextStyle(fontSize: 10)),
              pw.SizedBox(height: 12),

              // Receipt number box
              pw.Container(
                padding: const pw.EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                decoration: pw.BoxDecoration(
                  border: pw.Border.all(color: PdfColors.black),
                ),
                child: pw.Text(
                  transaction['transaction_number'] ?? '',
                  style: pw.TextStyle(fontSize: 10, fontWeight: pw.FontWeight.bold),
                ),
              ),
              pw.SizedBox(height: 8),
              pw.Text('Powered by Sasampa POS', style: const pw.TextStyle(fontSize: 8, color: PdfColors.grey700)),
              pw.Text('sasampa.com', style: const pw.TextStyle(fontSize: 8, color: PdfColors.grey700)),
            ],
          );
        },
      ),
    );

    return pdf.save();
  }

  /// Generate proforma invoice PDF — same roll80 size as receipts
  static Future<Uint8List> generateProformaInvoicePdf(Map<String, dynamic> proformaData) async {
    final pdf = pw.Document();

    final company = proformaData['company'] as Map<String, dynamic>? ?? {};
    final branch = proformaData['branch'] as Map<String, dynamic>?;
    final order = proformaData['order'] as Map<String, dynamic>? ?? {};
    final customer = proformaData['customer'] as Map<String, dynamic>? ?? {};
    final items = proformaData['items'] as List? ?? [];
    final totals = proformaData['totals'] as Map<String, dynamic>? ?? {};
    final notes = proformaData['notes'] as String?;

    // Try to load company logo
    pw.ImageProvider? logoImage;
    final logoUrl = company['logo'] as String?;
    if (logoUrl != null && logoUrl.isNotEmpty) {
      try {
        final response = await http.get(Uri.parse(logoUrl));
        if (response.statusCode == 200) {
          logoImage = pw.MemoryImage(response.bodyBytes);
        }
      } catch (_) {}
    }

    pdf.addPage(
      pw.Page(
        pageFormat: PdfPageFormat.roll80,
        build: (pw.Context context) {
          return pw.Column(
            mainAxisSize: pw.MainAxisSize.min,
            crossAxisAlignment: pw.CrossAxisAlignment.center,
            children: [
              // Company Logo
              if (logoImage != null)
                pw.Container(
                  width: 50,
                  height: 50,
                  child: pw.Image(logoImage, fit: pw.BoxFit.contain),
                ),
              if (logoImage != null) pw.SizedBox(height: 8),

              // Company Name
              pw.Text(
                company['name'] ?? 'SASAMPA POS',
                style: pw.TextStyle(fontSize: 14, fontWeight: pw.FontWeight.bold),
                textAlign: pw.TextAlign.center,
              ),

              // Company Address
              if (company['address'] != null)
                pw.Text(
                  company['address'],
                  style: const pw.TextStyle(fontSize: 8),
                  textAlign: pw.TextAlign.center,
                ),

              // Company Phone
              if (company['phone'] != null)
                pw.Text(
                  'Tel: ${company['phone']}',
                  style: const pw.TextStyle(fontSize: 8),
                  textAlign: pw.TextAlign.center,
                ),

              // Branch
              if (branch != null && branch['name'] != null)
                pw.Text(
                  'Tawi: ${branch['name']}',
                  style: const pw.TextStyle(fontSize: 8),
                  textAlign: pw.TextAlign.center,
                ),

              pw.SizedBox(height: 6),

              // Proforma badge
              pw.Container(
                padding: const pw.EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                decoration: pw.BoxDecoration(
                  border: pw.Border.all(color: PdfColors.black),
                ),
                child: pw.Text(
                  'PROFORMA / ANKARA',
                  style: pw.TextStyle(fontSize: 10, fontWeight: pw.FontWeight.bold),
                ),
              ),

              pw.SizedBox(height: 8),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 8),

              // Order Info
              _buildInfoRow('Nambari:', order['number'] ?? ''),
              _buildInfoRow('Tarehe:', order['date'] ?? ''),
              _buildInfoRow('Saa:', order['time'] ?? ''),
              if (order['cashier'] != null)
                _buildInfoRow('Muuzaji:', order['cashier']),
              if (order['valid_until'] != null)
                _buildInfoRow('Inaisha:', order['valid_until']),
              if (order['status'] != null)
                _buildInfoRow('Hali:', order['status'] == 'pending' ? 'Inasubiri' : order['status']),

              pw.SizedBox(height: 8),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 4),

              // Customer
              if (customer['name'] != null && (customer['name'] as String).isNotEmpty) ...[
                pw.Container(
                  width: double.infinity,
                  child: pw.Column(
                    crossAxisAlignment: pw.CrossAxisAlignment.start,
                    children: [
                      pw.Text('Mteja:', style: pw.TextStyle(fontSize: 8, fontWeight: pw.FontWeight.bold, color: PdfColors.grey700)),
                      pw.Text(customer['name'] ?? '', style: pw.TextStyle(fontSize: 10, fontWeight: pw.FontWeight.bold)),
                      if (customer['phone'] != null)
                        pw.Text('Tel: ${customer['phone']}', style: const pw.TextStyle(fontSize: 8)),
                      if (customer['tin'] != null)
                        pw.Text('TIN: ${customer['tin']}', style: const pw.TextStyle(fontSize: 8)),
                    ],
                  ),
                ),
                pw.SizedBox(height: 8),
                pw.Divider(thickness: 0.5),
                pw.SizedBox(height: 4),
              ],

              // Items header
              pw.Row(
                children: [
                  pw.Expanded(
                    flex: 5,
                    child: pw.Text('Bidhaa', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 9)),
                  ),
                  pw.Expanded(
                    flex: 2,
                    child: pw.Text('Idadi', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 9), textAlign: pw.TextAlign.center),
                  ),
                  pw.Expanded(
                    flex: 3,
                    child: pw.Text('Jumla', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 9), textAlign: pw.TextAlign.right),
                  ),
                ],
              ),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 4),

              // Items
              ...items.map((item) {
                final unitPrice = (item['unit_price'] ?? 0).toDouble();
                final qty = item['quantity'] ?? 0;
                final subtotal = (item['subtotal'] ?? 0).toDouble();
                return pw.Padding(
                  padding: const pw.EdgeInsets.symmetric(vertical: 2),
                  child: pw.Row(
                    crossAxisAlignment: pw.CrossAxisAlignment.start,
                    children: [
                      pw.Expanded(
                        flex: 5,
                        child: pw.Column(
                          crossAxisAlignment: pw.CrossAxisAlignment.start,
                          children: [
                            pw.Text(item['name'] ?? '', style: const pw.TextStyle(fontSize: 9)),
                            pw.Text('@ TZS ${_currencyFormat.format(unitPrice)}', style: const pw.TextStyle(fontSize: 7, color: PdfColors.grey700)),
                          ],
                        ),
                      ),
                      pw.Expanded(
                        flex: 2,
                        child: pw.Text('$qty', style: const pw.TextStyle(fontSize: 9), textAlign: pw.TextAlign.center),
                      ),
                      pw.Expanded(
                        flex: 3,
                        child: pw.Text('TZS ${_currencyFormat.format(subtotal)}', style: const pw.TextStyle(fontSize: 9), textAlign: pw.TextAlign.right),
                      ),
                    ],
                  ),
                );
              }),

              pw.SizedBox(height: 8),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 8),

              // Totals
              _buildTotalRow('Jumla Ndogo:', 'TZS ${_currencyFormat.format((totals['subtotal'] ?? 0).toDouble())}'),
              if ((totals['tax'] ?? 0) > 0)
                _buildTotalRow('Kodi (VAT):', 'TZS ${_currencyFormat.format((totals['tax'] ?? 0).toDouble())}'),
              if ((totals['discount'] ?? 0) > 0)
                _buildTotalRow('Punguzo:', '-TZS ${_currencyFormat.format((totals['discount'] ?? 0).toDouble())}'),
              pw.SizedBox(height: 4),
              _buildTotalRow('JUMLA:', 'TZS ${_currencyFormat.format((totals['total'] ?? 0).toDouble())}', isBold: true),

              // Notes
              if (notes != null && notes.isNotEmpty) ...[
                pw.SizedBox(height: 8),
                pw.Divider(thickness: 0.5),
                pw.SizedBox(height: 4),
                pw.Container(
                  width: double.infinity,
                  child: pw.Column(
                    crossAxisAlignment: pw.CrossAxisAlignment.start,
                    children: [
                      pw.Text('Maelezo:', style: pw.TextStyle(fontSize: 8, fontWeight: pw.FontWeight.bold)),
                      pw.Text(notes, style: const pw.TextStyle(fontSize: 8, color: PdfColors.grey700)),
                    ],
                  ),
                ),
              ],

              pw.SizedBox(height: 8),
              pw.Divider(thickness: 0.5),
              pw.SizedBox(height: 12),

              // Footer
              pw.Text(
                'Hii ni ankara ya bei (proforma)',
                style: pw.TextStyle(fontSize: 9, fontStyle: pw.FontStyle.italic),
                textAlign: pw.TextAlign.center,
              ),
              pw.Text(
                'si risiti - malipo yanahitajika',
                style: pw.TextStyle(fontSize: 9, fontStyle: pw.FontStyle.italic),
                textAlign: pw.TextAlign.center,
              ),
              pw.SizedBox(height: 8),

              // Order number box
              pw.Container(
                padding: const pw.EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                decoration: pw.BoxDecoration(
                  border: pw.Border.all(color: PdfColors.black),
                ),
                child: pw.Text(
                  order['number'] ?? '',
                  style: pw.TextStyle(fontSize: 10, fontWeight: pw.FontWeight.bold),
                ),
              ),
              pw.SizedBox(height: 8),
              pw.Text('Powered by Sasampa POS', style: const pw.TextStyle(fontSize: 8, color: PdfColors.grey700)),
              pw.Text('sasampa.com', style: const pw.TextStyle(fontSize: 8, color: PdfColors.grey700)),
            ],
          );
        },
      ),
    );

    return pdf.save();
  }

  /// Share proforma invoice as PDF
  static Future<void> shareProformaFromApi(Map<String, dynamic> proformaData) async {
    final pdfData = await generateProformaInvoicePdf(proformaData);
    final orderNumber = proformaData['order']?['number'] ?? 'proforma';

    await Printing.sharePdf(
      bytes: pdfData,
      filename: 'Proforma_$orderNumber.pdf',
    );
  }

  /// Print proforma invoice
  static Future<void> printProformaFromApi(Map<String, dynamic> proformaData) async {
    final pdfData = await generateProformaInvoicePdf(proformaData);
    final orderNumber = proformaData['order']?['number'] ?? 'proforma';

    await Printing.layoutPdf(
      onLayout: (PdfPageFormat format) async => pdfData,
      name: 'Proforma_$orderNumber',
    );
  }

  static pw.Widget _buildInfoRow(String label, String value) {
    return pw.Padding(
      padding: const pw.EdgeInsets.symmetric(vertical: 1),
      child: pw.Row(
        children: [
          pw.SizedBox(
            width: 70,
            child: pw.Text(label, style: const pw.TextStyle(fontSize: 9, color: PdfColors.grey700)),
          ),
          pw.Expanded(
            child: pw.Text(value, style: const pw.TextStyle(fontSize: 9), textAlign: pw.TextAlign.right),
          ),
        ],
      ),
    );
  }

  static pw.Widget _buildTotalRow(String label, String value, {bool isBold = false}) {
    return pw.Padding(
      padding: const pw.EdgeInsets.symmetric(vertical: 1),
      child: pw.Row(
        mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
        children: [
          pw.Text(label, style: pw.TextStyle(fontSize: isBold ? 11 : 9, fontWeight: isBold ? pw.FontWeight.bold : null)),
          pw.Text(value, style: pw.TextStyle(fontSize: isBold ? 11 : 9, fontWeight: isBold ? pw.FontWeight.bold : null)),
        ],
      ),
    );
  }

  /// Print receipt using system print dialog (from API data)
  static Future<void> printReceiptFromApi(Map<String, dynamic> receiptData) async {
    final pdfData = await generateReceiptPdfFromApi(receiptData);
    final txNumber = receiptData['transaction']?['number'] ?? 'receipt';

    await Printing.layoutPdf(
      onLayout: (PdfPageFormat format) async => pdfData,
      name: 'Receipt_$txNumber',
    );
  }

  /// Share receipt as PDF (from API data)
  static Future<void> shareReceiptFromApi(Map<String, dynamic> receiptData) async {
    final pdfData = await generateReceiptPdfFromApi(receiptData);
    final txNumber = receiptData['transaction']?['number'] ?? 'receipt';

    await Printing.sharePdf(
      bytes: pdfData,
      filename: 'Receipt_$txNumber.pdf',
    );
  }

  /// Print receipt using system print dialog (simple)
  static Future<void> printReceipt({
    required Map<String, dynamic> transaction,
    String? companyName,
    String? cashierName,
  }) async {
    final pdfData = await generateReceiptPdf(
      transaction: transaction,
      companyName: companyName,
      cashierName: cashierName,
    );

    await Printing.layoutPdf(
      onLayout: (PdfPageFormat format) async => pdfData,
      name: 'Receipt_${transaction['transaction_number'] ?? 'receipt'}',
    );
  }

  /// Share receipt as PDF (simple)
  static Future<void> shareReceipt({
    required Map<String, dynamic> transaction,
    String? companyName,
    String? cashierName,
  }) async {
    final pdfData = await generateReceiptPdf(
      transaction: transaction,
      companyName: companyName,
      cashierName: cashierName,
    );

    await Printing.sharePdf(
      bytes: pdfData,
      filename: 'Receipt_${transaction['transaction_number'] ?? 'receipt'}.pdf',
    );
  }

  /// Print receipt using the user's preferred method (AirPrint or Bluetooth)
  static Future<bool> printWithPreferredMethod({
    required Map<String, dynamic> receiptData,
    required WidgetRef ref,
    ApiClient? api,
    int? transactionId,
  }) async {
    final prefsState = ref.read(printerPrefsProvider);
    final prefs = prefsState.prefs;

    if (prefs.printerType == PrinterType.bluetooth) {
      final btService = ref.read(bluetoothPrinterServiceProvider);
      if (btService.isConnected) {
        return bluetoothPrintReceipt(
          receiptData: receiptData,
          btService: btService,
          paperSize: prefs.paperSize == 'mm58' ? PaperSize.mm58 : PaperSize.mm80,
        );
      }
      // Fall through to system-print if not connected
    }

    // System-print fallback — prefer server-rendered PDF (one design across platform).
    if (api != null && transactionId != null) {
      final txNumber = receiptData['transaction']?['number']?.toString() ?? 'receipt';
      await printServerReceiptPdf(api: api, transactionId: transactionId, txNumber: txNumber);
    } else {
      await printReceiptFromApi(receiptData);
    }
    return true;
  }

  /// Print receipt via Bluetooth thermal printer using ESC/POS commands
  static Future<bool> bluetoothPrintReceipt({
    required Map<String, dynamic> receiptData,
    required BluetoothPrinterService btService,
    PaperSize paperSize = PaperSize.mm80,
  }) async {
    // Extract transaction data from the API receipt format
    final transaction = receiptData['transaction'] as Map<String, dynamic>? ?? {};
    final company = receiptData['company'] as Map<String, dynamic>? ?? {};
    final customer = receiptData['customer'] as Map<String, dynamic>? ?? {};
    final items = receiptData['items'] as List? ?? [];
    final totals = receiptData['totals'] as Map<String, dynamic>? ?? {};
    final payment = receiptData['payment'] as Map<String, dynamic>? ?? {};

    final fiscal = receiptData['fiscal'] as Map<String, dynamic>?;

    // Build a flat transaction map for EscPosCommands
    final flatTransaction = <String, dynamic>{
      'transaction_number': transaction['number'] ?? '',
      'created_at': '${transaction['date'] ?? ''} ${transaction['time'] ?? ''}',
      'cashier': transaction['cashier'],
      'customer_name': customer['name'],
      'customer_tin': customer['tin'],
      'company_tin': company['tin'],
      'company_vrn': company['vrn'],
      'items': items.map((item) => {
        'product_name': item['name'] ?? item['product_name'] ?? '',
        'quantity': item['quantity'] ?? 0,
        'unit_price': item['unit_price'] ?? 0,
        'subtotal': item['subtotal'] ?? 0,
      }).toList(),
      'subtotal': totals['subtotal'] ?? 0,
      'tax_amount': totals['tax'] ?? 0,
      'discount_amount': totals['discount'] ?? 0,
      'total': totals['total'] ?? 0,
      'payment_method': payment['method'] ?? 'cash',
      'amount_paid': payment['amount_paid'] ?? 0,
      'change_given': payment['change'] ?? 0,
      if (fiscal != null) ...{
        'fiscal_receipt_number': fiscal['receipt_number'],
        'fiscal_verification_code': fiscal['verification_code'],
        'fiscal_qr_code': fiscal['qr_code'],
      },
    };

    final bytes = EscPosCommands.buildReceiptBytes(
      flatTransaction,
      paperSize: paperSize,
      companyName: company['name'] as String?,
      companyPhone: company['phone'] as String?,
      companyAddress: company['address'] as String?,
    );

    return btService.printBytes(bytes);
  }

  /// Print simple transaction receipt via Bluetooth (for checkout flow)
  static Future<bool> bluetoothPrintSimpleReceipt({
    required Map<String, dynamic> transaction,
    required BluetoothPrinterService btService,
    PaperSize paperSize = PaperSize.mm80,
    String? companyName,
    String? companyPhone,
    String? companyAddress,
  }) async {
    final bytes = EscPosCommands.buildReceiptBytes(
      transaction,
      paperSize: paperSize,
      companyName: companyName,
      companyPhone: companyPhone,
      companyAddress: companyAddress,
    );

    return btService.printBytes(bytes);
  }
}
