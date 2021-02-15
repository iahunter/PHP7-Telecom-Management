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
        $result = DB::connection('egw')->select('select * from endpoint_view_location');

        return $result;
    }

    public static function get_endpoint_by_name($name)
    {
        $result = DB::connection('egw')->select("select * from endpoint_view_location where device_name = '{$name}'");

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
            ORDER BY locations.erl_id

            // Updated with EGW version v5.5.5.202
            SELECT device_name, mac_address, ip_address, endpoint_view_location.last_updated, locations.erl_id
            FROM `endpoint_view_location`
            LEFT JOIN locations ON endpoint_view_location.location_id = locations.location_id
            ORDER BY locations.erl_id
        */

        $endpoints = DB::connection('egw')->select('SELECT device_name, mac_address, ip_address, locations.erl_id AS erl, endpoint_view_location.last_updated FROM `endpoint_view_location` LEFT JOIN locations ON endpoint_view_location.location_id = locations.location_id ORDER BY locations.erl_id');

        $result = [];
        foreach ($endpoints as $phone) {
            $result[$phone->device_name] = (array) $phone;
        }
        //print_r($result);
        return $result;
    }

    public static function list_erls()
    {
        /*
        SELECT *
        FROM `locations`
        WHERE 'erl_id' IS NOT NULL AND TRIM(erl_id) <> ''
        ORDER BY erl_id

        */
        $result = DB::connection('egw')->select("select * from locations WHERE 'erl_id' IS NOT NULL AND TRIM(erl_id) <> '' ORDER BY erl_id");

        return $result;
    }

    public static function list_erls_and_phone_counts()
    {
        /*
        SELECT *
        FROM `locations`
        WHERE 'erl_id' IS NOT NULL AND TRIM(erl_id) <> ''
        ORDER BY erl_id
        */

        $erls = DB::connection('egw')->select("select * from locations WHERE 'erl_id' IS NOT NULL AND TRIM(erl_id) <> '' ORDER BY erl_id");

        /*
        SELECT erl, count(erl)
        FROM `cucmphone`
        GROUP by erl
        */
        // Telecom Management DB
        $counts = DB::table('cucmphone')
            ->select('cucmphone.erl', DB::raw('count(cucmphone.erl) as count'))
            ->where('deleted_at', '=', null)
            ->groupBy('erl')
            ->orderBy('count', 'DESC')
            ->get();

        $counts = json_decode(json_encode($counts, true));
        //$counts = (array)$counts;

        $erlcounts = [];
        foreach ($counts as $count) {
            $erlcounts[$count->erl] = $count;
        }

        //return $erlcounts;

        $result = [];
        foreach ($erls as $erl) {
            //return in_array($erl->erl_id, $erlcounts);
            //return $erl->erl_id;
            if (array_key_exists($erl->erl_id, $erlcounts)) {
                //return $erl->erl_id;
                $count = $erlcounts[$erl->erl_id];
                $count = $count->count;
                $erl->phonecount = $count;
                $result[] = $erl;
            } else {
                $erl->phonecount = 0;
                $result[] = $erl;
            }
        }

        //return $erls;

        return $result;
    }
}
