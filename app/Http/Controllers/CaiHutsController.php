<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaiHutsRequest;
use App\Http\Requests\UpdateCaiHutsRequest;
use App\Models\CaiHuts;

class CaiHutsController extends Controller
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
     * @param  \App\Http\Requests\StoreCaiHutsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCaiHutsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CaiHuts  $caiHuts
     * @return \Illuminate\Http\Response
     */
    public function show(CaiHuts $caiHuts)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CaiHuts  $caiHuts
     * @return \Illuminate\Http\Response
     */
    public function edit(CaiHuts $caiHuts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCaiHutsRequest  $request
     * @param  \App\Models\CaiHuts  $caiHuts
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCaiHutsRequest $request, CaiHuts $caiHuts)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CaiHuts  $caiHuts
     * @return \Illuminate\Http\Response
     */
    public function destroy(CaiHuts $caiHuts)
    {
        //
    }
}
