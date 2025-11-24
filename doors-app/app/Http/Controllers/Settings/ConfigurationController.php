<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Configuration;

class ConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $configurations = Configuration::paginate(10);
        return view('settings.configuration.index', compact('configurations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('settings.configuration.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|unique:configurations',
            'value' => 'required',
        ]);

        Configuration::create($request->all());

        return redirect()->route('settings.configurations.index')
                        ->with('success','Configuration created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Configuration $configuration)
    {
        return view('settings.configuration.edit', ['config' => $configuration]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Configuration $configuration)
    {
        $request->validate([
            'value' => 'required',
        ]);

        $configuration->update($request->all());

        return redirect()->route('settings.configurations.index')
                        ->with('success','Configuration updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Configuration $configuration)
    {
        $configuration->delete();

        return redirect()->route('settings.configurations.index')
                        ->with('success','Configuration deleted successfully');
    }
}
