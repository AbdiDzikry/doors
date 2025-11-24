<?php

namespace App\Http\Controllers\Meeting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meeting;

use Illuminate\Support\Facades\Auth;

class MeetingListController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeTab = $request->input('tab', 'meeting-list');
        $meetings = collect();
        $myMeetings = collect();
        $stats = [];
        $sortBy = $request->input('sort_by', 'start_time');
        $sortDirection = $request->input('sort_direction', 'asc');

        // Get filter and dates from request
        $filter = $request->input('filter', 'week');

        if ($filter === 'day') {
            $carbonStartDate = today();
            $carbonEndDate = today();
        } else {
            $startDateInput = $request->input('start_date');
            $endDateInput = $request->input('end_date');
            $carbonStartDate = $startDateInput ? \Carbon\Carbon::parse($startDateInput) : today();
            $carbonEndDate = $endDateInput ? \Carbon\Carbon::parse($endDateInput) : today();
        }


        switch ($filter) {
            case 'day':
                $effectiveStartDate = $carbonStartDate->startOfDay();
                $effectiveEndDate = $carbonEndDate->endOfDay();
                break;
            case 'week':
                $effectiveStartDate = $carbonStartDate->startOfWeek();
                $effectiveEndDate = $carbonEndDate->endOfWeek();
                break;
            case 'month':
                $effectiveStartDate = $carbonStartDate->startOfMonth();
                $effectiveEndDate = $carbonEndDate->endOfMonth();
                break;
            case 'year':
                $effectiveStartDate = $carbonStartDate->startOfYear();
                $effectiveEndDate = $carbonEndDate->endOfYear();
                break;
            case 'custom':
            default:
                $effectiveStartDate = $carbonStartDate->startOfDay();
                $effectiveEndDate = $carbonEndDate->endOfDay();
                break;
        }


        if ($activeTab === 'my-meetings') {
            $query = Meeting::where('user_id', $user->id);

            $query->whereBetween('start_time', [$effectiveStartDate, $effectiveEndDate]);

            $myMeetings = $query->with('room')->get();

            $stats = [
                'total' => $myMeetings->count(),
                'scheduled' => $myMeetings->where('calculated_status', 'scheduled')->count(),
                'completed' => $myMeetings->where('calculated_status', 'completed')->count(),
                'cancelled' => $myMeetings->where('calculated_status', 'cancelled')->count(),
            ];

        } else {
            $query = Meeting::query();

            $query->whereBetween('start_time', [$effectiveStartDate, $effectiveEndDate]);


            // Define allowed sortable columns
            $allowedSortBy = ['topic', 'start_time', 'end_time'];


            // Validate sort_by and sort_direction
            if (!in_array($sortBy, $allowedSortBy)) {
                $sortBy = 'start_time';
            }
            if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
                $sortDirection = 'asc';
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('topic', 'like', '%' . $search . '%')
                        ->orWhereHas('room', function ($qr) use ($search) {
                            $qr->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('user', function ($qu) use ($search) {
                            $qu->where('name', 'like', '%' . $search . '%');
                        });
                });
            }

            $query->orderBy($sortBy, $sortDirection);

            $meetings = $query->with('room', 'user')->get();
        }

        return view('meetings.list.index', compact('meetings', 'myMeetings', 'stats', 'filter', 'effectiveStartDate', 'effectiveEndDate', 'activeTab', 'sortBy', 'sortDirection'));
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
    public function store(Request $request)
    {
        //
    }

    public function show(Meeting $meeting)
    {
        $meeting->load('room', 'user', 'meetingParticipants.participant', 'pantryOrders.pantryItem');
        return view('meetings.list.show', compact('meeting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Meeting $meeting)
    {
        $meeting->load('room', 'user', 'meetingParticipants.participant', 'pantryOrders.pantryItem');
        return view('meetings.list.edit', compact('meeting'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Meeting $meeting)
    {
        $validatedData = $request->validate([
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            // Add other fields as necessary
        ]);

        $meeting->update($validatedData);

        return redirect()->route('meeting.meeting-lists.show', $meeting)->with('success', 'Meeting updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Meeting $meeting)
    {
        // Authorization: Only the meeting creator or an admin can cancel.
        if (auth()->user()->id !== $meeting->user_id && !auth()->user()->hasRole(['Super Admin', 'Admin'])) {
            return back()->with('error', 'You are not authorized to cancel this meeting.');
        }

        $meeting->status = 'cancelled';
        $meeting->save();

        return redirect()->route('meeting.meeting-lists.index')
                        ->with('success','Meeting cancelled successfully.');
    }
}

