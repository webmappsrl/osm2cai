<?php

namespace App\Services;

use SimpleXMLElement;
use App\Models\HikingRoute;
use Illuminate\Support\Facades\Http;
use App\Providers\Osm2CaiHikingRoutesServiceProvider;
use Symfony\Component\String\Exception\RuntimeException;


class OsmService
{

  /**
   * CONSTRUCTOR METHODS
   */


  /**
   * Undocumented function
   *
   * @param Osm2CaiHikingRoutesServiceProvider $provider
   */
  public function __construct(Osm2CaiHikingRoutesServiceProvider $provider, Http $http)
  {
    $this->provider = $provider;
    $this->http = $http;
  }

  /**
   * Return an istance of this class
   *
   * @return \App\Services\OsmService
   */
  public static function getService()
  {
    return app(__CLASS__);
  }

  /**
   * Handle dynamic, static calls to the object.
   *
   * @param  string  $method
   * @param  array  $args
   * @return mixed
   *
   * @throws \RuntimeException
   */
  public static function __callStatic($method, $args)
  {
    $instance = static::getService();

    if (!$instance) {
      throw new RuntimeException('OsmService has not been set.');
    }

    return $instance->$method(...$args);
  }


  /**
   * SERVICES METHODS
   */

  public function getRelationApiFieldsKey()
  {
    return  [
      'ref', 'old_ref', 'source_ref', 'survey_date', 'name', 'rwn_name', 'ref_REI',
      'from', 'to', 'osmc_symbol', 'network', 'roundtrip', 'symbol', 'symbol_it',
      'ascent', 'descent', 'distance', 'duration_forward', 'duration_backward',
      'operator', 'state', 'description', 'description_it', 'website', 'wikimedia_commons',
      'maintenance', 'maintenance_it', 'note', 'note_it', 'note_project_page'
    ];
  }


  /**
   *
   * @param [type] $relationId
   * @return void
   */
  function hikingRouteExists($relationId)
  {
    return $this->http::head("https://www.openstreetmap.org/api/0.6/relation/" . intval($relationId))->ok();
  }

  //   function syncHikingRoute(HikingRoute $hrModel)
  //   {
  //     $route = $this->provider->syncHikingRoute($hrModel->relation_id);
  //   }

  /**
   * Return osm API data by relation id provided
   *
   * @param string|int $relationId
   * @return array
   */
  function getHikingRoute($relationId)
  {
    $return = false;
    $response = $this->http::get("https://www.openstreetmap.org/api/0.6/relation/" . intval($relationId));
    if ($response->ok()) {
      $allowedKeys = $this->getRelationApiFieldsKey();
      $xml = $response->body();
      $relation = (new SimpleXMLElement($xml))->relation;
      foreach ($relation->tag as $tag) {

        $key = str_replace(':', '_', (string) $tag['k']);
        if ( in_array( $key , $allowedKeys ) )
        {
          $return[$key.'_osm'] = (string) $tag['v'];
        }

      }
      $return['relation_id'] = $relationId;
    }
    return $return;
  }

  function getHikingRouteGeojson($relationId)
  {
    $return = false;
    $response = $this->http::get("https://hiking.waymarkedtrails.org/api/v1/details/relation/" . intval($relationId) . "/geometry/geojson");
    if ( $response->ok() )
    {
      $return = $response->body();
    }

    return $return;
  }

  function getHikingRouteGpx($relationId)
  {
    $return = false;
    $response = $this->http::get("https://hiking.waymarkedtrails.org/api/v1/details/relation/" . intval($relationId) . "/geometry/gpx");
    if ( $response->ok() )
    {
      $return = $response->body();
    }

    return $return;
  }

  function getHikingRouteGeometry($relationId)
  {

    $geojson = $this->getHikingRouteGpx($relationId);
    if ( $geojson )
    {
      $service = GeometryService::getService();
      $geometry = $service->textToGeojson($geojson);
      return $geometry;
    }
    return false;
  }
}
