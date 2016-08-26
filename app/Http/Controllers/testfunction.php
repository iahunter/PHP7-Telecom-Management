<?php

/*
function is_in_range($val, $min, $max)
{
    return $val >= $min && $val <= $max;
}

$test = is_in_range(2, 0, 5);

//print_r($test);

if ($test) {
    echo true;
}
*/

/*
function is_in_same_npanxx($start, $end)
{
    $startarray = str_split($start, 6);
    $endarray = str_split($end, 6);
    $npanxx_start = $startarray[0];
    $npanxx_end = $endarray[0];
    //print "Start: ".$npanxx_start."\n";
    //print "End: ".$npanxx_end."\n";

    if ($npanxx_start == $npanxx_end){
        //print "equal \n";
        return true;
    }
}


print_r(is_in_same_npanxx(1001230000, 1001239999));
*/


function less_10digits($num)
{
    $num_length = strlen((string) $num);
    if ($num_length <= 10) {
        return true;
    }
}

$test = less_10digits(123);
echo $test;

function check_countrycode($num)
{
    if (isset($num) && (! preg_match('/^[0-9]+$/', $num))) {
        echo 'error';
    }
}

echo check_countrycode(+1);
