<?php namespace Jaybizzle\MigrationsOrganiser;

use Jaybizzle\MigrationsOrganiser\MigrationCreator;
use Illuminate\Database\MigrationServiceProvider as MSP;
use Jaybizzle\MigrationsOrganiser\Commands\MigrateOrganise;
use Jaybizzle\MigrationsOrganiser\Commands\MigrateDisorganise;

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

	private function registerMigrateOrganise()
	{
		$this->app['command.migrate.organise'] = $this->app->share(function($app)
		{
			return new MigrateOrganise($app['files'],$app['migrator']);
		});
	}
	
	private function registerMigrateDisorganise()
	{
		$this->app['command.migrate.disorganise'] = $this->app->share(function($app)
		{
			return new MigrateDisorganise($app['files'],$app['migrator']);
		});
	}
