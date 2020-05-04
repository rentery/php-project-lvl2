<?php

namespace Differ\Differ;

use function Funct\Collection\union;

function genDiff($pathToFile1, $pathToFile2)
{
    $data1 = parseData($pathToFile1);
    $data2 = parseData($pathToFile2);

    $data1 = get_object_vars($data1);
    $data2 = get_object_vars($data2);

    $keysWithStates = getStates($data1, $data2);

    $diff = array_map(function ($item) {
        $key = $item['key'];
        $value = $item['value'];
        $value = !is_bool($value) ? $value : boolToString($value);
        $state = $item['state'];
        switch ($state) {
            case 'unchanged':
                return "{$key}: {$value}";
                break;
            case 'changed':
                return "+ {$key}: {$value['before']}\n  - {$key}: {$value['after']}";
                break;
            case 'deleted':
                return "- {$key}: {$value}";
                break;
            case 'added':
                return "+ {$key}: {$value}";
        }
    }, $keysWithStates);
    $diff = implode("\n  ", $diff);
    $diff = "{\n    {$diff}\n}";
    return $diff;
}

function getStates($data1, $data2)
{
    $keys1 = array_keys($data1);
    $keys2 = array_keys($data2);
    $unionKeys = array_values(union($keys1, $keys2));
    $statesOfKeys = array_map(function ($key) use ($data1, $data2) {
        if (isUnchadged($data1, $data2, $key)) {
            return ['key' => $key,
                    'value' => $data1[$key],
                    'state' => 'unchanged'];
        }
        if (isChanged($data1, $data2, $key)) {
            return ['key' => $key,
                    'value' => ['before' => $data1[$key], 'after' => $data2[$key]],
                    'state' => 'changed'];
        }
        if (isDeleted($data1, $data2, $key)) {
            return ['key' => $key,
                    'value' => $data1[$key],
                    'state' => 'deleted'];
        }
        if (isAdded($data1, $data2, $key)) {
            return ['key' => $key,
                    'value' => $data2[$key],
                    'state' => 'added'];
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

function boolToString($string)
{
    if ($string === true) {
        return 'true';
    } elseif ($string === false) {
        return 'false';
    }
    return $string;
}
