<?php namespace Jaybizzle\MigrationsOrganiser\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\Migrator;

class MigrateOrganise extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'migrate:organise';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Move migrations into a yyyy/mm folder structure';
	
	
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
		parent::__construct();
		$this->migrator = $migrator;
		$this->files = $files;
	}
	
	/**
	 * Create date folder structure and move migrations into.
	 *
	 * @return void
	 */
	public function fire()
	{
		$basePath = $this->laravel['path.database'].'/migrations/';
		$migrations = $this->migrator->getMigrationFiles($basePath, false);
	
		if(count($migrations) == 0)
		{
			$this->line('No migrations to move');
			return;
		}
		
		foreach($migrations as $migration)
		{	
			$datePath = $this->migrator->getDateFolderStructure($migration);
						
			// Create folder if it does not already exist
			if(!$this->files->exists($basePath.$datePath)) 
			{
				$this->files->makeDirectory($basePath.$datePath, 0775, true);
			}
			
			// Move the migration into its new folder	
			$this->files->move($basePath.$migration.'.php', $basePath.$datePath.$migration.'.php');
		}
		
		$this->line('Migrations organised successfully');
	}
}
