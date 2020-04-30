<?php

namespace Differ;

function run()
{
    $doc = <<<DOC
    Generate diff

    Usage:
    gendiff (-h|--help)
    gendiff (-v|--version)
    gendiff [--format <fmt>] <firstFile> <secondFile>

    Options:
    -h --help                     Show this screen
    -v --version                  Show version
    --format <fmt>                Report format [default: pretty]

DOC;

    $args = \Docopt::handle($doc, array('version' => 'Gendiff 0.0.1'));
    $firstFilePath = getFilePath($args['<firstFile>']);
    $secondFilePath = getFilePath($args['<secondFile>']);
    //print_r($_SERVER);
    echo Differ\genDiff($firstFilePath, $secondFilePath);
}

function getFilePath($path)
{
    $explodedPath = explode('/', $path);
    if ($explodedPath[1] == 'home') {
        return $path;
    }
    $pwd = $_SERVER['PWD'];
    $path = "{$pwd}/{$path}";
    return $path;
}
