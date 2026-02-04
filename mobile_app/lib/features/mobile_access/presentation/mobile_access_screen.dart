import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:device_info_plus/device_info_plus.dart';
import 'dart:io';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';
import '../../../shared/models/user.dart';

class MobileAccessScreen extends ConsumerStatefulWidget {
  const MobileAccessScreen({super.key});

  @override
  ConsumerState<MobileAccessScreen> createState() => _MobileAccessScreenState();
}

class _MobileAccessScreenState extends ConsumerState<MobileAccessScreen> {
  final _reasonController = TextEditingController();
  final _devicesController = TextEditingController(text: '3');
  bool _isLoading = false;
  bool _isRegistering = false;
  String? _error;

  @override
  void dispose() {
    _reasonController.dispose();
    _devicesController.dispose();
    super.dispose();
  }

  Future<void> _requestAccess() async {
    if (_reasonController.text.trim().isEmpty) {
      setState(() => _error = 'Please provide a reason for requesting mobile access');
      return;
    }

    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final api = ref.read(apiClientProvider);
      final response = await api.requestMobileAccess(
        _reasonController.text.trim(),
        int.tryParse(_devicesController.text) ?? 3,
      );

      // Refresh user data
      await ref.read(authProvider.notifier).refreshUser();

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Mobile access request submitted successfully'),
            backgroundColor: AppColors.success,
          ),
        );
      }
    } catch (e) {
      setState(() => _error = 'Failed to submit request. Please try again.');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _registerDevice() async {
    setState(() {
      _isRegistering = true;
      _error = null;
    });

    try {
      final api = ref.read(apiClientProvider);
      final storage = ref.read(secureStorageProvider);

      final deviceId = await storage.getOrCreateDeviceId();
      final deviceInfo = DeviceInfoPlugin();

      String deviceName = 'Unknown Device';
      String deviceModel = '';
      String osVersion = '';

      if (Platform.isIOS) {
        final info = await deviceInfo.iosInfo;
        deviceName = info.name;
        deviceModel = info.model;
        osVersion = 'iOS ${info.systemVersion}';
      } else if (Platform.isAndroid) {
        final info = await deviceInfo.androidInfo;
        deviceName = '${info.brand} ${info.model}';
        deviceModel = info.model;
        osVersion = 'Android ${info.version.release}';
      }

      await api.registerDevice(
        deviceIdentifier: deviceId,
        deviceName: deviceName,
        deviceModel: deviceModel,
        osVersion: osVersion,
        appVersion: '1.0.0',
      );

      // Refresh user data
      await ref.read(authProvider.notifier).refreshUser();

      if (mounted) {
        context.go('/');
      }
    } catch (e) {
      setState(() => _error = 'Failed to register device. Please try again.');
    } finally {
      setState(() => _isRegistering = false);
    }
  }

  Future<void> _checkStatus() async {
    setState(() => _isLoading = true);
    await ref.read(authProvider.notifier).refreshUser();
    setState(() => _isLoading = false);
  }

  Future<void> _logout() async {
    await ref.read(authProvider.notifier).logout();
    if (mounted) {
      context.go('/login');
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final mobileAccess = authState.mobileAccess;
    final user = authState.user;
    final isOwner = user?.isCompanyOwner ?? false;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 24),

              // Status Icon
              Center(
                child: Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    color: _getStatusColor(mobileAccess).withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    _getStatusIcon(mobileAccess),
                    size: 40,
                    color: _getStatusColor(mobileAccess),
                  ),
                ),
              ),

              const SizedBox(height: 24),

              // Title
              Text(
                _getStatusTitle(mobileAccess),
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: AppColors.textPrimary,
                ),
              ),

              const SizedBox(height: 8),

              Text(
                _getStatusSubtitle(mobileAccess, isOwner),
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 16,
                  color: AppColors.textSecondary,
                ),
              ),

              const SizedBox(height: 32),

              // Content based on status
              if (mobileAccess?.isApproved == true) ...[
                // Device Registration
                _buildInfoCard(
                  icon: Icons.phone_android,
                  title: 'Register This Device',
                  subtitle: 'To use the POS on this device, you need to register it first.',
                ),
                const SizedBox(height: 24),
                SizedBox(
                  height: 50,
                  child: ElevatedButton(
                    onPressed: _isRegistering ? null : _registerDevice,
                    child: _isRegistering
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation(Colors.white),
                            ),
                          )
                        : const Text('Register Device'),
                  ),
                ),
              ] else if (mobileAccess?.isPending == true) ...[
                // Pending
                _buildInfoCard(
                  icon: Icons.hourglass_empty,
                  title: 'Request Pending',
                  subtitle: 'Your request is being reviewed by the administrator. Please check back later.',
                ),
                const SizedBox(height: 24),
                SizedBox(
                  height: 50,
                  child: OutlinedButton(
                    onPressed: _isLoading ? null : _checkStatus,
                    child: _isLoading
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Text('Check Status'),
                  ),
                ),
              ] else if (mobileAccess?.isRejected == true) ...[
                // Rejected
                _buildInfoCard(
                  icon: Icons.cancel_outlined,
                  title: 'Request Rejected',
                  subtitle: mobileAccess?.rejectionReason ?? 'Your request was rejected. Please contact support.',
                  isError: true,
                ),
                if (isOwner) ...[
                  const SizedBox(height: 24),
                  const Text(
                    'You can submit a new request:',
                    style: TextStyle(color: AppColors.textSecondary),
                  ),
                  const SizedBox(height: 16),
                  _buildRequestForm(),
                ],
              ] else if (mobileAccess?.isRevoked == true) ...[
                // Revoked
                _buildInfoCard(
                  icon: Icons.block,
                  title: 'Access Revoked',
                  subtitle: mobileAccess?.revocationReason ?? 'Your mobile access has been revoked. Please contact support.',
                  isError: true,
                ),
              ] else if (isOwner) ...[
                // No request - Owner can request
                _buildInfoCard(
                  icon: Icons.smartphone,
                  title: 'Request Mobile Access',
                  subtitle: 'Submit a request to use the mobile POS app for your business.',
                ),
                const SizedBox(height: 24),
                _buildRequestForm(),
              ] else ...[
                // No request - Not owner
                _buildInfoCard(
                  icon: Icons.info_outline,
                  title: 'Mobile Access Required',
                  subtitle: 'Please ask your company owner to request mobile access.',
                ),
              ],

              // Error message
              if (_error != null) ...[
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppColors.error.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    _error!,
                    style: const TextStyle(color: AppColors.error, fontSize: 14),
                  ),
                ),
              ],

              const SizedBox(height: 32),

              // Logout
              TextButton(
                onPressed: _logout,
                child: const Text('Sign Out'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoCard({
    required IconData icon,
    required String title,
    required String subtitle,
    bool isError = false,
  }) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: isError ? AppColors.error.withOpacity(0.05) : AppColors.gray6,
        borderRadius: BorderRadius.circular(12),
        border: isError ? Border.all(color: AppColors.error.withOpacity(0.2)) : null,
      ),
      child: Column(
        children: [
          Icon(
            icon,
            size: 32,
            color: isError ? AppColors.error : AppColors.primary,
          ),
          const SizedBox(height: 12),
          Text(
            title,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w600,
              color: isError ? AppColors.error : AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 14,
              color: isError ? AppColors.error.withOpacity(0.8) : AppColors.textSecondary,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRequestForm() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // Reason field with border
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.gray4),
          ),
          child: TextFormField(
            controller: _reasonController,
            maxLines: 3,
            decoration: const InputDecoration(
              labelText: 'Reason for Request',
              hintText: 'Explain why you need mobile access...',
              alignLabelWithHint: true,
              border: InputBorder.none,
              contentPadding: EdgeInsets.all(16),
            ),
          ),
        ),
        const SizedBox(height: 20),
        // Devices field with border
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.gray4),
          ),
          child: TextFormField(
            controller: _devicesController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Expected Number of Devices',
              hintText: 'How many devices will use the app?',
              border: InputBorder.none,
              contentPadding: EdgeInsets.all(16),
            ),
          ),
        ),
        const SizedBox(height: 28),
        SizedBox(
          height: 54,
          child: ElevatedButton(
            onPressed: _isLoading ? null : _requestAccess,
            child: _isLoading
                ? const SizedBox(
                    height: 20,
                    width: 20,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      valueColor: AlwaysStoppedAnimation(Colors.white),
                    ),
                  )
                : const Text('Submit Request'),
          ),
        ),
      ],
    );
  }

  Color _getStatusColor(MobileAccess? access) {
    if (access?.isApproved == true) return AppColors.success;
    if (access?.isPending == true) return AppColors.warning;
    if (access?.isRejected == true || access?.isRevoked == true) return AppColors.error;
    return AppColors.primary;
  }

  IconData _getStatusIcon(MobileAccess? access) {
    if (access?.isApproved == true) return Icons.check_circle;
    if (access?.isPending == true) return Icons.hourglass_empty;
    if (access?.isRejected == true) return Icons.cancel;
    if (access?.isRevoked == true) return Icons.block;
    return Icons.smartphone;
  }

  String _getStatusTitle(MobileAccess? access) {
    if (access?.isApproved == true) return 'Access Approved';
    if (access?.isPending == true) return 'Pending Approval';
    if (access?.isRejected == true) return 'Request Rejected';
    if (access?.isRevoked == true) return 'Access Revoked';
    return 'Mobile Access';
  }

  String _getStatusSubtitle(MobileAccess? access, bool isOwner) {
    if (access?.isApproved == true) return 'Your company has mobile access. Register this device to continue.';
    if (access?.isPending == true) return 'Your request is being reviewed.';
    if (access?.isRejected == true) return 'Your request was not approved.';
    if (access?.isRevoked == true) return 'Contact support for assistance.';
    if (isOwner) return 'Request access to use the mobile POS app.';
    return 'Contact your company owner for access.';
  }
}
