<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdultoRequest;
use App\Http\Requests\UpdateAdultoRequest;
use App\Models\Adulto;

class AdultoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $adultos = Adulto::all();
        return inertia('adultos/Index', compact('adultos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('adultos/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdultoRequest $request)
    {
        $adulto = Adulto::create($request->validated());

        return redirect()->route('adultos.index')->with('success', 'Adulto created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Adulto $adulto)
    {
        return inertia('adultos/Show', compact('adulto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Adulto $adulto)
    {
        return inertia('adultos/Edit', compact('adulto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdultoRequest $request, Adulto $adulto)
    {
        $adulto->update($request->validated());

        return redirect()->route('adultos.index')->with('success', 'Adulto updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Adulto $adulto)
    {
        $adulto->delete();

        return redirect()->route('adultos.index')->with('success', 'Adulto deleted successfully.');
    }
}
