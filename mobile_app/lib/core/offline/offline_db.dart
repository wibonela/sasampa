import 'dart:convert';

import 'package:path/path.dart' as p;
import 'package:sqflite/sqflite.dart';

/// Local store for offline use: a key/value cache (product catalog, last
/// dashboard) and the queue of sales made while there was no internet.
class OfflineDb {
  OfflineDb._();
  static final OfflineDb instance = OfflineDb._();

  Database? _db;

  Future<Database> get _database async {
    if (_db != null) return _db!;
    final dir = await getDatabasesPath();
    _db = await openDatabase(
      p.join(dir, 'sasampa_offline.db'),
      version: 1,
      onCreate: (db, _) async {
        await db.execute(
            'CREATE TABLE kv (key TEXT PRIMARY KEY, value TEXT NOT NULL, saved_at TEXT NOT NULL)');
        await db.execute('''CREATE TABLE pending_sales (
            offline_id TEXT PRIMARY KEY,
            payload TEXT NOT NULL,
            created_at TEXT NOT NULL,
            attempts INTEGER NOT NULL DEFAULT 0,
            status TEXT NOT NULL DEFAULT 'pending',
            last_error TEXT)''');
      },
    );
    return _db!;
  }

  // ── key/value cache ──────────────────────────────────────────────

  Future<void> putJson(String key, Object value) async {
    final db = await _database;
    await db.insert(
      'kv',
      {'key': key, 'value': jsonEncode(value), 'saved_at': DateTime.now().toIso8601String()},
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  /// Returns the decoded value and when it was saved, or null.
  Future<({dynamic value, DateTime savedAt})?> getJson(String key) async {
    final db = await _database;
    final rows = await db.query('kv', where: 'key = ?', whereArgs: [key], limit: 1);
    if (rows.isEmpty) return null;
    return (
      value: jsonDecode(rows.first['value'] as String),
      savedAt: DateTime.parse(rows.first['saved_at'] as String),
    );
  }

  Future<void> deleteKey(String key) async {
    final db = await _database;
    await db.delete('kv', where: 'key = ?', whereArgs: [key]);
  }

  // ── pending sales queue ──────────────────────────────────────────

  Future<void> enqueueSale(String offlineId, Map<String, dynamic> payload, DateTime createdAt) async {
    final db = await _database;
    await db.insert(
      'pending_sales',
      {
        'offline_id': offlineId,
        'payload': jsonEncode(payload),
        'created_at': createdAt.toIso8601String(),
      },
      conflictAlgorithm: ConflictAlgorithm.ignore,
    );
  }

  /// Oldest first. [status] null = all.
  Future<List<PendingSale>> pendingSales({String? status}) async {
    final db = await _database;
    final rows = await db.query(
      'pending_sales',
      where: status != null ? 'status = ?' : null,
      whereArgs: status != null ? [status] : null,
      orderBy: 'created_at ASC',
    );
    return rows.map(PendingSale.fromRow).toList();
  }

  Future<void> deleteSale(String offlineId) async {
    final db = await _database;
    await db.delete('pending_sales', where: 'offline_id = ?', whereArgs: [offlineId]);
  }

  Future<void> markAttempt(String offlineId, {required String status, String? error}) async {
    final db = await _database;
    await db.rawUpdate(
      'UPDATE pending_sales SET attempts = attempts + 1, status = ?, last_error = ? WHERE offline_id = ?',
      [status, error, offlineId],
    );
  }

  /// Put a failed sale back in the queue (after the owner fixed the cause).
  Future<void> retryFailed() async {
    final db = await _database;
    await db.update('pending_sales', {'status': 'pending', 'last_error': null},
        where: "status = 'failed'");
  }
}

class PendingSale {
  final String offlineId;
  final Map<String, dynamic> payload;
  final DateTime createdAt;
  final int attempts;
  final String status;
  final String? lastError;

  PendingSale({
    required this.offlineId,
    required this.payload,
    required this.createdAt,
    required this.attempts,
    required this.status,
    this.lastError,
  });

  factory PendingSale.fromRow(Map<String, Object?> r) => PendingSale(
        offlineId: r['offline_id'] as String,
        payload: jsonDecode(r['payload'] as String) as Map<String, dynamic>,
        createdAt: DateTime.parse(r['created_at'] as String),
        attempts: r['attempts'] as int,
        status: r['status'] as String,
        lastError: r['last_error'] as String?,
      );

  double get total => ((payload['_total'] ?? 0) as num).toDouble();
}
