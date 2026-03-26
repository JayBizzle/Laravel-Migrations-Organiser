<?php

namespace Jaybizzle\MigrationsOrganiser\Tests;

use Jaybizzle\MigrationsOrganiser\MigrationCreator;
use Jaybizzle\MigrationsOrganiser\MigrationsOrganiserServiceProvider;
use Jaybizzle\MigrationsOrganiser\Migrator;
use Orchestra\Testbench\TestCase;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [MigrationsOrganiserServiceProvider::class];
    }

    public function test_registers_custom_migrator()
    {
        $migrator = $this->app->make('migrator');

        $this->assertInstanceOf(Migrator::class, $migrator);
    }

    public function test_registers_custom_migration_creator()
    {
        $creator = $this->app->make('migration.creator');

        $this->assertInstanceOf(MigrationCreator::class, $creator);
    }

    public function test_registers_organise_command()
    {
        $this->artisan('migrate:organise', ['--help' => true])
            ->assertExitCode(0);
    }

    public function test_registers_flatten_command()
    {
        $this->artisan('migrate:flatten', ['--help' => true])
            ->assertExitCode(0);
    }

    public function test_registers_disorganise_alias()
    {
        $this->artisan('migrate:disorganise', ['--help' => true])
            ->assertExitCode(0);
    }
}
