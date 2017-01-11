<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Cookie\FileCookieJar as FileCookieJar;

class Cupiuser extends Model
{
    //

    public static function finduserbyalias($alias)
    {
        $client = new GuzzleHttpClient();

        $URL = env('UNITYCONNECTION_URL');

        $apiRequest = $client->request('GET', "{$URL}/users/", [
                'query'   => ['query' => "(alias startswith {$alias})"],
                'auth'    => [env('UNITYCONNECTION_USER'), env('UNITYCONNECTION_PASS')],
                'verify'  => false,
                'headers' => [
                            'Content-Type'     => 'application/json',
                            'Accept'           => 'application/json',
                        ],
        ]);
        //print_r($apiRequest->getBody()->getContents());

        return json_decode($apiRequest->getBody()->getContents(), true);
    }

    public static function updateUserbyobjectid($ID, $UPDATE = [])
    {
        // *******Something is not working with this for some reason. *****
        $client = new GuzzleHttpClient();

        $URL = env('UNITYCONNECTION_URL');

        $apiRequest = $client->request('PUT', "{$URL}/users/{$ID}", [
                //'query' 	=> ['query' => $ID],
                'auth'        => [env('UNITYCONNECTION_USER'), env('UNITYCONNECTION_PASS')],
                'verify'      => false,
                'headers'     => [
                                'Content-Type'     => 'application/json',
                                'Accept'           => 'application/json',
                            ],
                'json'        => $UPDATE,
                //'debug' => true,

        ]);
        //print_r($apiRequest->getBody()->getContents());

        return json_decode($apiRequest->getBody()->getContents(), true);
    }

    public static function getLDAPUserbyAlias($alias)
    {
        $client = new GuzzleHttpClient();

        $URL = env('UNITYCONNECTION_URL');

        $apiRequest = $client->request('GET', "{$URL}/import/users/ldap", [
                'query'   => ['query' => "(alias startswith {$alias})"],
                'auth'    => [env('UNITYCONNECTION_USER'), env('UNITYCONNECTION_PASS')],
                'verify'  => false,
                'headers' => [
                            'Content-Type'     => 'application/json',
                            'Accept'           => 'application/json',
                        ],
        ]);
        //print_r($apiRequest->getBody()->getContents());

        return json_decode($apiRequest->getBody()->getContents(), true);
    }

    public static function importLDAPUser($TEMPLATE, $UPDATE)
    {
        $client = new GuzzleHttpClient();

        $URL = env('UNITYCONNECTION_URL');

        $apiRequest = $client->request('POST', "{$URL}/import/users/ldap", [
                'query'   => ['templateAlias' => $TEMPLATE],
                'auth'    => [env('UNITYCONNECTION_USER'), env('UNITYCONNECTION_PASS')],
                'verify'  => false,
                'headers' => [
                            'Content-Type'     => 'application/json',
                            'Accept'           => 'application/json',
                            'connection'       => 'keep_alive',
                        ],
                'json'        => $UPDATE,
                //'debug' => true,
                //'http_errors' => true,
        ]);

        //print_r($apiRequest->getBody()->getContents());
        // return json_decode($apiRequest->getBody()->getContents(), true);
        return $apiRequest->getBody()->getContents();
    }
}
