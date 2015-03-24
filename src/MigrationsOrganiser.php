<?php namespace Jaybizzle\MigrationsOrganiser;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\MigrationCreator as MC;

class MigrationsOrganiser extends MC
{
	public function __construct(Filesystem $files)
	{
		$this->files = $files;
	}

	protected function getPath($name, $path)
	{
		$path = $path.'/'.date('Y').'/'.date('m');

		if(!$this->files->exists($path)) {
			$this->files->makeDirectory($path, 0775, true);
		}

		return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
	}
}