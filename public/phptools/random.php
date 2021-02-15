<?php

function random_str(
    $length,
    $keyspace = '!@#$%^&*()0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
) {
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    if ($max < 1) {
        throw new Exception('$keyspace must be at least two characters long');
    }
    for ($i = 0; $i < $length; $i++) {
        $str .= $keyspace[random_int(0, $max)];
    }

    return $str;
}

echo random_str($length = 16);
