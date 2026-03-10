import 'dart:async';
import 'dart:typed_data';
import 'package:flutter/foundation.dart';
import 'package:flutter_blue_plus/flutter_blue_plus.dart';

class BluetoothPrinterService {
  BluetoothDevice? _connectedDevice;
  BluetoothCharacteristic? _writeCharacteristic;
  StreamSubscription? _connectionSubscription;

  bool get isConnected => _connectedDevice != null && _writeCharacteristic != null;
  BluetoothDevice? get connectedDevice => _connectedDevice;
  String? get connectedDeviceName => _connectedDevice?.platformName;

  /// Scan for nearby Bluetooth printers
  Stream<List<ScanResult>> scanForPrinters({Duration timeout = const Duration(seconds: 10)}) {
    FlutterBluePlus.startScan(timeout: timeout);
    return FlutterBluePlus.scanResults;
  }

  void stopScan() {
    FlutterBluePlus.stopScan();
  }

  /// Connect to a Bluetooth printer
  Future<bool> connectToPrinter(BluetoothDevice device) async {
    try {
      await device.connect(timeout: const Duration(seconds: 10));

      // Discover services
      final services = await device.discoverServices();

      // Find the write characteristic — thermal printers commonly use 0xFFE1
      for (final service in services) {
        for (final char in service.characteristics) {
          if (char.properties.write || char.properties.writeWithoutResponse) {
            // Prefer 0xFFE1 but accept any writable characteristic
            if (char.uuid.toString().toLowerCase().contains('ffe1') ||
                _writeCharacteristic == null) {
              _writeCharacteristic = char;
            }
          }
        }
      }

      if (_writeCharacteristic == null) {
        debugPrint('BT_PRINTER: No writable characteristic found');
        await device.disconnect();
        return false;
      }

      _connectedDevice = device;

      // Listen for disconnection
      _connectionSubscription?.cancel();
      _connectionSubscription = device.connectionState.listen((state) {
        if (state == BluetoothConnectionState.disconnected) {
          _connectedDevice = null;
          _writeCharacteristic = null;
          _connectionSubscription?.cancel();
        }
      });

      debugPrint('BT_PRINTER: Connected to ${device.platformName}');
      return true;
    } catch (e) {
      debugPrint('BT_PRINTER: Connection failed - $e');
      return false;
    }
  }

  /// Disconnect from the current printer
  Future<void> disconnectPrinter() async {
    _connectionSubscription?.cancel();
    if (_connectedDevice != null) {
      try {
        await _connectedDevice!.disconnect();
      } catch (_) {}
    }
    _connectedDevice = null;
    _writeCharacteristic = null;
  }

  /// Print raw bytes to the connected printer
  Future<bool> printBytes(Uint8List data) async {
    if (!isConnected) {
      debugPrint('BT_PRINTER: Not connected');
      return false;
    }

    try {
      // Send in chunks (BLE has MTU limits, typically 20-512 bytes)
      const chunkSize = 100;
      for (var i = 0; i < data.length; i += chunkSize) {
        final end = (i + chunkSize > data.length) ? data.length : i + chunkSize;
        final chunk = data.sublist(i, end);

        if (_writeCharacteristic!.properties.writeWithoutResponse) {
          await _writeCharacteristic!.write(chunk, withoutResponse: true);
        } else {
          await _writeCharacteristic!.write(chunk);
        }

        // Small delay between chunks to avoid buffer overflow
        await Future.delayed(const Duration(milliseconds: 20));
      }

      debugPrint('BT_PRINTER: Print complete (${data.length} bytes)');
      return true;
    } catch (e) {
      debugPrint('BT_PRINTER: Print failed - $e');
      return false;
    }
  }

  void dispose() {
    _connectionSubscription?.cancel();
    disconnectPrinter();
  }
}
