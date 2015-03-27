<?php namespace Jaybizzle\MigrationsOrganiser\Commands;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\Migrator;
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
		$basePath = $this->getMigrationPath();
		$migrations = $this->migrator->getMigrationFiles($basePath);
	
		if (count($migrations) == 0)
		{
			$this->line('No migrations to move');
			return;
		}
		
		foreach ($migrations as $migration)
		{	
			$datePath = $this->migrator->getDateFolderStructure($migration);
			
			// Move the migration into base migration folder	
			$this->files->move($basePath.'/'.$datePath.$migration.'.php', $basePath.'/'.$migration.'.php');
		}
		
		$this->line('Migrations disorganised successfully');
		$this->line('');
		$this->line('Run clean up function?');
		$this->line('This will delete all subdirectories in the migrations directory');
		
		if (!$this->confirmToProceed('Would you like to run the clean up command?')) return;
		// clean up the folders
		$dirs = $this->files->directories($basePath);

		foreach ($dirs as $dir)
		{
			$this->files->deleteDirectory($dir);
		}
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
		);
	}
}
