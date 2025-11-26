@extends('layouts.master')
@section('title', 'Receptionist Dashboard')
@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Receptionist Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4 col-span-full">Daily Operations</h2>
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Pantry Orders</h2>
                <p class="text-gray-600 mb-4">
                    <span class="font-bold text-2xl text-primary">{{ $pendingPantryOrdersCount }}</span> pending orders
                </p>
                <a href="{{ route('dashboard.receptionist') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <i class="fas fa-clipboard-list mr-2"></i> View Pantry Queue
                </a>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 lg:col-span-2">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Today's Meetings</h2>
                @if ($todaysMeetings->isEmpty())
                    <p class="text-gray-600">No meetings scheduled for today.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($todaysMeetings as $meeting)
                            <li class="flex items-center justify-between bg-gray-50 p-3 rounded-md">
                                <div>
                                    <p class="font-medium text-gray-700">{{ $meeting->title }}</p>
                                    <p class="text-sm text-gray-500">{{ $meeting->start_time->format('H:i') }} - {{ $meeting->end_time->format('H:i') }} in {{ $meeting->room->name }}</p>
                                </div>
                                <a href="{{ route('meeting.bookings.show', $meeting->id) }}" class="text-primary hover:text-primary-dark text-sm">Details</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4 col-span-full mt-6">Guidance</h2>
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 lg:col-span-3">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">How to Use (Receptionist)</h2>
                <ul class="list-disc list-inside text-gray-600 space-y-2">
                    <li>Monitor and manage pantry orders from the "Pantry Orders" section.</li>
                    <li>View all meetings scheduled for today in "Today's Meetings".</li>
                    <li>Ensure guests are checked in for their meetings.</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
