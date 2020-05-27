<?php

namespace Differ\Differ;

use Symfony\Component\Yaml\Yaml;

function parseData($rawConfig, $pathToFile)
{
    $extension = substr($pathToFile, -4);
    switch ($extension) {
        case 'json':
            return jsonParse($rawConfig);
        case 'yaml':
            return yamlParse($rawConfig);
        default:
            throw new \Exception("Unknown file extension: {$extension}. Use json or yaml files");
    }
}

function getFilePath($path)
{
    if (substr($path, 0, 5) == '/home') {
        return $path;
    }
    $pwd = $_SERVER['PWD'];
    $path = "{$pwd}/{$path}";
    return $path;
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
