<?php

namespace Differ\Differ;

use Symfony\Component\Yaml\Yaml;

function parseData($rawConfig, $dataType)
{
    switch ($dataType) {
        case 'json':
            return jsonParse($rawConfig);
        case 'yaml':
            return yamlParse($rawConfig);
        default:
            throw new \Exception("Unknown data type: {$dataType}. Use json or yaml data types");
    }
}

function jsonParse($rawConfig)
{
    $data = json_decode($rawConfig);
    if (json_last_error() !== 0) {
        return json_last_error_msg();
    }
    return $data;
}

function yamlParse($rawConfig)
{
    $data = Yaml::parse($rawConfig, Yaml::PARSE_OBJECT_FOR_MAP);
    return $data;
}
