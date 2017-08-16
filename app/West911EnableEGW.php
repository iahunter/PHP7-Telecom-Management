<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class West911EnableEGW extends Model
{
    // West 911Enable Emergency Gateway Direct MySQL Connection Queries. Requires DB Access...

    // egw DB settings are configured in config/database.php file.

    public static function get_egw_phones()
    {
        $result = DB::connection('egw')->select('select * from cisco_phone');

        return $result;
    }

    public static function get_cisco_phone_by_name($name)
    {
        $result = DB::connection('egw')->select("select * from cisco_phone where device_name = '{$name}'");

        return $result;
    }

    public static function get_all_endpoints()
    {
        $result = DB::connection('egw')->select('select * from endpoint');

        return $result;
    }

    public static function get_endpoint_by_name($name)
    {
        $result = DB::connection('egw')->select("select * from endpoint where device_name = '{$name}'");

        return $result;
    }

    public static function get_all_endpoints_ip_erl()
    {

        /*
            Only Active Phones... with IP and ERL Information
            SELECT device_name, mac_address, ip_address, locations.erl_id AS erl, endpoint.last_updated
            FROM `endpoint`
            LEFT JOIN locations ON endpoint.location_id = locations.location_id
            WHERE endpoint.isDiscovered = 1
        */

        $endpoints = DB::connection('egw')->select('SELECT device_name, mac_address, ip_address, locations.erl_id AS erl, endpoint.last_updated FROM `endpoint` LEFT JOIN locations ON endpoint.location_id = locations.location_id WHERE endpoint.isDiscovered = 1');

        $result = [];
        foreach ($endpoints as $phone) {
            $result[$phone->device_name] = (array) $phone;
        }

        return $result;
    }
}
