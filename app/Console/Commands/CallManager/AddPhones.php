<?php

namespace App\Console\Commands\CallManager;

use App\Http\Controllers\Cucm;
use Illuminate\Console\Command;
use App\Http\Controllers\Cucmphone;

class AddPhones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:add-phones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds phones from phones.txt ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->cucmphone = new Cucmphone();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public $site = 'CENCONER';
    public $extlength = 4;

    public function handle()
    {
        // Include the phones.php $phones variable to import phones.
        require __DIR__.'/Imports/Phones.txt';
        //print $phones;

        $phones = $this->cucmphone->phones_string_to_array($phones);

        $PHONE = [];
        foreach ($phones as $phone) {
            //$phone['sitecode'] = $this->site;
            $phone['sitecode'] = $site;

            //$phone['extlength'] = $this->extlength;
            $phone['extlength'] = $extlength;
            $PHONES[] = $phone;
        }
        //print_r($PHONES);

        $ARRAY = [];
        foreach ($PHONES as $PHONE) {
            echo 'Adding Phone...'.PHP_EOL;
            print_r($PHONE);
            $REQUEST = $this->cucmphone->createPhone(new \Illuminate\Http\Request($PHONE));
            print_r($REQUEST);
            $ARRAY[] = $REQUEST;
        }
    }
}
