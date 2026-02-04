<x-pos-layout>
    <div class="pos-main">
        <!-- Products Panel -->
        <div class="pos-products-panel">
            <div class="search-box">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="productSearch"
                           placeholder="Search products by name, SKU or barcode..." autofocus>
                </div>
            </div>

            <div class="category-tabs">
                <button class="btn btn-primary btn-sm category-btn active" data-category="">
                    All Products
                </button>
                @foreach($categories as $category)
                    <button class="btn btn-outline-secondary btn-sm category-btn" data-category="{{ $category->id }}">
                        {{ $category->name }} ({{ $category->products_count }})
                    </button>
                @endforeach
            </div>

            <div class="product-grid" id="productGrid">
                @foreach($products as $product)
                    <div class="product-item"
                         data-id="{{ $product->id }}"
                         data-name="{{ $product->name }}"
                         data-price="{{ $product->selling_price }}"
                         data-tax="{{ $product->tax_rate }}"
                         data-stock="{{ $product->stock_quantity }}"
                         data-category="{{ $product->category_id }}">
                        @if($product->image_path)
                            <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto mb-2"
                                 style="width: 60px; height: 60px;">
                                <i class="bi bi-box text-muted"></i>
                            </div>
                        @endif
                        <div class="name">{{ $product->name }}</div>
                        <div class="price">TZS {{ number_format($product->selling_price, 0) }}</div>
                        <small class="text-muted">Stock: {{ $product->stock_quantity }}</small>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Cart Panel (Desktop only) -->
        <div class="pos-cart-panel">
            <div class="cart-header">
                <i class="bi bi-cart3 me-2"></i>Current Sale
            </div>

            <div class="cart-items" id="cartItems">
                <div class="text-center text-muted py-5" id="emptyCartMessage">
                    <i class="bi bi-cart display-4 d-block mb-2"></i>
                    Click products to add them to cart
                </div>
            </div>

            <div class="cart-summary">
                <div class="cart-summary-row">
                    <span>Subtotal:</span>
                    <span id="cartSubtotal">TZS 0</span>
                </div>
                <div class="cart-summary-row">
                    <span>Tax:</span>
                    <span id="cartTax">TZS 0</span>
                </div>
                <div class="cart-summary-row total">
                    <span>Total:</span>
                    <span id="cartTotal">TZS 0</span>
                </div>
            </div>

            <div class="cart-actions">
                <button class="btn btn-outline-danger" id="clearCartBtn" disabled>
                    <i class="bi bi-trash me-1"></i>Clear
                </button>
                <button class="btn btn-outline-secondary" id="holdBtn" disabled>
                    <i class="bi bi-pause me-1"></i>Hold
                </button>
                <button class="btn btn-success btn-lg btn-pay" id="payBtn" disabled>
                    <i class="bi bi-cash me-1"></i>Pay Now
                </button>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Total Amount</label>
                        <div class="h3 text-primary" id="paymentTotal">TZS 0</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Discount</label>
                        <div class="input-group">
                            <span class="input-group-text">TZS</span>
                            <input type="number" class="form-control" id="paymentDiscount" value="0" min="0">
                        </div>
                        <small class="text-muted">Enter discount amount if customer pays less</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount to Pay</label>
                        <div class="h3 text-success" id="finalPaymentAmount">TZS 0</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="paymentMethod" id="methodCash" value="cash" checked>
                            <label class="btn btn-outline-primary" for="methodCash">
                                <i class="bi bi-cash me-1"></i>Cash
                            </label>
                            <input type="radio" class="btn-check" name="paymentMethod" id="methodCard" value="card" disabled>
                            <label class="btn btn-outline-secondary" for="methodCard" style="opacity: 0.5; cursor: not-allowed;" title="Coming soon">
                                <i class="bi bi-credit-card me-1"></i>Card
                            </label>
                            <input type="radio" class="btn-check" name="paymentMethod" id="methodMobile" value="mobile" disabled>
                            <label class="btn btn-outline-secondary" for="methodMobile" style="opacity: 0.5; cursor: not-allowed;" title="Coming soon">
                                <i class="bi bi-phone me-1"></i>Mobile
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="amountPaidSection">
                        <label class="form-label">Amount Received</label>
                        <div class="input-group">
                            <span class="input-group-text">TZS</span>
                            <input type="number" class="form-control form-control-lg" id="amountPaid" min="0">
                        </div>
                    </div>

                    <div class="mb-3" id="changeSection" style="display: none;">
                        <label class="form-label">Change</label>
                        <div class="h4 text-success" id="changeAmount">TZS 0</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Customer Name (Optional)</label>
                        <input type="text" class="form-control" id="customerName" placeholder="Enter customer name">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number (Optional)</label>
                            <input type="tel" class="form-control" id="customerPhone" placeholder="e.g., 0712345678">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">TIN Number (Optional)</label>
                            <input type="text" class="form-control" id="customerTin" placeholder="e.g., 123-456-789">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="completePaymentBtn">
                        <i class="bi bi-check-circle me-1"></i>Complete Sale
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quantity Modal -->
    <div class="modal fade" id="quantityModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quantityModalTitle">Add to Cart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="h5 mb-1" id="quantityProductName">Product Name</div>
                        <div class="text-primary" id="quantityProductPrice">TZS 0</div>
                        <small class="text-muted">Available: <span id="quantityProductStock">0</span></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <div class="input-group input-group-lg">
                            <button class="btn btn-outline-secondary" type="button" onclick="adjustModalQty(-1)">-</button>
                            <input type="number" class="form-control text-center" id="quantityInput" value="1" min="1">
                            <button class="btn btn-outline-secondary" type="button" onclick="adjustModalQty(1)">+</button>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-muted">Subtotal</div>
                        <div class="h4 text-primary" id="quantitySubtotal">TZS 0</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="addToCartBtn" onclick="window.addSelectedToCart && window.addSelectedToCart()">
                        <i class="bi bi-cart-plus me-1"></i>Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content text-center">
                <div class="modal-body py-4">
                    <i class="bi bi-check-circle text-success display-1"></i>
                    <h4 class="mt-3">Sale Complete!</h4>
                    <p class="text-muted mb-0">Transaction #<span id="transactionNumber"></span></p>
                    <div class="h4 mt-2" id="successChange"></div>
                </div>
                <div class="modal-footer justify-content-center flex-wrap gap-2">
                    <a href="#" id="printReceiptBtn" class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-printer me-1"></i>Print
                    </a>
                    <a href="#" id="downloadPdfBtn" class="btn btn-outline-success">
                        <i class="bi bi-file-pdf me-1"></i>Download PDF
                    </a>
                    <button type="button" class="btn btn-primary" id="newSaleBtn">
                        <i class="bi bi-plus me-1"></i>New Sale
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let cart = [];

            // Helper function
            function formatNumber(num) {
                return Math.round(num).toLocaleString();
            }

            // Category filtering
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active', 'btn-primary'));
                    document.querySelectorAll('.category-btn').forEach(b => b.classList.add('btn-outline-secondary'));
                    this.classList.add('active', 'btn-primary');
                    this.classList.remove('btn-outline-secondary');

                    const category = this.dataset.category;
                    filterProducts(category, document.getElementById('productSearch').value);
                });
            });

            // Product search
            document.getElementById('productSearch').addEventListener('input', function() {
                const activeCategory = document.querySelector('.category-btn.active')?.dataset.category || '';
                filterProducts(activeCategory, this.value);
            });

            function filterProducts(category, search) {
                const items = document.querySelectorAll('.product-item');
                const searchLower = search.toLowerCase();

                items.forEach(item => {
                    const matchCategory = !category || item.dataset.category === category;
                    const matchSearch = !search ||
                        item.dataset.name.toLowerCase().includes(searchLower);

                    item.style.display = matchCategory && matchSearch ? 'block' : 'none';
                });
            }

            // Quantity modal variables
            let selectedProduct = null;
            const quantityModal = new bootstrap.Modal(document.getElementById('quantityModal'));

            // Add to cart - show quantity modal
            document.querySelectorAll('.product-item').forEach(item => {
                item.addEventListener('click', function() {
                    const id = parseInt(this.dataset.id);
                    const name = this.dataset.name;
                    const price = parseFloat(this.dataset.price);
                    const tax = parseFloat(this.dataset.tax);
                    const stock = parseInt(this.dataset.stock);

                    const existingItem = cart.find(i => i.id === id);
                    const currentQty = existingItem ? existingItem.quantity : 0;
                    const availableStock = stock - currentQty;

                    if (availableStock <= 0) {
                        alert('Not enough stock available');
                        return;
                    }

                    // Store selected product
                    selectedProduct = { id, name, price, tax, stock, availableStock };

                    // Update modal
                    document.getElementById('quantityProductName').textContent = name;
                    document.getElementById('quantityProductPrice').textContent = 'TZS ' + formatNumber(price);
                    document.getElementById('quantityProductStock').textContent = availableStock;
                    document.getElementById('quantityInput').value = 1;
                    document.getElementById('quantityInput').max = availableStock;
                    updateQuantitySubtotal();

                    // Show modal
                    quantityModal.show();

                    // Focus on quantity input
                    setTimeout(() => document.getElementById('quantityInput').select(), 300);
                });
            });

            // Adjust quantity in modal
            window.adjustModalQty = function(change) {
                const input = document.getElementById('quantityInput');
                let newVal = parseInt(input.value) + change;
                newVal = Math.max(1, Math.min(newVal, selectedProduct.availableStock));
                input.value = newVal;
                updateQuantitySubtotal();
            };

            // Update subtotal when quantity changes
            document.getElementById('quantityInput').addEventListener('input', updateQuantitySubtotal);

            function updateQuantitySubtotal() {
                if (!selectedProduct) return;
                const qty = parseInt(document.getElementById('quantityInput').value) || 1;
                const subtotal = selectedProduct.price * qty;
                document.getElementById('quantitySubtotal').textContent = 'TZS ' + formatNumber(subtotal);
            }

            // Function to add selected product to cart
            function doAddToCart() {
                if (!selectedProduct) {
                    console.log('No selected product');
                    return;
                }

                const qty = parseInt(document.getElementById('quantityInput').value) || 1;
                if (qty > selectedProduct.availableStock) {
                    alert('Not enough stock available');
                    return;
                }

                const existingItem = cart.find(i => i.id === selectedProduct.id);

                if (existingItem) {
                    existingItem.quantity += qty;
                } else {
                    cart.push({
                        id: selectedProduct.id,
                        name: selectedProduct.name,
                        price: selectedProduct.price,
                        tax: selectedProduct.tax,
                        quantity: qty,
                        stock: selectedProduct.stock
                    });
                }

                renderCart();

                // Properly close modal and remove backdrop
                quantityModal.hide();

                // Force remove any stuck backdrop
                setTimeout(() => {
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, 300);

                selectedProduct = null;
            }

            // Add to cart button event listener
            document.getElementById('addToCartBtn').addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                doAddToCart();
            });

            // Global function to add to cart (for onclick backup)
            window.addSelectedToCart = doAddToCart;

            function renderCart() {
                const container = document.getElementById('cartItems');

                if (cart.length === 0) {
                    container.innerHTML = `
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-cart display-4 d-block mb-2"></i>
                            Click products to add them to cart
                        </div>
                    `;
                    updateTotals();
                    return;
                }

                container.innerHTML = cart.map((item, index) => `
                    <div class="cart-item">
                        <div class="cart-item-details">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">TZS ${formatNumber(item.price)} x ${item.quantity}</div>
                        </div>
                        <div class="cart-item-qty">
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, -1)">-</button>
                            <input type="number" class="form-control form-control-sm" value="${item.quantity}"
                                   min="1" max="${item.stock}" onchange="setQuantity(${index}, this.value)">
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, 1)">+</button>
                        </div>
                        <div class="cart-item-total">TZS ${formatNumber(item.price * item.quantity)}</div>
                        <button class="btn btn-sm btn-outline-danger ms-2" onclick="removeItem(${index})">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `).join('');

                updateTotals();
            }

            window.updateQuantity = function(index, change) {
                const item = cart[index];
                const newQty = item.quantity + change;

                if (newQty < 1) {
                    cart.splice(index, 1);
                } else if (newQty > item.stock) {
                    alert('Not enough stock available');
                    return;
                } else {
                    item.quantity = newQty;
                }

                renderCart();
            };

            window.setQuantity = function(index, value) {
                const item = cart[index];
                const newQty = parseInt(value) || 1;

                if (newQty > item.stock) {
                    alert('Not enough stock available');
                    renderCart();
                    return;
                }

                item.quantity = Math.max(1, newQty);
                renderCart();
            };

            window.removeItem = function(index) {
                cart.splice(index, 1);
                renderCart();
            };

            function updateTotals() {
                let subtotal = 0;
                let tax = 0;
                let totalItems = 0;

                cart.forEach(item => {
                    const itemSubtotal = item.price * item.quantity;
                    subtotal += itemSubtotal;
                    tax += itemSubtotal * (item.tax / 100);
                    totalItems += item.quantity;
                });

                const total = subtotal + tax;

                document.getElementById('cartSubtotal').textContent = 'TZS ' + formatNumber(subtotal);
                document.getElementById('cartTax').textContent = 'TZS ' + formatNumber(tax);
                document.getElementById('cartTotal').textContent = 'TZS ' + formatNumber(total);

                const hasItems = cart.length > 0;
                document.getElementById('clearCartBtn').disabled = !hasItems;
                document.getElementById('holdBtn').disabled = !hasItems;
                document.getElementById('payBtn').disabled = !hasItems;

                // Update mobile cart bar
                if (typeof updateMobileCart === 'function') {
                    updateMobileCart(cart, subtotal, tax, total, 0);
                }
            }

            // Clear cart
            document.getElementById('clearCartBtn').addEventListener('click', function() {
                if (confirm('Clear all items from cart?')) {
                    cart = [];
                    renderCart();
                }
            });

            // Open payment modal function (for mobile)
            window.openPaymentModal = function() {
                // Calculate total without discount
                const total = cart.reduce((sum, item) => {
                    const itemSubtotal = item.price * item.quantity;
                    return sum + itemSubtotal + (itemSubtotal * item.tax / 100);
                }, 0);

                // Reset discount and show totals
                document.getElementById('paymentDiscount').value = 0;
                document.getElementById('paymentTotal').textContent = 'TZS ' + formatNumber(total);
                document.getElementById('finalPaymentAmount').textContent = 'TZS ' + formatNumber(total);
                document.getElementById('amountPaid').value = Math.ceil(total);
                document.getElementById('changeSection').style.display = 'none';

                // Close mobile cart sheet if open
                document.getElementById('mobileCartBar')?.classList.remove('expanded');

                const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                modal.show();
            };

            // Handle discount change in payment modal
            document.getElementById('paymentDiscount').addEventListener('input', function() {
                const total = cart.reduce((sum, item) => {
                    const itemSubtotal = item.price * item.quantity;
                    return sum + itemSubtotal + (itemSubtotal * item.tax / 100);
                }, 0);

                const discount = parseFloat(this.value) || 0;
                const finalAmount = Math.max(0, total - discount);

                document.getElementById('finalPaymentAmount').textContent = 'TZS ' + formatNumber(finalAmount);
                document.getElementById('amountPaid').value = Math.ceil(finalAmount);

                // Trigger change calculation
                document.getElementById('amountPaid').dispatchEvent(new Event('input'));
            });

            // Pay button (desktop)
            document.getElementById('payBtn').addEventListener('click', function() {
                window.openPaymentModal();
            });

            // Amount paid change
            document.getElementById('amountPaid').addEventListener('input', function() {
                const total = cart.reduce((sum, item) => {
                    const itemSubtotal = item.price * item.quantity;
                    return sum + itemSubtotal + (itemSubtotal * item.tax / 100);
                }, 0);
                const discount = parseFloat(document.getElementById('paymentDiscount').value) || 0;
                const finalAmount = Math.max(0, total - discount);

                const paid = parseFloat(this.value) || 0;
                const change = paid - finalAmount;

                if (change >= 0) {
                    document.getElementById('changeSection').style.display = 'block';
                    document.getElementById('changeAmount').textContent = 'TZS ' + formatNumber(change);
                } else {
                    document.getElementById('changeSection').style.display = 'none';
                }
            });

            // Complete payment
            document.getElementById('completePaymentBtn').addEventListener('click', function() {
                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';

                const discount = parseFloat(document.getElementById('paymentDiscount').value) || 0;
                const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;

                fetch('{{ route("pos.checkout") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        items: cart.map(item => ({
                            product_id: item.id,
                            quantity: item.quantity
                        })),
                        payment_method: document.querySelector('input[name="paymentMethod"]:checked').value,
                        amount_paid: amountPaid,
                        discount_amount: discount,
                        customer_name: document.getElementById('customerName').value,
                        customer_phone: document.getElementById('customerPhone').value,
                        customer_tin: document.getElementById('customerTin').value,
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();

                        document.getElementById('transactionNumber').textContent = data.transaction.transaction_number;
                        document.getElementById('successChange').textContent =
                            data.transaction.change_given > 0 ? 'Change: TZS ' + formatNumber(data.transaction.change_given) : '';
                        document.getElementById('printReceiptBtn').href = data.receipt_url;
                        document.getElementById('downloadPdfBtn').href = data.pdf_url;

                        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                        successModal.show();

                        // Reset cart
                        cart = [];
                        document.getElementById('customerName').value = '';
                        document.getElementById('customerPhone').value = '';
                        document.getElementById('customerTin').value = '';
                        renderCart();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred. Please try again.');
                    console.error(error);
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Complete Sale';
                });
            });

            // New sale button
            document.getElementById('newSaleBtn').addEventListener('click', function() {
                bootstrap.Modal.getInstance(document.getElementById('successModal')).hide();
                document.getElementById('productSearch').focus();
            });

            // Initialize
            renderCart();
        });
    </script>
</x-pos-layout>
