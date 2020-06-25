<?php

namespace Differ\Formatters;

function prettyFormatter($configTree)
{
    $renderedConfigTree = prettyRenderer($configTree);
    return "{\n" . $renderedConfigTree . "\n}";
}

function prettyRenderer($tree, $indent = '  ')
{
    $renderedData = array_map(function ($node) use ($indent) {
        $type = $node['type'];
        $key = $node['key'];

        switch ($type) {
            case 'node':
                return "    {$node['key']}: {\n" . prettyRenderer($node['children'], "{$indent}    ") . "\n    }";
            case 'unchanged':
                $value = prettyStringify($node['value'], $indent);
                return "  {$indent}{$key}: {$value}";
            case 'changed':
                $oldValue = prettyStringify($node['oldValue']);
                $newValue = prettyStringify($node['newValue']);
                return "{$indent}+ {$key}: {$newValue}\n{$indent}- {$key}: {$oldValue}";
            case 'deleted':
                $value = prettyStringify($node['value'], $indent);
                return "{$indent}- {$key}: {$value}";
            case 'added':
                $value = prettyStringify($node['value'], $indent);
                return "{$indent}+ {$key}: {$value}";
            default:
                throw new \Exception('Unknown type: {$type}');
        }
    }, $tree);

    return is_array($renderedData) ? implode("\n", $renderedData) : $renderedData;
}

function prettyStringify($item, $indent = '')
{
    $value = json_encode($item);
    $search = ['{', '}', ':', '"'];
    $replace = ["{\n      {$indent}", "\n{$indent}  }", ': ', ''];
    return str_replace($search, $replace, $value);
}
