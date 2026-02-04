class Product {
  final int id;
  final String name;
  final String? sku;
  final String? barcode;
  final double sellingPrice;
  final double taxRate;
  final int stock;
  final int lowStockThreshold;
  final bool isLowStock;
  final String? imageUrl;
  final Category? category;

  Product({
    required this.id,
    required this.name,
    this.sku,
    this.barcode,
    required this.sellingPrice,
    required this.taxRate,
    required this.stock,
    this.lowStockThreshold = 10,
    this.isLowStock = false,
    this.imageUrl,
    this.category,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'],
      name: json['name'],
      sku: json['sku'],
      barcode: json['barcode'],
      sellingPrice: (json['selling_price'] ?? 0).toDouble(),
      taxRate: (json['tax_rate'] ?? 0).toDouble(),
      stock: json['stock'] ?? 0,
      lowStockThreshold: json['low_stock_threshold'] ?? 10,
      isLowStock: json['is_low_stock'] ?? false,
      imageUrl: json['image_url'],
      category: json['category'] != null ? Category.fromJson(json['category']) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'sku': sku,
      'barcode': barcode,
      'selling_price': sellingPrice,
      'tax_rate': taxRate,
      'stock': stock,
      'low_stock_threshold': lowStockThreshold,
      'is_low_stock': isLowStock,
      'image_url': imageUrl,
      'category': category?.toJson(),
    };
  }

  double get priceWithTax => sellingPrice * (1 + taxRate / 100);

  bool get isInStock => stock > 0;
}

class Category {
  final int id;
  final String name;
  final String? description;
  final int productsCount;
  final int? parentId;

  Category({
    required this.id,
    required this.name,
    this.description,
    this.productsCount = 0,
    this.parentId,
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['id'],
      name: json['name'],
      description: json['description'],
      productsCount: json['products_count'] ?? 0,
      parentId: json['parent_id'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'products_count': productsCount,
      'parent_id': parentId,
    };
  }
}
