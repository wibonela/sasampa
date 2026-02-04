import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../app/theme/colors.dart';
import '../../../core/providers.dart';

class AddExpenseScreen extends ConsumerStatefulWidget {
  final int? expenseId; // null for new expense, set for edit

  const AddExpenseScreen({super.key, this.expenseId});

  @override
  ConsumerState<AddExpenseScreen> createState() => _AddExpenseScreenState();
}

class _AddExpenseScreenState extends ConsumerState<AddExpenseScreen> {
  final _formKey = GlobalKey<FormState>();
  bool _isLoading = true;
  bool _isSaving = false;

  List<Map<String, dynamic>> _categories = [];
  int? _selectedCategoryId;
  DateTime _selectedDate = DateTime.now();
  String _selectedPaymentMethod = 'cash';
  String? _selectedUnit;

  final _descriptionController = TextEditingController();
  final _amountController = TextEditingController();
  final _quantityController = TextEditingController(text: '1');
  final _supplierController = TextEditingController();
  final _referenceController = TextEditingController();
  final _notesController = TextEditingController();

  final _currencyFormat = NumberFormat('#,###');

  bool get isEditing => widget.expenseId != null;

  final List<String> _units = [
    'kg',
    'g',
    'L',
    'ml',
    'pcs',
    'bags',
    'boxes',
    'rolls',
  ];

