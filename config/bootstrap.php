<?php

include __DIR__ . '/../vendor/autoload.php';

if (!function_exists('debug')) {
    function debug($var)
    {
        echo '<pre>' . print_r($var, true) . '</pre>';
    }
}