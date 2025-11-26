{{-- resources/views/nurse/medical-records/index.blade.php - ULTRA-FAST VERSION --}}
@extends('layouts.nurse-app')

@section('title', 'Medical Records - Nurse Portal')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Student Medical Records
            </h1>
            <p class="text-gray-600 mt-1">View and manage student health information</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 w-full md:w-auto">
            <a href="{{ route('nurse.medical-records.create') }}" class="bg-green-600 text-white px-5 py-2.5 rounded-lg hover:bg-green-700 flex items-center justify-center font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                New Record
            </a>
            <!-- <a href="{{ route('nurse.students.search') }}" class="bg-gray-600 text-white px-5 py-2.5 rounded-lg hover:bg-gray-700 flex items-center justify-center font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Find Student
            </a> -->
        </div>
    </div>

    <!-- Quick Search Section -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <div class="flex items-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900">Search & Filter Records</h3>
        </div>
        <form method="GET" action="{{ route('nurse.medical-records.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4 items-end">
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Records</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by student name, ID, or medical condition..."
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Risk Level</label>
                    <select name="risk_level" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-submit">
                        <option value="">All Levels</option>
                        <option value="high" {{ request('risk_level') == 'high' ? 'selected' : '' }}>High Risk</option>
                        <option value="medium" {{ request('risk_level') == 'medium' ? 'selected' : '' }}>Medium Risk</option>
                        <option value="low" {{ request('risk_level') == 'low' ? 'selected' : '' }}>Low Risk</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Blood Type</label>
                    <select name="blood_type" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-submit">
                        <option value="">All Types</option>
                        <option value="A+" {{ request('blood_type') == 'A+' ? 'selected' : '' }}>A+</option>
                        <option value="A-" {{ request('blood_type') == 'A-' ? 'selected' : '' }}>A-</option>
                        <option value="B+" {{ request('blood_type') == 'B+' ? 'selected' : '' }}>B+</option>
                        <option value="B-" {{ request('blood_type') == 'B-' ? 'selected' : '' }}>B-</option>
                        <option value="AB+" {{ request('blood_type') == 'AB+' ? 'selected' : '' }}>AB+</option>
                        <option value="AB-" {{ request('blood_type') == 'AB-' ? 'selected' : '' }}>AB-</option>
                        <option value="O+" {{ request('blood_type') == 'O+' ? 'selected' : '' }}>O+</option>
                        <option value="O-" {{ request('blood_type') == 'O-' ? 'selected' : '' }}>O-</option>
                    </select>
                </div>
                <div class="flex space-x-2">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Search
                    </button>
                    <a href="{{ route('nurse.medical-records.index') }}" class="bg-gray-500 text-white px-4 py-3 rounded-lg hover:bg-gray-600 flex items-center">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600 font-medium">Total Records</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $medicalRecords->total() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white rounded-lg border border-gray-200 mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex flex-wrap px-6" aria-label="Tabs">
                <a href="{{ route('nurse.medical-records.index') }}" class="border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600 flex items-center mr-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    All Records
                </a>
            </nav>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
            <div class="flex">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <div class="flex">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L1.732 13.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Records Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                Medical Records 
                <span class="text-sm text-gray-500">({{ $medicalRecords->firstItem() ?? 0 }} - {{ $medicalRecords->lastItem() ?? 0 }} of {{ $medicalRecords->total() }})</span>
            </h3>
        </div>

        @if($medicalRecords->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Blood Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($medicalRecords as $record)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-sm font-medium mr-4">
                                    {{ strtoupper(substr($record->user->first_name, 0, 1) . substr($record->user->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $record->user->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $record->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-mono">{{ $record->user->student_id }}</div>
                            <div class="text-sm text-gray-500">{{ $record->user->course ?? 'No course' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($record->blood_type)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $record->blood_type }}
                                </span>
                            @else
                                <span class="text-gray-400 text-sm">Not specified</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $riskLevel = 'Low';
                                $riskColor = 'green';
                                
                                $riskFactors = 0;
                                if ($record->is_pwd) $riskFactors++;
                                if (!empty($record->allergies)) $riskFactors++;
                                if ($record->is_taking_maintenance_drugs) $riskFactors++;
                                if ($record->has_been_hospitalized_6_months) $riskFactors++;
                                if (!empty($record->past_illnesses)) $riskFactors++;
                                
                                if ($riskFactors >= 3) {
                                    $riskLevel = 'High';
                                    $riskColor = 'red';
                                } elseif ($riskFactors >= 1) {
                                    $riskLevel = 'Medium';
                                    $riskColor = 'yellow';
                                }
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $riskColor }}-100 text-{{ $riskColor }}-800">
                                {{ $riskLevel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $record->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('nurse.medical-records.show', $record->id) }}" 
                                   class="text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg transition-colors inline-flex items-center">
                                    View
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $medicalRecords->links() }}
        </div>
        @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No medical records found</h3>
            <p class="mt-2 text-gray-500">Get started by creating a new medical record for a student.</p>
            <div class="mt-6">
                <a href="{{ route('nurse.medical-records.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create Medical Record
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
// ULTRA-FAST JavaScript - Minimal operations only
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit search form on filter change - single event listener
    const autoSubmitElements = document.querySelectorAll('.auto-submit');
    autoSubmitElements.forEach(function(element) {
        element.addEventListener('change', function() {
            this.form.submit();
        });
    });

    // Keyboard shortcut for search - single listener
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
            }
        });
        
        // Auto-focus search input on page load
        if (!searchInput.value) {
            searchInput.focus();
        }
    }
});
</script>

