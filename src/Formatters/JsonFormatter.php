<?php

namespace Differ\Formatters;

function jsonFormatter($configTree)
{
    return json_encode($configTree);
}
