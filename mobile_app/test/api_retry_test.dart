// Run with: flutter test test/api_retry_test.dart --dart-define=API_BASE_URL=http://127.0.0.1:8765/api/v1
import 'dart:convert';
import 'dart:io';

import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sasampa_pos/core/network/api_client.dart';
import 'package:sasampa_pos/core/storage/secure_storage.dart';

const _enabled = String.fromEnvironment('API_BASE_URL') == 'http://127.0.0.1:8765/api/v1';

void main() {
  if (!_enabled) return; // needs the define above so it never talks to production
  late HttpServer server;
  var hits = 0;
  var dropFirst = 0;

  setUp(() async {
    SharedPreferences.setMockInitialValues({});
    hits = 0;
    server = await HttpServer.bind('127.0.0.1', 8765);
    server.listen((req) async {
      hits++;
      if (hits <= dropFirst) {
        final socket = await req.response.detachSocket(writeHeaders: false);
        socket.destroy(); // abrupt close, like "connection reset by peer"
        return;
      }
      req.response
        ..headers.contentType = ContentType.json
        ..write(jsonEncode({'data': {'ok': true}}));
      await req.response.close();
    });
  });

  tearDown(() => server.close(force: true));

  test('GET succeeds after two dropped connections', () async {
    dropFirst = 2;
    final res = await ApiClient(SecureStorage()).getSettings();
    expect(res.data['data']['ok'], true);
    expect(hits, 3);
  });

  test('GET gives up after 3 attempts total', () async {
    dropFirst = 10;
    await expectLater(ApiClient(SecureStorage()).getSettings(), throwsA(isA<DioException>()));
    expect(hits, 3);
  });

  test('POST is never retried', () async {
    dropFirst = 10;
    await expectLater(
      ApiClient(SecureStorage()).updateSettings({'store_name': 'x'}),
      throwsA(isA<DioException>()),
    );
    expect(hits, 1);
  });
}
