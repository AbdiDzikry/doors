<?php

namespace App\Http\Controllers\Meeting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meeting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'month');
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : today();

        // Set the start and end dates based on the filter
        switch ($filter) {
            case 'day':
                $startDate = $date->copy()->startOfDay();
                $endDate = $date->copy()->endOfDay();
                break;
            case 'week':
                $startDate = $date->copy()->startOfWeek();
                $endDate = $date->copy()->endOfWeek();
                break;
            case 'month':
                $startDate = $date->copy()->startOfMonth();
                $endDate = $date->copy()->endOfMonth();
                break;
            case 'year':
                $startDate = $date->copy()->startOfYear();
                $endDate = $date->copy()->endOfYear();
                break;
            default:
                $startDate = today()->startOfMonth();
                $endDate = today()->endOfMonth();
                break;
        }

        // 1. Busy Hours
        $busyHoursQuery = Meeting::select(DB::raw('HOUR(start_time) as hour, count(*) as count'))
            ->whereBetween('start_time', [$startDate, $endDate])
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')->all();

        $busyHours = [];
        for ($i = 0; $i < 24; $i++) {
            // Format to 24-hour number
            $busyHours[strval($i)] = $busyHoursQuery[$i] ?? 0;
        }

        // 2. Department Usage
        $departmentUsageRaw = Meeting::join('users', 'meetings.user_id', '=', 'users.id')
            ->select('users.department', DB::raw('count(meetings.id) as count'))
            ->whereBetween('meetings.start_time', [$startDate, $endDate])
            ->groupBy('users.department')
            ->pluck('count', 'department')->all();

        $departmentUsage = [];
        foreach ($departmentUsageRaw as $department => $count) {
            if (empty($department) || $department === 'N/A') {
                $departmentUsage['No Department'] = ($departmentUsage['No Department'] ?? 0) + $count;
            } else {
                $departmentUsage[$department] = ($departmentUsage[$department] ?? 0) + $count;
            }
        }

        // 3. Room Usage
        $roomUsage = Meeting::join('rooms', 'meetings.room_id', '=', 'rooms.id')
            ->select('rooms.name', DB::raw('count(meetings.id) as count'))
            ->whereBetween('meetings.start_time', [$startDate, $endDate])
            ->groupBy('rooms.name')
            ->pluck('count', 'name')->all();

        // 4. Meeting Status Distribution
        $meetings = Meeting::whereBetween('start_time', [$startDate, $endDate])->get();
        $meetingStatusDistribution = $meetings->groupBy('calculated_status')->map->count()->all();


        return view('meetings.analytics.index', compact(
            'busyHours',
            'departmentUsage',
            'roomUsage',
            'meetingStatusDistribution',
            'filter',
            'date'
        ));
    }
}