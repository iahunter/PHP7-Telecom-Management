<?php

namespace App\Console\Commands\OnCall;

use App\Cucmclass;
use Illuminate\Console\Command;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class OnCallPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bouncer:add_oncall_permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add Permissions for specific Lines for Oncall Groups';

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
        // Add Bower Permissions for OnCall Groups based on the json array inside the environment variable.
        $numbers = json_decode(env('oncall_numbers'), true);
        print_r($numbers);

        foreach ($numbers as $number => $group) {
            echo 'starting'.PHP_EOL;
            $a = new \App\Http\Controllers\CucmLine();

            echo $number.PHP_EOL;
            $line = $a->cucm->get_object_type_by_pattern_and_partition($number, 'Global-All-Lines', 'Line');
            $line_instance = new Cucmclass();
            $line_instance->uuid = $line['uuid'];
            echo $line_instance->uuid;
            echo PHP_EOL;
            $line_instance->exists = true;
            $line_instance->getKey();
            echo $group.PHP_EOL;
            \Bouncer::allow($group)->to('read', $line_instance);
            \Bouncer::allow($group)->to('update', $line_instance);
            echo 'done'.PHP_EOL;
        }
    }
}
