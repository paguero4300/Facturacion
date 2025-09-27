# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

QPOS is a complete electronic invoicing Point of Sale (POS) system for SUNAT (Peru), built with Laravel 12 and Filament 4. The system provides comprehensive business management including electronic invoicing, inventory control, client management, warehouse management, and SUNAT integration.

## Development Environment

### Essential Commands

```bash
# Start full development environment (server + queue + logs + vite)
composer run dev

# Individual services
php artisan serve                    # Laravel development server
php artisan queue:listen --tries=1   # Queue worker
php artisan pail --timeout=0        # Real-time logs
npm run dev                          # Frontend build with hot reload

# Database operations
php artisan migrate                  # Run migrations
php artisan migrate:fresh --seed    # Fresh migration with seeders
php artisan db:seed                  # Run seeders only

# Testing
composer run test                    # Run Pest tests with config clear
php artisan test                     # Run tests directly
php artisan test --filter=TestName  # Run specific test

# Code quality
vendor/bin/pint                     # Laravel Pint code formatting
vendor/bin/pint --dirty             # Format only modified files

# Frontend build
npm run build                       # Production build
vite build                          # Direct Vite build

# Cache management
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear          # Clear all caches
```

## Technology Stack

### Backend Core
- **Laravel 12** with PHP 8.2+
- **Filament 4** admin panel with Livewire 3
- **MySQL** database with migrations
- **Pest PHP 4** for testing
- **Laravel Pint** for code formatting

### Electronic Invoicing
- **Greenter** library for SUNAT electronic invoicing
- **QPSE** integration as Electronic Service Provider
- **SUNAT API** direct integration support
- **PDF generation** with Laravel PDF (Spatie) and wkhtmltopdf

### Frontend
- **TailwindCSS 4** with Vite 7 integration
- **Alpine.js** for interactivity (via Filament)
- **Iconoir** icon library via Filament plugin
- **Flatpickr** for date/time selection

### Key Dependencies
- **Puppeteer** for PDF generation fallback
- **Concurrently** for running multiple dev processes
- **Filament Excel** for import/export functionality (pxlrbt/filament-excel)
- **Filament Log Viewer** for application logs (achyutn/filament-log-viewer)
- **Filament Excel Import** for data imports (eightynine/filament-excel-import)
- **Filament Media Action** for file handling (hugomyb/filament-media-action)
- **Brisk UI** components (filafly/brisk)

## Project Architecture

### Core Business Models
- `Invoice` - Main invoicing with SUNAT integration
- `InvoiceDetail` - Invoice line items
- `Client` - Customer management with document validation
- `Product` - Inventory items with categories and brands
- `Company` - Multi-company support with SUNAT configuration
- `DocumentSeries` - SUNAT document series management
- `Stock` & `InventoryMovement` - Inventory tracking with warehouse support
- `Warehouse` - Multi-warehouse inventory management
- `ExchangeRate` - Currency exchange rate management
- `Brand` & `Category` - Product categorization
- `PaymentInstallment` - Installment payment tracking

### Filament Resources Structure
Located in `app/Filament/Resources/`:
- Complete CRUD operations for all business entities
- Custom form components and table layouts
- Advanced filtering and bulk actions
- Export/import functionality via Excel
- Inventory reporting with specialized resources
- Warehouse management with stock control

### Key Configuration Files
- `config/greenter.php` - SUNAT electronic invoicing setup
- `config/qpse.php` - Electronic Service Provider configuration
- `config/laravel-pdf.php` - PDF generation settings
- `config/invoice-pdf.php` - Invoice PDF customization

### Service Integration
- **SUNAT Integration**: Direct API and PSE provider support
- **Electronic Invoicing**: XML generation and SUNAT submission
- **PDF Generation**: Multiple engines (wkhtmltopdf, Puppeteer)
- **Exchange Rates**: Automated currency rate updates

## Development Patterns

### Laravel Conventions
- Use **Eloquent relationships** with proper type hints
- Follow **Laravel naming conventions** for files and classes
- Implement **Form Requests** for validation instead of inline validation
- Use **Policies** for authorization logic
- Leverage **Service classes** for complex business logic

### Filament Best Practices
- Extend `Resource` classes for admin functionality
- Use **Filament forms** and tables for consistent UI
- Implement **custom form components** when needed
- Utilize **Filament actions** for bulk operations

### Testing with Pest
- Feature tests for Filament resources and forms
- Unit tests for service classes and models
- Database testing with factories and in-memory SQLite
- Test configuration located in `phpunit.xml`

### Code Quality
- **Laravel Pint** enforces PSR-12 coding standards
- Use type hints and return types
- Follow SOLID principles for service classes
- Implement proper error handling and logging

## SUNAT Electronic Invoicing

### Configuration Requirements
1. **Company Certificate**: Place SSL certificate in `public/certs/certificate.pem`
2. **SUNAT Credentials**: Configure SOL user credentials in environment
3. **Environment Mode**: Set `GREENTER_MODE` to 'beta' or 'prod'
4. **PSE Integration**: Configure QPSE credentials for indirect SUNAT connection

