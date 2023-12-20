<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUgcPoiRequest;
use App\Http\Requests\UpdateUgcPoiRequest;
use App\Models\UgcPoi;

class UgcPoiController extends Controller
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
     * @param  \App\Http\Requests\StoreUgcPoiRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUgcPoiRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UgcPoi  $ugcPoi
     * @return \Illuminate\Http\Response
     */
    public function show(UgcPoi $ugcPoi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UgcPoi  $ugcPoi
     * @return \Illuminate\Http\Response
     */
    public function edit(UgcPoi $ugcPoi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUgcPoiRequest  $request
     * @param  \App\Models\UgcPoi  $ugcPoi
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUgcPoiRequest $request, UgcPoi $ugcPoi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UgcPoi  $ugcPoi
     * @return \Illuminate\Http\Response
     */
    public function destroy(UgcPoi $ugcPoi)
    {
        //
    }
}
