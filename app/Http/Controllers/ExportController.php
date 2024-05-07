<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\User;
use App\Models\EcPoi;
use App\Models\Sector;
use App\Models\UgcPoi;
use App\Models\CaiHuts;
use App\Models\Section;
use App\Models\UgcMedia;
use App\Models\UgcTrack;
use App\Models\Itinerary;
use App\Models\HikingRoute;
use App\Models\NaturalSpring;
use App\Models\MountainGroups;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Http\Resources\AreaResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\EcPoiResource;
use App\Http\Resources\CaiHutResource;
use App\Http\Resources\SectorResource;
use App\Http\Resources\UgcPoiResource;
use App\Http\Resources\SectionResource;
use App\Http\Resources\UgcMediaResource;
use App\Http\Resources\UgcTrackResource;
use App\Http\Resources\ItineraryResource;
use App\Http\Resources\HikingRouteResource;
use App\Http\Resources\MountainGroupResource;
use App\Http\Resources\NaturalSpringResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * This controller handles the export of the data to OSM2CAI 2
 */
class ExportController extends Controller
{
    /**
     * Return a list of hiking routes with their updated_at date.
     *
     * @return \Illuminate\Http\JsonResponse<array<int, string>>
     */
    public function hikingRoutesList(): JsonResponse
    {
        /** @var Collection<int, HikingRoute> $hikingRoutes */
        $hikingRoutes = HikingRoute::all('id', 'updated_at');

        $data =  $hikingRoutes->mapWithKeys(function (HikingRoute $hikingRoute) {

            return [$hikingRoute->id => $hikingRoute->updated_at];
        });

        return response()->json($data);
    }

    /**
     * Return a single hiking route with its features.
     *
     * @param int $id the id of the hiking route
     *
     * @return \App\Http\Resources\HikingRouteResource
     */
    public function hikingRoutesSingleFeature(int $id): HikingRouteResource
    {
        /** @var HikingRoute $hikingRoute */
        $hikingRoute = HikingRoute::find($id);
        return new HikingRouteResource($hikingRoute);
    }

    /**
     * Return a list of users with their updated_at date.
     *
     * @return \Illuminate\Http\JsonResponse<array<int, string>>
     */
    public function UsersList(): JsonResponse
    {

        /** @var Collection<int, User> $users */
        $users = User::all('id', 'updated_at');

        $data =  $users->mapWithKeys(function (User $user) {

            return [$user->id => $user->updated_at];
        });

        return response()->json($data);
    }

    /**
     * Return a single user with its features.
     *
     * @param int $id the id of the user
     *
     * @return \App\Http\Resources\UserResource
     */
    public function UsersSingleFeature(int $id): UserResource
    {
        /** @var User $user */
        $user = User::find($id);
        return new UserResource($user);
    }

    /**
     * Return a list of UGC points of interest with their updated_at date.
     *
     * @return \Illuminate\Http\JsonResponse<array<int, string>>
     */
    public function UgcPoisList(): JsonResponse
    {

        /** @var Collection<int, UgcPoi> $ugcPois */
        $ugcPois = UgcPoi::all('id', 'updated_at');

        $data =  $ugcPois->mapWithKeys(function (UgcPoi $ugcPoi) {
            return [$ugcPoi->id => $ugcPoi->updated_at];
        });

        return response()->json($data);
    }

    /**
     * Return a single UGC point of interest with its features.
     *
     * @param int $id the id of the UGC poi
     *
     * @return \App\Http\Resources\UgcPoiResource
     */
    public function UgcPoisSingleFeature(int $id): UgcPoiResource
    {
        /** @var UgcPoi $ugcPoi */
        $ugcPoi = UgcPoi::find($id);
        return new UgcPoiResource($ugcPoi);
    }

    /**
     * Return a list of UGC tracks with their updated_at date.
     *
     * @return \Illuminate\Http\JsonResponse<array<int, string>>
     */
    public function UgcTracksList(): JsonResponse
    {

        /** @var Collection<int, UgcTrack> $tracks */
        $tracks = UgcTrack::all('id', 'updated_at');

        $data =  $tracks->mapWithKeys(function (UgcTrack $track) {
            return [$track->id => $track->updated_at];
        });

        return response()->json($data);
    }

