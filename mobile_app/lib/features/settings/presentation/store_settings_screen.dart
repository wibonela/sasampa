import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';

class StoreSettingsScreen extends ConsumerStatefulWidget {
  const StoreSettingsScreen({super.key});

  @override
  ConsumerState<StoreSettingsScreen> createState() => _StoreSettingsScreenState();
}

class _StoreSettingsScreenState extends ConsumerState<StoreSettingsScreen> {
  final _formKey = GlobalKey<FormState>();
  bool _isLoading = true;
  bool _isSaving = false;

  final _storeNameController = TextEditingController();
  final _storeAddressController = TextEditingController();
  final _storePhoneController = TextEditingController();
  final _storeEmailController = TextEditingController();
  final _currencyController = TextEditingController();
  final _taxRateController = TextEditingController();
  final _lowStockController = TextEditingController();
  final _receiptHeaderController = TextEditingController();
  final _receiptFooterController = TextEditingController();

  String? _logoUrl;

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  @override
  void dispose() {
    _storeNameController.dispose();
    _storeAddressController.dispose();
    _storePhoneController.dispose();
    _storeEmailController.dispose();
    _currencyController.dispose();
    _taxRateController.dispose();
    _lowStockController.dispose();
    _receiptHeaderController.dispose();
    _receiptFooterController.dispose();
    super.dispose();
  }

