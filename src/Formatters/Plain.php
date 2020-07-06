<?php

namespace Differ\Formatters\Plain;

use function Funct\Collection\compact;
use function Funct\Collection\flattenAll;

function render($tree)
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
                    $oldValue = stringify($node['oldValue']);
                    $newValue = stringify($node['newValue']);
                    return "Property '{$rootKey}{$key}' was changed. From '{$oldValue}' to '{$newValue}'";
                case 'deleted':
                    return "Property '{$rootKey}{$key}' was removed";
                case 'added':
                    $value = stringify($node['value']);
                    return "Property '{$rootKey}{$key}' was added with value: '{$value}'";
                default:
                    throw new \Exception("Unknown type: {$type}");
            }
        }, $tree);
        return $renderedData;
    };

    $renderedConfigTree = $iter($tree);
    $flattenTree = compact(flattenAll($renderedConfigTree));
    $renderedData = implode("\n", $flattenTree);
    return $renderedData;
}


function stringify($item)
{
    if (is_object($item)) {
        return "complex value";
    }
    $value = json_encode($item);
    return str_replace('"', "", $value);
}
