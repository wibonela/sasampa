import 'dart:typed_data';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import 'package:intl/intl.dart';
import 'package:http/http.dart' as http;

class ReceiptService {
  static final _currencyFormat = NumberFormat('#,###');

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
}
