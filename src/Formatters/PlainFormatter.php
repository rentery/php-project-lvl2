<?php

namespace Differ\Formatters;

use function Funct\Collection\compact;
use function Funct\Collection\flattenAll;

function plainFormatter($configTree)
{
    $renderedConfigTree = plainRenderer($configTree);
    return plainStringify($renderedConfigTree);
}

function plainRenderer($tree, $rootKey = '')
{
    $render = array_map(function ($node) use ($rootKey) {
        $type = $node['type'];
        $key = $node['key'];
        
        switch ($type) {
            case 'node':
                return [plainRenderer($node['children'], "{$rootKey}{$node['key']}.")];
            case 'unchanged':
                return null;
            case 'changed':
                $oldValue = is_bool($node['oldValue']) ? json_encode($node['oldValue']) : $node['oldValue'];
                $newValue = is_bool($node['newValue']) ? json_encode($node['newValue']) : $node['newValue'];
                return "Property '{$rootKey}{$key}' was changed. From '{$oldValue}' to '{$newValue}'";
            case 'deleted':
                return "Property '{$rootKey}{$key}' was removed";
            case 'added':
                $value = is_object($node['value']) ? 'complex value' : $node['value'];
                $value = is_bool($value) ? json_encode($value) : $value;
                return "Property '{$rootKey}{$key}' was added with value: '{$value}'";
            default:
                throw new \Exception("Unknown type: {$type}");
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
