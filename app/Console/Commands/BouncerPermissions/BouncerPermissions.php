<?php

namespace App\Console\Commands\BouncerPermissions;

use Illuminate\Console\Command;
use Bouncer;
use App;

class BouncerPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bouncer:admin_permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'bouncer assign permissions';

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
		print "Starting Assigning Permissions to ".env('ADMIN_GRP').PHP_EOL;
		// Assign Network Engineer to Admin.
        $group = env('ADMIN_GRP');
        $tasks = [
            'create',
            'read',
            'update',
            'delete',
        ];

        $types = [
            App\Didblock::class,
            App\Did::class,
            App\Site::class,
            App\Phone::class,
            App\Phoneplan::class,
            App\Sonus5k::class,
            App\Sonus5kCDR::class,
            App\Cupi::class,
            App\Cucmclass::class,
            App\Calls::class,
            App\Cucmsiteconfigs::class,
            App\Cucmphoneconfigs::class,
			App\TelecomInfrastructure::class,
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }
		
		print "Finished Assigning Permissions".PHP_EOL;
    }
}
