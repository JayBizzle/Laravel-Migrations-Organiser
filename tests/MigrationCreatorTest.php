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

    private $packageVersion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->packageVersion = substr(InstalledVersions::getVersionRanges('illuminate/database'), 1);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testBasicCreateMethodStoresMigrationFile()
    {
        $creator = $this->getCreator();

        $date = date('Y').'/'.date('m');
        $creator->expects($this->any())->method('getDatePrefix')->willReturn('foo');
        $creator->getFilesystem()->shouldReceive('exists')->once()->with('stubs/migration.stub')->andReturn(false);
        $creator->getFilesystem()->shouldReceive('get')->once()->with($creator->stubPath().'/migration.stub')->andReturn('DummyClass');
        $creator->getFilesystem()->shouldReceive('exists')->once()->with('foo/'.$date)->andReturn(false);
        $creator->getFilesystem()->shouldReceive('makeDirectory')->once();
        $creator->getFilesystem()->shouldReceive('ensureDirectoryExists')->andReturn(true);
        $expectedStub = $this->expectClassNameReplace() ? 'CreateBar' : 'DummyClass';
        $creator->getFilesystem()->shouldReceive('put')->once()->with('foo/'.$date.'/foo_create_bar.php', $expectedStub);
        $creator->getFilesystem()->shouldReceive('glob')->once()->with('foo/'.$date.'/*.php')->andReturn(['foo/'.$date.'/foo_create_bar.php']);
        $creator->getFilesystem()->shouldReceive('requireOnce')->once()->with('foo/'.$date.'/foo_create_bar.php');

        $creator->create('create_bar', 'foo');
    }

    public function testBasicCreateMethodCallsPostCreateHooks()
    {
        $table = 'baz';

        $creator = $this->getCreator();
        unset($_SERVER['__migration.creator']);
        $creator->afterCreate(function ($table) {
            $_SERVER['__migration.creator'] = $table;
        });
        $date = date('Y').'/'.date('m');
        $creator->expects($this->any())->method('getDatePrefix')->willReturn('foo');
        $creator->getFilesystem()->shouldReceive('exists')->once()->with('stubs/migration.update.stub')->andReturn(false);
        $creator->getFilesystem()->shouldReceive('get')->once()->with($creator->stubPath().'/migration.update.stub')->andReturn('DummyClass DummyTable');
        $creator->getFilesystem()->shouldReceive('exists')->once()->with('foo/'.$date)->andReturn(false);
        $creator->getFilesystem()->shouldReceive('makeDirectory')->once();
        $creator->getFilesystem()->shouldReceive('ensureDirectoryExists')->andReturn(true);
        $expectedStub = $this->expectClassNameReplace() ? 'CreateBar baz' : 'DummyClass baz';
        $creator->getFilesystem()->shouldReceive('put')->once()->with('foo/'.$date.'/foo_create_bar.php', $expectedStub);
        $creator->getFilesystem()->shouldReceive('glob')->once()->with('foo/'.$date.'/*.php')->andReturn(['foo/'.$date.'/foo_create_bar.php']);
        $creator->getFilesystem()->shouldReceive('requireOnce')->once()->with('foo/'.$date.'/foo_create_bar.php');

        $creator->create('create_bar', 'foo', $table);

        $this->assertEquals($_SERVER['__migration.creator'], $table);

        unset($_SERVER['__migration.creator']);
    }

    public function testTableUpdateMigrationStoresMigrationFile()
    {
        $creator = $this->getCreator();
        $date = date('Y').'/'.date('m');
        $creator->expects($this->any())->method('getDatePrefix')->willReturn('foo');
        $creator->getFilesystem()->shouldReceive('exists')->once()->with('stubs/migration.update.stub')->andReturn(false);
        $creator->getFilesystem()->shouldReceive('get')->once()->with($creator->stubPath().'/migration.update.stub')->andReturn('DummyClass DummyTable');
        $creator->getFilesystem()->shouldReceive('exists')->once()->with('foo/'.$date)->andReturn(false);
        $creator->getFilesystem()->shouldReceive('makeDirectory')->once();
        $creator->getFilesystem()->shouldReceive('ensureDirectoryExists')->andReturn(true);
        $expectedStub = $this->expectClassNameReplace() ? 'CreateBar baz' : 'DummyClass baz';
        $creator->getFilesystem()->shouldReceive('put')->once()->with('foo/'.$date.'/foo_create_bar.php', $expectedStub);
        $creator->getFilesystem()->shouldReceive('glob')->once()->with('foo/'.$date.'/*.php')->andReturn(['foo/'.$date.'/foo_create_bar.php']);
        $creator->getFilesystem()->shouldReceive('requireOnce')->once()->with('foo/'.$date.'/foo_create_bar.php');

        $creator->create('create_bar', 'foo', 'baz');
    }

    public function testTableCreationMigrationStoresMigrationFile()
    {
        $creator = $this->getCreator();
        $date = date('Y').'/'.date('m');
        $creator->expects($this->any())->method('getDatePrefix')->willReturn('foo');
        $creator->getFilesystem()->shouldReceive('exists')->once()->with('stubs/migration.create.stub')->andReturn(false);
        $creator->getFilesystem()->shouldReceive('get')->once()->with($creator->stubPath().'/migration.create.stub')->andReturn('DummyClass DummyTable');
        $creator->getFilesystem()->shouldReceive('exists')->once()->with('foo/'.$date)->andReturn(false);
        $creator->getFilesystem()->shouldReceive('makeDirectory')->once();
        $creator->getFilesystem()->shouldReceive('ensureDirectoryExists')->andReturn(true);
        $expectedStub = $this->expectClassNameReplace() ? 'CreateBar baz' : 'DummyClass baz';
        $creator->getFilesystem()->shouldReceive('put')->once()->with('foo/'.$date.'/foo_create_bar.php', $expectedStub);
        $creator->getFilesystem()->shouldReceive('glob')->once()->with('foo/'.$date.'/*.php')->andReturn(['foo/'.$date.'/foo_create_bar.php']);
        $creator->getFilesystem()->shouldReceive('requireOnce')->once()->with('foo/'.$date.'/foo_create_bar.php');

        $creator->create('create_bar', 'foo', 'baz', true);
    }

    public function testTableUpdateMigrationWontCreateDuplicateClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A MigrationCreatorFakeMigration class already exists.');

        $creator = $this->getCreator();
        $date = date('Y').'/'.date('m');
        $creator->getFilesystem()->shouldReceive('glob')->once()->with('foo/'.$date.'/*.php')->andReturn(['foo/'.$date.'/foo_create_bar.php']);
        $creator->getFilesystem()->shouldReceive('requireOnce')->once()->with('foo/'.$date.'/foo_create_bar.php');

        $creator->create('migration_creator_fake_migration', 'foo');
    }

    protected function expectClassNameReplace()
    {
        // Since Laravel 9.x, class name placeholders in migrations are not replaced.
        // @see https://github.com/laravel/framework/blob/8.x/src/Illuminate/Database/Migrations/MigrationCreator.php#L142
        // @see https://github.com/laravel/framework/blob/9.x/src/Illuminate/Database/Migrations/MigrationCreator.php#L139
        return version_compare($this->packageVersion, '9', '<');
    }

    protected function getCreator()
    {
        $files = Mockery::mock(Filesystem::class);
        $customStubs = 'stubs';

        return $this->getMockBuilder(MigrationCreator::class)
            ->setMethods(['getDatePrefix'])
            ->setConstructorArgs([$files, $customStubs])
            ->getMock();
    }
}
