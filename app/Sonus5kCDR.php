<?php

namespace App;

use Carbon\Carbon;
use phpseclib\Net\SFTP as Net_SFTP;
use Illuminate\Database\Eloquent\Model;

class Sonus5kCDR extends Model
{
    protected $table = 'sonus_cdrs';
    protected $fillable = ['gw_name',
                            'type',
                            'accounting_id',
                            'gcid',
                            'start_date',
                            'start_time',
                            'calling_name',
                            'calling_number',
                            'called_number',
                            'dialed_number',
                            'disconnect_date',
                            'disconnect_time',
                            'call_duration',
                            'disconnect_initiator',
                            'disconnect_reason',
                            'disconnect_ingress_sip_response',
                            'disconnect_egress_sip_response',
                            'route_label',
                            'ingress_callid',
                            'egress_callid',
                            'ingress_media',
                            'egress_media',
                            'ingress_trunkgrp',
                            'egress_trunkgrp',
                            'ingress_lost_ptks',
                            'egress_lost_ptks',
                            'cdr_json',
                        ];

    // Cast data type conversions. Converting one type of data to another.
    protected $casts = [
            'cdr_json' => 'array',
        ];

    public static function convert_sonus_date_format_to_carbon($mm_dd_yyyy)
    {
        // This returns Y-m-d format.
        $date = Carbon::createFromFormat('m/d/Y', $mm_dd_yyyy)->format('Y-m-d');

        return $date;
    }

    public static function get_today_in_sonus_format()
    {
        //return \Carbon\Carbon::now();
        //2017-05-08 22:55:59

        /* Log field 4 will give you date. and 5 will give you time.
        "Start Time (MM/DD/YYYY)": "05/08/2017",
        "Start Time (HH/MM/SS.s)": "20:52:17.4",.
        */

        $currenttime = \Carbon\Carbon::now();
        $format = 'm/d/Y';

        return Carbon::parse($currenttime)->format('m/d/Y');
    }

    public static function get_yesterday_in_sonus_format()
    {
        //return \Carbon\Carbon::now();
        //2017-05-08 22:55:59

        /* Log field 4 will give you date. and 5 will give you time.
        "Start Time (MM/DD/YYYY)": "05/08/2017",
        "Start Time (HH/MM/SS.s)": "20:52:17.4",.
        */

        $yesterday = \Carbon\Carbon::now()->subDay();
        $format = 'm/d/Y';

        return Carbon::parse($yesterday)->format('m/d/Y');
    }

    public static function get_daybeforelast_in_sonus_format()
    {
        //return \Carbon\Carbon::now();
        //2017-05-08 22:55:59

        /* Log field 4 will give you date. and 5 will give you time.
        "Start Time (MM/DD/YYYY)": "05/08/2017",
        "Start Time (HH/MM/SS.s)": "20:52:17.4",.
        */

        $yesterday = \Carbon\Carbon::now()->subDay(2);
        $format = 'm/d/Y';

        return Carbon::parse($yesterday)->format('m/d/Y');
    }

    public static function get_cdr_log_names($SBC)
    {
        $time = \Carbon\Carbon::now();

        $sftp = new Net_SFTP($SBC, 2024);
        if (! $sftp->login(env('SONUSSFTPUSER'), env('SONUSSFTPPASS'))) {
            exit('Login Failed');
        }

        $sftp->chdir('/var/log/sonus/sbx/evlog/');

        $fileobject = $sftp->rawlist();

        $files = (array) $fileobject;
        $fileobject = '';

        $regex = '/ACT$/';

        $cdr_files = [];

        foreach ($files as $file) {
            $filename = $file['filename'];

            $type = explode('.', $filename);
            //return $type;
            if (isset($type[1]) && $type[1] == 'ACT') {
                $cdr_files[] = $files[$filename];
            }
        }
        $files = [];

        $cdr_files_array = $cdr_files;
        $cdr_files = [];
        foreach ($cdr_files_array as $file) {
            $cdr_files[$file['atime']] = $file;
        }

        krsort($cdr_files);
        //print_r($cdr_files);

        $locations = [];
        foreach ($cdr_files as $file) {
            $locations[] = "/var/log/sonus/sbx/evlog/{$file['filename']}";
        }

        return $locations;
    }

    public static function get_last_two_days_cdr_completed_calls($SBC)
    {
        $lasttwodays_calls = [];

            // Get latest CDR File.
            $locations = self::get_cdr_log_names($SBC);

            // Trim off the two most recent files and set them to the locations.
            $locations = array_slice($locations, 0, 1);

        foreach ($locations as $location) {
            $sftp = new Net_SFTP($SBC, 2024);
            if (! $sftp->login(env('SONUSSFTPUSER'), env('SONUSSFTPPASS'))) {
                exit('Login Failed');
            }

            $currentfile = $sftp->get($location);

            $currentfile = explode(PHP_EOL, $currentfile);
            array_shift($currentfile);

            $today = self::get_today_in_sonus_format();
            $yesterday = self::get_yesterday_in_sonus_format();

            foreach ($currentfile as $callrecord) {

                    // Parse record to the the record type.
                    $callrecord = explode(',', $callrecord);
                if ($callrecord[0] == 'STOP') {

                        // Only Return entries that are in the last two days.
                        if ($callrecord[5] == $today || $callrecord[5] == $yesterday) {
                            $callrecord = implode(',', $callrecord);
                            $lasttwodays_calls[] = $callrecord;
                        }
                }

                    // Pop off the first member of the array to reduce memory usage.
                    array_shift($currentfile);
            }
        }
            // Return the raw comma seperated Log entries not an array.
            return implode(PHP_EOL, $lasttwodays_calls);
    }

