import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_blue_plus/flutter_blue_plus.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/services/escpos_commands.dart';
import '../../../core/services/printer_preferences.dart';
import '../../../core/services/printer_providers.dart';

class PrinterSetupScreen extends ConsumerStatefulWidget {
  const PrinterSetupScreen({super.key});

  @override
  ConsumerState<PrinterSetupScreen> createState() => _PrinterSetupScreenState();
}

class _PrinterSetupScreenState extends ConsumerState<PrinterSetupScreen> {
  bool _isScanning = false;
  bool _isConnecting = false;
  bool _isPrinting = false;
  List<ScanResult> _scanResults = [];
  StreamSubscription<List<ScanResult>>? _scanSubscription;

  @override
  void initState() {
    super.initState();
    final prefsState = ref.read(printerPrefsProvider);
    if (!prefsState.isLoaded) {
      ref.read(printerPrefsProvider.notifier).loadPreferences();
    }
  }

  @override
  void dispose() {
    _scanSubscription?.cancel();
    ref.read(bluetoothPrinterServiceProvider).stopScan();
    super.dispose();
  }

  Future<void> _startScan() async {
    setState(() {
      _isScanning = true;
      _scanResults = [];
    });

    final btService = ref.read(bluetoothPrinterServiceProvider);
    _scanSubscription?.cancel();
    _scanSubscription = btService.scanForPrinters().listen((results) {
      if (mounted) {
        setState(() {
          // Filter to show only devices with names (likely printers)
          _scanResults = results
              .where((r) => r.device.platformName.isNotEmpty)
              .toList();
        });
      }
    });

    // Stop after timeout
    await Future.delayed(const Duration(seconds: 10));
    if (mounted) {
      setState(() => _isScanning = false);
    }
  }

