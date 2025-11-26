{{-- resources/views/nurse/medical-records/show.blade.php --}}
@extends('layouts.nurse-app')

@section('title', 'Medical Record - ' . $medicalRecord->user->full_name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Medical Record</h1>
                <p class="text-gray-600 mt-1">Viewing medical record for {{ $medicalRecord->user->full_name }}</p>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center mb-6">
        <div class="flex items-center text-sm text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Last updated: {{ $medicalRecord->updated_at->format('F j, Y \a\t g:i A') }}
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('nurse.medical-records.edit', $medicalRecord) }}"
               class="inline-flex items-center px-5 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors duration-200 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Record
            </a>
            <a href="{{ route('nurse.medical-records.index') }}"
               class="inline-flex items-center px-5 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Records
            </a>
        </div>
    </div>

    <!-- Patient Information & Basic Medical Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Patient Information Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-blue-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Patient Information
                </h2>
            </div>
            <div class="p-6">
                <dl class="space-y-4 text-gray-700">
                    <div class="flex">
                        <dt class="font-medium w-1/3 text-gray-500">Name:</dt>
                        <dd class="w-2/3 font-medium">{{ $medicalRecord->user->full_name }}</dd>
                    </div>
                    <div class="flex">
                        <dt class="font-medium w-1/3 text-gray-500">Student ID:</dt>
                        <dd class="w-2/3">{{ $medicalRecord->user->student_id }}</dd>
                    </div>
                    <div class="flex">
                        <dt class="font-medium w-1/3 text-gray-500">Course:</dt>
                        <dd class="w-2/3">{{ $medicalRecord->user->course ?? 'N/A' }}</dd>
                    </div>
                    <div class="flex">
                        <dt class="font-medium w-1/3 text-gray-500">Age:</dt>
                        <dd class="w-2/3">{{ $medicalRecord->user->age ?? 'N/A' }} years old</dd>
                    </div>
                    <div class="flex">
                        <dt class="font-medium w-1/3 text-gray-500">Gender:</dt>
                        <dd class="w-2/3">{{ ucfirst($medicalRecord->user->gender ?? 'N/A') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Basic Medical Info Card -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-blue-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    Basic Medical Information
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <dl class="space-y-4 text-gray-700">
                        <div class="flex">
                            <dt class="font-medium w-1/3 text-gray-500">Blood Type:</dt>
                            <dd class="w-2/3">
                                @if($medicalRecord->blood_type)
                                    <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-sm font-medium">
                                        {{ $medicalRecord->blood_type }}
                                    </span>
                                @else
                                    <span class="text-gray-400">Not specified</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium w-1/3 text-gray-500">Height:</dt>
                            <dd class="w-2/3">{{ $medicalRecord->height ? $medicalRecord->height . ' cm' : 'N/A' }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium w-1/3 text-gray-500">Weight:</dt>
                            <dd class="w-2/3">{{ $medicalRecord->weight ? $medicalRecord->weight . ' kg' : 'N/A' }}</dd>
                        </div>
                    </dl>
                    @if($medicalRecord->calculateBMI())
                        <div class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <h4 class="text-lg font-bold text-gray-800 mb-2">Body Mass Index</h4>
                            <div class="text-3xl font-bold text-blue-600 mb-2">{{ $medicalRecord->calculateBMI() }}</div>
                            <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-sm font-medium">
                                {{ $medicalRecord->getBMICategory() }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Medical History Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="bg-purple-600 px-6 py-4">
            <h2 class="text-xl font-semibold text-white flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Medical History
            </h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- General Health Column -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-4 border-b border-gray-200 pb-2">General Health</h3>
                    <div class="space-y-4">
                        <!-- Pregnancy Status -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-700 font-medium">Has been pregnant</span>
                            <div class="flex items-center">
                                @if($medicalRecord->has_been_pregnant)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Yes
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        No
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Surgery Status -->
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-700 font-medium">Has undergone surgery</span>
                                <div class="flex items-center">
                                    @if($medicalRecord->has_undergone_surgery)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Yes
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            No
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($medicalRecord->has_undergone_surgery && $medicalRecord->surgery_details)
                                <div class="mt-2 text-sm text-gray-600 p-3 bg-blue-50 rounded-lg border-l-4 border-blue-400">
                                    <strong class="text-blue-800">Surgery Details:</strong>
                                    <p class="mt-1">{{ $medicalRecord->surgery_details }}</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Maintenance Drugs -->
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-700 font-medium">Taking maintenance drugs</span>
                                <div class="flex items-center">
                                    @if($medicalRecord->is_taking_maintenance_drugs)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Yes
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            No
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($medicalRecord->is_taking_maintenance_drugs && $medicalRecord->maintenance_drugs_specify)
                                <div class="mt-2 text-sm text-gray-600 p-3 bg-green-50 rounded-lg border-l-4 border-green-400">
                                    <strong class="text-green-800">Medications:</strong>
                                    <p class="mt-1">{{ $medicalRecord->maintenance_drugs_specify }}</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Hospitalization -->
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-700 font-medium">Hospitalized in last 6 months</span>
                                <div class="flex items-center">
                                    @if($medicalRecord->has_been_hospitalized_6_months)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Yes
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            No
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($medicalRecord->has_been_hospitalized_6_months && $medicalRecord->hospitalization_details_6_months)
                                <div class="mt-2 text-sm text-gray-600 p-3 bg-yellow-50 rounded-lg border-l-4 border-yellow-400">
                                    <strong class="text-yellow-800">Hospitalization Details:</strong>
                                    <p class="mt-1">{{ $medicalRecord->hospitalization_details_6_months }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Special Conditions Column -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-4 border-b border-gray-200 pb-2">Special Conditions</h3>
                    <div class="space-y-4">
                        <!-- PWD Status -->
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-700 font-medium">Person with Disability (PWD)</span>
                                <div class="flex items-center">
                                    @if($medicalRecord->is_pwd)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Yes
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            No
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($medicalRecord->is_pwd)
                                @if($medicalRecord->pwd_id)
                                    <p class="text-sm text-gray-600 mt-2">
                                        <strong>PWD ID:</strong> {{ $medicalRecord->pwd_id }}
                                    </p>
                                @endif
                                @if($medicalRecord->pwd_disability_details)
                                    <div class="mt-2 text-sm text-gray-600 p-3 bg-indigo-50 rounded-lg border-l-4 border-indigo-400">
                                        <strong class="text-indigo-800">Disability Details:</strong>
                                        <p class="mt-1">{{ $medicalRecord->pwd_disability_details }}</p>
                                    </div>
                                @endif
                                @if($medicalRecord->pwd_reason)
                                    <div class="mt-2 text-sm text-gray-600 p-3 bg-indigo-50 rounded-lg border-l-4 border-indigo-400">
                                        <strong class="text-indigo-800">Reason:</strong>
                                        <p class="mt-1">{{ $medicalRecord->pwd_reason }}</p>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    
                    <!-- Allergies Alert -->
                    @if($medicalRecord->allergies && $medicalRecord->allergies !== 'none' && $medicalRecord->allergies !== 'None')
                        <div class="mt-6 p-4 bg-red-50 border-l-4 border-red-400 rounded-lg">
                            <div class="flex items-center mb-2">
                                <svg class="h-5 w-5 text-red-600 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                                </svg>
                                <h4 class="font-medium text-red-800">Allergies</h4>
                            </div>
                            <p class="text-sm text-red-700">{{ $medicalRecord->allergies }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Past Illnesses -->
            @if($medicalRecord->past_illnesses && $medicalRecord->past_illnesses !== 'none' && $medicalRecord->past_illnesses !== 'None')
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Past Illnesses</h3>
                    <div class="p-4 bg-purple-50 border-l-4 border-purple-400 rounded-lg">
                        <p class="text-purple-700">{{ $medicalRecord->past_illnesses }}</p>
                    </div>
                </div>
            @endif

            <!-- Health Notes -->
            @if($medicalRecord->notes_health_problems)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Health Notes</h3>
                    <div class="p-4 bg-blue-50 border-l-4 border-blue-400 rounded-lg">
                        <p class="text-blue-700">{{ $medicalRecord->notes_health_problems }}</p>
                    </div>
                </div>
            @endif

            <!-- Family History -->
            @if($medicalRecord->family_history_details)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Family History</h3>
                    <div class="p-4 bg-purple-50 border-l-4 border-purple-400 rounded-lg">
                        <p class="text-purple-700">{{ $medicalRecord->family_history_details }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Vaccination Information Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="bg-indigo-600 px-6 py-4">
            <h2 class="text-xl font-semibold text-white flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Vaccination Information
            </h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Primary Vaccination -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-4 border-b border-gray-200 pb-2">Primary Vaccination</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-700 font-medium">Vaccination Status</span>
                            <div class="flex items-center">
                                @if($medicalRecord->is_fully_vaccinated)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Fully Vaccinated
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Not Vaccinated
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if($medicalRecord->is_fully_vaccinated)
                            <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-400">
                                @if($medicalRecord->vaccine_name || $medicalRecord->vaccine_type)
                                    <div class="mb-3">
                                        <span class="text-sm text-gray-600 block mb-1">Vaccine Type</span>
                                        <span class="text-sm font-medium text-green-900">{{ $medicalRecord->vaccine_name ?? $medicalRecord->vaccine_type }}</span>
                                    </div>
                                @endif
                                @if($medicalRecord->vaccine_date)
                                    <div class="mb-3">
                                        <span class="text-sm text-gray-600 block mb-1">Vaccination Date</span>
                                        <span class="text-sm font-medium text-green-900">{{ \Carbon\Carbon::parse($medicalRecord->vaccine_date)->format('M d, Y') }}</span>
                                    </div>
                                @endif
                                @if($medicalRecord->number_of_doses)
                                    <div>
                                        <span class="text-sm text-gray-600 block mb-1">Number of Doses</span>
                                        <span class="text-sm font-medium text-green-900">{{ $medicalRecord->number_of_doses }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Booster Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-4 border-b border-gray-200 pb-2">Booster Information</h3>
                    @php
                        // Check if there's actually booster data
                        $hasBoosterData = !empty($medicalRecord->booster_type) || 
                                         ($medicalRecord->number_of_boosters && $medicalRecord->number_of_boosters !== 'None');
                    @endphp
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-700 font-medium">Booster Status</span>
                            <div class="flex items-center">
                                @if($hasBoosterData)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Received Booster
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        No Booster Yet
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        @if($hasBoosterData)
                            <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-400">
                                @if($medicalRecord->booster_type)
                                    <div class="mb-3">
                                        <span class="text-sm text-gray-600 block mb-1">Booster Type</span>
                                        <span class="text-sm font-medium text-green-900">{{ $medicalRecord->booster_type }}</span>
                                    </div>
                                @endif
                                
                                @if($medicalRecord->number_of_boosters && $medicalRecord->number_of_boosters !== 'None')
                                    <div>
                                        <span class="text-sm text-gray-600 block mb-1">Number of Boosters</span>
                                        <span class="text-sm font-medium text-green-900">{{ $medicalRecord->number_of_boosters }}</span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-gray-300">
                                <div class="flex items-center text-gray-500">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0118 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/>
                                    </svg>
                                    <span class="text-sm italic">No booster shots recorded</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Emergency Contacts Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="bg-red-600 px-6 py-4">
            <h2 class="text-xl font-semibold text-white flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                Emergency Contacts
            </h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Primary Contact -->
                <div class="bg-red-50 rounded-lg p-5 border-l-4 border-red-400">
                    <h3 class="text-lg font-medium text-red-800 mb-3">Primary Contact</h3>
                    <div class="space-y-3">
                        <p class="text-gray-800 text-lg font-semibold">
                            {{ $medicalRecord->emergency_contact_name_1 }}
                        </p>
                        @if($medicalRecord->emergency_contact_relationship_1)
                            <p class="text-sm">
                                <span class="inline-block px-3 py-1 bg-red-200 text-red-900 rounded-full font-medium">
                                    {{ ucfirst($medicalRecord->emergency_contact_relationship_1) }}
                                </span>
                            </p>
                        @endif
                        <p class="flex items-center text-gray-700 font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            {{ $medicalRecord->emergency_contact_number_1 }}
                        </p>
                    </div>
                </div>
                
                <!-- Secondary Contact -->
                @if($medicalRecord->emergency_contact_name_2)
                    <div class="bg-blue-50 rounded-lg p-5 border-l-4 border-blue-400">
                        <h3 class="text-lg font-medium text-blue-800 mb-3">Secondary Contact</h3>
                        <div class="space-y-3">
                            <p class="text-gray-800 text-lg font-semibold">
                                {{ $medicalRecord->emergency_contact_name_2 }}
                            </p>
                            @if($medicalRecord->emergency_contact_relationship_2)
                                <p class="text-sm">
                                    <span class="inline-block px-3 py-1 bg-blue-200 text-blue-900 rounded-full font-medium">
                                        {{ ucfirst($medicalRecord->emergency_contact_relationship_2) }}
                                    </span>
                                </p>
                            @endif
                            <p class="flex items-center text-gray-700 font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                {{ $medicalRecord->emergency_contact_number_2 }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="bg-gray-50 rounded-lg p-5 border-l-4 border-gray-300">
                        <h3 class="text-lg font-medium text-gray-700 mb-3">Secondary Contact</h3>
                        <div class="text-center text-gray-500 py-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            <p class="text-sm">No secondary contact provided</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Record Metadata Card -->
    <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
        <h3 class="text-lg font-medium text-gray-700 mb-4">Record Information</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm text-gray-600">
            <div class="space-y-3">
                <p class="flex items-center">
                    <svg class="w-4 h-4 mr-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                    </svg>
                    <span><strong>Created by:</strong> {{ $medicalRecord->createdBy->full_name ?? 'System' }}</span>
                </p>
                <p class="flex items-center">
                    <svg class="w-4 h-4 mr-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                    </svg>
                    <span><strong>Created:</strong> {{ $medicalRecord->created_at->format('F j, Y \a\t g:i A') }}</span>
                </p>
            </div>
            <div class="space-y-3">
                <p class="flex items-center">
                    <svg class="w-4 h-4 mr-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                    </svg>
                    <span><strong>Last updated:</strong> {{ $medicalRecord->updated_at->format('F j, Y \a\t g:i A') }}</span>
                </p>
                @if($medicalRecord->updated_at->diffInDays($medicalRecord->created_at) > 0)
                    <p class="flex items-center text-blue-600 font-medium">
                        <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0118 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                        </svg>
                        Modified {{ $medicalRecord->updated_at->diffForHumans() }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection