# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application using PHP 8.2+ with Vite for frontend asset compilation and Tailwind CSS v4 for styling. The project follows standard Laravel MVC architecture with additional frontend tooling.

## Development Commands

### PHP/Laravel Commands
- `composer dev` - Start the complete development environment (server, queue, logs, and Vite)
- `php artisan serve` - Start Laravel development server only
- `php artisan tinker` - Interactive PHP shell
- `php artisan migrate` - Run database migrations
- `php artisan queue:listen --tries=1` - Start queue worker
- `php artisan pail --timeout=0` - Live log monitoring

### Frontend Commands
- `npm run dev` - Start Vite development server with hot reload
- `npm run build` - Build assets for production

### Testing
- `composer test` - Run the full test suite (clears config and runs artisan test)
- `php artisan test` - Run tests directly
- Uses Pest testing framework with PHPUnit XML configuration

### Code Quality
- `vendor/bin/pint` - PHP code style fixer (Laravel Pint)

## Project Structure

### Core Directories
- `app/` - Application logic (Models, Controllers, Providers)
  - `app/Models/` - Eloquent models
  - `app/Http/Controllers/` - HTTP controllers
  - `app/Providers/` - Service providers
- `routes/` - Route definitions
  - `routes/web.php` - Web routes
  - `routes/console.php` - Artisan commands
- `resources/` - Frontend assets and views
  - `resources/views/` - Blade templates
  - `resources/css/` - CSS files (compiled by Vite)
  - `resources/js/` - JavaScript files (compiled by Vite)
- `database/` - Database-related files
  - `database/migrations/` - Database schema migrations
  - `database/seeders/` - Data seeders
  - `database/factories/` - Model factories for testing
- `tests/` - Test files using Pest framework
  - `tests/Feature/` - Feature tests
  - `tests/Unit/` - Unit tests
- `config/` - Configuration files
- `storage/` - Application storage (logs, cache, sessions)
- `public/` - Web server document root

### Key Configuration
- Uses SQLite database (`:memory:` for testing)
- Environment configuration in `.env` (copy from `.env.example`)
- Vite configuration in `vite.config.js` with Laravel plugin and Tailwind CSS v4
- Composer autoloading for `App\` namespace

## Development Workflow

The project uses a comprehensive development setup via `composer dev` which concurrently runs:
1. Laravel development server (`php artisan serve`)
2. Queue worker (`php artisan queue:listen --tries=1`)
3. Log monitoring (`php artisan pail --timeout=0`)
4. Vite development server (`npm run dev`)

For testing, always run `composer test` which clears the config cache before running tests.

## Framework Versions
- Laravel: ^12.0
- PHP: ^8.2
- Vite: ^7.0.4
- Tailwind CSS: ^4.0.0
- Pest (testing): ^4.0