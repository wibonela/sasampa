import 'product.dart';

class CartItem {
  final Product product;
  int quantity;

  CartItem({
    required this.product,
    this.quantity = 1,
  });

  double get subtotal => product.sellingPrice * quantity;
  double get taxAmount => subtotal * (product.taxRate / 100);
  double get total => subtotal + taxAmount;

  Map<String, dynamic> toCheckoutJson() {
    return {
      'product_id': product.id,
      'quantity': quantity,
    };
  }
}

class Cart {
  final List<CartItem> items;
  double discountAmount;

  Cart({
    List<CartItem>? items,
    this.discountAmount = 0,
  }) : items = items ?? [];

  int get itemCount => items.fold(0, (sum, item) => sum + item.quantity);
  int get uniqueItemCount => items.length;

  double get subtotal => items.fold(0, (sum, item) => sum + item.subtotal);
  double get taxAmount => items.fold(0, (sum, item) => sum + item.taxAmount);
  double get total => subtotal + taxAmount - discountAmount;

  bool get isEmpty => items.isEmpty;
  bool get isNotEmpty => items.isNotEmpty;

  void addItem(Product product, {int quantity = 1}) {
    final existingIndex = items.indexWhere((item) => item.product.id == product.id);
    if (existingIndex >= 0) {
      items[existingIndex].quantity += quantity;
    } else {
      items.add(CartItem(product: product, quantity: quantity));
    }
  }

  void removeItem(int productId) {
    items.removeWhere((item) => item.product.id == productId);
  }

  void updateQuantity(int productId, int quantity) {
    final index = items.indexWhere((item) => item.product.id == productId);
    if (index >= 0) {
      if (quantity <= 0) {
        items.removeAt(index);
      } else {
        items[index].quantity = quantity;
      }
    }
  }

  void incrementQuantity(int productId) {
    final index = items.indexWhere((item) => item.product.id == productId);
    if (index >= 0) {
      items[index].quantity++;
    }
  }

  void decrementQuantity(int productId) {
    final index = items.indexWhere((item) => item.product.id == productId);
    if (index >= 0) {
      if (items[index].quantity > 1) {
        items[index].quantity--;
      } else {
        items.removeAt(index);
      }
    }
  }

  void clear() {
    items.clear();
    discountAmount = 0;
  }

  void setDiscount(double amount) {
    discountAmount = amount;
  }

  List<Map<String, dynamic>> toCheckoutItems() {
    return items.map((item) => item.toCheckoutJson()).toList();
  }

  CartItem? getItem(int productId) {
    try {
      return items.firstWhere((item) => item.product.id == productId);
    } catch (e) {
      return null;
    }
  }

  bool hasProduct(int productId) {
    return items.any((item) => item.product.id == productId);
  }

  Cart copyWith({
    List<CartItem>? items,
    double? discountAmount,
  }) {
    return Cart(
      items: items ?? List.from(this.items),
      discountAmount: discountAmount ?? this.discountAmount,
    );
  }
}
