<?php

namespace Differ\Formatters;

function prettyFormatter($configTree)
{
    $renderedConfigTree = prettyRenderer($configTree);
    return "{\n" . prettyStringify($renderedConfigTree) . "\n}";
}

function prettyRenderer($tree)
{
    $renderedData = array_map(function ($node) {
        $type = $node['type'];
        $key = $node['key'];
        
        switch ($type) {
            case 'node':
                return [$key => prettyRenderer($node['children'])];
            case 'unchanged':
                $value = getValue($node);
                return "  {$key}:{$value}";
            case 'changed':
                $oldValue = is_bool($node['oldValue']) ? json_encode($node['oldValue']) : $node['oldValue'];
                $newValue = is_bool($node['newValue']) ? json_encode($node['newValue']) : $node['newValue'];
                return "+ {$key}:{$newValue}\n- {$key}:{$oldValue}";
            case 'deleted':
                $value = getValue($node);
                return "- {$key}:{$value}";
            case 'added':
                $value = getValue($node);
                return "+ {$key}:{$value}";
            default:
                throw new \Exception('Unknown type: {$type}');
        }
    }, $tree);

    return $renderedData;
}

function prettyStringify($renderedConfigTree, $spaces = '  ')
{
    $diff = array_map(function ($item) use ($spaces) {
        if (is_array($item)) {
            $key = key($item);
            return "  {$spaces}{$key}: {\n" . prettyStringify($item[$key], $spaces = "{$spaces}    ") . "\n    }";
        }
        $item = str_replace("\n-", "\n{$spaces}-", $item);
        $item = str_replace("{", "{\n{$spaces}      ", $item);
        $item = str_replace("}", "\n{$spaces}  }", $item);
        $item = str_replace("\"", "", $item);
        $item = str_replace(":", ": ", $item);
        return "{$spaces}{$item}";
    }, $renderedConfigTree);

    $diffString = implode("\n", $diff);
    return $diffString;
}

function getValue($node)
{
    if (is_object($node['value'])) {
        $value = json_encode($node['value']);
    } else {
        $value = $node['value'];
    }
    return is_bool($value) ? json_encode($value) : $value;
}