    public static function get_last_two_days_cdrs($SBC)
    {
        $lasttwodays_calls = [];

        // Get latest CDR File.
        $locations = self::get_cdr_log_names($SBC);

        // Trim off the two most recent files and set them to the locations.
        $locations = array_slice($locations, 0, 1);

        foreach ($locations as $location) {
            $sftp = new Net_SFTP($SBC, 2024);
            if (! $sftp->login(env('SONUSSFTPUSER'), env('SONUSSFTPPASS'))) {
                exit('Login Failed');
            }

            $currentfile = $sftp->get($location);

            $currentfile = explode(PHP_EOL, $currentfile);
            array_shift($currentfile);

            $today = self::get_today_in_sonus_format();
            $yesterday = self::get_yesterday_in_sonus_format();

            foreach ($currentfile as $callrecord) {

                // Parse record to the the record type.
                $callrecord = explode(',', $callrecord);
                if ($callrecord[0] == 'STOP' || $callrecord[0] == 'ATTEMPT') {

                    // Only Return entries that are in the last two days.
                    if ($callrecord[5] == $today || $callrecord[5] == $yesterday) {
                        $callrecord = implode(',', $callrecord);
                        $lasttwodays_calls[] = $callrecord;
                    }
                }

                // Pop off the first member of the array to reduce memory usage.
                array_shift($currentfile);
            }
        }
        // Return the raw comma seperated Log entries not an array.
        return implode(PHP_EOL, $lasttwodays_calls);
    }

    public static function get_travis_view(array $CDRS)
    {
        //return $CDRS;
        $RETURN = [];
        foreach ($CDRS as $CDR) {
            $RECORD = [];

            if ($CDR['Record Type'] == 'START') {
                continue;
            }

            if ($CDR['Record Type'] == 'STOP') {
                $RECORD['Record Type'] = $CDR['Record Type'];
                $RECORD['Gateway Name'] = $CDR['Gateway Name'];
                $RECORD['Global Call ID (GCID)'] = $CDR['Global Call ID (GCID)'];
                $RECORD['Start Time (system ticks)'] = $CDR['Start Time (system ticks)'];
                $RECORD['Start Time (MM/DD/YYYY)'] = $CDR['Start Time (MM/DD/YYYY)'];
                $RECORD['Start Time (HH/MM/SS.s)'] = $CDR['Start Time (HH/MM/SS.s)'];

                $RECORD['Disconnect Time (MM/DD/YYYY)'] = $CDR['Disconnect Time (MM/DD/YYYY)'];
                $RECORD['Disconnect Time (HH:MM:SS.s)'] = $CDR['Disconnect Time (HH:MM:SS.s)'];
                $RECORD['Call Service Duration'] = $CDR['Call Service Duration'];
                $RECORD['Disconnect Initiator'] = $CDR['Disconnect Initiator'];
                $RECORD['Call Disconnect Reason'] = $CDR['Call Disconnect Reason'];

                $RECORD['Calling Name'] = $CDR['Calling Name'];
                $RECORD['Incoming Calling Number'] = $CDR['Incoming Calling Number'];
                $RECORD['Calling Number'] = $CDR['Calling Number'];
                $RECORD['Dialed Number'] = $CDR['Dialed Number'];
                $RECORD['Called Number'] = $CDR['Called Number'];

                $RECORD['Route Label'] = $CDR['Route Label'];
                $RECORD['Route Attempt Number'] = $CDR['Route Attempt Number'];
                $RECORD['Route Selected'] = $CDR['Route Selected'];

                $RECORD['Ingress Protocol Variant Specific Data']['Call ID'] = $CDR['Ingress Protocol Variant Specific Data'][1];

                $RECORD['Ingress Trunk Group Name'] = $CDR['Ingress Trunk Group Name'];
                $RECORD['Ingress Remote Signaling IP Address'] = $CDR['Ingress Remote Signaling IP Address'];
                $RECORD['Ingress Local Signaling IP Address'] = $CDR['Ingress Local Signaling IP Address'];
                $RECORD['Ingress IP Circuit End Point'] = $CDR['Ingress IP Circuit End Point'];

                $RECORD['Egress Protocol Variant Specific Data']['Call ID'] = $CDR['Egress Protocol Variant Specific Data'][1];

                $RECORD['Egress Trunk Group Name'] = $CDR['Egress Trunk Group Name'];
                $RECORD['Egress Local Signaling IP Address'] = $CDR['Egress Local Signaling IP Address'];
                $RECORD['Egress Remote Signaling IP Address'] = $CDR['Egress Remote Signaling IP Address'];
                $RECORD['Egress IP Circuit End Point'] = $CDR['Egress IP Circuit End Point'];

                $RECORD['Media Stream Stats']['Ingress Packet Lost 1'] = $CDR['Media Stream Stats'][7];
                $RECORD['Media Stream Stats']['Egress Packet Lost 1'] = $CDR['Media Stream Stats'][13];

                /*
                $RECORD[""] = $CDR[""];
                $RECORD[""] = $CDR[""];
                $RECORD[""] = $CDR[""];
                $RECORD[""] = $CDR[""];
                $RECORD[""] = $CDR[""];
                $RECORD[""] = $CDR[""];
                $RECORD[""] = $CDR[""];
                $RECORD[""] = $CDR[""];
                */

                $RETURN[] = $RECORD;

                /*
                if($RECORD["Media Stream Stats"]["Ingress Packet Lost 1"] > 100 || $RECORD["Media Stream Stats"]["Egress Packet Lost 1"] > 100){
                    $RETURN[] = $RECORD;
                }
                */
            }

            if ($CDR['Record Type'] == 'ATTEMPT') {
                $RECORD['Record Type'] = $CDR['Record Type'];
                $RECORD['Gateway Name'] = $CDR['Gateway Name'];
                $RECORD['Global Call ID (GCID)'] = $CDR['Global Call ID (GCID)'];
                $RECORD['Start Time (system ticks)'] = $CDR['Start Time (system ticks)'];
                $RECORD['Start Time (MM/DD/YYYY)'] = $CDR['Start Time (MM/DD/YYYY)'];
                $RECORD['Start Time (HH/MM/SS.s)'] = $CDR['Start Time (HH/MM/SS.s)'];

                $RECORD['Disconnect Time (MM/DD/YYYY)'] = $CDR['Disconnect Time (MM/DD/YYYY)'];
                $RECORD['Disconnect Time (HH:MM:SS.s)'] = $CDR['Disconnect Time (HH:MM:SS.s)'];
                //$RECORD["Call Service Duration"] = $CDR["Call Service Duration"];
                $RECORD['Disconnect Initiator'] = $CDR['Disconnect Initiator'];
                $RECORD['Call Disconnect Reason'] = $CDR['Call Disconnect Reason'];

                $RECORD['Call Disconnect Reason Sent to Ingress'] = $CDR['Call Disconnect Reason Sent to Ingress'];
                $RECORD['Call Disconnect Reason Sent to Egress'] = $CDR['Call Disconnect Reason Sent to Egress'];

                $RECORD['Calling Name'] = $CDR['Calling Name'];
                $RECORD['Incoming Calling Number'] = $CDR['Incoming Calling Number'];
                $RECORD['Calling Number'] = $CDR['Calling Number'];
                $RECORD['Dialed Number'] = $CDR['Dialed Number'];
                $RECORD['Called Number'] = $CDR['Called Number'];

                $RECORD['Route Label'] = $CDR['Route Label'];
                $RECORD['Route Attempt Number'] = $CDR['Route Attempt Number'];
                $RECORD['Route Selected'] = $CDR['Route Selected'];

                if (isset($CDR['Ingress Protocol Variant Specific Data']) && $CDR['Ingress Protocol Variant Specific Data']) {
                    $RECORD['Ingress Protocol Variant Specific Data']['Call ID'] = $CDR['Ingress Protocol Variant Specific Data'][1];
                }

                $RECORD['Ingress Trunk Group Name'] = $CDR['Ingress Trunk Group Name'];
                $RECORD['Ingress Remote Signaling IP Address'] = $CDR['Ingress Remote Signaling IP Address'];
                $RECORD['Ingress Local Signaling IP Address'] = $CDR['Ingress Local Signaling IP Address'];
                $RECORD['Ingress IP Circuit End Point'] = $CDR['Ingress IP Circuit End Point'];

                if (isset($CDR['Egress Protocol Variant Specific Data']) && $CDR['Egress Protocol Variant Specific Data']) {
                    $RECORD['Egress Protocol Variant Specific Data']['Call ID'] = $CDR['Egress Protocol Variant Specific Data'][1];
                }

                $RECORD['Egress Trunk Group Name'] = $CDR['Egress Trunk Group Name'];
                $RECORD['Egress Local Signaling IP Address'] = $CDR['Egress Local Signaling IP Address'];
                $RECORD['Egress Remote Signaling IP Address'] = $CDR['Egress Remote Signaling IP Address'];
                $RECORD['Egress IP Circuit End Point'] = $CDR['Egress IP Circuit End Point'];

                /*
                $RECORD[""] = $CDR[""];
                $RECORD[""] = $CDR[""];
                $RECORD[""] = $CDR[""];
                $RECORD[""] = $CDR[""];
                $RECORD[""] = $CDR[""];
                $RECORD[""] = $CDR[""];
                $RECORD[""] = $CDR[""];
                $RECORD[""] = $CDR[""];
                */

                $RETURN[] = $RECORD;
            }

            array_shift($CDRS);
        }

        return $RETURN;
    }

