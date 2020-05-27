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
        $type = $item['type'];
        $key = $item['key'];

        if ($type === 'node') {
            return [plainRenderer($item['children'], "{$item['key']}.")];
        }
        
        if ($type === 'changed') {
            return "Property '{$mainKey}{$key}' was changed. From '{$item['oldValue']}' to '{$item['newValue']}'";
        }

        if (is_object($item['value'])) {
            $value = "complex value";
        } else {
            $value = $item['value'];
        }

        $value = is_bool($value) ? json_encode($value) : $value;
        switch ($type) {
            case 'unchanged':
                return null;
            case 'deleted':
                return "Property '{$mainKey}{$key}' was removed";
            case 'added':
                return "Property '{$mainKey}{$key}' was added with value: '{$value}'";
            default:
                throw new \Exception("Unknown state: {$type}");
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