  final List<Map<String, dynamic>> _paymentMethods = [
    {'value': 'cash', 'label': 'Cash', 'icon': Icons.money},
    {'value': 'mobile', 'label': 'Mobile Money', 'icon': Icons.phone_android},
    {'value': 'card', 'label': 'Card', 'icon': Icons.credit_card},
    {'value': 'bank', 'label': 'Bank Transfer', 'icon': Icons.account_balance},
  ];

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  @override
  void dispose() {
    _descriptionController.dispose();
    _amountController.dispose();
    _quantityController.dispose();
    _supplierController.dispose();
    _referenceController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    try {
      final api = ref.read(apiClientProvider);
      final categoriesResponse = await api.getExpenseCategories();

      setState(() {
        _categories = List<Map<String, dynamic>>.from(
          categoriesResponse.data['data'] ?? [],
        );
      });

      // If editing, load the expense data
      if (isEditing) {
        final expenseResponse = await api.getExpense(widget.expenseId!);
        final expense = expenseResponse.data['data'] as Map<String, dynamic>;

        setState(() {
          _selectedCategoryId = expense['category']['id'];
          _descriptionController.text = expense['description'] ?? '';
          _amountController.text = expense['amount'].toString();
          _quantityController.text = expense['quantity'].toString();
          _selectedUnit = expense['unit'];
          _selectedDate = DateTime.parse(expense['expense_date']);
          _supplierController.text = expense['supplier'] ?? '';
          _referenceController.text = expense['reference_number'] ?? '';
          _selectedPaymentMethod = expense['payment_method'];
          _notesController.text = expense['notes'] ?? '';
        });
      }

      setState(() => _isLoading = false);
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to load: $e'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  double get _total {
    final amount = double.tryParse(_amountController.text) ?? 0;
    final quantity = double.tryParse(_quantityController.text) ?? 1;
    return amount * quantity;
  }

  Future<void> _selectDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now(),
    );
    if (picked != null) {
      setState(() => _selectedDate = picked);
    }
  }

  Future<void> _addCategory() async {
    final nameController = TextEditingController();
    final result = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Add Category'),
        content: TextField(
          controller: nameController,
          autofocus: true,
          decoration: const InputDecoration(
            labelText: 'Category Name',
            hintText: 'e.g., Raw Materials, Utilities',
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Add'),
          ),
        ],
      ),
    );

    if (result == true && nameController.text.isNotEmpty) {
      try {
        final api = ref.read(apiClientProvider);
        final response = await api.createExpenseCategory(
          nameController.text,
          null,
        );
        final newCategory = response.data['data'] as Map<String, dynamic>;

        setState(() {
          _categories.add(newCategory);
          _selectedCategoryId = newCategory['id'];
        });
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Failed to add category: $e'),
              backgroundColor: AppColors.error,
            ),
          );
        }
      }
    }
  }

  Future<void> _saveExpense() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedCategoryId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please select a category'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    setState(() => _isSaving = true);

    try {
      final api = ref.read(apiClientProvider);

      if (isEditing) {
        await api.updateExpense(widget.expenseId!, {
          'expense_category_id': _selectedCategoryId,
          'description': _descriptionController.text,
          'amount': double.parse(_amountController.text),
          'quantity': double.parse(_quantityController.text),
          'unit': _selectedUnit,
          'expense_date': DateFormat('yyyy-MM-dd').format(_selectedDate),
          'reference_number': _referenceController.text.isEmpty
              ? null
              : _referenceController.text,
          'supplier':
              _supplierController.text.isEmpty ? null : _supplierController.text,
          'payment_method': _selectedPaymentMethod,
          'notes': _notesController.text.isEmpty ? null : _notesController.text,
        });
      } else {
        await api.createExpense(
          categoryId: _selectedCategoryId!,
          description: _descriptionController.text,
          amount: double.parse(_amountController.text),
          quantity: double.parse(_quantityController.text),
          unit: _selectedUnit,
          expenseDate: DateFormat('yyyy-MM-dd').format(_selectedDate),
          referenceNumber: _referenceController.text.isEmpty
              ? null
              : _referenceController.text,
          supplier:
              _supplierController.text.isEmpty ? null : _supplierController.text,
          paymentMethod: _selectedPaymentMethod,
          notes: _notesController.text.isEmpty ? null : _notesController.text,
        );
      }

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(isEditing ? 'Expense updated' : 'Expense recorded'),
            backgroundColor: AppColors.success,
          ),
        );
        Navigator.pop(context, true);
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
        title: Text(isEditing ? 'Edit Expense' : 'Add Expense'),
        centerTitle: true,
        actions: [
          TextButton(
            onPressed: _isSaving ? null : _saveExpense,
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
                  // Category & Date
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Category *',
                                    style: TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.w500,
                                      color: AppColors.textSecondary,
                                    ),
                                  ),
                                  const SizedBox(height: 8),
                                  DropdownButtonFormField<int>(
                                    value: _selectedCategoryId,
                                    decoration: InputDecoration(
                                      border: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      contentPadding: const EdgeInsets.symmetric(
                                        horizontal: 12,
                                        vertical: 12,
                                      ),
                                    ),
                                    hint: const Text('Select'),
                                    items: _categories.map((c) {
                                      return DropdownMenuItem<int>(
                                        value: c['id'],
                                        child: Text(c['name']),
                                      );
                                    }).toList(),
                                    onChanged: (v) =>
                                        setState(() => _selectedCategoryId = v),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 8),
                            IconButton(
                              onPressed: _addCategory,
                              icon: const Icon(Icons.add_circle_outline),
                              tooltip: 'Add Category',
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        const Text(
                          'Date *',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                            color: AppColors.textSecondary,
                          ),
                        ),
                        const SizedBox(height: 8),
                        InkWell(
                          onTap: _selectDate,
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 14,
                            ),
                            decoration: BoxDecoration(
                              border: Border.all(color: AppColors.gray4),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.calendar_today, size: 20),
                                const SizedBox(width: 12),
                                Text(
                                  DateFormat('dd MMMM yyyy').format(_selectedDate),
                                  style: const TextStyle(fontSize: 16),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Description
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Description *',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                            color: AppColors.textSecondary,
                          ),
                        ),
                        const SizedBox(height: 8),
                        TextFormField(
                          controller: _descriptionController,
                          decoration: InputDecoration(
                            hintText: 'e.g., Sugar, Flour, Oil, Packaging',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                          ),
                          validator: (v) =>
                              v?.isEmpty == true ? 'Required' : null,
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Amount, Quantity, Unit
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Unit Price (TZS) *',
                                    style: TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.w500,
                                      color: AppColors.textSecondary,
                                    ),
                                  ),
                                  const SizedBox(height: 8),
                                  TextFormField(
                                    controller: _amountController,
                                    keyboardType: TextInputType.number,
                                    decoration: InputDecoration(
                                      border: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                    ),
                                    onChanged: (_) => setState(() {}),
                                    validator: (v) =>
                                        v?.isEmpty == true ? 'Required' : null,
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Quantity *',
                                    style: TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.w500,
                                      color: AppColors.textSecondary,
                                    ),
                                  ),
                                  const SizedBox(height: 8),
                                  TextFormField(
                                    controller: _quantityController,
                                    keyboardType: TextInputType.number,
                                    decoration: InputDecoration(
                                      border: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                    ),
                                    onChanged: (_) => setState(() {}),
                                    validator: (v) =>
                                        v?.isEmpty == true ? 'Required' : null,
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        const Text(
                          'Unit',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                            color: AppColors.textSecondary,
                          ),
                        ),
                        const SizedBox(height: 8),
                        DropdownButtonFormField<String>(
                          value: _selectedUnit,
                          decoration: InputDecoration(
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                            contentPadding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 12,
                            ),
                          ),
                          hint: const Text('Select Unit'),
                          items: _units.map((u) {
                            return DropdownMenuItem<String>(
                              value: u,
                              child: Text(u),
                            );
                          }).toList(),
                          onChanged: (v) => setState(() => _selectedUnit = v),
                        ),
                        const SizedBox(height: 16),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: AppColors.gray6,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text(
                                'Total:',
                                style: TextStyle(
                                  fontWeight: FontWeight.w600,
                                  fontSize: 16,
                                ),
                              ),
                              Text(
                                'TZS ${_currencyFormat.format(_total)}',
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 18,
                                  color: AppColors.primary,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Supplier & Reference
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Supplier',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                            color: AppColors.textSecondary,
                          ),
                        ),
                        const SizedBox(height: 8),
                        TextFormField(
                          controller: _supplierController,
                          decoration: InputDecoration(
                            hintText: 'Vendor or supplier name',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),
                        const Text(
                          'Reference Number',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                            color: AppColors.textSecondary,
                          ),
                        ),
                        const SizedBox(height: 8),
                        TextFormField(
                          controller: _referenceController,
                          decoration: InputDecoration(
                            hintText: 'Receipt or invoice number',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Payment Method
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Payment Method *',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                            color: AppColors.textSecondary,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: _paymentMethods.map((m) {
                            final isSelected =
                                _selectedPaymentMethod == m['value'];
                            return GestureDetector(
                              onTap: () => setState(
                                  () => _selectedPaymentMethod = m['value']),
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 16,
                                  vertical: 12,
                                ),
                                decoration: BoxDecoration(
                                  color: isSelected
                                      ? AppColors.primary.withValues(alpha: 0.1)
                                      : AppColors.gray6,
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(
                                    color: isSelected
                                        ? AppColors.primary
                                        : Colors.transparent,
                                    width: 2,
                                  ),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      m['icon'],
                                      size: 18,
                                      color: isSelected
                                          ? AppColors.primary
                                          : AppColors.textSecondary,
                                    ),
                                    const SizedBox(width: 8),
                                    Text(
                                      m['label'],
                                      style: TextStyle(
                                        fontWeight: isSelected
                                            ? FontWeight.w600
                                            : FontWeight.normal,
                                        color: isSelected
                                            ? AppColors.primary
                                            : AppColors.textPrimary,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          }).toList(),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // Notes
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Notes',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                            color: AppColors.textSecondary,
                          ),
                        ),
                        const SizedBox(height: 8),
                        TextFormField(
                          controller: _notesController,
                          maxLines: 3,
                          decoration: InputDecoration(
                            hintText: 'Additional notes...',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 32),

                  // Save Button
                  SizedBox(
                    height: 50,
                    child: ElevatedButton(
                      onPressed: _isSaving ? null : _saveExpense,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: _isSaving
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                valueColor:
                                    AlwaysStoppedAnimation(Colors.white),
                              ),
                            )
                          : Text(isEditing ? 'Update Expense' : 'Save Expense'),
                    ),
                  ),

                  const SizedBox(height: 32),
                ],
              ),
            ),
    );
  }
}