    /**
     * Return a single UGC track with its features.
     *
     * @param int $id the id of the UGC track
     *
     * @return \App\Http\Resources\UgcTrackResource
     */
    public function UgcTracksSingleFeature(int $id): UgcTrackResource
    {
        /** @var UgcTrack $track */
        $track = UgcTrack::find($id);
        return new UgcTrackResource($track);
    }

    /**
     * Return a list of UGC media with their updated_at date.
     *
     * @return \Illuminate\Http\JsonResponse<array<int, string>>
     */
    public function UgcMediasList(): JsonResponse
    {

        /** @var Collection<int, UgcMedia> $media */
        $media = UgcMedia::all('id', 'updated_at');

        $data =  $media->mapWithKeys(function (UgcMedia $medium) {
            return [$medium->id => $medium->updated_at];
        });

        return response()->json($data);
    }

    /**
     * Return a single UGC media with its features.
     *
     * @param int $id the id of the UGC media
     *
     * @return \App\Http\Resources\UgcMediaResource
     */
    public function UgcMediasSingleFeature(int $id): UgcMediaResource
    {
        /** @var UgcMedia $medium */
        $medium = UgcMedia::find($id);
        return new UgcMediaResource($medium);
    }

    /**
     * Return a list of areas with their updated_at date.
     *
     * @return \Illuminate\Http\JsonResponse<array<int, string>>
     */
    public function AreasList(): JsonResponse
    {

        /** @var Collection<int, Area> $areas */
        $areas = Area::all('id', 'updated_at');

        $data =  $areas->mapWithKeys(function (Area $area) {

            return [$area->id => $area->updated_at];
        });

        return response()->json($data);
    }

    /**
     * Return a single area with its features.
     *
     * @param int $id the id of the area
     *
     * @return \App\Http\Resources\AreaResource
     */
    public function AreasSingleFeature(int $id): AreaResource
    {

        /** @var Area $area */
        $area = Area::find($id);

        return new AreaResource($area);
    }

    /**
     * Return a list of sectors with their updated_at date.
     *
     * @return \Illuminate\Http\JsonResponse<array<int, string>>
     */
    public function SectorsList(): JsonResponse
    {

        /** @var Collection<int, Sector> $sectors */
        $sectors = Sector::all('id', 'updated_at');

        $data =  $sectors->mapWithKeys(function (Sector $sector) {

            return [$sector->id => $sector->updated_at];
        });

        return response()->json($data);
    }


    /**
     * Return a single sector with its features.
     *
     * @param int $id the id of the sector
     *
     * @return \App\Http\Resources\SectorResource
     */
    public function SectorsSingleFeature(int $id): SectorResource
    {

        /** @var Sector $sector */
        $sector = Sector::find($id);

        return new SectorResource($sector);
    }

    /**
     * Return a list of sections with their updated_at date.
     *
     * @return \Illuminate\Http\JsonResponse<array<int, string>>
     */
    public function SectionsList(): JsonResponse
    {

        /** @var Collection<int, Sector> $sectors */
        $sections = Section::all('id', 'updated_at');

        $data =  $sections->mapWithKeys(function (Section $section) {

            return [$section->id => $section->updated_at];
        });

        return response()->json($data);
    }

    /**
     * Return a single section with its features.
     *
     * @param int $id the id of the section
     *
     * @return \App\Http\Resources\SectionResource
     */
    public function SectionsSingleFeature(int $id): SectionResource
    {

        /** @var Section $section */
        $section = Section::find($id);

        return new SectionResource($section);
    }

    /**
     * Return a list of mountain groups with their updated_at date.
     *
     * @return \Illuminate\Http\JsonResponse<array<int, string>>
     */
    public function MountainGroupsList(): JsonResponse
    {

        /** @var Collection<int, MountainGroups> $mountainGroups */
        $mountainGroups = MountainGroups::all('id', 'updated_at');

        $data =  $mountainGroups->mapWithKeys(function (MountainGroups $mountainGroup) {

            return [$mountainGroup->id => $mountainGroup->updated_at];
        });

        return response()->json($data);
    }

    /**
     * Return a single mountain group with its features.
     *
     * @param int $id the id of the mountain group
     *
     * @return \App\Http\Resources\MountainGroupResource
     */
    public function MountainGroupsSingleFeature(int $id): MountainGroupResource
    {

        /** @var MountainGroups $mountainGroup */
        $mountainGroup = MountainGroups::find($id);

        return new MountainGroupResource($mountainGroup);
    }

