import 'dart:convert';

enum PrinterType { airprint, bluetooth }

class PrinterPreferences {
  final PrinterType printerType;
  final String? savedDeviceName;
  final String? savedDeviceAddress;
  final String paperSize; // 'mm58' or 'mm80'
  final bool autoPrintAfterSale;

  const PrinterPreferences({
    this.printerType = PrinterType.airprint,
    this.savedDeviceName,
    this.savedDeviceAddress,
    this.paperSize = 'mm80',
    this.autoPrintAfterSale = false,
  });

  bool get hasSavedPrinter =>
      savedDeviceName != null && savedDeviceAddress != null;

  PrinterPreferences copyWith({
    PrinterType? printerType,
    String? savedDeviceName,
    String? savedDeviceAddress,
    String? paperSize,
    bool? autoPrintAfterSale,
    bool clearSavedPrinter = false,
  }) {
    return PrinterPreferences(
      printerType: printerType ?? this.printerType,
      savedDeviceName:
          clearSavedPrinter ? null : (savedDeviceName ?? this.savedDeviceName),
      savedDeviceAddress: clearSavedPrinter
          ? null
          : (savedDeviceAddress ?? this.savedDeviceAddress),
      paperSize: paperSize ?? this.paperSize,
      autoPrintAfterSale: autoPrintAfterSale ?? this.autoPrintAfterSale,
    );
  }

  Map<String, dynamic> toJson() => {
        'printerType': printerType.index,
        'savedDeviceName': savedDeviceName,
        'savedDeviceAddress': savedDeviceAddress,
        'paperSize': paperSize,
        'autoPrintAfterSale': autoPrintAfterSale,
      };

  factory PrinterPreferences.fromJson(Map<String, dynamic> json) {
    return PrinterPreferences(
      printerType:
          PrinterType.values[json['printerType'] as int? ?? 0],
      savedDeviceName: json['savedDeviceName'] as String?,
      savedDeviceAddress: json['savedDeviceAddress'] as String?,
      paperSize: json['paperSize'] as String? ?? 'mm80',
      autoPrintAfterSale: json['autoPrintAfterSale'] as bool? ?? false,
    );
  }

  String serialize() => jsonEncode(toJson());

  factory PrinterPreferences.deserialize(String json) {
    return PrinterPreferences.fromJson(
        jsonDecode(json) as Map<String, dynamic>);
  }
}
