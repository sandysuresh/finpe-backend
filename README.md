# FinPay Gateway — Laravel + Livewire Admin

This is the initial Phase-1 source project for the FinPay multi-vendor fintech platform. Dependencies are intentionally not bundled; run Composer and npm install on your development machine.

## Included

- Laravel 12 project configuration
- Livewire 3 dashboard
- Tailwind CSS 4 + Vite
- Dedicated Admin guard
- Admin login/logout
- Professional top horizontal navigation
- Dashboard KPI cards
- Transaction overview data
- Transaction status data
- Recent transactions
- Top vendors
- Seeded demo data

## Demo Login

Email: `admin@finpay.test`
Password: `password`

Change the demo password before using this in any environment other than local development.

## Setup

Requirements:

- PHP 8.2+
- Composer
- Node.js 20+
- MySQL 8+

Commands:

```bash
composer install
cp .env.example .env
php artisan key:generate

# Configure DB_* in .env

php artisan migrate --seed

npm install
npm run build

php artisan serve
```

Open:

`/admin/login`

For development:

```bash
npm run dev
```

## Important

This is the Phase-1 UI/auth foundation. Bank integrations, vendor API authentication, HMAC signing, wallet ledger, transaction locking, idempotency, reconciliation, settlement and production security controls are intentionally not enabled yet. Those should be implemented as separate phases and security-reviewed before production.
# finpe-backend
