<?php


function success( $code= '', $data = [],$count)
{
    $data = [
        'code' => $code,
        'data' =>$data,
        'count' =>$count
    ];
    return $data;
}