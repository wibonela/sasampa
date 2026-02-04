import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:printing/printing.dart';
import 'package:pdf/pdf.dart';
import 'package:app_settings/app_settings.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';

class SettingsScreen extends ConsumerStatefulWidget {
  const SettingsScreen({super.key});

  @override
  ConsumerState<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends ConsumerState<SettingsScreen> {
  bool _isLoggingOut = false;

  Future<void> _logout() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Logout'),
        content: const Text('Are you sure you want to logout?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            child: const Text('Logout'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    setState(() => _isLoggingOut = true);

    try {
      await ref.read(authProvider.notifier).logout();
      if (mounted) {
        context.go('/login');
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Failed to logout. Please try again.'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoggingOut = false);
      }
    }
  }

  Future<void> _changePin() async {
    final currentPinController = TextEditingController();
    final newPinController = TextEditingController();
    final confirmPinController = TextEditingController();

    final result = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Change PIN'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: currentPinController,
              keyboardType: TextInputType.number,
              maxLength: 6,
              obscureText: true,
              decoration: const InputDecoration(
                labelText: 'Current PIN',
                counterText: '',
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: newPinController,
              keyboardType: TextInputType.number,
              maxLength: 6,
              obscureText: true,
              decoration: const InputDecoration(
                labelText: 'New PIN (4-6 digits)',
                counterText: '',
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: confirmPinController,
              keyboardType: TextInputType.number,
              maxLength: 6,
              obscureText: true,
              decoration: const InputDecoration(
                labelText: 'Confirm New PIN',
                counterText: '',
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () async {
              if (newPinController.text.length < 4) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('PIN must be at least 4 digits')),
                );
                return;
              }
              if (newPinController.text != confirmPinController.text) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('PINs do not match')),
                );
                return;
              }

