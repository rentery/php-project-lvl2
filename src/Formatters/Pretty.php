<?php

namespace Differ\Formatters\Pretty;

const COUNT_OF_SPACES_UNCHANGED_CASE = 4;
const COUNT_OF_SPACES_OTHER_CASES = 2;

function render($tree)
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
                    $value = stringify($node['value'], $indent);
                    return "{$indent}{$key}: {$value}";
                case 'changed':
                    $oldValue = stringify($node['oldValue']);
                    $newValue = stringify($node['newValue']);
                    return "{$indentSmall}+ {$key}: {$newValue}\n{$indentSmall}- {$key}: {$oldValue}";
                case 'deleted':
                    $value = stringify($node['value'], $indent);
                    return "{$indentSmall}- {$key}: {$value}";
                case 'added':
                    $value = stringify($node['value'], $indent);
                    return "{$indentSmall}+ {$key}: {$value}";
                default:
                    throw new \Exception('Unknown type: {$type}');
            }
        }, $tree);
    
        return is_array($renderedData) ? implode("\n", $renderedData) : $renderedData;
    };

    $renderedData = $iter($tree);
    return "{\n" . $renderedData . "\n}";
}

function stringify($item, $indent = '')
{
    $value = json_encode($item);
    $indentLeft = str_repeat(' ', strlen($indent) + COUNT_OF_SPACES_UNCHANGED_CASE);
    $search = ['{', '}', ':', '"'];
    $replace = ["{\n{$indentLeft}", "\n{$indent}}", ': ', ''];
    return str_replace($search, $replace, $value);
}
