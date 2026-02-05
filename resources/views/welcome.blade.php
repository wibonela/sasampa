<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sasampa - Point of Sale for Modern Businesses</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; color: #1a1a1a; line-height: 1.6; background: #fff; -webkit-font-smoothing: antialiased; }

        /* Navigation */
        .nav { position: fixed; top: 0; left: 0; right: 0; z-index: 100; padding: 20px 0; transition: all 0.3s; }
        .nav.scrolled { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); box-shadow: 0 1px 0 rgba(0,0,0,0.05); }
        .nav-container { max-width: 1200px; margin: 0 auto; padding: 0 24px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 20px; font-weight: 700; color: #1a1a1a; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .logo-mark { width: 32px; height: 32px; background: #1a1a1a; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .logo-mark svg { width: 18px; height: 18px; }
        .nav-links { display: flex; align-items: center; gap: 32px; }
        .nav-links a { color: #666; text-decoration: none; font-size: 14px; font-weight: 500; transition: color 0.2s; }
        .nav-links a:hover { color: #1a1a1a; }
        .nav-btn { background: #1a1a1a; color: #fff !important; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; text-decoration: none; transition: all 0.2s; }
        .nav-btn:hover { background: #333; }

        /* Hero */
        .hero { min-height: 100vh; display: flex; align-items: center; padding: 120px 24px 80px; background: linear-gradient(180deg, #fafafa 0%, #fff 100%); }
        .hero-container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; }
        .hero-content { max-width: 520px; }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: #f0f0f0; padding: 6px 14px; border-radius: 100px; font-size: 13px; font-weight: 500; color: #666; margin-bottom: 24px; }
        .hero-badge-dot { width: 6px; height: 6px; background: #22c55e; border-radius: 50%; }
        .hero-title { font-size: 56px; font-weight: 700; line-height: 1.1; letter-spacing: -0.02em; margin-bottom: 24px; color: #0a0a0a; }
        .hero-desc { font-size: 18px; color: #666; margin-bottom: 40px; line-height: 1.7; }
        .hero-actions { display: flex; gap: 16px; flex-wrap: wrap; }
        .btn-primary { background: #1a1a1a; color: #fff; padding: 16px 32px; border-radius: 12px; font-size: 15px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; transition: all 0.2s; }
        .btn-primary:hover { background: #333; transform: translateY(-2px); }
        .btn-secondary { background: #fff; color: #1a1a1a; padding: 16px 32px; border-radius: 12px; font-size: 15px; font-weight: 600; text-decoration: none; border: 1px solid #e5e5e5; display: inline-flex; align-items: center; gap: 10px; transition: all 0.2s; }
        .btn-secondary:hover { border-color: #ccc; background: #fafafa; }

        /* Device Mockups */
        .hero-visual { display: flex; justify-content: center; align-items: center; position: relative; }
        .mockup-wrapper { position: relative; }

        /* Desktop Mockup - Show on desktop */
        .desktop-mockup { display: block; }
        .laptop-frame { width: 580px; background: #1a1a1a; border-radius: 12px 12px 0 0; padding: 8px 8px 0; box-shadow: 0 50px 100px -20px rgba(0,0,0,0.25); }
        .laptop-camera { width: 8px; height: 8px; background: #333; border-radius: 50%; margin: 0 auto 8px; }
        .laptop-screen { width: 100%; height: 340px; background: #fff; border-radius: 4px 4px 0 0; overflow: hidden; }
        .laptop-base { width: 650px; height: 14px; background: linear-gradient(180deg, #d1d5db 0%, #9ca3af 100%); border-radius: 0 0 12px 12px; margin: 0 auto; position: relative; }
        .laptop-base::before { content: ''; position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 120px; height: 4px; background: #9ca3af; border-radius: 0 0 4px 4px; }
        .laptop-notch { width: 180px; height: 24px; background: #1a1a1a; border-radius: 0 0 12px 12px; margin: 0 auto; margin-top: -1px; }

        /* Phone Mockup - Show on mobile */
        .phone-mockup { display: none; }
        .phone-frame { width: 280px; height: 580px; background: #0a0a0a; border-radius: 44px; padding: 12px; box-shadow: 0 50px 100px -20px rgba(0,0,0,0.25), 0 30px 60px -30px rgba(0,0,0,0.3), inset 0 0 0 2px #333; position: relative; }
        .phone-notch { position: absolute; top: 12px; left: 50%; transform: translateX(-50%); width: 100px; height: 28px; background: #0a0a0a; border-radius: 0 0 20px 20px; z-index: 10; }
        .phone-screen { width: 100%; height: 100%; background: #fff; border-radius: 32px; overflow: hidden; position: relative; }

        /* POS App UI */
        .app-header { background: #1a1a1a; padding: 16px; }
        .app-header.phone-header { padding: 44px 16px 16px; }
        .app-header-content { display: flex; justify-content: space-between; align-items: center; }
        .app-title { color: #fff; font-size: 15px; font-weight: 600; }
        .app-user { color: #888; font-size: 12px; }
        .app-cart-badge { background: #22c55e; color: #fff; font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 100px; }

        /* Desktop POS Layout */
        .desktop-pos { display: flex; height: 100%; }
        .pos-sidebar { width: 200px; background: #f8f8f8; border-right: 1px solid #eee; padding: 12px; }
        .pos-sidebar-item { padding: 10px 12px; border-radius: 8px; font-size: 12px; color: #666; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; }
        .pos-sidebar-item.active { background: #1a1a1a; color: #fff; }
        .pos-sidebar-item svg { width: 16px; height: 16px; }
        .pos-main { flex: 1; display: flex; flex-direction: column; }
        .pos-content { flex: 1; display: flex; }
        .pos-products-area { flex: 1; padding: 16px; background: #fff; }
        .pos-cart-area { width: 240px; background: #f8f8f8; border-left: 1px solid #eee; padding: 16px; display: flex; flex-direction: column; }

        .pos-search { background: #f5f5f5; padding: 10px 14px; border-radius: 8px; font-size: 12px; color: #999; margin-bottom: 12px; border: 1px solid #eee; }
        .pos-categories { display: flex; gap: 6px; margin-bottom: 16px; flex-wrap: wrap; }
        .pos-cat { padding: 6px 12px; border-radius: 100px; font-size: 11px; font-weight: 500; }
        .pos-cat.active { background: #1a1a1a; color: #fff; }
        .pos-cat.inactive { background: #f0f0f0; color: #666; }
        .pos-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .pos-item { background: #fff; border: 1px solid #eee; border-radius: 10px; padding: 12px; text-align: center; cursor: pointer; transition: all 0.2s; }
        .pos-item:hover { border-color: #22c55e; box-shadow: 0 4px 12px rgba(34,197,94,0.15); }
        .pos-item-img { width: 100%; aspect-ratio: 1; background: #f8f8f8; border-radius: 6px; margin-bottom: 8px; display: flex; align-items: center; justify-content: center; font-size: 28px; }
        .pos-item-name { font-size: 11px; font-weight: 600; color: #1a1a1a; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pos-item-price { font-size: 12px; font-weight: 700; color: #22c55e; }

        .cart-title { font-size: 13px; font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
        .cart-items { flex: 1; overflow-y: auto; }
        .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #eee; font-size: 11px; }
        .cart-item-name { font-weight: 500; }
        .cart-item-price { color: #666; }
        .cart-total { padding-top: 12px; border-top: 2px solid #1a1a1a; margin-top: auto; }
        .cart-total-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; }
        .cart-total-row.final { font-size: 14px; font-weight: 700; }
        .cart-checkout-btn { background: #22c55e; color: #fff; border: none; padding: 12px; border-radius: 8px; font-size: 13px; font-weight: 600; width: 100%; margin-top: 12px; cursor: pointer; }

        /* Mobile POS App UI */
        .app-search { background: #f5f5f5; margin: 12px; padding: 12px 16px; border-radius: 10px; font-size: 13px; color: #999; }
        .app-categories { display: flex; gap: 8px; padding: 0 12px 12px; overflow-x: auto; }
        .app-cat { padding: 8px 16px; border-radius: 100px; font-size: 12px; font-weight: 500; white-space: nowrap; }
        .app-cat.active { background: #1a1a1a; color: #fff; }
        .app-cat.inactive { background: #f5f5f5; color: #666; }
        .app-products { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 12px; }
        .app-product { background: #fff; border: 1px solid #eee; border-radius: 12px; padding: 12px; text-align: center; }
        .app-product-img { width: 100%; aspect-ratio: 1; background: linear-gradient(135deg, #f8f8f8 0%, #eee 100%); border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .app-product-name { font-size: 12px; font-weight: 600; color: #1a1a1a; margin-bottom: 4px; }
        .app-product-price { font-size: 13px; font-weight: 700; color: #22c55e; }
        .app-checkout { position: absolute; bottom: 0; left: 0; right: 0; background: #22c55e; margin: 12px; padding: 14px; border-radius: 12px; text-align: center; }
        .app-checkout-text { color: #fff; font-size: 13px; font-weight: 600; }
        .app-checkout-amount { color: #fff; font-size: 11px; opacity: 0.9; }

        /* Product Icons */
        .product-icon { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 11px; color: #fff; border-radius: 6px; }
        .product-icon.cola { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); }
        .product-icon.water { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .product-icon.chips { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .product-icon.chapati { background: linear-gradient(135deg, #a16207 0%, #854d0e 100%); }
        .product-icon.juice { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); }
        .product-icon.bread { background: linear-gradient(135deg, #ca8a04 0%, #a16207 100%); }

        /* Floating Elements */
        .float-card { position: absolute; background: #fff; border-radius: 16px; padding: 16px 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1), 0 0 0 1px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 12px; }
        .float-card-1 { top: 20px; right: -30px; }
        .float-card-2 { bottom: 60px; left: -40px; }
        .float-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .float-icon.green { background: #dcfce7; color: #22c55e; }
        .float-icon.blue { background: #dbeafe; color: #3b82f6; }
        .float-text { font-size: 13px; }
        .float-text strong { display: block; color: #1a1a1a; font-weight: 600; }
        .float-text span { color: #666; font-size: 12px; }

        /* Features Section */
        .features { padding: 120px 24px; background: #fff; }
        .features-container { max-width: 1200px; margin: 0 auto; }
        .section-header { text-align: center; max-width: 600px; margin: 0 auto 80px; }
        .section-label { font-size: 13px; font-weight: 600; color: #22c55e; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 16px; }
        .section-title { font-size: 40px; font-weight: 700; color: #0a0a0a; margin-bottom: 20px; letter-spacing: -0.02em; }
        .section-desc { font-size: 17px; color: #666; }
        .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; }
        .feature-item { padding: 32px; border-radius: 20px; background: #fafafa; transition: all 0.3s; }
        .feature-item:hover { background: #f5f5f5; }
        .feature-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 22px; }
        .feature-icon.fi-1 { background: #fee2e2; }
        .feature-icon.fi-2 { background: #dbeafe; }
        .feature-icon.fi-3 { background: #dcfce7; }
        .feature-icon.fi-4 { background: #fef3c7; }
        .feature-icon.fi-5 { background: #ede9fe; }
        .feature-icon.fi-6 { background: #fce7f3; }
        .feature-title { font-size: 18px; font-weight: 600; color: #1a1a1a; margin-bottom: 10px; }
        .feature-desc { font-size: 14px; color: #666; line-height: 1.6; }

        /* Stats Section */
        .stats { padding: 80px 24px; background: #0a0a0a; }
        .stats-container { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 48px; text-align: center; }
        .stat-number { font-size: 48px; font-weight: 700; color: #fff; margin-bottom: 8px; }
        .stat-label { font-size: 14px; color: #888; }

        /* CTA Section */
        .cta { padding: 120px 24px; background: linear-gradient(180deg, #fff 0%, #fafafa 100%); }
        .cta-container { max-width: 800px; margin: 0 auto; text-align: center; }
        .cta-title { font-size: 44px; font-weight: 700; color: #0a0a0a; margin-bottom: 20px; letter-spacing: -0.02em; }
        .cta-desc { font-size: 18px; color: #666; margin-bottom: 40px; }
        .cta-actions { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; }

        /* Footer */
        .footer { padding: 48px 24px; background: #0a0a0a; }
        .footer-container { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 24px; }
        .footer-brand { display: flex; align-items: center; gap: 10px; }
        .footer-logo { width: 28px; height: 28px; background: #fff; border-radius: 6px; display: flex; align-items: center; justify-content: center; }
        .footer-logo svg { width: 16px; height: 16px; }
        .footer-name { color: #fff; font-size: 16px; font-weight: 600; }
        .footer-copy { color: #666; font-size: 14px; }

        /* How It Works */
        .how-it-works { padding: 120px 24px; background: #fafafa; }
        .steps-grid { display: flex; align-items: flex-start; justify-content: center; gap: 0; max-width: 900px; margin: 0 auto; }
        .step-item { display: flex; flex-direction: column; align-items: center; text-align: center; flex: 1; }
        .step-number { width: 64px; height: 64px; border-radius: 50%; background: #1a1a1a; color: #fff; font-size: 24px; font-weight: 700; display: flex; align-items: center; justify-content: center; margin-bottom: 24px; }
        .step-content { max-width: 200px; }
        .step-title { font-size: 18px; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; }
        .step-desc { font-size: 14px; color: #666; line-height: 1.6; }
        .step-connector { width: 80px; height: 2px; background: #e5e5e5; margin-top: 32px; }

        /* Why Choose Us */
        .why-us { padding: 120px 24px; background: #fff; }
        .why-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; }
        .why-item { text-align: center; padding: 32px 24px; }
        .why-icon { width: 64px; height: 64px; border-radius: 16px; background: #f5f5f5; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #1a1a1a; }
        .why-title { font-size: 18px; font-weight: 600; color: #1a1a1a; margin-bottom: 10px; }
        .why-desc { font-size: 14px; color: #666; line-height: 1.6; }

        /* Testimonials */
        .testimonials { padding: 120px 24px; background: #fafafa; }
        .testimonials-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; }
        .testimonial-card { background: #fff; border-radius: 20px; padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .testimonial-stars { display: flex; gap: 4px; margin-bottom: 20px; }
        .testimonial-text { font-size: 15px; color: #444; line-height: 1.7; margin-bottom: 24px; font-style: italic; }
        .testimonial-author { display: flex; align-items: center; gap: 12px; }
        .testimonial-avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #1a1a1a 0%, #333 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; }
        .testimonial-name { font-size: 15px; font-weight: 600; color: #1a1a1a; }
        .testimonial-role { font-size: 13px; color: #888; }

        /* Share Section */
        .share-section { padding: 80px 24px; background: #fff; }
        .share-card { background: linear-gradient(135deg, #1a1a1a 0%, #333 100%); border-radius: 24px; padding: 60px 40px; text-align: center; }
        .share-title { font-size: 32px; font-weight: 700; color: #fff; margin-bottom: 12px; }
        .share-desc { font-size: 16px; color: #aaa; margin-bottom: 32px; }
        .share-buttons { display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; }
        .share-btn { display: inline-flex; align-items: center; gap: 8px; padding: 14px 24px; border-radius: 12px; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.2s; border: none; cursor: pointer; }
        .share-btn.whatsapp { background: #25D366; color: #fff; }
        .share-btn.whatsapp:hover { background: #1da851; }
        .share-btn.facebook { background: #1877F2; color: #fff; }
        .share-btn.facebook:hover { background: #0d65d9; }
        .share-btn.twitter { background: #000; color: #fff; }
        .share-btn.twitter:hover { background: #333; }
        .share-btn.copy { background: #fff; color: #1a1a1a; }
        .share-btn.copy:hover { background: #f5f5f5; }

        /* Responsive */
        @media (max-width: 968px) {
            .hero-container { grid-template-columns: 1fr; text-align: center; gap: 60px; }
            .hero-content { max-width: 100%; margin: 0 auto; }
            .hero-title { font-size: 40px; }
            .hero-actions { justify-content: center; }
            .features-grid { grid-template-columns: 1fr; }
            .stats-container { grid-template-columns: repeat(2, 1fr); }
            .float-card { display: none; }
            .nav-links { display: none; }
            .steps-grid { flex-direction: column; gap: 32px; }
            .step-connector { width: 2px; height: 40px; margin: 0; }
            .why-grid { grid-template-columns: repeat(2, 1fr); }
            .testimonials-grid { grid-template-columns: 1fr; }

            /* Show phone mockup on tablets and mobile */
            .desktop-mockup { display: none; }
            .phone-mockup { display: block; }
        }
        @media (max-width: 640px) {
            .hero-title { font-size: 32px; }
            .section-title { font-size: 28px; }
            .cta-title { font-size: 28px; }
            .phone-frame { width: 260px; height: 540px; }
            .stat-number { font-size: 36px; }
            .why-grid { grid-template-columns: 1fr; }
            .share-title { font-size: 24px; }
            .share-buttons { flex-direction: column; }
            .share-btn { justify-content: center; }
        }
    </style>
</head>
<body>
    <nav class="nav" id="nav">
        <div class="nav-container">
            <a href="/" class="logo">
                <div class="logo-mark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <path d="M9 9h6M9 15h6M9 12h6"/>
                    </svg>
                </div>
                Sasampa
            </a>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="{{ route('docs.index') }}">Documentation</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="nav-btn">Dashboard</a>
                @else
                    <a href="{{ route('login') }}">Sign in</a>
                    <a href="{{ route('company.register') }}" class="nav-btn">Get Started</a>
                @endauth
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="hero-badge-dot"></span>
                    Now available in Tanzania
                </div>
                <h1 class="hero-title">The POS system built for African businesses</h1>
                <p class="hero-desc">Accept payments, manage inventory, and grow your business with a point of sale designed for speed and simplicity. Works online and offline.</p>
                <div class="hero-actions">
                    @auth
                        <a href="{{ route('pos.index') }}" class="btn-primary">
                            Open POS
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <a href="{{ route('company.register') }}" class="btn-primary">
                            Get Started
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('login') }}" class="btn-secondary">Sign in</a>
                    @endauth
                </div>
            </div>
            <div class="hero-visual">
                <div class="mockup-wrapper">
                    <!-- Floating Cards -->
                    <div class="float-card float-card-1">
                        <div class="float-icon green">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <div class="float-text">
                            <strong>Sale Complete</strong>
                            <span>TZS 45,000</span>
                        </div>
                    </div>
                    <div class="float-card float-card-2">
                        <div class="float-icon blue">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <div class="float-text">
                            <strong>Stock Updated</strong>
                            <span>12 items synced</span>
                        </div>
                    </div>

                    <!-- Desktop/Laptop Mockup -->
                    <div class="desktop-mockup">
                        <div class="laptop-frame">
                            <div class="laptop-camera"></div>
                            <div class="laptop-screen">
                                <div class="desktop-pos">
                                    <div class="pos-sidebar">
                                        <div class="pos-sidebar-item active">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                                            Point of Sale
                                        </div>
                                        <div class="pos-sidebar-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                                            Products
                                        </div>
                                        <div class="pos-sidebar-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
                                            Reports
                                        </div>
                                        <div class="pos-sidebar-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v6M12 17v6M4.22 4.22l4.24 4.24M15.54 15.54l4.24 4.24M1 12h6M17 12h6M4.22 19.78l4.24-4.24M15.54 8.46l4.24-4.24"/></svg>
                                            Settings
                                        </div>
                                    </div>
                                    <div class="pos-main">
                                        <div class="app-header">
                                            <div class="app-header-content">
                                                <div>
                                                    <span class="app-title">Point of Sale</span>
                                                    <span class="app-user"> - Cashier: John</span>
                                                </div>
                                                <span class="app-cart-badge">3 items</span>
                                            </div>
                                        </div>
                                        <div class="pos-content">
                                            <div class="pos-products-area">
                                                <div class="pos-search">Search products by name or scan barcode...</div>
                                                <div class="pos-categories">
                                                    <span class="pos-cat active">All</span>
                                                    <span class="pos-cat inactive">Beverages</span>
                                                    <span class="pos-cat inactive">Food</span>
                                                    <span class="pos-cat inactive">Snacks</span>
                                                    <span class="pos-cat inactive">Dairy</span>
                                                </div>
                                                <div class="pos-grid">
                                                    <div class="pos-item">
                                                        <div class="pos-item-img"><div class="product-icon cola">COLA</div></div>
                                                        <div class="pos-item-name">Coca Cola 500ml</div>
                                                        <div class="pos-item-price">TZS 1,500</div>
                                                    </div>
                                                    <div class="pos-item">
                                                        <div class="pos-item-img"><div class="product-icon water">H2O</div></div>
                                                        <div class="pos-item-name">Bottled Water</div>
                                                        <div class="pos-item-price">TZS 800</div>
                                                    </div>
                                                    <div class="pos-item">
                                                        <div class="pos-item-img"><div class="product-icon chips">CHIPS</div></div>
                                                        <div class="pos-item-name">Chips Kuku</div>
                                                        <div class="pos-item-price">TZS 8,000</div>
                                                    </div>
                                                    <div class="pos-item">
                                                        <div class="pos-item-img"><div class="product-icon juice">JUICE</div></div>
                                                        <div class="pos-item-name">Azam Juice</div>
                                                        <div class="pos-item-price">TZS 1,200</div>
                                                    </div>
                                                    <div class="pos-item">
                                                        <div class="pos-item-img"><div class="product-icon bread">BREAD</div></div>
                                                        <div class="pos-item-name">Supa Loaf</div>
                                                        <div class="pos-item-price">TZS 2,500</div>
                                                    </div>
                                                    <div class="pos-item">
                                                        <div class="pos-item-img"><div class="product-icon chapati">CHAP</div></div>
                                                        <div class="pos-item-name">Chapati</div>
                                                        <div class="pos-item-price">TZS 500</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pos-cart-area">
                                                <div class="cart-title">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                                    Current Order
                                                </div>
                                                <div class="cart-items">
                                                    <div class="cart-item">
                                                        <div>
                                                            <div class="cart-item-name">Coca Cola 500ml</div>
                                                            <div class="cart-item-price">x2</div>
                                                        </div>
                                                        <div>TZS 3,000</div>
                                                    </div>
                                                    <div class="cart-item">
                                                        <div>
                                                            <div class="cart-item-name">Chips Kuku</div>
                                                            <div class="cart-item-price">x1</div>
                                                        </div>
                                                        <div>TZS 8,000</div>
                                                    </div>
                                                    <div class="cart-item">
                                                        <div>
                                                            <div class="cart-item-name">Bottled Water</div>
                                                            <div class="cart-item-price">x1</div>
                                                        </div>
                                                        <div>TZS 800</div>
                                                    </div>
                                                </div>
                                                <div class="cart-total">
                                                    <div class="cart-total-row">
                                                        <span>Subtotal</span>
                                                        <span>TZS 11,800</span>
                                                    </div>
                                                    <div class="cart-total-row">
                                                        <span>Tax (18%)</span>
                                                        <span>TZS 2,124</span>
                                                    </div>
                                                    <div class="cart-total-row final">
                                                        <span>Total</span>
                                                        <span>TZS 13,924</span>
                                                    </div>
                                                    <button class="cart-checkout-btn">Checkout</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="laptop-notch"></div>
                        <div class="laptop-base"></div>
                    </div>

                    <!-- Phone Mockup (shown on mobile) -->
                    <div class="phone-mockup">
                        <div class="phone-frame">
                            <div class="phone-notch"></div>
                            <div class="phone-screen">
                                <div class="app-header phone-header">
                                    <div class="app-header-content">
                                        <span class="app-title">Point of Sale</span>
                                        <span class="app-cart-badge">3 items</span>
                                    </div>
                                </div>
                                <div class="app-search">Search products...</div>
                                <div class="app-categories">
                                    <span class="app-cat active">All</span>
                                    <span class="app-cat inactive">Drinks</span>
                                    <span class="app-cat inactive">Food</span>
                                    <span class="app-cat inactive">Snacks</span>
                                </div>
                                <div class="app-products">
                                    <div class="app-product">
                                        <div class="app-product-img"><div class="product-icon cola" style="width:100%;height:100%;border-radius:8px;font-size:14px;">COLA</div></div>
                                        <div class="app-product-name">Coca Cola</div>
                                        <div class="app-product-price">TZS 1,500</div>
                                    </div>
                                    <div class="app-product">
                                        <div class="app-product-img"><div class="product-icon water" style="width:100%;height:100%;border-radius:8px;font-size:14px;">H2O</div></div>
                                        <div class="app-product-name">Bottled Water</div>
                                        <div class="app-product-price">TZS 800</div>
                                    </div>
                                    <div class="app-product">
                                        <div class="app-product-img"><div class="product-icon chips" style="width:100%;height:100%;border-radius:8px;font-size:14px;">CHIPS</div></div>
                                        <div class="app-product-name">Chips Kuku</div>
                                        <div class="app-product-price">TZS 8,000</div>
                                    </div>
                                    <div class="app-product">
                                        <div class="app-product-img"><div class="product-icon chapati" style="width:100%;height:100%;border-radius:8px;font-size:14px;">CHAP</div></div>
                                        <div class="app-product-name">Chapati</div>
                                        <div class="app-product-price">TZS 500</div>
                                    </div>
                                </div>
                                <div class="app-checkout">
                                    <div class="app-checkout-text">Checkout - TZS 10,800</div>
                                    <div class="app-checkout-amount">3 items in cart</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="features" id="features">
        <div class="features-container">
            <div class="section-header">
                <div class="section-label">Features</div>
                <h2 class="section-title">Everything you need to run your business</h2>
                <p class="section-desc">From quick sales to detailed reports, Sasampa gives you the tools to manage and grow your business.</p>
            </div>
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon fi-1">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    </div>
                    <h3 class="feature-title">Lightning Fast Checkout</h3>
                    <p class="feature-desc">Process sales in seconds with barcode scanning, quick search, and one-tap payments. Designed for busy environments.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon fi-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    </div>
                    <h3 class="feature-title">Smart Inventory</h3>
                    <p class="feature-desc">Track stock in real-time. Get automatic low stock alerts and manage purchase orders from one dashboard.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon fi-3">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                    <h3 class="feature-title">Business Insights</h3>
                    <p class="feature-desc">Understand your sales with daily, weekly, and monthly reports. Know your best sellers and peak hours.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon fi-4">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    </div>
                    <h3 class="feature-title">Multi-Payment Support</h3>
                    <p class="feature-desc">Accept cash, M-Pesa, Tigo Pesa, Airtel Money, and card payments. All in one system.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon fi-5">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3 class="feature-title">Staff Management</h3>
                    <p class="feature-desc">Add cashiers with PIN access. Track who made each sale and manage permissions by role.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon fi-6">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#db2777" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    </div>
                    <h3 class="feature-title">Digital Receipts</h3>
                    <p class="feature-desc">Print professional receipts or send digital copies via SMS or WhatsApp to your customers.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="stats">
        <div class="stats-container">
            <div>
                <div class="stat-number">500+</div>
                <div class="stat-label">Active Businesses</div>
            </div>
            <div>
                <div class="stat-number">1M+</div>
                <div class="stat-label">Transactions</div>
            </div>
            <div>
                <div class="stat-number">99.9%</div>
                <div class="stat-label">Uptime</div>
            </div>
            <div>
                <div class="stat-number">24/7</div>
                <div class="stat-label">Support</div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <div class="features-container">
            <div class="section-header">
                <div class="section-label">How It Works</div>
                <h2 class="section-title">Start selling in 3 simple steps</h2>
                <p class="section-desc">Get your business up and running in minutes, not days.</p>
            </div>
            <div class="steps-grid">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3 class="step-title">Register Your Business</h3>
                        <p class="step-desc">Sign up with your business details. Quick verification and you're ready to go.</p>
                    </div>
                </div>
                <div class="step-connector"></div>
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3 class="step-title">Add Your Products</h3>
                        <p class="step-desc">Enter your products with prices. Set up categories to keep things organized.</p>
                    </div>
                </div>
                <div class="step-connector"></div>
                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3 class="step-title">Start Selling</h3>
                        <p class="step-desc">Open the POS, make sales, print receipts. Watch your business grow!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-us">
        <div class="features-container">
            <div class="section-header">
                <div class="section-label">Why Sasampa?</div>
                <h2 class="section-title">Built for Tanzanian Businesses</h2>
                <p class="section-desc">We understand your needs because we built this for businesses just like yours.</p>
            </div>
            <div class="why-grid">
                <div class="why-item">
                    <div class="why-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </div>
                    <h3 class="why-title">Bilingual Support</h3>
                    <p class="why-desc">Use Sasampa in English or Kiswahili. Switch anytime based on your preference.</p>
                </div>
                <div class="why-item">
                    <div class="why-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <h3 class="why-title">Multi-Branch Ready</h3>
                    <p class="why-desc">Manage multiple shop locations from one account. Track each branch separately.</p>
                </div>
                <div class="why-item">
                    <div class="why-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><circle cx="12" cy="16" r="1"/></svg>
                    </div>
                    <h3 class="why-title">PIN Quick Login</h3>
                    <p class="why-desc">Cashiers switch in seconds with 4-digit PIN. No passwords to remember.</p>
                </div>
                <div class="why-item">
                    <div class="why-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 12 15 22 5"/></svg>
                    </div>
                    <h3 class="why-title">Secure & Reliable</h3>
                    <p class="why-desc">Your data is backed up automatically. Works even with unstable internet.</p>
                </div>
                <div class="why-item">
                    <div class="why-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    </div>
                    <h3 class="why-title">Works on Any Device</h3>
                    <p class="why-desc">Use on tablet, laptop, or phone. No special hardware required.</p>
                </div>
                <div class="why-item">
                    <div class="why-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M16 8l-4 4-4-4"/><path d="M12 12v6"/></svg>
                    </div>
                    <h3 class="why-title">Free to Start</h3>
                    <p class="why-desc">No credit card required. Start selling immediately after sign up.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="features-container">
            <div class="section-header">
                <div class="section-label">Testimonials</div>
                <h2 class="section-title">Trusted by businesses across Tanzania</h2>
                <p class="section-desc">See what our customers have to say about Sasampa POS.</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <p class="testimonial-text">"Bro! App hii ni moto 🔥 Kwanza stock tracking yake ni next level. Siku hizi sichanganyikiwi na hesabu, kila kitu kiko clear. Hata wateja wangu wanashangaa speed ya service!"</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">DK</div>
                        <div>
                            <div class="testimonial-name">Dennis Kaaya</div>
                            <div class="testimonial-role">Duka la Simu, Sinza</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <p class="testimonial-text">"As a restaurant owner, having a reliable POS system is essential. Sasampa has streamlined our operations significantly. The reporting features provide valuable insights that help us make informed business decisions."</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">CT</div>
                        <div>
                            <div class="testimonial-name">Cozy Taste Restaurant</div>
                            <div class="testimonial-role">Restaurant & Catering, Dar es Salaam</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <p class="testimonial-text">"Nilidhani POS ni ngumu lakini Sasampa ni rahisi kupindukia! Wafanyakazi wangu wamejifunza ndani ya dakika 10. Sasa naweza kuangalia mauzo hata nikiwa likizo."</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">AM</div>
                        <div>
                            <div class="testimonial-name">Agnes Mushi</div>
                            <div class="testimonial-role">Boutique Owner, Mwenge</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Share Section -->
    <section class="share-section">
        <div class="features-container">
            <div class="share-card">
                <div class="share-content">
                    <h2 class="share-title">Love Sasampa? Share with friends!</h2>
                    <p class="share-desc">Help other business owners discover better ways to manage their sales.</p>
                    <div class="share-buttons">
                        <a href="https://wa.me/?text=Nimegundua%20app%20nzuri%20ya%20kusimamia%20biashara%20-%20Sasampa%20POS!%20Jaribu%20bure:%20https://sasampa.com" target="_blank" class="share-btn whatsapp">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Share on WhatsApp
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=https://sasampa.com" target="_blank" class="share-btn facebook">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            Share on Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=Check%20out%20Sasampa%20POS%20-%20modern%20point%20of%20sale%20for%20African%20businesses!&url=https://sasampa.com" target="_blank" class="share-btn twitter">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            Share on X
                        </a>
                        <button onclick="copyLink()" class="share-btn copy">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            Copy Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="cta-container">
            <h2 class="cta-title">Ready to modernize your business?</h2>
            <p class="cta-desc">Join hundreds of businesses across Tanzania using Sasampa to grow their sales and simplify operations.</p>
            <div class="cta-actions">
                @auth
                    <a href="{{ route('pos.index') }}" class="btn-primary">
                        Open POS
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <a href="{{ route('company.register') }}" class="btn-primary">
                        Get Started
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('login') }}" class="btn-secondary">Sign in to your account</a>
                @endauth
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand">
                <div class="footer-logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#0a0a0a" stroke-width="2.5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <path d="M9 9h6M9 15h6M9 12h6"/>
                    </svg>
                </div>
                <span class="footer-name">Sasampa</span>
            </div>
            <div class="footer-copy">&copy; {{ date('Y') }} Sasampa. All rights reserved.</div>
        </div>
    </footer>

    <!-- Sanduku Feedback -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @include('components.sanduku')

    <script>
        window.addEventListener('scroll', function() {
            document.getElementById('nav').classList.toggle('scrolled', window.scrollY > 50);
        });

        function copyLink() {
            const url = 'https://sasampa.com';
            navigator.clipboard.writeText(url).then(function() {
                const btn = document.querySelector('.share-btn.copy');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Copied!';
                btn.style.background = '#22c55e';
                btn.style.color = '#fff';
                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    btn.style.background = '#fff';
                    btn.style.color = '#1a1a1a';
                }, 2000);
            }).catch(function(err) {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Link copied: ' + url);
            });
        }
    </script>
</body>
</html>
