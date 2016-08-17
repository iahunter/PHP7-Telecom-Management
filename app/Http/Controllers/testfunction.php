<?php
function is_in_range($val, $min, $max) 
{
  return ($val >= $min && $val <= $max);
}

$test = is_in_range(2,0,5);

//print_r($test);

if ($test){
	print true;
}