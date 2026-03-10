<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sasampa - Guides</title>
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
        .auth-box input:focus { border-color: #007AFF; }
        .auth-box .error { color: #e53e3e; font-size: 13px; margin-top: 8px; display: none; }
        .auth-box button { width: 100%; padding: 14px; background: #1a1a1a; color: #fff; border: none; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 16px; transition: background 0.2s; }
        .auth-box button:hover { background: #333; }

        /* Navigation */
        .nav { background: #fff; border-bottom: 1px solid #e5e5e5; padding: 12px 0; position: sticky; top: 0; z-index: 100; }
        .nav-inner { max-width: 960px; margin: 0 auto; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
        .nav-brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 18px; color: #1a1a1a; text-decoration: none; }
        .nav-brand svg { width: 28px; height: 28px; }

        /* Category nav */
        .cat-nav { display: flex; gap: 4px; padding: 8px 0; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .cat-btn { padding: 6px 14px; border: none; background: transparent; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; color: #666; transition: all 0.2s; white-space: nowrap; display: flex; align-items: center; gap: 5px; }
        .cat-btn:hover { background: #f0f0f0; }
        .cat-btn.active { background: #1a1a1a; color: #fff; }
        .cat-btn .dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .cat-nav-wrap { max-width: 960px; margin: 0 auto; padding: 0 24px; border-bottom: 1px solid #f0f0f0; background: #fff; }

        /* Content */
        .content { max-width: 960px; margin: 0 auto; padding: 40px 24px; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .section-title { font-size: 28px; font-weight: 800; margin-bottom: 8px; }
        .section-desc { color: #666; font-size: 16px; margin-bottom: 32px; }
        .section-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 16px; }

        /* Steps */
        .step { background: #fff; border-radius: 16px; padding: 28px; margin-bottom: 20px; border: 1px solid #e8e8e8; }
        .step-header { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 16px; }
        .step-number { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; flex-shrink: 0; }
        .step-number.android { background: #3DDC84; color: #000; }
        .step-number.ios { background: #007AFF; color: #fff; }
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

        /* Links */
        .step-link { display: inline-flex; align-items: center; gap: 6px; background: #f0f0f0; color: #1a1a1a; padding: 10px 16px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 500; margin-top: 8px; margin-right: 8px; transition: background 0.2s; }
        .step-link:hover { background: #e5e5e5; }
        .step-link svg { width: 16px; height: 16px; }

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
        .copyable .copy-icon { flex-shrink: 0; color: #999; font-size: 12px; white-space: nowrap; }

        /* Checklist */
        .checklist { background: #f8f9fa; border-radius: 10px; padding: 20px; margin: 12px 0; }
        .checklist h4 { font-size: 15px; margin-bottom: 12px; }
        .check-item { display: flex; align-items: center; gap: 8px; padding: 6px 0; font-size: 14px; color: #444; }
        .check-item::before { content: ''; width: 18px; height: 18px; border: 2px solid #ccc; border-radius: 4px; flex-shrink: 0; }

        /* Table */
        .compare-table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 14px; }
        .compare-table th { background: #f5f5f5; padding: 12px 16px; text-align: left; font-weight: 600; border-bottom: 2px solid #e0e0e0; }
        .compare-table td { padding: 12px 16px; border-bottom: 1px solid #eee; }
        .compare-table tr:last-child td { border-bottom: none; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .badge.green { background: #e8f5e9; color: #2e7d32; }
        .badge.blue { background: #e3f2fd; color: #1565c0; }
        .badge.orange { background: #fff3e0; color: #e65100; }

        /* Home cards */
        .guide-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin: 24px 0; }
        .guide-card { background: #fff; border: 1px solid #e8e8e8; border-radius: 16px; padding: 24px; cursor: pointer; transition: all 0.2s; }
        .guide-card:hover { border-color: #ccc; box-shadow: 0 4px 12px rgba(0,0,0,0.06); transform: translateY(-2px); }
        .guide-card .card-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; font-size: 22px; }
        .guide-card h3 { font-size: 16px; font-weight: 600; margin-bottom: 6px; }
        .guide-card p { font-size: 13px; color: #666; }

        @media (max-width: 640px) {
            .section-title { font-size: 22px; }
            .step { padding: 20px; }
            .step-body { padding-left: 0; margin-top: 12px; }
            .guide-cards { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- Auth Overlay -->
<div class="auth-overlay" id="authOverlay">
    <div class="auth-box">
        <svg class="logo-icon" viewBox="0 0 32 32" fill="none">
            <defs><linearGradient id="authGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#FF2D20"/><stop offset="100%" style="stop-color:#E53E3E"/></linearGradient></defs>
            <rect width="32" height="32" rx="6" fill="url(#authGrad)"/>
            <rect x="7" y="7" width="8" height="8" rx="2" fill="#fff"/>
            <rect x="17" y="7" width="8" height="8" rx="2" fill="#fff"/>
            <rect x="7" y="17" width="8" height="8" rx="2" fill="#fff"/>
            <rect x="17" y="17" width="8" height="8" rx="2" fill="#fff"/>
        </svg>
        <h2>Sasampa Guides</h2>
        <p>Enter the admin password to continue</p>
        <form onsubmit="return checkPassword()">
            <input type="password" id="authPassword" placeholder="Password" autofocus>
            <div class="error" id="authError">Incorrect password. Try again.</div>
            <button type="submit">Unlock</button>
        </form>
    </div>
</div>

<!-- Navigation -->
<nav class="nav">
    <div class="nav-inner">
        <a href="/guide" class="nav-brand">
            <svg viewBox="0 0 32 32"><defs><linearGradient id="navG" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#FF2D20"/><stop offset="100%" style="stop-color:#E53E3E"/></linearGradient></defs><rect width="32" height="32" rx="6" fill="url(#navG)"/><rect x="7" y="7" width="8" height="8" rx="2" fill="#fff"/><rect x="17" y="7" width="8" height="8" rx="2" fill="#fff"/><rect x="7" y="17" width="8" height="8" rx="2" fill="#fff"/><rect x="17" y="17" width="8" height="8" rx="2" fill="#fff"/></svg>
            Sasampa Guides
        </a>
    </div>
</nav>
<div class="cat-nav-wrap">
    <div class="cat-nav">
        <button class="cat-btn active" onclick="switchTab('home')"><span class="dot" style="background:#1a1a1a"></span> Home</button>
        <button class="cat-btn" onclick="switchTab('android')"><span class="dot" style="background:#3DDC84"></span> Android</button>
        <button class="cat-btn" onclick="switchTab('ios')"><span class="dot" style="background:#007AFF"></span> iOS</button>
        <button class="cat-btn" onclick="switchTab('meta')"><span class="dot" style="background:#0084FF"></span> Meta Cloud API</button>
        <button class="cat-btn" onclick="switchTab('at')"><span class="dot" style="background:#F7941D"></span> Africa's Talking</button>
        <button class="cat-btn" onclick="switchTab('pindo')"><span class="dot" style="background:#6C63FF"></span> Pindo</button>
        <button class="cat-btn" onclick="switchTab('server')"><span class="dot" style="background:#263238"></span> Server Config</button>
    </div>
</div>

<div class="content">

{{-- ======================== HOME ======================== --}}
<div id="tab-home" class="tab-content active">
    <h1 class="section-title">Sasampa Guides</h1>
    <p class="section-desc">Everything you need to publish, update, and configure Sasampa POS.</p>

    <h3 style="font-size:14px; color:#999; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px;">App Publishing</h3>
    <div class="guide-cards">
        <div class="guide-card" onclick="switchTab('android')">
            <div class="card-icon" style="background:#e8f5e9;">&#129302;</div>
            <h3>Android — Google Play Store</h3>
            <p>Build AAB, upload to Play Console, create release, roll out update.</p>
        </div>
        <div class="guide-card" onclick="switchTab('ios')">
            <div class="card-icon" style="background:#e3f2fd;">&#127823;</div>
            <h3>iOS — App Store & TestFlight</h3>
            <p>Build IPA, upload with Transporter, TestFlight testing, App Store submission.</p>
        </div>
    </div>

    <h3 style="font-size:14px; color:#999; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px; margin-top:32px;">WhatsApp API Setup</h3>
    <div class="guide-cards">
        <div class="guide-card" onclick="switchTab('meta')" style="border-color:#0084FF; border-width:2px;">
            <div class="card-icon" style="background:#e3f2fd;">&#128172;</div>
            <h3>Meta WhatsApp Cloud API <span class="badge green" style="margin-left:4px;">Recommended</span></h3>
            <p>Direct from Meta/Facebook. Free tier: 1,000 messages/month. No middleman needed.</p>
        </div>
        <div class="guide-card" onclick="switchTab('at')">
            <div class="card-icon" style="background:#fff3e0;">&#127758;</div>
            <h3>Africa's Talking</h3>
            <p>Alternative provider. WhatsApp + SMS. Free sandbox for testing.</p>
        </div>
        <div class="guide-card" onclick="switchTab('pindo')">
            <div class="card-icon" style="background:#ede7f6;">&#128233;</div>
            <h3>Pindo</h3>
            <p>Alternative provider. Great SMS rates for East Africa.</p>
        </div>
    </div>

    <h3 style="font-size:14px; color:#999; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px; margin-top:32px;">Server & Deployment</h3>
    <div class="guide-cards">
        <div class="guide-card" onclick="switchTab('server')">
            <div class="card-icon" style="background:#eceff1;">&#9881;&#65039;</div>
            <h3>Server Configuration</h3>
            <p>SSH, .env setup, queue worker, enabling WhatsApp receipts, troubleshooting.</p>
        </div>
    </div>
</div>

{{-- ======================== ANDROID ======================== --}}
<div id="tab-android" class="tab-content">
    <div class="section-badge" style="background:#e8f5e9; color:#2e7d32;">&#129302; Google Play Store</div>
    <h1 class="section-title">Update Sasampa on Google Play Store</h1>
    <p class="section-desc">Step-by-step guide to publish a new version to the Play Store.</p>

    <div class="checklist">
        <h4>Pre-flight Checklist</h4>
        <div class="check-item">Code changes committed and pushed to GitHub</div>
        <div class="check-item">Version number bumped in pubspec.yaml (e.g. 1.0.2+8)</div>
        <div class="check-item">Tested on a real Android device</div>
        <div class="check-item">Upload key / keystore file ready</div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number android">1</div>
            <div><div class="step-title">Bump the Version Number</div><div class="step-subtitle">File: mobile_app/pubspec.yaml</div></div>
        </div>
        <div class="step-body">
            <p>Open <code>pubspec.yaml</code> and update the version line. Format: <code>major.minor.patch+buildNumber</code>.</p>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button><span class="comment"># Example: going from 1.0.1+7 to 1.0.2+8</span>
version: 1.0.2+8</div>
            <div class="info-box warning"><strong>Important:</strong> The <code>buildNumber</code> (after +) MUST be higher than the previous upload. Google Play rejects duplicate build numbers.</div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number android">2</div>
            <div><div class="step-title">Build the App Bundle (AAB)</div><div class="step-subtitle">Terminal command</div></div>
        </div>
        <div class="step-body">
            <p>Google Play requires an App Bundle (.aab), not an APK:</p>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>cd ~/Desktop/Sasampa/mobile_app
<span class="cmd">flutter build appbundle --release</span></div>
            <p>Output file location:</p>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>build/app/outputs/bundle/release/app-release.aab</div>
            <div class="info-box tip"><strong>Tip:</strong> If you get signing errors, ensure <code>key.properties</code> and keystore file are in <code>android/</code>.</div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number android">3</div>
            <div><div class="step-title">Upload to Google Play Console</div><div class="step-subtitle">Create a new release</div></div>
        </div>
        <div class="step-body">
            <ol>
                <li>Sign in to <strong>Google Play Console</strong></li>
                <li>Select the <strong>Sasampa POS</strong> app</li>
                <li>Go to <strong>Release</strong> &gt; <strong>Production</strong></li>
                <li>Click <strong>"Create new release"</strong></li>
                <li>In the "App bundles" section, click <strong>"Upload"</strong></li>
                <li>Select the <code>app-release.aab</code> file</li>
                <li>Add <strong>Release notes</strong> (what changed)</li>
                <li>Click <strong>"Review release"</strong> then <strong>"Start rollout to Production"</strong></li>
            </ol>
            <a href="https://play.google.com/console" target="_blank" class="step-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Open Google Play Console
            </a>
            <div class="info-box tip"><strong>Timeline:</strong> Updates typically go live within 1-3 hours, but can take up to 24 hours.</div>
        </div>
    </div>
</div>

{{-- ======================== iOS ======================== --}}
<div id="tab-ios" class="tab-content">
    <div class="section-badge" style="background:#e3f2fd; color:#1565c0;">&#127823; App Store</div>
    <h1 class="section-title">Update Sasampa on the App Store</h1>
    <p class="section-desc">Step-by-step guide to publish to the Apple App Store and TestFlight.</p>

    <div class="checklist">
        <h4>Pre-flight Checklist</h4>
        <div class="check-item">Code changes committed and pushed to GitHub</div>
        <div class="check-item">Version number bumped in pubspec.yaml (e.g. 1.0.2+8)</div>
        <div class="check-item">Tested on a real iOS device or simulator</div>
        <div class="check-item">Apple Developer account active (Team: MP3N9HDNTH)</div>
        <div class="check-item">Xcode installed and up to date</div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number ios">1</div>
            <div><div class="step-title">Bump Version & Build IPA</div><div class="step-subtitle">Terminal commands</div></div>
        </div>
        <div class="step-body">
            <p>Update version in <code>pubspec.yaml</code> (same as Android), then build:</p>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>cd ~/Desktop/Sasampa/mobile_app
<span class="cmd">flutter build ipa --release</span></div>
            <p>Output file:</p>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>build/ios/ipa/sasampa_pos.ipa</div>
            <div class="info-box tip"><strong>Signing errors?</strong> Open <code>ios/Runner.xcworkspace</code> in Xcode and verify Team is set to MP3N9HDNTH under Signing & Capabilities.</div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number ios">2</div>
            <div><div class="step-title">Upload with Transporter</div><div class="step-subtitle">Apple's official upload tool</div></div>
        </div>
        <div class="step-body">
            <ol>
                <li>Open the <strong>Transporter</strong> app (free from Mac App Store)</li>
                <li>Sign in with your Apple Developer account</li>
                <li>Drag the <code>.ipa</code> file into the Transporter window</li>
                <li>Click <strong>"Deliver"</strong></li>
                <li>Wait for upload and processing (5-15 minutes)</li>
            </ol>
            <a href="https://apps.apple.com/app/transporter/id1450874784" target="_blank" class="step-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Download Transporter
            </a>
            <div class="info-box tip"><strong>Alternative:</strong> Upload via Xcode: Runner.xcworkspace &gt; Product &gt; Archive &gt; Distribute App &gt; App Store Connect.</div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number ios">3</div>
            <div><div class="step-title">TestFlight (Recommended)</div><div class="step-subtitle">Test before public release</div></div>
        </div>
        <div class="step-body">
            <ol>
                <li>Wait for Apple's processing email (15-30 min after upload)</li>
                <li>Go to App Store Connect &gt; your app &gt; <strong>TestFlight</strong> tab</li>
                <li>Find your build under "iOS Builds"</li>
                <li>If "Missing Compliance" — click Manage, select "None of the algorithms mentioned above"</li>
                <li>Add testers under Internal Testing</li>
            </ol>
            <a href="https://appstoreconnect.apple.com/apps" target="_blank" class="step-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Open App Store Connect
            </a>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number ios">4</div>
            <div><div class="step-title">Submit to App Store</div><div class="step-subtitle">Public release</div></div>
        </div>
        <div class="step-body">
            <ol>
                <li>Go to App Store Connect &gt; your app &gt; <strong>App Store</strong> tab</li>
                <li>Click <strong>"+ Version or Platform"</strong> (top left sidebar)</li>
                <li>Enter new version number, click Create</li>
                <li>Scroll to Build section, click "+" and select your uploaded build</li>
                <li>Add "What's New in This Version" release notes</li>
                <li>Click <strong>"Add for Review"</strong> then <strong>"Submit to App Review"</strong></li>
            </ol>
            <div class="info-box tip"><strong>Timeline:</strong> App Review takes 24-48 hours. The update goes live automatically after approval.</div>
            <div class="info-box important"><strong>If rejected:</strong> Apple sends an email explaining why. Fix the issue, upload a new build, resubmit.</div>
        </div>
    </div>
</div>

{{-- ======================== META WHATSAPP CLOUD API ======================== --}}
<div id="tab-meta" class="tab-content">
    <div class="section-badge" style="background:#e3f2fd; color:#0d47a1;">&#128172; Meta WhatsApp Cloud API <span class="badge green" style="margin-left:4px;">Recommended</span></div>
    <h1 class="section-title">Meta WhatsApp Cloud API Setup</h1>
    <p class="section-desc">Use Meta's official WhatsApp Cloud API directly — no middleman. Free tier: 1,000 service conversations/month. This is what you see at developers.facebook.com.</p>

    <div class="step">
        <div class="step-header">
            <div class="step-number" style="background:#0084FF; color:#fff;">1</div>
            <div><div class="step-title">Create a Meta App</div><div class="step-subtitle">developers.facebook.com</div></div>
        </div>
        <div class="step-body">
            <ol>
                <li>Go to <strong>developers.facebook.com/apps/creation/</strong></li>
                <li>Select <strong>"Connect with customers through WhatsApp"</strong> (bottom option with WhatsApp icon)</li>
                <li>Click <strong>"Next"</strong></li>
                <li>Fill in the app details:</li>
            </ol>
            <div class="copyable" onclick="copyText(this)"><span class="text">App Name: Sasampa POS</span><span class="copy-icon">&#128203; Copy</span></div>
            <div class="copyable" onclick="copyText(this)"><span class="text">App Contact Email: info@sasampa.com</span><span class="copy-icon">&#128203; Copy</span></div>
            <ol start="5">
                <li>Select your <strong>Business Portfolio</strong> (or create one if prompted)</li>
                <li>Click <strong>"Create App"</strong></li>
            </ol>
            <div class="info-box tip"><strong>No Business Portfolio yet?</strong> You'll be prompted to create one. Use "Sasampa Technologies" as the name. This is your Meta Business account.</div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number" style="background:#0084FF; color:#fff;">2</div>
            <div><div class="step-title">Get Your API Credentials</div><div class="step-subtitle">From the WhatsApp > API Setup page</div></div>
        </div>
        <div class="step-body">
            <p>After creating the app, you'll be taken to the dashboard. Go to:</p>
            <ol>
                <li>Left sidebar: <strong>WhatsApp</strong> &gt; <strong>API Setup</strong></li>
                <li>You'll see a <strong>test phone number</strong> already provided by Meta (free for testing)</li>
                <li>Copy these 3 values — you'll need them for the server:</li>
            </ol>

            <div class="info-box info" style="margin: 16px 0;">
                <strong>Find these on the API Setup page:</strong>
                <ul style="margin-top:8px; padding-left:20px;">
                    <li><strong>Temporary Access Token</strong> — shown at the top (expires in 24 hours, for testing only)</li>
                    <li><strong>Phone Number ID</strong> — under "From" phone number, click the number to see the ID (looks like <code>123456789012345</code>)</li>
                    <li><strong>WhatsApp Business Account ID</strong> — shown in the URL or under the phone number section</li>
                </ul>
            </div>

            <h4>Test immediately with the temporary token (paste in Terminal):</h4>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>curl -X POST "https://graph.facebook.com/v22.0/YOUR_PHONE_NUMBER_ID/messages" \
  -H "Authorization: Bearer YOUR_TEMPORARY_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "messaging_product": "whatsapp",
    "to": "255712345678",
    "type": "text",
    "text": { "body": "Hello from Sasampa POS! This is a test message." }
  }'</div>

            <div class="info-box warning"><strong>Important:</strong> The temporary token expires in 24 hours. Step 4 below shows how to get a permanent token.</div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number" style="background:#0084FF; color:#fff;">3</div>
            <div><div class="step-title">Add Your Business Phone Number</div><div class="step-subtitle">Replace Meta's test number with yours</div></div>
        </div>
        <div class="step-body">
            <p>Meta gives you a test number to start, but you'll want your own business number:</p>
            <ol>
                <li>On the API Setup page, click <strong>"Add phone number"</strong></li>
                <li>Fill in your business details:</li>
            </ol>
            <div class="copyable" onclick="copyText(this)"><span class="text">WhatsApp Business Profile Name: Sasampa POS</span><span class="copy-icon">&#128203; Copy</span></div>
            <div class="copyable" onclick="copyText(this)"><span class="text">Category: Shopping and Retail</span><span class="copy-icon">&#128203; Copy</span></div>
            <div class="copyable" onclick="copyText(this)"><span class="text">Business Description: Point of Sale system for businesses in Tanzania. Automated receipt delivery.</span><span class="copy-icon">&#128203; Copy</span></div>
            <ol start="3">
                <li>Enter your <strong>phone number</strong> (must NOT be registered on WhatsApp)</li>
                <li>Choose verification method: <strong>SMS</strong> or <strong>Voice call</strong></li>
                <li>Enter the verification code</li>
                <li>Your number is now registered! Note the new <strong>Phone Number ID</strong></li>
            </ol>
            <div class="info-box tip"><strong>Tip:</strong> You can start testing with Meta's free test number first. Add your own number when ready for production.</div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number" style="background:#0084FF; color:#fff;">4</div>
            <div><div class="step-title">Get a Permanent Access Token</div><div class="step-subtitle">System User token that doesn't expire</div></div>
        </div>
        <div class="step-body">
            <p>The temporary token from Step 2 expires in 24 hours. For production, create a permanent System User token:</p>
            <ol>
                <li>Go to <strong>business.facebook.com</strong> &gt; <strong>Business Settings</strong></li>
                <li>Left sidebar: <strong>Users</strong> &gt; <strong>System Users</strong></li>
                <li>Click <strong>"Add"</strong> to create a new System User:</li>
            </ol>
            <div class="copyable" onclick="copyText(this)"><span class="text">System User Name: Sasampa API</span><span class="copy-icon">&#128203; Copy</span></div>
            <div class="copyable" onclick="copyText(this)"><span class="text">Role: Admin</span><span class="copy-icon">&#128203; Copy</span></div>
            <ol start="4">
                <li>Click <strong>"Create System User"</strong></li>
                <li>Now click <strong>"Add Assets"</strong> on the system user</li>
                <li>Select <strong>"Apps"</strong> tab &gt; find <strong>"Sasampa POS"</strong> &gt; toggle <strong>"Full Control"</strong></li>
                <li>Click <strong>"Save Changes"</strong></li>
                <li>Back on the System User page, click <strong>"Generate New Token"</strong></li>
                <li>Select the <strong>"Sasampa POS"</strong> app</li>
                <li>Set token expiration to <strong>"Never"</strong></li>
                <li>Select these permissions:</li>
            </ol>

            <div class="info-box info">
                <strong>Required Permissions (check these boxes):</strong>
                <ul style="margin-top:8px; padding-left:20px;">
                    <li><code>whatsapp_business_management</code></li>
                    <li><code>whatsapp_business_messaging</code></li>
                </ul>
            </div>

            <ol start="12">
                <li>Click <strong>"Generate Token"</strong></li>
                <li><strong>COPY THE TOKEN IMMEDIATELY</strong> — it's shown only once!</li>
            </ol>

            <div class="info-box important"><strong>Save this token securely!</strong> This is your <code>META_WHATSAPP_TOKEN</code> for the .env file. It won't be shown again. If you lose it, you'll need to generate a new one.</div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number" style="background:#0084FF; color:#fff;">5</div>
            <div><div class="step-title">Add a Test Number (For Sandbox Testing)</div><div class="step-subtitle">Whitelist numbers before verification</div></div>
        </div>
        <div class="step-body">
            <p>Before your business is verified, you can only send messages to <strong>whitelisted test numbers</strong>:</p>
            <ol>
                <li>Go to <strong>WhatsApp</strong> &gt; <strong>API Setup</strong></li>
                <li>Scroll to <strong>"To" field</strong> &gt; click <strong>"Manage phone number list"</strong></li>
                <li>Click <strong>"Add phone number"</strong></li>
                <li>Enter your personal number (or whoever will receive test messages)</li>
                <li>Verify with the code sent to that number</li>
            </ol>
            <div class="info-box tip"><strong>After business verification:</strong> You can send to ANY WhatsApp number worldwide. Verification removes the test-number restriction.</div>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number" style="background:#0084FF; color:#fff;">6</div>
            <div><div class="step-title">Pricing — Free Tier!</div><div class="step-subtitle">1,000 free service conversations per month</div></div>
        </div>
        <div class="step-body">
            <table class="compare-table">
                <thead><tr><th>Conversation Type</th><th>Cost</th><th>Description</th></tr></thead>
                <tbody>
                    <tr><td><strong>Service</strong></td><td><span class="badge green">1,000 FREE/month</span></td><td>Customer messages you within 24 hrs, you reply</td></tr>
                    <tr><td><strong>Utility</strong></td><td>~TZS 30-50/msg</td><td>Receipts, order updates, payment confirmations</td></tr>
                    <tr><td><strong>Marketing</strong></td><td>~TZS 80-120/msg</td><td>Promotions, offers</td></tr>
                </tbody>
            </table>
            <div class="info-box tip"><strong>For receipts:</strong> When a customer initiates a conversation (e.g., they message your business first), your receipt reply is FREE (up to 1,000/month). Proactive receipts sent first are "Utility" conversations.</div>

            <h4>Add a payment method:</h4>
            <ol>
                <li>Go to <strong>WhatsApp</strong> &gt; <strong>API Setup</strong> &gt; <strong>"Add payment method"</strong></li>
                <li>Or go to <strong>business.facebook.com</strong> &gt; <strong>Billing</strong></li>
                <li>Add a <strong>credit/debit card</strong> (Visa/Mastercard)</li>
            </ol>
        </div>
    </div>

    <div class="step">
        <div class="step-header">
            <div class="step-number" style="background:#0084FF; color:#fff;">&#10003;</div>
            <div><div class="step-title">Your Credentials Summary</div><div class="step-subtitle">What to put in the .env file</div></div>
        </div>
        <div class="step-body">
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button><span class="comment"># Meta WhatsApp Cloud API (Direct from Meta)</span>
<span class="comment"># Get from: developers.facebook.com > Your App > WhatsApp > API Setup</span>

<span class="key">WHATSAPP_PROVIDER</span>=<span class="value">meta</span>
<span class="key">META_WHATSAPP_TOKEN</span>=<span class="value">your-permanent-system-user-token</span>
<span class="key">META_WHATSAPP_PHONE_ID</span>=<span class="value">your-phone-number-id</span>
<span class="key">META_WHATSAPP_BUSINESS_ID</span>=<span class="value">your-whatsapp-business-account-id</span></div>
            <p>Now go to <a href="#" onclick="switchTab('server'); return false;" style="color:#1a1a1a; font-weight:600;">Server Config</a> to add these to your server.</p>
        </div>
    </div>
</div>

{{-- ======================== AFRICA'S TALKING ======================== --}}
<div id="tab-at" class="tab-content">
    <div class="section-badge" style="background:#fff3e0; color:#e65100;">&#127758; Africa's Talking</div>
    <h1 class="section-title">Africa's Talking API Setup</h1>
    <p class="section-desc">Get your API credentials for WhatsApp and SMS.</p>

    <div class="step">
        <div class="step-header"><div class="step-number at">1</div><div><div class="step-title">Create Account</div><div class="step-subtitle">Free signup, free sandbox</div></div></div>
        <div class="step-body">
            <ol>
                <li>Go to <strong>africastalking.com</strong> and click <strong>"Sign Up"</strong></li>
                <li>Fill in registration:</li>
            </ol>
            <div class="copyable" onclick="copyText(this)"><span class="text">Email: info@sasampa.com</span><span class="copy-icon">&#128203; Copy</span></div>
            <div class="copyable" onclick="copyText(this)"><span class="text">Country: Tanzania</span><span class="copy-icon">&#128203; Copy</span></div>
            <ol start="3"><li>Set a strong password, verify your email, log in</li></ol>
        </div>
    </div>

    <div class="step">
        <div class="step-header"><div class="step-number at">2</div><div><div class="step-title">Get Sandbox API Key (Testing)</div><div class="step-subtitle">Start testing immediately</div></div></div>
        <div class="step-body">
            <ol>
                <li>In dashboard, ensure <strong>"Sandbox"</strong> is selected (top-left toggle)</li>
                <li>Click your <strong>username</strong> (top-right) &gt; <strong>"API Key"</strong></li>
                <li>Enter your password &gt; copy the API key</li>
            </ol>
            <div class="info-box tip">
                <strong>Sandbox credentials:</strong>
                <ul style="margin-top:8px; padding-left:20px;">
                    <li><strong>Username:</strong> <code>sandbox</code> (literally)</li>
                    <li><strong>API Key:</strong> the key you just copied</li>
                </ul>
            </div>
            <h4>Test your key (paste in Terminal):</h4>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>curl -X POST https://api.sandbox.africastalking.com/version1/messaging \
  -H "apiKey: YOUR_API_KEY_HERE" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Accept: application/json" \
  -d "username=sandbox&to=%2B255712345678&message=Test+from+Sasampa"</div>
        </div>
    </div>

    <div class="step">
        <div class="step-header"><div class="step-number at">3</div><div><div class="step-title">Create Live Application</div><div class="step-subtitle">For production use</div></div></div>
        <div class="step-body">
            <ol>
                <li>Switch to <strong>"Live"</strong> mode (top-left toggle)</li>
                <li>Click <strong>"Create App"</strong></li>
            </ol>
            <div class="copyable" onclick="copyText(this)"><span class="text">App Name: Sasampa POS</span><span class="copy-icon">&#128203; Copy</span></div>
            <div class="copyable" onclick="copyText(this)"><span class="text">Callback URL: https://sasampa.com/api/webhooks/africastalking</span><span class="copy-icon">&#128203; Copy</span></div>
            <ol start="3">
                <li>After creating, go to app <strong>Settings &gt; API Key</strong></li>
                <li>Generate a <strong>Live API key</strong></li>
                <li>Note your <strong>App Username</strong> (this is your <code>AT_USERNAME</code> for production)</li>
            </ol>
            <div class="info-box important"><strong>Important:</strong> Live API key is different from sandbox key. Use the correct one for each environment.</div>
        </div>
    </div>

    <div class="step">
        <div class="step-header"><div class="step-number at">4</div><div><div class="step-title">Enable WhatsApp Channel</div><div class="step-subtitle">Request WhatsApp Business API access</div></div></div>
        <div class="step-body">
            <ol><li>Go to <strong>"Chat"</strong> &gt; <strong>"WhatsApp"</strong> &gt; <strong>"Request Access"</strong></li></ol>
            <div class="copyable" onclick="copyText(this)"><span class="text">Business Name: Sasampa Technologies</span><span class="copy-icon">&#128203; Copy</span></div>
            <div class="copyable" onclick="copyText(this)"><span class="text">Use Case: Automated receipt delivery for POS transactions</span><span class="copy-icon">&#128203; Copy</span></div>
            <div class="copyable" onclick="copyText(this)"><span class="text">Expected Monthly Volume: 1,000-10,000 messages</span><span class="copy-icon">&#128203; Copy</span></div>
            <ol start="2">
                <li>Africa's Talking processes your request (1-3 business days)</li>
                <li>Once approved, you'll get a <strong>WhatsApp Product ID</strong> = your <code>AT_WHATSAPP_PRODUCT_ID</code></li>
            </ol>
        </div>
    </div>

    <div class="step">
        <div class="step-header"><div class="step-number at">5</div><div><div class="step-title">Fund Your Account</div><div class="step-subtitle">Add credit for messages</div></div></div>
        <div class="step-body">
            <ol>
                <li>Go to <strong>Billing</strong> &gt; <strong>"Top Up"</strong></li>
                <li>Payment: <strong>Mobile Money</strong> (M-Pesa, Tigo Pesa, Airtel Money), Bank Transfer, or Card</li>
                <li>Start with <strong>TZS 50,000</strong> (~$20 USD)</li>
            </ol>
            <div class="info-box tip"><strong>TZS 50,000</strong> = ~1,200 SMS or ~500-800 WhatsApp messages</div>
        </div>
    </div>

    <div class="step">
        <div class="step-header"><div class="step-number at">&#10003;</div><div><div class="step-title">Your Credentials Summary</div><div class="step-subtitle">Copy to .env file</div></div></div>
        <div class="step-body">
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button><span class="comment"># Africa's Talking — get from africastalking.com > Settings > API Key</span>
<span class="key">AT_API_KEY</span>=<span class="value">your-api-key-here</span>
<span class="key">AT_USERNAME</span>=<span class="value">your-app-username-or-sandbox</span>
<span class="key">AT_WHATSAPP_PRODUCT_ID</span>=<span class="value">from-whatsapp-approval</span>
<span class="key">AT_SANDBOX</span>=<span class="value">true</span>  <span class="comment"># false for production</span></div>
            <p>Now go to <a href="#" onclick="switchTab('server'); return false;" style="color:#1a1a1a; font-weight:600;">Server Config</a> to add these to your server.</p>
        </div>
    </div>
</div>

{{-- ======================== PINDO ======================== --}}
<div id="tab-pindo" class="tab-content">
    <div class="section-badge" style="background:#ede7f6; color:#4527a0;">&#128233; Pindo</div>
    <h1 class="section-title">Pindo API Setup</h1>
    <p class="section-desc">Get your Pindo credentials for WhatsApp and SMS in East Africa.</p>

    <div class="step">
        <div class="step-header"><div class="step-number pindo">1</div><div><div class="step-title">Create Account</div><div class="step-subtitle">Quick signup, East Africa focused</div></div></div>
        <div class="step-body">
            <ol><li>Go to <strong>pindo.io</strong> and click <strong>"Get Started"</strong></li></ol>
            <div class="copyable" onclick="copyText(this)"><span class="text">Company Name: Sasampa Technologies</span><span class="copy-icon">&#128203; Copy</span></div>
            <div class="copyable" onclick="copyText(this)"><span class="text">Email: info@sasampa.com</span><span class="copy-icon">&#128203; Copy</span></div>
            <div class="copyable" onclick="copyText(this)"><span class="text">Country: Tanzania</span><span class="copy-icon">&#128203; Copy</span></div>
            <div class="copyable" onclick="copyText(this)"><span class="text">Use Case: POS receipt delivery via WhatsApp and SMS</span><span class="copy-icon">&#128203; Copy</span></div>
            <ol start="2"><li>Verify email, complete KYC if prompted</li></ol>
        </div>
    </div>

    <div class="step">
        <div class="step-header"><div class="step-number pindo">2</div><div><div class="step-title">Get API Token</div><div class="step-subtitle">Your authentication credential</div></div></div>
        <div class="step-body">
            <ol>
                <li>Log in at <strong>app.pindo.io</strong></li>
                <li>Go to <strong>"API Keys"</strong> or <strong>"Settings"</strong></li>
                <li>Click <strong>"Generate API Token"</strong> — save it securely</li>
            </ol>
            <h4>Test your token:</h4>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>curl -X POST https://api.pindo.io/v1/sms/ \
  -H "Authorization: Bearer YOUR_API_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{"to":"+255712345678","text":"Test from Sasampa POS","sender":"SASAMPA"}'</div>
        </div>
    </div>

    <div class="step">
        <div class="step-header"><div class="step-number pindo">3</div><div><div class="step-title">Register Sender ID</div><div class="step-subtitle">Business name shown to customers</div></div></div>
        <div class="step-body">
            <ol><li>Go to <strong>"Sender IDs"</strong> &gt; <strong>"Request Sender ID"</strong></li></ol>
            <div class="copyable" onclick="copyText(this)"><span class="text">Sender ID: SASAMPA</span><span class="copy-icon">&#128203; Copy</span></div>
            <div class="copyable" onclick="copyText(this)"><span class="text">Purpose: Transaction receipts from POS system</span><span class="copy-icon">&#128203; Copy</span></div>
            <div class="copyable" onclick="copyText(this)"><span class="text">Sample Message: Thank you for your purchase at Sasampa! Receipt #TXN-001. Total: TZS 25,000. Payment: Cash.</span><span class="copy-icon">&#128203; Copy</span></div>
            <ol start="2"><li>Submit for approval (1-2 business days)</li></ol>
        </div>
    </div>

    <div class="step">
        <div class="step-header"><div class="step-number pindo">4</div><div><div class="step-title">Enable WhatsApp & Fund Account</div><div class="step-subtitle">Same process as Africa's Talking</div></div></div>
        <div class="step-body">
            <ol>
                <li>Go to <strong>"Channels"</strong> &gt; <strong>"WhatsApp"</strong> &gt; <strong>"Enable WhatsApp"</strong></li>
                <li>Provide your Facebook Business Manager ID and WhatsApp number</li>
                <li>Top up with <strong>TZS 50,000</strong> via Mobile Money, Card, or Bank Transfer</li>
            </ol>
            <div class="copyable" onclick="copyText(this)"><span class="text">Business Description: Sasampa is a POS system for businesses in Tanzania. We send automated receipts to customers after purchases.</span><span class="copy-icon">&#128203; Copy</span></div>
        </div>
    </div>

    <div class="step">
        <div class="step-header"><div class="step-number pindo">&#10003;</div><div><div class="step-title">Your Credentials Summary</div><div class="step-subtitle">Copy to .env file</div></div></div>
        <div class="step-body">
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button><span class="comment"># Pindo — get from app.pindo.io > Settings > API Keys</span>
<span class="key">PINDO_API_TOKEN</span>=<span class="value">your-api-token-here</span>
<span class="key">PINDO_SENDER_ID</span>=<span class="value">SASAMPA</span></div>
            <p>Now go to <a href="#" onclick="switchTab('server'); return false;" style="color:#1a1a1a; font-weight:600;">Server Config</a> to add these to your server.</p>
        </div>
    </div>
</div>

{{-- ======================== SERVER CONFIG ======================== --}}
<div id="tab-server" class="tab-content">
    <div class="section-badge" style="background:#eceff1; color:#37474f;">&#9881;&#65039; Server Configuration</div>
    <h1 class="section-title">Server Configuration</h1>
    <p class="section-desc">Add your API credentials to the Sasampa server and enable WhatsApp receipts.</p>

    <div class="step">
        <div class="step-header"><div class="step-number env">1</div><div><div class="step-title">SSH into the Server</div><div class="step-subtitle">Connect to production</div></div></div>
        <div class="step-body">
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>ssh wibo@46.202.128.164</div>
            <p>Enter server password when prompted, then:</p>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>cd /var/www/sasampa</div>
        </div>
    </div>

    <div class="step">
        <div class="step-header"><div class="step-number env">2</div><div><div class="step-title">Edit the .env File</div><div class="step-subtitle">Add API credentials</div></div></div>
        <div class="step-body">
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>sudo nano .env</div>
            <p>Scroll to the bottom and add your provider config. Pick ONE:</p>

            <h4>Option A: Meta WhatsApp Cloud API <span class="badge green">Recommended</span></h4>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button># ========================================
# WhatsApp & Messaging Configuration
# ========================================
WHATSAPP_PROVIDER=meta

# Meta WhatsApp Cloud API (from developers.facebook.com)
META_WHATSAPP_TOKEN=paste-your-permanent-token-here
META_WHATSAPP_PHONE_ID=paste-your-phone-number-id-here
META_WHATSAPP_BUSINESS_ID=paste-your-business-account-id-here</div>

            <h4>Option B: Africa's Talking</h4>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button># ========================================
# WhatsApp & Messaging Configuration
# ========================================
WHATSAPP_PROVIDER=africastalking

# Africa's Talking Credentials
AT_API_KEY=paste-your-api-key-here
AT_USERNAME=paste-your-username-here
AT_WHATSAPP_PRODUCT_ID=paste-your-whatsapp-product-id-here
AT_SANDBOX=false</div>

            <h4>Option C: Pindo</h4>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button># ========================================
# WhatsApp & Messaging Configuration
# ========================================
WHATSAPP_PROVIDER=pindo

# Pindo Credentials
PINDO_API_TOKEN=paste-your-api-token-here
PINDO_SENDER_ID=SASAMPA</div>

            <h4>Option D: Testing Mode (no real messages)</h4>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button># Stub mode — messages logged but not sent
WHATSAPP_PROVIDER=stub</div>

            <p>Save: <strong>Ctrl+X</strong> &gt; <strong>Y</strong> &gt; <strong>Enter</strong></p>
        </div>
    </div>

    <div class="step">
        <div class="step-header"><div class="step-number env">3</div><div><div class="step-title">Apply Config & Start Queue Worker</div><div class="step-subtitle">Essential commands</div></div></div>
        <div class="step-body">
            <h4>Clear and re-cache config:</h4>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>sudo php artisan config:clear && sudo php artisan config:cache</div>

            <h4>Start queue worker (required for async message delivery):</h4>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button># Quick test (stops when you disconnect):
sudo php artisan queue:work --queue=default --tries=3 --timeout=60</div>

            <h4>For production — use Supervisor (auto-restarts):</h4>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>sudo apt install supervisor -y
sudo nano /etc/supervisor/conf.d/sasampa-worker.conf</div>
            <p>Paste this config:</p>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>[program:sasampa-worker]
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
stopwaitsecs=3600</div>
            <p>Activate:</p>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start sasampa-worker:*</div>
        </div>
    </div>

    <div class="step">
        <div class="step-header"><div class="step-number env">4</div><div><div class="step-title">Enable in the App</div><div class="step-subtitle">Final step</div></div></div>
        <div class="step-body">
            <ol>
                <li>Open <strong>Sasampa POS</strong> mobile app</li>
                <li>Go to <strong>Settings</strong> &gt; <strong>"WhatsApp Receipts"</strong></li>
                <li>Toggle ON, choose Automatic or Prompted mode</li>
                <li>Tap <strong>"Send Test Message"</strong> to verify</li>
            </ol>
        </div>
    </div>

    <div class="step">
        <div class="step-header"><div class="step-number env">5</div><div><div class="step-title">Troubleshooting</div><div class="step-subtitle">Common issues & fixes</div></div></div>
        <div class="step-body">
            <h4>Check messaging log:</h4>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>tail -f /var/www/sasampa/storage/logs/messaging.log</div>
            <h4>Check/retry failed jobs:</h4>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>sudo php artisan queue:failed
sudo php artisan queue:retry all</div>
            <h4>Check worker status:</h4>
            <div class="code-block"><button class="copy-btn" onclick="copyCode(this)">Copy</button>sudo supervisorctl status sasampa-worker:*</div>
            <table class="compare-table">
                <thead><tr><th>Problem</th><th>Solution</th></tr></thead>
                <tbody>
                    <tr><td>"Provider not configured"</td><td>Check <code>WHATSAPP_PROVIDER</code> in .env, run <code>php artisan config:clear</code></td></tr>
                    <tr><td>Messages stuck "pending"</td><td>Queue worker not running — start with supervisor</td></tr>
                    <tr><td>"Authentication failed"</td><td>Double-check API key/token in .env</td></tr>
                    <tr><td>WhatsApp fails, no SMS fallback</td><td>Enable SMS Fallback in app settings</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>{{-- /content --}}

<script>
    function checkPassword() {
        const input = document.getElementById('authPassword').value;
        if (input === 'Sasampa@2026') {
            document.getElementById('authOverlay').classList.add('hidden');
            sessionStorage.setItem('sasampa_guide_auth', '1');
            return false;
        }
        document.getElementById('authError').style.display = 'block';
        document.getElementById('authPassword').value = '';
        document.getElementById('authPassword').focus();
        return false;
    }

    if (sessionStorage.getItem('sasampa_guide_auth') === '1') {
        document.getElementById('authOverlay').classList.add('hidden');
    }

    function switchTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.cat-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        document.querySelectorAll('.cat-btn').forEach(btn => {
            if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes("'" + tab + "'")) {
                btn.classList.add('active');
            }
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
        // Update URL hash for bookmarking
        history.replaceState(null, '', '/guide#' + tab);
    }

    // Handle hash in URL
    const hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById('tab-' + hash)) {
        switchTab(hash);
    }

    function copyCode(btn) {
        const block = btn.parentElement;
        const text = block.innerText.replace('Copy', '').replace('Copied!', '').trim();
        navigator.clipboard.writeText(text).then(() => {
            btn.textContent = 'Copied!';
            btn.classList.add('copied');
            setTimeout(() => { btn.textContent = 'Copy'; btn.classList.remove('copied'); }, 2000);
        });
    }

    function copyText(el) {
        const text = el.querySelector('.text').textContent;
        navigator.clipboard.writeText(text).then(() => {
            const icon = el.querySelector('.copy-icon');
            icon.textContent = '\u2713 Copied!';
            icon.style.color = '#25D366';
            setTimeout(() => { icon.textContent = '\uD83D\uDCCB Copy'; icon.style.color = '#999'; }, 2000);
        });
    }
</script>
</body>
</html>
