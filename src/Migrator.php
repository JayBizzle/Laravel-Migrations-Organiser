<?php namespace Jaybizzle\MigrationsOrganiser;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\Migrator as M;
use Illuminate\Database\ConnectionResolverInterface as Resolver;

class Migrator extends M
{
    /**
     * Get all of the migration files in a given path.
     *
     * @param  string  $path
     * @return array
     */
    public function getMigrationFiles($path, $recursive = true)
    {
        if ($recursive === true) {
            $files = $this->rglob($path.'/*_*.php', 0, true);
        } else {
            $files = $this->files->glob($path.'/*_*.php');
        }
        
        // Once we have the array of files in the directory we will just remove the
        // extension and take the basename of the file which is all we need when
        // finding the migrations that haven't been run against the databases.
        if ($files === false) {
            return array();
        }

        $files = array_map(function ($file) {
            return str_replace('.php', '', basename($file));

        }, $files);

        // Once we have all of the formatted file names we will sort them and since
        // they all start with a timestamp this should give us the migrations in
        // the order they were actually created by the application developers.
        sort($files);

        return $files;
    }

    /**
     * Require in all the migration files in a given path.
     *
     * @param  string  $path
     * @param  array   $files
     * @return void
     */
    public function requireFiles($path, array $files)
    {
        foreach ($files as $file) {
            $newPath = $path.$this->getFilePathWithFolders($file).'.php';
            $this->files->requireOnce($newPath);
        }
    }

    /**
     * Run "up" a migration instance.
     *
     * @param  string  $file
     * @param  int     $batch
     * @param  bool    $pretend
     * @return void
     */
    protected function runUp($file, $batch, $pretend)
    {
        // First we will resolve a "real" instance of the migration class from this
        // migration file name. Once we have the instances we can run the actual
        // command such as "up" or "down", or we can just simulate the action.
        $migration = $this->resolve($file);

        if ($pretend) {
            return $this->pretendToRun($migration, 'up');
        }

        $migration->up();

        // Once we have run a migrations class, we will log that it was run in this
        // repository so that we don't try to run it next time we do a migration
        // in the application. A migration repository keeps the migrate order.
        $this->repository->log($this->getFilePathWithoutFolders($file), $batch);

        $this->note("<info>Migrated:</info> $file");
    }

    /**
     * Recursive glob
     * @param  string  $pattern
     * @param  integer $flags
     * @param  boolean $ignore
     * @return array
     */
    public function rglob($pattern, $flags = 0, $ignore = false)
    {
        if ($ignore === false) {
            $files = glob($pattern, $flags);
        } else {
            $files = [];
        }

        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->rglob($dir.'/'.basename($pattern), $flags));
        }

        return $files;
    }

    /**
     * Get the migration file path with our injected date folder
     *
     * @param  string $file
     * @return string
     */
    public function getFilePathWithFolders($file)
    {
        $datePath = $this->getDateFolderStructure($file);
        return '/'.$datePath.$file;
    }

    /**
     * Remove folders from file path
     *
     * @param  string $file
     * @return string
     */
    public function getFilePathWithoutFolders($file)
    {
        return basename($file);
    }

    /**
     * Add date folders to migrations path
     *
     * @param  string $file
     * @return string
     */
    public function getDateFolderStructure($file)
    {
        $parts = explode('_', $file);
        return $parts[0].'/'.$parts[1].'/';
    }
}
