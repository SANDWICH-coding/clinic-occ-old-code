{{-- resources/views/nurse/students/comprehensive-view.blade.php --}}
@extends('layouts.nurse-app')

@section('title', 'Student Record - ' . $student->full_name)

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header with Back Button --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Student Comprehensive Record
            </h1>
            <p class="text-gray-600">Complete profile and medical history</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('nurse.medical-records.index') }}" 
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Records
            </a>
            <a href="{{ route('nurse.medical-records.edit', $medicalRecord) }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Record
            </a>
            <button onclick="window.print()" 
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
            </button>
        </div>
    </div>

    {{-- Student Profile Section --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <div class="flex items-center mb-4">
            <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xl font-bold mr-4">
                {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $student->full_name }}</h2>
                <p class="text-gray-600">Student ID: {{ $student->student_id }}</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-500">Email</p>
                <p class="font-medium">{{ $student->email }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Course</p>
                <p class="font-medium">{{ $student->course ?? 'Not specified' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Year Level</p>
                <p class="font-medium">{{ $student->year_level ?? 'Not specified' }}</p>
            </div>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Completion</p>
                    <p class="text-xl font-bold">{{ $stats['completion_rate'] }}%</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full {{ $stats['risk_level'] == 'High' ? 'bg-red-100 text-red-600' : ($stats['risk_level'] == 'Medium' ? 'bg-yellow-100 text-yellow-600' : 'bg-green-100 text-green-600') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Risk Level</p>
                    <p class="text-xl font-bold">{{ $stats['risk_level'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Blood Type</p>
                    <p class="text-xl font-bold">{{ $medicalRecord->blood_type ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Last Updated</p>
                    <p class="text-xl font-bold">{{ $stats['last_updated']->diffForHumans() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Medical Information --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Left Column --}}
        <div class="space-y-6">
            {{-- Physical Measurements --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Physical Measurements</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Height:</span>
                        <span class="font-medium">{{ $medicalRecord->height ? $medicalRecord->height . ' cm' : 'Not recorded' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Weight:</span>
                        <span class="font-medium">{{ $medicalRecord->weight ? $medicalRecord->weight . ' kg' : 'Not recorded' }}</span>
                    </div>
                    @if($stats['bmi'])
                    <div class="flex justify-between">
                        <span class="text-gray-600">BMI:</span>
                        <span class="font-medium">{{ $stats['bmi'] }} ({{ $stats['bmi_category'] }})</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Medical Conditions --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Medical Conditions</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Allergies</p>
                        <p class="font-medium">{{ $medicalRecord->allergies ?: 'None reported' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Past Illnesses</p>
                        <p class="font-medium">{{ $medicalRecord->past_illnesses ?: 'None reported' }}</p>
                    </div>
                    @if($medicalRecord->is_taking_maintenance_drugs)
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Maintenance Medications</p>
                        <p class="font-medium">{{ $medicalRecord->maintenance_drugs_specify ?: 'Not specified' }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">
            {{-- Emergency Contacts --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Emergency Contacts</h3>
                <div class="space-y-4">
                    <div class="bg-red-50 p-3 rounded-lg">
                        <p class="text-sm font-medium text-red-800">Primary Contact</p>
                        <p class="font-medium">{{ $medicalRecord->emergency_contact_name_1 }}</p>
                        <p class="text-sm text-gray-600">{{ $medicalRecord->emergency_contact_number_1 }}</p>
                    </div>
                    @if($medicalRecord->emergency_contact_name_2)
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-sm font-medium text-gray-700">Secondary Contact</p>
                        <p class="font-medium">{{ $medicalRecord->emergency_contact_name_2 }}</p>
                        <p class="text-sm text-gray-600">{{ $medicalRecord->emergency_contact_number_2 }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Vaccination Status --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Vaccination Status</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">COVID-19 Vaccination:</span>
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $medicalRecord->is_fully_vaccinated ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $medicalRecord->is_fully_vaccinated ? 'Complete' : 'Incomplete' }}
                        </span>
                    </div>
                    @if($medicalRecord->is_fully_vaccinated)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Vaccine Type:</span>
                        <span class="font-medium">{{ $medicalRecord->vaccine_type ?? 'Not specified' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Doses:</span>
                        <span class="font-medium">{{ $medicalRecord->number_of_doses ?? 0 }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection