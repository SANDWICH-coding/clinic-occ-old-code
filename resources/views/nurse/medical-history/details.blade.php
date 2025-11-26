{{-- resources/views/nurse/medical-history/details.blade.php --}}
@extends('layouts.nurse-app')

@section('title', 'Medical Record - ' . $student->full_name)

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header Section --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Medical Record Details</h1>
            <p class="text-gray-600">{{ $student->full_name }} - {{ $student->student_id }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('nurse.medical-records.index') }}" 
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                Back to Records
            </a>
            @if($medicalRecord)
            <a href="{{ route('nurse.medical-records.edit', $medicalRecord) }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Edit Record
            </a>
            @endif
        </div>
    </div>

    {{-- Combined Student & Medical Information --}}
    @if($medicalRecord)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Student Profile Card --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">Student Profile</h2>
            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Student ID:</dt>
                    <dd class="font-medium">{{ $student->student_id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Name:</dt>
                    <dd class="font-medium">{{ $student->full_name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Email:</dt>
                    <dd class="font-medium">{{ $student->email }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Course:</dt>
                    <dd class="font-medium">{{ $student->course ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Year Level:</dt>
                    <dd class="font-medium">{{ $student->year_level ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Medical Summary Card --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">Medical Summary</h2>
            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Blood Type:</dt>
                    <dd class="font-medium">
                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded">
                            {{ $medicalRecord->blood_type ?? 'Not specified' }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Risk Level:</dt>
                    <dd class="font-medium">
                        <span class="px-2 py-1 rounded text-white
                            {{ $stats['risk_level'] == 'High' ? 'bg-red-600' : 
                               ($stats['risk_level'] == 'Medium' ? 'bg-yellow-500' : 'bg-green-600') }}">
                            {{ $stats['risk_level'] }}
                        </span>
                    </dd>
                </div>
                @if($stats['bmi'])
                <div class="flex justify-between">
                    <dt class="text-gray-600">BMI:</dt>
                    <dd class="font-medium">{{ number_format($stats['bmi'], 1) }} ({{ $stats['bmi_category'] }})</dd>
                </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-gray-600">Vaccination:</dt>
                    <dd class="font-medium">
                        <span class="px-2 py-1 rounded text-white
                            {{ $medicalRecord->is_fully_vaccinated ? 'bg-green-600' : 'bg-yellow-500' }}">
                            {{ $medicalRecord->is_fully_vaccinated ? 'Complete' : 'Incomplete' }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Completion:</dt>
                    <dd class="font-medium">{{ $stats['completion_rate'] }}%</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Detailed Medical Information Sections --}}
    <div class="mt-6 space-y-6">
        {{-- Health Information --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">Health Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Allergies</h4>
                    <p class="text-gray-600">{{ $medicalRecord->allergies ?: 'None reported' }}</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Past Illnesses</h4>
                    <p class="text-gray-600">{{ $medicalRecord->past_illnesses ?: 'None reported' }}</p>
                </div>
            </div>
        </div>

        {{-- Emergency Contacts --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">Emergency Contacts</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-red-50 p-4 rounded-lg">
                    <h4 class="font-medium text-red-800 mb-2">Primary Contact</h4>
                    <p class="font-medium">{{ $medicalRecord->emergency_contact_name_1 }}</p>
                    <p class="text-gray-600">{{ $medicalRecord->emergency_contact_number_1 }}</p>
                </div>
                @if($medicalRecord->emergency_contact_name_2)
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-700 mb-2">Secondary Contact</h4>
                    <p class="font-medium">{{ $medicalRecord->emergency_contact_name_2 }}</p>
                    <p class="text-gray-600">{{ $medicalRecord->emergency_contact_number_2 }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @else
    {{-- No Record Found --}}
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg">
        <h2 class="text-lg font-semibold text-yellow-700 mb-2">No Medical Record Found</h2>
        <p class="text-gray-600 mb-4">This student doesn't have a medical record yet.</p>
        <a href="{{ route('nurse.medical-records.create-for', $student) }}" 
           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Create Medical Record
        </a>
    </div>
    @endif
</div>
@endsection