<?php

namespace Differ\Cli;

use function Differ\Differ\genDiff;

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
    $firstFilePath = $args['<firstFile>'];
    $secondFilePath = $args['<secondFile>'];
    $format = $args['--format'];
    print_r(genDiff($firstFilePath, $secondFilePath, $format));
}
