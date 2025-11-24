<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExternalParticipant;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExternalParticipantsExport;
use App\Imports\ExternalParticipantsImport;

class ExternalParticipantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('master.participants.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master.participants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:external_participants',
            'type' => 'required|in:internal,external',
        ]);

        ExternalParticipant::create($request->all());

        return redirect()->route('master.external-participants.index')
                        ->with('success','Participant created successfully.');
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
    public function edit(ExternalParticipant $externalParticipant)
    {
        return view('master.participants.edit',['participant' => $externalParticipant]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExternalParticipant $externalParticipant)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:external_participants,email,'.$externalParticipant->id,
            'type' => 'required|in:internal,external',
        ]);

        $externalParticipant->update($request->all());

        return redirect()->route('master.external-participants.index')
                        ->with('success','Participant updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExternalParticipant $externalParticipant)
    {
        $externalParticipant->delete();

        return redirect()->route('master.external-participants.index')
                        ->with('success','Participant deleted successfully');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=external_participants_template.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $columns = ['NAME', 'EMAIL', 'PHONE', 'COMPANY', 'DEPARTMENT', 'ADDRESS'];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new ExternalParticipantsImport, $request->file('file'));

        return redirect()->route('master.external-participants.index')->with('success', 'External participants imported successfully!');
    }

    public function export()
    {
        return Excel::download(new ExternalParticipantsExport, 'external_participants.xlsx');
    }
}