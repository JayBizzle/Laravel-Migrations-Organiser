<?php

namespace Jaybizzle\MigrationsOrganiser;

use Illuminate\Database\Migrations\Migrator as M;
use RecursiveDirectoryIterator as DirectoryIterator;
use RecursiveIteratorIterator as Iterator;

class Migrator extends M
{
    /**
     * Get all of the migration files in a given path.
     *
     * @param string $path
     * @param bool   $recursive
     *
     * @return array
     */
    public function getMigrationFiles($paths = [], $recursive = true)
    {
        if ($recursive) {
            $paths = $this->getRecursiveFolders($paths);
        }

        $files = parent::getMigrationFiles($paths);

        return $files;
    }

    /**
     * Get all subdirectories located in an array of folders.
     *
     * @param array $folders
     *
     * @return array
     */
    public function getRecursiveFolders($folders)
    {
        if (! is_array($folders)) {
            $folders = [$folders];
        }

        $paths = [];

        foreach ($folders as $folder) {
            $iter = new Iterator(
                new DirectoryIterator($folder, DirectoryIterator::SKIP_DOTS),
                Iterator::SELF_FIRST,
                Iterator::CATCH_GET_CHILD // Ignore "Permission denied"
            );

            $subPaths = [$folder];
            foreach ($iter as $path => $dir) {
                if ($dir->isDir()) {
                    $subPaths[] = $path;
                }
            }

            $paths = array_merge($paths, $subPaths);
        }

        return $paths;
    }

    /**
     * Add date folders to migrations path.
     *
     * @param string $file
     *
     * @return string
     */
    public function getDateFolderStructure($file)
    {
        $parts = explode('_', $file);

        return $parts[0].'/'.$parts[1].'/';
    }
}
