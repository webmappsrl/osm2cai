<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUgcMediaRequest;
use App\Http\Requests\UpdateUgcMediaRequest;
use App\Models\UgcMedia;

class UgcMediaController extends Controller
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
     * @param  \App\Http\Requests\StoreUgcMediaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUgcMediaRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UgcMedia  $ugcMedia
     * @return \Illuminate\Http\Response
     */
    public function show(UgcMedia $ugcMedia)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UgcMedia  $ugcMedia
     * @return \Illuminate\Http\Response
     */
    public function edit(UgcMedia $ugcMedia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUgcMediaRequest  $request
     * @param  \App\Models\UgcMedia  $ugcMedia
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUgcMediaRequest $request, UgcMedia $ugcMedia)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UgcMedia  $ugcMedia
     * @return \Illuminate\Http\Response
     */
    public function destroy(UgcMedia $ugcMedia)
    {
        //
    }
}
