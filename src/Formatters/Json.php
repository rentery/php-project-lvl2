<?php

namespace Differ\Formatters\Json;

function render($configTree)
{
    return json_encode($configTree);
}
