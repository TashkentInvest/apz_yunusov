<?php

namespace App\Http\Controllers;

use App\Models\CancellationReason;
use App\Http\Requests\StoreCancellationReasonRequest;
use App\Http\Requests\UpdateCancellationReasonRequest;

class CancellationReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCancellationReasonRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CancellationReason $cancellationReason)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CancellationReason $cancellationReason)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCancellationReasonRequest $request, CancellationReason $cancellationReason)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CancellationReason $cancellationReason)
    {
        //
    }
}
