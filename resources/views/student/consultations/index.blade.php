@extends('layouts.app')

@section('title', 'My Consultations')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">My Consultations</h1>
            <p class="mt-1 text-sm text-gray-500">
                View your consultation history and results
            </p>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Consultations</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Completed</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['completed'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">This Month</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['this_month'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <form method="GET" action="{{ route('student.consultations.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search consultations..."
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>Waiting</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quick Filter</label>
                        <select name="filter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Time</option>
                            <option value="this_week" {{ request('filter') === 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="this_month" {{ request('filter') === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="follow_up_required" {{ request('filter') === 'follow_up_required' ? 'selected' : '' }}>Follow-ups Required</option>
                            <option value="with_referral" {{ request('filter') === 'with_referral' ? 'selected' : '' }}>With Referral</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Consultations List --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($consultations->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($consultations as $consultation)
                        <li class="hover:bg-gray-50 transition">
                            <a href="{{ route('student.consultations.show', $consultation) }}" class="block p-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ $consultation->chief_complaint }}
                                            </h3>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                                {{ $consultation->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                                   ($consultation->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                                   'bg-yellow-100 text-yellow-800') }}">
                                                {{ ucfirst(str_replace('_', ' ', $consultation->status)) }}
                                            </span>
                                        </div>

                                        <div class="space-y-1 text-sm text-gray-600">
                                            <p>
                                                <span class="font-medium">Date:</span>
                                                {{ $consultation->created_at->format('F j, Y - g:i A') }}
                                            </p>
                                            @if($consultation->nurse)
                                                <p>
                                                    <span class="font-medium">Attended by:</span>
                                                    {{ $consultation->nurse->full_name }}
                                                </p>
                                            @endif
                                            @if($consultation->diagnosis && $consultation->status === 'completed')
                                                <p>
                                                    <span class="font-medium">Diagnosis:</span>
                                                    {{ Str::limit($consultation->diagnosis, 100) }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="mt-3 flex items-center space-x-4 text-xs">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full
                                                {{ $consultation->type === 'emergency' ? 'bg-red-100 text-red-800' : 
                                                   ($consultation->type === 'walk_in' ? 'bg-yellow-100 text-yellow-800' : 
                                                   'bg-blue-100 text-blue-800') }}">
                                                {{ ucfirst(str_replace('_', ' ', $consultation->type)) }}
                                            </span>

                                            @if($consultation->follow_up_required)
                                                <span class="inline-flex items-center text-yellow-700">
                                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    Follow-up Required
                                                </span>
                                            @endif

                                            @if($consultation->referral_issued)
                                                <span class="inline-flex items-center text-purple-700">
                                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    Referral Issued
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="ml-4">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $consultations->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No consultations found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        You haven't had any consultations yet.
                    </p>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection