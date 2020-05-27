<?php

namespace Differ\Formatters;

function prettyFormatter($configTree)
{
    $renderedConfigTree = prettyRenderer($configTree);
    return "{\n" . prettyStringify($renderedConfigTree) . "\n}";
}


function prettyRenderer($tree)
{
    $renderedTree = array_map(function ($node) {
        $type = $node['type'];
        $key = $node['key'];
        
        if ($type == 'node') {
            return [$key => prettyRenderer($node['children'])];
        }

        if ($type == 'changed') {
            $oldValue = is_bool($node['oldValue']) ? json_encode($node['oldValue']) : $node['oldValue'];
            $newValue = is_bool($node['newValue']) ? json_encode($node['newValue']) : $node['newValue'];
            return "+ {$key}: {$newValue}\n- {$key}: {$oldValue}";
        }

        if (is_object($node['value'])) {
            $data = get_object_vars($node['value']);
            $objectKey = key($data);
            $node['value'] = "{{$objectKey}: {$data[$objectKey]}}";
        }

        $value = is_bool($node['value']) ? json_encode($node['value']) : $node['value'];

        switch ($type) {
            case 'unchanged':
                return "  {$key}: {$value}";
            case 'deleted':
                return "- {$key}: {$value}";
            case 'added':
                return "+ {$key}: {$value}";
            default:
                throw new \Exception('Unknown state: {$state}');
        }
    }, $tree);

    return $renderedTree;
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
        return "{$spaces}{$item}";
    }, $renderedConfigTree);

    $diffString = implode("\n", $diff);
    return $diffString;
}
