<p align="center">
  <img src="public/logo.png" alt="Sasampa POS" width="120">
</p>

<h1 align="center">Sasampa POS</h1>

<p align="center">
  <strong>Modern Point of Sale System for East African Businesses</strong>
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#installation">Installation</a> •
  <a href="#billing">Billing</a> •
  <a href="#deployment">Deployment</a> •
  <a href="#documentation">Documentation</a>
</p>

---

## Overview

Sasampa POS is a cloud-ready, multi-tenant point of sale system built for businesses in Tanzania and East Africa. It ships as a Laravel 12 web app plus a Flutter mobile app that talks to a versioned REST API.

## Features

### Selling and stock
- **Point of Sale** - Fast checkout, receipt printing and PDF receipts
- **Products and Categories** - SKUs, barcodes, cost and selling prices
- **Inventory** - Real-time stock levels, adjustments with history, low-stock alerts
- **Transactions** - Complete sales history with void capability
- **Customers (Wateja)** - Customer records with credit sales, payments and adjustments
- **Orders and Proforma** - Create orders and proforma invoices, convert them to sales

### Money and reporting
- **Expenses (Matumizi)** - Categories, suppliers and recurring expenses
- **Reports** - Sales, products, inventory, staff and profit, with PDF and CSV export
- **Profit Breakdown** - Customizable profit report (profit = sales - COGS, expenses tracked separately)
- **Profit Analytics** - Trends and per-branch profitability
- **TRA / EFD** - Fiscal receipt submission with retry for failed submissions
- **WhatsApp Receipts** - Send receipts to customers (Meta, Africa's Talking, Pindo, or a stub provider)

### Multi-tenant and multi-branch
- **Company Isolation** - Every record is scoped by `company_id`
- **Roles and Permissions** - Platform Admin, Company Owner and Cashier, with per-user permissions
- **PIN Login** - Fast cashier switching on shared devices
- **Branches** - Multiple locations with shared or independent catalogs, branch switching and branch-level reports
- **Onboarding and Approval** - Guided signup with email verification, signup throttling and honeypot protection

### Mobile app and API
- **Flutter app** (`mobile_app/`) - iOS and Android, English and Kiswahili, barcode scanning, offline sync
- **REST API v1** (`/api/v1`) - Laravel Sanctum token auth, see [`docs/api/README.md`](docs/api/README.md)
- **Mobile access approval** - Companies request mobile access, devices are registered and can be revoked

### Billing
- Tiered monthly plans with limits and feature gating, paid by Selcom mobile money or recorded manually. See [Billing](#billing).

### Documentation
- **In-App Help** - Searchable documentation in English and Kiswahili

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12 (PHP 8.2+, production runs 8.4) |
| Frontend | Blade, Bootstrap 5, Vite |
| Database | SQLite (default) or MySQL |
| Auth | Laravel Breeze (web), Laravel Sanctum (API) |
| PDF | barryvdh/laravel-dompdf |
| Queue / Cache / Sessions | Database drivers |
| Mobile | Flutter, Riverpod, Dio |

## Requirements

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ and NPM
- SQLite or MySQL 8.0+
- Flutter SDK (only to build the mobile app)

## Installation

```bash
# Clone the repository
git clone https://github.com/wibonela/sasampa.git
cd sasampa

# Install dependencies
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Database (SQLite)
touch database/database.sqlite
php artisan migrate

# Seed demo data (optional)
php artisan db:seed

# Build assets and run
npm run build
php artisan serve
```

Emails, WhatsApp receipts and subscription reminders are queued, so run a worker and the scheduler:

```bash
php artisan queue:work
php artisan schedule:work        # locally; in production use a cron entry for `schedule:run`
```

### Tests

```bash
php artisan test
```

## Billing

Billing is built but **off by default**. While `BILLING_ENFORCED=false` nothing is gated or limited and every company behaves as it did before billing existed.

### How it works
- **Plans** (`plans` table, seeded by the migration; edit `database/seeders/PlanSeeder.php` for prices and limits): Starter, Business and Multi-branch, with limits on users, branches and products and a list of included features (`full_reports`, `expenses`, `export`, `whatsapp_receipts`).
- **Subscriptions** run for prepaid months (1, 3, 6 or 12, with discounts). New companies get a free trial, and when a period ends the company keeps working with a short grace window, then drops to the Starter plan. Nothing is deleted and selling is never blocked.
- **Payments** are recorded either online through Selcom or manually by an admin (cash, bank, mobile money).
- **Mobile app** shows no pricing or plan wording. A hidden feature returns a neutral `feature_unavailable` error. Billing is managed on the web only.

### Turning it on
```bash
php artisan migrate                # creates billing tables and seeds plans
php artisan billing:backfill       # gives existing approved companies a grace period (default 30 days)
# then set BILLING_ENFORCED=true in .env and clear the config cache
```

### Commands (all scheduled)

| Command | Purpose |
|---------|---------|
| `subscriptions:expire` | Move lapsed subscriptions to past due, then expired (daily) |
| `subscriptions:remind` | Email owners 7, 3 and 1 days before the end date and on it (daily, only when enforced) |
| `payments:verify-pending` | Re-check pending Selcom payments in case a webhook was missed (every 5 minutes) |
| `billing:backfill` | One-off: create subscriptions for existing companies |

### Admin and owner screens
- **Admin** - On each company page (`/admin/companies/{id}`): set plan and end date, record a payment.
- **Owner** - `/billing`: plan, renewal date, payment history, and "Pay now" once Selcom is configured.

### Selcom (online payment)
Leave the Selcom variables empty to keep online payment off; the billing page then shows manual payment instructions.

The webhook is `POST /api/webhooks/selcom`. It is never trusted on its own: it only triggers a signed order-status lookup with Selcom, and only that result marks a payment paid.

## Environment Variables

| Variable | Description |
|----------|-------------|
| `APP_NAME` | Application name |
| `APP_ENV` | Environment (local/production) |
| `APP_KEY` | Application encryption key |
| `APP_URL` | Application URL |
| `DB_CONNECTION` | Database driver (sqlite/mysql) |
| `QUEUE_CONNECTION` | Queue driver (`database`) |
| `MAIL_MAILER` | Set to a real mailer in production, not `log` |
| `WHATSAPP_PROVIDER` | `meta`, `africas_talking`, `pindo` or `stub` (see `config/messaging.php`) |
| `BILLING_ENFORCED` | Master billing switch (default `false`) |
| `BILLING_GRACE_DAYS` | Access given to existing companies by `billing:backfill` (default 30) |
| `BILLING_TRIAL_DAYS` | Free trial for new companies (default 14) |
| `BILLING_PAYMENT_INSTRUCTIONS` | Text shown on `/billing` when online payment is off |
| `SELCOM_BASE_URL` | Selcom API gateway URL |
| `SELCOM_API_KEY` | Selcom API key |
| `SELCOM_API_SECRET` | Selcom API secret |
| `SELCOM_VENDOR` | Selcom vendor / till id |

## Deployment

### Safe Deployment

From your local machine, commit your changes and run:

```bash
./deploy-local.sh
```

It pushes to GitHub and runs the production deploy. On the server the deploy script:

1. Takes a full backup (database, `.env`, uploads)
2. Puts the app in maintenance mode
3. Pulls the latest code and updates dependencies
4. Runs migrations
5. Rebuilds caches
6. Runs a health check and rolls back automatically if it fails

Backups are kept in `/var/backups/sasampa` (last 10 retained).

### Rollback

```bash
sudo /var/www/sasampa/rollback.sh
```

The script lets you restore the database only, database and environment, or everything.

## Project Structure

```
sasampa/
├── app/
│   ├── Console/Commands/     # Scheduled and one-off commands
│   ├── Http/Controllers/     # Web controllers (Api/V1 for the mobile API)
│   ├── Http/Middleware/      # Approval, permissions, plan features
│   ├── Models/               # Eloquent models (company-scoped)
│   └── Services/             # Billing, payments, messaging
├── config/                   # billing.php, messaging.php, efdms.php ...
├── database/                 # Migrations, seeders
├── docs/api/                 # Mobile API reference
├── mobile_app/               # Flutter app
├── resources/views/          # Blade templates
├── routes/                   # web.php, api.php, console.php (scheduler)
└── tests/                    # Feature and unit tests
```

## Security

For security concerns, contact the development team. Never commit credentials; server details belong in your own private notes, not in this repository.

## License

Proprietary - All rights reserved.

---

<p align="center">
  <strong>Sasampa POS</strong><br>
  Empowering East African Businesses
</p>
