<?php

namespace Differ\Formatters;

function prettyFormatter($configTree)
{
    $renderedConfigTree = prettyRender($configTree);
    return "{\n" . prettyStringify($renderedConfigTree) . "\n}";
}

function prettyRender($tree)
{
    $render = array_map(function ($item) {
        $type = $item['type'] ?? null;
        $state = $item['state'] ?? null;
        $key = $item['key'];
        
        if ($type && !$state) {
            return [$item['key'] => prettyRender($item['children'])];
        }
        
        $value = $item['value'];

        if (is_object($value)) {
            $data = get_object_vars($value);
            $objectKey = key($data);
            $value = "{{$objectKey}: {$data[$objectKey]}}";
        }
        $value = is_bool($value) ? boolToString($value) : $value;

        switch ($state) {
            case 'unchanged':
                return "  {$key}: {$value}";
            case 'changed':
                return "+ {$key}: {$value['after']}\n- {$key}: {$value['before']}";
            case 'deleted':
                return "- {$key}: {$value}";
            case 'added':
                return "+ {$key}: {$value}";
            default:
                throw new \Exception('Unknown state: {$state}');
        }
    }, $tree);

    return $render;
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
