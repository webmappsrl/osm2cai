<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;



class OsmService {

  function hikingRouteExists( $relationId )
  {
    return Http::head( 'https://www.openstreetmap.org/api/0.6/relation/' . intval($relationId) )->ok();
  }

}
