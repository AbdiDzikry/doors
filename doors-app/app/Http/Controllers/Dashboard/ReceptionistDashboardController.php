<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PantryOrder;
use App\Events\PantryOrderStatusUpdated;

class ReceptionistDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|before_or_equal:today',
        ]);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $request->validate([
                'start_date' => 'before_or_equal:end_date',
            ]);
        }

        // Fetch pending pantry orders for today's meetings
        $pendingPantryOrdersGroupedByMeeting = PantryOrder::where('pantry_orders.status', 'pending')
                                    ->with(['meeting.room', 'pantryItem'])
                                    ->join('meetings', 'pantry_orders.meeting_id', '=', 'meetings.id')
                                    ->whereDate('meetings.start_time', today()) // Filter for today's meetings
                                    ->orderBy('meetings.start_time')
                                    ->select('pantry_orders.*')
                                    ->get()
                                    ->groupBy('meeting_id');

        // Fetch historical pantry orders with filters
        $historicalPantryOrdersQuery = PantryOrder::with(['meeting.room', 'pantryItem']);

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = \Carbon\Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = \Carbon\Carbon::parse($request->input('end_date'))->endOfDay();
            $historicalPantryOrdersQuery->whereHas('meeting', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_time', [$startDate, $endDate]);
            });
        }

        // Search by meeting topic or pantry item name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $historicalPantryOrdersQuery->where(function ($q) use ($search) {
                $q->whereHas('meeting', function ($q2) use ($search) {
                    $q2->where('topic', 'like', '%' . $search . '%');
                })->orWhereHas('pantryItem', function ($q2) use ($search) {
                    $q2->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        $historicalPantryOrders = $historicalPantryOrdersQuery->orderBy('created_at', 'desc')->paginate(10);

        $pendingPantryOrdersCount = PantryOrder::where('status', 'pending')->count();
        $todaysMeetings = \App\Models\Meeting::whereDate('start_time', today())->orderBy('start_time')->get();

        return view('dashboards.receptionist', compact('pendingPantryOrdersGroupedByMeeting', 'historicalPantryOrders', 'pendingPantryOrdersCount', 'todaysMeetings'));
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PantryOrder $pantryOrder)
    {
        $request->validate([
            'status' => 'required|in:preparing,delivered',
        ]);

        $pantryOrder->update(['status' => $request->input('status')]);

        // You may want to fetch updated pending orders or broadcast a more specific change
        event(new PantryOrderStatusUpdated($pantryOrder));

        return redirect()->route('dashboard.receptionist')
                        ->with('success','Pantry order status updated successfully.');
    }

    public function getPantryOrdersPartial()
    {
        $pantryOrders = PantryOrder::where('status', 'pending')
                                    ->with(['meeting.room', 'pantryItem'])
                                    ->join('meetings', 'pantry_orders.meeting_id', '=', 'meetings.id')
                                    ->orderBy('meetings.start_time')
                                    ->select('pantry_orders.*')
                                    ->get();

        return view('dashboards.partials.pantry-orders', compact('pantryOrders'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
