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
    protected $signature = 'osm2cai:tdh {id?}';

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
        ini_set('memory_limit', '-1');
        if ($this->argument('id')) {
            $hrs = HikingRoute::where('id', $this->argument('id'))->get();
        } else {
            $hrs = HikingRoute::whereIn('osm2cai_status', [3, 4])->get();
        }

        if (!$hrs) {
            $this->info("No Hiking Routes found");
            return 0;
        }
        $tot = $hrs->count();
        $count = 1;
        foreach ($hrs as $hr) {
            $this->info("($count/$tot) Processing Hiking route $hr->id ");
            $hr->tdh = $hr->computeTdh();
            $hr->save();
            $count++;
        }
    }
}