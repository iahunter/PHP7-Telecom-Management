<?php

namespace App\Console\Commands\CallManager;

use Illuminate\Console\Command;

class Site911Local extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:site911Local';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build All Site 911 Local Partiions, Route Lists, Route Patterns, and Clear out old 911! and 9911! patterns from Site Partitions ';

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
        //
    }
}