    /**
     * Return a list of natural springs with their updated_at date.
     *
     * @return \Illuminate\Http\JsonResponse<array<int, string>>
     */
    public function NaturalSpringsList(): JsonResponse
    {

        /** @var Collection<int, NaturalSpring> $naturalSprings */
        $naturalSprings = NaturalSpring::all('id', 'updated_at');

        $data =  $naturalSprings->mapWithKeys(function (NaturalSpring $naturalSpring) {

            return [$naturalSpring->id => $naturalSpring->updated_at];
        });

        return response()->json($data);
    }

    /**
     * Return a single natural spring with its features.
     *
     * @param int $id the id of the natural spring
     *
     * @return \App\Http\Resources\NaturalSpringResource
     */
    public function NaturalSpringsSingleFeature(int $id): NaturalSpringResource
    {

        /** @var NaturalSpring $naturalSpring */
        $naturalSpring = NaturalSpring::find($id);

        return new NaturalSpringResource($naturalSpring);
    }

    /**
     * Return a list of itineraries with their updated_at date.
     *
     * @return \Illuminate\Http\JsonResponse<array<int, string>>
     */
    public function ItinerariesList(): JsonResponse

    {

        /** @var Collection<int, Itinerary> $itineraries */
        $itineraries = Itinerary::all('id', 'updated_at');

        $data =  $itineraries->mapWithKeys(function (Itinerary $itinerary) {

            return [$itinerary->id => $itinerary->updated_at];
        });

        return response()->json($data);
    }

    /**
     * Return a single itinerary with its features.
     *
     * @param int $id the id of the itinerary
     *
     * @return \App\Http\Resources\ItineraryResource
     */
    public function ItinerariesSingleFeature(int $id): ItineraryResource
    {

        /** @var Itinerary $itinerary */
        $itinerary = Itinerary::find($id);

        return new ItineraryResource($itinerary);
    }

    /**
     * Return a list of Cai Huts with their updated_at date.
     *
     * @return \Illuminate\Http\JsonResponse<array<int, string>>
     */
    public function HutsList(): JsonResponse
    {

        /** @var Collection<int, CaiHuts> $caiHuts */
        $caiHuts = CaiHuts::all('id', 'updated_at');

        $data =  $caiHuts->mapWithKeys(function (CaiHuts $caiHut) {

            return [$caiHut->id => $caiHut->updated_at];
        });

        return response()->json($data);
    }

    /**
     * Return a single Cai Hut with its features.
     *
     * @param int $id the id of the Cai Hut
     *
     * @return \App\Http\Resources\CaiHutResource
     */
    public function HutsSingleFeature(int $id): CaiHutResource
    {

        /** @var CaiHuts $caiHut */
        $caiHut = CaiHuts::find($id);

        return new CaiHutResource($caiHut);
    }

    /**
     * Return a list of EC POIs with their updated_at date.
     *
     * @return \Illuminate\Http\JsonResponse<array<int, string>>
     */
    public function EcPoisList(): JsonResponse
    {

        /** @var Collection<int, EcPoi> $ecPois */
        $ecPois = EcPoi::all('id', 'updated_at');

        $data =  $ecPois->mapWithKeys(function (EcPoi $ecPoi) {

            return [$ecPoi->id => $ecPoi->updated_at];
        });

        return response()->json($data);
    }

    /**
     * Return a single EC POI with its features.
     *
     * @param int $id the id of the EC POI
     *
     * @return \App\Http\Resources\EcPoiResource
     */
    public function EcPoisSingleFeature(int $id): EcPoiResource
    {

        /** @var EcPoi $ecPoi */
        $ecPoi = EcPoi::find($id);

        if (is_null($ecPoi)) {
            abort(404);
        }

        return new EcPoiResource($ecPoi);
    }

    /**
     * Return a single EC POI with its features passing osmfeatures id
     * 
     * @param string $osmfeaturesId
     * 
     * @return \App\Http\Resources\EcPoiResource
     */
    public function ecPoisSingleFeatureByOsmfeaturesId(string $osmfeaturesId): EcPoiResource
    {
        $osmtype = substr($osmfeaturesId, 0, 1);
        $osmId = substr($osmfeaturesId, 1);

        $ecPoi = EcPoi::where('osm_type', $osmtype)->where('osm_id', $osmId)->first();

        if (is_null($ecPoi)) {
            abort(404);
        }

        return new EcPoiResource($ecPoi);
    }
}
