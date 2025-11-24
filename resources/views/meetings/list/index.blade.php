@extends('layouts.master')

@section('title', 'Meeting List')

@section('content')
    <div class="container-fluid px-6 py-4" x-data="{ activeTab: '{{ $activeTab }}' }">
        <div class="py-4">
            <h1 class="text-2xl font-bold text-gray-800">Meeting List</h1>
            <p class="text-sm text-gray-600">A list of all scheduled meetings.</p>
        </div>

        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="{{ route('meeting.meeting-lists.index', ['tab' => 'meeting-list']) }}"
                   :class="{ 'border-primary text-primary': activeTab === 'meeting-list', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'meeting-list' }"
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Meeting List
                </a>
                <a href="{{ route('meeting.meeting-lists.index', ['tab' => 'my-meetings']) }}"
                   :class="{ 'border-primary text-primary': activeTab === 'my-meetings', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'my-meetings' }"
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    My Meetings
                </a>
            </nav>
        </div>

        <!-- Meeting List Tab -->
        <div x-show="activeTab === 'meeting-list'">
            <!-- Date Navigation and Search Form -->
            <div class="bg-white shadow-md rounded-lg p-4 mb-6">
                <form action="{{ route('meeting.meeting-lists.index') }}" method="GET" class="flex items-end space-x-2" x-data="{}">
                    <input type="hidden" name="tab" value="meeting-list">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date', $effectiveStartDate->format('Y-m-d')) }}" @change="document.getElementById('filter').value = 'custom'" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date', $effectiveEndDate->format('Y-m-d')) }}" @change="document.getElementById('filter').value = 'custom'" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="filter" class="block text-sm font-medium text-gray-700">Filter</label>
                        <select name="filter" id="filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="custom" {{ request('filter') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            <option value="day" {{ request('filter', 'week') == 'day' ? 'selected' : '' }}>Day</option>
                            <option value="week" {{ request('filter', 'week') == 'week' ? 'selected' : '' }}>Week</option>
                            <option value="month" {{ request('filter') == 'month' ? 'selected' : '' }}>Month</option>
                            <option value="year" {{ request('filter') == 'year' ? 'selected' : '' }}>Year</option>
                        </select>
                    </div>
                    <div class="flex-grow">
                        <label for="search" class="block text-sm font-medium text-gray-700 sr-only">Search</label>
                        <div class="relative">
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search by topic, room or user..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm pl-10">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                 </svg>
                            </span>
                        </div>
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Search
                    </button>
                    <a href="{{ route('meeting.meeting-lists.index', ['tab' => 'meeting-list']) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Clear
                    </a>
                </form>
            </div>

            <!-- Meetings Table -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                @php
                                    $columns = [
                                        'topic' => 'Topic',
                                        'room.name' => 'Room',
                                        'start_time' => 'Start Time',
                                        'end_time' => 'End Time',
                                        'user.name' => 'Booked By',
                                    ];
                                @endphp
                                @foreach ($columns as $column => $title)
                                    @php
                                        $sortable = in_array($column, ['topic', 'start_time', 'end_time']);
                                        $currentSortBy = request('sort_by', 'start_time');
                                        $currentSortDirection = request('sort_direction', 'asc');
                                        $newSortDirection = ($currentSortBy === $column && $currentSortDirection === 'asc') ? 'desc' : 'asc';
                                        $queryString = array_merge(request()->query(), ['sort_by' => $column, 'sort_direction' => $newSortDirection, 'tab' => 'meeting-list']);
                                    @endphp
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider {{ $sortable ? 'cursor-pointer hover:bg-gray-100' : '' }}"
                                        @if($sortable) onclick="window.location.href = '{{ route('meeting.meeting-lists.index', $queryString) }}'" @endif>
                                        <div class="flex items-center">
                                            {{ $title }}
                                            @if ($currentSortBy === $column)
                                                @if ($currentSortDirection === 'asc')
                                                    <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                                @else
                                                    <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                @endif
                                            @endif
                                        </div>
                                    </th>
                                @endforeach
                                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                                    <th scope="col" class="relative px-6 py-3">
                                                                        <span class="sr-only">Actions</span>
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-white divide-y divide-gray-200">
                                                                @forelse ($meetings as $meeting)
                                                                    <tr class="hover:bg-gray-50 {{ $meeting->calculated_status === 'cancelled' ? 'bg-red-50 opacity-60' : '' }}">
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                            {{ $meeting->topic }}
                                                                            @if($meeting->calculated_status === 'cancelled')
                                                                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                                                    Cancelled
                                                                                </span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $meeting->room->name }}</td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($meeting->start_time)->format('l, d F Y, H:i') }}</td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($meeting->end_time)->format('l, d F Y, H:i') }}</td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $meeting->user->name }}</td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                                            @if ($meeting->calculated_status === 'scheduled')
                                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                                                    Scheduled
                                                                                </span>
                                                                            @elseif ($meeting->calculated_status === 'cancelled')
                                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                                                    Cancelled
                                                                                </span>
                                                                            @elseif ($meeting->calculated_status === 'completed')
                                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                                                    Completed
                                                                                </span>
                                                                            @else
                                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                                                    {{ ucfirst($meeting->calculated_status) }}
                                                                                </span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('meeting.meeting-lists.show', $meeting) }}" class="text-primary hover:text-green-700" title="View Details">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                <span class="sr-only">View</span>
                                            </a>
                                            
                                            @if($meeting->status !== 'cancelled')
                                                @if(Auth::user()->hasAnyRole(['Admin', 'Super Admin']))
                                                    <a href="{{ route('meeting.meeting-lists.edit', $meeting) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit Meeting">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14.25v4.5a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18V7.5a2.25 2.25 0 012.25-2.25H9"></path>
                                                        </svg>
                                                        <span class="sr-only">Edit</span>
                                                    </a>
                                                @endif

                                                @if(Auth::user()->hasAnyRole(['Admin', 'Super Admin']) || Auth::id() === $meeting->user_id)
                                                    <form action="{{ route('meeting.meeting-lists.destroy', $meeting) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to cancel this meeting?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Cancel Meeting">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.927a2.25 2.25 0 01-2.244-2.077L4.74 5.79a48.106 48.106 0 01-.994-3.21C3.547 2.045 3.82 1.75 4.25 1.75h15.5c.43 0 .703.295.546.646-.09.35-.22.678-.362.939m-6.523-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.927a2.25 2.25 0 01-2.244-2.077L4.74 5.79a48.106 48.106 0 01-.994-3.21C3.547 2.045 3.82 1.75 4.25 1.75h15.5c.43 0 .703.295.546.646-.09.35-.22.678-.362.939M7.5 4.5v.75m7.5-1.5v.75m-6.75 0h-1.5C6.927 3.75 6.75 3.927 6.75 4.148v.75m7.5 0h-1.5c-.219 0-.398-.177-.398-.398V4.148c0-.221.179-.398.398-.398h1.5c.219 0 .398.177.398.398v.75c0 .221-.179-.398-.398-.398z"></path>
                                                            </svg>
                                                            <span class="sr-only">Cancel</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No meetings found. Adjust your filters or add new meetings.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- My Meetings Tab -->
        <div x-show="activeTab === 'my-meetings'" class="space-y-6">
            <!-- Filter and Statistics -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-700">My Meeting Stats</h2>
                    <!-- Filter Form -->
                    <form action="{{ route('meeting.meeting-lists.index') }}" method="GET">
                        <input type="hidden" name="tab" value="my-meetings">
                        <div class="flex items-center space-x-2">
                            <label for="my_meetings_filter" class="text-sm font-medium text-gray-700">Filter by</label>
                            <select name="filter" id="my_meetings_filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm" onchange="this.form.submit()">
                                <option value="day" {{ $filter == 'day' ? 'selected' : '' }}>Day</option>
                                <option value="week" {{ $filter == 'week' ? 'selected' : '' }}>Week</option>
                                <option value="month" {{ $filter == 'month' ? 'selected' : '' }}>Month</option>
                                <option value="year" {{ $filter == 'year' ? 'selected' : '' }}>Year</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Meetings -->
                    <div class="bg-white shadow-lg rounded-lg p-5 flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m-1 4h1m5-4h1m-1 4h1m-1-4h1m-1-4h1" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Total Meetings</div>
                            <div class="text-2xl font-bold text-gray-800">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                    </div>
                    <!-- Scheduled -->
                    <div class="bg-white shadow-lg rounded-lg p-5 flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Scheduled</div>
                            <div class="text-2xl font-bold text-green-600">{{ $stats['scheduled'] ?? 0 }}</div>
                        </div>
                    </div>
                    <!-- Completed -->
                    <div class="bg-white shadow-lg rounded-lg p-5 flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Completed</div>
                            <div class="text-2xl font-bold text-blue-600">{{ $stats['completed'] ?? 0 }}</div>
                        </div>
                    </div>
                    <!-- Cancelled -->
                    <div class="bg-white shadow-lg rounded-lg p-5 flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Cancelled</div>
                            <div class="text-2xl font-bold text-red-600">{{ $stats['cancelled'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Meeting List -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800">My Meeting Schedule</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topic</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($myMeetings as $meeting)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $meeting->topic }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $meeting->room->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($meeting->start_time)->format('d M Y, H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($meeting->end_time)->format('d M Y, H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if ($meeting->calculated_status === 'scheduled')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Scheduled
                                            </span>
                                        @elseif ($meeting->calculated_status === 'cancelled')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Cancelled
                                            </span>
                                        @elseif ($meeting->calculated_status === 'completed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Completed
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ ucfirst($meeting->calculated_status) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 whitespace-nowrap text-sm text-gray-500 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No meetings found</h3>
                                        <p class="mt-1 text-sm text-gray-500">No meetings scheduled for this period.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection