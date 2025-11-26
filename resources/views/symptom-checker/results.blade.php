@extends('layouts.app')

@section('title', 'Symptom Check Results')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Symptom Check Results</h1>
        <p class="text-gray-600">Based on your selected symptoms</p>
    </div>

    <!-- Results Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <!-- Selected Symptoms -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-3">Your Selected Symptoms:</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($selectedSymptoms as $symptom)
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                    {{ $symptom->name }}
                </span>
                @endforeach
            </div>
        </div>

        <!-- Emergency Alert -->
        @if($isEmergency)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="h-6 w-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h3 class="text-lg font-semibold text-red-800">Emergency Alert</h3>
            </div>
            <p class="mt-2 text-red-700">
                You have selected symptoms that may indicate a medical emergency. 
                Please seek immediate medical attention or contact campus security at 
                <strong>(063) 123-4567</strong>.
            </p>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('student.symptom-checker.index') }}" 
               class="flex-1 bg-gray-600 text-white py-2 px-4 rounded-lg hover:bg-gray-700 text-center transition-colors duration-200">
                Check Again
            </a>
            
            @if($possibleIllnesses->count() > 0)
            <a href="{{ route('student.appointments.create') }}" 
               class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 text-center transition-colors duration-200">
                Schedule Appointment
            </a>
            @endif
        </div>
    </div>

    <!-- Important Notice -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <p class="text-red-800 font-medium">⚠️ Important Medical Disclaimer</p>
        <p class="text-red-700 text-sm mt-1">
            This is not a medical diagnosis. The results provided are for informational purposes only 
            and should not be considered medical advice. Always consult with a qualified healthcare 
            professional for proper evaluation and treatment.
        </p>
    </div>
</div>
@endsection