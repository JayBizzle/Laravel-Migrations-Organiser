<?php

namespace Jaybizzle\MigrationsOrganiser\Tests;

use Illuminate\Filesystem\Filesystem;
use Jaybizzle\MigrationsOrganiser\MigrationsOrganiserServiceProvider;
use Orchestra\Testbench\TestCase;

class MigrateDisorganiseTest extends TestCase
{
    private string $migrationPath;

    protected function getPackageProviders($app): array
    {
        return [MigrationsOrganiserServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrationPath = $this->app->databasePath('migrations');
        (new Filesystem)->cleanDirectory($this->migrationPath);
    }

    public function test_disorganise_alias_works()
    {
        mkdir($this->migrationPath.'/2024/03', 0775, true);
        file_put_contents($this->migrationPath.'/2024/03/2024_03_26_000000_create_users_table.php', '<?php');

        $this->artisan('migrate:disorganise', ['--force' => true])
            ->assertExitCode(0);

        $this->assertFileExists($this->migrationPath.'/2024_03_26_000000_create_users_table.php');
    }
}
