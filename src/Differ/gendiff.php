<?php

namespace Differ\Differ;

use function Funct\Collection\union;
use function Funct\Collection\flatten;
use function Funct\Collection\flattenAll;
use function Funct\Collection\zip;

function genDiff($pathToFile1, $pathToFile2)
{
    $data1 = parseData($pathToFile1);
    $data2 = parseData($pathToFile2);
    $data1 = getAst($data1);
    $data2 = getAst($data2);
    $astWithStates = getStates($data1, $data2);
    $renderedData = diffRenderer($astWithStates);
    $res = "{\n" . stringify($renderedData) . "\n}";
    return $res;
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


function diffRenderer($tree)
{
    $render = array_map(function ($item) {
        $type = $item['type'] ?? null;
        $state = $item['state'] ?? null;
        $key = $item['key'];
        if ($type && !$state) {
            return [$item['key'] => diffRenderer($item['children'])];
        }
        
        if (isset($item['children'])) {
            $value = "{{$item['children']['key']}: {$item['children']['value']}}";
        } else {
            $value = $item['value'];
        }
        $value = is_bool($value) ? boolToString($value) : $value;
        switch ($state) {
            case 'unchanged':
                return "  {$key}: {$value}";
            case 'changed':
                return "+ {$key}: {$value['after']}\n- {$key}: {$value['before']}";
            case 'deleted':
                return "- {$key}: {$value}";
            case 'added':
                return "+ {$key}: {$value}";
        }
    }, $tree);

    return $render;
}


function getStates($data1, $data2)
{
    $keys1 = array_keys($data1);
    $keys2 = array_keys($data2);
    $unionKeys = array_values(union($keys1, $keys2));
    sort($unionKeys);
    $statesOfKeys = array_map(function ($key) use ($data1, $data2) {
        $children1 = $data1[$key]['children'] ?? false;
        $children2 = $data2[$key]['children'] ?? false;
        if ($children1 && $children2) {
            return ['key' => $key,
                    'children' => getStates($children1, $children2),
                    'type' => 'nested'];
        }
        
        if ($children1 || $children2) {
            $value = 'children';
            $type = ['type' => 'nested'];
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

function stringify($diff, $spaces = '  ')
{
    $diff = array_map(function ($item) use ($spaces) {
        if (is_array($item)) {
            $key = key($item);
            return "  {$spaces}{$key}: {\n" . stringify($item[$key], $spaces = "{$spaces}    ") . "\n    }";
        }
        $item = str_replace("\n-", "\n{$spaces}-", $item);
        $item = str_replace("{", "{\n{$spaces}      ", $item);
        $item = str_replace("}", "\n{$spaces}  }", $item);
        return "{$spaces}{$item}";
    }, $diff);
    $res = implode("\n", $diff);
    return $res;
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


function boolToString($string)
{
    if ($string === true) {
        return 'true';
    } elseif ($string === false) {
        return 'false';
    }
    return $string;
}
