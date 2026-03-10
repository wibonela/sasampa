import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';

class EfdSettingsScreen extends ConsumerStatefulWidget {
  const EfdSettingsScreen({super.key});

  @override
  ConsumerState<EfdSettingsScreen> createState() => _EfdSettingsScreenState();
}

class _EfdSettingsScreenState extends ConsumerState<EfdSettingsScreen> {
  final _tinController = TextEditingController();
  final _vrnController = TextEditingController();
  final _serialController = TextEditingController();
  String _environment = 'sandbox';
  bool _efdEnabled = false;
  String? _efdUin;
  String? _efdRegisteredAt;
  bool _isEfdReady = false;

  bool _isLoading = true;
  bool _isSaving = false;
  bool _isRegistering = false;
  bool _isTesting = false;
  bool _isRetrying = false;
  int _pendingCount = 0;

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  @override
  void dispose() {
    _tinController.dispose();
    _vrnController.dispose();
    _serialController.dispose();
    super.dispose();
  }

  Future<void> _loadSettings() async {
    try {
      final api = ref.read(apiClientProvider);
      final responses = await Future.wait([
        api.getEfdSettings(),
        api.getEfdPending(),
      ]);

      final data = responses[0].data['data'];
      final pendingData = responses[1].data['data'];

      if (mounted) {
        setState(() {
          _tinController.text = data['tin'] ?? '';
          _vrnController.text = data['vrn'] ?? '';
          _serialController.text = data['efd_serial_number'] ?? '';
          _environment = data['efd_environment'] ?? 'sandbox';
          _efdEnabled = data['efd_enabled'] ?? false;
          _efdUin = data['efd_uin'];
          _efdRegisteredAt = data['efd_registered_at'];
          _isEfdReady = data['is_efd_ready'] ?? false;
          _pendingCount = pendingData['pending_count'] ?? 0;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${AppLocalizations.of(context)!.failedToLoad}: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<void> _saveSettings() async {
    setState(() => _isSaving = true);
    try {
      final api = ref.read(apiClientProvider);
      await api.updateEfdSettings({
        'tin': _tinController.text.trim(),
        'vrn': _vrnController.text.trim(),
        'efd_serial_number': _serialController.text.trim(),
        'efd_environment': _environment,
        'efd_enabled': _efdEnabled,
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(AppLocalizations.of(context)!.save),
            backgroundColor: AppColors.success,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${AppLocalizations.of(context)!.failedToProcess}: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  Future<void> _registerDevice() async {
    setState(() => _isRegistering = true);
    try {
      final api = ref.read(apiClientProvider);
      final response = await api.registerEfd();
      final data = response.data;

      if (data['success'] == true) {
        setState(() {
          _efdUin = data['data']?['efd_uin'];
          _efdRegisteredAt = data['data']?['efd_registered_at'];
          _isEfdReady = _efdEnabled && _efdUin != null;
        });
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(data['message'] ?? 'Device registered'),
              backgroundColor: AppColors.success,
            ),
          );
        }
      } else {
        throw Exception(data['message'] ?? 'Registration failed');
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('$e'), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _isRegistering = false);
    }
  }

  Future<void> _testConnection() async {
    setState(() => _isTesting = true);
    try {
      final api = ref.read(apiClientProvider);
      final response = await api.testEfd();
      final data = response.data;

      if (mounted) {
        final success = data['success'] == true;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(data['message'] ?? (success ? 'Test passed' : 'Test failed')),
            backgroundColor: success ? AppColors.success : AppColors.error,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('$e'), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _isTesting = false);
    }
  }

  Future<void> _retryPending() async {
    setState(() => _isRetrying = true);
    try {
      final api = ref.read(apiClientProvider);
      final response = await api.retryEfd();
      final data = response.data;

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(data['message'] ?? 'Retry complete'),
            backgroundColor: AppColors.success,
          ),
        );
        // Refresh pending count
        _loadSettings();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('$e'), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _isRetrying = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: Text(l10n.efdSettings),
        centerTitle: true,
        actions: [
          TextButton(
            onPressed: _isSaving ? null : _saveSettings,
            child: _isSaving
                ? const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : Text(l10n.save),
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                // Status Badge
                _buildStatusCard(),

                const SizedBox(height: 24),

                // TRA Registration Section
                Text(
                  l10n.traRegistration,
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
                  child: Column(
                    children: [
                      TextField(
                        controller: _tinController,
                        decoration: InputDecoration(
                          labelText: l10n.tinNumber,
                          hintText: 'e.g. 100-100-100',
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: _vrnController,
                        decoration: InputDecoration(
                          labelText: l10n.vrnNumber,
                          hintText: 'e.g. 10-000000-A',
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: _serialController,
                        decoration: InputDecoration(
                          labelText: l10n.efdSerialNumber,
                          hintText: 'e.g. 10TZ100100',
                        ),
                      ),
                      if (_efdUin != null) ...[
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Text(
                              'UIN: ',
                              style: TextStyle(
                                color: AppColors.textSecondary,
                                fontSize: 13,
                              ),
                            ),
                            Expanded(
                              child: Text(
                                _efdUin!,
                                style: const TextStyle(
                                  fontWeight: FontWeight.w600,
                                  fontSize: 13,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),

                const SizedBox(height: 24),

                // Connection Section
                Text(
                  l10n.testConnection,
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
                  child: Column(
                    children: [
                      // Environment Selector
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              l10n.efdEnvironment,
                              style: const TextStyle(fontWeight: FontWeight.w500),
                            ),
                          ),
                          SegmentedButton<String>(
                            segments: [
                              ButtonSegment(value: 'stub', label: Text('Stub')),
                              ButtonSegment(value: 'sandbox', label: Text(l10n.sandbox)),
                              ButtonSegment(value: 'production', label: Text(l10n.production)),
                            ],
                            selected: {_environment},
                            onSelectionChanged: (value) {
                              setState(() => _environment = value.first);
                            },
                            style: ButtonStyle(
                              textStyle: WidgetStatePropertyAll(
                                const TextStyle(fontSize: 12),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      // Enable Toggle
                      SwitchListTile(
                        title: Text(l10n.efdEnabled),
                        subtitle: Text(
                          _efdEnabled ? l10n.efdEnabled : l10n.efdDisabled,
                          style: TextStyle(
                            color: _efdEnabled ? AppColors.success : AppColors.textSecondary,
                            fontSize: 12,
                          ),
                        ),
                        value: _efdEnabled,
                        onChanged: (value) => setState(() => _efdEnabled = value),
                        contentPadding: EdgeInsets.zero,
                      ),
                      const Divider(),
                      const SizedBox(height: 8),
                      // Action Buttons
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton.icon(
                              onPressed: _isRegistering ? null : _registerDevice,
                              icon: _isRegistering
                                  ? const SizedBox(
                                      width: 16,
                                      height: 16,
                                      child: CircularProgressIndicator(strokeWidth: 2),
                                    )
                                  : const Icon(Icons.app_registration, size: 18),
                              label: Text(l10n.registerDevice),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: OutlinedButton.icon(
                              onPressed: _isTesting ? null : _testConnection,
                              icon: _isTesting
                                  ? const SizedBox(
                                      width: 16,
                                      height: 16,
                                      child: CircularProgressIndicator(strokeWidth: 2),
                                    )
                                  : const Icon(Icons.send, size: 18),
                              label: Text(l10n.testConnection),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 24),

                // Pending Submissions Section
                Text(
                  l10n.pendingSubmissions,
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
                  child: Column(
                    children: [
                      Row(
                        children: [
                          Icon(
                            _pendingCount > 0
                                ? Icons.warning_amber_rounded
                                : Icons.check_circle_outline,
                            color: _pendingCount > 0 ? AppColors.warning : AppColors.success,
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Text(
                              _pendingCount > 0
                                  ? '$_pendingCount ${l10n.pendingSubmissions.toLowerCase()}'
                                  : l10n.efdAllSubmitted,
                              style: const TextStyle(fontWeight: FontWeight.w500),
                            ),
                          ),
                        ],
                      ),
                      if (_pendingCount > 0) ...[
                        const SizedBox(height: 12),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton.icon(
                            onPressed: _isRetrying ? null : _retryPending,
                            icon: _isRetrying
                                ? const SizedBox(
                                    width: 16,
                                    height: 16,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      color: Colors.white,
                                    ),
                                  )
                                : const Icon(Icons.refresh, size: 18),
                            label: Text(l10n.retryFailed),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),

                const SizedBox(height: 32),
              ],
            ),
    );
  }

  Widget _buildStatusCard() {
    final l10n = AppLocalizations.of(context)!;
    final isRegistered = _efdUin != null && _efdUin!.isNotEmpty;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: _isEfdReady
            ? AppColors.success.withValues(alpha: 0.1)
            : isRegistered
                ? AppColors.warning.withValues(alpha: 0.1)
                : AppColors.gray1.withValues(alpha: 0.3),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: _isEfdReady
              ? AppColors.success.withValues(alpha: 0.3)
              : isRegistered
                  ? AppColors.warning.withValues(alpha: 0.3)
                  : AppColors.gray3.withValues(alpha: 0.3),
        ),
      ),
      child: Row(
        children: [
          Icon(
            _isEfdReady
                ? Icons.verified
                : isRegistered
                    ? Icons.pending
                    : Icons.info_outline,
            color: _isEfdReady
                ? AppColors.success
                : isRegistered
                    ? AppColors.warning
                    : AppColors.textSecondary,
            size: 32,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  _isEfdReady
                      ? l10n.efdRegistered
                      : isRegistered
                          ? l10n.efdEnabled
                          : l10n.efdNotRegistered,
                  style: const TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 15,
                  ),
                ),
                if (_efdRegisteredAt != null)
                  Text(
                    _efdRegisteredAt!,
                    style: const TextStyle(
                      fontSize: 12,
                      color: AppColors.textSecondary,
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
