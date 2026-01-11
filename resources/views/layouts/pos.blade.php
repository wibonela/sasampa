<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>POS - {{ config('app.name', 'Sasampa POS') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/favicon.svg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { box-sizing: border-box; }
        body {
            overflow: hidden;
            background: var(--apple-gray-6);
            margin: 0;
            padding: 0;
        }
        .pos-header {
            background: var(--apple-bg);
            border-bottom: 1px solid var(--apple-border);
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            height: 56px;
        }
        .pos-header .logo {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 15px;
            color: var(--apple-text);
        }
        .pos-header .logo i {
            font-size: 20px;
            color: var(--apple-blue);
        }
        .pos-main {
            height: calc(100vh - 56px);
            margin-top: 56px;
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 0;
        }
        .pos-products-panel {
            background: var(--apple-gray-6);
            padding: 16px;
            overflow-y: auto;
            height: 100%;
        }
        .pos-cart-panel {
            background: var(--apple-bg);
            border-left: 1px solid var(--apple-border);
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .search-box {
            margin-bottom: 12px;
        }
        .search-box .form-control {
            background: var(--apple-bg);
            border-radius: 10px;
            padding: 12px 16px;
            padding-left: 44px;
            font-size: 16px;
        }
        .search-box .input-group-text {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--apple-gray-1);
            z-index: 10;
        }
        .search-box .input-group {
            position: relative;
        }
        .category-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
            overflow-x: auto;
            padding-bottom: 4px;
            -webkit-overflow-scrolling: touch;
        }
        .category-tabs::-webkit-scrollbar { display: none; }
        .category-tabs .btn {
            border-radius: 20px;
            font-size: 13px;
            padding: 8px 16px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
        }
        .product-item {
            background: var(--apple-bg);
            border: 1px solid var(--apple-border);
            border-radius: 12px;
            padding: 12px 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s ease;
        }
        .product-item:hover, .product-item:active {
            border-color: var(--apple-blue);
            box-shadow: var(--apple-shadow-lg);
        }
        .product-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .product-item .name {
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--apple-text);
        }
        .product-item .price {
            color: var(--apple-blue);
            font-weight: 600;
            font-size: 13px;
        }
        .product-item small {
            font-size: 10px;
        }
        .cart-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--apple-border);
            font-weight: 600;
            font-size: 15px;
            color: var(--apple-text);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .cart-count {
            background: var(--apple-blue);
            color: #fff;
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 10px;
        }
        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 8px;
        }
        .cart-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 4px;
            background: var(--apple-gray-6);
        }
        .cart-item-details {
            flex: 1;
            min-width: 0;
        }
        .cart-item-name {
            font-weight: 500;
            font-size: 13px;
            color: var(--apple-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .cart-item-price {
            color: var(--apple-text-secondary);
            font-size: 11px;
        }
        .cart-item-qty {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .cart-item-qty input {
            width: 40px;
            text-align: center;
            padding: 4px;
            font-size: 13px;
            border-radius: 6px;
        }
        .cart-item-qty .btn {
            padding: 4px 10px;
            font-size: 14px;
        }
        .cart-item-total {
            font-weight: 600;
            min-width: 65px;
            text-align: right;
            font-size: 12px;
            color: var(--apple-text);
        }
        .cart-summary {
            padding: 14px 16px;
            border-top: 1px solid var(--apple-border);
            background: var(--apple-gray-6);
        }
        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            font-size: 13px;
            color: var(--apple-text-secondary);
        }
        .cart-summary-row.total {
            font-size: 18px;
            font-weight: 700;
            color: var(--apple-text);
            border-top: 1px solid var(--apple-border);
            padding-top: 10px;
            margin-top: 10px;
            margin-bottom: 0;
        }
        .cart-actions {
            padding: 12px 16px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .cart-actions .btn-pay {
            grid-column: span 2;
            padding: 14px;
            font-size: 16px;
        }
        .modal-content {
            border: none;
            border-radius: 16px;
        }
        .modal-header {
            border-bottom: 1px solid var(--apple-border);
            padding: 16px 20px;
        }
        .modal-title {
            font-weight: 600;
            font-size: 17px;
        }
        .modal-body {
            padding: 20px;
        }
        .modal-footer {
            border-top: 1px solid var(--apple-border);
            padding: 16px 20px;
        }

        /* Mobile Floating Cart Bar */
        .mobile-cart-bar {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--apple-bg);
            border-top: 1px solid var(--apple-border);
            z-index: 200;
            padding-bottom: env(safe-area-inset-bottom);
        }
        .mobile-cart-bar .cart-bar-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            cursor: pointer;
        }
        .mobile-cart-bar .cart-bar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .mobile-cart-bar .cart-bar-icon {
            position: relative;
            font-size: 24px;
            color: var(--apple-blue);
        }
        .mobile-cart-bar .cart-bar-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--apple-red);
            color: #fff;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }
        .mobile-cart-bar .cart-bar-info {
            display: flex;
            flex-direction: column;
        }
        .mobile-cart-bar .cart-bar-items {
            font-size: 13px;
            color: var(--apple-text-secondary);
        }
        .mobile-cart-bar .cart-bar-total {
            font-size: 17px;
            font-weight: 700;
            color: var(--apple-text);
        }
        .mobile-cart-bar .cart-bar-pay {
            padding: 10px 24px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 10px;
        }
        .mobile-cart-bar .expand-icon {
            font-size: 20px;
            color: var(--apple-gray-1);
            transition: transform 0.3s;
        }
        .mobile-cart-bar.expanded .expand-icon {
            transform: rotate(180deg);
        }

        /* Mobile Cart Sheet */
        .mobile-cart-sheet {
            display: none;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: var(--apple-bg);
            border-top: 1px solid var(--apple-border);
        }
        .mobile-cart-bar.expanded .mobile-cart-sheet {
            max-height: 50vh;
            overflow-y: auto;
        }
        .mobile-cart-sheet .cart-items-mobile {
            padding: 12px;
            max-height: calc(50vh - 80px);
            overflow-y: auto;
        }
        .mobile-cart-sheet .cart-summary-mobile {
            padding: 12px 16px;
            background: var(--apple-gray-6);
            border-top: 1px solid var(--apple-border);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .pos-header {
                padding: 10px 12px;
            }
            .pos-header .logo span {
                display: none;
            }
            .pos-header .d-flex span {
                display: none;
            }
            .pos-main {
                grid-template-columns: 1fr;
                height: calc(100vh - 56px - 80px);
                margin-bottom: 80px;
            }
            .pos-products-panel {
                display: block;
                padding: 12px;
                padding-bottom: 100px;
            }
            .pos-cart-panel {
                display: none;
            }
            .mobile-cart-bar {
                display: block;
            }
            .mobile-cart-bar .mobile-cart-sheet {
                display: block;
            }
            .product-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
            }
            .product-item {
                padding: 10px 6px;
            }
            .product-item img {
                width: 50px;
                height: 50px;
            }
            .product-item .name {
                font-size: 11px;
            }
            .product-item .price {
                font-size: 12px;
            }
            .category-tabs .btn {
                font-size: 12px;
                padding: 6px 12px;
            }
        }

        @media (max-width: 380px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Added product animation */
        @keyframes addToCart {
            0% { transform: scale(1); }
            50% { transform: scale(0.95); background: var(--apple-blue); }
            100% { transform: scale(1); }
        }
        .product-item.adding {
            animation: addToCart 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- POS Header -->
    <header class="pos-header">
        <div class="logo">
            <svg width="24" height="24" viewBox="0 0 32 32"><defs><linearGradient id="posLogoGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#FF2D20"/><stop offset="100%" style="stop-color:#E53E3E"/></linearGradient></defs><rect width="32" height="32" rx="6" fill="url(#posLogoGrad)"/><rect x="8" y="8" width="4" height="4" rx="1" fill="#fff"/><rect x="14" y="8" width="4" height="4" rx="1" fill="#fff"/><rect x="20" y="8" width="4" height="4" rx="1" fill="#fff"/><rect x="8" y="14" width="4" height="4" rx="1" fill="#fff"/><rect x="14" y="14" width="4" height="4" rx="1" fill="#fff"/><rect x="20" y="14" width="4" height="4" rx="1" fill="#fff"/><rect x="8" y="20" width="16" height="4" rx="1" fill="#fff"/></svg>
            <span>Sasampa POS</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-secondary" style="font-size: 13px;">
                <i class="bi bi-person-circle me-1"></i>
                {{ Auth::user()->name }}
            </span>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i>
                <span class="d-none d-md-inline ms-1">Dashboard</span>
            </a>
        </div>
    </header>

    <!-- POS Main -->
    {{ $slot }}

    <!-- Mobile Floating Cart Bar -->
    <div class="mobile-cart-bar" id="mobileCartBar">
        <!-- Expandable Cart Sheet -->
        <div class="mobile-cart-sheet">
            <div class="cart-items-mobile" id="mobileCartItems">
                <!-- Cart items will be rendered here -->
            </div>
            <div class="cart-summary-mobile">
                <div class="cart-summary-row">
                    <span>Discount:</span>
                    <div class="input-group input-group-sm" style="width: 120px;">
                        <span class="input-group-text">TZS</span>
                        <input type="number" class="form-control form-control-sm" id="mobileDiscountAmount" value="0" min="0">
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Bar Summary (always visible) -->
        <div class="cart-bar-summary" onclick="toggleCartSheet()">
            <div class="cart-bar-left">
                <div class="cart-bar-icon">
                    <i class="bi bi-cart3"></i>
                    <span class="cart-bar-badge" id="mobileCartBadge">0</span>
                </div>
                <div class="cart-bar-info">
                    <span class="cart-bar-items" id="mobileCartItemsCount">0 items</span>
                    <span class="cart-bar-total" id="mobileCartTotal">TZS 0</span>
                </div>
                <i class="bi bi-chevron-up expand-icon"></i>
            </div>
            <button class="btn btn-success cart-bar-pay" id="mobilePayBtn" disabled onclick="event.stopPropagation(); openPaymentModal();">
                <i class="bi bi-cash me-1"></i>Pay
            </button>
        </div>
    </div>

    <script>
        function toggleCartSheet() {
            const cartBar = document.getElementById('mobileCartBar');
            cartBar.classList.toggle('expanded');
        }

        function updateMobileCart(cart, subtotal, tax, total, discount) {
            const itemsCount = cart.reduce((sum, item) => sum + item.quantity, 0);

            // Update badge and summary
            document.getElementById('mobileCartBadge').textContent = itemsCount;
            document.getElementById('mobileCartItemsCount').textContent = itemsCount + ' item' + (itemsCount !== 1 ? 's' : '');
            document.getElementById('mobileCartTotal').textContent = 'TZS ' + Math.round(total).toLocaleString();

            // Enable/disable pay button
            document.getElementById('mobilePayBtn').disabled = cart.length === 0;

            // Render cart items
            const container = document.getElementById('mobileCartItems');
            if (cart.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-cart display-6 d-block mb-2"></i>Cart is empty</div>';
            } else {
                container.innerHTML = cart.map((item, index) => `
                    <div class="cart-item">
                        <div class="cart-item-details">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">TZS ${Math.round(item.price).toLocaleString()} x ${item.quantity}</div>
                        </div>
                        <div class="cart-item-qty">
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, -1)">-</button>
                            <span style="min-width: 30px; text-align: center;">${item.quantity}</span>
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, 1)">+</button>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeItem(${index})">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `).join('');
            }

            // Sync discount
            const mobileDiscount = document.getElementById('mobileDiscountAmount');
            const desktopDiscount = document.getElementById('discountAmount');
            if (mobileDiscount && desktopDiscount) {
                mobileDiscount.value = desktopDiscount.value;
            }
        }

        // Sync mobile discount with desktop
        document.getElementById('mobileDiscountAmount')?.addEventListener('input', function() {
            const desktopDiscount = document.getElementById('discountAmount');
            if (desktopDiscount) {
                desktopDiscount.value = this.value;
                desktopDiscount.dispatchEvent(new Event('input'));
            }
        });

        // Make functions globally available
        window.updateMobileCart = updateMobileCart;
        window.toggleCartSheet = toggleCartSheet;
    </script>

    <!-- Sanduku Feedback -->
    @include('components.sanduku')
</body>
</html>
