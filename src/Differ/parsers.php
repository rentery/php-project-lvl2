<?php

namespace Differ\Differ;

use Symfony\Component\Yaml\Yaml;

function parseData($pathToFile)
{
    $filePath = getFilePath($pathToFile);
    $parsedData = startParse($filePath);
    return $parsedData;
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

function startParse($filePath)
{
    $extension = substr($filePath, -4);
    if ($extension === 'json') {
        return jsonParser($filePath);
    }
    if ($extension === 'yaml') {
        return yamlParser($filePath);
    }
}

function jsonParser($pathToFile)
{
    $raw = file_get_contents($pathToFile);
    if ($raw === false) {
        return;
    }
    $data = json_decode($raw);
    if (json_last_error() !== 0) {
        return json_last_error_msg();
    }
    return $data;
}

function yamlParser($pathToFile)
{
    $raw = file_get_contents($pathToFile);
    if ($raw === false) {
        return;
    }
    $data = Yaml::parse($raw, Yaml::PARSE_OBJECT_FOR_MAP);

    return $data;
}
