# Laravel Migrations Organiser

[![Total Downloads](https://img.shields.io/packagist/dt/JayBizzle/Laravel-Migrations-Organiser.svg?style=flat-square)](https://packagist.org/packages/jaybizzle/Laravel-Migrations-Organiser)
[![Tests](https://github.com/JayBizzle/Laravel-Migrations-Organiser/actions/workflows/tests.yml/badge.svg)](https://github.com/JayBizzle/Laravel-Migrations-Organiser/actions/workflows/tests.yml)

As projects grow, the number of migration files can quickly become unwieldy. This package automatically organises your migrations into `yyyy/mm` folders, making them easier to navigate.

```
database/migrations/2025/03/2025_03_26_000000_create_users_table.php
```

## Version Compatibility

| Laravel   | Package |
|-----------|---------|
| 5.3 - 6.x | v4.*   |
| 7.x       | v5.*   |
| 8 - 10    | v6.*   |
| 11 - 13   | v7.*   |

## Installation

```bash
composer require jaybizzle/laravel-migrations-organiser
```

The package uses Laravel's auto-discovery, so no manual service provider registration is needed.

## Usage

This package hooks into the default `artisan make:migration` command. Just use it as you normally would and the package takes care of the rest.

### Organise existing migrations

If you already have migrations in the base folder, run:

```bash
php artisan migrate:organise
```

This will move all existing migrations into the appropriate `yyyy/mm` folders.

### Flatten migrations

To move all migrations back to the base migrations folder:

```bash
php artisan migrate:flatten
```

Add `--force` to delete leftover subdirectories without confirmation.

> `migrate:disorganise` is still available as an alias for backward compatibility.
