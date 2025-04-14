<?php

namespace Jaybizzle\MigrationsOrganiser\Tests;

use Composer\InstalledVersions;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Jaybizzle\MigrationsOrganiser\MigrationCreator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class MigrationCreatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private string $packageVersion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->packageVersion = substr(InstalledVersions::getVersionRanges('illuminate/database'), 1);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testBasicCreateMethodStoresMigrationFile()
    {
        $creator = $this->getCreator();
        $date = $this->currentDate();

        $creator->expects($this->any())->method('getDatePrefix')->willReturn('foo');
        $this->mockFilesystem($creator, 'migration.stub', 'DummyClass', $date);

        $expectedStub = $this->replacesStubClassName() ? 'CreateBar' : 'DummyClass';
        $creator->getFilesystem()->shouldReceive('put')->once()->with("foo/$date/foo_create_bar.php", $expectedStub);

        $this->mockPostWriteFilesystem($creator, $date);

        $creator->create('create_bar', 'foo');
    }

    public function testPostCreateHooksAreCalled()
    {
        $creator = $this->getCreator();
        $date = $this->currentDate();
        $table = 'baz';

        unset($_SERVER['__migration.creator']);

        $creator->afterCreate(function ($table) {
            $_SERVER['__migration.creator'] = $table;
        });

        $creator->expects($this->any())->method('getDatePrefix')->willReturn('foo');
        $this->mockFilesystem($creator, 'migration.update.stub', 'DummyClass DummyTable', $date);

        $expectedStub = $this->replacesStubClassName() ? 'CreateBar baz' : 'DummyClass baz';
        $creator->getFilesystem()->shouldReceive('put')->once()->with("foo/$date/foo_create_bar.php", $expectedStub);

        $this->mockPostWriteFilesystem($creator, $date);

        $creator->create('create_bar', 'foo', $table);

        $this->assertEquals($table, $_SERVER['__migration.creator']);
        unset($_SERVER['__migration.creator']);
    }

    public function testTableUpdateMigrationStoresFile()
    {
        $creator = $this->getCreator();
        $date = $this->currentDate();

        $creator->expects($this->any())->method('getDatePrefix')->willReturn('foo');
        $this->mockFilesystem($creator, 'migration.update.stub', 'DummyClass DummyTable', $date);

        $expectedStub = $this->replacesStubClassName() ? 'CreateBar baz' : 'DummyClass baz';
        $creator->getFilesystem()->shouldReceive('put')->once()->with("foo/$date/foo_create_bar.php", $expectedStub);

        $this->mockPostWriteFilesystem($creator, $date);

        $creator->create('create_bar', 'foo', 'baz');
    }

    public function testTableCreationMigrationStoresFile()
    {
        $creator = $this->getCreator();
        $date = $this->currentDate();

        $creator->expects($this->any())->method('getDatePrefix')->willReturn('foo');
        $this->mockFilesystem($creator, 'migration.create.stub', 'DummyClass DummyTable', $date);

        $expectedStub = $this->replacesStubClassName() ? 'CreateBar baz' : 'DummyClass baz';
        $creator->getFilesystem()->shouldReceive('put')->once()->with("foo/$date/foo_create_bar.php", $expectedStub);

        $this->mockPostWriteFilesystem($creator, $date);

        $creator->create('create_bar', 'foo', 'baz', true);
    }

    public function testThrowsWhenDuplicateMigrationClassExists()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A MigrationCreatorFakeMigration class already exists.');

        $creator = $this->getCreator();
        $date = $this->currentDate();

        $creator->getFilesystem()->shouldReceive('exists')->andReturn(false);
        $creator->getFilesystem()->shouldReceive('get')->andReturn('MigrationCreatorFakeMigration');
        $creator->getFilesystem()->shouldReceive('ensureDirectoryExists')->andReturn(true);
        $creator->getFilesystem()->shouldReceive('makeDirectory')->andReturnTrue();
        $creator->getFilesystem()->shouldReceive('put')->andReturn(true);

        $stubPath = __DIR__.'/stubs/MigrationCreatorFakeMigration.php';

        $creator->getFilesystem()->shouldReceive('glob')->once()
            ->with("foo/$date/*.php")
            ->andReturn([$stubPath]);

        $creator->getFilesystem()->shouldReceive('requireOnce')->once()
            ->with($stubPath)
            ->andReturnUsing(fn () => require_once $stubPath);

        $creator->create('migration_creator_fake_migration', 'foo');
    }

    protected function getCreator(): MigrationCreator
    {
        $files = Mockery::mock(Filesystem::class);
        $customStubs = 'stubs';

        return $this->getMockBuilder(MigrationCreator::class)
            ->onlyMethods(['getDatePrefix'])
            ->setConstructorArgs([$files, $customStubs])
            ->getMock();
    }

    protected function mockFilesystem(MigrationCreator $creator, string $stub, string $content, string $date): void
    {
        $creator->getFilesystem()->shouldReceive('exists')->once()->with("stubs/$stub")->andReturn(false);
        $creator->getFilesystem()->shouldReceive('get')->once()
            ->with($creator->stubPath()."/$stub")
            ->andReturn($content);

        $creator->getFilesystem()->shouldReceive('exists')->once()
            ->with("foo/$date")->andReturn(false);

        $creator->getFilesystem()->shouldReceive('makeDirectory')->once();
        $creator->getFilesystem()->shouldReceive('ensureDirectoryExists')->andReturn(true);
    }

    protected function mockPostWriteFilesystem(MigrationCreator $creator, string $date): void
    {
        $creator->getFilesystem()->shouldReceive('glob')->once()
            ->with("foo/$date/*.php")
            ->andReturn(["foo/$date/foo_create_bar.php"]);

        $creator->getFilesystem()->shouldReceive('requireOnce')->once()
            ->with("foo/$date/foo_create_bar.php");
    }

    protected function currentDate(): string
    {
        return date('Y').'/'.date('m');
    }

    /**
     * Laravel 8.x replaced `DummyClass` in stubs; 9.x+ leaves it untouched.
     *
     * @see https://github.com/laravel/framework/blob/8.x/src/Illuminate/Database/Migrations/MigrationCreator.php#L142
     * @see https://github.com/laravel/framework/blob/9.x/src/Illuminate/Database/Migrations/MigrationCreator.php#L139
     */
    protected function replacesStubClassName(): bool
    {
        return version_compare($this->packageVersion, '9', '<');
    }
}