              try {
                final api = ref.read(apiClientProvider);
                await api.changePin(
                  currentPinController.text,
                  newPinController.text,
                );
                if (context.mounted) {
                  Navigator.pop(context, true);
                }
              } catch (e) {
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Failed to change PIN'),
                      backgroundColor: AppColors.error,
                    ),
                  );
                }
              }
            },
            child: const Text('Change'),
          ),
        ],
      ),
    );

    if (result == true && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('PIN changed successfully'),
          backgroundColor: AppColors.success,
        ),
      );
    }
  }

  Future<void> _changePassword() async {
    final currentPasswordController = TextEditingController();
    final newPasswordController = TextEditingController();
    final confirmPasswordController = TextEditingController();

    final result = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Change Password'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: currentPasswordController,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: 'Current Password',
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: newPasswordController,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: 'New Password',
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: confirmPasswordController,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: 'Confirm New Password',
                ),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () async {
              if (newPasswordController.text.length < 8) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Password must be at least 8 characters')),
                );
                return;
              }
              if (newPasswordController.text != confirmPasswordController.text) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Passwords do not match')),
                );
                return;
              }

              try {
                final api = ref.read(apiClientProvider);
                await api.changePassword(
                  currentPasswordController.text,
                  newPasswordController.text,
                  confirmPasswordController.text,
                );
                if (context.mounted) {
                  Navigator.pop(context, true);
                }
              } catch (e) {
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Failed to change password'),
                      backgroundColor: AppColors.error,
                    ),
                  );
                }
              }
            },
            child: const Text('Change'),
          ),
        ],
      ),
    );

    if (result == true && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Password changed successfully'),
          backgroundColor: AppColors.success,
        ),
      );
    }
  }

  Future<void> _showPrinterSetup() async {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Printer Setup'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Receipt Printing Options:',
              style: TextStyle(fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 16),
            ListTile(
              leading: const Icon(Icons.print, color: AppColors.primary),
              title: const Text('AirPrint'),
              subtitle: const Text('Print to any AirPrint-enabled printer'),
              contentPadding: EdgeInsets.zero,
              onTap: () async {
                Navigator.pop(context);
                // Test print
                await Printing.layoutPdf(
                  onLayout: (_) async {
                    // Generate a simple test page
                    final pdf = await _generateTestReceipt();
                    return pdf;
                  },
                  name: 'Test Receipt',
                );
              },
            ),
            const Divider(),
            ListTile(
              leading: const Icon(Icons.share, color: AppColors.primary),
              title: const Text('Share as PDF'),
              subtitle: const Text('Save or share receipts as PDF'),
              contentPadding: EdgeInsets.zero,
              onTap: () async {
                Navigator.pop(context);
                final pdf = await _generateTestReceipt();
                await Printing.sharePdf(bytes: pdf, filename: 'test_receipt.pdf');
              },
            ),
            const SizedBox(height: 8),
            const Text(
              'Tip: Use the Print or Share buttons on receipts to print directly.',
              style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }

  Future<Uint8List> _generateTestReceipt() async {
    final pdf = await Printing.convertHtml(
      format: PdfPageFormat.roll80,
      html: '''
        <html>
        <body style="font-family: monospace; font-size: 12px; text-align: center;">
          <h2>Test Receipt</h2>
          <p>-----------------------</p>
          <p>SASAMPA POS</p>
          <p>Test Print</p>
          <p>-----------------------</p>
          <p>Date: ${DateTime.now().toString().substring(0, 16)}</p>
          <p>-----------------------</p>
          <p>If you can see this,</p>
          <p>printing is working!</p>
          <p>-----------------------</p>
        </body>
        </html>
      ''',
    );
    return Uint8List.fromList(pdf);
  }

  Future<void> _showNotificationSettings() async {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Notification Settings'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Manage your notification preferences:'),
            const SizedBox(height: 16),
            ListTile(
              leading: const Icon(Icons.settings, color: AppColors.primary),
              title: const Text('Open System Settings'),
              subtitle: const Text('Configure app notifications'),
              contentPadding: EdgeInsets.zero,
              onTap: () {
                Navigator.pop(context);
                AppSettings.openAppSettings(type: AppSettingsType.notification);
              },
            ),
            const SizedBox(height: 12),
            const Text(
              'Notifications include:',
              style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
            ),
            const SizedBox(height: 8),
            const Text('Low stock alerts', style: TextStyle(fontSize: 13)),
            const Text('New order notifications', style: TextStyle(fontSize: 13)),
            const Text('System updates', style: TextStyle(fontSize: 13)),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final user = authState.user;

    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: const Text('Settings'),
        centerTitle: true,
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Profile Card
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              children: [
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: 0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Center(
                    child: Text(
                      (user?.name.isNotEmpty ?? false)
                          ? user!.name[0].toUpperCase()
                          : 'U',
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: AppColors.primary,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        user?.name ?? 'User',
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        user?.email ?? '',
                        style: const TextStyle(
                          color: AppColors.textSecondary,
                        ),
                      ),
                      if (user?.company != null) ...[
                        const SizedBox(height: 4),
                        Text(
                          user!.company!.name,
                          style: const TextStyle(
                            fontSize: 13,
                            color: AppColors.textSecondary,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // Business Section
          const Text(
            'Business',
            style: TextStyle(
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
                _buildSettingItem(
                  icon: Icons.store_outlined,
                  title: 'Store Settings',
                  subtitle: 'Name, address, receipt settings',
                  onTap: () => context.push('/store-settings'),
                ),
                const Divider(height: 1, indent: 56),
                _buildSettingItem(
                  icon: Icons.wallet_outlined,
                  title: 'Expenses (Matumizi)',
                  subtitle: 'Track operational costs',
                  onTap: () => context.push('/expenses'),
                ),
                const Divider(height: 1, indent: 56),
                _buildSettingItem(
                  icon: Icons.inventory_2_outlined,
                  title: 'Inventory',
                  subtitle: 'Stock levels & adjustments',
                  onTap: () => context.push('/inventory'),
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // Account Section
          const Text(
            'Account',
            style: TextStyle(
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
                _buildSettingItem(
                  icon: Icons.lock_outline,
                  title: 'Change PIN',
                  subtitle: 'Update your login PIN',
                  onTap: _changePin,
                ),
                const Divider(height: 1, indent: 56),
                _buildSettingItem(
                  icon: Icons.key_outlined,
                  title: 'Change Password',
                  subtitle: 'Update your password',
                  onTap: _changePassword,
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // App Section
          const Text(
            'App',
            style: TextStyle(
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
                _buildSettingItem(
                  icon: Icons.print_outlined,
                  title: 'Printer Setup',
                  subtitle: 'Configure receipt printer',
                  onTap: _showPrinterSetup,
                ),
                const Divider(height: 1, indent: 56),
                _buildSettingItem(
                  icon: Icons.notifications_outlined,
                  title: 'Notifications',
                  subtitle: 'Manage push notifications',
                  onTap: _showNotificationSettings,
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // Support Section
          const Text(
            'Support',
            style: TextStyle(
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
                _buildSettingItem(
                  icon: Icons.help_outline,
                  title: 'Help & Support',
                  subtitle: 'Get help with the app',
                  onTap: () {
                    showDialog(
                      context: context,
                      builder: (context) => AlertDialog(
                        title: const Text('Help & Support'),
                        content: const Column(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('For assistance, please contact:'),
                            SizedBox(height: 12),
                            Text('Email: support@sasampa.com'),
                            Text('Phone: +255 123 456 789'),
                          ],
                        ),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(context),
                            child: const Text('Close'),
                          ),
                        ],
                      ),
                    );
                  },
                ),
                const Divider(height: 1, indent: 56),
                _buildSettingItem(
                  icon: Icons.info_outline,
                  title: 'About',
                  subtitle: 'App version and info',
                  onTap: () {
                    showDialog(
                      context: context,
                      builder: (context) => AlertDialog(
                        title: const Text('About Sasampa POS'),
                        content: const Column(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Version: 1.0.0'),
                            SizedBox(height: 8),
                            Text('A modern point of sale system for your business.'),
                            SizedBox(height: 16),
                            Text(
                              '2024 Sasampa POS',
                              style: TextStyle(color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(context),
                            child: const Text('Close'),
                          ),
                        ],
                      ),
                    );
                  },
                ),
              ],
            ),
          ),

          const SizedBox(height: 32),

          // Logout Button
          SizedBox(
            height: 50,
            child: OutlinedButton(
              onPressed: _isLoggingOut ? null : _logout,
              style: OutlinedButton.styleFrom(
                foregroundColor: AppColors.error,
                side: const BorderSide(color: AppColors.error),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: _isLoggingOut
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation(AppColors.error),
                      ),
                    )
                  : const Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.logout),
                        SizedBox(width: 8),
                        Text('Logout'),
                      ],
                    ),
            ),
          ),

          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _buildSettingItem({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
    Widget? trailing,
  }) {
    return ListTile(
      onTap: onTap,
      leading: Container(
        width: 40,
        height: 40,
        decoration: BoxDecoration(
          color: AppColors.primary.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Icon(icon, color: AppColors.primary, size: 20),
      ),
      title: Text(
        title,
        style: const TextStyle(fontWeight: FontWeight.w500),
      ),
      subtitle: Text(
        subtitle,
        style: const TextStyle(fontSize: 13, color: AppColors.textSecondary),
      ),
      trailing: trailing ?? const Icon(Icons.chevron_right, color: AppColors.gray3),
    );
  }
}
