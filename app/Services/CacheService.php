<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CacheService {

  public function getLastOsmSyncDate()
  {
    $date = Cache::get('osm2cai_sync_finished','24 ore fa');
    if ( $date instanceof Carbon )
    {
      $date = $date->locale('it_IT');
      $date = $date->setTimezone('Europe/Rome')->format('d/m/Y H:i:s');
    }
    return $date;
  }

  public function setOsmSyncDate()
  {
    Cache::forever('osm2cai_sync_finished', Carbon::now());
  }
}
