@extends('layouts.master')

@section('title', 'Meeting Details')

@section('content')
    <div class="container-fluid px-6 py-4">
        <div class="py-4">
            <a href="{{ route('meeting.meeting-lists.index') }}" class="inline-flex items-center text-sm font-semibold text-green-600 hover:text-green-800 mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back to Meeting List
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Meeting Details</h1>
            <p class="text-sm text-gray-600">Detailed information for meeting: <span class="font-semibold">{{ $meeting->topic }}</span></p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Meeting Info -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-md rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Topic</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $meeting->topic }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Room</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $meeting->room?->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Start Time</h3>
                            <p class="mt-1 text-gray-900">{{ \Carbon\Carbon::parse($meeting->start_time)->format('l, d F Y, H:i') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">End Time</h3>
                            <p class="mt-1 text-gray-900">{{ \Carbon\Carbon::parse($meeting->end_time)->format('l, d F Y, H:i') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @switch($meeting->calculated_status)
                                    @case('scheduled') bg-blue-100 text-blue-800 @break
                                    @case('completed') bg-green-100 text-green-800 @break
                                    @case('cancelled') bg-red-100 text-red-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch">
                                {{ ucfirst($meeting->calculated_status) }}
                            </span>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Booked By</h3>
                            <p class="mt-1 text-gray-900">{{ $meeting->user?->name ?? 'N/A' }} ({{ $meeting->user?->email ?? 'N/A' }})</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Participants & Pantry -->
            <div class="space-y-6">
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Participants</h2>
                    @if ($meeting->meetingParticipants->isNotEmpty())
                        <ul class="divide-y divide-gray-200">
                            @foreach ($meeting->meetingParticipants as $participant)
                                @if ($participant->participant_type === 'App\Models\User') {{-- Internal Participant --}}
                                    <li class="py-3 flex justify-between items-center">
                                        <span class="text-sm text-gray-800">{{ $participant->participant?->name ?? 'N/A' }}</span>
                                        <span class="text-xs font-medium bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Internal</span>
                                    </li>
                                @elseif ($participant->participant_type === 'App\Models\ExternalParticipant') {{-- External Participant --}}
                                    <li class="py-3 flex justify-between items-center">
                                        <div>
                                            <p class="text-sm text-gray-800">{{ $participant->participant?->name ?? 'N/A' }}</p>
                                            <p class="text-xs text-gray-500">{{ $participant->participant?->company ?? 'N/A' }}</p>
                                        </div>
                                        <span class="text-xs font-medium bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">External</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500">No participants for this meeting.</p>
                    @endif
                </div>

                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Pantry Orders</h2>
                    @if ($meeting->pantryOrders->isNotEmpty())
                        <ul class="divide-y divide-gray-200">
                            @foreach ($meeting->pantryOrders as $order)
                                <li class="py-3 flex justify-between items-center">
                                    <span class="text-sm text-gray-800">{{ $order->pantryItem?->name ?? 'N/A' }} x {{ $order->quantity }}</span>
                                    <span class="text-xs font-medium px-2 py-1 rounded-full 
                                        @switch($order->status)
                                            @case('pending') bg-yellow-100 text-yellow-800 @break
                                            @case('prepared') bg-blue-100 text-blue-800 @break
                                            @case('delivered') bg-green-100 text-green-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500">No pantry orders for this meeting.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
