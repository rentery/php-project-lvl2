<?php

namespace Differ\Differ;

use function Differ\Formatters\prettyFormatter;
use function Differ\Formatters\plainFormatter;
use function Funct\Collection\union;

function genDiff($pathToFile1, $pathToFile2, $format = 'pretty')
{
    $data1 = parseData($pathToFile1);
    $data2 = parseData($pathToFile2);
    $tree1 = getAst($data1);
    $tree2 = getAst($data2);
    $treeWithStates = getStates($tree1, $tree2);
    if ($format == 'plain') {
        return plainFormatter($treeWithStates);
    } else {
        return prettyFormatter($treeWithStates);
    }
}

function getAst($data)
{
    $data = get_object_vars($data);
    $keys = array_keys($data);
    $a = array_combine($keys, array_map(function ($key) use ($data) {
        if (is_object($data[$key])) {
            return ['children' => getAst($data[$key])];
        }
        return $data[$key];
    }, $keys));

    return $a;
}

function getStates($data1, $data2)
{
    $keys1 = array_keys($data1);
    $keys2 = array_keys($data2);
    $unionKeys = array_values(union($keys1, $keys2));
    sort($unionKeys);
    $statesOfKeys = array_map(function ($key) use ($data1, $data2) {
        $children1 = $data1[$key]['children'] ?? null;
        $children2 = $data2[$key]['children'] ?? null;
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
        
        if (isUnchadged($data1, $data2, $key)) {
            $res = ['key' => $key,
                    $value => $data1[$key],
                    'state' => 'unchanged'];
            return isset($type) ? array_merge($res, $type) : $res;
        }
        if (isChanged($data1, $data2, $key)) {
            $res = ['key' => $key,
                    $value => ['before' => $data1[$key], 'after' => $data2[$key]],
                    'state' => 'changed'];
                    return isset($type) ? array_merge($res, $type) : $res;
        }
        if (isDeleted($data1, $data2, $key)) {
            $res = ['key' => $key,
                    $value => $children1 ? ['key' => key($children1), 'value' => $children1[key($children1)]]
                                        : $data1[$key],
                    'state' => 'deleted'];
                    return isset($type) ? array_merge($res, $type) : $res;
        }
        if (isAdded($data1, $data2, $key)) {
            $res = ['key' => $key,
                    $value => $children2 ? ['key' => key($children2), 'value' => $children2[key($children2)]]
                                        : $data2[$key],
                    'state' => 'added'];
                    return isset($type) ? array_merge($res, $type) : $res;
        }
    }, $unionKeys);

    return $statesOfKeys;
}

function isUnchadged($data1, $data2, $key)
{
    if (array_key_exists($key, $data1) && array_key_exists($key, $data2)) {
        if ($data1[$key] === $data2[$key]) {
            return true;
        }
    }
}

function isChanged($data1, $data2, $key)
{
    if (array_key_exists($key, $data1) && array_key_exists($key, $data2)) {
        if ($data1[$key] !== $data2[$key]) {
            return true;
        }
    }
}

function isDeleted($data1, $data2, $key)
{
    if (array_key_exists($key, $data1) && !array_key_exists($key, $data2)) {
        return true;
    }
}

function isAdded($data1, $data2, $key)
{
    if (!array_key_exists($key, $data1) && array_key_exists($key, $data2)) {
        return true;
    }
}
