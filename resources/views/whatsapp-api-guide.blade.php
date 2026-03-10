<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sasampa - WhatsApp & Messaging API Setup Guide</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; color: #1a1a1a; line-height: 1.7; background: #f8f9fa; }

        /* Auth overlay */
        .auth-overlay { position: fixed; inset: 0; background: #fff; z-index: 1000; display: flex; align-items: center; justify-content: center; }
        .auth-overlay.hidden { display: none; }
        .auth-box { text-align: center; max-width: 360px; padding: 40px; }
        .auth-box .logo-icon { width: 64px; height: 64px; margin: 0 auto 24px; }
        .auth-box h2 { font-size: 22px; margin-bottom: 8px; }
        .auth-box p { color: #666; font-size: 14px; margin-bottom: 24px; }
        .auth-box input { width: 100%; padding: 14px 16px; border: 2px solid #e5e5e5; border-radius: 12px; font-size: 16px; text-align: center; letter-spacing: 2px; outline: none; transition: border-color 0.2s; }
        .auth-box input:focus { border-color: #25D366; }
        .auth-box .error { color: #e53e3e; font-size: 13px; margin-top: 8px; display: none; }
        .auth-box button { width: 100%; padding: 14px; background: #1a1a1a; color: #fff; border: none; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 16px; transition: background 0.2s; }
        .auth-box button:hover { background: #333; }

        /* Navigation */
        .nav { background: #fff; border-bottom: 1px solid #e5e5e5; padding: 16px 0; position: sticky; top: 0; z-index: 100; }
        .nav-inner { max-width: 960px; margin: 0 auto; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
        .nav-brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 18px; color: #1a1a1a; text-decoration: none; }
        .nav-brand svg { width: 28px; height: 28px; }
        .tab-nav { display: flex; gap: 0; background: #f0f0f0; border-radius: 10px; padding: 3px; flex-wrap: wrap; }
        .tab-btn { padding: 8px 16px; border: none; background: transparent; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; color: #666; transition: all 0.2s; white-space: nowrap; }
        .tab-btn.active { background: #fff; color: #1a1a1a; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }

        /* Content */
        .content { max-width: 960px; margin: 0 auto; padding: 40px 24px; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .section-title { font-size: 28px; font-weight: 800; margin-bottom: 8px; }
        .section-desc { color: #666; font-size: 16px; margin-bottom: 32px; }
        .section-badge { display: inline-flex; align-items: center; gap: 6px; background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 16px; }

        /* Steps */
        .step { background: #fff; border-radius: 16px; padding: 28px; margin-bottom: 20px; border: 1px solid #e8e8e8; }
        .step-header { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 16px; }
        .step-number { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; flex-shrink: 0; }
        .step-number.whatsapp { background: #25D366; color: #fff; }
        .step-number.at { background: #F7941D; color: #fff; }
        .step-number.pindo { background: #6C63FF; color: #fff; }
        .step-number.env { background: #1a1a1a; color: #fff; }
        .step-title { font-size: 18px; font-weight: 600; margin-bottom: 4px; }
        .step-subtitle { font-size: 13px; color: #999; }
        .step-body { padding-left: 52px; }
        .step-body p { margin-bottom: 12px; color: #444; font-size: 15px; }
        .step-body ul, .step-body ol { margin-bottom: 12px; padding-left: 20px; }
        .step-body li { margin-bottom: 8px; color: #444; font-size: 14px; }
        .step-body h4 { font-size: 15px; margin: 16px 0 8px; color: #1a1a1a; }

        /* Code blocks */
        .code-block { background: #1a1a1a; color: #e5e5e5; padding: 16px 20px; border-radius: 10px; font-family: 'SF Mono', 'Fira Code', monospace; font-size: 13px; margin: 12px 0; overflow-x: auto; line-height: 1.6; position: relative; }
        .code-block .comment { color: #6a9955; }
        .code-block .key { color: #9cdcfe; }
        .code-block .value { color: #ce9178; }
        .code-block .cmd { color: #dcdcaa; }

        .copy-btn { position: absolute; top: 8px; right: 8px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #ccc; padding: 4px 10px; border-radius: 6px; font-size: 11px; cursor: pointer; font-family: inherit; }
        .copy-btn:hover { background: rgba(255,255,255,0.2); }
        .copy-btn.copied { background: #25D366; color: #fff; border-color: #25D366; }

        /* Info boxes */
        .info-box { padding: 16px 20px; border-radius: 10px; margin: 12px 0; font-size: 14px; }
        .info-box.warning { background: #fff8e1; border: 1px solid #ffe082; color: #7c6200; }
        .info-box.tip { background: #e8f5e9; border: 1px solid #a5d6a7; color: #1b5e20; }
        .info-box.important { background: #fce4ec; border: 1px solid #ef9a9a; color: #b71c1c; }
        .info-box.info { background: #e3f2fd; border: 1px solid #90caf9; color: #0d47a1; }
        .info-box strong { font-weight: 600; }

        /* Copyable text */
        .copyable { background: #f5f5f5; border: 1px solid #e0e0e0; padding: 10px 14px; border-radius: 8px; margin: 8px 0; font-family: 'SF Mono', monospace; font-size: 13px; display: flex; align-items: center; justify-content: space-between; gap: 8px; cursor: pointer; transition: border-color 0.2s; }
        .copyable:hover { border-color: #999; }
        .copyable .text { word-break: break-all; }
        .copyable .copy-icon { flex-shrink: 0; color: #999; font-size: 12px; }

        /* Comparison table */
        .compare-table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 14px; }
        .compare-table th { background: #f5f5f5; padding: 12px 16px; text-align: left; font-weight: 600; border-bottom: 2px solid #e0e0e0; }
        .compare-table td { padding: 12px 16px; border-bottom: 1px solid #eee; }
        .compare-table tr:last-child td { border-bottom: none; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .badge.green { background: #e8f5e9; color: #2e7d32; }
        .badge.blue { background: #e3f2fd; color: #1565c0; }
        .badge.orange { background: #fff3e0; color: #e65100; }

        /* Responsive */
        @media (max-width: 640px) {
            .step-body { padding-left: 0; margin-top: 12px; }
            .section-title { font-size: 22px; }
            .tab-btn { padding: 6px 12px; font-size: 12px; }
        }
    </style>
</head>
<body>

<!-- Auth Overlay -->
<div class="auth-overlay" id="authOverlay">
    <div class="auth-box">
        <svg class="logo-icon" viewBox="0 0 32 32" fill="none">
            <defs><linearGradient id="authGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#25D366"/><stop offset="100%" style="stop-color:#128C7E"/></linearGradient></defs>
            <rect width="32" height="32" rx="6" fill="url(#authGrad)"/>
            <text x="16" y="22" text-anchor="middle" fill="#fff" font-size="16" font-weight="bold" font-family="Inter, sans-serif">S</text>
        </svg>
        <h2>API Setup Guides</h2>
        <p>Enter the admin password to access the setup guides</p>
        <form onsubmit="return checkPassword()">
            <input type="password" id="authPassword" placeholder="Password" autofocus>
            <div class="error" id="authError">Incorrect password. Try again.</div>
            <button type="submit">Unlock Guides</button>
        </form>
    </div>
</div>

<!-- Navigation -->
<nav class="nav">
    <div class="nav-inner">
        <a href="/" class="nav-brand">
            <svg viewBox="0 0 32 32" fill="none"><rect width="32" height="32" rx="6" fill="#1a1a1a"/><text x="16" y="22" text-anchor="middle" fill="#fff" font-size="16" font-weight="bold" font-family="Inter">S</text></svg>
            API Setup Guides
        </a>
        <div class="tab-nav">
            <button class="tab-btn active" onclick="switchTab('overview')">Overview</button>
            <button class="tab-btn" onclick="switchTab('whatsapp')">WhatsApp API</button>
            <button class="tab-btn" onclick="switchTab('at')">Africa's Talking</button>
            <button class="tab-btn" onclick="switchTab('pindo')">Pindo</button>
            <button class="tab-btn" onclick="switchTab('env')">Server Config</button>
        </div>
    </div>
</nav>

<div class="content">

<!-- ======================== OVERVIEW TAB ======================== -->
<div id="tab-overview" class="tab-content active">
    <div class="section-badge">&#128640; Getting Started</div>
    <h1 class="section-title">Messaging API Setup for Sasampa POS</h1>
    <p class="section-desc">Complete guide to set up WhatsApp receipt delivery and SMS fallback for your Sasampa POS system. Choose your provider and follow the step-by-step instructions.</p>

    <div class="step">
        <div class="step-header">
            <div class="step-number env">?</div>
            <div>
                <div class="step-title">Which Provider Should I Choose?</div>
                <div class="step-subtitle">Provider comparison for Tanzania</div>
            </div>
        </div>
        <div class="step-body">
            <table class="compare-table">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th>Africa's Talking</th>
                        <th>Pindo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>WhatsApp</strong></td>
                        <td><span class="badge green">Yes</span> (Business API)</td>
                        <td><span class="badge green">Yes</span> (Business API)</td>
                    </tr>
                    <tr>
                        <td><strong>SMS</strong></td>
                        <td><span class="badge green">Yes</span></td>
                        <td><span class="badge green">Yes</span></td>
                    </tr>
                    <tr>
                        <td><strong>TZ Coverage</strong></td>
                        <td><span class="badge green">Excellent</span></td>
                        <td><span class="badge green">Excellent</span> (East Africa focus)</td>
                    </tr>
                    <tr>
                        <td><strong>Pricing (SMS)</strong></td>
                        <td>~TZS 25-40 per SMS</td>
                        <td>~TZS 20-35 per SMS</td>
                    </tr>
                    <tr>
                        <td><strong>Pricing (WhatsApp)</strong></td>
                        <td>~TZS 60-100 per message</td>
                        <td>~TZS 50-80 per message</td>
                    </tr>
                    <tr>
                        <td><strong>Setup Time</strong></td>
                        <td>~1-3 days (sandbox instant)</td>
                        <td>~1-2 days</td>
                    </tr>
                    <tr>
                        <td><strong>Sandbox/Testing</strong></td>
                        <td><span class="badge green">Free sandbox</span></td>
                        <td><span class="badge blue">Test credits</span></td>
                    </tr>
                    <tr>
                        <td><strong>Documentation</strong></td>
                        <td><span class="badge green">Excellent</span></td>
                        <td><span class="badge blue">Good</span></td>
                    </tr>
                    <tr>
                        <td><strong>Recommendation</strong></td>
                        <td><span class="badge orange">Best for testing first</span></td>
                        <td><span class="badge blue">Best for production TZ</span></td>
                    </tr>
                </tbody>
            </table>

            <div class="info-box tip">
                <strong>&#x1F4A1; Our Recommendation:</strong> Start with <strong>Africa's Talking</strong> (free sandbox for testing), then switch to <strong>Pindo</strong> for production if you want lower costs in Tanzania. You can switch providers anytime by changing one line in your <code>.env</code> file.
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number env">&#8594;</div>
            <div>
                <div class="step-title">Quick Start Flow</div>
                <div class="step-subtitle">What to do in order</div>
            </div>
        </div>
        <div class="step-body">
            <ol>
                <li><strong>Choose a provider</strong> — Africa's Talking or Pindo (you can use both, but typically pick one)</li>
                <li><strong>Create account & get API keys</strong> — Follow the provider-specific guide (tabs above)</li>
                <li><strong>Configure the server</strong> — Add API keys to your <code>.env</code> file (Server Config tab)</li>
                <li><strong>Enable in Sasampa</strong> — Go to Settings &gt; WhatsApp Receipts in the mobile app and turn it on</li>
                <li><strong>Test</strong> — Send a test message from the settings screen</li>
            </ol>
        </div>
    </div>
</div>

<!-- ======================== WHATSAPP TAB ======================== -->
<div id="tab-whatsapp" class="tab-content">
    <div class="section-badge" style="background:#dcf8c6; color:#075e54;">&#128172; WhatsApp Business API</div>
    <h1 class="section-title">WhatsApp Business API Setup</h1>
    <p class="section-desc">WhatsApp Business API is accessed through providers like Africa's Talking or Pindo — you don't apply to Meta directly. Here's what you need to know.</p>

    <div class="step">
        <div class="step-header">
            <div class="step-number whatsapp">1</div>
            <div>
                <div class="step-title">Understand How WhatsApp Business API Works</div>
                <div class="step-subtitle">Important background</div>
            </div>
        </div>
        <div class="step-body">
            <p>WhatsApp Business API is <strong>not</strong> the same as WhatsApp Business App (the green app on your phone). Key differences:</p>
            <ul>
                <li><strong>WhatsApp Business App</strong> — free, manual, one phone at a time</li>
                <li><strong>WhatsApp Business API</strong> — paid per message, automated, works from servers, unlimited volume</li>
            </ul>
            <p>To use the API, you go through a <strong>Business Solution Provider (BSP)</strong> like Africa's Talking or Pindo. They handle the Meta/Facebook approval process for you.</p>

            <div class="info-box info">
                <strong>What You'll Need:</strong>
                <ul style="margin-top: 8px; padding-left: 20px;">
                    <li>A <strong>phone number</strong> dedicated to WhatsApp Business API (can't be the same number you use on WhatsApp personal/business app)</li>
                    <li>A <strong>Facebook Business Manager</strong> account (free to create)</li>
                    <li>Your <strong>business name, address, and website</strong> (sasampa.com)</li>
                    <li><strong>Business verification documents</strong> (TIN certificate, business license)</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number whatsapp">2</div>
            <div>
                <div class="step-title">Create a Facebook Business Manager Account</div>
                <div class="step-subtitle">Required by all WhatsApp API providers</div>
            </div>
        </div>
        <div class="step-body">
            <ol>
                <li>Go to <strong>business.facebook.com</strong></li>
                <li>Click <strong>"Create Account"</strong></li>
                <li>Fill in your details:</li>
            </ol>

            <h4>Copy-paste these details:</h4>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Business Name: Sasampa Technologies</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Your Name: [Your full name]</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Business Email: info@sasampa.com</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>

            <ol start="4">
                <li>Verify your email address</li>
                <li>Go to <strong>Business Settings</strong> &gt; <strong>Security Center</strong> &gt; <strong>Start Verification</strong></li>
                <li>Upload your <strong>business documents</strong> (TIN certificate or business license)</li>
                <li>Wait for verification (usually 1-3 business days)</li>
            </ol>

            <div class="info-box warning">
                <strong>&#9888;&#65039; Important:</strong> Facebook Business verification is required before your WhatsApp messages can be sent to customers. Start this early as it takes a few days. You can still use sandbox/test mode while waiting.
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number whatsapp">3</div>
            <div>
                <div class="step-title">Get a Dedicated Phone Number</div>
                <div class="step-subtitle">For WhatsApp Business API</div>
            </div>
        </div>
        <div class="step-body">
            <p>You need a phone number that is <strong>not currently registered</strong> on any WhatsApp account (personal or business app).</p>

            <h4>Options:</h4>
            <ul>
                <li><strong>Buy a new SIM card</strong> — cheapest option, get a Vodacom/Airtel/Tigo number (~TZS 1,000)</li>
                <li><strong>Use an existing business landline</strong> — WhatsApp can verify via voice call</li>
                <li><strong>Transfer an existing WhatsApp Business number</strong> — delete WhatsApp from it first, wait 24 hours</li>
            </ul>

            <div class="info-box tip">
                <strong>&#x1F4A1; Tip:</strong> Buy a new SIM card specifically for this. Keep it simple. The number will appear as your business WhatsApp number to customers. Choose a number that's easy to remember.
            </div>

            <p><strong>If transferring from WhatsApp Business App:</strong></p>
            <ol>
                <li>Open WhatsApp Business on your phone</li>
                <li>Go to Settings &gt; Account &gt; Delete Account</li>
                <li>Wait <strong>24 hours</strong></li>
                <li>Now this number can be registered for WhatsApp Business API</li>
            </ol>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number whatsapp">4</div>
            <div>
                <div class="step-title">Choose Your Provider & Set Up</div>
                <div class="step-subtitle">Follow the provider-specific guide</div>
            </div>
        </div>
        <div class="step-body">
            <p>Now go to the provider-specific tab to complete the setup:</p>
            <ul>
                <li><strong><a href="#" onclick="switchTab('at'); return false;" style="color: #F7941D;">Africa's Talking Guide</a></strong> — Best for starting with sandbox testing</li>
                <li><strong><a href="#" onclick="switchTab('pindo'); return false;" style="color: #6C63FF;">Pindo Guide</a></strong> — Great for East Africa production use</li>
            </ul>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number whatsapp">5</div>
            <div>
                <div class="step-title">Message Templates (Required)</div>
                <div class="step-subtitle">WhatsApp requires pre-approved templates for business messages</div>
            </div>
        </div>
        <div class="step-body">
            <p>WhatsApp requires that the first message to a customer uses a <strong>pre-approved template</strong>. Your provider will help you submit these for approval.</p>

            <h4>Receipt Template — Copy this text when submitting:</h4>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<span class="comment"># Template Name:</span>
<span class="value">receipt_notification</span>

<span class="comment"># Category:</span>
<span class="value">UTILITY</span>

<span class="comment"># Language:</span>
<span class="value">en</span>

<span class="comment"># Body Text:</span>
<span class="value">Thank you for your purchase at {{1}}!

Receipt #{{2}}
Date: {{3}}

{{4}}

Total: {{5}}
Payment: {{6}}

Powered by Sasampa POS</span>

<span class="comment"># Variables:</span>
<span class="key">{{1}}</span> = Business name
<span class="key">{{2}}</span> = Transaction number
<span class="key">{{3}}</span> = Date/time
<span class="key">{{4}}</span> = Items list
<span class="key">{{5}}</span> = Total amount
<span class="key">{{6}}</span> = Payment method
            </div>

            <h4>Swahili Receipt Template:</h4>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<span class="comment"># Template Name:</span>
<span class="value">risiti_arifa</span>

<span class="comment"># Category:</span>
<span class="value">UTILITY</span>

<span class="comment"># Language:</span>
<span class="value">sw</span>

<span class="comment"># Body Text:</span>
<span class="value">Asante kwa ununuzi wako kutoka {{1}}!

Risiti #{{2}}
Tarehe: {{3}}

{{4}}

Jumla: {{5}}
Malipo: {{6}}

Powered by Sasampa POS</span>
            </div>

            <div class="info-box info">
                <strong>Note:</strong> Template approval usually takes 24-48 hours. While waiting, you can test with sandbox mode which doesn't require templates. Sasampa currently sends plain text messages (not templates) which work for customer-initiated conversations. For proactive sending, templates are required.
            </div>
        </div>
    </div>
</div>

<!-- ======================== AFRICA'S TALKING TAB ======================== -->
<div id="tab-at" class="tab-content">
    <div class="section-badge" style="background:#fff3e0; color:#e65100;">&#127758; Africa's Talking</div>
    <h1 class="section-title">Africa's Talking API Setup</h1>
    <p class="section-desc">Complete step-by-step guide to get your Africa's Talking API credentials for WhatsApp and SMS.</p>

    <div class="step">
        <div class="step-header">
            <div class="step-number at">1</div>
            <div>
                <div class="step-title">Create an Africa's Talking Account</div>
                <div class="step-subtitle">Free to sign up, free sandbox</div>
            </div>
        </div>
        <div class="step-body">
            <ol>
                <li>Go to <strong>africastalking.com</strong></li>
                <li>Click <strong>"Sign Up"</strong> (top right)</li>
                <li>Fill in the registration form:</li>
            </ol>

            <h4>Registration details:</h4>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">First Name: [Your first name]</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Last Name: [Your last name]</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Email: info@sasampa.com</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Country: Tanzania</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>

            <ol start="4">
                <li>Set a strong password</li>
                <li>Verify your email (click the link sent to your inbox)</li>
                <li>Log in to your new account</li>
            </ol>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number at">2</div>
            <div>
                <div class="step-title">Get Your Sandbox API Key (For Testing)</div>
                <div class="step-subtitle">Start testing immediately, no approval needed</div>
            </div>
        </div>
        <div class="step-body">
            <ol>
                <li>After logging in, you'll see the <strong>Dashboard</strong></li>
                <li>In the top-left, make sure <strong>"Sandbox"</strong> is selected (not "Live")</li>
                <li>Click on your <strong>username</strong> in the top-right corner</li>
                <li>Select <strong>"API Key"</strong> from the dropdown</li>
                <li>Enter your account password when prompted</li>
                <li>Your API key will be displayed — <strong>copy it and save it securely</strong></li>
            </ol>

            <div class="info-box tip">
                <strong>&#x1F4A1; Your sandbox credentials:</strong>
                <ul style="margin-top: 8px; padding-left: 20px;">
                    <li><strong>Username:</strong> "sandbox" (literally the word "sandbox")</li>
                    <li><strong>API Key:</strong> The key you just copied</li>
                </ul>
            </div>

            <h4>Test your API key with this command (paste in Terminal):</h4>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
curl -X POST https://api.sandbox.africastalking.com/version1/messaging \
  -H "apiKey: YOUR_API_KEY_HERE" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Accept: application/json" \
  -d "username=sandbox&to=%2B255712345678&message=Test+from+Sasampa"
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number at">3</div>
            <div>
                <div class="step-title">Create a Live Application (For Production)</div>
                <div class="step-subtitle">When you're ready to send real messages</div>
            </div>
        </div>
        <div class="step-body">
            <ol>
                <li>Switch to <strong>"Live"</strong> mode (toggle in top-left)</li>
                <li>Click <strong>"Create App"</strong></li>
                <li>Fill in your app details:</li>
            </ol>

            <h4>Copy-paste these details:</h4>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">App Name: Sasampa POS</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Country: Tanzania</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Callback URL: https://sasampa.com/api/webhooks/africastalking</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>

            <ol start="4">
                <li>After creating the app, go to your app's <strong>Settings &gt; API Key</strong></li>
                <li>Generate a <strong>Live API key</strong></li>
                <li>Note your <strong>App Username</strong> (this is your AT_USERNAME for production — NOT "sandbox")</li>
            </ol>

            <div class="info-box important">
                <strong>&#128680; Important:</strong> The live API key is different from the sandbox key. Make sure you use the correct one for each environment.
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number at">4</div>
            <div>
                <div class="step-title">Enable WhatsApp Channel</div>
                <div class="step-subtitle">Request WhatsApp Business API access</div>
            </div>
        </div>
        <div class="step-body">
            <ol>
                <li>In your Africa's Talking dashboard, go to <strong>"Chat"</strong> &gt; <strong>"WhatsApp"</strong></li>
                <li>Click <strong>"Request Access"</strong> or <strong>"Enable WhatsApp"</strong></li>
                <li>You'll need to provide:</li>
            </ol>

            <h4>WhatsApp Business details to submit:</h4>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Business Name: Sasampa Technologies</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Facebook Business Manager ID: [Your FB Business Manager ID]</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">WhatsApp Phone Number: +255XXXXXXXXX</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Use Case: Automated receipt delivery for POS transactions</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Expected Monthly Volume: 1,000-10,000 messages</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>

            <ol start="4">
                <li>Africa's Talking will process your request (1-3 business days)</li>
                <li>Once approved, you'll get a <strong>WhatsApp Product ID</strong></li>
                <li>This is your <code>AT_WHATSAPP_PRODUCT_ID</code></li>
            </ol>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number at">5</div>
            <div>
                <div class="step-title">Fund Your Account</div>
                <div class="step-subtitle">Add credit for sending messages</div>
            </div>
        </div>
        <div class="step-body">
            <ol>
                <li>Go to <strong>Billing</strong> in your dashboard</li>
                <li>Click <strong>"Top Up"</strong></li>
                <li>Payment options:
                    <ul>
                        <li><strong>Mobile Money</strong> (M-Pesa, Tigo Pesa, Airtel Money)</li>
                        <li><strong>Bank Transfer</strong></li>
                        <li><strong>Credit/Debit Card</strong></li>
                    </ul>
                </li>
                <li>Start with <strong>TZS 50,000</strong> (~$20 USD) for testing and initial use</li>
            </ol>

            <div class="info-box tip">
                <strong>&#x1F4A1; Cost Estimate:</strong> With TZS 50,000 you can send approximately:
                <ul style="margin-top: 8px; padding-left: 20px;">
                    <li>~1,200 SMS messages, or</li>
                    <li>~500-800 WhatsApp messages</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number at">6</div>
            <div>
                <div class="step-title">Your Africa's Talking Credentials Summary</div>
                <div class="step-subtitle">What you'll need for the .env file</div>
            </div>
        </div>
        <div class="step-body">
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<span class="comment"># Africa's Talking Credentials</span>
<span class="comment"># Get these from: africastalking.com > Dashboard > Settings > API Key</span>

<span class="key">AT_API_KEY</span>=<span class="value">[your-api-key-from-step-2-or-3]</span>
<span class="key">AT_USERNAME</span>=<span class="value">[your-app-username OR "sandbox" for testing]</span>
<span class="key">AT_WHATSAPP_PRODUCT_ID</span>=<span class="value">[from-step-4-after-whatsapp-approval]</span>
<span class="key">AT_SANDBOX</span>=<span class="value">true</span>  <span class="comment"># Change to false for production</span>
            </div>
        </div>
    </div>
</div>

<!-- ======================== PINDO TAB ======================== -->
<div id="tab-pindo" class="tab-content">
    <div class="section-badge" style="background:#ede7f6; color:#4527a0;">&#128233; Pindo</div>
    <h1 class="section-title">Pindo API Setup</h1>
    <p class="section-desc">Complete step-by-step guide to get your Pindo API credentials for WhatsApp and SMS in East Africa.</p>

    <div class="step">
        <div class="step-header">
            <div class="step-number pindo">1</div>
            <div>
                <div class="step-title">Create a Pindo Account</div>
                <div class="step-subtitle">Quick signup, East Africa focused</div>
            </div>
        </div>
        <div class="step-body">
            <ol>
                <li>Go to <strong>pindo.io</strong></li>
                <li>Click <strong>"Get Started"</strong> or <strong>"Sign Up"</strong></li>
                <li>Fill in the registration form:</li>
            </ol>

            <h4>Registration details:</h4>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Company Name: Sasampa Technologies</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Email: info@sasampa.com</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Phone: +255XXXXXXXXX</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Country: Tanzania</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Use Case: POS receipt delivery via WhatsApp and SMS</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>

            <ol start="4">
                <li>Verify your email</li>
                <li>Complete KYC if prompted (business documents)</li>
            </ol>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number pindo">2</div>
            <div>
                <div class="step-title">Get Your API Token</div>
                <div class="step-subtitle">Your authentication credential</div>
            </div>
        </div>
        <div class="step-body">
            <ol>
                <li>Log in to your Pindo dashboard at <strong>app.pindo.io</strong></li>
                <li>Go to <strong>"API Keys"</strong> or <strong>"Settings"</strong></li>
                <li>Click <strong>"Generate API Token"</strong> or copy your existing token</li>
                <li><strong>Save this token securely</strong> — you'll need it for the .env file</li>
            </ol>

            <h4>Test your API token (paste in Terminal):</h4>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
curl -X POST https://api.pindo.io/v1/sms/ \
  -H "Authorization: Bearer YOUR_API_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+255712345678",
    "text": "Test from Sasampa POS",
    "sender": "SASAMPA"
  }'
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number pindo">3</div>
            <div>
                <div class="step-title">Register a Sender ID</div>
                <div class="step-subtitle">Your business name shown to customers</div>
            </div>
        </div>
        <div class="step-body">
            <p>A Sender ID is the name that appears when customers receive your SMS (instead of a random number).</p>

            <ol>
                <li>In the Pindo dashboard, go to <strong>"Sender IDs"</strong></li>
                <li>Click <strong>"Request Sender ID"</strong></li>
                <li>Fill in the details:</li>
            </ol>

            <h4>Copy-paste these details:</h4>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Sender ID: SASAMPA</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Purpose: Transaction receipts from POS system</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Sample Message: Thank you for your purchase at Sasampa! Receipt #TXN-001. Total: TZS 25,000. Payment: Cash.</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>

            <ol start="4">
                <li>Submit for approval (usually 1-2 business days)</li>
                <li>Once approved, this is your <code>PINDO_SENDER_ID</code></li>
            </ol>

            <div class="info-box info">
                <strong>Note:</strong> While waiting for Sender ID approval, messages will be sent from a default Pindo number. The Sender ID is cosmetic — messages will still be delivered.
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number pindo">4</div>
            <div>
                <div class="step-title">Enable WhatsApp Channel</div>
                <div class="step-subtitle">Request WhatsApp Business API access</div>
            </div>
        </div>
        <div class="step-body">
            <ol>
                <li>In the Pindo dashboard, go to <strong>"Channels"</strong> &gt; <strong>"WhatsApp"</strong></li>
                <li>Click <strong>"Enable WhatsApp"</strong> or <strong>"Request Access"</strong></li>
                <li>You'll need to provide:</li>
            </ol>

            <h4>WhatsApp setup details:</h4>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Business Name: Sasampa Technologies</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">WhatsApp Number: +255XXXXXXXXX</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Facebook Business Manager ID: [Your FB Business Manager ID]</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>
            <div class="copyable" onclick="copyText(this)">
                <span class="text">Business Description: Sasampa is a POS (Point of Sale) system for businesses in Tanzania. We send automated receipts to customers after purchases.</span>
                <span class="copy-icon">&#128203; Copy</span>
            </div>

            <ol start="4">
                <li>Pindo will guide you through Facebook Business verification</li>
                <li>WhatsApp number verification (via SMS or voice call)</li>
                <li>Once approved, WhatsApp sending is enabled on your account</li>
            </ol>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number pindo">5</div>
            <div>
                <div class="step-title">Fund Your Account</div>
                <div class="step-subtitle">Add credit for sending messages</div>
            </div>
        </div>
        <div class="step-body">
            <ol>
                <li>Go to <strong>"Billing"</strong> or <strong>"Top Up"</strong> in your dashboard</li>
                <li>Payment methods available:
                    <ul>
                        <li><strong>Mobile Money</strong> (M-Pesa, Tigo Pesa, Airtel Money) — most convenient in TZ</li>
                        <li><strong>Credit/Debit Card</strong></li>
                        <li><strong>Bank Transfer</strong></li>
                    </ul>
                </li>
                <li>Start with <strong>TZS 50,000</strong> for testing</li>
            </ol>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number pindo">6</div>
            <div>
                <div class="step-title">Your Pindo Credentials Summary</div>
                <div class="step-subtitle">What you'll need for the .env file</div>
            </div>
        </div>
        <div class="step-body">
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<span class="comment"># Pindo Credentials</span>
<span class="comment"># Get these from: app.pindo.io > Settings > API Keys</span>

<span class="key">PINDO_API_TOKEN</span>=<span class="value">[your-api-token-from-step-2]</span>
<span class="key">PINDO_SENDER_ID</span>=<span class="value">SASAMPA</span>  <span class="comment"># Your approved sender ID from step 3</span>
            </div>
        </div>
    </div>
</div>

<!-- ======================== ENV CONFIG TAB ======================== -->
<div id="tab-env" class="tab-content">
    <div class="section-badge" style="background:#263238; color:#b0bec5;">&#9881;&#65039; Server Configuration</div>
    <h1 class="section-title">Server Configuration</h1>
    <p class="section-desc">How to add your API credentials to the Sasampa server and enable WhatsApp receipts.</p>

    <div class="step">
        <div class="step-header">
            <div class="step-number env">1</div>
            <div>
                <div class="step-title">SSH into the Server</div>
                <div class="step-subtitle">Connect to the production server</div>
            </div>
        </div>
        <div class="step-body">
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
ssh wibo@46.202.128.164
            </div>
            <p>Enter the server password when prompted.</p>

            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
cd /var/www/sasampa
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number env">2</div>
            <div>
                <div class="step-title">Edit the .env File</div>
                <div class="step-subtitle">Add your API credentials</div>
            </div>
        </div>
        <div class="step-body">
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
sudo nano .env
            </div>

            <p>Scroll to the bottom of the file and add the following lines. Choose the block that matches your provider:</p>

            <h4>Option A: Africa's Talking (recommended for starting)</h4>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<span class="comment"># ========================================</span>
<span class="comment"># WhatsApp & Messaging Configuration</span>
<span class="comment"># ========================================</span>

<span class="comment"># Provider: "africastalking", "pindo", or "stub" (for testing)</span>
<span class="key">WHATSAPP_PROVIDER</span>=<span class="value">africastalking</span>

<span class="comment"># Africa's Talking Credentials</span>
<span class="key">AT_API_KEY</span>=<span class="value">paste-your-api-key-here</span>
<span class="key">AT_USERNAME</span>=<span class="value">paste-your-username-here</span>
<span class="key">AT_WHATSAPP_PRODUCT_ID</span>=<span class="value">paste-your-whatsapp-product-id-here</span>
<span class="key">AT_SANDBOX</span>=<span class="value">false</span>
            </div>

            <h4>Option B: Pindo</h4>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<span class="comment"># ========================================</span>
<span class="comment"># WhatsApp & Messaging Configuration</span>
<span class="comment"># ========================================</span>

<span class="comment"># Provider: "africastalking", "pindo", or "stub" (for testing)</span>
<span class="key">WHATSAPP_PROVIDER</span>=<span class="value">pindo</span>

<span class="comment"># Pindo Credentials</span>
<span class="key">PINDO_API_TOKEN</span>=<span class="value">paste-your-api-token-here</span>
<span class="key">PINDO_SENDER_ID</span>=<span class="value">SASAMPA</span>
            </div>

            <h4>Option C: Testing Mode (no real messages sent)</h4>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<span class="comment"># ========================================</span>
<span class="comment"># WhatsApp & Messaging Configuration</span>
<span class="comment"># ========================================</span>

<span class="comment"># Stub mode - messages are logged but not sent</span>
<span class="key">WHATSAPP_PROVIDER</span>=<span class="value">stub</span>
            </div>

            <p>Save the file: Press <strong>Ctrl+X</strong>, then <strong>Y</strong>, then <strong>Enter</strong></p>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number env">3</div>
            <div>
                <div class="step-title">Clear Config Cache</div>
                <div class="step-subtitle">Apply the new configuration</div>
            </div>
        </div>
        <div class="step-body">
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
sudo php artisan config:clear && sudo php artisan config:cache
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number env">4</div>
            <div>
                <div class="step-title">Start the Queue Worker</div>
                <div class="step-subtitle">Required for async message delivery</div>
            </div>
        </div>
        <div class="step-body">
            <p>WhatsApp receipts are sent via background jobs. You need a queue worker running.</p>

            <h4>Option A: Quick test (runs in foreground, stops when you disconnect):</h4>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
sudo php artisan queue:work --queue=default --tries=3 --timeout=60
            </div>

            <h4>Option B: Persistent with Supervisor (recommended for production):</h4>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<span class="comment"># Install supervisor if not already installed</span>
sudo apt install supervisor -y

<span class="comment"># Create supervisor config</span>
sudo nano /etc/supervisor/conf.d/sasampa-worker.conf
            </div>

            <p>Paste this configuration:</p>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
[program:sasampa-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sasampa/artisan queue:work --queue=default --sleep=3 --tries=3 --timeout=60
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/sasampa/storage/logs/worker.log
stopwaitsecs=3600
            </div>

            <p>Then activate it:</p>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start sasampa-worker:*
            </div>

            <h4>Check if the worker is running:</h4>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
sudo supervisorctl status sasampa-worker:*
            </div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number env">5</div>
            <div>
                <div class="step-title">Enable WhatsApp Receipts in the App</div>
                <div class="step-subtitle">Final step — turn it on from the mobile app</div>
            </div>
        </div>
        <div class="step-body">
            <ol>
                <li>Open <strong>Sasampa POS</strong> mobile app</li>
                <li>Go to <strong>Settings</strong> (bottom nav)</li>
                <li>Tap <strong>"WhatsApp Receipts"</strong> in the Business section</li>
                <li>Toggle <strong>ON</strong> the "Enable WhatsApp Receipts" switch</li>
                <li>Choose your delivery mode:
                    <ul>
                        <li><strong>Automatic</strong> — receipts sent automatically when customer has a phone number</li>
                        <li><strong>Prompted</strong> — cashier can choose to send after each sale</li>
                    </ul>
                </li>
                <li>Toggle <strong>SMS Fallback</strong> if you want SMS when WhatsApp fails</li>
                <li>Tap <strong>"Send Test Message"</strong> to verify everything works</li>
            </ol>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number env">6</div>
            <div>
                <div class="step-title">Verify & Troubleshoot</div>
                <div class="step-subtitle">Check that everything is working</div>
            </div>
        </div>
        <div class="step-body">
            <h4>Check the messaging log:</h4>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
tail -f /var/www/sasampa/storage/logs/messaging.log
            </div>

            <h4>Check the queue for pending jobs:</h4>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
sudo php artisan queue:monitor default
            </div>

            <h4>Check failed jobs:</h4>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
sudo php artisan queue:failed
            </div>

            <h4>Retry all failed jobs:</h4>
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
sudo php artisan queue:retry all
            </div>

            <h4>Common issues:</h4>
            <table class="compare-table">
                <thead>
                    <tr>
                        <th>Problem</th>
                        <th>Solution</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>"Provider not configured"</td>
                        <td>Check <code>WHATSAPP_PROVIDER</code> in .env, run <code>php artisan config:clear</code></td>
                    </tr>
                    <tr>
                        <td>Messages stuck as "pending"</td>
                        <td>Queue worker not running. Start it with supervisor (Step 4)</td>
                    </tr>
                    <tr>
                        <td>"Authentication failed"</td>
                        <td>Double-check your API key/token in .env</td>
                    </tr>
                    <tr>
                        <td>WhatsApp fails, no SMS fallback</td>
                        <td>Enable SMS Fallback in app settings</td>
                    </tr>
                    <tr>
                        <td>"Number not on WhatsApp"</td>
                        <td>Normal — falls back to SMS if enabled</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number env">&#10003;</div>
            <div>
                <div class="step-title">Quick Reference: All .env Variables</div>
                <div class="step-subtitle">Complete list for copy-paste</div>
            </div>
        </div>
        <div class="step-body">
            <div class="code-block">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<span class="comment"># ========================================</span>
<span class="comment"># WhatsApp & Messaging Configuration</span>
<span class="comment"># ========================================</span>

<span class="comment"># Provider: "africastalking", "pindo", or "stub"</span>
<span class="key">WHATSAPP_PROVIDER</span>=<span class="value">stub</span>

<span class="comment"># Africa's Talking (africastalking.com)</span>
<span class="key">AT_API_KEY</span>=<span class="value"></span>
<span class="key">AT_USERNAME</span>=<span class="value"></span>
<span class="key">AT_WHATSAPP_PRODUCT_ID</span>=<span class="value"></span>
<span class="key">AT_SANDBOX</span>=<span class="value">false</span>

<span class="comment"># Pindo (pindo.io)</span>
<span class="key">PINDO_API_TOKEN</span>=<span class="value"></span>
<span class="key">PINDO_SENDER_ID</span>=<span class="value">SASAMPA</span>
            </div>
        </div>
    </div>
</div>

</div><!-- /content -->

<script>
    // Password protection
    function checkPassword() {
        const input = document.getElementById('authPassword').value;
        if (input === 'Sasampa@2026') {
            document.getElementById('authOverlay').classList.add('hidden');
            sessionStorage.setItem('api_guide_auth', '1');
            return false;
        }
        document.getElementById('authError').style.display = 'block';
        document.getElementById('authPassword').value = '';
        document.getElementById('authPassword').focus();
        return false;
    }

    // Check if already authed this session
    if (sessionStorage.getItem('api_guide_auth') === '1') {
        document.getElementById('authOverlay').classList.add('hidden');
    }

    // Tab switching
    function switchTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        event.target && event.target.classList ? event.target.classList.add('active') :
            document.querySelectorAll('.tab-btn').forEach(btn => {
                if (btn.textContent.toLowerCase().includes(tab) || btn.onclick.toString().includes(tab)) {
                    btn.classList.add('active');
                }
            });
        // Find the correct button
        document.querySelectorAll('.tab-btn').forEach(btn => {
            if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes("'" + tab + "'")) {
                btn.classList.add('active');
            }
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Copy code blocks
    function copyCode(btn) {
        const block = btn.parentElement;
        const text = block.innerText.replace('Copy', '').replace('Copied!', '').trim();
        // Remove HTML color-coded comments for plain text
        const cleanText = text.replace(/^#.*$/gm, (match) => match); // Keep comments
        navigator.clipboard.writeText(cleanText).then(() => {
            btn.textContent = 'Copied!';
            btn.classList.add('copied');
            setTimeout(() => {
                btn.textContent = 'Copy';
                btn.classList.remove('copied');
            }, 2000);
        });
    }

    // Copy individual text items
    function copyText(el) {
        const text = el.querySelector('.text').textContent;
        navigator.clipboard.writeText(text).then(() => {
            const icon = el.querySelector('.copy-icon');
            const original = icon.textContent;
            icon.textContent = '✓ Copied!';
            icon.style.color = '#25D366';
            setTimeout(() => {
                icon.textContent = original;
                icon.style.color = '#999';
            }, 2000);
        });
    }
</script>

</body>
</html>
