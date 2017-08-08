<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CucmRealTime extends Model
{
    // Realtime API for CUCM Integration.

    public $SOAPCLIENT;

    public function __construct()
    {
        $SCHEMA = env('CALLMANAGER_RISDB_URL').':8443/realtimeservice/services/RisPort?wsdl';

        $OPTIONS = [
                    'trace'                 => true,
                    'exceptions'            => true,
                    'stream_context'        => stream_context_create(
                        ['ssl' => [
                            'verify_peer'              => false,
                            'verify_peer_name'         => false,
                            'allow_self_signed'        => true,
                            ],
                        ]),
                    'connection_timeout'    => 10,
                    'location'              => env('CALLMANAGER_RISDB_URL').':8443/realtimeservice/services/RisPort',
                    'login'                 => env('CALLMANAGER_USER'),
                    'password'              => env('CALLMANAGER_PASS'),
                   ];

        $this->SOAPCLIENT = new \SoapClient($SCHEMA, $OPTIONS);
    }

    public function getIPAddresses($searchCriteria)
    {

        // Build Query
        $query = [];
        $query['Class'] = 'Phone';
        $query['Status'] = 'Any';
        $query['Model'] = '255';
        $query['SelectBy'] = 'Name';
        $query['SelectItems'] = $searchCriteria;

        $output = [];

        try {
            $phones = $this->SOAPCLIENT->SelectCmDevice('', $query);
            if (! is_soap_fault($phones)) {
                $result = $phones['SelectCmDeviceResult'];
            //echo __FUNCTION__ . ": Number of phones: {$result->TotalDevicesFound}".PHP_EOL;

            if ($result->TotalDevicesFound > 0) {
                foreach ($result->CmNodes as $node) {
                    if ($node->ReturnCode == 'Ok') {
                        //echo "Node $node->Name returned " . count($node->CmDevices) . " phones".PHP_EOL;
                  foreach ($node->CmDevices as $device) {
                      if ($device->Status == 'Registered' || $device->Status == 'PartiallyRegistered') {
                          $output[$device->Name]['ipAddress'] = $device->IpAddress;
                      }
                  }
                    }
                }
            }
            } else {
                throw new \Exception('No Phones Found!!!');
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $output;
    }
}
