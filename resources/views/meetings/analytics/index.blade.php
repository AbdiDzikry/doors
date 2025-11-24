@extends('layouts.master')

@section('title', 'Meeting Analytics')

@section('content')
<div class="container-fluid px-6 py-4">
    <div class="py-4">
        <h1 class="text-2xl font-bold text-gray-800">Meeting Analytics</h1>
        <p class="text-sm text-gray-600">An overview of your meeting statistics.</p>
    </div>

    <div class="bg-white shadow-md rounded-lg p-4 mb-6">
        <form action="{{ route('meeting.analytics.index') }}" method="GET" class="flex items-end space-x-2">
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                <input type="date" name="date" id="date" value="{{ $date->format('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <div>
                <label for="filter" class="block text-sm font-medium text-gray-700">Filter</label>
                <select name="filter" id="filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    <option value="day" {{ $filter == 'day' ? 'selected' : '' }}>Day</option>
                    <option value="week" {{ $filter == 'week' ? 'selected' : '' }}>Week</option>
                    <option value="month" {{ $filter == 'month' ? 'selected' : '' }}>Month</option>
                    <option value="year" {{ $filter == 'year' ? 'selected' : '' }}>Year</option>
                </select>
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                Filter
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-800 mb-4">Busy Hours</h3>
            <canvas id="busyHoursChart"></canvas>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-800 mb-4">Department Usage</h3>
            <canvas id="departmentUsageChart"></canvas>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-800 mb-4">Room Usage</h3>
            <canvas id="roomUsageChart"></canvas>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-800 mb-4">Meeting Status Distribution</h3>
            <canvas id="meetingStatusDistributionChart"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Busy Hours Chart
        const busyHoursCtx = document.getElementById('busyHoursChart').getContext('2d');
        const busyHoursChart = new Chart(busyHoursCtx, {
            type: 'bar',
            data: {
                labels: @json(array_keys($busyHours)),
                datasets: [{
                    label: 'Number of Meetings',
                    data: @json(array_values($busyHours)),
                    backgroundColor: 'rgba(8, 146, 68, 0.2)',
                    borderColor: 'rgba(8, 146, 68, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Hour of Day (24-hour format)'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Meetings'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Department Usage Chart
        const departmentUsageCtx = document.getElementById('departmentUsageChart').getContext('2d');
        const departmentUsageChart = new Chart(departmentUsageCtx, {
            type: 'pie',
            data: {
                labels: @json(array_keys($departmentUsage)),
                datasets: [{
                    data: @json(array_values($departmentUsage)),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        title: {
                            display: true,
                            text: 'Number of Meetings by Department'
                        }
                    }
                }
            }
        });

        // Room Usage Chart
        const roomUsageCtx = document.getElementById('roomUsageChart').getContext('2d');
        const roomUsageChart = new Chart(roomUsageCtx, {
            type: 'doughnut',
            data: {
                labels: @json(array_keys($roomUsage)),
                datasets: [{
                    data: @json(array_values($roomUsage)),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        title: {
                            display: true,
                            text: 'Number of Meetings by Room'
                        }
                    }
                }
            }
        });

        // Meeting Status Distribution Chart
        const meetingStatusDistributionCtx = document.getElementById('meetingStatusDistributionChart').getContext('2d');
        const meetingStatusDistributionChart = new Chart(meetingStatusDistributionCtx, {
            type: 'polarArea',
            data: {
                labels: @json(array_keys($meetingStatusDistribution)),
                datasets: [{
                    data: @json(array_values($meetingStatusDistribution)),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        title: {
                            display: true,
                            text: 'Number of Meetings by Status'
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