    public static function parse_cdr($LOG)
    {
        // This function parses the comman seperated data and maps each record to a key, value array.

        $LOGLINES = explode("\n", $LOG);                    // Split the lines into individual records
        $LOGPARSED = [];                                // Make a destination to populate with parsed CSV arrays
        foreach ($LOGLINES as $LINE) {                        // Loop through the raw CDR text lines
            if ($LINE != '') {                                // Skip Blank Lines!
                array_push($LOGPARSED, str_getcsv($LINE));    // Parse each line as a set of comma separated values
            }
        }

        $CDRFIELDS = [];                                // Define the fields for each TYPE of CDR!
        $CDRFIELDS['START'] = [
            'Record Type',
            'Gateway Name',
            'Accounting ID',
            'Start Time(System Ticks)',
            'Node Time Zone',
            'Start Time (MM/DD/YYYY)',
            'Start Time (HH/MM/SS.s)',
            'Ticks from Setup Msg to Policy Response',
            'Ticks from Setup Msg to Alert/Proc/Prog',
            'Ticks from Setup Msg to Service Est',
            'Service Delivered',
            'Call Direction',
            'Service Provider',
            'Transit Network Selection Code (TNS)',
            'Calling Number',
            'Called Number',
            'Extra Called Address Digits',
            'Number of Called Num Translations',
            'Called Number Before Translation #1',
            'Translation Type #1',
            'Called Number Before Translation #2',
            'Translation Type #2',
            'Billing Number',
            'Route Label',
            'Route Attempt Number',
            'Route Selected',
            'Egress Local Signaling IP Address',
            'Egress Remote Signaling IP Address',
            'Ingress Trunk Group Name',
            'Ingress PSTN Circuit End Point',
            'Ingress IP Circuit End Point',
            'Egress PSTN Circuit End Point',
            'Egress IP Circuit End Point',
            'Originating Line Information (OLIP)',
            'Jurisdiction Information Parameter (JIP)',
            'Carrier Code',
            'Call Group ID',
            'Ticks from Setup Msg to Rx of EXM',
            'Ticks from Setup Msg to Generation of EXM',
            'Calling Party Nature of Address',
            'Called Party Nature of Address',
            'Ingress Protocol Variant Specific Data',
            'Ingress Signaling Type',
            'Egress Signaling Type',
            'Ingress Far End Switch Type',
            'Egress Far End Switch Type',
            'Carrier Code of Carrier who Owns iTG Far End',
            'Carrier Code of Carrier who Owns eTG Far End',
            'Calling Party Category',
            'Dialed Number',
            'Carrier Selection Information',
            'Called Number Numbering Plan',
            'Generic Address Parameter',
            'Egress Trunk Group Name',
            'Egress Protocol Variant Specific Data',
            'Incoming Calling Number',
            'AMA Call Type',
            'Message Billing Indicator (MBI)',
            'LATA',
            'Route Index Used',
            'Calling Party Presentation Restriction',
            'Incoming ISUP Charge Number',
            'Incoming ISUP Nature Of Address',
            'Dialed Number Nature of Address',
            'Global Call ID (GCID)',
            'Charge Flag',
            'AMA slp ID',
            'AMA BAF Module',
            'AMA Set Hex AB Indication',
            'Service Feature ID',
            'FE Parameter',
            'Satellite Indicator',
            'PSX Billing Info',
            'Originating TDM Trunk Group Type',
            'Terminating TDM Trunk Group Type',
            'Ingress Trunk Member Number',
            'Egress Trunk Group ID',
            'Egress Switch ID',
            'Ingress Local ATM Address',
            'Ingress Remote ATM Address',
            'Egress Local ATM Address',
            'Egress Remote ATM Address',
            'PSX Call Type',
            'Outgoing Route Trunk Group ID',
            'Outgoing Route Message ID',
            'Incoming Route ID',
            'Calling Name',
            'Calling Name Type',
            'Incoming Calling Party Numbering Plan',
            'Outgoing Calling Party Numbering Plan',
            'Calling Party Business Group ID',
            'Called Party Business Group ID',
            'Calling Party PPDN',
            'Ticks from Setup Msg to Last Route Attempt',
            'Billing Number Nature of Address',
            'Incoming Calling Number Nature of Address',
            'Egress Trunk Member Number',
            'Selected Route Type',
            'Cumulative Route Index',
            'ISDN PRI Calling Party Subaddress',
            'Outgoing Trunk Group Number in EXM',
            'Ingress Local Signaling IP Address',
            'Ingress Remote Signaling IP Address',
            'Record Sequence Number',
            'Transmission Medium Requirement',
            'Information Transfer Rate',
            'USI User Info Layer 1',
            'Unrecognized Raw ISUP Calling Party Category',
            'FSD: Egress Release Link Trunking',
            'FSD: Two B-Channel Transfer',
            'Calling Party Business Unit',
            'Called Party Business Unit',
            'FSD: Redirecting',
            'FSD: Ingress Release Link Trunking',
            'PSX ID',
            'PSX Congestion Level',
            'PSX Processing Time (milliseconds)',
            'Script Name',
            'Ingress External Accounting Data',
            'Egress External Accounting Data',
            'Answer Supervision Type',
            'Ingress Sip Refer or Sip Replaces Feature Specific Data',
            'Egress Sip Refer or Sip Replaces Feature Specific Data',
            'Network Transfers Feature Specific Data',
            'Call Condition',
            'Toll Indicator',
            'Generic Number (Number)',
            'Generic Number Presentation Restriction Indicator',
            'Generic Number Numbering Plan',
            'Generic Number Nature of Address',
            'Generic Number Type',
            'Originating Trunk Type',
            'Terminating Trunk Type',
            'VPN Calling Public Presence Number',
            'VPN Calling Private Presence Number',
            'External Furnish Charging Info',
            'Announcement Id',
            'Network Data - Source Information',
            'Network Data - Partition ID',
            'Network Data - Network ID',
            'Network Data - NCOS',
            'ISDN access Indicator',
            'Network Call Reference - Call Identity',
            'Network Call Reference - Signaling Point Code',
            'Ingress MIME Protocol Variant Specific Data',
            'Egress MIME Protocol Variant Specific Data',
            'Video Data - Video Bandwidth, Video Call Duration,Ingress/Egress IP video Endpoint',
            'SVS Customer',
            'SVS Vendor - Deprecated in 7.2.2 as part of Pcr1624',
            'Remote GSX Billing Indicator (PCR1216 - GSX 6.4 for KDDI special V3)',
            'Call To Test PSX',
            'PSX Overlap Route Requests',
            'Call Setup Delay',
            'Overload Status',
            'reserved',
            'reserved',
            'MLPP Precedence Level',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'Global Charge Reference',

        ];
        $CDRFIELDS['STOP'] = [
            'Record Type',
            'Gateway Name',
            'Accounting ID',
            'Start Time (system ticks)',
            'Node Time Zone',
            'Start Time (MM/DD/YYYY)',
            'Start Time (HH/MM/SS.s)',
            'Ticks from Setup Msg to Policy Response',
            'Ticks from Setup Msg to Alert/Proc/Prog',
            'Ticks from Setup Msg to Service Est',
            'Disconnect Time (MM/DD/YYYY)',
            'Disconnect Time (HH:MM:SS.s)',
            'Ticks from Disconnect to Call Termination',
            'Call Service Duration',
            'Call Disconnect Reason',
            'Service Delivered',
            'Call Direction',
            'Service Provider',
            'Transit Network Selection Code (TNS)',
            'Calling Number',
            'Called Number',
            'Extra Called Address Digits',
            'Number of Called Num Translations',
            'Called Number Before Translation #1',
            'Translation Type #1',
            'Called Number Before Translation #2',
            'Translation Type #2',
            'Billing Number',
            'Route Label',
            'Route Attempt Number',
            'Route Selected',
            'Egress Local Signaling IP Address',
            'Egress Remote Signaling IP Address',
            'Ingress Trunk Group Name',
            'Ingress PSTN Circuit End Point',
            'Ingress IP Circuit End Point',
            'Egress PSTN Circuit End Point',
            'Egress IP Circuit End Point',
            'Ingress Number of Audio Bytes Sent',
            'Ingress Number of Audio Packets Sent',
            'Ingress Number of Audio Bytes Received',
            'Ingress Number of Audio Packets Received',
            'Originating Line Information (OLIP)',
            'Jurisdiction Information Parameter (JIP)',
            'Carrier Code',
            'Call Group ID',
            'Script Log Data',
            'Ticks from Setup Msg to Rx of EXM',
            'Ticks from Setup Msg to Generation of EXM',
            'Calling Party Nature of Address',
            'Called Party Nature of Address',
            'Ingress Protocol Variant Specific Data',
            'Ingress Signaling Type',
            'Egress Signaling Type',
            'Ingress Far End Switch Type',
            'Egress Far End Switch Type',
            'Carrier Code of Carrier who Owns iTG Far End',
            'Carrier Code of Carrier who Owns eTG Far End',
            'Calling Party Category',
            'Dialed Number',
            'Carrier Selection Information',
            'Called Number Numbering Plan',
            'Generic Address Parameter',
            'Disconnect Initiator',
            'Ingress Number of Packets Recorded as Lost',
            'Ingress Interarrival Packet Jitter',
            'Ingress Last Measurement for Latency',
            'Egress Trunk Group Name',
            'Egress Protocol Variant Specific Data',
            'Incoming Calling Number',
            'AMA Call Type',
            'Message Billing Indicator (MBI)',
            'LATA',
            'Route Index Used',
            'Calling Party Presentation Restriction',
            'Incoming ISUP Charge Number',
            'Incoming ISUP Nature Of Address',
            'Dialed Number Nature of Address',
            'Ingress Codec Info',
            'Egress Codec Info',
            'Ingress RTP Packetization Time',
            'Global Call ID (GCID)',
            'Originator Echo Cancellation',
            'Terminator Echo Cancellation',
            'Charge Flag',
            'AMA slp ID',
            'AMA BAF Module',
            'AMA Set Hex AB Indication',
            'Service Feature ID',
            'FE Parameter',
            'Satellite Indicator',
            'PSX Billing Info',
            'Originating TDM Trunk Group Type',
            'Terminating TDM Trunk Group Type',
            'Ingress Trunk Member Number',
            'Egress Trunk Group ID',
            'Egress Switch ID',
            'Ingress Local ATM Address',
            'Ingress Remote ATM Address',
            'Egress Local ATM Address',
            'Egress Remote ATM Address',
            'PSX Call Type',
            'Outgoing Route Trunk Group ID',
            'Outgoing Route Message ID',
            'Incoming Route ID',
            'Calling Name',
            'Calling Name Type',
            'Incoming Calling Party Numbering Plan',
            'Outgoing Calling Party Numbering Plan',
            'Calling Party Business Group ID',
            'Called Party Business Group ID',
            'Calling Party PPDN',
            'Ticks from Setup Msg to Last Route Attempt',
            'Billing Number Nature of Address',
            'Incoming Calling Number Nature of Address',
            'Egress Trunk Member Number',
            'Selected Route Type',
            'Telcordia Long Duration Record Type',
            'Ticks From Previous Record',
            'Cumulative Route Index',
            'Call Disconnect Reason Sent to Ingress',
            'Call Disconnect Reason Sent to Egress',
            'ISDN PRI Calling Party Subaddress',
            'Outgoing Trunk Group Number in EXM',
            'Ingress Local Signaling IP Address',
            'Ingress Remote Signaling IP Address',
            'Record Sequence Number',
            'Transmission Medium Requirement',
            'Information Transfer Rate',
            'USI User Info Layer 1',
            'Unrecognized Raw ISUP Calling Party Category',
            'FSD: Egress Release Link Trunking',
            'FSD: Two B-Channel Transfer',
            'Calling Party Business Unit',
            'Called Party Business Unit',
            'FSD: Redirecting',
            'FSD: Ingress Release Link Trunking',
            'PSX Index',
            'PSX Congestion Level',
            'PSX Processing Time (milliseconds)',
            'Script Name',
            'Ingress External Accounting Data',
            'Egress External Accounting Data',
            'Egress RTP Packetization Time',
            'Egress Number of Audio Bytes Sent',
            'Egress Number of Audio Packets Sent',
            'Egress Number of Audio Bytes Received',
            'Egress Number of Audio Packets Received',
            'Egress Number of Packets Recorded as Lost',
            'Egress Interarrival Packet Jitter',
            'Egress Last Measurement for Latency',
            'Ingress Maximum Packet Outage',
            'Egress Maximum Packet Outage',
            'Ingress Packet Playout Buffer Quality',
            'Egress Packet Playout Buffer Quality',
            'Answer Supervision Type',
            'Ingress Sip Refer or Sip Replaces Feature Specific Data',
            'Egress Sip Refer or Sip Replaces Feature Specific Data',
            'Network Transfers Feature Specific Data',
            'Call Condition',
            'Toll Indicator',
            'Generic Number ( Number )',
            'Generic Number Presentation Restriction Indicator',
            'Generic Number Numbering Plan',
            'Generic Number Nature of Address',
            'Generic Number Type',
            'Originating Trunk Type',
            'Terminating Trunk Type',
            'Remote GSX Billing Indicator',
            'VPN Calling Private Presence Number',
            'VPN Calling Public Presence Number',
            'External Furnish Charging Info',
            'Ingress Policing Discards',
            'Egress Policing Discards',
            'Announcement Id',
            'Network Data - Source Information',
            'Network Data - Partition ID',
            'Network Data - Network ID',
            'Network Data - NCOS',
            'Ingress SRTP (Secure RTP/RTCP',
            'Egress SRTP (Secure RTP/RTCP)',
            'ISDN access Indicator',
            'Call Disconnect Location',
            'Call Disconnect Location Transmitted to Ingress',
            'Call Disconnect Location Transmitted to Egress',
            'Network Call Reference - Call Identity',
            'Network Call Reference - Signaling Point Code',
            'Ingress MIME Protocol Variant Specific Data',
            'Egress MIME Protocol Variant Specific Data',
            'Modem Tone Type',
            'Modem Tone Signal Level',
            'Video Data - Video Bandwidth, Video Call Duration, ',
            'Video Statistics - Ingress/Egress Video Statistics.',
            'SVS Customer',
            'SVS Vendor - Deprecated in 7.2.2 as part of Pcr1624',
            'Call To Test PSX',
            'Psx Overlap Route Requests',
            'Call Setup Delay',
            'Overload Status',
            'reserved',
            'reserved',
            'Ingress DSP Data Bitmap',
            'Egress DSP Data Bitmap',
            'Call Recorded Indicator',
            'Call Recorded RTP Tx Ip Address',
            'Call Recorded RTP Tx Port Number',
            'Call Recorded RTP Rv Ip Address',
            'Call Recorded RTP Rv Port Number',
            'Mlpp Precedence Level',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'Global Charge Reference',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'Ingress Inbound R-Factor',
            'Ingress Outbount R-Factor',
            'Egress Inbound R-Factor',
            'Egress Outbount R-Factor',
            'Media Stream Data',
            'Media Stream Stats',
            'Transcoded Indicator',
            'HD Codec Rate',
            'Remote Ingress Audio RTCP Learned Metrics',
            'Remote Egress Audio RTCP Learned Metrics',
        ];
        $CDRFIELDS['ATTEMPT'] = [
            'Record Type',
            'Gateway Name',
            'Accounting ID',
            'Start Time (system ticks)',
            'Node Time Zone',
            'Start Time (MM/DD/YYYY)',
            'Start Time (HH/MM/SS.s)',
            'Ticks from Setup Msg to Policy Response',
            'Ticks from Setup Msg to Alert/Proc/Prog',
            'Disconnect Time (HH:MM:SS.s)',
            'Ticks from Disconnect to Call Termination',
            'Call Disconnect Reason',
            'Service Delivered',
            'Call Direction',
            'Service Provider',
            'Transit Network Selection Code (TNS)',
            'Calling Number',
            'Called Number',
            'Extra Called Address Digits',
            'Number of Called Num Translations',
            'Called Number Before Translation #1',
            'Translation Type #1',
            'Called Number Before Translation #2',
            'Translation Type #2',
            'Billing Number',
            'Route Label',
            'Route Attempt Number',
            'Route Selected',
            'Egress Local Signaling IP Address',
            'Egress Remote Signaling IP Address',
            'Ingress Trunk Group Name',
            'Ingress PSTN Circuit End Point',
            'Ingress IP Circuit End Point',
            'Egress PSTN Circuit End Point',
            'Egress IP Circuit End Point',
            'Originating Line Information (OLIP)',
            'Jurisdiction Information Parameter (JIP)',
            'Carrier Code',
            'Call Group ID',
            'Script Log Data',
            'Ticks from Setup Msg to Rx of EXM',
            'Ticks from Setup Msg to Generation of EXM',
            'Calling Party Nature of Address',
            'Called Party Nature of Address',
            'Ingress Protocol Variant Specific Data',
            'Ingress Signaling Type',
            'Egress Signaling Type',
            'Ingress Far End Switch Type',
            'Egress Far End Switch Type',
            'Carrier Code of Carrier who Owns iTG Far End',
            'Carrier Code of Carrier who Owns eTG Far End',
            'Calling Party Category',
            'Dialed Number',
            'Carrier Selection Information',
            'Called Number Numbering Plan',
            'Generic Address Parameter',
            'Disconnect Initiator',
            'Egress Trunk Group Name',
            'Egress Protocol Variant Specific Data',
            'Incoming Calling Number',
            'AMA Call Type',
            'Message Billing Indicator (MBI)',
            'LATA',
            'Route Index Used',
            'Calling Party Presentation Restriction',
            'Incoming ISUP Charge Number',
            'Incoming ISUP Nature Of Address',
            'Dialed Number Nature of Address',
            'Ingress Codec Info',
            'Egress Codec Info',
            'Ingress RTP Packetization Time',
            'Global Call ID (GCID)',
            'Terminated With Script Execution',
            'Originator Echo Cancellation',
            'Terminator Echo Cancellation',
            'Charge Flag',
            'AMA slp ID',
            'AMA BAF Module',
            'AMA Set Hex AB Indication',
            'Service Feature ID',
            'FE Parameter',
            'Satellite Indicator',
            'PSX Billing Info',
            'Originating TDM Trunk Group Type',
            'Terminating TDM Trunk Group Type',
            'Ingress Trunk Member Number',
            'Egress Trunk Group ID',
            'Egress Switch ID',
            'Ingress Local ATM Address',
            'Ingress Remote ATM Address',
            'Egress Local ATM Address',
            'Egress Remote ATM Address',
            'PSX Call Type',
            'Outgoing Route Trunk Group ID',
            'Outgoing Route Message ID',
            ' Incoming Route ID',
            'Calling Name',
            'Calling Name Type',
            'Incoming Calling Party Numbering Plan',
            'Outgoing Calling Party Numbering Plan',
            'Calling Party Business Group ID',
            'Called Party Business Group ID',
            'Calling Party PPDN',
            'Ticks from Setup Msg to Last Route Attempt',
            'Disconnect Time (MM/DD/YYYY)',
            'Billing Number Nature of Address',
            'Incoming Calling Number Nature of Address',
            'Egress Trunk Member Number',
            'Selected Route Type',
            'Cumulative Route Index',
            'Call Disconnect Reason Sent to Ingress',
            'Call Disconnect Reason Sent to Egress',
            'ISDN PRI Calling Party Subaddress',
            'Outgoing Trunk Group Number in EXM',
            'Ingress Local Signaling IP Address',
            'Ingress Remote Signaling IP Address',
            'Record Sequence Number',
            'Transmission Medium Requirement',
            'Information Transfer Rate',
            'USI User Info Layer 1',
            'Unrecognized Raw ISUP Calling Party Category',
            'FSD: Release Link Trunking',
            'FSD: Two B-Channel Transfer',
            'Calling Party Business Unit',
            'Called Party Business Unit',
            'FSD: Redirecting',
            'FSD: Ingress Release Link Trunking',
            'PSX Index',
            'PSX Congestion Level',
            'PSX Processing Time (milliseconds)',
            'Script Name',
            'Ingress External Accounting Data',
            'Egress External Accounting Data',
            'Egress RTP Packetization Time',
            'Answer Supervision Type',
            'Ingress Sip Refer & Replaces Feature Specific Data',
            'Egress Sip Refer Feature Specific Data',
            'Network Transfers Feature Specific Data',
            'Call Condition',
            'Toll Indicator',
            'Generic Number (Number)',
            'Generic Number Presentation Restriction Indicator',
            'Generic Number Numbering Plan',
            'Generic Number Nature of Address',
            'Generic Number Type',
            'Final Attempt Indicator',
            'Originating Trunk Type',
            'Terminating Trunk Type',
            'Remote GSX Billing Indicator',
            'Extra Disconnect Reason',
            'VPN Calling Private Presence Number',
            'VPN Calling Public Presence Number',
            'External Furnish Charging Info',
            'Announcement Id',
            'Network Data - Source Information',
            'Network Data - Partition ID',
            'Network Data - Network ID',
            'Network Data - NCOS',
            'ISDN access Indicator',
            'Call Disconnect Location',
            'Call Disconnect Location Transmitted to Ingress',
            'Call Disconnect Location Transmitted to Egress',
            'Network Call Reference - Call Identity',
            'Network Call Reference - Signaling Point Code',
            'Ingress MIME Protocol Variant Specific Data',
            'Egress MIME Protocol Variant Specific Data',
            'Video Data - Video Bandwidth, Video Call Duration, ',
            'Ingress/Egress IP video Endpoint',
            'SVS Customer',
            'SVS Vendor - Deprecated in 7.2.2 as part of Pcr1624',
            'Call To Test PSX',
            'Psx Overlap Route Requests',
            'Call Setup Delay',
            'Overload Status',
            'reserved',
            'reserved',
            'Mlpp Precedence Level',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'reserved',
            'Global Charge Reference',

        ];

        $CDRS = [];                                // Make a final list of fully field mapped CDR records
        foreach ($LOGPARSED as $PARSED) {                    // Loop through parsed CSV data and map to the correct CDR fields
            $i = 0;                                        // Counter for field iteration through data
            //return $PARSED;
            //$PARSED = explode(",",$PARSED);
            //return $PARSED;
            $CDRTYPE = $PARSED[0];                        // The first field type is always the CDR type
            if (isset($CDRFIELDS[$CDRTYPE])) {        // If the CDR type is known:
                $CDR = [];                            // Container for our final CDR with mapped key,value pairs
                foreach ($CDRFIELDS[$CDRTYPE] as $FIELD) {    // Loop through CDR fields and associate them with known values
                    $VALUE = $PARSED[$i++];                // Get the value for this field
                    if (strpos($VALUE, ',') !== false) {    // IF our final parsed value contains commas, it is a SUB RECORD!!!
                        //$VALUE = \Metaclassing\Utility::dumperToString(str_getcsv($VALUE) );	// FOR NOW! v1.0 just parse it into dumpered output for display!
                        $VALUE = explode(',', $VALUE);
                    }
                    $CDR[$FIELD] = $VALUE;                // assign the new CDR field a value and increment our position in the parsed array
                }
                array_push($CDRS, $CDR);                    // Jam the newly created key,value'd CDR into the CDR array
            } else {
                echo "<b>WARNING: DROPPED UNKNOWN CDR TYPE '{$PARSED[0]}'</b><br>\n";
            }
        }
        //return count($CDRS);
        //asort($CDRS);
        krsort($CDRS);

        return $CDRS;
    }

