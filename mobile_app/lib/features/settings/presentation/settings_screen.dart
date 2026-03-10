import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:app_settings/app_settings.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
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
    final l10n = AppLocalizations.of(context)!;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(l10n.logout),
        content: Text(l10n.logoutConfirm),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text(l10n.cancel),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            child: Text(l10n.logout),
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
          SnackBar(
            content: Text(l10n.failedToLogout),
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
    final l10n = AppLocalizations.of(context)!;
    final currentPinController = TextEditingController();
    final newPinController = TextEditingController();
    final confirmPinController = TextEditingController();

    final result = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(l10n.changePin),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: currentPinController,
              keyboardType: TextInputType.number,
              maxLength: 6,
              obscureText: true,
              decoration: InputDecoration(
                labelText: l10n.currentPin,
                counterText: '',
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: newPinController,
              keyboardType: TextInputType.number,
              maxLength: 6,
              obscureText: true,
              decoration: InputDecoration(
                labelText: l10n.newPin,
                counterText: '',
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: confirmPinController,
              keyboardType: TextInputType.number,
              maxLength: 6,
              obscureText: true,
              decoration: InputDecoration(
                labelText: l10n.confirmNewPin,
                counterText: '',
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text(l10n.cancel),
          ),
          ElevatedButton(
            onPressed: () async {
              if (newPinController.text.length < 4) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text(l10n.pinTooShort)),
                );
                return;
              }
              if (newPinController.text != confirmPinController.text) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text(l10n.pinsDoNotMatch)),
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
                    SnackBar(
                      content: Text('${l10n.failedToProcess}: $e'),
                      backgroundColor: AppColors.error,
                    ),
                  );
                }
              }
            },
            child: Text(l10n.changeAction),
          ),
        ],
      ),
    );

    if (result == true && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(l10n.pinChangedSuccessfully),
          backgroundColor: AppColors.success,
        ),
      );
    }
  }

  Future<void> _changePassword() async {
    final l10n = AppLocalizations.of(context)!;
    final currentPasswordController = TextEditingController();
    final newPasswordController = TextEditingController();
    final confirmPasswordController = TextEditingController();

    final result = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(l10n.changePassword),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: currentPasswordController,
                obscureText: true,
                decoration: InputDecoration(
                  labelText: l10n.currentPassword,
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: newPasswordController,
                obscureText: true,
                decoration: InputDecoration(
                  labelText: l10n.newPassword,
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: confirmPasswordController,
                obscureText: true,
                decoration: InputDecoration(
                  labelText: l10n.confirmNewPassword,
                ),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text(l10n.cancel),
          ),
          ElevatedButton(
            onPressed: () async {
              if (newPasswordController.text.length < 8) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text(l10n.passwordTooShort)),
                );
                return;
              }
              if (newPasswordController.text != confirmPasswordController.text) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text(l10n.passwordsDoNotMatch)),
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
                    SnackBar(
                      content: Text('${l10n.failedToProcess}: $e'),
                      backgroundColor: AppColors.error,
                    ),
                  );
                }
              }
            },
            child: Text(l10n.changeAction),
          ),
        ],
      ),
    );

    if (result == true && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(l10n.passwordChangedSuccessfully),
          backgroundColor: AppColors.success,
        ),
      );
    }
  }

  Future<void> _showNotificationSettings() async {
    final l10n = AppLocalizations.of(context)!;
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(l10n.notifications),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(l10n.manageNotificationPreferences),
            const SizedBox(height: 16),
            ListTile(
              leading: const Icon(Icons.settings, color: AppColors.primary),
              title: Text(l10n.openSystemSettings),
              subtitle: Text(l10n.configureNotifications),
              contentPadding: EdgeInsets.zero,
              onTap: () {
                Navigator.pop(context);
                AppSettings.openAppSettings(type: AppSettingsType.notification);
              },
            ),
            const SizedBox(height: 12),
            Text(
              l10n.notificationsInclude,
              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
            ),
            const SizedBox(height: 8),
            Text(l10n.lowStockAlerts, style: const TextStyle(fontSize: 13)),
            Text(l10n.newOrderNotifications, style: const TextStyle(fontSize: 13)),
            Text(l10n.systemUpdates, style: const TextStyle(fontSize: 13)),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(l10n.close),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final authState = ref.watch(authProvider);
    final user = authState.user;

    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: Text(l10n.settings),
        centerTitle: true,
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Profile Card with Company Logo
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              children: [
                _buildCompanyLogo(user),
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
          Text(
            l10n.business,
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
                _buildSettingItem(
                  icon: Icons.store_outlined,
                  title: l10n.storeSettings,
                  subtitle: l10n.businessDetails,
                  onTap: () => context.push('/store-settings'),
                ),
                const Divider(height: 1, indent: 56),
                _buildSettingItem(
                  icon: Icons.wallet_outlined,
                  title: l10n.expenses,
                  subtitle: l10n.trackCosts,
                  onTap: () => context.push('/expenses'),
                ),
                const Divider(height: 1, indent: 56),
                _buildSettingItem(
                  icon: Icons.inventory_2_outlined,
                  title: l10n.inventory,
                  subtitle: l10n.stockLevels,
                  onTap: () => context.push('/inventory'),
                ),
                if (user?.isCompanyOwner == true || user?.hasPermission('manage_settings') == true) ...[
                  const Divider(height: 1, indent: 56),
                  _buildSettingItem(
                    icon: Icons.chat_outlined,
                    title: l10n.whatsappReceipts,
                    subtitle: l10n.whatsappSettings,
                    onTap: () => context.push('/whatsapp-settings'),
                  ),
                  const Divider(height: 1, indent: 56),
                  _buildSettingItem(
                    icon: Icons.receipt_long_outlined,
                    title: l10n.efdSettings,
                    subtitle: l10n.traRegistration,
                    onTap: () => context.push('/efd-settings'),
                  ),
                ],
              ],
            ),
          ),

          const SizedBox(height: 24),

          // Account Section
          Text(
            l10n.account,
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
                _buildSettingItem(
                  icon: Icons.lock_outline,
                  title: l10n.changePin,
                  subtitle: l10n.pin,
                  onTap: _changePin,
                ),
                const Divider(height: 1, indent: 56),
                _buildSettingItem(
                  icon: Icons.key_outlined,
                  title: l10n.changePassword,
                  subtitle: l10n.password,
                  onTap: _changePassword,
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // App Section
          Text(
            l10n.app,
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
                _buildSettingItem(
                  icon: Icons.dashboard_customize_outlined,
                  title: l10n.customizeDashboard,
                  subtitle: l10n.customizeDashboardDesc,
                  onTap: () => context.push('/dashboard-customization'),
                ),
                const Divider(height: 1, indent: 56),
                _buildLanguageSwitcher(),
                const Divider(height: 1, indent: 56),
                _buildSettingItem(
                  icon: Icons.print_outlined,
                  title: l10n.printerSetup,
                  subtitle: l10n.receipt,
                  onTap: () => context.push('/printer-setup'),
                ),
                const Divider(height: 1, indent: 56),
                _buildSettingItem(
                  icon: Icons.notifications_outlined,
                  title: l10n.notifications,
                  subtitle: l10n.notifications,
                  onTap: _showNotificationSettings,
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // Support Section
          Text(
            l10n.support,
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
                _buildSettingItem(
                  icon: Icons.help_outline,
                  title: l10n.helpSupport,
                  subtitle: l10n.needHelp,
                  onTap: () {
                    showDialog(
                      context: context,
                      builder: (context) => AlertDialog(
                        title: Text(l10n.helpSupport),
                        content: Column(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(l10n.forAssistanceContact),
                            const SizedBox(height: 12),
                            const Text('Email: support@sasampa.com'),
                            const Text('Phone: +255 123 456 789'),
                          ],
                        ),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(context),
                            child: Text(l10n.close),
                          ),
                        ],
                      ),
                    );
                  },
                ),
                const Divider(height: 1, indent: 56),
                _buildSettingItem(
                  icon: Icons.info_outline,
                  title: l10n.about,
                  subtitle: '${l10n.version} & ${l10n.about}',
                  onTap: () {
                    showDialog(
                      context: context,
                      builder: (context) => AlertDialog(
                        title: Text('${l10n.aboutSasampa} POS'),
                        content: Column(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('${l10n.version}: 1.0.0'),
                            const SizedBox(height: 8),
                            Text(l10n.modernPosSystem),
                            const SizedBox(height: 16),
                            const Text(
                              '2024 Sasampa POS',
                              style: TextStyle(color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(context),
                            child: Text(l10n.close),
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
                  : Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.logout),
                        const SizedBox(width: 8),
                        Text(l10n.logout),
                      ],
                    ),
            ),
          ),

          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _buildLanguageSwitcher() {
    final locale = ref.watch(localeProvider);
    final isSwahili = locale.languageCode == 'sw';

    return ListTile(
      onTap: () {
        final newLocale = isSwahili ? const Locale('en') : const Locale('sw');
        ref.read(localeProvider.notifier).setLocale(newLocale);
      },
      leading: Container(
        width: 40,
        height: 40,
        decoration: BoxDecoration(
          color: AppColors.primary.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(10),
        ),
        child: const Icon(Icons.language, color: AppColors.primary, size: 20),
      ),
      title: const Text('Lugha / Language', style: TextStyle(fontWeight: FontWeight.w500)),
      subtitle: Text(
        isSwahili ? 'Kiswahili' : 'English',
        style: const TextStyle(fontSize: 13, color: AppColors.textSecondary),
      ),
      trailing: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: AppColors.primary.withOpacity(0.3)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            GestureDetector(
              onTap: () => ref.read(localeProvider.notifier).setLocale(const Locale('sw')),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: isSwahili ? AppColors.primary : Colors.transparent,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  'SW',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: isSwahili ? Colors.white : AppColors.textSecondary,
                  ),
                ),
              ),
            ),
            GestureDetector(
              onTap: () => ref.read(localeProvider.notifier).setLocale(const Locale('en')),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: !isSwahili ? AppColors.primary : Colors.transparent,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  'EN',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: !isSwahili ? Colors.white : AppColors.textSecondary,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCompanyLogo(dynamic user) {
    final logoUrl = user?.company?.logo;
    final companyName = user?.company?.name ?? '';
    final initial = companyName.isNotEmpty
        ? companyName[0].toUpperCase()
        : (user?.name?.isNotEmpty == true ? user!.name[0].toUpperCase() : 'U');

    Widget initialWidget() => Container(
      width: 60,
      height: 60,
      decoration: BoxDecoration(
        color: AppColors.primary.withValues(alpha: 0.1),
        shape: BoxShape.circle,
      ),
      child: Center(
        child: Text(
          initial,
          style: const TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.bold,
            color: AppColors.primary,
          ),
        ),
      ),
    );

    if (logoUrl != null && logoUrl.toString().isNotEmpty) {
      return ClipOval(
        child: SizedBox(
          width: 60,
          height: 60,
          child: Image.network(
            logoUrl.toString(),
            fit: BoxFit.cover,
            errorBuilder: (_, __, ___) => initialWidget(),
          ),
        ),
      );
    }

    return initialWidget();
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
