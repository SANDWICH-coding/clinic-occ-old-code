@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Header -->
           <div class="bg-blue-600 px-6 py-4 flex flex-col sm:flex-row justify-between items-center">
    <h1 class="text-2xl font-bold text-white mb-4 sm:mb-0">My Symptom Logs</h1>
    <a href="{{ route('student.symptom-checker.index') }}" 
       class="bg-white text-blue-600 px-4 py-2 rounded-lg font-semibold hover:bg-blue-50 transition duration-200 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Use Symptom Checker
    </a>
</div>

            <div class="p-6">
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($logs->count() > 0)
                    <!-- Mobile Cards -->
                    <div class="sm:hidden space-y-4">
                        @foreach ($logs as $log)
                            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                <div class="flex justify-between items-start mb-3">
                                    <span class="text-sm text-gray-600">{{ $log->created_at->format('M d, Y h:i A') }}</span>
                                    @if ($log->is_emergency)
                                        <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Emergency</span>
                                    @endif
                                </div>
                                
                                <div class="mb-3">
                                    <p class="text-gray-800 font-medium">
                                        @if (is_array($log->symptoms))
                                            {{ implode(', ', $log->symptoms) }}
                                        @else
                                            {{ $log->symptoms }}
                                        @endif
                                    </p>
                                </div>
                                
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-sm text-gray-600">Duration: {{ $log->duration }}</span>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium
                                        @if($log->severity === 'mild') bg-green-100 text-green-800
                                        @elseif($log->severity === 'moderate') bg-yellow-100 text-yellow-800
                                        @elseif($log->severity === 'severe') bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($log->severity) }}
                                    </span>
                                </div>
                                
                                <!-- View Only Button -->
                                <div class="flex">
                                    <a href="{{ route('student.symptom-logs.show', $log->id) }}" 
                                       class="flex-1 bg-blue-600 text-white px-3 py-2 rounded text-sm font-medium hover:bg-blue-700 transition duration-200 text-center">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Symptoms</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emergency</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($logs as $log)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $log->created_at->format('M d, Y h:i A') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                            @if (is_array($log->symptoms))
                                                {{ implode(', ', $log->symptoms) }}
                                            @else
                                                {{ $log->symptoms }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                                @if($log->severity === 'mild') bg-green-100 text-green-800
                                                @elseif($log->severity === 'moderate') bg-yellow-100 text-yellow-800
                                                @elseif($log->severity === 'severe') bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst($log->severity) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $log->duration }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($log->is_emergency)
                                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Yes</span>
                                            @else
                                                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">No</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('student.symptom-logs.show', $log->id) }}" 
                                                   class="text-blue-600 hover:text-blue-900 transition duration-200 flex items-center" title="View Details">
                                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
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
                    <div class="mt-6">
                        {{ $logs->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="bg-blue-50 rounded-lg p-8 max-w-md mx-auto">
                            <svg class="w-16 h-16 text-blue-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No symptom logs yet</h3>
                            <p class="text-gray-600 mb-4">Start tracking your symptoms to monitor your health.</p>
                            <a href="{{ route('student.symptom-logs.create') }}" 
                               class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition duration-200 inline-block">
                                Log Your First Symptoms
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection