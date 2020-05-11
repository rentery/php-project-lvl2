<?php

namespace Differ\Formatters;

function jsonFormatter($configTree)
{
    $jsonTree = json_encode($configTree);
    return $jsonTree;
}
