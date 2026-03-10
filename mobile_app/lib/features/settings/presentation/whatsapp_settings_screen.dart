import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:sasampa_pos/l10n/app_localizations.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';

class WhatsAppSettingsScreen extends ConsumerStatefulWidget {
  const WhatsAppSettingsScreen({super.key});

  @override
  ConsumerState<WhatsAppSettingsScreen> createState() => _WhatsAppSettingsScreenState();
}

class _WhatsAppSettingsScreenState extends ConsumerState<WhatsAppSettingsScreen> {
  bool _isLoading = true;
  bool _isSaving = false;
  bool _isTesting = false;

  bool _enabled = false;
  String _mode = 'prompted';
  bool _smsFallback = true;
  final _footerController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  @override
  void dispose() {
    _footerController.dispose();
    super.dispose();
  }

  Future<void> _loadSettings() async {
    try {
      final api = ref.read(apiClientProvider);
      final response = await api.getWhatsAppSettings();
      final data = response.data['data'];
      setState(() {
        _enabled = data['whatsapp_receipts_enabled'] == true;
        _mode = data['whatsapp_receipts_mode'] ?? 'prompted';
        _smsFallback = data['whatsapp_receipts_sms_fallback'] != false;
        _footerController.text = data['whatsapp_receipts_marketing_footer'] ?? '';
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(AppLocalizations.of(context)!.failedToLoad),
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
      await api.updateWhatsAppSettings({
        'whatsapp_receipts_enabled': _enabled,
        'whatsapp_receipts_mode': _mode,
        'whatsapp_receipts_sms_fallback': _smsFallback,
        'whatsapp_receipts_marketing_footer': _footerController.text.trim(),
      });
      if (mounted) {
        final l10n = AppLocalizations.of(context)!;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(l10n.save),
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

  Future<void> _sendTestMessage() async {
    final l10n = AppLocalizations.of(context)!;
    final phoneController = TextEditingController();

    final phone = await showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(l10n.testWhatsApp),
        content: TextField(
          controller: phoneController,
          keyboardType: TextInputType.phone,
          decoration: InputDecoration(
            labelText: l10n.enterCustomerPhone,
            hintText: '+255...',
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(l10n.cancel),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, phoneController.text.trim()),
            child: Text(l10n.confirm),
          ),
        ],
      ),
    );

    if (phone == null || phone.isEmpty) return;

    setState(() => _isTesting = true);
    try {
      // We'll create a dummy test by sending to the last transaction
      // For a real test, we just validate the connection
      final api = ref.read(apiClientProvider);
      final txResponse = await api.getMyTransactions();
      final transactions = txResponse.data['data'] as List? ?? [];
      if (transactions.isEmpty) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(l10n.noTransactions)),
          );
        }
        return;
      }
      final lastTxId = transactions.first['id'];
      await api.sendWhatsAppReceipt(lastTxId, phone: phone);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(l10n.whatsappReceiptSent),
            backgroundColor: AppColors.success,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${l10n.failedToSend}: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isTesting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: Text(l10n.whatsappSettings),
        actions: [
          if (!_isLoading)
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
                // Enable/Disable
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: SwitchListTile(
                    title: Text(l10n.whatsappReceipts),
                    subtitle: Text(_enabled ? l10n.whatsappEnabled : l10n.whatsappDisabled),
                    value: _enabled,
                    onChanged: (v) => setState(() => _enabled = v),
                    secondary: Container(
                      width: 40,
                      height: 40,
                      decoration: BoxDecoration(
                        color: AppColors.primary.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(Icons.chat_outlined, color: AppColors.primary, size: 20),
                    ),
                  ),
                ),

                const SizedBox(height: 16),

                // Delivery Mode
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        l10n.deliveryMode,
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                      ),
                      const SizedBox(height: 12),
                      RadioListTile<String>(
                        title: Text(l10n.automatic),
                        subtitle: Text(l10n.automaticDesc),
                        value: 'automatic',
                        groupValue: _mode,
                        onChanged: _enabled ? (v) => setState(() => _mode = v!) : null,
                        contentPadding: EdgeInsets.zero,
                      ),
                      RadioListTile<String>(
                        title: Text(l10n.prompted),
                        subtitle: Text(l10n.promptedDesc),
                        value: 'prompted',
                        groupValue: _mode,
                        onChanged: _enabled ? (v) => setState(() => _mode = v!) : null,
                        contentPadding: EdgeInsets.zero,
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 16),

                // SMS Fallback
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: SwitchListTile(
                    title: Text(l10n.smsFallback),
                    subtitle: Text(l10n.smsFallbackDesc),
                    value: _smsFallback,
                    onChanged: _enabled ? (v) => setState(() => _smsFallback = v) : null,
                  ),
                ),

                const SizedBox(height: 16),

                // Marketing Footer
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        l10n.marketingFooter,
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                      ),
                      const SizedBox(height: 8),
                      TextField(
                        controller: _footerController,
                        maxLines: 3,
                        enabled: _enabled,
                        decoration: InputDecoration(
                          hintText: l10n.marketingFooterHint,
                          border: const OutlineInputBorder(),
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 24),

                // Test Button
                SizedBox(
                  height: 48,
                  child: OutlinedButton.icon(
                    onPressed: _enabled && !_isTesting ? _sendTestMessage : null,
                    icon: _isTesting
                        ? const SizedBox(
                            width: 16,
                            height: 16,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Icon(Icons.send_outlined),
                    label: Text(l10n.testWhatsApp),
                  ),
                ),

                const SizedBox(height: 32),
              ],
            ),
    );
  }
}
