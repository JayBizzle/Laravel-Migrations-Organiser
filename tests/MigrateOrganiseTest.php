<?php

namespace Jaybizzle\MigrationsOrganiser\Tests;

use Illuminate\Filesystem\Filesystem;
use Jaybizzle\MigrationsOrganiser\MigrationsOrganiserServiceProvider;
use Orchestra\Testbench\TestCase;

class MigrateOrganiseTest extends TestCase
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

    public function test_organise_moves_migrations_into_date_folders()
    {
        file_put_contents($this->migrationPath.'/2024_03_26_000000_create_users_table.php', '<?php');
        file_put_contents($this->migrationPath.'/2023_12_01_000000_create_posts_table.php', '<?php');

        $this->artisan('migrate:organise')
            ->expectsOutputToContain('2 migrations moved')
            ->assertExitCode(0);

        $this->assertFileExists($this->migrationPath.'/2024/03/2024_03_26_000000_create_users_table.php');
        $this->assertFileExists($this->migrationPath.'/2023/12/2023_12_01_000000_create_posts_table.php');
    }

    public function test_organise_reports_when_no_migrations_to_move()
    {
        $this->artisan('migrate:organise')
            ->expectsOutputToContain('No migrations to move')
            ->assertExitCode(0);
    }

    public function test_organise_creates_directories_that_do_not_exist()
    {
        file_put_contents($this->migrationPath.'/2025_01_15_000000_create_tags_table.php', '<?php');

        $this->artisan('migrate:organise')->assertExitCode(0);

        $this->assertDirectoryExists($this->migrationPath.'/2025/01');
        $this->assertFileExists($this->migrationPath.'/2025/01/2025_01_15_000000_create_tags_table.php');
        $this->assertFileDoesNotExist($this->migrationPath.'/2025_01_15_000000_create_tags_table.php');
    }
}
