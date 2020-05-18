<?php

namespace Differ\Differ;

use function Differ\Formatters\prettyFormatter;
use function Differ\Formatters\plainFormatter;
use function Differ\Formatters\jsonFormatter;
use function Funct\Collection\union;

function genDiff($pathToFile1, $pathToFile2, $format = 'pretty')
{
    $config1 = parseData($pathToFile1);
    $config2 = parseData($pathToFile2);
    $diffTree = getAst($config1, $config2);
    if ($format == 'plain') {
        return plainFormatter($diffTree);
    } elseif ($format == 'json') {
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
        $value1 = $config1[$key] ?? null;
        $value2 = $config2[$key] ?? null;

        if (is_object($value1) && is_object($value2)) {
            return ['key' => $key,
                    'children' => getAst($value1, $value2),
                    'type' => 'node'];
        }
        
        if (isUnchadged($config1, $config2, $key)) {
            return ['key' => $key,
                    'value' => $value1,
                    'state' => 'unchanged'];
        }
        if (isChanged($config1, $config2, $key)) {
            return ['key' => $key,
                    'value' => ['before' => $value1, 'after' => $value2],
                    'state' => 'changed'];
        }
        if (isDeleted($config1, $config2, $key)) {
            return ['key' => $key,
                    'value' => $value1,
                    'state' => 'deleted'];
        }
        if (isAdded($config1, $config2, $key)) {
            return ['key' => $key,
                    'value' => $value2,
                    'state' => 'added'];
        }
    }, $unionKeys);

    return $diffTree;
}

function isUnchadged($config1, $config2, $key)
{
    if (array_key_exists($key, $config1) && array_key_exists($key, $config2)) {
        if ($config1[$key] === $config2[$key]) {
            return true;
        }
    }
}

function isChanged($config1, $config2, $key)
{
    if (array_key_exists($key, $config1) && array_key_exists($key, $config2)) {
        if ($config1[$key] !== $config2[$key]) {
            return true;
        }
    }
}

function isDeleted($config1, $config2, $key)
{
    if (array_key_exists($key, $config1) && !array_key_exists($key, $config2)) {
        return true;
    }
}

function isAdded($config1, $config2, $key)
{
    if (!array_key_exists($key, $config1) && array_key_exists($key, $config2)) {
        return true;
    }
}
