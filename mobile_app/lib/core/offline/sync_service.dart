import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:dio/dio.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:uuid/uuid.dart';

import '../network/api_client.dart';
import '../utils/error_utils.dart';
import 'offline_db.dart';

/// True when the failure means "no usable connection" (as opposed to the
/// server answering with an error).
bool isOfflineError(Object e) {
  if (e is! DioException) return false;
  if (e.response != null) return false;
  return e.type == DioExceptionType.connectionError ||
      e.type == DioExceptionType.connectionTimeout ||
      e.type == DioExceptionType.sendTimeout ||
      e.type == DioExceptionType.receiveTimeout ||
      e.type == DioExceptionType.unknown;
}

class SyncState {
  final int pending;
  final int failed;
  final bool syncing;
  final bool online;
  final List<PendingSale> failedSales;

  const SyncState({
    this.pending = 0,
    this.failed = 0,
    this.syncing = false,
    this.online = true,
    this.failedSales = const [],
  });

  SyncState copyWith({
    int? pending,
    int? failed,
    bool? syncing,
    bool? online,
    List<PendingSale>? failedSales,
  }) =>
      SyncState(
        pending: pending ?? this.pending,
        failed: failed ?? this.failed,
        syncing: syncing ?? this.syncing,
        online: online ?? this.online,
        failedSales: failedSales ?? this.failedSales,
      );
}

/// Keeps sales made without internet in a local queue and sends them, oldest
/// first, once the connection is back. Each sale carries a unique offline_id,
/// and the server ignores repeats, so retrying can never double-count a sale.
class SyncNotifier extends StateNotifier<SyncState> with WidgetsBindingObserver {
  final ApiClient _api;
  final int? Function() _currentUserId;
  StreamSubscription<List<ConnectivityResult>>? _connSub;
  Timer? _timer;
  bool _running = false;

  SyncNotifier(this._api, this._currentUserId) : super(const SyncState()) {
    WidgetsBinding.instance.addObserver(this);
    _connSub = Connectivity().onConnectivityChanged.listen((results) {
      final online = results.any((r) => r != ConnectivityResult.none);
      state = state.copyWith(online: online);
      if (online) sync();
    });
    _timer = Timer.periodic(const Duration(minutes: 1), (_) {
      if (state.pending > 0) sync();
    });
    refresh().then((_) => sync());
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState s) {
    if (s == AppLifecycleState.resumed) sync();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _connSub?.cancel();
    _timer?.cancel();
    super.dispose();
  }

  /// Only this device user's own queued sales are shown/sent.
  Future<List<PendingSale>> _mine({String? status}) async {
    final uid = _currentUserId();
    final all = await OfflineDb.instance.pendingSales(status: status);
    return all.where((s) => s.payload['_user_id'] == uid).toList();
  }

  Future<void> refresh() async {
    final pending = await _mine(status: 'pending');
    final failed = await _mine(status: 'failed');
    state = state.copyWith(pending: pending.length, failed: failed.length, failedSales: failed);
  }

  /// Queue a sale. Returns the offline_id.
  Future<String> enqueue(Map<String, dynamic> checkoutPayload,
      {required double total, String? offlineId}) async {
    offlineId ??= const Uuid().v4();
    final now = DateTime.now();
    await OfflineDb.instance.enqueueSale(
      offlineId,
      {...checkoutPayload, '_user_id': _currentUserId(), '_total': total},
      now,
    );
    await refresh();
    return offlineId;
  }

  Future<void> sync() async {
    if (_running) return;
    _running = true;
    state = state.copyWith(syncing: true);
    try {
      for (final sale in await _mine(status: 'pending')) {
        final body = Map<String, dynamic>.from(sale.payload)
          ..removeWhere((k, _) => k.startsWith('_'));
        try {
          await _api.checkoutRaw({
            ...body,
            'offline_id': sale.offlineId,
            'created_at': sale.createdAt.toUtc().toIso8601String(),
          });
          await OfflineDb.instance.deleteSale(sale.offlineId);
        } catch (e) {
          if (isOfflineError(e)) break; // still no connection; try again later
          final status = e is DioException ? e.response?.statusCode : null;
          if (status == 401) break; // logged out; keep the sale until they sign in
          // Server answered with an error: this sale will not succeed by retrying.
          await OfflineDb.instance.markAttempt(sale.offlineId,
              status: 'failed', error: extractErrorMessage(e, 'Could not be recorded'));
        }
      }
    } finally {
      _running = false;
      await refresh();
      state = state.copyWith(syncing: false);
    }
  }

  Future<void> retryFailed() async {
    await OfflineDb.instance.retryFailed();
    await refresh();
    await sync();
  }

  Future<void> discard(String offlineId) async {
    await OfflineDb.instance.deleteSale(offlineId);
    await refresh();
  }
}
