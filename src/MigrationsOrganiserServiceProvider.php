<?php namespace Jaybizzle\MigrationsOrganiser;

use Jaybizzle\MigrationsOrganiser\MigrationsOrganiser;
use Illuminate\Database\MigrationServiceProvider as MSP;

class MigrationsOrganiserServiceProvider extends MSP
{
    protected function registerCreator()
    {
        $this->app->bindShared('migration.creator', function ($app) {
            return new MigrationsOrganiser($app['files']);
        });
    }
}