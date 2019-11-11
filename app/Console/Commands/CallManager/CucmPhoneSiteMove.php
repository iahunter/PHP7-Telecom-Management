<?php

namespace App\Console\Commands\CallManager;

use App\Cucmclass;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class CucmPhoneSiteMove extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:phone_move_to {site}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move CUCM Phones to New Site - Pass sitecode in after command';

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
        $site = $this->argument('site');

        echo 'Moving Phones to: '.$site.PHP_EOL;

        /*
            Examples:
            SEP0004ABCDEEEE
            SEP0004ABCDEEEF
        */

        $data = file_get_contents(storage_path('cucm/working/working_file'));
        $phones = explode(PHP_EOL, $data);

        $phones = array_filter($phones);

        foreach ($phones as $phone) {
            $phone = trim($phone);

            try {
                $result = Cucmclass::updatePhoneSite($phone, $site);
                print_r($result);
            } catch (\Exception $E) {
                echo "{$E->getMessage()}".PHP_EOL;
            }
        }
    }
}
