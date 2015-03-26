<?php namespace Jaybizzle\MigrationsOrganiser\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

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
		public function __construct(Filesystem $files)
		{
			$this->files = $files;
        	parent::__construct();
		}
	/**
	 * Get all of the migration files in a given path.
	 *
	 * @param  string  $path
	 * @return array
	 */
	public function fire()
	{
		$basePath = $this->laravel['path.database'].'/migrations/';
		$files = $this->files->glob($basePath.'*_*.php');
		// Once we have the array of files in the directory we will just remove the
		// extension and take the basename of the file which is all we need when
		// finding the migrations that haven't been run against the databases.
		if ($files === false) return array();
		$files = array_map(function($file)
		{
			return str_replace('.php', '', basename($file));
		}, $files);
		// Once we have all of the formatted file names we will sort them and since
		// they all start with a timestamp this should give us the migrations in
		// the order they were actually created by the application developers.
		sort($files);
		
		foreach($files as $file){
			$folders = explode('_', $file);
			$datePath = $folders[0].'/'.$folders[1].'/';
			
			if(!$this->files->exists($basePath.$datePath)) {
				$this->files->makeDirectory($basePath.$datePath, 0775, true);
			}	
			$this->files->copy($basePath.$file.'.php', $basePath.$datePath.$file.'.php');
			
		}
		
		$this->line('Migrations Organised Successfully');
	}
}
