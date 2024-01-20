<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreSectionRequest;
use App\Http\Requests\UpdateSectionRequest;

class SectionController extends Controller
{

    public function geojson(string $id)
    {
        $section = Section::find($id);
        if ($section) {
            $hikingRoutes = $section->hikingRoutes()->get();
            $geojson = [
                'type' => 'FeatureCollection',
                'features' => [],
            ];

            if (count($hikingRoutes) > 0) {
                foreach ($hikingRoutes as $hr) {
                    $geometry = DB::select('SELECT ST_AsGeoJSON(ST_ForceRHR(geometry)) as geom FROM hiking_routes WHERE id = ?;', [$hr->id]);
                    $geometry = json_decode($geometry[0]->geom);
                    $name = $hr->name ? $hr->name . ' - ' .  $hr->ref : $hr->ref;
                    $regions = '';
                    $provinces = '';
                    $areas = '';
                    $sectors = '';
                    $sections = '';
                    if (count($hr->regions) > 0) {
                        $regions = $hr->regions->map(function ($region) {
                            return $region->name;
                        })->implode(', ');
                    }
                    if (count($hr->provinces) > 0) {
                        $provinces = $hr->provinces->map(function ($province) {
                            return $province->name;
                        })->implode(', ');
                    }
                    if (count($hr->areas) > 0) {
                        $areas = $hr->areas->map(function ($area) {
                            return $area->name;
                        })->implode(', ');
                    }
                    if (count($hr->sectors) > 0) {
                        $sectors = $hr->sectors->map(function ($sector) {
                            return $sector->name;
                        })->implode(', ');
                    }
                    if (count($hr->sections) > 0) {
                        $sections = $hr->sections->map(function ($section) {
                            return $section->name;
                        })->implode(', ');
                    }
                    $user = User::where('id', $hr->user_id)->first();
                    $userName = $user ? $user->name : '';

                    $geojson['features'][] = [
                        'type' => 'Feature',
                        'geometry' => $geometry,
                        'properties' => [
                            'id' => $hr->id,
                            'name' => $name,
                            'user' => $userName,
                            'relation_id' => $hr->relation_id,
                            'ref' => $hr->ref ?? '',
                            'source_ref' => $hr->source_ref ?? '',
                            'difficulty' => $hr->cai_scale ?? '',
                            'from' => $hr->from ?? '',
                            'to' => $hr->to ?? '',
                            'regions' => $regions,
                            'provinces' => $provinces,
                            'areas' => $areas,
                            'sector' => $hr->mainSector()->full_code ?? '',
                            'sections' => $sections,
                            'last_updated' => $hr->updated_at->format('Y-m-d'),
                        ]
                    ];
                }
            }

            $headers = [
                'Content-type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $id . '.geojson"',
            ];

            return response(json_encode($geojson), 200, $headers);
        }

        return response()->json(['Error' => 'Sector ' . $id . ' not found'], 404);
    }

    public function csv(string $id)
    {
        $section = Section::find($id);

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="osm2cai_' . date('Ymd') . '_settore_' . $section->name . '.csv"',
        ];

        return response($section->getCsv(), 200, $headers);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSectionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSectionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function show(Section $section)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function edit(Section $section)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSectionRequest  $request
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSectionRequest $request, Section $section)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function destroy(Section $section)
    {
        //
    }
}
