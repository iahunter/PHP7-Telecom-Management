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
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public $phones = <<<'END'

CENNEOMA	CIPC		CENNEOMA_CIPC	7942	4027349204	English	147369	N	
Travis 	Riesenberg		88908D730016	8841	4029384404	English			
	
END;

    public function handle()
    {
        $cucm->pastePhones($phones);
    }
}
