<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use Differ\Differ;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    private $path;

    public function setUp(): void
    {
        $this->path = __DIR__. '/fixtures';
    }

    public function testLoadJson()
    {
        $diff = genDiff("{$this->path}/before.json", "{$this->path}/after.json");
        $expected = <<<DOC
{
    host: hexlet.io
  + timeout: 50
  - timeout: 20
  - proxy: 123.234.53.22
  + verbose: true
}
DOC;
        $this->assertEquals($expected, $diff);
    }

    public function testLoadYaml()
    {
        $diff = genDiff("{$this->path}/first.yaml", "{$this->path}/second.yaml");
        $expected = <<<DOC
{
    host: hexlet.io
  + timeout: 50
  - timeout: 20
  - proxy: 123.234.53.22
  + verbose: true
}
DOC;
        $this->assertEquals($expected, $diff);
    }
}
