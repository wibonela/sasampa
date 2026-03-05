<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sasampa Mobile App - Coming Soon</title>
    <meta name="description" content="Sasampa POS mobile app for iOS & Android. Manage your business from anywhere. Join the waitlist!">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #0a0a0a; color: #fff; min-height: 100vh; }

        /* Header */
        .header { padding: 20px 24px; display: flex; align-items: center; justify-content: space-between; max-width: 800px; margin: 0 auto; }
        .header-left { display: flex; align-items: center; gap: 12px; text-decoration: none; color: #fff; }
        .header-left:hover { opacity: 0.8; }
        .header-logo { width: 36px; height: 36px; background: linear-gradient(135deg, #FF2D20, #E53E3E); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; }
        .header-title { font-size: 18px; font-weight: 600; }
        .header-nav { display: flex; gap: 12px; }
        .header-nav a { color: #999; text-decoration: none; font-size: 14px; font-weight: 500; padding: 8px 16px; border-radius: 8px; transition: all 0.2s; }
        .header-nav a:hover { color: #fff; background: rgba(255,255,255,0.08); }
        .header-nav .btn-login { background: #fff; color: #0a0a0a; border-radius: 8px; }
        .header-nav .btn-login:hover { background: #e5e5e5; }

        /* Main */
        .main { padding: 40px 24px 80px; max-width: 720px; margin: 0 auto; }
        .badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(34, 197, 94, 0.15); color: #22c55e; padding: 8px 16px; border-radius: 100px; font-size: 13px; font-weight: 600; margin-bottom: 24px; }
        .badge svg { flex-shrink: 0; }
        h1 { font-size: 40px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 12px; }
        .subtitle { font-size: 18px; color: #888; margin-bottom: 16px; line-height: 1.5; }
        .features { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 48px; }
        .feature-tag { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); padding: 8px 14px; border-radius: 8px; font-size: 13px; color: #bbb; }
        .feature-tag svg { flex-shrink: 0; color: #22c55e; }

        /* Form Card */
        .form-card { background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 40px; }
        .form-title { font-size: 20px; font-weight: 600; margin-bottom: 24px; }
        .form { display: flex; flex-direction: column; gap: 20px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .field { display: flex; flex-direction: column; gap: 6px; }
        .field label { font-size: 13px; font-weight: 500; color: #ccc; }
        .field input, .field select { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 14px 16px; font-size: 15px; color: #fff; transition: all 0.2s; }
        .field input::placeholder { color: #555; }
        .field input:focus, .field select:focus { outline: none; border-color: #22c55e; background: rgba(255,255,255,0.12); }
        .field select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 40px; cursor: pointer; }
        .field select option { background: #1a1a1a; color: #fff; }
        .radios { display: flex; gap: 16px; padding: 10px 0; }
        .radio { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: #ccc; }
        .radio input { accent-color: #22c55e; width: 18px; height: 18px; cursor: pointer; }
        .submit-btn { background: #22c55e; color: #fff; border: none; padding: 16px 32px; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 8px; width: 100%; }
        .submit-btn:hover { background: #16a34a; transform: translateY(-2px); }
        .submit-btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
        .success { text-align: center; padding: 40px 20px; }
        .success .success-icon { margin-bottom: 20px; }
        .success h3 { font-size: 24px; font-weight: 600; color: #fff; margin-bottom: 12px; }
        .success p { font-size: 15px; color: #999; }
        .error { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; padding: 14px 18px; border-radius: 10px; font-size: 14px; margin-top: 16px; }
        .footer-stats { display: flex; justify-content: center; gap: 32px; margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.08); }
        .stat { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #666; }
        .spinner { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        /* Footer */
        .page-footer { text-align: center; padding: 40px 24px; color: #555; font-size: 13px; border-top: 1px solid rgba(255,255,255,0.06); max-width: 800px; margin: 0 auto; }
        .page-footer a { color: #888; text-decoration: none; }
        .page-footer a:hover { color: #fff; }

        @media (max-width: 640px) {
            h1 { font-size: 28px; }
            .form-row { grid-template-columns: 1fr; }
            .form-card { padding: 24px; }
            .header-nav .hide-mobile { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="/" class="header-left">
            <div class="header-logo">S</div>
            <div class="header-title">Sasampa</div>
        </a>
        <nav class="header-nav">
            <a href="/" class="hide-mobile">Home</a>
            <a href="/login" class="btn-login">Login</a>
        </nav>
    </div>

    <div class="main">
        <div class="badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
            Coming Soon
        </div>

        <h1>Sasampa Mobile App</h1>
        <p class="subtitle">Manage your business from anywhere. Full POS system in your pocket for iOS & Android.</p>

        <div class="features">
            <span class="feature-tag">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Quick Checkout
            </span>
            <span class="feature-tag">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Real-time Reports
            </span>
            <span class="feature-tag">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Inventory Management
            </span>
            <span class="feature-tag">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Offline Support
            </span>
            <span class="feature-tag">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Barcode Scanner
            </span>
        </div>

        <div class="form-card">
            <div class="form-title">Join the Waitlist</div>

            <form id="waitlistForm" class="form">
                @csrf
                <div class="form-row">
                    <div class="field">
                        <label for="wl_name">Full Name *</label>
                        <input type="text" id="wl_name" name="name" placeholder="Your full name" required>
                    </div>
                    <div class="field">
                        <label for="wl_phone">Phone Number *</label>
                        <input type="tel" id="wl_phone" name="phone" placeholder="+255 XXX XXX XXX" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label for="wl_business">Business Name *</label>
                        <input type="text" id="wl_business" name="business_name" placeholder="Your business name" required>
                    </div>
                    <div class="field">
                        <label for="wl_email">Email (Optional)</label>
                        <input type="email" id="wl_email" name="email" placeholder="your@email.com">
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label for="wl_type">Business Type *</label>
                        <select id="wl_type" name="business_type" required>
                            <option value="">Select type...</option>
                            <option value="restaurant">Restaurant</option>
                            <option value="retail">Retail Shop</option>
                            <option value="pharmacy">Pharmacy</option>
                            <option value="supermarket">Supermarket</option>
                            <option value="salon">Salon</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Platform Preference *</label>
                        <div class="radios">
                            <label class="radio">
                                <input type="radio" name="platform" value="ios"> iOS
                            </label>
                            <label class="radio">
                                <input type="radio" name="platform" value="android"> Android
                            </label>
                            <label class="radio">
                                <input type="radio" name="platform" value="both" checked> Both
                            </label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="submit-btn" id="submitBtn">
                    <span class="btn-text">Join the Waitlist</span>
                    <span class="btn-loading" style="display: none;">
                        <svg class="spinner" width="20" height="20" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="32" stroke-linecap="round"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></circle></svg>
                        Joining...
                    </span>
                </button>
            </form>

            <div id="waitlistSuccess" class="success" style="display: none;">
                <div class="success-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <h3>Hongera! You're on the list!</h3>
                <p>We'll notify you when we launch. Get ready to manage your business from anywhere!</p>
            </div>

            <div id="waitlistError" class="error" style="display: none;"></div>

            <div class="footer-stats">
                <span class="stat">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span id="waitlistCount">0</span> businesses already waiting
                </span>
            </div>
        </div>
    </div>

    <div class="page-footer">
        <p>&copy; {{ date('Y') }} Sasampa POS. All rights reserved.</p>
        <p style="margin-top: 8px;"><a href="/privacy-policy">Privacy Policy</a></p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('/api/mobile-waitlist/count')
                .then(r => r.json())
                .then(data => { document.getElementById('waitlistCount').textContent = data.count; })
                .catch(() => {});

            const form = document.getElementById('waitlistForm');
            const submitBtn = document.getElementById('submitBtn');
            const successDiv = document.getElementById('waitlistSuccess');
            const errorDiv = document.getElementById('waitlistError');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                errorDiv.style.display = 'none';
                submitBtn.disabled = true;
                submitBtn.querySelector('.btn-text').style.display = 'none';
                submitBtn.querySelector('.btn-loading').style.display = 'flex';

                const formData = new FormData(form);

                fetch('/api/mobile-waitlist', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        form.style.display = 'none';
                        successDiv.style.display = 'block';
                        document.getElementById('waitlistCount').textContent = data.count;
                    } else if (data.errors) {
                        errorDiv.innerHTML = Object.values(data.errors).flat().join('<br>');
                        errorDiv.style.display = 'block';
                    } else if (data.message) {
                        errorDiv.innerHTML = data.message;
                        errorDiv.style.display = 'block';
                    }
                })
                .catch(() => {
                    errorDiv.innerHTML = 'Something went wrong. Please try again.';
                    errorDiv.style.display = 'block';
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.querySelector('.btn-text').style.display = 'inline';
                    submitBtn.querySelector('.btn-loading').style.display = 'none';
                });
            });
        });
    </script>
</body>
</html>
