@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 px-6 py-4">
                <div class="flex flex-col sm:flex-row justify-between items-center">
                    <h1 class="text-2xl font-bold text-white mb-4 sm:mb-0">Symptom Log Details</h1>
                    <a href="{{ route('student.symptom-logs.index') }}" 
                       class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to List
                    </a>
                </div>
                <p class="text-blue-100 mt-2">{{ $symptomLog->created_at->format('F j, Y \a\t g:i A') }}</p>
            </div>

            <div class="p-6">
                <!-- Emergency Alert -->
                @if ($symptomLog->is_emergency)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span class="text-red-800 font-semibold">Emergency Case - Please seek medical attention</span>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Symptoms -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            Symptoms
                        </h3>
                        @if (is_array($symptomLog->symptoms))
                            <ul class="space-y-1">
                                @foreach ($symptomLog->symptoms as $symptom)
                                    <li class="text-gray-700 flex items-center">
                                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{ $symptom }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-700">{{ $symptomLog->symptoms }}</p>
                        @endif
                    </div>

                    <!-- Severity & Duration -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Details
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-gray-600">Severity:</span>
                                <span class="px-3 py-1 rounded-full text-sm font-medium ml-2
                                    @if($symptomLog->severity === 'mild') bg-green-100 text-green-800
                                    @elseif($symptomLog->severity === 'moderate') bg-yellow-100 text-yellow-800
                                    @elseif($symptomLog->severity === 'severe') bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($symptomLog->severity) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Duration:</span>
                                <span class="text-gray-900 font-medium ml-2">{{ $symptomLog->duration }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Emergency:</span>
                                <span class="ml-2">
                                    @if ($symptomLog->is_emergency)
                                        <span class="bg-red-100 text-red-800 text-sm font-medium px-2.5 py-0.5 rounded-full">Yes</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-800 text-sm font-medium px-2.5 py-0.5 rounded-full">No</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if ($symptomLog->notes)
                    <div class="bg-gray-50 rounded-lg p-4 md:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Additional Notes
                        </h3>
                        <p class="text-gray-700 bg-white p-4 rounded-lg border border-gray-200">
                            {{ $symptomLog->notes }}
                        </p>
                    </div>
                    @endif
                </div>

                <!-- Back Button Only -->
                <div class="flex justify-center mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('student.symptom-logs.index') }}" 
                       class="bg-blue-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-blue-700 transition duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Symptom Logs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection