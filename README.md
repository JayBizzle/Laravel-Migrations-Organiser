# Laravel 5 Migrations Organiser

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

Then just use the standard artisan commands as you normally would and the package will take care of the rest.

### Still to do
- Add options to migrate:organise command to allow other actions such as delete migration files once moved.
- Reverse migrate:organise command?
