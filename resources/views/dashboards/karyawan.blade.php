@extends('layouts.master')
@section('title', 'My Dashboard')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
@endpush

@section('content')
<div class="container mx-auto px-6 py-8">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-xl p-8 mb-8 text-white flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold mb-2 flex items-center">
                <i class="fas fa-hand-sparkles mr-3 text-blue-200"></i> Welcome Back, {{ Auth::user()->name }}!
            </h1>
            <p class="text-lg opacity-90">Here's a quick overview of your schedule and important actions.</p>
        </div>
        <div class="hidden md:block">
            <i class="fas fa-calendar-check text-6xl opacity-25"></i>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Next Meeting & Quick Actions -->
        <div class="lg:col-span-1 space-y-8">
            <!-- Next Meeting -->
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg shadow-xl p-6 text-white">
                <h2 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-star text-yellow-300 mr-3"></i>
                    Your Next Meeting
                </h2>
                @if ($nextMeeting)
                    <div class="space-y-3">
                        <h3 class="text-3xl font-bold mb-2">{{ $nextMeeting->topic }}</h3>
                        <div class="flex items-center text-sm opacity-90">
                            <i class="far fa-calendar-alt w-4 mr-2"></i>
                            <span>{{ $nextMeeting->start_time->format('l, F jS') }}</span>
                        </div>
                        <div class="flex items-center text-sm opacity-90">
                            <i class="far fa-clock w-4 mr-2"></i>
                            <span>{{ $nextMeeting->start_time->format('H:i') }} - {{ $nextMeeting->end_time->format('H:i') }}</span>
                        </div>
                        <div class="flex items-center text-sm opacity-90">
                            <i class="fas fa-map-marker-alt w-4 mr-2"></i>
                            <span>{{ $nextMeeting->room?->name ?? 'N/A' }}</span>
                        </div>
                        <a href="{{ route('meeting.meeting-lists.show', $nextMeeting) }}" class="inline-block w-full text-center mt-6 px-4 py-2 bg-white text-indigo-700 font-bold rounded-lg shadow-md hover:bg-gray-100 transition-colors">
                            View Details
                        </a>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-coffee text-4xl opacity-75 mb-4"></i>
                        <p class="text-white text-lg">No upcoming meetings. Enjoy your free time!</p>
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="{{ route('meeting.room-reservations.create') }}" class="flex flex-col items-center justify-center p-4 bg-green-500 text-white font-semibold rounded-lg shadow-md hover:bg-green-600 transition-colors text-center">
                        <i class="fas fa-plus-circle text-3xl mb-2"></i>
                        <span>Book New Meeting</span>
                    </a>
                    <a href="{{ route('meeting.meeting-lists.index', ['tab' => 'my-meetings']) }}" class="flex flex-col items-center justify-center p-4 bg-yellow-500 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-600 transition-colors text-center">
                        <i class="fas fa-calendar-alt text-3xl mb-2"></i>
                        <span>My Meetings</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column: Other Upcoming Meetings -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Other Upcoming Meetings</h2>
                @if($otherMeetings->isNotEmpty())
                    <ul class="space-y-4">
                        @foreach ($otherMeetings as $meeting)
                            <li class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 flex items-center justify-between p-4">
                                <div class="flex items-center space-x-3">
                                    <i class="far fa-calendar-check text-2xl text-blue-600"></i>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $meeting->topic }}</p>
                                        <p class="text-sm text-gray-600">
                                            {{ $meeting->start_time->format('D, M j, H:i') }} in <span class="font-medium text-indigo-700">{{ $meeting->room?->name ?? 'N/A' }}</span>
                                        </p>
                                    </div>
                                </div>
                                <a href="{{ route('meeting.meeting-lists.show', $meeting) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center">
                                    Details <i class="fas fa-arrow-right ml-2 text-xs"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                        <i class="far fa-calendar-times text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600">No other meetings scheduled for this period.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection