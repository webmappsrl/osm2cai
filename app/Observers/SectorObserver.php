<?php

namespace App\Observers;

use App\Models\Sector;

class SectorObserver
{
    /**
     * Handle the Sector "created" event.
     *
     * @param  \App\Models\Sector  $sector
     * @return void
     */
    public function created(Sector $sector)
    {
        //
    }

    /**
     * Handle the Sector "updated" event.
     *
     * @param  \App\Models\Sector  $sector
     * @return void
     */
    public function updated(Sector $sector)
    {
        if($sector->isDirty(['code','area_id'])){
            $sector->calculateFullCode();
        }
    }

    /**
     * Handle the Sector "deleted" event.
     *
     * @param  \App\Models\Sector  $sector
     * @return void
     */
    public function deleted(Sector $sector)
    {
        //
    }

    /**
     * Handle the Sector "restored" event.
     *
     * @param  \App\Models\Sector  $sector
     * @return void
     */
    public function restored(Sector $sector)
    {
        //
    }

    /**
     * Handle the Sector "force deleted" event.
     *
     * @param  \App\Models\Sector  $sector
     * @return void
     */
    public function forceDeleted(Sector $sector)
    {
        //
    }
}
