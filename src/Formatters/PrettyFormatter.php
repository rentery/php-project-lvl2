<?php

namespace Differ\Formatters;

function prettyFormatter($diff)
{
    $res = prettyRenderer($diff);
    return "{\n" . prettyStringify($res) . "\n}";
}

function prettyRenderer($tree)
{
    $render = array_map(function ($item) {
        $type = $item['type'] ?? null;
        $state = $item['state'] ?? null;
        $key = $item['key'];
        if ($type && !$state) {
            return [$item['key'] => prettyRenderer($item['children'])];
        }
        
        if (isset($item['children'])) {
            $value = "{{$item['children']['key']}: {$item['children']['value']}}";
        } else {
            $value = $item['value'];
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
        }
    }, $tree);

    return $render;
}

function prettyStringify($diff, $spaces = '  ')
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
    }, $diff);

    $res = implode("\n", $diff);
    return $res;
}
