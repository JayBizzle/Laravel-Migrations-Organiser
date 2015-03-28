<?php namespace Jaybizzle\MigrationsOrganiser;

use Illuminate\Database\Migrations\MigrationCreator as MC;

class MigrationCreator extends MC
{
	protected function getPath($name, $path)
	{
		$path = $path.'/'.date('Y').'/'.date('m');

		if (!$this->files->exists($path)) {
			$this->files->makeDirectory($path, 0775, true);
		}

		return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
	}
}