  Future<void> _connectToDevice(BluetoothDevice device) async {
    setState(() => _isConnecting = true);

    final btService = ref.read(bluetoothPrinterServiceProvider);
    final success = await btService.connectToPrinter(device);

    if (success) {
      // Save printer info
      await ref.read(printerPrefsProvider.notifier).savePrinter(
            device.platformName,
            device.remoteId.str,
          );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${AppLocalizations.of(context)!.connected}: ${device.platformName}'),
            backgroundColor: AppColors.success,
          ),
        );
      }
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(AppLocalizations.of(context)!.connectionFailed),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }

    if (mounted) {
      setState(() => _isConnecting = false);
    }
  }

  Future<void> _disconnectPrinter() async {
    final btService = ref.read(bluetoothPrinterServiceProvider);
    await btService.disconnectPrinter();
    if (mounted) {
      setState(() {});
    }
  }

  Future<void> _testPrint() async {
    final btService = ref.read(bluetoothPrinterServiceProvider);
    final prefs = ref.read(printerPrefsProvider).prefs;

    if (!btService.isConnected) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(AppLocalizations.of(context)!.printerNotConnected),
            backgroundColor: AppColors.error,
          ),
        );
      }
      return;
    }

    setState(() => _isPrinting = true);

    final paperSize =
        prefs.paperSize == 'mm58' ? PaperSize.mm58 : PaperSize.mm80;

    final testTransaction = <String, dynamic>{
      'transaction_number': 'TEST-001',
      'created_at': DateTime.now().toIso8601String(),
      'items': [
        {
          'product_name': 'Test Item 1',
          'quantity': 2,
          'unit_price': 5000,
          'subtotal': 10000,
        },
        {
          'product_name': 'Test Item 2',
          'quantity': 1,
          'unit_price': 15000,
          'subtotal': 15000,
        },
      ],
      'subtotal': 25000,
      'tax_amount': 0,
      'discount_amount': 0,
      'total': 25000,
      'payment_method': 'cash',
      'amount_paid': 30000,
      'change_given': 5000,
    };

    final bytes = EscPosCommands.buildReceiptBytes(
      testTransaction,
      paperSize: paperSize,
      companyName: 'SASAMPA POS',
    );

    final success = await btService.printBytes(bytes);

    if (mounted) {
      setState(() => _isPrinting = false);
      if (!success) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(AppLocalizations.of(context)!.failedToPrint),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final prefsState = ref.watch(printerPrefsProvider);
    final prefs = prefsState.prefs;
    final btService = ref.watch(bluetoothPrinterServiceProvider);

    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: Text(l10n.printerSetup),
        centerTitle: true,
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Printer Type Selection
          Text(
            l10n.printerType,
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: AppColors.textSecondary,
            ),
          ),
          const SizedBox(height: 8),
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              children: [
                RadioListTile<PrinterType>(
                  title: Text(l10n.airprint),
                  subtitle: Text(l10n.printToAnyPrinter),
                  value: PrinterType.airprint,
                  groupValue: prefs.printerType,
                  onChanged: (value) {
                    if (value != null) {
                      ref.read(printerPrefsProvider.notifier).setPrinterType(value);
                    }
                  },
                  activeColor: AppColors.primary,
                ),
                const Divider(height: 1, indent: 16, endIndent: 16),
                RadioListTile<PrinterType>(
                  title: Text(l10n.bluetoothPrinter),
                  subtitle: Text(l10n.scanForPrinters),
                  value: PrinterType.bluetooth,
                  groupValue: prefs.printerType,
                  onChanged: (value) {
                    if (value != null) {
                      ref.read(printerPrefsProvider.notifier).setPrinterType(value);
                    }
                  },
                  activeColor: AppColors.primary,
                ),
              ],
            ),
          ),

          // Bluetooth-specific settings
          if (prefs.printerType == PrinterType.bluetooth) ...[
            const SizedBox(height: 24),

            // Paper Size
            Text(
              l10n.paperSize,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
            ),
            const SizedBox(height: 8),
            Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                children: [
                  RadioListTile<String>(
                    title: Text(l10n.mm58),
                    subtitle: const Text('32 chars/line'),
                    value: 'mm58',
                    groupValue: prefs.paperSize,
                    onChanged: (value) {
                      if (value != null) {
                        ref.read(printerPrefsProvider.notifier).setPaperSize(value);
                      }
                    },
                    activeColor: AppColors.primary,
                  ),
                  const Divider(height: 1, indent: 16, endIndent: 16),
                  RadioListTile<String>(
                    title: Text(l10n.mm80),
                    subtitle: const Text('48 chars/line'),
                    value: 'mm80',
                    groupValue: prefs.paperSize,
                    onChanged: (value) {
                      if (value != null) {
                        ref.read(printerPrefsProvider.notifier).setPaperSize(value);
                      }
                    },
                    activeColor: AppColors.primary,
                  ),
                ],
              ),
            ),

            const SizedBox(height: 24),

            // Connected / Saved Printer
            Text(
              btService.isConnected ? l10n.connected : l10n.savedPrinter,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
            ),
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
              ),
              child: btService.isConnected
                  ? Row(
                      children: [
                        Container(
                          width: 40,
                          height: 40,
                          decoration: BoxDecoration(
                            color: AppColors.success.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.bluetooth_connected,
                              color: AppColors.success, size: 20),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                btService.connectedDeviceName ?? l10n.bluetoothPrinter,
                                style: const TextStyle(fontWeight: FontWeight.w500),
                              ),
                              Text(
                                l10n.connected,
                                style: const TextStyle(
                                  fontSize: 13,
                                  color: AppColors.success,
                                ),
                              ),
                            ],
                          ),
                        ),
                        TextButton(
                          onPressed: _disconnectPrinter,
                          child: Text(
                            l10n.disconnectPrinter,
                            style: const TextStyle(color: AppColors.error),
                          ),
                        ),
                      ],
                    )
                  : prefs.hasSavedPrinter
                      ? Row(
                          children: [
                            Container(
                              width: 40,
                              height: 40,
                              decoration: BoxDecoration(
                                color: AppColors.primary.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Icon(Icons.bluetooth,
                                  color: AppColors.primary, size: 20),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    prefs.savedDeviceName!,
                                    style: const TextStyle(fontWeight: FontWeight.w500),
                                  ),
                                  Text(
                                    l10n.disconnected,
                                    style: const TextStyle(
                                      fontSize: 13,
                                      color: AppColors.textSecondary,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            TextButton(
                              onPressed: () {
                                ref.read(printerPrefsProvider.notifier).clearSavedPrinter();
                              },
                              child: Text(l10n.changePrinter),
                            ),
                          ],
                        )
                      : Column(
                          children: [
                            const Icon(Icons.bluetooth_disabled,
                                size: 48, color: AppColors.textSecondary),
                            const SizedBox(height: 8),
                            Text(
                              l10n.noDevicesFound,
                              style: const TextStyle(color: AppColors.textSecondary),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              l10n.scanForPrinters,
                              style: const TextStyle(
                                fontSize: 13,
                                color: AppColors.textSecondary,
                              ),
                            ),
                          ],
                        ),
            ),

            const SizedBox(height: 16),

            // Scan Button
            SizedBox(
              height: 48,
              child: ElevatedButton.icon(
                onPressed: _isScanning || _isConnecting ? null : _startScan,
                icon: _isScanning
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation(Colors.white),
                        ),
                      )
                    : const Icon(Icons.bluetooth_searching),
                label: Text(_isScanning ? l10n.scanning : l10n.scanForPrinters),
              ),
            ),

            // Scan Results
            if (_scanResults.isNotEmpty) ...[
              const SizedBox(height: 16),
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Column(
                  children: _scanResults.map((result) {
                    final device = result.device;
                    final isCurrentlyConnected = btService.isConnected &&
                        btService.connectedDevice?.remoteId == device.remoteId;

                    return ListTile(
                      leading: Icon(
                        isCurrentlyConnected
                            ? Icons.bluetooth_connected
                            : Icons.bluetooth,
                        color: isCurrentlyConnected
                            ? AppColors.success
                            : AppColors.primary,
                      ),
                      title: Text(
                        device.platformName.isNotEmpty
                            ? device.platformName
                            : 'Unknown',
                        style: const TextStyle(fontWeight: FontWeight.w500),
                      ),
                      subtitle: Text(
                        device.remoteId.str,
                        style: const TextStyle(fontSize: 12),
                      ),
                      trailing: isCurrentlyConnected
                          ? Chip(
                              label: Text(
                                l10n.connected,
                                style: const TextStyle(
                                  fontSize: 12,
                                  color: AppColors.success,
                                ),
                              ),
                              backgroundColor: AppColors.success.withOpacity(0.1),
                              side: BorderSide.none,
                            )
                          : _isConnecting
                              ? const SizedBox(
                                  width: 20,
                                  height: 20,
                                  child: CircularProgressIndicator(strokeWidth: 2),
                                )
                              : TextButton(
                                  onPressed: () => _connectToDevice(device),
                                  child: Text(l10n.connectPrinter),
                                ),
                    );
                  }).toList(),
                ),
              ),
            ],

            // Test Print Button
            if (btService.isConnected) ...[
              const SizedBox(height: 16),
              SizedBox(
                height: 48,
                child: OutlinedButton.icon(
                  onPressed: _isPrinting ? null : _testPrint,
                  icon: _isPrinting
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Icon(Icons.print),
                  label: Text(l10n.testPrint),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.primary,
                    side: const BorderSide(color: AppColors.primary),
                  ),
                ),
              ),
            ],
          ],

          const SizedBox(height: 24),

          // Auto-print toggle
          Text(
            l10n.settings,
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: AppColors.textSecondary,
            ),
          ),
          const SizedBox(height: 8),
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
            ),
            child: SwitchListTile(
              title: Text(l10n.autoPrintAfterSale),
              value: prefs.autoPrintAfterSale,
              onChanged: (value) {
                ref.read(printerPrefsProvider.notifier).setAutoPrint(value);
              },
              activeColor: AppColors.primary,
            ),
          ),

          const SizedBox(height: 32),
        ],
      ),
    );
  }
}
