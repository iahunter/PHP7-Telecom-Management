<?php

namespace App\Console\Commands\Numbers;

use Illuminate\Console\Command;

class NumberSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'numbers:scan-numbers-usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan Number Database for Numbers';

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
        if (! file_exists(storage_path('numbers/numberscan')) || ! is_readable(storage_path('numbers/numberscan'))) {
            return 'FILE IS NOT BEING LOADED FROM: '.$location;
        }

        $data = file_get_contents(storage_path('numbers/numberscan'));

        $numbers = explode(PHP_EOL, $data);

        print_r($numbers);
        die();

        $numbers = storage_path('numbers/numberscan');

        if (! is_array($numbers)) {
            //return "true";
            $numbers = explode(',', $numbers);
        }

        //return $numbers;
        //die();
        $dids = [];
        foreach ($numbers as $number_search) {
            if ($number_search == '') {
                unset($number_search);
                continue;
            }

            // Search for DID by numberCheck if there are any matches.
            if (! Did::where([['number', 'like', $number_search.'%']])->count()) {
                $did = [$number_search => false];
            } else {
                // Search for numbers like search.
                $did = Did::where('number', 'like', $number_search)->get();
                if ($did != '') {
                    $did = [$number_search => $did];
                }
            }
            print_r($did);
            $dids[] = $did;
        }
    }
}
