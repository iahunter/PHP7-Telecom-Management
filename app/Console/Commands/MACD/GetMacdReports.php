<?php

namespace App\Console\Commands\MACD;

use App\PhoneMACD;
use Illuminate\Console\Command;

class GetMacdReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'macd:macd_reports_to_cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gather MACD Reports and Cache the output.';

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
        echo 'Hi'.PHP_EOL;
    }
}
