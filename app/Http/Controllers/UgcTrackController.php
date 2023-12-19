<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUgcTrackRequest;
use App\Http\Requests\UpdateUgcTrackRequest;
use App\Models\UgcTrack;

class UgcTrackController extends Controller
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
     * @param  \App\Http\Requests\StoreUgcTrackRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUgcTrackRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UgcTrack  $ugcTrack
     * @return \Illuminate\Http\Response
     */
    public function show(UgcTrack $ugcTrack)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UgcTrack  $ugcTrack
     * @return \Illuminate\Http\Response
     */
    public function edit(UgcTrack $ugcTrack)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUgcTrackRequest  $request
     * @param  \App\Models\UgcTrack  $ugcTrack
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUgcTrackRequest $request, UgcTrack $ugcTrack)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UgcTrack  $ugcTrack
     * @return \Illuminate\Http\Response
     */
    public function destroy(UgcTrack $ugcTrack)
    {
        //
    }
}
