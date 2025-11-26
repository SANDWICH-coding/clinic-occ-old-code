@extends('layouts.nurse-app')

@php
    use App\Models\Consultation;
@endphp

@section('title', 'Consultations')

@section('content')
<div class="min-h-screen bg-gray-50 -m-6">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-6 gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Consultations</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage and track student consultations</p>
                </div>
                <div>
                    <a href="{{ route('nurse.consultations.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-200 w-full sm:w-auto justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Consultation
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center">
                    <div class="flex-shrink-0 mb-3 sm:mb-0">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Total Today</p>
                        <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['today']['total'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center">
                    <div class="flex-shrink-0 mb-3 sm:mb-0">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Complete</p>
                        <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['today']['complete'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center">
                    <div class="flex-shrink-0 mb-3 sm:mb-0">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">In Progress</p>
                        <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['today']['in_progress'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center">
                    <div class="flex-shrink-0 mb-3 sm:mb-0">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Emergencies</p>
                        <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['today']['emergency'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-medium text-gray-900">Search Consultations</h3>
            </div>
            <div class="p-4 sm:p-6">
                <form method="GET" action="{{ route('nurse.consultations.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Search by student name or complaint..." 
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-4">
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="submit" 
                                class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search
                        </button>
                        @if(request()->has('search'))
                            <a href="{{ route('nurse.consultations.index') }}" 
                               class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Clear Search
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Consultations Cards for Mobile, Table for Desktop -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-medium text-gray-900">
                    Consultations 
                    <span class="text-sm font-normal text-gray-500">({{ $consultations->total() }} total)</span>
                </h3>
            </div>

            <!-- Mobile Card View -->
            <div class="block lg:hidden">
                @forelse($consultations as $consultation)
                    <div class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors {{ $consultation->priority === 'emergency' ? 'bg-red-50 border-l-4 border-l-red-500' : ($consultation->priority === 'high' ? 'bg-amber-50 border-l-4 border-l-amber-500' : '') }}">
                        <!-- Student Info -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center flex-1 min-w-0">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-sm font-semibold text-gray-700">
                                        {{ substr($consultation->student->full_name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="ml-3 flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 truncate">{{ $consultation->student->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $consultation->student->student_id }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        {{ $consultation->student->course ?? 'N/A' }}
                                        @if($consultation->student->year_level)
                                            - Year {{ $consultation->student->year_level }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $consultation->priority === 'emergency' ? 'bg-red-600 text-white' : ($consultation->priority === 'high' ? 'bg-amber-600 text-white' : 'bg-gray-600 text-white') }}">
                                {{ $consultation->priority_display }}
                            </span>
                        </div>

                        <!-- Complaint -->
                        <div class="mb-3">
                            <div class="text-sm text-gray-900 line-clamp-2">{{ $consultation->chief_complaint }}</div>
                            @if($consultation->pain_level)
                                <div class="text-xs text-red-700 mt-1 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                    </svg>
                                    Pain: {{ $consultation->pain_level }}/10
                                </div>
                            @endif
                            @if($consultation->parent_pickup_required && !$consultation->parent_pickup_completed)
                                <div class="text-xs text-purple-700 mt-1 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    Parent Pickup Required
                                </div>
                            @endif
                        </div>

                        <!-- Meta Info -->
                        <div class="flex flex-wrap gap-2 mb-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $consultation->type === 'walk_in' ? 'bg-blue-100 text-blue-800' : ($consultation->type === 'emergency' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800') }}">
                                {{ $consultation->type_display }}
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $consultation->status === 'complete' ? 'bg-green-600 text-white' : 'bg-gray-600 text-white' }}">
                                {{ $consultation->status_display === 'Waiting' ? 'Complete' : $consultation->status_display }}
                            </span>
                        </div>

                        <!-- Date and Nurse -->
                        <div class="flex justify-between items-center text-xs text-gray-500 mb-3">
                            <span>{{ $consultation->created_at->format('M d, Y H:i') }}</span>
                            <span>{{ $consultation->nurse->full_name ?? 'Unassigned' }}</span>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('nurse.consultations.show', $consultation) }}" 
                               class="flex-1 inline-flex items-center justify-center px-3 py-1.5 border border-blue-600 text-blue-600 hover:bg-blue-50 rounded-lg text-xs font-medium transition-all duration-200">
                                View
                            </a>
                            @if($consultation->canStart())
                                <form action="{{ route('nurse.consultations.start', $consultation) }}" method="POST" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-1.5 border border-green-600 text-green-600 hover:bg-green-50 rounded-lg text-xs font-medium transition-all duration-200">
                                        Start
                                    </button>
                                </form>
                            @endif
                            @if($consultation->status !== 'cancelled')
                                <a href="{{ route('nurse.consultations.edit', $consultation) }}" 
                                   class="flex-1 inline-flex items-center justify-center px-3 py-1.5 border border-orange-600 text-orange-600 hover:bg-orange-50 rounded-lg text-xs font-medium transition-all duration-200">
                                    Edit
                                </a>
                            @endif
                            @if($consultation->canCancel())
                                <form action="{{ route('nurse.consultations.cancel', $consultation) }}" method="POST" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" onclick="return confirm('Are you sure you want to cancel this consultation?')" 
                                            class="w-full inline-flex items-center justify-center px-3 py-1.5 border border-red-600 text-red-600 hover:bg-red-50 rounded-lg text-xs font-medium transition-all duration-200">
                                        Cancel
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <svg class="w-12 h-12 text-gray-400 mb-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="text-base font-medium text-gray-900 mb-2">
                            {{ request()->has('search') ? 'No consultations match your search' : 'No consultations found' }}
                        </h3>
                        <p class="text-sm text-gray-500 mb-4">
                            {{ request()->has('search') ? 'Try adjusting your search term.' : 'Get started by creating your first consultation.' }}
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course & Year</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Complaint</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nurse</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($consultations as $consultation)
                            <tr class="hover:bg-gray-50 transition-colors duration-200 {{ $consultation->priority === 'emergency' ? 'bg-red-50 border-l-4 border-red-500' : ($consultation->priority === 'high' ? 'bg-amber-50 border-l-4 border-amber-500' : '') }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $consultation->priority === 'emergency' ? 'bg-red-600 text-white' : ($consultation->priority === 'high' ? 'bg-amber-600 text-white' : 'bg-gray-600 text-white') }}">
                                        {{ $consultation->priority_display }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-sm font-semibold text-gray-700">
                                                {{ substr($consultation->student->full_name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $consultation->student->full_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $consultation->student->student_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $consultation->student->course ?? 'N/A' }}</div>
                                    @if($consultation->student->year_level)
                                        <div class="text-sm text-gray-500">Year {{ $consultation->student->year_level }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    <div class="text-sm text-gray-900 truncate" title="{{ $consultation->chief_complaint }}">
                                        {{ $consultation->chief_complaint }}
                                    </div>
                                    @if($consultation->pain_level)
                                        <div class="text-xs text-red-700 mt-1 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                            </svg>
                                            Pain: {{ $consultation->pain_level }}/10
                                        </div>
                                    @endif
                                    @if($consultation->parent_pickup_required && !$consultation->parent_pickup_completed)
                                        <div class="text-xs text-purple-700 mt-1 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            </svg>
                                            Parent Pickup
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $consultation->type === 'walk_in' ? 'bg-blue-100 text-blue-800' : ($consultation->type === 'emergency' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800') }}">
                                        {{ $consultation->type_display }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $consultation->status === 'complete' ? 'bg-green-600 text-white' : 'bg-gray-600 text-white' }}">
                                        {{ $consultation->status_display === 'Waiting' ? 'Complete' : $consultation->status_display }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>{{ $consultation->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $consultation->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $consultation->nurse->full_name ?? 'Unassigned' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('nurse.consultations.show', $consultation) }}" 
                                           class="text-blue-600 hover:text-blue-900" title="View">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542-7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        @if($consultation->canStart())
                                            <form action="{{ route('nurse.consultations.start', $consultation) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-green-600 hover:text-green-900" title="Start">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                        @if($consultation->status !== 'cancelled')
                                            <a href="{{ route('nurse.consultations.edit', $consultation) }}" 
                                               class="text-orange-600 hover:text-orange-900" title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                        @endif
                                        @if($consultation->canCancel())
                                            <form action="{{ route('nurse.consultations.cancel', $consultation) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" onclick="return confirm('Are you sure you want to cancel this consultation?')" 
                                                        class="text-red-600 hover:text-red-900" title="Cancel">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                                            {{ request()->has('search') ? 'No consultations match your search' : 'No consultations found' }}
                                        </h3>
                                        <p class="text-gray-500 mb-4">
                                            {{ request()->has('search') ? 'Try adjusting your search term.' : 'Get started by creating your first consultation.' }}
                                        </p>
                                        @if(!request()->has('search'))
                                            <a href="{{ route('nurse.consultations.create') }}" 
                                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                Create First Consultation
                                            </a>
                                        @else
                                            <a href="{{ route('nurse.consultations.index') }}" 
                                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                Clear Search
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($consultations->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            Showing {{ $consultations->firstItem() }} to {{ $consultations->lastItem() }} of {{ $consultations->total() }} results
                        </div>
                        <div class="flex space-x-2">
                            {{ $consultations->appends(request()->query())->links('pagination::tailwind') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection