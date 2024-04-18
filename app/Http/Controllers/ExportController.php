<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\User;
use App\Models\EcPoi;
use App\Models\Sector;
use App\Models\UgcPoi;
use App\Models\Section;
use App\Models\UgcMedia;
use App\Models\UgcTrack;
use App\Models\Itinerary;
use App\Models\HikingRoute;
use Illuminate\Http\Request;
use App\Models\NaturalSpring;
use App\Models\MountainGroups;
use App\Http\Resources\AreaResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\EcPoiResource;
use App\Http\Resources\SectorResource;
use App\Http\Resources\UgcPoiResource;
use App\Http\Resources\SectionResource;
use App\Http\Resources\UgcMediaResource;
use App\Http\Resources\UgcTrackResource;
use App\Http\Resources\ItineraryResource;
use App\Http\Resources\HikingRouteResource;
use App\Http\Resources\MountainGroupResource;
use App\Http\Resources\NaturalSpringResource;
use App\Http\Resources\HikingRouteResourceCollection;

class ExportController extends Controller
{
    public function hikingRoutesList()
    {
        $hikingRoutes = HikingRoute::all('id', 'updated_at');

        $data =  $hikingRoutes->mapWithKeys(function ($hikingRoute) {

            return [$hikingRoute->id => $hikingRoute->updated_at];
        });

        return response()->json($data);
    }

    public function hikingRoutesSingleFeature($id)
    {
        $hikingRoute = HikingRoute::find($id);
        return new HikingRouteResource($hikingRoute);
    }

    public function UsersList()
    {

        return response()->json(User::all('id', 'updated_at')->mapWithKeys(function ($user) {

            return [$user->id => $user->updated_at];
        }));
    }

    public function UsersSingleFeature($id)
    {
        return new UserResource(User::find($id));
    }

    public function UgcPoisList()
    {
        return response()->json(UgcPoi::all('id', 'updated_at')->mapWithKeys(function ($ugcPoi) {
            return [$ugcPoi->id => $ugcPoi->updated_at];
        }));
    }

    public function UgcPoisSingleFeature($id)
    {
        return new UgcPoiResource(UgcPoi::find($id));
    }

    public function UgcTracksList()
    {
        return response()->json(UgcTrack::all('id', 'updated_at')->mapWithKeys(function ($track) {
            return [$track->id => $track->updated_at];
        }));
    }

    public function UgcTracksSingleFeature($id)
    {
        return new UgcTrackResource(UgcTrack::find($id));
    }

    public function UgcMediasList()
    {
        return response()->json(UgcMedia::all('id', 'updated_at')->mapWithKeys(function ($media) {
            return [$media->id => $media->updated_at];
        }));
    }

    public function UgcMediasSingleFeature($id)
    {
        return new UgcMediaResource(UgcMedia::find($id));
    }

    public function AreasList()
    {

        return response()->json(Area::all('id', 'updated_at')->mapWithKeys(function ($area) {

            return [$area->id => $area->updated_at];
        }));
    }

    public function AreasSingleFeature($id)
    {

        return new AreaResource(Area::find($id));
    }

    public function SectorsList()
    {

        return response()->json(Sector::all('id', 'updated_at')->mapWithKeys(function ($sector) {
            return [$sector->id => $sector->updated_at];
        }));
    }

    public function SectorsSingleFeature($id)
    {

        return new SectorResource(Sector::find($id));
    }

    public function SectionsList()
    {

        return response()->json(Section::all('id', 'updated_at')->mapWithKeys(function ($section) {
            return [$section->id => $section->updated_at];
        }));
    }

    public function SectionsSingleFeature($id)
    {

        return new SectionResource(Section::find($id));
    }

    public function ItinerariesList()
    {
        return response()->json(Itinerary::all('id', 'updated_at')->mapWithKeys(function ($itinerary) {
            return [$itinerary->id => $itinerary->updated_at];
        }));
    }

    public function ItinerariesSingleFeature($id)
    {
        return new ItineraryResource(Itinerary::find($id));
    }

    public function EcPoisList()
    {
        return response()->json(EcPoi::all('id', 'updated_at')->mapWithKeys(function ($poi) {
            return [$poi->id => $poi->updated_at];
        }));
    }

    public function EcPoisSingleFeature($id)
    {
        return new EcPoiResource(EcPoi::find($id));
    }

    public function MountainGroupsList()
    {
        return response()->json(MountainGroups::all('id', 'updated_at')->mapWithKeys(function ($mountainGroup) {
            return [$mountainGroup->id => $mountainGroup->updated_at];
        }));
    }

    public function MountainGroupsSingleFeature($id)
    {
        return new MountainGroupResource(MountainGroups::find($id));
    }

    public function NaturalSpringList()
    {
        return response()->json(NaturalSpring::all('id', 'updated_at')->mapWithKeys(function ($naturalSpring) {
            return [$naturalSpring->id => $naturalSpring->updated_at];
        }));
    }

    public function NaturalSpringSingleFeature($id)
    {
        return new NaturalSpringResource(NaturalSpring::find($id));
    }
}