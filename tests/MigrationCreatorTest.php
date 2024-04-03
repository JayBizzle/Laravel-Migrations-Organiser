<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Jaybizzle\MigrationsOrganiser\MigrationCreator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class DatabaseMigrationCreatorTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
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
        $creator->getFilesystem()->shouldReceive('put')->once()->with('foo/'.$date.'/foo_create_bar.php', 'CreateBar');
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
        $creator->getFilesystem()->shouldReceive('put')->once()->with('foo/'.$date.'/foo_create_bar.php', 'CreateBar baz');
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
        $creator->getFilesystem()->shouldReceive('put')->once()->with('foo/'.$date.'/foo_create_bar.php', 'CreateBar baz');
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
        $creator->getFilesystem()->shouldReceive('put')->once()->with('foo/'.$date.'/foo_create_bar.php', 'CreateBar baz');
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

    protected function getCreator()
    {
        $files = m::mock(Filesystem::class);
        $customStubs = 'stubs';

        if(version_compare('8.2.0', PHP_VERSION, '<=')) {
            return $this->getMockBuilder(MigrationCreator::class)
                ->addMethods(['getDatePrefix'])
                ->setConstructorArgs([$files, $customStubs])
                ->getMock();
        } else {
            return $this->getMockBuilder(MigrationCreator::class)
                ->setMethods(['getDatePrefix'])
                ->setConstructorArgs([$files, $customStubs])
                ->getMock();
        }
    }
}
