<?php namespace Jaybizzle\MigrationsOrganiser;

use Jaybizzle\MigrationsOrganiser\MigrationCreator;
use Illuminate\Database\MigrationServiceProvider as MSP;
use Jaybizzle\MigrationsOrganiser\Commands\MigrateOrganise;

class MigrationsOrganiserServiceProvider extends MSP
{
	protected function registerCreator()
	{
		$this->app->bindShared('migration.creator', function ($app) {
			return new MigrationCreator($app['files']);
		});
	}

	protected function registerMigrator()
	{
		$this->app->singleton('migrator', function($app)
		{
			$repository = $app['migration.repository'];

			return new Migrator($repository, $app['db'], $app['files']);
		});
	}
	
	public function register()
	{
		$this->registerMigrateOrganise();
		$this->commands('migrateorganise');
	}

	private function registerMigrateOrganise()
	{
		$this->app['migrateorganise'] = $this->app->share(function($app)
		{
			return new MigrateOrganise($app['files']);
		});
	}
}
