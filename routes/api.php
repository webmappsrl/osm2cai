<?php

use App\Models\HikingRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EcPoiController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\ItineraryController;
use App\Http\Resources\HikingRouteTDHResource;
use App\Http\Controllers\HikingRouteController;
use App\Http\Controllers\V2\MiturAbruzzoController;
use App\Http\Controllers\V1\HikingRoutesRegionControllerV1;
use App\Http\Controllers\V2\HikingRoutesRegionControllerV2;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::name('api.')->group(function () {
    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('csv')->name('csv.')->group(function () {
        Route::get('/region/{id}', [RegionController::class, 'csv'])->name('region');
        Route::get('/sector/{id}', [SectorController::class, 'csv'])->name('sector');
        Route::get('/area/{id}', [AreaController::class, 'csv'])->name('area');
        Route::get('/province/{id}', [ProvinceController::class, 'csv'])->name('province');
        Route::get('/section/{id}', [SectionController::class, 'csv'])->name('section');
        Route::get('/users/', [UserController::class, 'csv'])->name('users');
    });
    Route::prefix('geojson/complete')->name('geojson_complete.')->group(function () {
        Route::get('/region/{id}', [RegionController::class, 'geojsonComplete'])->name('region');
    });
    Route::prefix('geojson')->name('geojson.')->group(function () {
        Route::get('/region/{id}', [RegionController::class, 'geojson'])->name('region');
        Route::get('/province/{id}', [ProvinceController::class, 'geojson'])->name('province');
        Route::get('/area/{id}', [AreaController::class, 'geojson'])->name('area');
        Route::get('/sector/{id}', [SectorController::class, 'geojson'])->name('sector');
        Route::get('/section/{id}', [SectionController::class, 'geojson'])->name('section');
        Route::post('/hiking_routes/bounding_box', [HikingRouteController::class, 'boundingBox'])->name('hiking_routes');
    });
    Route::prefix('shapefile')->name('shapefile.')->group(function () {
        Route::get('/region/{id}', [RegionController::class, 'shapefile'])->name('region');
        Route::get('/province/{id}', [ProvinceController::class, 'shapefile'])->name('province');
        Route::get('/area/{id}', [AreaController::class, 'shapefile'])->name('area');
        Route::get('/sector/{id}', [SectorController::class, 'shapefile'])->name('sector');
    });
    Route::prefix('hiking-routes-shapefile')->name('hiking-routes-shapefile.')->group(function () {
        Route::get('/region/{id}', [RegionController::class, 'hikingRouteShapefile'])->name('region');
    });
    // API KML: /api/kml/region/{id}
    Route::prefix('kml')->name('kml.')->group(function () {
        Route::get('/region/{id}', [RegionController::class, 'kml'])->name('region');
        Route::get('/province/{id}', [ProvinceController::class, 'kml'])->name('province');
        Route::get('/area/{id}', [AreaController::class, 'kml'])->name('area');
        Route::get('/sector/{id}', [SectorController::class, 'kml'])->name('sector');
    });

    Route::prefix('v1')->name('v1')->group(function () {
        Route::get('/hiking-routes/region/{regione_code}/{sda}', [HikingRoutesRegionControllerV1::class, 'hikingroutelist'])->name('hr-ids-by-region');
        Route::get('/hiking-routes-osm/region/{regione_code}/{sda}', [HikingRoutesRegionControllerV1::class, 'hikingrouteosmlist'])->name('hr_osmids_by_region');
        Route::get('/hiking-route/{id}', [HikingRoutesRegionControllerV1::class, 'hikingroutebyid'])->name('hr_by_id');
        Route::get('/hiking-route-osm/{id}', [HikingRoutesRegionControllerV1::class, 'hikingroutebyosmid'])->name('hr_by_osmid');
        Route::get('/hiking-routes/bb/{bounding_box}/{sda}', [HikingRoutesRegionControllerV1::class, 'hikingroutelist_bb'])->name('hr-ids-by-bb');
        Route::get('/hiking-routes-osm/bb/{bounding_box}/{sda}', [HikingRoutesRegionControllerV1::class, 'hikingrouteosmlist_bb'])->name('hr-osmids-by-bb');
        Route::get('/hiking-routes-collection/bb/{bounding_box}/{sda}', [HikingRoutesRegionControllerV1::class, 'hikingroutelist_collection'])->name('hr-collection-by-bb');
    });


    Route::prefix('v2')->name('v2')->group(function () {
        Route::get('/hiking-routes/list', [HikingRoutesRegionControllerV2::class, 'hikingRoutesAllList'])->name('v2-hr-list');
        Route::get('/hiking-routes/region/{regione_code}/{sda}', [HikingRoutesRegionControllerV2::class, 'hikingroutelist'])->name('v2-hr-ids-by-region');
        Route::get('/hiking-routes-osm/region/{regione_code}/{sda}', [HikingRoutesRegionControllerV2::class, 'hikingrouteosmlist'])->name('v2-hr_osmids_by_region');
        Route::get('/hiking-route/{id}', [HikingRoutesRegionControllerV2::class, 'hikingroutebyid'])->name('v2-hr_by_id');
        Route::get('/hiking-route/euma/{id}', [HikingRoutesRegionControllerV2::class, 'hikingroutebyideuma'])->name('v2-hr_by_id_euma');
        Route::get('/hiking-route-tdh/{id}', function (string $id) {
            return new HikingRouteTDHResource(HikingRoute::findOrFail($id));
        })->name('v2-hr_thd_by_id');
        Route::get('/hiking-route-osm/{id}', [HikingRoutesRegionControllerV2::class, 'hikingroutebyosmid'])->name('v2-hr_by_osmid');
        Route::get('/hiking-routes/bb/{bounding_box}/{sda}', [HikingRoutesRegionControllerV2::class, 'hikingroutelist_bb'])->name('v2-hr-ids-by-bb');
        Route::get('/hiking-routes-osm/bb/{bounding_box}/{sda}', [HikingRoutesRegionControllerV2::class, 'hikingrouteosmlist_bb'])->name('v2-hr-osmids-by-bb');
        Route::get('/hiking-routes-collection/bb/{bounding_box}/{sda}', [HikingRoutesRegionControllerV2::class, 'hikingroutelist_collection'])->name('v2-hr-collection-by-bb');
        Route::get('/itinerary/list', [ItineraryController::class, 'itineraryList'])->name('v2-itinerary-list');
        Route::get('/itinerary/{id}', [ItineraryController::class, 'itineraryById'])->name('v2-itinerary-id');
        Route::get('/ecpois/bb/{bounding_box}/{type}', [EcPoiController::class, 'ecPoisBBox'])->name('v2-ecpois-by-bb');
        Route::get('/ecpois/{hr_osm2cai_id}/{type}', [EcPoiController::class, 'ecPoisByOsm2CaiId'])->name('v2-ecpois-by-osm2caiId');
        Route::get('/ecpois/{hr_osm_id}/{type}', [EcPoiController::class, 'ecPoisByOsmId'])->name('v2-ecpois-by-OsmId');

        //mitur_abruzzo
        Route::prefix('mitur_abruzzo')->name('v2-mitur-abruzzo')->group(function () {
            Route::get('/region_list', [MiturAbruzzoController::class, 'miturAbruzzoRegionList'])->name('region-list');
            Route::get('/region/{id}', [MiturAbruzzoController::class, 'miturAbruzzoRegionById'])->name('region-by-id');
            Route::get('/mountain_group/{id}', [MiturAbruzzoController::class, 'miturAbruzzoMountainGroupById'])->name('mountain-group-by-id');
            Route::get('/hiking_route/{id}', [MiturAbruzzoController::class, 'miturAbruzzoHikingRouteById'])->name('hiking-route-by-id');
            Route::get('/hut/{id}', [MiturAbruzzoController::class, 'miturAbruzzoHutById'])->name('hut-by-id');
            Route::get('/poi/{id}', [MiturAbruzzoController::class, 'miturAbruzzoPoiById'])->name('poi-by-id');
            Route::get('/section/{id}', [MiturAbruzzoController::class, 'miturAbruzzoSectionById'])->name('section-by-id');
        });

        //Export
        Route::prefix('export')->name('export')->group(function () {
            Route::get('/hiking-routes/list', [ExportController::class, 'hikingRoutesList'])->name('hiking-routes-export');
            Route::get('/hiking-routes/{id}', [ExportController::class, 'hikingRoutesSingleFeature'])->name('hiking-routes-single-feature-export');
            Route::get('/users/list', [ExportController::class, 'usersList'])->name('users-export');
            Route::get('/users/{id}', [ExportController::class, 'usersSingleFeature'])->name('users-single-feature-export');
            Route::get('/ugc_pois/list', [ExportController::class, 'ugcPoisList'])->name('ugc-pois-export');
            Route::get('/ugc_pois/{id}', [ExportController::class, 'ugcPoisSingleFeature'])->name('ugc-pois-single-feature-export');
            Route::get('/ugc_tracks/list', [ExportController::class, 'ugcTracksList'])->name('ugc-tracks-export');
            Route::get('/ugc_tracks/{id}', [ExportController::class, 'ugcTracksSingleFeature'])->name('ugc-tracks-single-feature-export');
            Route::get('/ugc_media/list', [ExportController::class, 'ugcMediasList'])->name('ugc-medias-export');
            Route::get('/ugc_media/{id}', [ExportController::class, 'ugcMediasSingleFeature'])->name('ugc-medias-single-feature-export');
            Route::get('/areas/list', [ExportController::class, 'areasList'])->name('areas-export');
            Route::get('/areas/{id}', [ExportController::class, 'areasSingleFeature'])->name('areas-single-feature-export');
        });
    });
});