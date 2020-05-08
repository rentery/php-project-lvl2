<?php

namespace Differ\Formatters;

function jsonFormatter($diff)
{
    $res = json_encode($diff);
    return $res;
}