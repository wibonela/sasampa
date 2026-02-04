 Sasampa POS Mobile App Implementation Plan                                                                                                                               
                                                                                                                                                                        
 Overview

 Build premium iOS & Android mobile apps for Sasampa POS with a separate mobile access request/verification flow before companies can use the app.

 Framework: Flutter (single codebase, excellent for premium custom UI)

 Design Philosophy: Apple/Notion-like aesthetics with Microsoft Teams touches - clean, minimal, premium feel.

 Design References:
 - Apple built-in apps (Settings, Notes, Calendar) - Clean iOS patterns
 - Notion - Minimal, content-focused, excellent typography
 - Square POS / Toast - Professional POS workflows, efficient checkout

 Team: Plan designed for handoff to hired Flutter developers.

 ---
 Part 1: Backend API Development (Laravel)

 1.1 Install Laravel Sanctum for API Authentication

 composer require laravel/sanctum
 php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
 php artisan migrate

 Why Sanctum over Passport: Lighter weight, perfect for first-party mobile apps, no OAuth complexity needed.

 1.2 New Database Tables

 mobile_app_requests table - Separate from web company approval:
 - id
 - company_id (FK)
 - status: pending | approved | rejected | revoked
 - request_reason (text)
 - expected_devices (int)
 - approved_at, rejected_at
 - rejection_reason
 - reviewed_by (FK users)
 - timestamps

 mobile_devices table - Track registered devices:
 - id
 - company_id, user_id (FKs)
 - device_identifier (unique)
 - device_name, device_model, os_version, app_version
 - push_token (for notifications)
 - is_active
 - last_active_at
 - timestamps

 1.3 API Route Structure

 Create /routes/api.php:

 /api/v1/
 ├── auth/
 │   ├── POST   /login              # Email + password → token
 │   ├── POST   /login/pin          # Email + PIN → token
 │   ├── POST   /logout             # Revoke token
 │   └── GET    /user               # Get current user
 │
 ├── mobile-access/
 │   ├── POST   /request            # Company requests mobile access
 │   ├── GET    /status             # Check request status
 │   └── POST   /register-device    # Register device after approval
 │
 ├── pos/
 │   ├── GET    /products           # List products with search/category
 │   ├── GET    /categories         # List categories
 │   ├── POST   /checkout           # Process sale
 │   ├── GET    /transactions       # Transaction history
 │   └── GET    /transactions/{id}  # Transaction details + receipt
 │
 ├── inventory/
 │   ├── GET    /                   # Inventory list
 │   └── POST   /{product}/adjust   # Stock adjustment
 │
 ├── reports/
 │   └── GET    /dashboard          # Dashboard summary stats
 │
 └── sync/
     ├── GET    /pull               # Pull data changes since timestamp
     └── POST   /push               # Push offline transactions

 1.4 New Controllers

 Location: /app/Http/Controllers/Api/V1/
 ┌────────────────────────────┬──────────────────────────────────────────┐
 │         Controller         │                 Purpose                  │
 ├────────────────────────────┼──────────────────────────────────────────┤
 │ AuthController.php         │ Token-based login (email/password + PIN) │
 ├────────────────────────────┼──────────────────────────────────────────┤
 │ MobileAccessController.php │ Handle access request flow               │
 ├────────────────────────────┼──────────────────────────────────────────┤
 │ ProductController.php      │ API products/categories                  │
 ├────────────────────────────┼──────────────────────────────────────────┤
 │ POSController.php          │ Checkout operations                      │
 ├────────────────────────────┼──────────────────────────────────────────┤
 │ TransactionController.php  │ Transaction history                      │
 ├────────────────────────────┼──────────────────────────────────────────┤
 │ SyncController.php         │ Offline data sync                        │
 └────────────────────────────┴──────────────────────────────────────────┘
 1.5 New Middleware
 ┌────────────────────────────┬──────────────────────────────────────────┐
 │         Middleware         │                 Purpose                  │
 ├────────────────────────────┼──────────────────────────────────────────┤
 │ EnsureMobileAccessApproved │ Check company has approved mobile access │
 ├────────────────────────────┼──────────────────────────────────────────┤
 │ EnsureDeviceRegistered     │ Validate device is registered            │
 ├────────────────────────────┼──────────────────────────────────────────┤
 │ TrackDeviceActivity        │ Update last_active_at                    │
 └────────────────────────────┴──────────────────────────────────────────┘
 1.6 Admin Web Interface for Mobile Access

 New admin routes under /admin/mobile-access/:
 - List pending requests with company details
 - Approve/Reject with reason
 - Revoke access for violations
 - View registered devices per company

 1.7 Critical Files to Modify
 ┌────────────────────────┬─────────────────────────────────────┐
 │          File          │               Changes               │
 ├────────────────────────┼─────────────────────────────────────┤
 │ app/Models/User.php    │ Add HasApiTokens trait from Sanctum │
 ├────────────────────────┼─────────────────────────────────────┤
 │ app/Models/Company.php │ Add mobileAppRequest() relationship │
 ├────────────────────────┼─────────────────────────────────────┤
 │ config/sanctum.php     │ Configure token expiration          │
 ├────────────────────────┼─────────────────────────────────────┤
 │ bootstrap/app.php      │ Register API middleware             │
 └────────────────────────┴─────────────────────────────────────┘
 ---
 Part 2: Mobile App Architecture

 2.1 Framework: Flutter

 Rationale:
 - Single codebase for iOS + Android (95%+ code sharing)
 - Excellent premium UI capabilities
 - Strong offline support with SQLite
 - Good ecosystem for POS features (printing, scanning)

 2.2 Project Structure

 lib/
 ├── app/
 │   ├── router/app_router.dart
 │   └── theme/
 │       ├── colors.dart
 │       ├── typography.dart
 │       └── spacing.dart
 ├── core/
 │   ├── network/api_client.dart
 │   ├── storage/local_database.dart
 │   └── sync/sync_manager.dart
 ├── features/
 │   ├── auth/
 │   ├── onboarding/
 │   ├── pos/
 │   ├── products/
 │   ├── transactions/
 │   ├── reports/
 │   └── settings/
 └── shared/widgets/

 2.3 Key Dependencies

 # State Management
 flutter_riverpod: ^2.4.0

 # Navigation
 go_router: ^13.0.0

 # Network
 dio: ^5.4.0
 connectivity_plus: ^5.0.0

 # Local Database
 drift: ^2.14.0 (SQLite)

 # Hardware
 mobile_scanner: ^4.0.0      # Barcode scanning
 bluetooth_print: ^4.3.0     # Thermal printing
 local_auth: ^2.1.0          # Biometrics

 # Push Notifications
 firebase_messaging: ^14.7.0

 ---
 Part 3: Premium UI/UX Design System

 3.1 Color Palette (Extending existing)

 // Primary
 primary: #007AFF (Apple Blue)
 accent: #5856D6 (Purple)

 // Semantic
 success: #34C759 (Green)
 warning: #FF9500 (Orange)
 error: #FF3B30 (Red)

 // Grayscale
 gray1-6: #8E8E93 → #F2F2F7

 // Backgrounds
 background: #FFFFFF
 backgroundSecondary: #F5F5F7

 3.2 Typography

 Display: 34px, Bold, -0.4 letter-spacing
 Headline 1: 28px, Bold
 Headline 2: 22px, Bold
 Body Large: 17px, Regular
 Body Medium: 15px, Regular
 Caption: 12px, Regular

 Font: SF Pro (iOS) / Roboto (Android)

 3.3 Navigation Pattern

 Phone: Bottom tab bar (Home, POS, Transactions, Settings)

 Tablet: Persistent sidebar + main content area with optional split view

 3.4 Key Design Principles

 1. Content-first: Minimal chrome, let data breathe
 2. Generous whitespace: 16px page padding, 8px base unit
 3. Subtle shadows: Soft elevation, not harsh
 4. Smooth animations: 150-300ms transitions
 5. Touch-friendly: 44px minimum touch targets

 ---
 Part 4: Mobile Access Request Flow

 ┌─────────────────────────────────────────────────────┐
 │                    USER JOURNEY                      │
 └─────────────────────────────────────────────────────┘

 1. Download app from App Store / Play Store

 2. Launch → Onboarding screens (3 slides)

 3. Login with existing web credentials
    └─ Must already have approved web account

 4. Check: Does company have mobile access?
    ├─ YES → Register device → Full app access
    └─ NO → Show "Request Mobile Access" screen
            └─ Company owner submits request
               ├─ Request reason
               └─ Expected # of devices

 5. Status: PENDING
    └─ Show waiting screen with status check button
    └─ Admin receives notification (email + web)

 6. Admin reviews request
    ├─ APPROVE → User notified, can register device
    └─ REJECT → User sees reason, can contact support

 7. After approval:
    └─ Register device → Set up PIN (or use existing)
    └─ Enable biometrics (optional)
    └─ Full POS access

 ---
 Part 5: Core Screens

 5.1 Onboarding (3 slides)

 - Welcome with animated logo
 - Key features overview
 - Get Started / Login

 5.2 Login

 - Email + Password (primary)
 - PIN login option (for quick access)
 - Biometric unlock (after setup)

 5.3 Request Access (if needed)

 - Form: reason, expected devices
 - Clear explanation of process
 - Status check capability

 5.4 Dashboard

 - Today/Month sales summary
 - Transaction count
 - Low stock alerts
 - Quick actions (New Sale, Add Product, View Inventory)
 - Recent transactions

 5.5 POS Interface

 - Phone: Products grid → floating cart bar → bottom sheet cart
 - Tablet: Split view (products | cart sidebar)
 - Search + category tabs
 - Barcode scanner button
 - Quantity selector modal
 - Payment modal (method, amount, change)
 - Success confirmation with print/share options

 5.6 Transaction History

 - Grouped by date
 - Filter by status, payment method, date range
 - Tap for details + receipt view

 5.7 Reports (Simplified)

 - Period selector (Today/Week/Month/Custom)
 - Sales total + chart
 - Top products
 - Link to "View Full Report" on web

 5.8 Settings

 - Profile info
 - Change PIN / Password
 - Biometrics toggle
 - Dark mode
 - Printer setup
 - Notifications
 - Help / Support
 - Logout

 ---
 Part 6: Offline Capabilities

 6.1 Data Sync Strategy
 ┌─────────────────────┬──────────────────────────────────┐
 │      Data Type      │             Strategy             │
 ├─────────────────────┼──────────────────────────────────┤
 │ Products/Categories │ Pull from server, cache locally  │
 ├─────────────────────┼──────────────────────────────────┤
 │ Transactions        │ Create offline, sync when online │
 ├─────────────────────┼──────────────────────────────────┤
 │ Inventory           │ Read from cache, queue writes    │
 └─────────────────────┴──────────────────────────────────┘
 6.2 Sync Manager

 - Pull changes since last sync timestamp
 - Queue offline transactions with local UUID
 - Retry failed syncs up to 5 times
 - Show sync status indicator
 - Manual "Sync Now" button

 6.3 Conflict Resolution

 - Server wins for products/categories
 - Offline transactions assigned server ID on sync
 - Stock adjustments queued with timestamps

 ---
 Part 7: Hardware Integration

 7.1 Barcode Scanning

 - Camera-based scanning with mobile_scanner
 - Quick product lookup by barcode/SKU
 - Sound/haptic feedback on scan

 7.2 Thermal Printing

 - Bluetooth ESC/POS printer support
 - 80mm receipt format (matching web)
 - Printer discovery and pairing
 - Default printer setting

 7.3 Biometric Auth

 - Face ID / Touch ID (iOS)
 - Fingerprint / Face (Android)
 - Optional, enabled in settings
 - Fallback to PIN

 ---
 Part 8: Implementation Phases

 Phase 1: Backend API (Weeks 1-3)

 - Install Sanctum, create migrations
 - Build auth endpoints (login, PIN login, logout)
 - Build mobile access request system
 - Build POS API endpoints
 - Build admin approval interface
 - API tests

 Phase 2: Flutter Foundation (Weeks 4-6)

 - Project setup with architecture
 - Design system implementation
 - Auth flows (login, PIN, biometrics)
 - Local database setup (Drift)
 - API client with token management

 Phase 3: Core Features (Weeks 7-11)

 - Dashboard screen
 - POS interface (products, cart, checkout)
 - Transaction history
 - Offline sync manager
 - Push notifications setup

 Phase 4: Advanced Features (Weeks 12-14)

 - Barcode scanning
 - Bluetooth printing
 - Reports screens
 - Settings/Profile
 - Tablet layouts

 Phase 5: Polish & Launch (Weeks 15-17)

 - Animations and micro-interactions
 - Performance optimization
 - Beta testing
 - App Store / Play Store submission

 ---
 Verification Plan

 Backend Testing

 1. Run php artisan test for API endpoint tests
 2. Test mobile access request flow manually via Postman
 3. Verify token authentication works correctly
 4. Test offline transaction sync

 Mobile Testing

 1. Test on multiple device sizes (phone + tablet)
 2. Test offline mode (airplane mode)
 3. Test barcode scanning with various barcodes
 4. Test receipt printing with thermal printer
 5. Test biometric authentication

 Integration Testing

 1. Complete checkout flow (mobile → API → database)
 2. Sync offline transactions and verify in web dashboard
 3. Admin approve mobile access → user can login

 ---
 Key Files Reference

 Backend (to modify/create):
 - app/Models/User.php - Add HasApiTokens
 - app/Models/Company.php - Add mobile access relationship
 - routes/api.php - New API routes
 - app/Http/Controllers/Api/V1/* - New controllers
 - database/migrations/* - New tables

 Design Reference:
 - resources/css/app.css - Color palette, typography to replicate
 - resources/views/pos/index.blade.php - POS layout reference

 ---
 Hiring Developers: What to Look For

 Required Skills for Flutter Team

 Must Have:
 - 3+ years Flutter experience with published apps
 - Experience with Riverpod or similar state management
 - Offline-first app development (SQLite/Drift)
 - REST API integration with token auth
 - iOS and Android deployment experience

 Nice to Have:
 - POS/retail app experience
 - Bluetooth printer integration
 - Barcode scanning implementation
 - Firebase (for push notifications)
 - Laravel API experience (helpful for backend work)

 Interview Questions for Candidates

 1. "Show me an app you built with custom UI components"
 2. "How would you implement offline transaction queueing?"
 3. "Explain your approach to state management in Flutter"
 4. "Have you integrated hardware (printers/scanners) before?"

 Team Structure Recommendation

 Option A: Small Team (2-3 people)
 - 1 Senior Flutter Developer (lead)
 - 1 Mid-level Flutter Developer
 - Backend work: You/existing Laravel developer

 Option B: Full Team (4-5 people)
 - 1 Flutter Lead
 - 1-2 Flutter Developers
 - 1 Laravel Backend Developer
 - 1 UI/UX Designer (part-time)

 Where to Find Developers

 1. Upwork/Toptal - Filter for Flutter + POS experience
 2. LinkedIn - Search "Flutter Developer" + "POS" or "retail"
 3. Flutter communities - r/FlutterDev, Flutter Discord
 4. Local agencies - If budget allows, full-service mobile agencies

 Budget Estimates (Rough)
 ┌────────────────┬────────────┬───────────────────┐
 │    Approach    │  Timeline  │ Cost Range (USD)  │
 ├────────────────┼────────────┼───────────────────┤
 │ Freelance team │ 4-5 months │ $15,000 - $30,000 │
 ├────────────────┼────────────┼───────────────────┤
 │ Agency         │ 3-4 months │ $40,000 - $80,000 │
 ├────────────────┼────────────┼───────────────────┤
 │ In-house hire  │ 5-6 months │ Salary-based      │
 └────────────────┴────────────┴───────────────────┘
 Costs vary significantly by region and developer experience

 ---
 Deliverables for Developer Handoff

 When hiring developers, provide them with:

 1. This plan document (exported as PDF)
 2. API specification (we'll create OpenAPI/Swagger docs)
 3. Design mockups (recommend using Figma)
 4. Access to web codebase (for reference)
 5. Test API credentials (staging environment)
 6. Design system assets (colors, fonts, icons)

 ---
 Next Steps

 Immediate (This Week)

 1. Build the backend API - I can help implement the Laravel API endpoints
 2. Create API documentation - Swagger/OpenAPI spec for developers
 3. Set up staging environment - Separate from production

 Short Term (Next 2-4 Weeks)

 4. Create Figma mockups - Either hire a designer or use AI tools
 5. Write job posting - Based on skills list above
 6. Start interviewing - Review portfolios, conduct technical interviews

 Development Phase

 7. Onboard developers - Share all deliverables
 8. Weekly check-ins - Review progress, provide feedback
 9. Beta testing - Internal testing before public release
 10. App Store submission - Developer handles with your accounts
╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