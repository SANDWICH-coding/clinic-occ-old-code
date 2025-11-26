@extends('layouts.app')

@section('title', 'Symptom Check Details')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Symptom Check Details</h1>
            <p class="text-gray-600">{{ $log->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('student.symptom-checker.history') }}" 
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                Back to History
            </a>
            <a href="{{ route('student.symptom-checker.index') }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                New Check
            </a>
        </div>
    </div>

    <!-- Emergency Alert -->
    @if($log->is_emergency)
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <svg class="h-6 w-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h3 class="text-lg font-semibold text-red-800">Emergency Symptoms Detected</h3>
        </div>
        <p class="mt-2 text-red-700">
            This symptom check indicated potential emergency symptoms. If you haven't already, 
            please seek immediate medical attention.
        </p>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Selected Symptoms -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <svg class="inline h-5 w-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Selected Symptoms ({{ count($log->symptoms) }})
            </h2>
            <div class="space-y-2">
                @foreach($log->symptoms as $symptom)
                <div class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="w-2 h-2 bg-blue-600 rounded-full mr-3"></div>
                    <span class="text-gray-800">{{ $symptom }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Possible Conditions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <svg class="inline h-5 w-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                Possible Conditions
            </h2>
            @if(!empty($log->possible_illnesses) && count($log->possible_illnesses) > 0)
                <div class="space-y-3">
                    @foreach($log->possible_illnesses as $illness)
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <h3 class="font-semibold text-yellow-800">{{ $illness }}</h3>
                        <p class="text-sm text-yellow-700 mt-1">
                            Identified based on your selected symptoms
                        </p>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <p class="text-gray-600">No specific conditions were identified based on the selected symptoms.</p>
                    <p class="text-sm text-gray-500 mt-2">
                        This doesn't mean there's nothing wrong. Please consult with a healthcare professional if symptoms persist.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recommendations -->
    <div class="mt-6 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">
            <svg class="inline h-5 w-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Recommendations
        </h2>
        
        @if($log->is_emergency)
            <div class="bg-red-100 border border-red-300 rounded-lg p-4 mb-4">
                <p class="text-red-800 font-medium">🚨 Seek immediate medical attention</p>
                <p class="text-red-700 text-sm mt-1">Contact emergency services or visit the nearest emergency room.</p>
            </div>
        @elseif(!empty($log->possible_illnesses) && count($log->possible_illnesses) > 0)
            <div class="bg-blue-100 border border-blue-300 rounded-lg p-4 mb-4">
                <p class="text-blue-800 font-medium">📋 Schedule an appointment</p>
                <p class="text-blue-700 text-sm mt-1">Consider scheduling an appointment with the campus clinic for proper evaluation.</p>
            </div>
        @else
            <div class="bg-gray-100 border border-gray-300 rounded-lg p-4 mb-4">
                <p class="text-gray-800 font-medium">👩‍⚕️ Monitor your symptoms</p>
                <p class="text-gray-700 text-sm mt-1">Keep track of your symptoms and consult a healthcare professional if they worsen or persist.</p>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 mt-4">
            @if(!$log->is_emergency && !empty($log->possible_illnesses) && count($log->possible_illnesses) > 0)
            <a href="{{ route('student.appointments.create') }}" 
               class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 text-center transition-colors duration-200">
                Schedule Appointment
            </a>
            @endif
            
            <a href="{{ route('student.symptom-checker.index') }}" 
               class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 text-center transition-colors duration-200">
                Run New Check
            </a>
        </div>
    </div>

    <!-- Medical Disclaimer -->
    <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
        <p class="text-red-800 font-medium">⚠️ Important Medical Disclaimer</p>
        <p class="text-red-700 text-sm mt-1">
            This symptom checker provides general information only and is not a substitute for professional medical advice, 
            diagnosis, or treatment. Always consult with a qualified healthcare professional for proper evaluation and treatment.
        </p>
    </div>
</div>
@endsection