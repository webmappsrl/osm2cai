<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItineraryRequest;
use App\Http\Requests\UpdateItineraryRequest;
use App\Models\Itinerary;
use Illuminate\Support\Facades\DB;

class ItineraryController extends Controller
{
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
     * @param  \App\Http\Requests\StoreItineraryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreItineraryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Itinerary  $itinerary
     * @return \Illuminate\Http\Response
     */
    public function show(Itinerary $itinerary)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Itinerary  $itinerary
     * @return \Illuminate\Http\Response
     */
    public function edit(Itinerary $itinerary)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateItineraryRequest  $request
     * @param  \App\Models\Itinerary  $itinerary
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateItineraryRequest $request, Itinerary $itinerary)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Itinerary  $itinerary
     * @return \Illuminate\Http\Response
     */
    public function destroy(Itinerary $itinerary)
    {
        //
    }



    /**
     * @OA\Get(
     *      path="/api/v2/itinerary/list",
     *      tags={"Api V2"},
     *      @OA\Response(
     *          response=200,
     *          description="Returns all the itinerary IDs and updated_at date. These ids can be used in the json API to retrieve the itinerary data.",
     *       @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="id",
     *                     description="Internal osm2cai Identifier",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="updated_at",
     *                     description="last update of itinerary",
     *                     type="date"
     *                 ),
     *                 example={1269:"2022-12-03 12:34:25",652:"2022-07-31 18:23:34",273:"2022-09-12 23:12:11"},
     *             )
     *         )
     *      ),
     *     )
     *
     */
    public function itineraryList()
    {
        $itinerary = collect(DB::select('SELECT id, updated_at FROM itineraries'));
        $data = $itinerary->pluck('updated_at', 'id')->toArray();

        return response()->json($data);
    }


    /**
     * @OA\Get(
     *      path="/api/v2/itinerary/{id}",
     *      tags={"Api V2"},
     *      @OA\Parameter(
     *          name="id",
     *          description="Itinerary ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Returns the itinerary data in JSON format.",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="type",
     *                      description="Type of the feature",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="properties",
     *                      description="Properties of the feature",
     *                      type="object",
     *                      @OA\Property(
     *                          property="id",
     *                          description="Internal osm2cai Identifier",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="name",
     *                          description="Name of the itinerary",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="stages",
     *                          description="Number of stages",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="total_km",
     *                          description="Total kilometers",
     *                          type="number"
     *                      ),
     *                      @OA\Property(
     *                          property="items",
     *                          description="Array of items",
     *                          type="array",
     *                          @OA\Items(
     *                              type="integer"
     *                          )
     *                      )
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function itineraryById($id)
    {
        $itinerary = Itinerary::find($id);

        return response()->json($itinerary->generateItineraryJson());
    }
}
