<?php namespace Jaybizzle\MigrationsOrganiser\Commands;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Filesystem\Filesystem;
use Jaybizzle\MigrationsOrganiser\Migrator;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Database\Console\Migrations\BaseCommand;

class MigrateDisorganise extends BaseCommand {
	
	use ConfirmableTrait;

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
	 * @var \Jaybizzle\MigrationsOrganiser\Migrator
	 */
	protected $migrator;
	
	/**
	 * The filesystem instance.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $files;
	
	/**
	 * The basePath for the migrations.
	 */
	protected $basePath;
	
	/**
	 * Create a new migrator instance.
	 *
	 * @param  \Illuminate\Filesystem\Filesystem  $files
	 * @param  \Illuminate\Database\Migrations\Migrator  $migrator
	 */
	public function __construct(Filesystem $files, Migrator $migrator)
	{
		parent::__construct();
		$this->migrator = $migrator;
		$this->files    = $files;
	}
	
	/**
	 * Create date folder structure and move migrations into.
	 *
	 * @return void
	 */
	public function fire()
	{
		$basePath   = $this->getMigrationPath();
		$migrations = $this->migrator->getMigrationFiles($basePath);
		$count      = count($migrations);
		
		if ($count == 0)
		{
			$this->comment('No migrations to move');
			return;
		}
		
		foreach ($migrations as $migration)
		{	
			$datePath = $this->migrator->getDateFolderStructure($migration);
			// Move the migration into base migration folder	
			$this->files->move($basePath.'/'.$datePath.$migration.'.php', $basePath.'/'.$migration.'.php');
		}
		
		$this->info('Migrations disorganised successfully ('.$count.' migrations moved)');
		$this->cleanup();
	}
	
	/**
	* Decide whether or not to delete directories
	*
	* @return void
	*/
	public function cleanup()
	{
		if ($this->option('force'))
		{
			$this->deleteDirs();
		}
		elseif ($this->confirm('Delete all subdirectories in migrations folder? [yes|no]', false))
		{
			$this->deleteDirs();
		}
	}
	
	/**
	* Delete subdirectories in the migrations folder
	*
	* @return void
	*/
	public function deleteDirs()
	{
		$dirs = $this->files->directories($this->basePath);

		foreach ($dirs as $dir)
		{
			$this->files->deleteDirectory($dir);
		}
		
		$this->info('Subdirectories deleted');
	}
	
	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('force', null, InputOption::VALUE_NONE, 'Force the operation to delete migration folder subdirectories without prompt.'),

			array('bench', null, InputOption::VALUE_OPTIONAL, 'The name of the workbench to migrate.', null),

			array('path', null, InputOption::VALUE_OPTIONAL, 'The path to migration files.', null),

			array('package', null, InputOption::VALUE_OPTIONAL, 'The package to migrate.', null),
		);
	}
}
