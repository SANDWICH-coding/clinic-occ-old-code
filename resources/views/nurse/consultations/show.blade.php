@extends('layouts.nurse-app')

@section('title', 'Consultation Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Simplified Page Header -->
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Consultation Details</h1>
                <p class="mt-1 text-sm text-gray-500">Consultation record for {{ $consultation->student->full_name }}</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                    @if($consultation->status === 'completed') bg-green-100 text-green-800
                    @elseif($consultation->status === 'in_progress') bg-blue-100 text-blue-800
                    @elseif($consultation->status === 'registered') bg-yellow-100 text-yellow-800
                    @elseif($consultation->status === 'cancelled') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst(str_replace('_', ' ', $consultation->status)) }}
                </span>
                <a href="{{ route('nurse.consultations.edit', $consultation) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </a>
                <a href="{{ route('nurse.consultations.index') }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-50 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Patient & Medical Info -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Patient Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-5 py-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-lg font-semibold text-white">{{ $consultation->student->full_name }}</h2>
                            <p class="text-blue-100 text-sm">ID: {{ $consultation->student->student_id }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 gap-3">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Email</p>
                                <p class="text-sm text-gray-900">{{ $consultation->student->email ?? 'No record' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Phone</p>
                                <p class="text-sm text-gray-900">{{ $consultation->student->phone ?? 'No record' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Course & Year</p>
                                <p class="text-sm text-gray-900">{{ $consultation->student->course ?? 'N/A' }} - {{ $consultation->student->year_level ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Section</p>
                                <p class="text-sm text-gray-900">{{ $consultation->student->section ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Gender</p>
                                <p class="text-sm text-gray-900">{{ $consultation->student->gender ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Age</p>
                                <p class="text-sm text-gray-900">
                                    @php
                                        $age = null;
                                        if ($consultation->student->date_of_birth) {
                                            try {
                                                $age = \Carbon\Carbon::parse($consultation->student->date_of_birth)->age;
                                            } catch (\Exception $e) {
                                                $age = null;
                                            }
                                        }
                                    @endphp
                                    @if($age)
                                        {{ $age }} years
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($consultation->student->address)
                        <div class="flex items-start pt-2 border-t border-gray-100">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Address</p>
                                <p class="text-sm text-gray-900">{{ $consultation->student->address }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Consultation Details Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-5 py-4">
                    <h2 class="text-lg font-semibold text-white">Consultation Details</h2>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 gap-3">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Consultation ID</p>
                                <p class="text-sm font-mono text-gray-900">#{{ $consultation->id }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Date & Time</p>
                                <p class="text-sm text-gray-900">{{ $consultation->consultation_date->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Type</p>
                                <p class="text-sm text-gray-900">
                                    @if($consultation->type === 'walk_in')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Walk-in</span>
                                    @elseif($consultation->type === 'appointment')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Appointment</span>
                                    @else
                                        {{ $consultation->type }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Priority</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($consultation->priority === 'critical') bg-red-100 text-red-800
                                    @elseif($consultation->priority === 'high') bg-orange-100 text-orange-800
                                    @elseif($consultation->priority === 'medium') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800 @endif">
                                    @if($consultation->priority === 'critical') Critical
                                    @elseif($consultation->priority === 'high') High
                                    @elseif($consultation->priority === 'medium') Medium
                                    @elseif($consultation->priority === 'low') Low
                                    @else {{ $consultation->priority }} @endif
                                </span>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Pain Level</p>
                                <div class="flex items-center">
                                    @if($consultation->pain_level > 0)
                                        <div class="w-24 bg-gray-200 rounded-full h-2.5 mr-2">
                                            <div class="bg-red-600 h-2.5 rounded-full" style="width: {{ ($consultation->pain_level / 10) * 100 }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $consultation->pain_level }}/10</span>
                                    @else
                                        <span class="text-sm text-gray-900">No pain</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($consultation->nurse)
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Attending Nurse</p>
                                <p class="text-sm text-gray-900">{{ $consultation->nurse->full_name }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Medical Summary Card -->
            @if($medicalRecord)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-5 py-4">
                    <h2 class="text-lg font-semibold text-white">Medical Summary</h2>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Basic Medical Info -->
                        <div class="space-y-3">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Basic Information</h3>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Blood Type</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $medicalRecord->blood_type ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Height/Weight</p>
                                    <p class="text-sm font-medium text-gray-900">
                                        @if($medicalRecord->height && $medicalRecord->weight)
                                            {{ $medicalRecord->height }}cm / {{ $medicalRecord->weight }}kg
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if($bmiData['bmi'])
                            <div>
                                <p class="text-xs font-medium text-gray-500">BMI</p>
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">{{ $bmiData['bmi'] }}</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $bmiData['status_class'] == 'success' ? 'bg-green-100 text-green-800' : ($bmiData['status_class'] == 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $bmiData['category'] }}
                                    </span>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Medical History -->
                        <div class="space-y-3">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Medical History</h3>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Allergies</p>
                                <p class="text-sm text-gray-900">
                                    @if($medicalRecord->allergies)
                                        {{ $medicalRecord->allergies }}
                                    @else
                                        <span class="text-gray-400">None reported</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Chronic Conditions</p>
                                <p class="text-sm text-gray-900">
                                    @if($medicalRecord->chronic_conditions)
                                        {{ $medicalRecord->chronic_conditions }}
                                    @else
                                        <span class="text-gray-400">None reported</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Vaccination Status -->
                        @if($medicalRecord->is_fully_vaccinated || $medicalRecord->vaccine_type)
                        <div class="space-y-3">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Vaccination Status</h3>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Status</p>
                                    <p class="text-sm text-gray-900">
                                        @if($medicalRecord->is_fully_vaccinated)
                                            Fully Vaccinated
                                        @else
                                            Partially Vaccinated
                                        @endif
                                    </p>
                                </div>
                                @if($medicalRecord->has_received_booster)
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Booster</p>
                                    <p class="text-sm text-gray-900">Received</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- PWD Status -->
                        @if($medicalRecord->is_pwd)
                        <div class="space-y-3">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">PWD Status</h3>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <p class="text-sm text-gray-900">Registered Person with Disability</p>
                            </div>
                        </div>
                        @endif

                        <!-- Emergency Contacts -->
                        @if(count($emergencyContacts) > 0)
                        <div class="space-y-3 pt-3 border-t border-gray-100">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Emergency Contacts</h3>
                            <div class="space-y-2">
                                @foreach($emergencyContacts as $contact)
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $contact['name'] }}</p>
                                            <p class="text-xs text-gray-600">{{ $contact['relationship'] }}</p>
                                        </div>
                                        <a href="tel:{{ $contact['phone'] }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            {{ $contact['phone'] }}
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-500 to-gray-600 px-5 py-4">
                    <h2 class="text-lg font-semibold text-white">Medical Summary</h2>
                </div>
                <div class="p-8 text-center">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500">No medical record found for this student</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column - Clinical Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Chief Complaint & Symptoms -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white">Chief Complaint & Symptoms</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-5">
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Chief Complaint</h3>
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                <p class="text-gray-800">{{ $consultation->chief_complaint }}</p>
                            </div>
                        </div>
                        
                        @if($consultation->symptoms_description)
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Symptoms Description</h3>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                                <p class="text-gray-800">{{ $consultation->symptoms_description }}</p>
                            </div>
                        </div>
                        @endif

                        @if($consultation->initial_notes)
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Initial Notes</h3>
                            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100">
                                <p class="text-gray-800">{{ $consultation->initial_notes }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Vital Signs -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white">Vital Signs</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Temperature -->
                        <div class="bg-white border border-purple-100 rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Temperature</span>
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900">
                                @if($consultation->temperature)
                                    {{ $consultation->temperature }}°C
                                @else
                                    <span class="text-gray-400 text-base font-normal">Not recorded</span>
                                @endif
                            </p>
                        </div>

                        <!-- Blood Pressure -->
                        <div class="bg-white border border-red-100 rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Blood Pressure</span>
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900">
                                @if($consultation->blood_pressure_systolic && $consultation->blood_pressure_diastolic)
                                    {{ $consultation->blood_pressure_systolic }}/{{ $consultation->blood_pressure_diastolic }} mmHg
                                @else
                                    <span class="text-gray-400 text-base font-normal">Not recorded</span>
                                @endif
                            </p>
                        </div>

                        <!-- Heart Rate -->
                        <div class="bg-white border border-pink-100 rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Heart Rate</span>
                                <div class="w-8 h-8 bg-pink-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900">
                                @if($consultation->heart_rate)
                                    {{ $consultation->heart_rate }} BPM
                                @else
                                    <span class="text-gray-400 text-base font-normal">Not recorded</span>
                                @endif
                            </p>
                        </div>

                        <!-- Oxygen Saturation -->
                        <div class="bg-white border border-blue-100 rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Oxygen Saturation</span>
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900">
                                @if($consultation->oxygen_saturation)
                                    {{ $consultation->oxygen_saturation }}%
                                @else
                                    <span class="text-gray-400 text-base font-normal">Not recorded</span>
                                @endif
                            </p>
                        </div>

                        <!-- Respiratory Rate -->
                        <div class="bg-white border border-green-100 rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Respiratory Rate</span>
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-gray-900">
                                @if($consultation->respiratory_rate)
                                    {{ $consultation->respiratory_rate }} /min
                                @else
                                    <span class="text-gray-400 text-base font-normal">Not recorded</span>
                                @endif
                            </p>
                        </div>

                        <!-- Height & Weight -->
                        <div class="bg-white border border-indigo-100 rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Height & Weight</span>
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-xl font-bold text-gray-900">
                                @if($vitalSigns['height'] && $vitalSigns['weight'])
                                    {{ $vitalSigns['height'] }}cm / {{ $vitalSigns['weight'] }}kg
                                @else
                                    <span class="text-gray-400 text-base font-normal">Not recorded</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- BMI Calculation if height and weight are available -->
                    @if($vitalSigns['height'] && $vitalSigns['weight'])
                        @php
                            $heightInMeters = $vitalSigns['height'] / 100;
                            $bmi = $vitalSigns['weight'] / ($heightInMeters * $heightInMeters);
                            $bmiCategory = '';
                            $bmiClass = '';
                            
                            if ($bmi < 18.5) {
                                $bmiCategory = 'Underweight';
                                $bmiClass = 'bg-yellow-100 text-yellow-800';
                            } elseif ($bmi >= 18.5 && $bmi < 25) {
                                $bmiCategory = 'Normal';
                                $bmiClass = 'bg-green-100 text-green-800';
                            } elseif ($bmi >= 25 && $bmi < 30) {
                                $bmiCategory = 'Overweight';
                                $bmiClass = 'bg-orange-100 text-orange-800';
                            } else {
                                $bmiCategory = 'Obese';
                                $bmiClass = 'bg-red-100 text-red-800';
                            }
                        @endphp
                        
                        <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                                <div class="mb-2 sm:mb-0">
                                    <p class="text-sm font-medium text-gray-700">Body Mass Index (BMI)</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ number_format($bmi, 1) }}</p>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $bmiClass }}">
                                    {{ $bmiCategory }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Diagnosis & Treatment -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white">Diagnosis & Treatment</h2>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Diagnosis -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Diagnosis</h3>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                            <p class="text-gray-800">{{ $consultation->diagnosis }}</p>
                        </div>
                    </div>

                    <!-- Treatment Provided -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Treatment Provided</h3>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                            <p class="text-gray-800">{{ $consultation->treatment_provided }}</p>
                        </div>
                    </div>

                    <!-- Medications Given -->
                    @if($consultation->medications_given)
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Medications Given</h3>
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <p class="text-gray-800">{{ $consultation->medications_given }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Procedures Performed -->
                    @if($consultation->procedures_performed)
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Procedures Performed</h3>
                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-100">
                            <p class="text-gray-800">{{ $consultation->procedures_performed }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Home Care Instructions -->
                    @if($consultation->home_care_instructions)
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Home Care Instructions</h3>
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100">
                            <p class="text-gray-800">{{ $consultation->home_care_instructions }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection