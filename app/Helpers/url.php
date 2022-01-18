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

if (!function_exists('getPageCount')) {
    function getPageCount($pageCount)
    {   
        $maxPage = config('global.dataPerPage');
        if(!is_numeric($pageCount)){
            return $maxPage[0];
        }
        
        rsort($maxPage);
        if ($pageCount > $maxPage[0]) {
            return $maxPage[0];
        }

        return $pageCount;     
        
    }
}
