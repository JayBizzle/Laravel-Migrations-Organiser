<?php

namespace Jaybizzle\MigrationsOrganiser;

use Illuminate\Database\MigrationServiceProvider as MSP;
use Jaybizzle\MigrationsOrganiser\Commands\MigrateFlatten;
use Jaybizzle\MigrationsOrganiser\Commands\MigrateOrganise;

class MigrationsOrganiserServiceProvider extends MSP
{
    public function register()
    {
        parent::register();
        $this->registerMigrateOrganise();
        $this->registerMigrateFlatten();
        $this->commands('command.migrate.organise', 'command.migrate.flatten');
    }

    protected function registerCreator()
    {
        $this->app->singleton('migration.creator', function ($app) {
            return new MigrationCreator($app['files'], $app->basePath('stubs'));
        });
    }

    protected function registerMigrator()
    {
        $this->app->singleton('migrator', function ($app) {
            $repository = $app['migration.repository'];

            return new Migrator($repository, $app['db'], $app['files'], $app['events']);
        });
    }

    protected function registerMigrateOrganise()
    {
        $this->app->singleton('command.migrate.organise', function ($app) {
            return new MigrateOrganise($app['files'], $app['migrator']);
        });
    }

    protected function registerMigrateFlatten()
    {
        $this->app->singleton('command.migrate.flatten', function ($app) {
            return new MigrateFlatten($app['files'], $app['migrator']);
        });
    }
}
