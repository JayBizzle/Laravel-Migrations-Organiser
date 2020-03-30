# Laravel Migrations Organiser
 [![Build Status](https://img.shields.io/travis/JayBizzle/Laravel-Migrations-Organiser/master.svg?style=flat-square)](https://travis-ci.org/JayBizzle/Laravel-Migrations-Organiser)
 [![Total Downloads](https://img.shields.io/packagist/dt/JayBizzle/Laravel-Migrations-Organiser.svg?style=flat-square)](https://packagist.org/packages/jaybizzle/Laravel-Migrations-Organiser)
 [![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/JayBizzle/Laravel-Migrations-Organiser.svg?style=flat-square)](https://scrutinizer-ci.com/g/JayBizzle/Laravel-Migrations-Organiser/?branch=master)
 <a href="https://styleci.io/repos/32828907"><img src="https://styleci.io/repos/32828907/shield" /></a>

The number of migrations in any sized project can quickly become huge, and although they are ordered, having one big list can sometimes be inconvenient and slow to navigate.

This package will put your migrations in `yyyy/mm` folders e.g.

`./database/migrations/2015/03/2015_03_25_210946_create_users_table.php`

Versions
========
 - Laravel 5.3-6.* use `v4.*`
 - Laravel 7.* use `v5.*`

Installation
============

```
composer require jaybizzle/laravel-migrations-organiser
```

Add the following to the `providers` array in your `config/app.php` file..

```PHP
    Jaybizzle\MigrationsOrganiser\MigrationsOrganiserServiceProvider::class,
```

> Laravel ^5.5 uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider

Usage
============
This package hooks into the default `artisan make:migration` command. Just use that as you normally would and the package takes care of the rest.

##### What if I have already created migrations
No problem, just run `artisan migrate:organise` and your migrations will be moved into the relevant `yyyy/mm` folders.

##### I want my migrations back to how they were
Again, no problem. Running `artisan migrate:disorganise` will move all migrations from the `yyyy/mm` folder structure into the base migrations folder. Add the `--force` option to delete left over folders without confirmation.

[![Analytics](https://ga-beacon.appspot.com/UA-72430465-1/Laravel-Migrations-Organiser/readme?pixel)](https://github.com/JayBizzle/Laravel-Migrations-Organiser)
