<?php

namespace Differ\Differ;

use Differ\Formatters\Pretty;
use Differ\Formatters\Plain;
use Differ\Formatters\Json;

use function Funct\Collection\union;

function genDiff($pathToFile1, $pathToFile2, $format = 'pretty')
{
    $rawConfig1 = readFile($pathToFile1);
    $rawConfig2 = readFile($pathToFile2);

    $typeOfConfig1 = pathinfo($pathToFile1, PATHINFO_EXTENSION);
    $typeOfConfig2 = pathinfo($pathToFile2, PATHINFO_EXTENSION);

    $config1 = parseData($rawConfig1, $typeOfConfig1);
    $config2 = parseData($rawConfig2, $typeOfConfig2);

    $diffTree = getAst($config1, $config2);

    switch ($format) {
        case 'plain':
            return Plain\render($diffTree);
        case 'json':
            return Json\render($diffTree);
        default:
            return Pretty\render($diffTree);
    }
}

function getAst($config1, $config2)
{
    $config1 = get_object_vars($config1);
    $config2 = get_object_vars($config2);

    $configKeys1 = array_keys($config1);
    $configKeys2 = array_keys($config2);

    $unionKeys = union($configKeys1, $configKeys2);
    sort($unionKeys);
    
    $diffTree = array_map(function ($key) use ($config1, $config2) {

        if (!array_key_exists($key, $config1)) {
            return ['key' => $key,
                    'value' => $config2[$key],
                    'type' => 'added'];
        }
        if (!array_key_exists($key, $config2)) {
            return ['key' => $key,
                    'value' => $config1[$key],
                    'type' => 'deleted'];
        }
        if (is_object($config1[$key]) && is_object($config2[$key])) {
            return ['key' => $key,
                    'children' => getAst($config1[$key], $config2[$key]),
                    'type' => 'node'];
        }
        if ($config1[$key] === $config2[$key]) {
            return ['key' => $key,
                    'value' => $config1[$key],
                    'type' => 'unchanged'];
        }
        if ($config1[$key] !== $config2[$key]) {
            return ['key' => $key,
                    'oldValue' => $config1[$key],
                    'newValue' => $config2[$key],
                    'type' => 'changed'];
        }
    }, $unionKeys);

    return $diffTree;
}

function readFile($pathToFile)
{
    $fullPath = realpath($pathToFile);
    return file_get_contents($fullPath);
}
