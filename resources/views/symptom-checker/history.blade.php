@extends('layouts.app')

@section('title', 'Symptom Check History')

@section('content')
<div class="max-w-6xl mx-auto py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Symptom Check History</h1>
            <p class="text-gray-600">Your previous symptom checks and results</p>
        </div>
        <a href="{{ route('student.symptom-checker.index') }}" 
           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
            New Check
        </a>
    </div>

    <!-- History Table -->
    @if($logs->count() > 0)
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Symptoms</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Possible Conditions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emergency</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($logs as $log)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $log->created_at->format('M d, Y') }}</div>
                            <div class="text-sm text-gray-500">{{ $log->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($log->symptoms, 0, 3) as $symptom)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                    {{ $symptom }}
                                </span>
                                @endforeach
                                @if(count($log->symptoms) > 3)
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">
                                    +{{ count($log->symptoms) - 3 }} more
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if(!empty($log->possible_illnesses))
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($log->possible_illnesses, 0, 2) as $illness)
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">
                                    {{ $illness }}
                                </span>
                                @endforeach
                                @if(count($log->possible_illnesses) > 2)
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">
                                    +{{ count($log->possible_illnesses) - 2 }} more
                                </span>
                                @endif
                            </div>
                            @else
                            <span class="text-gray-500 text-sm">No conditions identified</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($log->is_emergency)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Emergency
                            </span>
                            @else
                            <span class="text-gray-500 text-sm">No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('student.symptom-checker.show-log', $log->id) }}" 
                               class="text-blue-600 hover:text-blue-900 mr-3">View Details</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50">
            {{ $logs->links() }}
        </div>
    </div>
    @else
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No symptom checks yet</h3>
        <p class="text-gray-500 mb-4">Start by checking your symptoms to track your health concerns.</p>
        <a href="{{ route('student.symptom-checker.index') }}" 
           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
            Check Symptoms
        </a>
    </div>
    @endif
</div>
@endsection