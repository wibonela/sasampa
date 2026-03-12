import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';

class VerifyEmailScreen extends ConsumerStatefulWidget {
  const VerifyEmailScreen({super.key});

  @override
  ConsumerState<VerifyEmailScreen> createState() => _VerifyEmailScreenState();
}

class _VerifyEmailScreenState extends ConsumerState<VerifyEmailScreen> {
  bool _isResending = false;
  bool _isChecking = false;
  bool _isUpdatingEmail = false;
  String? _message;
  bool _isError = false;
  Timer? _pollTimer;

  @override
  void initState() {
    super.initState();
    _pollTimer = Timer.periodic(const Duration(seconds: 5), (_) => _checkStatus());
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    super.dispose();
  }

  Future<void> _checkStatus() async {
    if (_isChecking) return;
    _isChecking = true;

    try {
      final api = ref.read(apiClientProvider);
      final response = await api.checkVerificationStatus();
      final verified = response.data['verified'] == true;

      if (verified && mounted) {
        _pollTimer?.cancel();
        context.go('/business-details');
      }
    } catch (_) {
      // Silently ignore polling errors
    } finally {
      _isChecking = false;
    }
  }

  Future<void> _resendEmail() async {
    setState(() {
      _isResending = true;
      _message = null;
    });

    try {
      final api = ref.read(apiClientProvider);
      await api.resendVerification();
      if (mounted) {
        setState(() {
          _message = AppLocalizations.of(context)!.verificationEmailSent;
          _isError = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _message = AppLocalizations.of(context)!.failedToSend;
          _isError = true;
        });
      }
    } finally {
      setState(() => _isResending = false);
    }
  }

  void _showEditEmailDialog() {
    final user = ref.read(authProvider).user;
    final controller = TextEditingController(text: user?.email ?? '');
    final l10n = AppLocalizations.of(context)!;

    showDialog(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text(l10n.editEmail),
        content: TextField(
          controller: controller,
          keyboardType: TextInputType.emailAddress,
          autofocus: true,
          decoration: InputDecoration(
            labelText: l10n.email,
            hintText: 'example@email.com',
            prefixIcon: const Icon(Icons.email_outlined),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext),
            child: Text(l10n.cancel),
          ),
          ElevatedButton(
            onPressed: () {
              final newEmail = controller.text.trim();
              if (newEmail.isNotEmpty && newEmail.contains('@')) {
                Navigator.pop(dialogContext);
                _updateEmail(newEmail);
              }
            },
            child: Text(l10n.save),
          ),
        ],
      ),
    );
  }

  Future<void> _updateEmail(String newEmail) async {
    setState(() {
      _isUpdatingEmail = true;
      _message = null;
    });

    try {
      final api = ref.read(apiClientProvider);
      await api.updateEmail(newEmail);
      if (mounted) {
        setState(() {
          _message = AppLocalizations.of(context)!.emailUpdatedVerificationSent;
          _isError = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _message = AppLocalizations.of(context)!.failedToProcess;
          _isError = true;
        });
      }
    } finally {
      setState(() => _isUpdatingEmail = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    final user = ref.watch(authProvider).user;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 80,
                height: 80,
                decoration: BoxDecoration(
                  color: AppColors.primary.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.mark_email_unread_outlined,
                  color: AppColors.primary,
                  size: 40,
                ),
              ),

              const SizedBox(height: 24),

              Text(
                l10n.verifyEmail,
                style: const TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: AppColors.textPrimary,
                ),
              ),

              const SizedBox(height: 12),

              Text(
                l10n.verifyEmailSubtitle,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 16,
                  color: AppColors.textSecondary,
                  height: 1.4,
                ),
              ),

              // Show current email
              if (user?.email != null) ...[
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withOpacity(0.05),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: AppColors.primary.withOpacity(0.2)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.email, size: 16, color: AppColors.primary),
                      const SizedBox(width: 8),
                      Flexible(
                        child: Text(
                          user!.email,
                          style: const TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w600,
                            color: AppColors.primary,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],

              const SizedBox(height: 32),

              // Status message
              if (_message != null)
                Container(
                  padding: const EdgeInsets.all(12),
                  margin: const EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(
                    color: _isError
                        ? AppColors.error.withOpacity(0.1)
                        : AppColors.success.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    _message!,
                    style: TextStyle(
                      color: _isError ? AppColors.error : AppColors.success,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),

              // "I've Verified" button
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isChecking ? null : _checkStatus,
                  child: _isChecking
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation(Colors.white),
                          ),
                        )
                      : Text(l10n.iveVerifiedEmail),
                ),
              ),

              const SizedBox(height: 12),

              // Resend button
              SizedBox(
                width: double.infinity,
                height: 50,
                child: OutlinedButton.icon(
                  onPressed: _isResending ? null : _resendEmail,
                  icon: _isResending
                      ? const SizedBox(
                          height: 16,
                          width: 16,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Icon(Icons.refresh),
                  label: Text(_isResending ? l10n.sending : l10n.resendVerificationEmail),
                ),
              ),

              const SizedBox(height: 12),

              // Edit email button
              TextButton.icon(
                onPressed: _isUpdatingEmail ? null : _showEditEmailDialog,
                icon: _isUpdatingEmail
                    ? const SizedBox(
                        height: 14,
                        width: 14,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.edit, size: 16),
                label: Text(l10n.editEmail),
              ),

              const SizedBox(height: 24),

              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    l10n.waitingForVerification,
                    style: const TextStyle(
                      color: AppColors.textTertiary,
                      fontSize: 13,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
