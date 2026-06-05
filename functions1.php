<?php

if (!function_exists('e')) {
    function e($value) // this is for output only
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('cleanUp')) {
    function cleanUp($field)
    {
        return strip_tags(trim($field));
    }
}

?>