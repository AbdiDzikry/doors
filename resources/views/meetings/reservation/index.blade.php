@extends('layouts.master')

@section('title', 'Room Reservation')

@section('content')
    <div class="container-fluid px-6 py-4">
        <div class="py-4">
            <h1 class="text-2xl font-bold text-gray-800">Room Reservation</h1>
            <p class="text-sm text-gray-600">View room availability and make new reservations.</p>
        </div>

        <!-- Filter and Search Form -->
        <div class="bg-white shadow-md rounded-lg p-4 mb-6">
            <form action="{{ route('meeting.room-reservations.index') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Search Room</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search by name or facilities..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Filter by Status</label>
                        <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                            <option value="">All Statuses</option>
                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="in_use" {{ request('status') == 'in_use' ? 'selected' : '' }}>In Use</option>
                            <option value="under_maintenance" {{ request('status') == 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Apply Filter
                        </button>
                        <a href="{{ route('meeting.room-reservations.index') }}" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse ($rooms as $room)
                @if ($room->status !== 'under_maintenance')
                    <a href="{{ route('meeting.bookings.create', ['room_id' => $room->id]) }}" class="block">
                @endif
                    <div class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 ease-in-out hover:scale-105 hover:shadow-lg {{ $room->status === 'under_maintenance' ? 'opacity-70 grayscale' : '' }} h-full flex flex-col">
                        <img class="w-full h-48 object-cover" src="{{ $room->image_path ? route('master.rooms.image', ['filename' => basename($room->image_path)]) : 'https://via.placeholder.com/400x250' }}" alt="{{ $room->name }}">
                        <div class="p-4 flex-grow flex flex-col">
                            <h3 class="text-xl font-semibold text-gray-800">{{ $room->name }}</h3>
                            <p class="text-gray-600 text-sm mb-2">Capacity: {{ $room->capacity }} people</p>
                            <p class="text-gray-500 text-sm mb-3 h-20 overflow-hidden">{{ Str::limit($room->description, 100) }}</p>
                            <div class="mb-3 mt-auto">
                                @if ($room->status === 'under_maintenance')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">Under Maintenance</span>
                                @elseif ($room->is_in_use)
                                    <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-medium rounded-full">In Use until {{ $room->current_meeting->end_time->format('H:i T') }}</span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Available</span>
                                @endif
                            </div>
                            @php
                                $facilities = !empty($room->facilities) ? array_map('trim', explode(',', $room->facilities)) : [];
                            @endphp
                            @if (!empty($facilities))
                                <div class="grid grid-cols-2 gap-2 mb-4">
                                    @foreach ($facilities as $facility)
                                        <x-facility-icon :facility="$facility" />
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @if ($room->status !== 'under_maintenance')
                    </a>
                @endif
            @empty
                <div class="md:col-span-2 lg:col-span-3 bg-white rounded-lg shadow-md p-6 text-center text-gray-500">
                    No rooms found matching your criteria.
                </div>
            @endforelse
        </div>
    </div>
@endsection
