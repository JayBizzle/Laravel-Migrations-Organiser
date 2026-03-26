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

    public function test_disorganise_moves_migrations_back_to_base_folder()
    {
        mkdir($this->migrationPath.'/2024/03', 0775, true);
        file_put_contents($this->migrationPath.'/2024/03/2024_03_26_000000_create_users_table.php', '<?php');

        $this->artisan('migrate:disorganise', ['--force' => true])
            ->expectsOutputToContain('1 migrations moved')
            ->assertExitCode(0);

        $this->assertFileExists($this->migrationPath.'/2024_03_26_000000_create_users_table.php');
    }

    public function test_disorganise_deletes_subdirectories_with_force_flag()
    {
        mkdir($this->migrationPath.'/2024/03', 0775, true);
        file_put_contents($this->migrationPath.'/2024/03/2024_03_26_000000_create_users_table.php', '<?php');

        $this->artisan('migrate:disorganise', ['--force' => true])
            ->expectsOutputToContain('Subdirectories deleted')
            ->assertExitCode(0);

        $this->assertDirectoryDoesNotExist($this->migrationPath.'/2024');
    }

    public function test_disorganise_reports_when_no_migrations_to_move()
    {
        $this->artisan('migrate:disorganise', ['--force' => true])
            ->expectsOutputToContain('No migrations to move')
            ->assertExitCode(0);
    }

    public function test_disorganise_prompts_for_cleanup_without_force()
    {
        mkdir($this->migrationPath.'/2024/03', 0775, true);
        file_put_contents($this->migrationPath.'/2024/03/2024_03_26_000000_create_users_table.php', '<?php');

        $this->artisan('migrate:disorganise')
            ->expectsConfirmation('Delete all subdirectories in migrations folder?', 'yes')
            ->expectsOutputToContain('Subdirectories deleted')
            ->assertExitCode(0);

        $this->assertDirectoryDoesNotExist($this->migrationPath.'/2024');
    }
}
