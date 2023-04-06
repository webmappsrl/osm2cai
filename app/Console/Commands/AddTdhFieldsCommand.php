<?php

namespace App\Console\Commands;

use App\Models\HikingRoute;
use Illuminate\Console\Command;

class AddTdhFieldsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:tdh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compute and add TDH fields to sda4 hiking routes';

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
     * @return int
     */
    public function handle()
    {
        $hrs = HikingRoute::where('osm2cai_status',4)->get();
        $tot = $hrs->count();
        $count = 1;
        foreach ($hrs as $hr) {
            $this->info("($count/$tot) Processing Hiking route $hr->id ");
            $hr->tdh=$hr->computeTdh();
            $hr->save();
            $count++;
        }
    }
}
