<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    private $path;

    public function setUp(): void
    {
        $this->path = __DIR__ . '/fixtures/';
    }

    public function testPrettyFormatter()
    {
        $jsonDiff = genDiff("{$this->path}before.json", "{$this->path}after.json");
        $yamlDiff = genDiff("{$this->path}first.yaml", "{$this->path}second.yaml");
        $expected = file_get_contents("{$this->path}resultPretty.txt");
        $this->assertEquals($expected, $jsonDiff);
        $this->assertEquals($expected, $yamlDiff);
    }

    public function testPlainFormatter()
    {
        $jsonDiff = genDiff("{$this->path}before.json", "{$this->path}after.json", 'plain');
        $yamlDiff = genDiff("{$this->path}first.yaml", "{$this->path}second.yaml", 'plain');
        $expected = file_get_contents("{$this->path}resultPlain.txt");
        $this->assertEquals($expected, $jsonDiff);
        $this->assertEquals($expected, $yamlDiff);
    }
}
