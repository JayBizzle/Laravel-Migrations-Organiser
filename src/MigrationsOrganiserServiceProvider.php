<?php

namespace Jaybizzle\MigrationsOrganiser;

use Illuminate\Database\MigrationServiceProvider as MSP;
use Jaybizzle\MigrationsOrganiser\Commands\MigrateDisorganise;
use Jaybizzle\MigrationsOrganiser\Commands\MigrateOrganise;

class MigrationsOrganiserServiceProvider extends MSP
{
    public function register()
    {
        parent::register();
        $this->registerMigrateOrganise();
        $this->registerMigrateDisorganise();
        $this->commands('command.migrate.organise', 'command.migrate.disorganise');
    }

    protected function registerCreator()
    {
        $this->app->singleton('migration.creator', function ($app) {
            return new MigrationCreator($app['files'], $app->path('stubs'));
        });
    }

    protected function registerMigrator()
    {
        $this->app->singleton('migrator', function ($app) {
            $repository = $app['migration.repository'];

            return new Migrator($repository, $app['db'], $app['files']);
        });
    }

    protected function registerMigrateOrganise()
    {
        $this->app->singleton('command.migrate.organise', function ($app) {
            return new MigrateOrganise($app['files'], $app['migrator']);
        });
    }

    protected function registerMigrateDisorganise()
    {
        $this->app->singleton('command.migrate.disorganise', function ($app) {
            return new MigrateDisorganise($app['files'], $app['migrator']);
        });
    }
}
