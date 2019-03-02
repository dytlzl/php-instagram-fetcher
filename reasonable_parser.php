<?php
function parse($string, $start_string, $end_string){
    $start = mb_strpos($string, $start_string) + mb_strlen($start_string);
    $string = mb_substr($string, $start);
    $end = mb_strpos($string, $end_string);
    $string = mb_substr($string, 0, $end);
    return $string;
}