  Future<void> _loadSettings() async {
    try {
      final api = ref.read(apiClientProvider);
      final response = await api.getSettings();
      final data = response.data['data'] as Map<String, dynamic>;

      setState(() {
        _storeNameController.text = data['store_name'] ?? '';
        _storeAddressController.text = data['store_address'] ?? '';
        _storePhoneController.text = data['store_phone'] ?? '';
        _storeEmailController.text = data['store_email'] ?? '';
        _currencyController.text = data['currency_symbol'] ?? 'TZS';
        _taxRateController.text = (data['default_tax_rate'] ?? 0).toString();
        _lowStockController.text = (data['low_stock_threshold'] ?? 10).toString();
        _receiptHeaderController.text = data['receipt_header'] ?? '';
        _receiptFooterController.text = data['receipt_footer'] ?? '';
        _logoUrl = data['store_logo_url'];
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to load settings: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<void> _saveSettings() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSaving = true);

    try {
      final api = ref.read(apiClientProvider);
      await api.updateSettings({
        'store_name': _storeNameController.text,
        'store_address': _storeAddressController.text,
        'store_phone': _storePhoneController.text,
        'store_email': _storeEmailController.text,
        'currency_symbol': _currencyController.text,
        'default_tax_rate': double.tryParse(_taxRateController.text) ?? 0,
        'low_stock_threshold': int.tryParse(_lowStockController.text) ?? 10,
        'receipt_header': _receiptHeaderController.text,
        'receipt_footer': _receiptFooterController.text,
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Settings saved successfully'),
            backgroundColor: AppColors.success,
          ),
        );
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to save: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isSaving = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      appBar: AppBar(
        title: const Text('Store Settings'),
        centerTitle: true,
        actions: [
          TextButton(
            onPressed: _isSaving ? null : _saveSettings,
            child: _isSaving
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Text('Save'),
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Form(
              key: _formKey,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Store Logo & Header Section
                  Container(
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          AppColors.primary,
                          AppColors.primary.withValues(alpha: 0.8),
                        ],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Column(
                      children: [
                        Container(
                          width: 100,
                          height: 100,
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(20),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.2),
                                blurRadius: 10,
                                offset: const Offset(0, 4),
                              ),
                            ],
                            image: _logoUrl != null && _logoUrl!.isNotEmpty
                                ? DecorationImage(
                                    image: NetworkImage(_logoUrl!),
                                    fit: BoxFit.cover,
                                  )
                                : null,
                          ),
                          child: _logoUrl == null || _logoUrl!.isEmpty
                              ? Center(
                                  child: Text(
                                    _storeNameController.text.isNotEmpty
                                        ? _storeNameController.text[0].toUpperCase()
                                        : 'S',
                                    style: TextStyle(
                                      fontSize: 40,
                                      fontWeight: FontWeight.bold,
                                      color: AppColors.primary,
                                    ),
                                  ),
                                )
                              : null,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          _storeNameController.text.isNotEmpty
                              ? _storeNameController.text
                              : 'Your Store',
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 20,
                            color: Colors.white,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        if (_storeAddressController.text.isNotEmpty) ...[
                          const SizedBox(height: 4),
                          Text(
                            _storeAddressController.text,
                            style: TextStyle(
                              color: Colors.white.withValues(alpha: 0.9),
                              fontSize: 14,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ],
                        const SizedBox(height: 12),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            'Logo managed in web dashboard',
                            style: TextStyle(
                              color: Colors.white.withValues(alpha: 0.9),
                              fontSize: 12,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Store Information
                  const Text(
                    'STORE INFORMATION',
                    style: TextStyle(
                      fontSize: 13,
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
                        _buildTextField(
                          controller: _storeNameController,
                          label: 'Store Name',
                          icon: Icons.store_outlined,
                          validator: (v) =>
                              v?.isEmpty == true ? 'Store name is required' : null,
                        ),
                        const Divider(height: 1, indent: 56),
                        _buildTextField(
                          controller: _storeAddressController,
                          label: 'Address',
                          icon: Icons.location_on_outlined,
                          maxLines: 2,
                        ),
                        const Divider(height: 1, indent: 56),
                        _buildTextField(
                          controller: _storePhoneController,
                          label: 'Phone',
                          icon: Icons.phone_outlined,
                          keyboardType: TextInputType.phone,
                        ),
                        const Divider(height: 1, indent: 56),
                        _buildTextField(
                          controller: _storeEmailController,
                          label: 'Email',
                          icon: Icons.email_outlined,
                          keyboardType: TextInputType.emailAddress,
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 24),

                  // POS Settings
                  const Text(
                    'POS SETTINGS',
                    style: TextStyle(
                      fontSize: 13,
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
                        _buildTextField(
                          controller: _currencyController,
                          label: 'Currency Symbol',
                          icon: Icons.attach_money,
                          hint: 'e.g., TZS, USD, KES',
                        ),
                        const Divider(height: 1, indent: 56),
                        _buildTextField(
                          controller: _taxRateController,
                          label: 'Default Tax Rate (%)',
                          icon: Icons.percent,
                          keyboardType: TextInputType.number,
                          hint: '0 for no tax',
                        ),
                        const Divider(height: 1, indent: 56),
                        _buildTextField(
                          controller: _lowStockController,
                          label: 'Low Stock Threshold',
                          icon: Icons.inventory_2_outlined,
                          keyboardType: TextInputType.number,
                          hint: 'Alert when stock falls below',
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Receipt Settings
                  const Text(
                    'RECEIPT SETTINGS',
                    style: TextStyle(
                      fontSize: 13,
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
                        _buildTextField(
                          controller: _receiptHeaderController,
                          label: 'Receipt Header',
                          icon: Icons.text_fields,
                          maxLines: 3,
                          hint: 'Text shown at top of receipt',
                        ),
                        const Divider(height: 1, indent: 56),
                        _buildTextField(
                          controller: _receiptFooterController,
                          label: 'Receipt Footer',
                          icon: Icons.text_fields,
                          maxLines: 3,
                          hint: 'e.g., Thank you for your business!',
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 32),
                ],
              ),
            ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    String? hint,
    int maxLines = 1,
    TextInputType? keyboardType,
    String? Function(String?)? validator,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        crossAxisAlignment:
            maxLines > 1 ? CrossAxisAlignment.start : CrossAxisAlignment.center,
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: AppColors.primary.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: AppColors.primary, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: TextFormField(
              controller: controller,
              maxLines: maxLines,
              keyboardType: keyboardType,
              validator: validator,
              decoration: InputDecoration(
                labelText: label,
                hintText: hint,
                border: InputBorder.none,
                contentPadding: EdgeInsets.zero,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
