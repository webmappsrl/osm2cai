<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMountainGroupsRequest;
use App\Http\Requests\UpdateMountainGroupsRequest;
use App\Models\MountainGroups;

class MountainGroupsController extends Controller
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
     * @param  \App\Http\Requests\StoreMountainGroupsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMountainGroupsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MountainGroups  $mountainGroups
     * @return \Illuminate\Http\Response
     */
    public function show(MountainGroups $mountainGroups)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MountainGroups  $mountainGroups
     * @return \Illuminate\Http\Response
     */
    public function edit(MountainGroups $mountainGroups)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMountainGroupsRequest  $request
     * @param  \App\Models\MountainGroups  $mountainGroups
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMountainGroupsRequest $request, MountainGroups $mountainGroups)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MountainGroups  $mountainGroups
     * @return \Illuminate\Http\Response
     */
    public function destroy(MountainGroups $mountainGroups)
    {
        //
    }
}
