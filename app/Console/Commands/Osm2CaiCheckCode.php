<?php

namespace App\Console\Commands;

use App\Providers\Osm2CaiHikingRoutesServiceProvider;
use Illuminate\Console\Command;

class Osm2CaiCheckCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:check_code {code : Insert zone part of REI code, like L or LPI or LPI or LPIO1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if the code is valid for a OSM2CAI zone';

    private $code;
    private $provider;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Osm2CaiHikingRoutesServiceProvider $provider)
    {
        $this->provider = $provider;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->code=strtoupper(trim($this->argument('code')));
        $this->info("Checking code $this->code");

        if(!$this->checkSyntax($this->code)) {
            $this->error('Invalid Syntax Valid codes example: L,LPI,LPIO,LPIO1');
            return 1;
        }
        $this->info('Code Syntax is OK');

        if(!$this->checkWithProvider($this->code,$this->provider)) {
            $this->error('Code has no corresponding zone in OSM2CAI HikingRoutes DB');
            return 2;
        }
        $this->info('Code Ok');
        return 0;
    }

    public function checkSyntax($code) {
        if(!in_array(strlen($code),[1,3,4,5])) {
            return false;
        }
        return true;
    }

    public function checkWithProvider($code,Osm2CaiHikingRoutesServiceProvider $provider) {
        if(!$this->checkSyntax($code)) return false;
        return $provider->checkCode($code);
    }


}
