# Laravel 5 Migrations Organiser
 [![Build Status](https://img.shields.io/travis/JayBizzle/Laravel-Migrations-Organiser.svg?style=flat-square)](https://travis-ci.org/JayBizzle/Laravel-Migrations-Organiser)

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
Running migrate:organise will move all of your migrations into the relevant `yyyy/mm` folder
Then just use the standard artisan commands as you normally would and the package will take care of the rest.

Uninstall
============

Running migrate:disorganise will move all migrations from the `yyyy/mm` folder structure into the base migrations folder.
