<?php

namespace App\Console\Commands\CallManager;

use App\Cucmphoneconfigs;
use Illuminate\Console\Command;

class PhoneLineCSSReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:phonelinecssreport {site}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $SITE = $this->argument('site');

        echo $SITE.PHP_EOL;

        $count = Cucmphoneconfigs::where('devicepool', 'like', '%'.$SITE.'%')->count();

        echo $count;
        $phone_array = [];

        if ($count) {
            echo 'asdfi';
            $phone_array[] = Cucmphoneconfigs::where('devicepool', '=', '%'.$SITE.'%')->chunk(300, function ($phones) {
                print_r($phone_array);
                $return = [];
                $TYPE = 'Phone';
                foreach ($phones as $PHONE) {
                    print_r($PHONE);
                    $DATA = [];
                    $PHONE_CONFIG = $PHONE['config'];
                    $DP = explode('_', $PHONE['devicepool']);
                    $SITE = $DP[1];
                    if ($PHONE_CONFIG['callingSearchSpaceName']['_'] == 'CSS_KHONEOMA-EXEC-ADMIN') {
                        // #################################################################
                        // DO NOT UPDATE THE CSS ON THE EXEC ADMIN GRP
                        $DATA['name'] = $PHONE_CONFIG['name'];
                        $DATA['description'] = $PHONE_CONFIG['description'];
                        //$DATA['callingSearchSpaceName'] = "CSS_{$SITE}_DEVICE";
                        //$DATA['subscribeCallingSearchSpaceName'] = 'CSS_DEVICE_SUBSCRIBE';
                        //$DATA['automatedAlternateRoutingCssName'] = '';
                        $this->PHONEUPDATE_OBJECTS[$TYPE][] = $DATA;
                    // #################################################################
                    } else {
                        continue;
                    }

                    $LINES_CONFIG = $PHONE['lines'];

                    foreach ($LINES_CONFIG as $line) {
                        $DATA = [];
                        $UPDATE = false;
                        $CSS = $line['shareLineAppearanceCssName']['_'];
                        $ARRAY = explode('_', $CSS);

                        $DATA['pattern'] = $line['pattern'];
                        $DATA['description'] = $line['description'];
                        $DATA['routePartitionName'] = $line['routePartitionName']['_'];

                        if ($line['routePartitionName']['_'] != 'Global-All-Lines') {
                            $this->REVIEW_OBJECTS['Line'][] = $line;
                            continue;
                        }
                        //print_r($line['e164AltNum']['routePartition']['_']);
                        //print_r($line['e164AltNum']);

                        if ($line['e164AltNum']['routePartition']['_'] != 'Global-All-Lines') {
                            // Update
                            $DATA['e164AltNum'] = [
                                        'numMask'                     => "+1{$line['pattern']}",
                                        'isUrgent'                    => 'true',
                                        'addLocalRoutePartition'      => 'true',
                                        'routePartition'              => $DATA['routePartitionName'],
                                        'active'                      => 'true',
                                        'advertiseGloballyIls'        => 'true',
                                    ];
                            $UPDATE = true;
                        }
                        if ($CSS == 'CSS-Local-10') {
                            $DATA['shareLineAppearanceCssName'] = 'CSS_LINEONLY_L2_LOCAL';
                            $UPDATE = true;
                        } elseif (! in_array('LINEONLY', $ARRAY)) {
                            // Update
                            $DATA['shareLineAppearanceCssName'] = 'CSS_LINEONLY_L4_INTL';
                            $UPDATE = true;
                        }
                        print_r($line);
                        /*
                        if (
                            $line['callForwardAll']['callingSearchSpaceName']['_'] != 'CSS_LINE_CFWD_LD' ||
                            $line['callForwardBusy']['callingSearchSpaceName']['_'] != 'CSS_LINE_CFWD_LD' ||
                            $line['callForwardBusyInt']['callingSearchSpaceName']['_'] != 'CSS_LINE_CFWD_LD' ||
                            $line['callForwardNoAnswer']['callingSearchSpaceName']['_'] != 'CSS_LINE_CFWD_LD' ||
                            $line['callForwardNoAnswerInt']['callingSearchSpaceName']['_'] != 'CSS_LINE_CFWD_LD' ||
                            $line['callForwardNoCoverage']['callingSearchSpaceName']['_'] != 'CSS_LINE_CFWD_LD' ||
                            $line['callForwardNoCoverageInt']['callingSearchSpaceName']['_'] != 'CSS_LINE_CFWD_LD' ||
                            $line['callForwardOnFailure']['callingSearchSpaceName']['_'] != 'CSS_LINE_CFWD_LD' ||
                            //$line['callForwardAlternateParty']['callingSearchSpaceName']['_'] 	!= 'CSS_LINE_CFWD_LD' ||
                            $line['callForwardNotRegistered']['callingSearchSpaceName']['_'] != 'CSS_LINE_CFWD_LD' ||
                            $line['callForwardNotRegisteredInt']['callingSearchSpaceName']['_'] != 'CSS_LINE_CFWD_LD'
                        ) {
                            // Update
                            $DATA['callForwardAll']['callingSearchSpaceName'] = 'CSS_LINE_CFWD_LD';
                            $DATA['callForwardBusy']['callingSearchSpaceName'] = 'CSS_LINE_CFWD_LD';
                            $DATA['callForwardBusyInt']['callingSearchSpaceName'] = 'CSS_LINE_CFWD_LD';
                            $DATA['callForwardNoAnswer']['callingSearchSpaceName'] = 'CSS_LINE_CFWD_LD';
                            $DATA['callForwardNoAnswerInt']['callingSearchSpaceName'] = 'CSS_LINE_CFWD_LD';
                            $DATA['callForwardNoCoverage']['callingSearchSpaceName'] = 'CSS_LINE_CFWD_LD';
                            $DATA['callForwardNoCoverageInt']['callingSearchSpaceName'] = 'CSS_LINE_CFWD_LD';
                            $DATA['callForwardOnFailure']['callingSearchSpaceName'] = 'CSS_LINE_CFWD_LD';
                            //$DATA['callForwardAlternateParty']['callingSearchSpaceName']		= 'CSS_LINE_CFWD_LD';
                            $DATA['callForwardNotRegistered']['callingSearchSpaceName'] = 'CSS_LINE_CFWD_LD';
                            $DATA['callForwardNotRegisteredInt']['callingSearchSpaceName'] = 'CSS_LINE_CFWD_LD';
                            $UPDATE = true;
                        }

                        if ($UPDATE == true) {
                            $this->PHONEUPDATE_OBJECTS['Line'][$line['pattern']] = $DATA;
                        }
                        */
                    }
                }
            });
        }
    }
}
