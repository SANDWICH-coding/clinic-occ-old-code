@extends('layouts.app')

@section('title', 'My Appointments')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-7xl mx-auto">
            {{-- Header Section --}}
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">My Appointments</h1>
                    <p class="text-gray-600">Manage your healthcare appointments and view appointment history</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 mt-4 lg:mt-0">
                    <a href="{{ route('student.symptom-checker.index') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Request New Appointment
                    </a>
                </div>
            </div>

            {{-- Status Alerts --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-start">
                    <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-green-800">
                        <h4 class="font-semibold">Success!</h4>
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 flex items-start">
                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-red-800">
                        <h4 class="font-semibold">Error</h4>
                        <p class="text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Quick Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                @php
                    $totalAppointments = $appointments->total();
                    $pendingCount = $appointments->where('status', 'pending')->count();
                    $confirmedCount = $appointments->where('status', 'confirmed')->count();
                    $completedCount = $appointments->where('status', 'completed')->count();
                @endphp
                
                <div class="bg-white rounded-xl p-6 shadow-md border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-gray-900">{{ $totalAppointments }}</p>
                            <p class="text-gray-600 text-sm">Total Appointments</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-md border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-gray-900">{{ $pendingCount }}</p>
                            <p class="text-gray-600 text-sm">Pending Review</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-md border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-gray-900">{{ $confirmedCount }}</p>
                            <p class="text-gray-600 text-sm">Confirmed</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-md border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-gray-900">{{ $completedCount }}</p>
                            <p class="text-gray-600 text-sm">Completed</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters Section --}}
            <div class="bg-white rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Filter Appointments</h3>
                    <button id="toggleFilters" class="lg:hidden text-blue-600 hover:text-blue-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707v4.586l-4-2v-2.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                    </button>
                </div>
                
                <form action="{{ route('student.appointments.index') }}" method="GET" id="filterForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="filterInputs">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="status" name="status" class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="rescheduled" {{ request('status') == 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                            <input type="date" id="date" name="date" value="{{ request('date') }}" 
                                   class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="filter" class="block text-sm font-medium text-gray-700 mb-2">Quick Filters</label>
                            <select id="filter" name="filter" class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Appointments</option>
                                <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Today's Appointments</option>
                                <option value="upcoming" {{ request('filter') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                <option value="this_week" {{ request('filter') == 'this_week' ? 'selected' : '' }}>This Week</option>
                                <option value="next_week" {{ request('filter') == 'next_week' ? 'selected' : '' }}>Next Week</option>
                            </select>
                        </div>
                        
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-200 font-medium">
                                Apply
                            </button>
                            <a href="{{ route('student.appointments.index') }}" class="px-4 py-2 border-2 border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-all duration-200 font-medium">
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Appointments List --}}
            @if($appointments->count() > 0)
                {{-- Action Required Appointments --}}
                @php
                    $actionRequired = $appointments->filter(function($appointment) {
                        return $appointment->isRescheduled() && $appointment->requires_student_confirmation;
                    });
                @endphp
                
                @if($actionRequired->count() > 0)
                    <div class="bg-gradient-to-r from-orange-50 to-red-50 border-2 border-orange-200 rounded-xl p-6 mb-8">
                        <h3 class="text-lg font-semibold text-orange-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Action Required ({{ $actionRequired->count() }})
                        </h3>
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($actionRequired as $appointment)
                                <div class="bg-white rounded-lg p-4 border-l-4 border-orange-500">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $appointment->formatted_date }} - {{ $appointment->formatted_time }}</h4>
                                            <p class="text-gray-600 text-sm">{{ Str::limit($appointment->reason, 50) }}</p>
                                            <p class="text-orange-700 text-sm font-medium mt-1">Rescheduled - Confirmation needed</p>
                                        </div>
                                        <a href="{{ route('student.appointments.show', $appointment) }}" 
                                           class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition-all duration-200">
                                            Review
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Appointments Grid/List View --}}
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-900">Your Appointments</h3>
                            <div class="flex items-center space-x-2">
                                <button id="gridView" class="p-2 rounded-lg hover:bg-gray-100 transition-all duration-200" title="Grid View">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                    </svg>
                                </button>
                                <button id="listView" class="p-2 rounded-lg bg-blue-100 text-blue-600 transition-all duration-200" title="List View">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    {{-- List View (Default) --}}
                    <div id="appointmentsList" class="divide-y divide-gray-200">
                        @foreach($appointments as $appointment)
                            <div class="p-6 hover:bg-gray-50 transition-all duration-200">
                                <div class="flex flex-col lg:flex-row justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between mb-2">
                                            <div>
                                                <h4 class="font-semibold text-gray-900 text-lg">{{ $appointment->formatted_date }}</h4>
                                                @if($appointment->appointment_time)
                                                    <p class="text-blue-600 font-medium">{{ $appointment->formatted_time }}</p>
                                                @else
                                                    <p class="text-gray-500 text-sm">Time to be assigned</p>
                                                @endif
                                            </div>
                                            <div class="flex items-center space-x-3">
                                                @php
                                                    $statusConfig = [
                                                        'pending' => ['bg-yellow-100 text-yellow-800', 'Pending'],
                                                        'confirmed' => ['bg-green-100 text-green-800', 'Confirmed'],
                                                        'completed' => ['bg-blue-100 text-blue-800', 'Completed'],
                                                        'cancelled' => ['bg-red-100 text-red-800', 'Cancelled'],
                                                        'rescheduled' => ['bg-orange-100 text-orange-800', 'Rescheduled'],
                                                    ];
                                                    $config = $statusConfig[$appointment->status] ?? ['bg-gray-100 text-gray-800', 'Unknown'];
                                                @endphp
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $config[0] }}">
                                                    {{ $config[1] }}
                                                </span>
                                                
                                                @if($appointment->priority >= 4)
                                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">
                                                        {{ $appointment->priority == 5 ? 'Emergency' : 'Urgent' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <p class="text-gray-900 font-medium">{{ Str::limit($appointment->reason, 80) }}</p>
                                            @if($appointment->notes)
                                                <p class="text-gray-600 text-sm mt-1">{{ Str::limit($appointment->notes, 60) }}</p>
                                            @endif
                                        </div>
                                        
                                        @if($appointment->nurse && $appointment->appointment_time)
                                            <div class="flex items-center text-sm text-gray-600 mb-2">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                Assigned to: {{ $appointment->nurse->full_name }}
                                            </div>
                                        @endif
                                        
                                        <div class="flex items-center text-xs text-gray-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Requested: {{ $appointment->created_at->format('M d, Y g:i A') }}
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2 mt-4 lg:mt-0 lg:ml-6">
                                        <a href="{{ route('student.appointments.show', $appointment) }}" 
                                           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-md hover:shadow-lg">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    {{-- Grid View (Hidden by default) --}}
                    <div id="appointmentsGrid" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                        @foreach($appointments as $appointment)
                            <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-all duration-200 border border-gray-200">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $appointment->formatted_date }}</h4>
                                        @if($appointment->appointment_time)
                                            <p class="text-blue-600 font-medium text-sm">{{ $appointment->formatted_time }}</p>
                                        @endif
                                    </div>
                                    @php
                                        $statusConfig = [
                                            'pending' => ['bg-yellow-100 text-yellow-800', 'Pending'],
                                            'confirmed' => ['bg-green-100 text-green-800', 'Confirmed'],
                                            'completed' => ['bg-blue-100 text-blue-800', 'Completed'],
                                            'cancelled' => ['bg-red-100 text-red-800', 'Cancelled'],
                                            'rescheduled' => ['bg-orange-100 text-orange-800', 'Rescheduled'],
                                        ];
                                        $config = $statusConfig[$appointment->status] ?? ['bg-gray-100 text-gray-800', 'Unknown'];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $config[0] }}">
                                        {{ $config[1] }}
                                    </span>
                                </div>
                                
                                <p class="text-gray-700 text-sm mb-4">{{ Str::limit($appointment->reason, 60) }}</p>
                                
                                <div class="flex justify-between items-center">
                                    <div class="text-xs text-gray-500">
                                        {{ $appointment->created_at->format('M d') }}
                                    </div>
                                    <a href="{{ route('student.appointments.show', $appointment) }}" 
                                       class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                        View
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if($appointments->hasPages())
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    Showing {{ $appointments->firstItem() }} to {{ $appointments->lastItem() }} of {{ $appointments->total() }} results
                                </div>
                                <div>
                                    {{ $appointments->withQueryString()->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                {{-- Empty State --}}
                <div class="bg-white rounded-xl shadow-md p-12 text-center border border-gray-100">
                    <div class="max-w-md mx-auto">
                        <svg class="mx-auto h-24 w-24 text-gray-400 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No appointments found</h3>
                        <p class="text-gray-600 mb-6">
                            @if(request()->hasAny(['status', 'date', 'filter']))
                                No appointments match your current filters. Try adjusting your search criteria.
                            @else
                                You haven't requested any appointments yet. Get started by scheduling your first appointment with our healthcare team.
                            @endif
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            @if(request()->hasAny(['status', 'date', 'filter']))
                                <a href="{{ route('student.appointments.index') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg transition-all duration-200 font-medium">
                                    Clear Filters
                                </a>
                            @endif
                            <a href="{{ route('student.appointments.create') }}" 
                               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg transition-all duration-200 font-semibold shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Request Your First Appointment
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const gridViewBtn = document.getElementById('gridView');
    const listViewBtn = document.getElementById('listView');
    const appointmentsList = document.getElementById('appointmentsList');
    const appointmentsGrid = document.getElementById('appointmentsGrid');
    
    gridViewBtn.addEventListener('click', function() {
        appointmentsList.classList.add('hidden');
        appointmentsGrid.classList.remove('hidden');
        gridViewBtn.classList.add('bg-blue-100', 'text-blue-600');
        listViewBtn.classList.remove('bg-blue-100', 'text-blue-600');
        localStorage.setItem('appointmentView', 'grid');
    });
    
    listViewBtn.addEventListener('click', function() {
        appointmentsGrid.classList.add('hidden');
        appointmentsList.classList.remove('hidden');
        listViewBtn.classList.add('bg-blue-100', 'text-blue-600');
        gridViewBtn.classList.remove('bg-blue-100', 'text-blue-600');
        localStorage.setItem('appointmentView', 'list');
    });
    
    const savedView = localStorage.getItem('appointmentView');
    if (savedView === 'grid') {
        gridViewBtn.click();
    }
    
    const toggleFilters = document.getElementById('toggleFilters');
    const filterInputs = document.getElementById('filterInputs');
    
    if (toggleFilters) {
        toggleFilters.addEventListener('click', function() {
            filterInputs.classList.toggle('hidden');
        });
    }
});
</script>

<style>
@media (max-width: 1024px) {
    #filterInputs {
        display: none;
    }
    #filterInputs.show {
        display: grid;
    }
}
</style>
@endsection