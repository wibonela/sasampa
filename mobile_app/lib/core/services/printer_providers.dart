import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../storage/secure_storage.dart';
import '../providers.dart';
import 'bluetooth_printer_service.dart';
import 'printer_preferences.dart';

// Printer Preferences State
class PrinterPrefsState {
  final PrinterPreferences prefs;
  final bool isLoaded;

  const PrinterPrefsState({
    this.prefs = const PrinterPreferences(),
    this.isLoaded = false,
  });

  PrinterPrefsState copyWith({
    PrinterPreferences? prefs,
    bool? isLoaded,
  }) {
    return PrinterPrefsState(
      prefs: prefs ?? this.prefs,
      isLoaded: isLoaded ?? this.isLoaded,
    );
  }
}

class PrinterPrefsNotifier extends StateNotifier<PrinterPrefsState> {
  final SecureStorage _storage;
  static const _key = 'printer_prefs';

  PrinterPrefsNotifier(this._storage) : super(const PrinterPrefsState());

  Future<void> loadPreferences() async {
    final json = await _storage.getString(_key);
    if (json != null && json.isNotEmpty) {
      try {
        final prefs = PrinterPreferences.deserialize(json);
        state = PrinterPrefsState(prefs: prefs, isLoaded: true);
      } catch (_) {
        state = const PrinterPrefsState(isLoaded: true);
      }
    } else {
      state = const PrinterPrefsState(isLoaded: true);
    }
  }

  Future<void> updatePreferences(PrinterPreferences prefs) async {
    state = state.copyWith(prefs: prefs);
    await _storage.saveString(_key, prefs.serialize());
  }

  Future<void> setPrinterType(PrinterType type) async {
    await updatePreferences(state.prefs.copyWith(printerType: type));
  }

  Future<void> setPaperSize(String size) async {
    await updatePreferences(state.prefs.copyWith(paperSize: size));
  }

  Future<void> setAutoPrint(bool value) async {
    await updatePreferences(state.prefs.copyWith(autoPrintAfterSale: value));
  }

  Future<void> savePrinter(String name, String address) async {
    await updatePreferences(state.prefs.copyWith(
      savedDeviceName: name,
      savedDeviceAddress: address,
    ));
  }

  Future<void> clearSavedPrinter() async {
    await updatePreferences(state.prefs.copyWith(clearSavedPrinter: true));
  }
}

final printerPrefsProvider =
    StateNotifierProvider<PrinterPrefsNotifier, PrinterPrefsState>((ref) {
  final storage = ref.watch(secureStorageProvider);
  return PrinterPrefsNotifier(storage);
});

final bluetoothPrinterServiceProvider =
    Provider<BluetoothPrinterService>((ref) {
  return BluetoothPrinterService();
});
