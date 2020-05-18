<?php

namespace Differ\Differ;

use Symfony\Component\Yaml\Yaml;

function parseData($pathToUserFile)
{
    $filePath = getFilePath($pathToUserFile);
    $extension = substr($filePath, -4);
    $rawData = file_get_contents($filePath);
    switch ($extension) {
        case 'json':
            $config = parseJson($rawData);
            break;
        case 'yaml':
            $config = parseYaml($rawData);
            break;
    }
    return $config;
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

function parseJson($raw)
{
    $data = json_decode($raw);
    if (json_last_error() !== 0) {
        return json_last_error_msg();
    }
    return $data;
}

function parseYaml($raw)
{
    $data = Yaml::parse($raw, Yaml::PARSE_OBJECT_FOR_MAP);
    return $data;
}
