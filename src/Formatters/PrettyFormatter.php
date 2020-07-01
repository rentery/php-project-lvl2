<?php

namespace Differ\Formatters;

const COUNT_OF_SPACES_UNCHANGED_CASE = 4;
const COUNT_OF_SPACES_OTHER_CASES = 2;

function prettyFormatter($configTree)
{
    $renderedConfigTree = prettyRenderer($configTree);
    return "{\n" . $renderedConfigTree . "\n}";
}

function prettyRenderer($tree)
{

    $iter = function ($tree, $depth = 1) use (&$iter) {
        $renderedData = array_map(function ($node) use ($iter, $depth) {
            $type = $node['type'];
            $key = $node['key'];
            $indent = str_repeat(' ', $depth * COUNT_OF_SPACES_UNCHANGED_CASE);
            $indentSmall = str_repeat(' ', $depth * COUNT_OF_SPACES_UNCHANGED_CASE - COUNT_OF_SPACES_OTHER_CASES);

            switch ($type) {
                case 'node':
                    return "{$indent}{$node['key']}: {\n" . $iter($node['children'], $depth + 1) . "\n    }";
                case 'unchanged':
                    $value = prettyStringify($node['value'], $indent);
                    return "{$indent}{$key}: {$value}";
                case 'changed':
                    $oldValue = prettyStringify($node['oldValue']);
                    $newValue = prettyStringify($node['newValue']);
                    return "{$indentSmall}+ {$key}: {$newValue}\n{$indentSmall}- {$key}: {$oldValue}";
                case 'deleted':
                    $value = prettyStringify($node['value'], $indent);
                    return "{$indentSmall}- {$key}: {$value}";
                case 'added':
                    $value = prettyStringify($node['value'], $indent);
                    return "{$indentSmall}+ {$key}: {$value}";
                default:
                    throw new \Exception('Unknown type: {$type}');
            }
        }, $tree);
    
        return is_array($renderedData) ? implode("\n", $renderedData) : $renderedData;
    };

    $renderedData = $iter($tree);
    return $renderedData;
}

function prettyStringify($item, $indent = '')
{
    $value = json_encode($item);
    $indentLeft = str_repeat(' ', strlen($indent) + COUNT_OF_SPACES_UNCHANGED_CASE);
    $search = ['{', '}', ':', '"'];
    $replace = ["{\n{$indentLeft}", "\n{$indent}}", ': ', ''];
    return str_replace($search, $replace, $value);
}
