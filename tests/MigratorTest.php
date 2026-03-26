<?php

namespace Jaybizzle\MigrationsOrganiser\Tests;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Jaybizzle\MigrationsOrganiser\Migrator;
use PHPUnit\Framework\TestCase;

class MigratorTest extends TestCase
{
    private Migrator $migrator;

    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $repository = $this->createMock(MigrationRepositoryInterface::class);
        $resolver = $this->createMock(ConnectionResolverInterface::class);
        $filesystem = new Filesystem;
        $dispatcher = $this->createMock(Dispatcher::class);

        $this->migrator = new Migrator($repository, $resolver, $filesystem, $dispatcher);

        $this->tempDir = sys_get_temp_dir().'/migrator_test_'.uniqid();
        mkdir($this->tempDir, 0775, true);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->tempDir);
        parent::tearDown();
    }

    public function test_get_date_folder_structure_returns_year_and_month()
    {
        $result = $this->migrator->getDateFolderStructure('2024_03_26_000000_create_users_table');

        $this->assertSame('2024/03/', $result);
    }

    public function test_get_date_folder_structure_with_different_date()
    {
        $result = $this->migrator->getDateFolderStructure('2019_11_01_123456_add_column');

        $this->assertSame('2019/11/', $result);
    }

    public function test_get_recursive_folders_finds_nested_directories()
    {
        mkdir($this->tempDir.'/2024/03', 0775, true);
        mkdir($this->tempDir.'/2024/04', 0775, true);
        mkdir($this->tempDir.'/2023/12', 0775, true);

        $result = $this->migrator->getRecursiveFolders($this->tempDir);

        sort($result);

        $this->assertContains($this->tempDir, $result);
        $this->assertContains($this->tempDir.'/2023', $result);
        $this->assertContains($this->tempDir.'/2023/12', $result);
        $this->assertContains($this->tempDir.'/2024', $result);
        $this->assertContains($this->tempDir.'/2024/03', $result);
        $this->assertContains($this->tempDir.'/2024/04', $result);
        $this->assertCount(6, $result);
    }

    public function test_get_recursive_folders_accepts_array_of_paths()
    {
        $dirA = $this->tempDir.'/a';
        $dirB = $this->tempDir.'/b';
        mkdir($dirA.'/sub', 0775, true);
        mkdir($dirB, 0775, true);

        $result = $this->migrator->getRecursiveFolders([$dirA, $dirB]);

        $this->assertContains($dirA, $result);
        $this->assertContains($dirA.'/sub', $result);
        $this->assertContains($dirB, $result);
        $this->assertCount(3, $result);
    }

    public function test_get_recursive_folders_with_no_subdirectories()
    {
        $result = $this->migrator->getRecursiveFolders($this->tempDir);

        $this->assertSame([$this->tempDir], $result);
    }

    public function test_get_recursive_folders_handles_file_path()
    {
        $file = $this->tempDir.'/2024_03_26_000000_create_users_table.php';
        file_put_contents($file, '<?php');

        $result = $this->migrator->getRecursiveFolders($file);

        $this->assertSame([$file], $result);
    }

    public function test_get_migration_files_finds_files_in_subdirectories()
    {
        mkdir($this->tempDir.'/2024/03', 0775, true);

        file_put_contents(
            $this->tempDir.'/2024/03/2024_03_26_000000_create_users_table.php',
            '<?php return new class {};'
        );

        $files = $this->migrator->getMigrationFiles($this->tempDir, true);

        $this->assertArrayHasKey('2024_03_26_000000_create_users_table', $files);
    }

    public function test_get_migration_files_non_recursive_ignores_subdirectories()
    {
        mkdir($this->tempDir.'/2024/03', 0775, true);

        file_put_contents(
            $this->tempDir.'/2024/03/2024_03_26_000000_create_users_table.php',
            '<?php return new class {};'
        );
        file_put_contents(
            $this->tempDir.'/2024_01_01_000000_base_migration.php',
            '<?php return new class {};'
        );

        $files = $this->migrator->getMigrationFiles($this->tempDir, false);

        $this->assertArrayHasKey('2024_01_01_000000_base_migration', $files);
        $this->assertArrayNotHasKey('2024_03_26_000000_create_users_table', $files);
    }
}
