<?php

namespace App\Services;

use App\Models\Area;
use Illuminate\Support\Facades\DB;

class AreaModelService {


  /**
   * Compute area geometry by its sectors
   *
   * @param Area $area
   * @return geometry
   */
  function computeGeometryBySectors( Area $area )
  {
    $sectorIds = $area->children->pluck('id')->all();

    $geom = DB::table('sectors')
      ->selectRaw('ST_Union(ST_force2d(geometry)) geometry')
      ->whereIn('id',$sectorIds)
      ->first();

    return $geom->geometry;
  }

  function computeAndSaveGeometryBySectors( Area $area )
  {
    try
    {
      $area->geometry = $this->computeGeometryBySectors($area);
      $area->save();
    }
    catch( Exception | Throwable $e )
    {
      throw $e;
    }

  }
}
