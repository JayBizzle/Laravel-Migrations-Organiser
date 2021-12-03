<?php

namespace Jaybizzle\MigrationsOrganiser\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Filesystem\Filesystem;
use Jaybizzle\MigrationsOrganiser\Migrator;

class MigrateOrganise extends BaseCommand
{
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
     * @var Migrator
     */
    protected $migrator;

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new migrator instance.
     *
     * @param  Filesystem  $files
     * @param  Migrator  $migrator
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
        $basePath = $this->getMigrationPath();
        $migrations = $this->migrator->getMigrationFiles($basePath, false);
        $count = count($migrations);

        if ($count == 0) {
            $this->comment('No migrations to move');

            return;
        }

        foreach ($migrations as $migration_name => $migration_path) {
            $datePath = $this->migrator->getDateFolderStructure($migration_name);

            // Create folder if it does not already exist
            if (! $this->files->exists($basePath.'/'.$datePath)) {
                $this->files->makeDirectory($basePath.'/'.$datePath, 0775, true);
            }

            // Move the migration into its new folder
            $this->files->move($basePath.'/'.$migration_name.'.php', $basePath.'/'.$datePath.$migration_name.'.php');
        }

        $this->info('Migrations organised successfully ('.$count.' migrations moved)');
    }
}
