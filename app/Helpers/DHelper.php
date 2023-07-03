<?php


function activeRoute($route, $isClass = false): string
{
    $requestUrl = request()->fullUrl() === $route ? true : false;

    if($isClass) {
        return $requestUrl ? $isClass : '';
    } else {
        return $requestUrl ? 'active' : '';
    }
}

function activeRoutechk($route, $isClass = false): string
{
    $requestUrl = str_contains(request()->fullUrl() ,$route) ? true : false;

    if($isClass) {
        return $requestUrl ? $isClass : '';
    } else {
        return $requestUrl ? 'active' : '';
    }
}

function showdropdownMenu($array)
{
    $requestUrl = explode(url('/').'/admin/',request()->fullUrl());

    if(in_array($requestUrl[1], $array)){
        return 'show';
    }else{
        return '';
    }

}

function oldshowdropdownMenu($route, $isClass = false)
{
    $requestUrl = str_contains(request()->fullUrl() ,$route) ? true : false;

    if($isClass) {
        return $requestUrl ? $isClass : '';
    } else {
        return $requestUrl ? 'show' : '';
    }

}

function globaldate($date)
{
   $date= date('d-m-Y',strtotime($date));
   $date=$date?? date('d-m-Y');
   return $date;
}

 function short_string($str) {
    $rest = substr($str, 0, 30);
    return $rest;
}