<?php

namespace Differ\Formatters;

use function Funct\Collection\compact;
use function Funct\Collection\flattenAll;

function plainFormatter($configTree)
{
    $renderedConfigTree = plainRenderer($configTree);
    return plainStringify($renderedConfigTree);
}

function plainRenderer($tree, $mainKey = '')
{
    $render = array_map(function ($item) use ($mainKey) {
        $type = $item['type'] ?? null;
        $state = $item['state'] ?? null;
        $key = $item['key'];
        if ($type && !$state) {
            return [plainRenderer($item['children'], "{$item['key']}.")];
        }
        
        if (is_object($item['value'])) {
            $value = "complex value";
        } else {
            $value = $item['value'];
        }
        $value = is_bool($value) ? boolToString($value) : $value;
        switch ($state) {
            case 'unchanged':
                return null;
            case 'changed':
                return "Property '{$mainKey}{$key}' was changed. From '{$value['before']}' to '{$value['after']}'";
            case 'deleted':
                return "Property '{$mainKey}{$key}' was removed";
            case 'added':
                return "Property '{$mainKey}{$key}' was added with value: '{$value}'";
            default:
                throw new \Exception('Unknown state: {$state}');
        }
    }, $tree);
    return $render;
}

function plainStringify($renderedConfigTree)
{
    $flattenTree = compact(flattenAll($renderedConfigTree));
    $diffString = implode("\n", $flattenTree);
    return $diffString;
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
