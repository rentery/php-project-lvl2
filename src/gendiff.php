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
    $configTree1 = getAst($config1);
    $configTree2 = getAst($config2);
    $treeWithStates = getStates($configTree1, $configTree2);
    if ($format == 'plain') {
        return plainFormatter($treeWithStates);
    } elseif ($format == 'json') {
        return jsonFormatter($treeWithStates);
    } else {
        return prettyFormatter($treeWithStates);
    }
}

function getAst($config)
{
    $node = get_object_vars($config);
    $nodeKeys = array_keys($node);
    $tree = array_combine($nodeKeys, array_map(function ($key) use ($node) {
        if (is_object($node[$key])) {
            return ['children' => getAst($node[$key])];
        }
        return $node[$key];
    }, $nodeKeys));

    return $tree;
}

function getStates($config1, $config2)
{
    $configKeys1 = array_keys($config1);
    $configKeys2 = array_keys($config2);
    $unionKeys = array_values(union($configKeys1, $configKeys2));
    sort($unionKeys);
    $statesOfKeys = array_map(function ($key) use ($config1, $config2) {
        $children1 = $config1[$key]['children'] ?? null;
        $children2 = $config2[$key]['children'] ?? null;
        if ($children1 && $children2) {
            return ['key' => $key,
                    'children' => getStates($children1, $children2),
                    'type' => 'node'];
        }
        
        if ($children1 || $children2) {
            $value = 'children';
            $type = ['type' => 'node'];
        } else {
            $value = 'value';
        }
        
        if (isUnchadged($config1, $config2, $key)) {
            $state = ['key' => $key,
                    $value => $config1[$key],
                    'state' => 'unchanged'];
            return isset($type) ? array_merge($state, $type) : $state;
        }
        if (isChanged($config1, $config2, $key)) {
            $state = ['key' => $key,
                    $value => ['before' => $config1[$key], 'after' => $config2[$key]],
                    'state' => 'changed'];
                    return isset($type) ? array_merge($state, $type) : $state;
        }
        if (isDeleted($config1, $config2, $key)) {
            $state = ['key' => $key,
                    $value => $children1 ? ['key' => key($children1), 'value' => $children1[key($children1)]]
                                        : $config1[$key],
                    'state' => 'deleted'];
                    return isset($type) ? array_merge($state, $type) : $state;
        }
        if (isAdded($config1, $config2, $key)) {
            $state = ['key' => $key,
                    $value => $children2 ? ['key' => key($children2), 'value' => $children2[key($children2)]]
                                        : $config2[$key],
                    'state' => 'added'];
                    return isset($type) ? array_merge($state, $type) : $state;
        }
    }, $unionKeys);

    return $statesOfKeys;
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
