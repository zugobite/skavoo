<?php

namespace App\Helpers;

/**
 * HTML escape helper, UTF-8 safe.
 *
 * @param string|null $v
 * @return string
 */
function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
