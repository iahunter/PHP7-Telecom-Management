<?php

namespace App\Console\Commands\Microsoft;

use Illuminate\Console\Command;

class MicrosoftTeamsUserReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microsoft:get_teams_user_report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Users on Teams';

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
