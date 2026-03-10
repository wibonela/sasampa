import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';

class BusinessDetailsScreen extends ConsumerStatefulWidget {
  const BusinessDetailsScreen({super.key});

  @override
  ConsumerState<BusinessDetailsScreen> createState() => _BusinessDetailsScreenState();
}

class _BusinessDetailsScreenState extends ConsumerState<BusinessDetailsScreen> {
  final _formKey = GlobalKey<FormState>();
  final _companyNameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();
  bool _isLoading = false;
  String? _error;

  @override
  void dispose() {
    _companyNameController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    super.dispose();
  }

  Future<void> _saveDetails() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final api = ref.read(apiClientProvider);
      await api.saveBusinessDetails(
        companyName: _companyNameController.text.trim(),
        companyPhone: _phoneController.text.trim().isNotEmpty
            ? _phoneController.text.trim()
            : null,
        companyAddress: _addressController.text.trim().isNotEmpty
            ? _addressController.text.trim()
            : null,
      );

      // Complete onboarding in the same step
      await api.completeOnboarding();

      // Refresh auth state
      await ref.read(authProvider.notifier).refreshUser();

      if (mounted) {
        // Go directly to mobile access request
        context.go('/mobile-access');
      }
    } catch (e) {
      setState(() {
        _error = AppLocalizations.of(context)!.failedToProcess;
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const SizedBox(height: 32),

                Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    color: AppColors.primary.withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.store_outlined,
                    color: AppColors.primary,
                    size: 40,
                  ),
                ),

                const SizedBox(height: 24),

                Text(
                  l10n.businessDetails,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary,
                  ),
                ),

                const SizedBox(height: 8),

                Text(
                  l10n.businessDetailsSubtitle,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 16,
                    color: AppColors.textSecondary,
                  ),
                ),

                const SizedBox(height: 32),

                // Company Name
                TextFormField(
                  controller: _companyNameController,
                  textInputAction: TextInputAction.next,
                  decoration: InputDecoration(
                    labelText: l10n.companyName,
                    hintText: l10n.enterBusinessName,
                    prefixIcon: const Icon(Icons.business_outlined),
                  ),
                  validator: (value) {
                    if (value == null || value.trim().isEmpty) {
                      return l10n.pleaseEnterBusinessName;
                    }
                    return null;
                  },
                ),

                const SizedBox(height: 16),

                // Phone
                TextFormField(
                  controller: _phoneController,
                  keyboardType: TextInputType.phone,
                  textInputAction: TextInputAction.next,
                  decoration: InputDecoration(
                    labelText: l10n.phoneOptional,
                    hintText: '+255 xxx xxx xxx',
                    prefixIcon: const Icon(Icons.phone_outlined),
                  ),
                ),

                const SizedBox(height: 16),

                // Address
                TextFormField(
                  controller: _addressController,
                  textInputAction: TextInputAction.done,
                  maxLines: 2,
                  decoration: InputDecoration(
                    labelText: l10n.addressOptional,
                    prefixIcon: const Icon(Icons.location_on_outlined),
                  ),
                  onFieldSubmitted: (_) => _saveDetails(),
                ),

                const SizedBox(height: 24),

                if (_error != null)
                  Container(
                    padding: const EdgeInsets.all(12),
                    margin: const EdgeInsets.only(bottom: 16),
                    decoration: BoxDecoration(
                      color: AppColors.error.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: AppColors.error.withOpacity(0.3)),
                    ),
                    child: Text(
                      _error!,
                      style: const TextStyle(color: AppColors.error, fontSize: 14),
                    ),
                  ),

                SizedBox(
                  height: 50,
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _saveDetails,
                    child: _isLoading
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation(Colors.white),
                            ),
                          )
                        : Text(l10n.continueBtn),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