### Invoice Workflow
1. Create invoice in Filament admin panel
2. Generate electronic document (XML)
3. Submit to SUNAT (direct or via PSE)
4. Generate PDF for printing/email
5. Handle SUNAT response and update status

### Document Types Supported
- **01**: Electronic Invoice (Factura)
- **07**: Credit Note (Nota de Crédito)
- **08**: Debit Note (Nota de Débito)
- **09**: Dispatch Guide (Guía de Remisión)

## Environment Setup

### Required Environment Variables
```env
# Database
DB_CONNECTION=mysql
DB_DATABASE=facturacion
DB_USERNAME=root
DB_PASSWORD=

# SUNAT Configuration
GREENTER_MODE=beta
GREENTER_COMPANY_RUC=20605878840
GREENTER_SOL_USER=MODDATOS
GREENTER_SOL_PASS=MODDATOS

# QPSE Integration
QPSE_MODE=demo
QPSE_URL=https://demo-cpe.qpse.pe
QPSE_TOKEN=your_token
QPSE_USERNAME=your_username
QPSE_PASSWORD=your_password

# PDF Generation
GREENTER_PDF_BIN_PATH="C:/Program Files/wkhtmltopdf/bin/wkhtmltopdf.exe"
```

### Development Dependencies
- **wkhtmltopdf** for PDF generation (Windows path configured)
- **Node.js** for frontend asset compilation
- **Composer** for PHP dependency management

## Troubleshooting

### Common Issues
1. **PDF Generation Fails**: Check wkhtmltopdf installation and path in config
2. **SUNAT Connection Issues**: Verify certificates and credentials
3. **Filament Not Loading**: Run `php artisan filament:upgrade` and clear caches
4. **Queue Jobs Failing**: Check database queue configuration and worker status

### Debug Commands
```bash
# Check application status
php artisan about

# Monitor queue jobs
php artisan queue:monitor

# View application logs
php artisan pail

# Check failed jobs
php artisan queue:failed

# Test SUNAT connection
php artisan qpse:test-connection
```

## File Organization

### Important Directories
- `app/Models/` - Eloquent models with relationships
- `app/Filament/Resources/` - Admin panel resources
- `app/Services/` - Business logic services
- `app/Helpers/` - Utility helper classes
- `app/Observers/` - Model observers for data lifecycle
- `app/Policies/` - Authorization policies
- `config/` - Application and integration configurations
- `database/migrations/` - Database schema evolution
- `tests/` - Pest PHP test suites (Feature and Unit)
- `public/certs/` - SUNAT SSL certificates
- `storage/app/qpse/` - Electronic document storage
- `docs/` - Comprehensive project documentation

### Key Files
- `composer.json` - PHP dependencies and scripts
- `package.json` - Frontend dependencies and build scripts
- `phpunit.xml` - Testing configuration
- `vite.config.js` - Frontend build configuration

## Integration Points

### External APIs
- **SUNAT API** for electronic document submission
- **QPSE API** as Electronic Service Provider
- **Exchange Rate APIs** for currency conversion
- **Document validation APIs** for client data verification

### Internal Services
- **Invoice PDF generation** with customizable templates
- **Document series management** for SUNAT compliance
- **Inventory tracking** with real-time stock updates
- **Multi-company management** with separate configurations
- **Warehouse-based inventory** with movement tracking
- **Advanced reporting system** with dedicated resources

## Additional Project Information

### Available Filament Resources
The system includes the following comprehensive admin resources:
- `BrandResource` - Product brand management
- `CategoryResource` - Product categorization
- `ClientResource` - Customer management with SUNAT validation
- `CompanyResource` - Multi-company configuration
- `DocumentSeriesResource` - SUNAT document series setup
- `InvoiceResource` - Complete invoicing system
- `ProductResource` - Product catalog with inventory
- `StockResource` - Inventory stock management
- `WarehouseResource` - Multi-warehouse support
- `InventoryMovementResource` - Stock movement tracking
- `ReporteInventarioResource` - Inventory reporting
- `PaymentInstallmentResource` - Payment plan management

### Development Tools Available
- **Comprehensive test suite** using Pest PHP 4
- **Laravel Pail** for real-time log monitoring
- **Filament Log Viewer** for application log analysis
- **Database schema export** available in `database-schema.sql`
- **Diagnostic scripts** for Browsershot/Puppeteer troubleshooting

### Documentation Structure
The project includes extensive documentation in the `docs/` directory:
- Complete setup guides for QPSE integration
- Troubleshooting guides for common issues
- API integration documentation
- Invoice PDF generation guides
- Factiliza API integration docs

### Performance Considerations
- Uses **Vite 7** for fast frontend builds with hot reload
- **Concurrently** runs multiple dev services in parallel
- **In-memory SQLite** for fast testing
- **Laravel Pint** for consistent code formatting
- **Queue system** support for background jobs