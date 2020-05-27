<?php

namespace Differ\Differ;

use function Differ\Formatters\prettyFormatter;
use function Differ\Formatters\plainFormatter;
use function Differ\Formatters\jsonFormatter;
use function Funct\Collection\union;

function genDiff($pathToFile1, $pathToFile2, $format = 'pretty')
{
    $pathToConfig1 = getFilePath($pathToFile1);
    $pathToConfig2 = getFilePath($pathToFile2);

    $rawConfig1 = file_get_contents($pathToConfig1);
    $rawConfig2 = file_get_contents($pathToConfig2);

    $config1 = parseData($rawConfig1, $pathToConfig1);
    $config2 = parseData($rawConfig2, $pathToConfig2);

    $diffTree = getAst($config1, $config2);
    
    if ($format === 'plain') {
        return plainFormatter($diffTree);
    } elseif ($format === 'json') {
        return jsonFormatter($diffTree);
    } else {
        return prettyFormatter($diffTree);
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

        if (array_key_exists($key, $config1) && !array_key_exists($key, $config2)) {
            return ['key' => $key,
                    'value' => $config1[$key],
                    'type' => 'deleted'];
        }
        if (!array_key_exists($key, $config1) && array_key_exists($key, $config2)) {
            return ['key' => $key,
                    'value' => $config2[$key],
                    'type' => 'added'];
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
