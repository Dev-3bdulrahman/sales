# 💰 Sales — Laravel ERP Module

[![Latest Version](https://img.shields.io/packagist/v/dev-3bdulrahman/sales.svg?style=flat-square)](https://packagist.org/packages/dev-3bdulrahman/sales)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue?style=flat-square)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11%2B%20%7C%2012%2B-red?style=flat-square)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)](LICENSE)

A complete **Sales** module for Laravel ERP systems. Handle the full sales cycle — quotations, sales orders, invoices, payments, returns, discounts, and sales commissions — with full API and Livewire admin interface.

---

## Features

- Quotation Management
- Sales Order Processing
- Invoice Generation & Management
- Payment Tracking
- Sales Returns
- Discount Management
- Sales Commission Tracking
- REST API endpoints
- Arabic & English translations

## Requirements

| Dependency | Version |
|---|---|
| PHP | ^8.2 \| ^8.3 |
| Laravel | ^11.0 \| ^12.0 |

## Installation

```bash
composer require dev-3bdulrahman/sales
```

Publish and run migrations:

```bash
php artisan vendor:publish --provider="Dev3bdulrahman\Sales\SalesServiceProvider"
php artisan migrate
```

## Service Provider

Auto-discovered via Laravel package discovery. Manual registration in `bootstrap/providers.php`:

```php
Dev3bdulrahman\Sales\SalesServiceProvider::class,
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history.

## License

MIT License © [Abdulrahman](https://3bdulrahman.com)
