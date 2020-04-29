<?php

namespace Differ\Differ;

use function Funct\Collection\union;

function genDiff($pathToFile1, $pathToFile2)
{
    $data1 = getDataFromFile($pathToFile1);
    $data2 = getDataFromFile($pathToFile2);
    $unchanged = getUnchanchedKeys($data1, $data2);
    $changed = getChangedKeys($data1, $data2);
    $deleted = getUniqueKeys($data1, $data2);
    $added = getUniqueKeys($data2, $data1);

    $unchangedKeys = array_reduce($unchanged, function($acc, $item) {
        $key = $item['key'];
        $value = $item['value'];
        $acc = "{$acc}\n\t  {$key}: {$value}";
        return $acc;
    }, "{");

    $changedKeys = array_reduce($changed, function($acc, $item) {
        $key = $item['key'];
        $value = $item['value'];
        $acc = "{$acc}\n\t+ {$key}: {$value[0]}";
        $acc = "{$acc}\n\t- {$key}: {$value[1]}";
        return $acc;
    }, "");

    $deletedKeys = array_reduce($deleted, function ($acc, $item) {
        $key = $item['key'];
        $value = $item['value'];
        $acc = "{$acc}\n\t- {$key}: {$value}";
        return $acc;
    }, "");

    $addedKeys = array_reduce($added, function ($acc, $item) {
        $key = $item['key'];
        $value = $item['value'];
        $acc = "{$acc}\n\t+ {$key}: {$value}";
        return $acc;
    }, "");

    $diff = "{$unchangedKeys}{$changedKeys}{$deletedKeys}{$addedKeys}\n}";
    return $diff;
}



function getDataFromFile($pathToFile)
{
    $raw = file_get_contents($pathToFile);
    if ($raw === false) {
        return;
    }
    $data = json_decode($raw, true);
    if (json_last_error() !== 0) {
        return json_last_error_msg();
    }
    return $data;
}

function getMapData($data)
{
    $mapping = array_map(function ($key, $value) {
        return ['key' => $key, 'value' => $value];
    }, array_keys($data), $data);

    return $mapping;
}

function getChangedKeys($data1, $data2)
{
    $changedKeys1 = array_filter($data1, function($value, $key) use ($data2) {
        return array_key_exists($key, $data2) && $value !== $data2[$key];
    }, ARRAY_FILTER_USE_BOTH);
    
    $changedKeys2 = array_filter($data2, function($value, $key) use ($data1) {
                return array_key_exists($key, $data1) && $value !== $data1[$key];
    }, ARRAY_FILTER_USE_BOTH);

    $changedKeys1 = getMapData($changedKeys1);
    $changedKeys2 = getMapData($changedKeys2);

    $changedKeys = array_map(function ($item1, $item2) {
        return ['key' => $item1['key'],  'value' => [$item1['value'], $item2['value']]];
    }, $changedKeys1, $changedKeys2);

    return $changedKeys;
}

function getUniqueKeys($data1, $data2)
{
    $uniqueKeys = array_filter($data1, function($value, $key) use ($data2) {
                return !array_key_exists($key, $data2);
    }, ARRAY_FILTER_USE_BOTH);

    return getMapData($uniqueKeys);
}

function getUnchanchedKeys($data1, $data2)
{
    $uniqueKeys = array_filter($data1, function($value, $key) use ($data2) {
        return array_key_exists($key, $data2) && $value === $data2[$key];
    }, ARRAY_FILTER_USE_BOTH);

    return getMapData($uniqueKeys);
}