    public static function get_disconnect_initiator_code($code)
    {
        // Search for disconnect initiator code and return the description.
        // https://support.sonus.net/display/SBXDOC41/CDR+Field+Descriptions#CDRFieldDescriptions-disconnectInitiator
        $disconnect_initiator_code = [
                                        '0' => 'INTERNAL',
                                        '1' => 'CALLING PARTY',
                                        '2' => 'CALLED PARTY',
                                        '3' => 'INTERNAL EARLY',
                                        '4' => 'CALLING PARTY EARLY',
                                    ];

        return $disconnect_initiator_code[$code];
    }
	
	public static function get_call_termination_codes(){
		// Search for reason code and return the description.
		// https://support.sonus.net/display/SBXDOC50/Call+Termination+Reason+Codes
		$termination_reason_codes = [
                                        '0'   => 'INVALID DISCONNECT REASON',
                                        '1'   => 'UNALLOCATED NUMBER ',
                                        '2'   => 'NO ROUTE TO SPECIFIED NETWORK',
                                        '3'   => 'NO ROUTE TO DESTINATION',
                                        '4'   => 'SEND SPECIAL INFO TONE',
                                        '5'   => 'MISDIALED TRUNK PREFIX',
                                        '6'   => 'CHANNEL UNACCEPTABLE',
                                        '7'   => 'CALL AWARDED',
                                        '8'   => 'PREEMPTION',
                                        '9'   => 'PREEMPTION - CIRCUIT RESERVED',
                                        '12'  => 'VALUE NOT CODED',
                                        '16'  => 'NORMAL ROUTE CLEARING',
                                        '17'  => 'USER BUSY',
                                        '18'  => 'NO USER RESPONDING',
                                        '19'  => 'NO ANSWER FROM USER',
                                        '20'  => 'SUBSCRIBER ABSENT',
                                        '21'  => 'CALL REJECTED',
                                        '22'  => 'NUMBER CHANGED',
                                        '23'  => 'UNALLOCATED DESTINATION NUMBER',
                                        '24'  => 'UNKNOWN BUSINESS GROUP',
                                        '25'  => 'EXCHANGE ROUTING ERROR',
                                        '26'  => 'MISROUTED CALL TO PORTED NUMBER ',
                                        '27'  => 'DESTINATION OUT OF ORDER',
                                        '28'  => 'INVALID NUMBER FORMAT',
                                        '29'  => 'FACILITY REJECTED',
                                        '30'  => 'RESPONSE TO STATUS ENQUIRY',
                                        '31'  => 'NORMAL UNSPECIFIED',
                                        '32'  => 'VALUE NOT CODED',
                                        '34'  => 'NO CIRCUIT AVAILABLE',
                                        '35'  => 'VALUE NOT CODED',
                                        '36'  => 'VALUE NOT CODED',
                                        '38'  => 'NETWORK OUT OF ORDER',
                                        '39'  => 'PERM FM CONNECTION OOS',
                                        '40'  => 'PERM FM CONNECTION OPERATIONAL',
                                        '41'  => 'TEMPORARY FAILURE',
                                        '42'  => 'SWITCHINGEQUIP CONGESTION',
                                        '43'  => 'ACCESS INFORMATION DISCARDED',
                                        '44'  => 'REQUESTED CIRCUIT NOT AVAILABLE',
                                        '45'  => 'ANSI PREEMPTION',
                                        '46'  => 'PRECEDENCE CALL BLOCKED',
                                        '47'  => 'RESOURCE UNAVAILABLE UNSPECIFIED',
                                        '49'  => 'QUALITY OF SERVICE UNAVAILABLE',
                                        '50'  => 'REQUESTED FACILITY NOT SUBSCRIBED',
                                        '51'  => 'CALL TYPE INCOMPATIBLE WITH SEREVICE',
                                        '53'  => 'OUTGOING CALL BARRED CUG',
                                        '54'  => 'CALL BLOCKED GROUP RESTRICTIONS',
                                        '55'  => 'INCOMING CALL BARRED CUG',
                                        '57'  => 'BEARER CAPABILITY NOT AUTHORIZED',
                                        '58'  => 'BEARER CAPABILITY PRESENTLY NOT AVAILABLE',
                                        '62'  => 'INCONSISTENT OUTGOING SUBSCRIBER CLASS',
                                        '63'  => 'SERVICE OR OPTION NOT AVAILABLE UNSPECIFIED',
                                        '65'  => 'BEARER CAPABILITY NOT IMPLEMENTED',
                                        '66'  => 'CHANNEL TYPE NOT IMPLEMENTED',
                                        '69'  => 'REQUESTED FACILITY NOT IMPLEMENTED',
                                        '70'  => 'ONLY RESTRICTED DIGITAL INFO BEARER CAPABILITY AVAILABLE',
                                        '79'  => 'SERVICE OR OPTION NOT IMPLEMENTED UNSPECIFIED',
                                        '80'  => 'VALUE NOT CODED',
                                        '81'  => 'INVALID CALL REFERENCE',
                                        '82'  => 'CHANNEL DOES NOT EXIST',
                                        '83'  => 'SUSPENDED CALL NO IDENTITY',
                                        '84'  => 'CALL IDENTITY IN USE',
                                        '85'  => 'NO CALL SUSPENDED',
                                        '86'  => 'CALL IDENTITY CLEARED',
                                        '87'  => 'NOT MEMBER OF CUG',
                                        '88'  => 'INCOMPATIBLE DESTINATION',
                                        '90'  => 'NON-EXISTENT CUG',
                                        '91'  => 'INVALID NETWORK SELECTION',
                                        '95'  => 'INVALID MESSAGE UNSPECIFIED',
                                        '96'  => 'MANDATORY INFORMATION ELEMENT MISSING',
                                        '97'  => 'MESSAGE TYPE NON-EXISTENT OR NOT IMPLEMENTED',
                                        '98'  => 'MSG TYPE NC NE OR NI',
                                        '99'  => 'IE NOT IMPLEMENTED',
                                        '100' => 'INVALID INFORMATION ELEMENT CONTENT',
                                        '101' => 'MSG NOT COMPATIBLE WITH STATE',
                                        '102' => 'RECOVERY ON TIMER EXPIRY(The SBC may release a call with this cause value if the internal SBC Call Establishment Timer expires. This interval is 5 minutes.)',
                                        '103' => 'PARAMETER NOT IMPLEMENTED',
                                        '110' => 'UNRECOGNIZED PARAMETER',
                                        '111' => 'PROTOCOL ERROR UNSPECIFIED',
                                        '127' => 'INTERWORKING UNSPECIFIED',
                                        '128' => 'RESOURCES ALLOCATION FAILURE[This Sonus code is mapped to Q.931 ID 34.]',
                                        '129' => 'CHANNEL COLLISION BACKOFF[This Sonus code is mapped to Q.931 ID 31.]',
                                        '130' => 'INSUFFICIENT DESTINATION RESOURCES',
                                        '131' => 'RESOURCE FAILURE',
                                        '132' => 'MODULE FAILURE',
                                        '133' => 'MEDIA GATEWAY SERVICE GROUP EGRESS DISCONNECT',
                                        '134' => 'CONTINUITY CHECK FAILED, BACKOFF',
                                        '135' => 'COLLISION REATTEMPT EXHAUSTED',
                                        '136' => 'CONTINUITY REATTEMPT EXHAUSTED',
                                        '137' => 'NO ROUTE FOR BEARER CAPABILITY',
                                        '138' => 'NO ROUTE SIGNALING',
                                        '139' => 'NO ROUTE DIRECTION',
                                        '140' => 'CIRCUIT ENDPOINT RESOURCE ALLOCATION FAILURE',
                                        '141' => 'DISCONNECT WITH NEW DESTINATION',
                                        '142' => 'AUTOMATIC CONGESTION CONTROL PROCEDURE',
                                        '143' => 'ACC ALTERNATE ROUTE',
                                        '144' => 'PACKET LOSS EXCEEDS THRESHOLD',
                                        '145' => 'NO RTP OR RTCP PACKETS',
                                        '146' => 'HOP COUNTER EXHAUSTED',
                                        '147' => 'CONVERSION FAILURE',
                                        '148' => 'CPC_DISC_CONGESTION_REROUTE_NOT_PERMITTED',
                                        '149' => 'CPC_DISC_ SBC_INCOMPATIBLE_DESTINATION',
                                        '150' => 'CPC_DISC_NO_PSX_ROUTES',
                                        '151' => 'CPC_DISC_SETUP_REQ_TIMER_EXPIRY',
                                        '152' => 'CPC_DISC_GW_CONGESTED',
                                        '153' => 'CPC_DISC_GLARE_OCCURRED',
                                        '154' => 'CPC_DISC_NO_TERM_CIT_RCVD',
                                        '155' => 'CPC_DISC_BUSY_EVERYWHERE',
                                        '156' => 'CPC_DISC_REASON_UNKNOWN_RESOURCE_PRIORITY',
                                        '157' => 'CPC_DISC_REASON_BAD_EXTENSION',
                                        '158' => 'CPC_DISC_REASON_QUEUE_TIMEOUT',
                                        '159' => 'CPC_DISC_REASON_FORBIDDEN',
                                        '161' => 'CPC_DISC_REASON_QUEUEING_IMPOSSIBLE',
                                        '166' => 'CPC_DISC_PDD_TIMER_EXPIRED',
                                        '176' => 'CPC_DISC_TG_AUTH_FAIL',
                                        '177' => 'CPC_DISC_TG_AUTH_FAIL_401_407',

                                ];
		return  $termination_reason_codes;
	}

    public static function get_call_termination_code($reason_code_number)
    {
        $termination_reason_codes = Sonus5kCDR::get_call_termination_codes();
        return $termination_reason_codes[$reason_code_number];
    }
	
}
