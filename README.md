<p align="center">
  <img src="public/images/logo.png" alt="Sasampa POS" width="120">
</p>

<h1 align="center">Sasampa POS</h1>

<p align="center">
  <strong>Modern Point of Sale System for East African Businesses</strong>
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#installation">Installation</a> •
  <a href="#deployment">Deployment</a> •
  <a href="#documentation">Documentation</a>
</p>

---

## Overview

Sasampa POS is a comprehensive, cloud-ready point of sale system designed specifically for businesses in Tanzania and East Africa. Built with Laravel 11 and modern web technologies, it provides an intuitive interface for managing sales, inventory, and business operations.

## Features

### Core Features
- **Point of Sale** - Fast, intuitive checkout with receipt printing
- **Product Management** - Organize products with categories and SKUs
- **Inventory Tracking** - Real-time stock levels with low-stock alerts
- **Transaction History** - Complete sales records with void capability
- **Reporting & Analytics** - Sales reports, product performance, and trends

### Multi-Tenant Architecture
- **Company Isolation** - Each business has isolated data
- **User Roles** - Platform Admin, Company Owner, and Cashier roles
- **Approval Workflow** - Admin approval for new company registrations

### Multi-Branch Support
- **Multiple Locations** - Manage branches across different locations
- **Configurable Sharing** - Shared or independent product catalogs per branch
- **Branch Switching** - Users can switch between assigned branches
- **Branch-Level Reports** - Filter reports by branch

### Bilingual Documentation
- **In-App Help** - Comprehensive documentation system
- **English & Kiswahili** - Full translation support
- **Searchable** - Find answers quickly

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 11 (PHP 8.2+) |
| Frontend | Blade, Bootstrap 5, Vite |
| Database | SQLite / MySQL |
| Authentication | Laravel Breeze |
| Styling | Custom Apple-inspired UI |

## Requirements

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ & NPM
- SQLite or MySQL 8.0+

## Installation

### Local Development

```bash
# Clone the repository
git clone https://github.com/wibonela/sasampa.git
cd sasampa

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed demo data (optional)
php artisan db:seed

# Build assets
npm run build

# Start development server
php artisan serve
```

## Deployment

### Safe Deployment

Use the deployment script for safe production deployments:

```bash
sudo /var/www/sasampa/deploy.sh
```

This script automatically:
1. Creates database backup
2. Pulls latest code from GitHub
3. Updates dependencies
4. Runs migrations
5. Clears caches

### Rollback

If needed, restore from backup:

```bash
sudo /var/www/sasampa/rollback.sh
```

## Project Structure

```
sasampa/
├── app/
│   ├── Http/Controllers/     # Controllers
│   ├── Models/               # Eloquent models
│   └── Http/Middleware/      # Custom middleware
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── resources/
│   ├── views/                # Blade templates
│   ├── css/                  # Stylesheets
│   └── js/                   # JavaScript
├── routes/
│   └── web.php               # Web routes
└── public/                   # Public assets
```

## Environment Variables

| Variable | Description |
|----------|-------------|
| `APP_NAME` | Application name |
| `APP_ENV` | Environment (local/production) |
| `APP_KEY` | Application encryption key |
| `APP_URL` | Application URL |
| `DB_CONNECTION` | Database driver (sqlite/mysql) |

## Security

For security concerns, contact the development team.

## License

Proprietary - All rights reserved.

---

<p align="center">
  <strong>Sasampa POS</strong><br>
  Empowering East African Businesses
</p>
