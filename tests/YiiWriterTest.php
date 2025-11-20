<?php

namespace BeastBytes\Yii\Router\Register\Tests;

use BeastBytes\Yii\Router\Register\Writer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class YiiWriterTest extends TestCase
{
    private array $expected;
    private array $groups;
    private string $path;

    protected function setUp(): void
    {
        $this->path = __DIR__ . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR . 'yii';
        $this->groups = require __DIR__
            . DIRECTORY_SEPARATOR . 'resources'
            . DIRECTORY_SEPARATOR . 'groups.php'
        ;

        $this->expected['groups'] = require __DIR__
            . DIRECTORY_SEPARATOR . 'resources'
            . DIRECTORY_SEPARATOR . 'expected'
            . DIRECTORY_SEPARATOR . 'groups.php'
        ;

        $this->expected['routes']['frontend'] = require __DIR__
            . DIRECTORY_SEPARATOR . 'resources'
            . DIRECTORY_SEPARATOR . 'expected'
            . DIRECTORY_SEPARATOR . 'frontend.php'
        ;

        $this->expected['routes']['backend'] = require __DIR__
            . DIRECTORY_SEPARATOR . 'resources'
            . DIRECTORY_SEPARATOR . 'expected'
            . DIRECTORY_SEPARATOR . 'backend.php'
        ;
    }

    protected function tearDown(): void
    {
        unlink($this->path . DIRECTORY_SEPARATOR . '*.php');
        unlink($this->path . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . '*.php');
    }

    #[Test]
    public function write()
    {
        $writer = new Writer();
        $writer->write($this->groups, $this->path);

        $filename = $this->path . DIRECTORY_SEPARATOR . 'groups.php';
        self::assertFileExists($filename);
        self::assertSame(
            $this->expected['groups'],
            file_get_contents($filename)
        );

        foreach ($this->groups as $group) {
            $filename = $this->path . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . $group->getName() . '.php';
            self::assertFileExists($filename);
            self::assertSame(
                $this->expected['routes'][$group->getName()],
                file_get_contents($filename)
            );
        }
    }
}