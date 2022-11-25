<?php

namespace App\Console\Commands;

use App\Models\HikingRoute;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class updateHikingRoutesValidationDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:update-hikingroutes-validation-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It perform actions on all routes updating validation_date with last update when validation_date is null and sda has value 4';

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
        $hikingRoutes = HikingRoute::where('osm2cai_status',4)
            ->whereNull('validation_date')
            ->get();
            foreach ($hikingRoutes as $hr){
                try {
                    $this->info("Updating validation_date, Route ID:{$hr->id} REF:{$hr->ref}");
                    $hr->validation_date = Carbon::create($hr->update_at)->format('Y-m-d');
                    $hr->save();
                }
                catch( Exception|Throwable $e)
                {
                    $this->error($e->getMessage());
                    Log::error($e->getMessage());
                }
            }
    }
}
