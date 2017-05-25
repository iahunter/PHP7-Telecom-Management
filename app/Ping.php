<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Ping extends Model
{
    public static function pinghost($host)
    {
        $ttl = 128;
        $timeout = 1;

        $ping = new \JJG\Ping($host, $ttl, $timeout);
        $latency = $ping->ping();

        if ($latency !== false) {
            $return = 'echo reply';
        } else {
            $return = 'Request timed out.';
        }

        return    [
                    'result'         => $return,
                    'latency'        => $latency,
                ];
    }
}
