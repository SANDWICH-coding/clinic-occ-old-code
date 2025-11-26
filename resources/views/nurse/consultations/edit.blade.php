@extends('layouts.nurse-app')

@section('title', 'Edit Consultation')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Simplified Page Header -->
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Consultation</h1>
                <p class="mt-1 text-sm text-gray-500">Update consultation record for {{ $consultation->student->full_name }}</p>
            </div>
            <a href="{{ route('nurse.consultations.show', $consultation) }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-50 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Details
            </a>
        </div>
    </div>

    <!-- Form wrapping the entire content -->
    <form id="consultationForm" method="POST" action="{{ route('nurse.consultations.update', $consultation) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- LEFT COLUMN: Information Cards -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Student Information Card -->
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
                                        @if($consultation->student->date_of_birth)
                                            {{ \Carbon\Carbon::parse($consultation->student->date_of_birth)->age }} years
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Status</p>
                                    <p class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $consultation->status)) }}</p>
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
                @if($consultation->student->medicalRecord)
                @php
                    $medicalRecord = $consultation->student->medicalRecord;
                    $bmiData = ['bmi' => null, 'category' => null, 'status_class' => 'secondary'];
                    if ($medicalRecord && $medicalRecord->height && $medicalRecord->weight) {
                        $heightInMeters = $medicalRecord->height / 100;
                        $bmi = $medicalRecord->weight / ($heightInMeters * $heightInMeters);
                        $bmiData['bmi'] = round($bmi, 1);
                        
                        if ($bmi < 18.5) {
                            $bmiData['category'] = 'Underweight';
                            $bmiData['status_class'] = 'warning';
                        } elseif ($bmi >= 18.5 && $bmi < 25) {
                            $bmiData['category'] = 'Normal';
                            $bmiData['status_class'] = 'success';
                        } elseif ($bmi >= 25 && $bmi < 30) {
                            $bmiData['category'] = 'Overweight';
                            $bmiData['status_class'] = 'warning';
                        } else {
                            $bmiData['category'] = 'Obese';
                            $bmiData['status_class'] = 'danger';
                        }
                    }
                @endphp
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

            <!-- RIGHT COLUMN: Consultation Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Session Messages -->
                @if (session('error'))
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-red-700 font-medium">{!! session('error') !!}</span>
                        </div>
                    </div>
                @endif

                @if (session('success'))
                    <div class="p-4 bg-green-50 border border-green-200 rounded-xl">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-green-700 font-medium">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Chief Complaint & Symptoms -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white">Chief Complaint & Symptoms</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-5">
                            <!-- Priority and Pain Level -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority <span class="text-red-500">*</span></label>
                                    <select name="priority" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
                                        <option value="">Select Priority</option>
                                        <option value="critical" {{ $consultation->priority == 'critical' ? 'selected' : '' }}>Critical</option>
                                        <option value="high" {{ $consultation->priority == 'high' ? 'selected' : '' }}>High Priority</option>
                                        <option value="medium" {{ $consultation->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="low" {{ $consultation->priority == 'low' ? 'selected' : '' }}>Low Priority</option>
                                    </select>
                                    @error('priority')
                                        <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Pain Level (0-10)</label>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <input type="range" name="pain_level" id="painLevel" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer" min="0" max="10" value="{{ old('pain_level', $consultation->pain_level ?? 0) }}">
                                            <span id="painValue" class="ml-4 font-bold text-gray-800 min-w-8">{{ old('pain_level', $consultation->pain_level ?? 0) }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 flex justify-between">
                                            <span>No pain</span>
                                            <span>Severe pain</span>
                                        </div>
                                    </div>
                                    @error('pain_level')
                                        <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Initial Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Initial Notes</label>
                                <textarea name="initial_notes" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" rows="3" placeholder="Initial assessment notes and observations...">{{ old('initial_notes', $consultation->initial_notes) }}</textarea>
                                @error('initial_notes')
                                    <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Chief Complaint -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Chief Complaint <span class="text-red-500">*</span></label>
                                <textarea name="chief_complaint" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" rows="2" placeholder="Main reason for visit..." required>{{ old('chief_complaint', $consultation->chief_complaint) }}</textarea>
                                @error('chief_complaint')
                                    <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Symptoms Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Symptoms Description</label>
                                <textarea name="symptoms_description" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" rows="3" placeholder="Detailed description of symptoms, onset, duration, severity...">{{ old('symptoms_description', $consultation->symptoms_description) }}</textarea>
                                @error('symptoms_description')
                                    <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                                @enderror
                            </div>
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
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Temperature (°C)</label>
                                <input type="number" name="temperature" step="0.1" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" placeholder="36.6" value="{{ old('temperature', $consultation->temperature ?? '') }}">
                            </div>

                            <!-- Blood Pressure -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Blood Pressure</label>
                                <div class="flex items-center space-x-2">
                                    <input type="number" name="blood_pressure_systolic" class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" placeholder="120" value="{{ old('blood_pressure_systolic', $consultation->blood_pressure_systolic ?? '') }}">
                                    <span class="text-gray-500">/</span>
                                    <input type="number" name="blood_pressure_diastolic" class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" placeholder="80" value="{{ old('blood_pressure_diastolic', $consultation->blood_pressure_diastolic ?? '') }}">
                                    <span class="text-sm text-gray-500">mmHg</span>
                                </div>
                            </div>

                            <!-- Heart Rate -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Heart Rate</label>
                                <input type="number" name="heart_rate" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" placeholder="72" value="{{ old('heart_rate', $consultation->heart_rate ?? '') }}">
                                <div class="text-xs text-gray-500">BPM</div>
                            </div>

                            <!-- Oxygen Saturation -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">O₂ Saturation</label>
                                <input type="number" name="oxygen_saturation" step="0.1" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" placeholder="98" value="{{ old('oxygen_saturation', $consultation->oxygen_saturation ?? '') }}">
                                <div class="text-xs text-gray-500">%</div>
                            </div>

                            <!-- Respiratory Rate -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Respiratory Rate</label>
                                <input type="number" name="respiratory_rate" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" placeholder="16" value="{{ old('respiratory_rate', $consultation->respiratory_rate ?? '') }}">
                                <div class="text-xs text-gray-500">per min</div>
                            </div>

                            <!-- Weight -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Weight</label>
                                <input type="number" name="weight" step="0.1" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" placeholder="60" value="{{ old('weight', $consultation->weight ?? '') }}">
                                <div class="text-xs text-gray-500">kg</div>
                            </div>

                            <!-- Height -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Height</label>
                                <input type="number" name="height" step="0.1" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" placeholder="170" value="{{ old('height', $consultation->height ?? '') }}">
                                <div class="text-xs text-gray-500">cm</div>
                            </div>
                        </div>
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Diagnosis <span class="text-red-500">*</span></label>
                            <textarea name="diagnosis" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition" rows="2" placeholder="Primary diagnosis or assessment..." required>{{ old('diagnosis', $consultation->diagnosis) }}</textarea>
                            @error('diagnosis')
                                <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Treatment Provided -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Treatment Provided</label>
                            <textarea name="treatment_provided" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition" rows="2" placeholder="Describe treatment given during consultation...">{{ old('treatment_provided', $consultation->treatment_provided) }}</textarea>
                            @error('treatment_provided')
                                <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Medications Given -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Medications Given</label>
                            <textarea name="medications_given" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition" rows="2" placeholder="List medications administered with dosage and instructions...">{{ old('medications_given', $consultation->medications_given) }}</textarea>
                            @error('medications_given')
                                <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Procedures Performed -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Procedures Performed</label>
                            <textarea name="procedures_performed" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition" rows="2" placeholder="List any medical procedures performed...">{{ old('procedures_performed', $consultation->procedures_performed) }}</textarea>
                            @error('procedures_performed')
                                <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Home Care Instructions -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Home Care Instructions</label>
                            <textarea name="home_care_instructions" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition" rows="2" placeholder="Instructions for home care and follow-up...">{{ old('home_care_instructions', $consultation->home_care_instructions) }}</textarea>
                            @error('home_care_instructions')
                                <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white">Actions</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
                            <a href="{{ route('nurse.consultations.show', $consultation) }}" class="inline-flex items-center px-6 py-3 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 w-full sm:w-auto justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-8 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full sm:w-auto justify-center" id="submitBtn">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Update Consultation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('consultationForm');
    const painLevel = document.getElementById('painLevel');
    const painValue = document.getElementById('painValue');
    const submitBtn = document.getElementById('submitBtn');

    // Pain level slider
    painLevel.addEventListener('input', function() {
        painValue.textContent = this.value;
    });

    // Form submission handler
    form.addEventListener('submit', function(e) {
        // Validate required fields
        const chiefComplaint = form.querySelector('textarea[name="chief_complaint"]').value.trim();
        const diagnosis = form.querySelector('textarea[name="diagnosis"]').value.trim();
        const priority = form.querySelector('select[name="priority"]').value;

        if (!chiefComplaint || !diagnosis || !priority) {
            e.preventDefault();
            showAlert('Please fill in all required fields (Chief Complaint, Diagnosis, and Priority).', 'error');
            return false;
        }

        // Show loading state on submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Updating...
        `;
    });

    // Real-time validation feedback
    const requiredFields = [
        { element: form.querySelector('textarea[name="chief_complaint"]'), label: 'Chief Complaint' },
        { element: form.querySelector('textarea[name="diagnosis"]'), label: 'Diagnosis' },
        { element: form.querySelector('select[name="priority"]'), label: 'Priority' }
    ];

    requiredFields.forEach(field => {
        if (field.element) {
            field.element.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    this.classList.add('border-red-300');
                    this.classList.remove('border-gray-300');
                } else {
                    this.classList.remove('border-red-300');
                    this.classList.add('border-gray-300');
                }
            });

            field.element.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('border-red-300');
                    this.classList.add('border-gray-300');
                }
            });
        }
    });
});

// Function to show alerts
function showAlert(message, type = 'info') {
    // Remove any existing alerts
    const existingAlert = document.querySelector('.custom-alert');
    if (existingAlert) {
        existingAlert.remove();
    }

    const alertDiv = document.createElement('div');
    alertDiv.className = `custom-alert fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg transition-all duration-300 ${type === 'error' ? 'bg-red-100 border-l-4 border-red-500 text-red-700' : type === 'warning' ? 'bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700' : type === 'success' ? 'bg-green-100 border-l-4 border-green-500 text-green-700' : 'bg-blue-100 border-l-4 border-blue-500 text-blue-700'}`;
    
    alertDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'error' ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>' : 
                type === 'warning' ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>' : 
                type === 'success' ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>' : 
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'}
            </svg>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(alertDiv);

    // Auto remove after 5 seconds
    setTimeout(() => {
        alertDiv.classList.add('translate-x-full');
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 300);
    }, 5000);
}
</script>

<style>
.custom-alert {
    min-width: 300px;
    max-width: 500px;
}
</style>
@endsection