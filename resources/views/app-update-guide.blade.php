<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sasampa - App Update Guide</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        .auth-box input { width: 100%; padding: 14px 16px; border: 2px solid #e5e5e5; border-radius: 12px; font-size: 16px; text-align: center; letter-spacing: 4px; outline: none; transition: border-color 0.2s; }
        .auth-box input:focus { border-color: #FF2D20; }
        .auth-box .error { color: #e53e3e; font-size: 13px; margin-top: 8px; display: none; }
        .auth-box button { width: 100%; padding: 14px; background: #1a1a1a; color: #fff; border: none; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 16px; transition: background 0.2s; }
        .auth-box button:hover { background: #333; }

        /* Navigation */
        .nav { background: #fff; border-bottom: 1px solid #e5e5e5; padding: 16px 0; position: sticky; top: 0; z-index: 100; }
        .nav-inner { max-width: 900px; margin: 0 auto; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; }
        .nav-brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 18px; color: #1a1a1a; text-decoration: none; }
        .nav-brand svg { width: 28px; height: 28px; }
        .tab-nav { display: flex; gap: 0; background: #f0f0f0; border-radius: 10px; padding: 3px; }
        .tab-btn { padding: 8px 20px; border: none; background: transparent; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; color: #666; transition: all 0.2s; }
        .tab-btn.active { background: #fff; color: #1a1a1a; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }

        /* Content */
        .content { max-width: 900px; margin: 0 auto; padding: 40px 24px; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .section-title { font-size: 28px; font-weight: 700; margin-bottom: 8px; }
        .section-desc { color: #666; font-size: 16px; margin-bottom: 32px; }

        /* Steps */
        .step { background: #fff; border-radius: 16px; padding: 28px; margin-bottom: 20px; border: 1px solid #e8e8e8; }
        .step-header { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 16px; }
        .step-number { width: 36px; height: 36px; background: #1a1a1a; color: #fff; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; flex-shrink: 0; }
        .step-number.android { background: #3DDC84; color: #000; }
        .step-number.ios { background: #007AFF; color: #fff; }
        .step-title { font-size: 18px; font-weight: 600; margin-bottom: 4px; }
        .step-subtitle { font-size: 13px; color: #999; }
        .step-body { padding-left: 52px; }
        .step-body p { margin-bottom: 12px; color: #444; font-size: 15px; }
        .step-body ul { margin-bottom: 12px; padding-left: 20px; }
        .step-body li { margin-bottom: 6px; color: #444; font-size: 14px; }

        /* Links */
        .step-link { display: inline-flex; align-items: center; gap: 6px; background: #f0f0f0; color: #1a1a1a; padding: 10px 16px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 500; margin-top: 8px; margin-right: 8px; transition: background 0.2s; }
        .step-link:hover { background: #e5e5e5; }
        .step-link svg { width: 16px; height: 16px; }

        /* Code blocks */
        .code-block { background: #1a1a1a; color: #e5e5e5; padding: 16px 20px; border-radius: 10px; font-family: 'SF Mono', 'Fira Code', monospace; font-size: 13px; margin: 12px 0; overflow-x: auto; line-height: 1.6; }
        .code-block .comment { color: #6a9955; }
        .code-block .cmd { color: #dcdcaa; }

        /* Warning/Info boxes */
        .info-box { padding: 16px 20px; border-radius: 10px; margin: 12px 0; font-size: 14px; }
        .info-box.warning { background: #fff8e1; border: 1px solid #ffe082; color: #7c6200; }
        .info-box.tip { background: #e8f5e9; border: 1px solid #a5d6a7; color: #1b5e20; }
        .info-box.important { background: #fce4ec; border: 1px solid #ef9a9a; color: #b71c1c; }
        .info-box strong { font-weight: 600; }

        /* Checklist */
        .checklist { background: #f8f9fa; border-radius: 10px; padding: 20px; margin: 12px 0; }
        .checklist h4 { font-size: 15px; margin-bottom: 12px; }
        .check-item { display: flex; align-items: center; gap: 8px; padding: 6px 0; font-size: 14px; color: #444; }
        .check-item::before { content: ''; width: 18px; height: 18px; border: 2px solid #ccc; border-radius: 4px; flex-shrink: 0; }

        @media (max-width: 640px) {
            .section-title { font-size: 22px; }
            .step { padding: 20px; }
            .step-body { padding-left: 0; margin-top: 12px; }
            .tab-btn { padding: 8px 14px; font-size: 13px; }
        }
    </style>
</head>
<body>

<!-- Password Gate -->
<div class="auth-overlay" id="authOverlay">
    <div class="auth-box">
        <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" class="logo-icon">
            <defs><linearGradient id="authGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#FF2D20"/><stop offset="100%" style="stop-color:#E53E3E"/></linearGradient></defs>
            <rect width="32" height="32" rx="6" fill="url(#authGrad)"/>
            <rect x="7" y="7" width="8" height="8" rx="2" fill="#fff"/>
            <rect x="17" y="7" width="8" height="8" rx="2" fill="#fff"/>
            <rect x="7" y="17" width="8" height="8" rx="2" fill="#fff"/>
            <rect x="17" y="17" width="8" height="8" rx="2" fill="#fff"/>
        </svg>
        <h2>App Update Guide</h2>
        <p>Enter the admin password to continue</p>
        <form onsubmit="return checkPassword()">
            <input type="password" id="authPassword" placeholder="Password" autofocus>
            <div class="error" id="authError">Incorrect password. Try again.</div>
            <button type="submit">Continue</button>
        </form>
    </div>
</div>

<!-- Main Content -->
<nav class="nav">
    <div class="nav-inner">
        <a href="/" class="nav-brand">
            <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                <defs><linearGradient id="navGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#FF2D20"/><stop offset="100%" style="stop-color:#E53E3E"/></linearGradient></defs>
                <rect width="32" height="32" rx="6" fill="url(#navGrad)"/>
                <rect x="7" y="7" width="8" height="8" rx="2" fill="#fff"/>
                <rect x="17" y="7" width="8" height="8" rx="2" fill="#fff"/>
                <rect x="7" y="17" width="8" height="8" rx="2" fill="#fff"/>
                <rect x="17" y="17" width="8" height="8" rx="2" fill="#fff"/>
            </svg>
            Update Guide
        </a>
        <div class="tab-nav">
            <button class="tab-btn active" onclick="switchTab('android')">Android</button>
            <button class="tab-btn" onclick="switchTab('ios')">iOS</button>
        </div>
    </div>
</nav>

<div class="content">

    <!-- ==================== ANDROID TAB ==================== -->
    <div class="tab-content active" id="tab-android">
        <h1 class="section-title">Update Sasampa on Google Play Store</h1>
        <p class="section-desc">Step-by-step guide to publish a new version of Sasampa POS to the Play Store.</p>

        <div class="checklist">
            <h4>Before You Start — Pre-flight Checklist</h4>
            <div class="check-item">Code changes committed and pushed to GitHub</div>
            <div class="check-item">Version number bumped in pubspec.yaml (e.g. 1.0.2+8)</div>
            <div class="check-item">Tested on a real Android device</div>
            <div class="check-item">Upload key / keystore file ready</div>
        </div>

        <!-- Step 1 -->
        <div class="step">
            <div class="step-header">
                <div class="step-number android">1</div>
                <div>
                    <div class="step-title">Bump the Version Number</div>
                    <div class="step-subtitle">File: mobile_app/pubspec.yaml</div>
                </div>
            </div>
            <div class="step-body">
                <p>Open <code>pubspec.yaml</code> and update the version line. The format is <code>major.minor.patch+buildNumber</code>.</p>
                <div class="code-block">
<span class="comment"># Example: going from 1.0.1+7 to 1.0.2+8</span>
version: 1.0.2+8
                </div>
                <div class="info-box warning">
                    <strong>Important:</strong> The <code>buildNumber</code> (after the +) MUST be higher than the previous upload. Google Play rejects duplicate build numbers.
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="step">
            <div class="step-header">
                <div class="step-number android">2</div>
                <div>
                    <div class="step-title">Build the App Bundle (AAB)</div>
                    <div class="step-subtitle">Terminal command</div>
                </div>
            </div>
            <div class="step-body">
                <p>Google Play requires an App Bundle (.aab), not an APK. Run this from the <code>mobile_app</code> folder:</p>
                <div class="code-block">
cd ~/Desktop/Sasampa/mobile_app
<span class="cmd">flutter build appbundle --release</span>
                </div>
                <p>The output file will be at:</p>
                <div class="code-block">
build/app/outputs/bundle/release/app-release.aab
                </div>
                <div class="info-box tip">
                    <strong>Tip:</strong> If you get signing errors, ensure your <code>key.properties</code> and keystore file are in <code>android/</code>.
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="step">
            <div class="step-header">
                <div class="step-number android">3</div>
                <div>
                    <div class="step-title">Open Google Play Console</div>
                    <div class="step-subtitle">Navigate to your app</div>
                </div>
            </div>
            <div class="step-body">
                <p>Sign in to the Google Play Console and select the Sasampa POS app.</p>
                <a href="https://play.google.com/console" target="_blank" class="step-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Open Google Play Console
                </a>
            </div>
        </div>

        <!-- Step 4 -->
        <div class="step">
            <div class="step-header">
                <div class="step-number android">4</div>
                <div>
                    <div class="step-title">Create a New Release</div>
                    <div class="step-subtitle">Production track</div>
                </div>
            </div>
            <div class="step-body">
                <p>In the left sidebar, go to:</p>
                <ul>
                    <li><strong>Release</strong> > <strong>Production</strong></li>
                    <li>Click the <strong>"Create new release"</strong> button (top right)</li>
                </ul>
                <a href="https://play.google.com/console/developers/app/releases/overview" target="_blank" class="step-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Go to Releases
                </a>
                <div class="info-box tip">
                    <strong>Where exactly:</strong> Left sidebar > Release > Production > "Create new release" button at top-right of the page.
                </div>
            </div>
        </div>

        <!-- Step 5 -->
        <div class="step">
            <div class="step-header">
                <div class="step-number android">5</div>
                <div>
                    <div class="step-title">Upload the AAB File</div>
                    <div class="step-subtitle">Drag and drop</div>
                </div>
            </div>
            <div class="step-body">
                <p>On the "Create new release" page:</p>
                <ul>
                    <li>In the <strong>"App bundles"</strong> section, click <strong>"Upload"</strong></li>
                    <li>Select the file: <code>build/app/outputs/bundle/release/app-release.aab</code></li>
                    <li>Wait for it to finish processing (usually 30-60 seconds)</li>
                </ul>
                <div class="info-box warning">
                    <strong>If upload fails:</strong> Check that the version code (build number) is higher than the previous release. You can see the current version in the release overview.
                </div>
            </div>
        </div>

        <!-- Step 6 -->
        <div class="step">
            <div class="step-header">
                <div class="step-number android">6</div>
                <div>
                    <div class="step-title">Add Release Notes</div>
                    <div class="step-subtitle">What's new in this version</div>
                </div>
            </div>
            <div class="step-body">
                <p>Scroll down to <strong>"Release notes"</strong> and add a description of what changed. Example:</p>
                <div class="code-block">
<span class="comment">What's new:</span>
- Dashboard customization (choose layout, toggle widgets)
- Improved proforma receipt design
- Updated app icon
- Bug fixes and performance improvements
                </div>
            </div>
        </div>

        <!-- Step 7 -->
        <div class="step">
            <div class="step-header">
                <div class="step-number android">7</div>
                <div>
                    <div class="step-title">Review and Roll Out</div>
                    <div class="step-subtitle">Final step</div>
                </div>
            </div>
            <div class="step-body">
                <ul>
                    <li>Click <strong>"Review release"</strong> at the bottom of the page</li>
                    <li>Check for any warnings or errors</li>
                    <li>Click <strong>"Start rollout to Production"</strong></li>
                    <li>Confirm by clicking <strong>"Roll out"</strong></li>
                </ul>
                <div class="info-box tip">
                    <strong>Timeline:</strong> The update typically goes live within 1-3 hours, but can take up to 24 hours for Google's review.
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== iOS TAB ==================== -->
    <div class="tab-content" id="tab-ios">
        <h1 class="section-title">Update Sasampa on the App Store</h1>
        <p class="section-desc">Step-by-step guide to publish a new version of Sasampa POS to the Apple App Store and TestFlight.</p>

        <div class="checklist">
            <h4>Before You Start — Pre-flight Checklist</h4>
            <div class="check-item">Code changes committed and pushed to GitHub</div>
            <div class="check-item">Version number bumped in pubspec.yaml (e.g. 1.0.2+8)</div>
            <div class="check-item">Tested on a real iOS device or simulator</div>
            <div class="check-item">Apple Developer account active (Team: MP3N9HDNTH)</div>
            <div class="check-item">Xcode installed and up to date</div>
        </div>

        <!-- Step 1 -->
        <div class="step">
            <div class="step-header">
                <div class="step-number ios">1</div>
                <div>
                    <div class="step-title">Bump the Version Number</div>
                    <div class="step-subtitle">File: mobile_app/pubspec.yaml</div>
                </div>
            </div>
            <div class="step-body">
                <p>Same as Android — update the version in <code>pubspec.yaml</code>:</p>
                <div class="code-block">
<span class="comment"># Example: going from 1.0.1+7 to 1.0.2+8</span>
version: 1.0.2+8
                </div>
                <div class="info-box warning">
                    <strong>Important:</strong> The build number (after +) must be unique. App Store Connect rejects duplicate build numbers even if the version string changes.
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="step">
            <div class="step-header">
                <div class="step-number ios">2</div>
                <div>
                    <div class="step-title">Build the IPA</div>
                    <div class="step-subtitle">Terminal command</div>
                </div>
            </div>
            <div class="step-body">
                <p>Run this from the <code>mobile_app</code> folder:</p>
                <div class="code-block">
cd ~/Desktop/Sasampa/mobile_app
<span class="cmd">flutter build ipa --release</span>
                </div>
                <p>The output file will be at:</p>
                <div class="code-block">
build/ios/ipa/sasampa_pos.ipa
                </div>
                <div class="info-box tip">
                    <strong>Tip:</strong> If you see signing errors, open <code>ios/Runner.xcworkspace</code> in Xcode and verify the Team is set to MP3N9HDNTH under Signing & Capabilities.
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="step">
            <div class="step-header">
                <div class="step-number ios">3</div>
                <div>
                    <div class="step-title">Upload with Transporter</div>
                    <div class="step-subtitle">Apple's official upload tool</div>
                </div>
            </div>
            <div class="step-body">
                <p>Open the <strong>Transporter</strong> app (free from Mac App Store). If you don't have it:</p>
                <a href="https://apps.apple.com/app/transporter/id1450874784" target="_blank" class="step-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Download Transporter from Mac App Store
                </a>
                <p style="margin-top:12px">Then:</p>
                <ul>
                    <li>Open Transporter and sign in with your Apple Developer account</li>
                    <li>Drag the <code>.ipa</code> file into the Transporter window</li>
                    <li>Click <strong>"Deliver"</strong></li>
                    <li>Wait for upload and processing (usually 5-15 minutes)</li>
                </ul>
                <div class="info-box tip">
                    <strong>Alternative:</strong> You can also upload via Xcode: Open <code>ios/Runner.xcworkspace</code> > Product > Archive > Distribute App > App Store Connect.
                </div>
            </div>
        </div>

        <!-- Step 4 -->
        <div class="step">
            <div class="step-header">
                <div class="step-number ios">4</div>
                <div>
                    <div class="step-title">Open App Store Connect</div>
                    <div class="step-subtitle">Navigate to your app</div>
                </div>
            </div>
            <div class="step-body">
                <p>Sign in to App Store Connect and select the Sasampa POS app.</p>
                <a href="https://appstoreconnect.apple.com/apps" target="_blank" class="step-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Open App Store Connect
                </a>
            </div>
        </div>

        <!-- Step 5 - TestFlight -->
        <div class="step">
            <div class="step-header">
                <div class="step-number ios">5</div>
                <div>
                    <div class="step-title">Test with TestFlight (Optional but Recommended)</div>
                    <div class="step-subtitle">Internal testing before public release</div>
                </div>
            </div>
            <div class="step-body">
                <p>After the build finishes processing (you'll get an email from Apple, usually 15-30 min after upload):</p>
                <ul>
                    <li>Go to your app in App Store Connect</li>
                    <li>Click the <strong>"TestFlight"</strong> tab at the top</li>
                    <li>Find your new build under <strong>"iOS Builds"</strong></li>
                    <li>If it says "Missing Compliance" — click <strong>"Manage"</strong> and select <strong>"None of the algorithms mentioned above"</strong> (unless you use custom encryption)</li>
                    <li>Add testers under <strong>"Internal Testing"</strong> > your group > <strong>"+"</strong> button</li>
                </ul>
                <a href="https://appstoreconnect.apple.com/apps" target="_blank" class="step-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Go to TestFlight
                </a>
                <div class="info-box tip">
                    <strong>Where exactly:</strong> App Store Connect > Your App > TestFlight tab > iOS Builds > click the build number > Manage Compliance > then add testers.
                </div>
            </div>
        </div>

        <!-- Step 6 -->
        <div class="step">
            <div class="step-header">
                <div class="step-number ios">6</div>
                <div>
                    <div class="step-title">Create a New Version in App Store</div>
                    <div class="step-subtitle">Prepare for submission</div>
                </div>
            </div>
            <div class="step-body">
                <p>To push the update to the public App Store:</p>
                <ul>
                    <li>Go to your app in App Store Connect</li>
                    <li>Click the <strong>"App Store"</strong> tab (not TestFlight)</li>
                    <li>In the left sidebar, click <strong>"+ Version or Platform"</strong> (blue text, top left under iOS App)</li>
                    <li>Enter the new version number (e.g. <code>1.0.2</code>)</li>
                    <li>Click <strong>"Create"</strong></li>
                </ul>
                <div class="info-box tip">
                    <strong>Where exactly:</strong> App Store Connect > Your App > App Store tab > left sidebar shows versions. Click the blue "+" next to "iOS App" at the top.
                </div>
            </div>
        </div>

        <!-- Step 7 -->
        <div class="step">
            <div class="step-header">
                <div class="step-number ios">7</div>
                <div>
                    <div class="step-title">Select the Build & Add Release Notes</div>
                    <div class="step-subtitle">Link your uploaded build</div>
                </div>
            </div>
            <div class="step-body">
                <p>On the new version page, scroll down to the <strong>"Build"</strong> section:</p>
                <ul>
                    <li>Click the <strong>"+"</strong> button next to "Build"</li>
                    <li>Select your newly uploaded build from the list</li>
                    <li>Click <strong>"Done"</strong></li>
                </ul>
                <p>Then scroll to <strong>"What's New in This Version"</strong> and add your release notes.</p>
                <div class="info-box warning">
                    <strong>If the build doesn't appear:</strong> Wait 15-30 minutes after uploading. Apple processes builds before they show up. You'll get an email when it's ready.
                </div>
            </div>
        </div>

        <!-- Step 8 -->
        <div class="step">
            <div class="step-header">
                <div class="step-number ios">8</div>
                <div>
                    <div class="step-title">Submit for Review</div>
                    <div class="step-subtitle">Final step</div>
                </div>
            </div>
            <div class="step-body">
                <ul>
                    <li>Scroll to the top of the version page</li>
                    <li>Click <strong>"Add for Review"</strong> (blue button, top right)</li>
                    <li>On the next screen, click <strong>"Submit to App Review"</strong></li>
                </ul>
                <div class="info-box tip">
                    <strong>Timeline:</strong> App Review typically takes 24-48 hours. You'll get an email when it's approved. The update goes live automatically after approval (unless you chose manual release).
                </div>
                <div class="info-box important">
                    <strong>If rejected:</strong> Apple will send you an email explaining why. Common reasons: missing privacy labels, crashes on their test devices, or guideline violations. Fix the issue, upload a new build, and resubmit.
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    // Password protection
    const CORRECT_HASH = '{{ md5("Sasampa2026!") }}';

    function checkPassword() {
        const input = document.getElementById('authPassword').value;
        // Simple client-side check (the real security is server-side if needed)
        if (input === 'Sasampa2026!') {
            document.getElementById('authOverlay').classList.add('hidden');
            sessionStorage.setItem('guide_auth', '1');
            return false;
        }
        document.getElementById('authError').style.display = 'block';
        document.getElementById('authPassword').value = '';
        document.getElementById('authPassword').focus();
        return false;
    }

    // Check if already authed this session
    if (sessionStorage.getItem('guide_auth') === '1') {
        document.getElementById('authOverlay').classList.add('hidden');
    }

    // Tab switching
    function switchTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        event.target.classList.add('active');
    }
</script>

</body>
</html>