<style>
/* ULTRA-FAST CSS - Zero animations, maximum performance */

/* Remove all animations and transitions */
* {
    transition: none !important;
    animation: none !important;
}

/* Basic responsive layout */
@media (max-width: 640px) {
    .flex.flex-col.sm\\:flex-row {
        flex-direction: column;
    }
    
    .space-y-2.sm\\:space-y-0.sm\\:space-x-3 > * + * {
        margin-left: 0;
        margin-top: 0.5rem;
    }
    
    .grid.grid-cols-1.md\\:grid-cols-4.lg\\:grid-cols-6 {
        grid-template-columns: 1fr;
    }
    
    .grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-4 {
        grid-template-columns: 1fr;
    }
    
    .flex.flex-wrap.px-6 {
        flex-direction: column;
    }
    
    .flex.flex-wrap.px-6 a {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
}

@media (min-width: 641px) and (max-width: 768px) {
    .grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-4 {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .grid.grid-cols-1.md\\:grid-cols-4.lg\\:grid-cols-6 {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Efficient hover states - color changes only */
.hover\\:bg-blue-700:hover {
    background-color: #1d4ed8;
}

.hover\\:bg-green-700:hover {
    background-color: #15803d;
}

.hover\\:bg-gray-700:hover {
    background-color: #374151;
}

.hover\\:bg-gray-600:hover {
    background-color: #4b5563;
}

.hover\\:bg-gray-50:hover {
    background-color: #f9fafb;
}

.hover\\:text-blue-900:hover {
    color: #1e3a8a;
}

.hover\\:text-green-900:hover {
    color: #14532d;
}

.hover\\:text-orange-900:hover {
    color: #9a3412;
}

.hover\\:text-gray-700:hover {
    color: #374151;
}

.hover\\:bg-blue-100:hover {
    background-color: #dbeafe;
}

.hover\\:bg-green-100:hover {
    background-color: #dcfce7;
}

.hover\\:bg-orange-100:hover {
    background-color: #fed7aa;
}

.hover\\:border-gray-300:hover {
    border-color: #d1d5db;
}

/* Remove shadows for performance */
.shadow-md {
    box-shadow: none !important;
}

/* Focus states - simplified */
.focus\\:ring-2:focus {
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

.focus\\:ring-blue-500:focus {
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

.focus\\:border-blue-500:focus {
    border-color: #3b82f6;
}

/* Focus visible for accessibility */
:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

:focus:not(:focus-visible) {
    outline: none;
}

/* Container optimization */
.container {
    max-width: 100%;
}

@media (min-width: 640px) {
    .container {
        max-width: 640px;
    }
}

@media (min-width: 768px) {
    .container {
        max-width: 768px;
    }
}

@media (min-width: 1024px) {
    .container {
        max-width: 1024px;
    }
}

@media (min-width: 1280px) {
    .container {
        max-width: 1280px;
    }
}

@media (min-width: 1536px) {
    .container {
        max-width: 1536px;
    }
}

/* Print optimization */
@media print {
    .no-print,
    button,
    .bg-blue-600,
    .bg-green-600,
    .bg-gray-600,
    .bg-red-600,
    nav,
    .flex.flex-col.sm\\:flex-row.space-y-2 {
        display: none !important;
    }
    
    .border,
    .border-gray-200 {
        border: 1px solid #000 !important;
    }
    
    .bg-white {
        background: #fff !important;
    }
    
    .text-gray-600,
    .text-gray-500 {
        color: #000 !important;
    }
}

/* Accessibility - reduced motion */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
}

/* High contrast support */
@media (prefers-contrast: high) {
    .text-gray-600 {
        color: #000;
    }
    
    .text-gray-500 {
        color: #333;
    }
    
    .bg-gray-50 {
        background-color: #f0f0f0;
        border: 1px solid #000;
    }
    
    .border-gray-200 {
        border-color: #000;
    }
}

/* Performance optimizations */
.bg-white {
    will-change: auto;
    contain: layout style;
}

/* Efficient spacing */
.space-y-4 > * + * {
    margin-top: 1rem;
}

.space-y-2 > * + * {
    margin-top: 0.5rem;
}

.space-x-2 > * + * {
    margin-left: 0.5rem;
}

.space-x-3 > * + * {
    margin-left: 0.75rem;
}

/* Table optimizations */
.min-w-full {
    min-width: 100%;
}

.divide-y.divide-gray-200 > * + * {
    border-top: 1px solid #e5e7eb;
}

.whitespace-nowrap {
    white-space: nowrap;
}

/* Button and form optimizations */
button,
.inline-flex {
    cursor: pointer;
    user-select: none;
}

button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

input,
select,
textarea {
    appearance: none;
    background-color: #fff;
}

input:focus,
select:focus,
textarea:focus {
    outline: none;
}

/* Efficient grid layouts */
.grid {
    display: grid;
}

.gap-4 {
    gap: 1rem;
}

.gap-6 {
    gap: 1.5rem;
}

/* Border utilities */
.border {
    border-width: 1px;
}

.border-gray-200 {
    border-color: #e5e7eb;
}

.border-gray-300 {
    border-color: #d1d5db;
}

.border-l-4 {
    border-left-width: 4px;
}

.border-b-2 {
    border-bottom-width: 2px;
}

.border-blue-500 {
    border-color: #3b82f6;
}

.border-green-500 {
    border-color: #10b981;
}

.border-red-500 {
    border-color: #ef4444;
}

.border-yellow-500 {
    border-color: #eab308;
}

.border-transparent {
    border-color: transparent;
}
</style>
@endsection