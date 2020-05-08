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

    public function testPlainFiles()
    {
        $json = genDiff("{$this->path}/before.json", "{$this->path}/after.json");
        $yaml = genDiff("{$this->path}/first.yaml", "{$this->path}/second.yaml");
        $expected = <<<DOC
{
    host: hexlet.io
  - proxy: 123.234.53.22
  + timeout: 20
  - timeout: 50
  + verbose: true
}
DOC;
        $this->assertEquals($expected, $json);
        $this->assertEquals($expected, $yaml);
    }



    public function testRecursiveFiles()
    {
        $json = genDiff("{$this->path}/beforeRecursive.json", "{$this->path}/afterRecursive.json");
        $yaml = genDiff("{$this->path}/firstRecursive.yaml", "{$this->path}/secondRecursive.yaml");
        $expected = <<<DOC
{
    common: {
        setting1: Value 1
      - setting2: 200
        setting3: true
      + setting4: blah blah
      + setting5: {
            key5: value5
        }
      - setting6: {
            key: value
        }
    }
    group1: {
      + baz: bars
      - baz: bas
        foo: bar
    }
  - group2: {
        abc: 12345
    }
  + group3: {
        fee: 100500
    }
}
DOC;
        $this->assertEquals($expected, $json);
        $this->assertEquals($expected, $yaml);
    }

    public function testPlainFormatter()
    {
        $json = genDiff("{$this->path}/beforeRecursive.json", "{$this->path}/afterRecursive.json", 'plain');
        $yaml = genDiff("{$this->path}/firstRecursive.yaml", "{$this->path}/secondRecursive.yaml", 'plain');
        $expected = <<<DOC
Property 'common.setting2' was removed
Property 'common.setting4' was added with value: 'blah blah'
Property 'common.setting5' was added with value: 'complex value'
Property 'common.setting6' was removed
Property 'group1.baz' was changed. From 'bas' to 'bars'
Property 'group2' was removed
Property 'group3' was added with value: 'complex value'
DOC;
        $this->assertEquals($expected, $json);
        $this->assertEquals($expected, $yaml);
    }
}
