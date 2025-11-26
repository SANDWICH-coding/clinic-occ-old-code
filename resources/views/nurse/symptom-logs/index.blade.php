{{-- resources/views/nurse/symptom-logs/index.blade.php --}}
{{-- Version: 1.1.1 | Last Updated: October 15, 2025 --}}
@extends('layouts.nurse-app')

@section('title', 'Student Symptom Logs')

@section('content')
<div class="min-h-screen bg-gray-100 p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            {{-- Card Header --}}
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-4 md:p-6 flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-clipboard-list text-2xl md:text-3xl"></i>
                    <h1 class="text-xl md:text-2xl font-semibold">Student Symptom Logs</h1>
                </div>
                <!-- <div class="flex flex-wrap gap-2">
                    <a href="{{ route('nurse.symptom-logs.export') }}" class="bg-white text-blue-600 px-4 py-2 rounded-full font-medium shadow-sm hover:bg-gray-200 transition-colors duration-200">
                        <i class="fas fa-download mr-1"></i> Export
                    </a>
                </div> -->
            </div>

            {{-- Card Body --}}
            <div class="p-4 md:p-6">
                {{-- Statistics Summary --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-100 text-blue-800 p-4 rounded-lg shadow-sm text-center transition-transform duration-200 hover:scale-105">
                        <p class="text-3xl font-bold">{{ $totalLogs }}</p>
                        <span class="text-sm mt-2 block">Total Logs</span>
                    </div>
                    <div class="bg-yellow-100 text-yellow-800 p-4 rounded-lg shadow-sm text-center transition-transform duration-200 hover:scale-105">
                        <p class="text-3xl font-bold">{{ $todayLogs }}</p>
                        <span class="text-sm mt-2 block">Today's Logs</span>
                    </div>
                    <div class="bg-red-100 text-red-800 p-4 rounded-lg shadow-sm text-center transition-transform duration-200 hover:scale-105">
                        <p class="text-3xl font-bold">{{ $emergencyLogs }}</p>
                        <span class="text-sm mt-2 block">Emergency Cases</span>
                    </div>
                    <div class="bg-green-100 text-green-800 p-4 rounded-lg shadow-sm text-center transition-transform duration-200 hover:scale-105">
                        <p class="text-3xl font-bold">{{ $recentLogs }}</p>
                        <span class="text-sm mt-2 block">This Week</span>
                    </div>
                </div>

                {{-- Search and Filters --}}
                <form method="GET" action="{{ route('nurse.symptom-logs.index') }}" class="mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        {{-- Search Input --}}
                        <div class="col-span-1 sm:col-span-2">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text"
                                       name="search"
                                       class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                                       placeholder="Search by student name or ID..."
                                       value="{{ request('search') }}"
                                       aria-label="Search by student name or ID">
                            </div>
                        </div>

                        {{-- Emergency Filter --}}
                        <div>
                            <select name="emergency" class="w-full px-4 py-2.5 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 transition" aria-label="Filter by emergency status">
                                <option value="">All Cases</option>
                                <option value="1" {{ request('emergency') == '1' ? 'selected' : '' }}>Emergency Only</option>
                                <option value="0" {{ request('emergency') == '0' ? 'selected' : '' }}>Non-Emergency</option>
                            </select>
                        </div>

                        {{-- Date Range Filter --}}
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar text-gray-400"></i>
                            </div>
                            <input type="date"
                                   name="date"
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                                   value="{{ request('date') }}"
                                   aria-label="Filter by specific date">
                        </div>

                        {{-- Review Status Filter --}}
                        <div>
                            <select name="reviewed_status" class="w-full px-4 py-2.5 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 transition" aria-label="Filter by review status">
                                <option value="">All Statuses</option>
                                <option value="reviewed" {{ request('reviewed_status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                <option value="pending" {{ request('reviewed_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-full font-medium hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center gap-2" aria-label="Apply filters">
                                <i class="fas fa-filter"></i>
                                <span class="hidden sm:inline">Apply</span>
                            </button>
                            <a href="{{ route('nurse.symptom-logs.index') }}" class="px-4 py-2.5 border border-gray-300 text-gray-700 rounded-full font-medium hover:bg-gray-50 transition-colors duration-200 flex items-center justify-center" aria-label="Reset filters">
                                <i class="fas fa-refresh"></i>
                            </a>
                        </div>
                    </div>

                    {{-- Advanced Date Range (Optional - can be hidden by default) --}}
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4 hidden" id="advanced-date-range">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date"
                                   name="date_from"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                                   value="{{ request('date_from') }}"
                                   aria-label="Filter by start date">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                            <input type="date"
                                   name="date_to"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                                   value="{{ request('date_to') }}"
                                   aria-label="Filter by end date">
                        </div>
                        <div class="flex items-end">
                            <button type="button" onclick="toggleDateRange()" class="w-full px-4 py-2.5 text-sm text-blue-600 border border-blue-300 rounded-lg hover:bg-blue-50 transition-colors duration-200">
                                Use Single Date
                            </button>
                        </div>
                    </div>

                    {{-- Toggle for Advanced Date Range --}}
                    <div class="mt-2 text-right">
                        <button type="button" onclick="toggleDateRange()" class="text-sm text-blue-600 hover:text-blue-800 transition-colors duration-200 flex items-center gap-1 ml-auto">
                            <i class="fas fa-calendar-alt"></i>
                            <span id="date-range-toggle-text">Show Date Range</span>
                        </button>
                    </div>
                </form>

                {{-- Symptom Logs Table --}}
                @if($logs->count() > 0)
                    <div class="overflow-x-auto bg-gray-50 rounded-lg shadow-sm max-h-[calc(100vh-400px)]" id="logs-table">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-800 text-white sticky top-0 z-10">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'logged_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="text-white hover:text-gray-300 transition">
                                            Date/Time <i class="fas fa-sort ml-1"></i>
                                        </a>
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Student</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Symptoms</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Possible Illnesses</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($logs as $log)
                                    <tr class="hover:bg-gray-50 {{ $log->is_emergency ? 'bg-red-50 hover:bg-red-100' : '' }}">
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $log->logged_at->format('M d, Y') }}<br>
                                            {{ $log->logged_at->format('h:i A') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $log->student_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ID: {{ $log->student_identifier }}
                                            </div>
                                            @if($log->is_emergency)
                                                <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-600 text-white">
                                                    EMERGENCY
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            <div class="flex flex-wrap gap-1">
                                                @if(is_array($log->symptoms) && count($log->symptoms) > 0)
                                                    @foreach(array_slice($log->symptoms, 0, 3) as $symptom)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-800">{{ $symptom }}</span>
                                                    @endforeach
                                                    @if(count($log->symptoms) > 3)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">+{{ count($log->symptoms) - 3 }} more</span>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-800">No symptoms recorded</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            <div class="flex flex-wrap gap-1">
                                                @if(is_array($log->possible_illnesses) && count($log->possible_illnesses) > 0)
                                                    @foreach(array_slice($log->possible_illnesses, 0, 2) as $illness)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">{{ $illness }}</span>
                                                    @endforeach
                                                    @if(count($log->possible_illnesses) > 2)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">+{{ count($log->possible_illnesses) - 2 }} more</span>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">No illnesses identified</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($log->nurse_reviewed)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500 text-white">
                                                    <i class="fas fa-check mr-1"></i> Reviewed
                                                </span>
                                                <div class="text-xs text-gray-400 mt-1">{{ $log->reviewed_at->diffForHumans() }}</div>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500 text-white">
                                                    <i class="fas fa-clock mr-1"></i> Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('nurse.symptom-logs.show', $log->id) }}" class="text-blue-600 hover:text-blue-800 transition" title="View Details" aria-label="View symptom log details">
                                                    <i class="fas fa-eye text-lg"></i>
                                                </a>
                                                <a href="{{ route('nurse.symptom-logs.student-history', $log->student_identifier) }}" class="text-purple-600 hover:text-purple-800 transition" title="View Student History" aria-label="View student history">
                                                    <i class="fas fa-history text-lg"></i>
                                                </a>
                                                @unless($log->nurse_reviewed)
                                                    <button onclick="markAsReviewed({{ $log->id }})" class="text-green-600 hover:text-green-800 transition" title="Mark as Reviewed" aria-label="Mark symptom log as reviewed">
                                                        <i class="fas fa-check text-lg"></i>
                                                    </button>
                                                @endunless
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-12 px-4">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-clipboard-list text-5xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">No symptom logs found</h3>
                        <p class="mt-2 text-sm text-gray-500">There are no logs that match your current filters or search criteria.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Scrollbar for Table */
#logs-table::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

#logs-table::-webkit-scrollbar-track {
    background: #f8fafc;
    border-radius: 3px;
}

#logs-table::-webkit-scrollbar-thumb {
    background: #6b7280;
    border-radius: 3px;
    transition: background 0.2s ease;
}

#logs-table::-webkit-scrollbar-thumb:hover {
    background: #4b5563;
}

