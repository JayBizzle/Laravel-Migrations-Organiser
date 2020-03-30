<?php

namespace Jaybizzle\MigrationsOrganiser;

use Illuminate\Database\Migrations\MigrationCreator as MC;
use InvalidArgumentException;

class MigrationCreator extends MC
{
    protected function ensureMigrationDoesntAlreadyExist($name, $migrationPath = null)
    {
        if (! empty($migrationPath)) {
            $migrationPath = $migrationPath.'/'.date('Y').'/'.date('m');
            $migrationFiles = $this->files->glob($migrationPath.'/*.php');

            foreach ($migrationFiles as $migrationFile) {
                $this->files->requireOnce($migrationFile);
            }
        }

        if (class_exists($className = $this->getClassName($name))) {
            throw new InvalidArgumentException("A {$className} class already exists.");
        }
    }

    protected function getPath($name, $path)
    {
        $path = $path.'/'.date('Y').'/'.date('m');

        if (! $this->files->exists($path)) {
            $this->files->makeDirectory($path, 0775, true);
        }

        return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
    }
}
