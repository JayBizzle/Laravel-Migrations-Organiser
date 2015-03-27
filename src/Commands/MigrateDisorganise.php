<?php namespace Jaybizzle\MigrationsOrganiser\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\Migrator;

class MigrateDisorganise extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'migrate:disorganise';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Move migrations from a yyyy/mm folder structure back to the base migrations folder';
	
	
	/**
	 * The migrator instance.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $migrator;
	
	/**
	 * The filesystem instance.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $files;
	
	/**
	 * Create a new migrator instance.
	 *
	 * @param  \Illuminate\Filesystem\Filesystem  $files
	 * @return void
	 */
	public function __construct(Filesystem $files, Migrator $migrator)
	{
		$this->migrator = $migrator;
		$this->files = $files;
		parent::__construct();
	}
	
	/**
	 * Create date folder structure and move migrations into.
	 *
	 * @return void
	 */
	public function fire()
	{
		$basePath = $this->laravel['path.database'].'/migrations/';
		$migrations = $this->migrator->getMigrationFiles($basePath);
	
		if(count($migrations) == 0)
		{
			$this->line('No migrations to move');
			return;
		}
		
		foreach($migrations as $migration)
		{	
			$datePath = $this->migrator->getDateFolderStructure($migration);
			
			// Move the migration into base migration folder	
			$this->files->move($basePath.$datePath.$migration.'.php', $basePath.$migration.'.php');
			
		}
		
		$this->line('Migrations Disorganised Successfully');
	}
}
