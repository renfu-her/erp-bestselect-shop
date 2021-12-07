<?php


if (!function_exists('isActive')) {
    function isActive(String $routeName, String $currentRouteName)
    {
        if ($routeName === $currentRouteName) {
            return 'active';
        } else {
            return '';
        }
    }
}
