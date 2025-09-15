# Repository Guidelines

## Project Structure & Module Organization
- `app/`: Domain logic (Models, Services, Filament), HTTP layer under `app/Http/`.
- `routes/`: HTTP and console routes (`web.php`, `console.php`).
- `resources/`: Blade views and front‑end assets; bundled with Vite.
- `database/`: Migrations/seeders. Tests default to in‑memory SQLite (see `phpunit.xml`).
- `tests/`: Pest tests in `tests/Feature` and `tests/Unit`.
- `config/`, `public/`, `bootstrap/`, `storage/`: Standard Laravel layout.

## Build, Test, and Development Commands
- Install: `composer install` and `npm install`
- Dev (concurrent): `composer run dev` (PHP server, queue, logs, Vite)
- Alternative dev: `php artisan serve` and `npm run dev`
- Test: `composer test` or `php artisan test` or `./vendor/bin/pest`
- Lint/format: `./vendor/bin/pint -v` (PSR‑12 formatter)
- Build assets: `npm run build`

## Coding Style & Naming Conventions
- PHP: PSR‑12 via Pint; 4‑space indent; classes `StudlyCase`, methods/properties `camelCase`.
- Blade: components/partials in `resources/views`, file names `kebab-case.blade.php`.
- Routes: prefer resourceful controllers; route names use dot notation (e.g., `invoices.show`).
- Folders: Models in `app/Models`, services in `app/Services`, observers in `app/Observers`.

## Testing Guidelines
- Framework: Pest + Laravel test runner. Place tests under `tests/Feature` or `tests/Unit`.
- Naming: `tests/Feature/InvoiceTest.php`, use Pest style `it('...')` blocks.
- Database: use `RefreshDatabase` or transactions; default to in‑memory SQLite from `phpunit.xml`.
- Aim to cover models, services, observers, and critical flows.

## Commit & Pull Request Guidelines
- Commits: `type(scope): summary` (e.g., `feat(invoice): add XML export`).
- PRs: clear description, linked issues, steps to test, and screenshots for UI. Keep scope focused and update docs (`DATOS.md`, `QPSE_SETUP.md`) when relevant.

## Security & Configuration Tips
- Copy `.env.example` → `.env`, run `php artisan key:generate`. Never commit secrets.
- Queues: use `php artisan queue:listen` in dev (started by `composer run dev`); configure a proper driver for production.
- Integration keys (e.g., Greenter) belong in `.env` with least privilege.
