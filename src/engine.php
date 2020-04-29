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
    $firstFile = $args['<firstFile>'];
    $secondFile = $args['<secondFile>'];
    print_r(Differ\genDiff($firstFile, $secondFile));
}
