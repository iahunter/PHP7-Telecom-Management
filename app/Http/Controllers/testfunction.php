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

function is_in_same_npanxx($start, $end)
{
    $startarray = str_split($start, 6);
    $endarray = str_split($end, 6);
    $npanxx_start = $startarray[0];
    $npanxx_end = $endarray[0];

    if ($npanxx_start == $npanxx_end) {
        //print "Equal \n";
        return true;
    }
}


print_r(is_in_same_npanxx(1001230000, 1001239999));
