# Flatpickr Usage Examples for Filament

## Installation Complete ✅

The Flatpickr package has been successfully installed in your project:

- **Package**: `coolsam/flatpickr` v5.0.0
- **Config file**: `config/flatpickr.php`
- **Assets**: Published to `public/vendor/flatpickr/`
- **Compatible with**: Filament v4.x

## Basic Usage

### 1. Import the Component

```php
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
```

### 2. Replace DatePicker with Flatpickr

**Before (Standard Filament DatePicker):**
```php
DatePicker::make('issue_date')
    ->required()
    ->label(__('Fecha de Emisión')),
```

**After (Flatpickr DatePicker):**
```php
Flatpickr::make('issue_date')
    ->required()
    ->label(__('Fecha de Emisión'))
    ->dateFormat('Y-m-d')
    ->displayFormat('d/m/Y'),
```

## Available Flatpickr Types

### 1. Date Picker
```php
Flatpickr::make('issue_date')
    ->label(__('Fecha de Emisión'))
    ->dateFormat('Y-m-d')
    ->displayFormat('d/m/Y')
    ->required(),
```

### 2. Date-Time Picker
```php
Flatpickr::make('created_at')
    ->label(__('Fecha y Hora'))
    ->enableTime()
    ->dateFormat('Y-m-d H:i')
    ->displayFormat('d/m/Y H:i')
    ->time24hr(true),
```

### 3. Time-Only Picker
```php
Flatpickr::make('start_time')
    ->label(__('Hora de Inicio'))
    ->enableTime()
    ->noCalendar()
    ->dateFormat('H:i')
    ->displayFormat('H:i')
    ->time24hr(true),
```

### 4. Date Range Picker
```php
Flatpickr::make('date_range')
    ->label(__('Rango de Fechas'))
    ->mode('range')
    ->dateFormat('Y-m-d')
    ->displayFormat('d/m/Y'),
```

### 5. Multiple Date Picker
```php
Flatpickr::make('event_dates')
    ->label(__('Fechas de Eventos'))
    ->mode('multiple')
    ->dateFormat('Y-m-d')
    ->displayFormat('d/m/Y'),
```

### 6. Week Picker
```php
Flatpickr::make('week_selection')
    ->label(__('Seleccionar Semana'))
    ->weekNumbers(true)
    ->dateFormat('Y-m-d'),
```

### 7. Month Picker
```php
Flatpickr::make('month_year')
    ->label(__('Mes y Año'))
    ->dateFormat('Y-m')
    ->displayFormat('m/Y')
    ->plugins(['monthSelect']),
```

## Advanced Configuration Options

### Localization (Spanish)
```php
Flatpickr::make('fecha')
    ->label(__('Fecha'))
    ->locale('es')
    ->dateFormat('Y-m-d')
    ->displayFormat('d/m/Y'),
```

### Min/Max Dates
```php
Flatpickr::make('due_date')
    ->label(__('Fecha de Vencimiento'))
    ->minDate('today')
    ->maxDate('+1 year')
    ->dateFormat('Y-m-d'),
```

### Disabled Dates
```php
Flatpickr::make('appointment_date')
    ->label(__('Fecha de Cita'))
    ->disable(['2024-12-25', '2024-01-01']) // Disable specific dates
    ->dateFormat('Y-m-d'),
```

### Custom Themes
```php
Flatpickr::make('styled_date')
    ->label(__('Fecha con Tema'))
    ->theme('dark') // or 'material_blue', 'material_green', etc.
    ->dateFormat('Y-m-d'),
```

## Practical Examples for Your Invoice System

### 1. Issue Date with Spanish Format
```php
Flatpickr::make('issue_date')
    ->required()
    ->label(__('Fecha de Emisión'))
    ->locale('es')
    ->dateFormat('Y-m-d')
    ->displayFormat('d/m/Y')
    ->maxDate('today'), // Can't issue invoices in the future
```

### 2. Due Date with Minimum Date Validation
```php
Flatpickr::make('due_date')
    ->label(__('Fecha de Vencimiento'))
    ->visible(fn (callable $get) => $get('payment_condition') === 'credit')
    ->required(fn (callable $get) => $get('payment_condition') === 'credit')
    ->locale('es')
    ->dateFormat('Y-m-d')
    ->displayFormat('d/m/Y')
    ->minDate(fn (callable $get) => $get('issue_date') ?: 'today'),
```

### 3. Payment Date-Time
```php
Flatpickr::make('paid_at')
    ->label(__('Fecha y Hora del Pago'))
    ->enableTime()
    ->time24hr(true)
    ->locale('es')
    ->dateFormat('Y-m-d H:i:S')
    ->displayFormat('d/m/Y H:i')
    ->maxDate('today')
    ->required(),
```

### 4. Date Range Filter for Reports
```php
Flatpickr::make('date_range')
    ->label(__('Rango de Fechas'))
    ->mode('range')
    ->locale('es')
    ->dateFormat('Y-m-d')
    ->displayFormat('d/m/Y')
    ->maxDate('today'),
```

## Configuration Options

The package config file (`config/flatpickr.php`) allows you to set global defaults:

```php
<?php

return [
    'theme' => \Coolsam\Flatpickr\Enums\FlatpickrTheme::DEFAULT, // Recommended for Filament compatibility
];
```

Available themes:
- `DEFAULT` (recommended for Filament)
- `DARK`
- `MATERIAL_BLUE`
- `MATERIAL_GREEN`
- `MATERIAL_ORANGE`
- `MATERIAL_RED`
- `AIRBNB`
- `CONFETTI`

## Integration with Your Current Forms

To integrate Flatpickr into your existing `InvoiceResource`, you would:

1. **Add the import** at the top of your resource file:
```php
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
```

2. **Replace DatePicker components** with Flatpickr:
```php
// Replace this:
DatePicker::make('issue_date')
    ->required()
    ->label(__('Fecha de Emisión')),

// With this:
Flatpickr::make('issue_date')
    ->required()
    ->label(__('Fecha de Emisión'))
    ->locale('es')
    ->dateFormat('Y-m-d')
    ->displayFormat('d/m/Y')
    ->maxDate('today'),
```

## Benefits of Using Flatpickr

1. **Better UX**: More intuitive date selection interface
2. **Localization**: Built-in Spanish support
3. **Flexibility**: Multiple picker types (date, time, range, etc.)
4. **Themes**: Dark mode and custom styling support
5. **Mobile Friendly**: Better touch interface on mobile devices
6. **Accessibility**: Better keyboard navigation and screen reader support

## Next Steps

1. **Test the installation** by replacing one DatePicker in your forms
2. **Customize the theme** in `config/flatpickr.php` if needed
3. **Add localization** by setting `locale('es')` on your components
4. **Explore advanced features** like date ranges and time pickers

The package is now ready to use in your Filament forms! 🎉