#logs-table {
    scrollbar-width: thin;
    scrollbar-color: #6b7280 #f8fafc;
}

/* Mobile Scrollbar */
@media (max-width: 768px) {
    #logs-table::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }
}

/* Responsive Table Adjustments */
@media (max-width: 768px) {
    table {
        display: block;
    }
    
    thead {
        display: none;
    }
    
    tbody, tr {
        display: block;
    }
    
    tr {
        margin-bottom: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
        background-color: #fff;
    }
    
    td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    
    td:last-child {
        border-bottom: none;
    }
    
    td:before {
        content: attr(data-label);
        font-weight: 600;
        color: #1f2937;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .grid-cols-2 {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
function markAsReviewed(logId) {
    if (confirm('Are you sure you want to mark this symptom log as reviewed?')) {
        fetch(`/nurse/symptom-logs/${logId}/mark-reviewed`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({})
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error marking as reviewed.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}

// Date Range Toggle Functionality
function toggleDateRange() {
    const advancedSection = document.getElementById('advanced-date-range');
    const toggleText = document.getElementById('date-range-toggle-text');
    const singleDateInput = document.querySelector('input[name="date"]');
    
    if (advancedSection.classList.contains('hidden')) {
        // Show advanced date range
        advancedSection.classList.remove('hidden');
        toggleText.textContent = 'Hide Date Range';
        singleDateInput.disabled = true;
        singleDateInput.classList.add('opacity-50');
    } else {
        // Hide advanced date range
        advancedSection.classList.add('hidden');
        toggleText.textContent = 'Show Date Range';
        singleDateInput.disabled = false;
        singleDateInput.classList.remove('opacity-50');
    }
}

// Initialize based on current URL parameters
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // If date_from or date_to is present, show advanced date range
    if (urlParams.has('date_from') || urlParams.has('date_to')) {
        toggleDateRange();
    }
});
</script>
@endpush