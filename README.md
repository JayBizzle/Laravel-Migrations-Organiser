# Laravel 5 Migrations Organiser
 [![Build Status](https://img.shields.io/travis/JayBizzle/Laravel-Migrations-Organiser.svg?style=flat-square)](https://travis-ci.org/JayBizzle/Laravel-Migrations-Organiser)
 [![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/JayBizzle/Laravel-Migrations-Organiser.svg?style=flat-square)](https://scrutinizer-ci.com/g/JayBizzle/Laravel-Migrations-Organiser/?branch=master) [![Laravel](https://img.shields.io/badge/laravel-5.0.*-ff69b4.svg?style=flat-square)](https://laravel.com)

The number of migrations in any sized project can quickly become huge, and although they are ordered, having one big list can sometimes be annoying to navigate.

This package will put your migrations in `yyyy/mm` folders e.g.

`./database/migrations/2015/03/2015_03_25_210946_create_users_table.php`

Installation
============

~~Run `composer require jaybizzle/laravel-migrations-organiser 1.*` or add `"jaybizzle/laravel-migrations-organiser": "1.*"` to your `composer.json` file~~

Add the following to the `providers` array in your `config/app.php` file..

```PHP
    'Jaybizzle\MigrationsOrganiser\MigrationsOrganiserServiceProvider',
```

Usage
============
This package hooks into the default `artisan make:migration` command. Just use that as you normally would and the package takes care of the rest.

##### What if I have already created migrations
No problem, just run `artisan migrate:organise` and your migrations will be moved into the relevant `yyyy/mm` folders.

##### I want my migrations back to how they were
Again, No problem. Running `artisan migrate:disorganise` will move all migrations from the `yyyy/mm` folder structure into the base migrations folder.
