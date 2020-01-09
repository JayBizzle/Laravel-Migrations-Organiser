<?php

namespace Jaybizzle\MigrationsOrganiser\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Filesystem\Filesystem;
use Jaybizzle\MigrationsOrganiser\Migrator;
use Symfony\Component\Console\Input\InputOption;

class MigrateDisorganise extends BaseCommand
{
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
     * @param \Illuminate\Filesystem\Filesystem        $files
     * @param \Illuminate\Database\Migrations\Migrator $migrator
     */
    public function __construct(Filesystem $files, Migrator $migrator)
    {
        parent::__construct();
        $this->migrator = $migrator;
        $this->files = $files;
    }

    /**
     * Fire the command. (Compatibility for < 5.5).
     */
    public function fire()
    {
        $this->handle();
    }

    /**
     * Create date folder structure and move migrations into.
     *
     * @return void
     */
    public function handle()
    {
        $this->basePath = $this->getMigrationPath();
        $migrations = $this->migrator->getMigrationFiles($this->basePath);
        $count = count($migrations);

        if ($count == 0) {
            $this->comment('No migrations to move');

            return;
        }

        foreach ($migrations as $migration_name => $migration_path) {
            $datePath = $this->migrator->getDateFolderStructure($migration_name);
            // Move the migration into base migration folder
            $this->files->move($this->basePath.'/'.$datePath.$migration_name.'.php', $this->basePath.'/'.$migration_name.'.php');
        }

        $this->info('Migrations disorganised successfully ('.$count.' migrations moved)');
        $this->cleanup();
    }

    /**
     * Decide whether or not to delete directories.
     *
     * @return void
     */
    public function cleanup()
    {
        if ($this->option('force')) {
            $this->deleteDirs();
        } elseif ($this->confirm('Delete all subdirectories in migrations folder?', true)) {
            $this->deleteDirs();
        }
    }

    /**
     * Delete subdirectories in the migrations folder.
     *
     * @return void
     */
    public function deleteDirs()
    {
        $dirs = $this->files->directories($this->basePath);

        foreach ($dirs as $dir) {
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
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to delete migration folder subdirectories without prompt.'],
        ];
    }
}
