# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SentrySMP is a Minecraft SMP shopping website built with Laravel 12. It allows players to purchase in-game items (keys, ranks, bundles, gems, battle passes, vouchers) and handles delivery via a game server HTTP API. An Admin panel is embedded in the same Laravel app. A separate ASP.NET microservice (`SentrySMP.Images`) handles image storage and serving.

## Commands

```bash
# Run from sentrysmp-laravel/
cd sentrysmp-laravel

# Install PHP dependencies
composer install

# Copy and configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start dev server
php artisan serve

# Run the Images microservice (from repo root)
dotnet run --project SentrySMP.Images
```

## Architecture

### Repository Structure

| Path | Type | Role |
|---|---|---|
| `sentrysmp-laravel/` | Laravel 12 App | Main shop + Admin panel + REST API |
| `SentrySMP.Images/` | ASP.NET Web API | Standalone microservice for image storage and serving |

### Laravel App Layout (`sentrysmp-laravel/`)

```
app/
  Http/Controllers/
    Admin/          # Admin panel controllers (CRUD for all product types)
    Api/            # REST API controllers (commands, status)
    Shop/           # Shop-facing controllers
    HomeController  # Public pages
    PageController  # Static content pages
    PaymentController
    ShopController
  Models/           # Eloquent models for all entities
  Services/
    CommandQueueService
    PayPalService
    StripeService
    VoucherService
database/migrations/  # All schema migrations
resources/views/
  admin/            # Admin panel Blade templates
  layouts/
  pages/            # Static/info pages
  shop/             # Shop Blade templates
routes/
  web.php           # Web routes (shop + admin)
  api.php           # API routes (game server callbacks)
```

### Key Design Decisions

**Single Laravel app for shop + admin.** The admin panel lives under an `/admin` route prefix with its own controllers in `app/Http/Controllers/Admin/`. No separate process.

**Command delivery** to the Minecraft game server uses an HTTP API via `CommandQueueService`. Commands are queued in the `command_queue` table and delivered asynchronously.

**Payment processing** via PayPal (`PayPalService`) and Stripe (`StripeService`). Webhooks are handled in `PaymentController`.

**Vouchers** are managed through `VoucherService` and tracked in `vouchers` + `voucher_usages` tables.

**Images** are served by the separate `SentrySMP.Images` ASP.NET microservice. Configure its base URL in `.env` as `IMAGES_BASE_URL`.

### Data Layer

- Database: MySQL
- Migrations live in `sentrysmp-laravel/database/migrations/`
- Run migrations: `php artisan migrate`

### Configuration

Copy `sentrysmp-laravel/.env.example` to `sentrysmp-laravel/.env` and fill in real values. Key variables:

- `DB_*` — MySQL connection
- `APP_KEY` — Laravel app key (generate with `php artisan key:generate`)
- `PAYPAL_*` — PayPal credentials
- `STRIPE_*` — Stripe credentials
- `IMAGES_BASE_URL` — Base URL for the Images microservice
- `GAME_SERVER_URL` + `GAME_SERVER_API_KEY` — Game server delivery API

### Adding a New Shop Product Type

1. Create a migration in `database/migrations/`
2. Add an Eloquent model in `app/Models/`
3. Add an Admin controller in `app/Http/Controllers/Admin/`
4. Add a Shop controller in `app/Http/Controllers/Shop/` if needed
5. Add routes in `routes/web.php`
6. Add Blade views in `resources/views/admin/` and `resources/views/shop/`

### Images Microservice

The `SentrySMP.Images` ASP.NET project is still used for image storage. Deploy it separately via the Cake script:

```bash
# From build/ directory
dotnet cake build-ftp-images.cake
```
