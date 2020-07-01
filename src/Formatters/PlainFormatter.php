<?php

namespace Differ\Formatters;

use function Funct\Collection\compact;
use function Funct\Collection\flattenAll;

function plainFormatter($configTree)
{
    $renderedConfigTree = plainRenderer($configTree);
    $flattenTree = compact(flattenAll($renderedConfigTree));
    $diff = implode("\n", $flattenTree);
    return $diff;
}

function plainRenderer($tree)
{
    $iter = function ($tree, $rootKey = '') use (&$iter) {
        $renderedData = array_map(function ($node) use (&$iter, $rootKey) {
            $type = $node['type'];
            $key = $node['key'];

            switch ($type) {
                case 'node':
                    return [$iter($node['children'], "{$rootKey}{$node['key']}.")];
                case 'unchanged':
                    return null;
                case 'changed':
                    $oldValue = plainStringify($node['oldValue']);
                    $newValue = plainStringify($node['newValue']);
                    return "Property '{$rootKey}{$key}' was changed. From '{$oldValue}' to '{$newValue}'";
                case 'deleted':
                    return "Property '{$rootKey}{$key}' was removed";
                case 'added':
                    $value = plainStringify($node['value']);
                    return "Property '{$rootKey}{$key}' was added with value: '{$value}'";
                default:
                    throw new \Exception("Unknown type: {$type}");
            }
        }, $tree);
        return $renderedData;
    };
    return $iter($tree);
}


function plainStringify($item)
{
    if (is_object($item)) {
        return "complex value";
    }
    $value = json_encode($item);
    return str_replace('"', "", $value);
}
