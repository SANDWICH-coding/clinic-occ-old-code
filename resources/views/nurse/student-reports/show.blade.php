@extends('layouts.nurse-app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Student Header -->
    <div class="mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-2xl font-bold text-blue-600">
                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">{{ $student->full_name }}</h2>
                        <div class="text-gray-600 text-sm">
                            <span class="font-semibold">{{ $student->student_id }}</span> | 
                            {{ $student->course }} - Year {{ $student->year_level }} | 
                            <a href="mailto:{{ $student->email }}" class="text-blue-600 hover:underline">{{ $student->email }}</a>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @php
                                $riskColors = [
                                    'High' => 'bg-red-100 text-red-800',
                                    'Medium' => 'bg-yellow-100 text-yellow-800',
                                    'Low' => 'bg-blue-100 text-blue-800',
                                    'None' => 'bg-green-100 text-green-800',
                                    'Unknown' => 'bg-gray-100 text-gray-800'
                                ];
                                $healthRiskLevel = $healthStats['health_risk_level'] ?? 'Unknown';
                                $color = $riskColors[$healthRiskLevel] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-sm {{ $color }}">
                                Health Risk: {{ $healthRiskLevel }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-800">
                                Medical Record: {{ $healthStats['medical_record_completion'] ?? 0 }}% Complete
                            </span>
                            @if($healthStats['has_allergies'] ?? false)
                                <span class="px-3 py-1 rounded-full text-sm bg-yellow-100 text-yellow-800">Has Allergies</span>
                            @endif
                            @if($healthStats['has_chronic_conditions'] ?? false)
                                <span class="px-3 py-1 rounded-full text-sm bg-red-100 text-red-800">Chronic Conditions</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <!-- <a href="{{ route('nurse.student-reports.export-pdf', $student->id) }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-download mr-2"></i>Export PDF
                    </a> -->
                    <a href="{{ route('nurse.student-reports.print', $student->id) }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition" target="_blank">
                        <i class="fas fa-print mr-2"></i>Print
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @foreach([
            ['icon' => 'stethoscope', 'label' => 'Consultations', 'value' => $healthStats['total_consultations'] ?? 0, 'color' => 'bg-blue-600'],
            ['icon' => 'thermometer-half', 'label' => 'Symptoms Logged', 'value' => $healthStats['total_symptoms_logged'] ?? 0, 'color' => 'bg-yellow-600'],
            ['icon' => 'calendar-check', 'label' => 'Appointments', 'value' => $healthStats['total_appointments'] ?? 0, 'color' => 'bg-indigo-600'],
            ['icon' => 'exclamation-triangle', 'label' => 'Emergency Cases', 'value' => $healthStats['emergency_cases'] ?? 0, 'color' => 'bg-red-600'],
            ['icon' => 'heartbeat', 'label' => 'Avg Pain Level', 'value' => ($healthStats['average_pain_level'] ?? 0) . '/10', 'color' => 'bg-green-600'],
            ['icon' => 'clipboard-list', 'label' => 'Follow-up Needed', 'value' => $healthStats['follow_up_needed'] ?? 0, 'color' => 'bg-gray-600']
        ] as $stat)
            <div class="bg-white rounded-xl shadow-lg p-4 flex items-center gap-4">
                <div class="w-12 h-12 {{ $stat['color'] }} rounded-lg flex items-center justify-center text-white">
                    <i class="fas fa-{{ $stat['icon'] }} text-lg"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600 uppercase">{{ $stat['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stat['value'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Column -->
        <div>
            <!-- Basic Student Information -->
            <div class="bg-white rounded-xl shadow-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-user-graduate mr-2"></i>Basic Student Information
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <table class="w-full text-sm">
                                <tr>
                                    <th class="text-left text-gray-600 font-semibold w-1/3">Student ID:</th>
                                    <td class="text-gray-800 font-medium">{{ $student->student_id ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-left text-gray-600 font-semibold">Full Name:</th>
                                    <td class="text-gray-800">{{ $student->full_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-left text-gray-600 font-semibold">Email:</th>
                                    <td>
                                        @if($student->email)
                                            <a href="mailto:{{ $student->email }}" class="text-blue-600 hover:underline">
                                                <i class="fas fa-envelope mr-1"></i>{{ $student->email }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-left text-gray-600 font-semibold">Phone:</th>
                                    <td>
                                        @if($student->phone_number)
                                            <a href="tel:{{ $student->phone_number }}" class="text-blue-600 hover:underline">
                                                <i class="fas fa-phone mr-1"></i>{{ $student->phone_number }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-left text-gray-600 font-semibold">Date of Birth:</th>
                                    <td>
                                        @if($student->date_of_birth)
                                            {{ \Carbon\Carbon::parse($student->date_of_birth)->format('M j, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-left text-gray-600 font-semibold">Age:</th>
                                    <td>
                                        @if($student->date_of_birth)
                                            {{ \Carbon\Carbon::parse($student->date_of_birth)->age }} years old
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div>
                            <table class="w-full text-sm">
                                <tr>
                                    <th class="text-left text-gray-600 font-semibold w-1/3">Gender:</th>
                                    <td>
                                        @if($student->gender)
                                            <span class="px-2 py-1 bg-gray-100 rounded-full text-sm">{{ ucfirst($student->gender) }}</span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-left text-gray-600 font-semibold">Course:</th>
                                    <td>{{ $student->course ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-left text-gray-600 font-semibold">Year Level:</th>
                                    <td>
                                        @if($student->year_level)
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">Year {{ $student->year_level }}</span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-left text-gray-600 font-semibold">Section:</th>
                                    <td>{{ $student->section ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-left text-gray-600 font-semibold">Address:</th>
                                    <td>{{ $student->address ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-left text-gray-600 font-semibold">Academic Year:</th>
                                    <td>
                                        @if($student->academic_year)
                                            <span class="px-2 py-1 bg-gray-100 rounded-full text-sm">{{ $student->academic_year }}</span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical Record Summary -->
            <div class="bg-white rounded-xl shadow-lg mb-6">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-file-medical-alt mr-2"></i>Medical Record Summary
                    </h3>
                    @if($student->medicalRecord && $student->medicalRecord->updated_at)
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-clock mr-1"></i>Last updated: {{ $student->medicalRecord->updated_at->diffForHumans() }}
                        </span>
                    @endif
                </div>
                <div class="p-6">
                    @if($student->medicalRecord)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <table class="w-full text-sm">
                                    <tr>
                                        <th class="text-left text-gray-600 font-semibold w-1/3">Blood Type:</th>
                                        <td>
                                            @if($student->medicalRecord->blood_type)
                                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">{{ $student->medicalRecord->blood_type }}</span>
                                            @else
                                                <span class="text-gray-600">Not specified</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-left text-gray-600 font-semibold">Height:</th>
                                        <td>
                                            @if($student->medicalRecord->height)
                                                <span class="px-2 py-1 bg-gray-100 rounded-full">{{ $student->medicalRecord->height }} cm</span>
                                            @else
                                                <span class="text-gray-600">Not specified</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-left text-gray-600 font-semibold">Weight:</th>
                                        <td>
                                            @if($student->medicalRecord->weight)
                                                <span class="px-2 py-1 bg-gray-100 rounded-full">{{ $student->medicalRecord->weight }} kg</span>
                                            @else
                                                <span class="text-gray-600">Not specified</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-left text-gray-600 font-semibold">BMI:</th>
                                        <td>
                                            @if($healthStats['bmi'] ?? false)
                                                <span class="px-2 py-1 bg-gray-100 rounded-full">{{ number_format($healthStats['bmi'], 1) }}</span>
                                                <span class="px-2 py-1 rounded-full {{ $healthStats['bmi_category']['color'] ?? 'bg-gray-100 text-gray-800' }} ml-2">
                                                    {{ $healthStats['bmi_category']['category'] ?? 'Unknown' }}
                                                </span>
                                            @else
                                                <span class="text-gray-600">Not calculated</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-left text-gray-600 font-semibold">Blood Pressure:</th>
                                        <td>
                                            @if($student->medicalRecord->blood_pressure)
                                                <span class="px-2 py-1 bg-gray-100 rounded-full">{{ $student->medicalRecord->blood_pressure }}</span>
                                            @else
                                                <span class="text-gray-600">Not recorded</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div>
                                <table class="w-full text-sm">
                                    <tr>
                                        <th class="text-left text-gray-600 font-semibold w-1/3">Vaccination Status:</th>
                                        <td>
                                            @if($student->medicalRecord->immunization_history)
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">Recorded</span>
                                            @else
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">Not specified</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-left text-gray-600 font-semibold">Hospitalization:</th>
                                        <td>
                                            @if($student->medicalRecord->has_undergone_surgery)
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">Yes</span>
                                                @if($student->medicalRecord->surgery_details)
                                                    <div class="text-gray-600 text-sm mt-1">{{ $student->medicalRecord->surgery_details }}</div>
                                                @endif
                                            @else
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">No history</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-left text-gray-600 font-semibold">Current Medications:</th>
                                        <td>
                                            @if($student->medicalRecord->is_taking_maintenance_drugs && $student->medicalRecord->current_medications)
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full">Yes</span>
                                            @else
                                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full">None</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-left text-gray-600 font-semibold">PWD Status:</th>
                                        <td>
                                            @if($student->medicalRecord->is_pwd)
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full">Yes</span>
                                                @if($student->medicalRecord->pwd_id)
                                                    <div class="text-gray-600 text-sm mt-1">ID: {{ $student->medicalRecord->pwd_id }}</div>
                                                @endif
                                            @else
                                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <!-- Medical Alerts -->
                        <div class="mt-6 space-y-4">
                            @if($student->medicalRecord->allergies)
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
                                    <h6 class="text-yellow-800 font-semibold"><i class="fas fa-exclamation-triangle mr-2"></i>Allergies</h6>
                                    <p class="text-yellow-800 text-sm">{{ $student->medicalRecord->allergies }}</p>
                                </div>
                            @endif
                            @if($student->medicalRecord->chronic_conditions)
                                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                                    <h6 class="text-red-800 font-semibold"><i class="fas fa-heartbeat mr-2"></i>Chronic Conditions</h6>
                                    <p class="text-red-800 text-sm">{{ $student->medicalRecord->chronic_conditions }}</p>
                                </div>
                            @endif
                            @if($student->medicalRecord->current_medications && $student->medicalRecord->is_taking_maintenance_drugs)
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                                    <h6 class="text-blue-800 font-semibold"><i class="fas fa-pills mr-2"></i>Current Medications</h6>
                                    <p class="text-blue-800 text-sm">{{ $student->medicalRecord->current_medications }}</p>
                                </div>
                            @endif
                        </div>
                        <!-- Additional Medical Information -->
                        @if($student->medicalRecord->family_medical_history || $student->medicalRecord->immunization_history)
                            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                                @if($student->medicalRecord->family_medical_history)
                                    <div>
                                        <h6 class="text-gray-800 font-semibold"><i class="fas fa-family mr-1"></i>Family Medical History</h6>
                                        <p class="text-gray-600 text-sm">{{ $student->medicalRecord->family_medical_history }}</p>
                                    </div>
                                @endif
                                @if($student->medicalRecord->immunization_history)
                                    <div>
                                        <h6 class="text-gray-800 font-semibold"><i class="fas fa-syringe mr-1"></i>Immunization Details</h6>
                                        <p class="text-gray-600 text-sm">{{ $student->medicalRecord->immunization_history }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="text-center text-gray-600 p-6">
                            <i class="fas fa-file-medical-alt text-4xl mb-3 text-gray-400"></i>
                            <p>No medical record found for this student.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Emergency Contacts -->
            <div class="bg-white rounded-xl shadow-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-address-book mr-2"></i>Emergency Contacts
                    </h3>
                </div>
                <div class="p-6">
                    @if($emergencyContacts->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            @foreach($emergencyContacts as $contact)
                                <div class="border rounded-lg p-4 {{ $contact['is_primary'] ?? false ? 'border-blue-400 bg-blue-50' : 'border-gray-200' }}">
                                    <div class="flex justify-between mb-2">
                                        <span class="px-2 py-1 rounded-full text-sm {{ $contact['is_primary'] ?? false ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $contact['is_primary'] ?? false ? 'Primary Contact' : 'Secondary Contact' }}
                                        </span>
                                    </div>
                                    <h6 class="text-gray-800 font-semibold">{{ $contact['name'] ?? $contact['full_name'] ?? 'N/A' }}</h6>
                                    <div class="text-gray-600 text-sm space-y-1">
                                        <div>
                                            <strong>Relationship:</strong> {{ $contact['relationship'] ?? 'Not specified' }}
                                        </div>
                                        <div>
                                            <strong>Phone:</strong> 
                                            @if($contact['phone'] ?? $contact['phone_number'] ?? false)
                                                <a href="tel:{{ $contact['phone'] ?? $contact['phone_number'] }}" class="text-blue-600 hover:underline">
                                                    {{ $contact['phone'] ?? $contact['phone_number'] }}
                                                </a>
                                            @else
                                                <span>Not specified</span>
                                            @endif
                                        </div>
                                        @if($contact['email'] ?? false)
                                            <div>
                                                <strong>Email:</strong> 
                                                <a href="mailto:{{ $contact['email'] }}" class="text-blue-600 hover:underline">{{ $contact['email'] }}</a>
                                            </div>
                                        @endif
                                        @if($contact['address'] ?? false)
                                            <div>
                                                <strong>Address:</strong> {{ $contact['address'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-gray-600 p-6">
                            <i class="fas fa-address-book text-3xl mb-2 text-gray-400"></i>
                            <p>No emergency contacts registered.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div>
            <!-- Symptom History -->
            <div class="bg-white rounded-xl shadow-lg mb-6">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-thermometer-half mr-2"></i>Symptom History
                    </h3>
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">{{ $symptomLogs->count() }} total</span>
                </div>
                <div class="max-h-[600px] overflow-y-auto">
                    @if($symptomLogs->count() > 0)
                        @foreach($symptomLogs as $symptom)
                            <div class="p-4 border-b border-gray-200 last:border-b-0">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h6 class="text-gray-800 font-semibold">
                                            @if(is_array($symptom->symptoms))
                                                {{ implode(', ', $symptom->symptoms) }}
                                            @else
                                                {{ $symptom->symptoms ?? 'No symptoms specified' }}
                                            @endif
                                            @if($symptom->is_emergency)
                                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-sm ml-2">Emergency</span>
                                            @endif
                                        </h6>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @if($symptom->severity_rating)
                                                <span class="px-2 py-1 rounded-full text-sm {{ $symptom->severity_rating >= 7 ? 'bg-red-100 text-red-800' : ($symptom->severity_rating >= 4 ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                                    Severity: {{ $symptom->severity_rating }}/10
                                                </span>
                                            @endif
                                            <span class="px-2 py-1 rounded-full text-sm {{ $symptom->status === 'resolved' ? 'bg-green-100 text-green-800' : ($symptom->status === 'under_review' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                                {{ ucfirst($symptom->status) }}
                                            </span>
                                            @if($symptom->duration)
                                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">
                                                    Duration: {{ $symptom->duration }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($symptom->description)
                                            <p class="mt-2 text-sm text-gray-600">
                                                <strong>Details:</strong> {{ $symptom->description }}
                                            </p>
                                        @endif
                                        @if($symptom->nurse_notes)
                                            <div class="mt-2 bg-gray-50 p-3 rounded-lg">
                                                <strong class="text-sm text-gray-800">Nurse Review:</strong> 
                                                <span class="text-sm text-gray-600">{{ $symptom->nurse_notes }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-right text-gray-600 text-sm ml-4">
                                        {{ \Carbon\Carbon::parse($symptom->logged_at)->format('M j, Y') }}<br>
                                        <span>{{ \Carbon\Carbon::parse($symptom->logged_at)->format('g:i A') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-gray-600 p-6">
                            <i class="fas fa-thermometer-half text-3xl mb-2 text-gray-400"></i>
                            <p>No symptom logs found.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Consultation History -->
            <div class="bg-white rounded-xl shadow-lg mb-6">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-stethoscope mr-2"></i>Consultation History
                    </h3>
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">{{ $consultations->count() }} total</span>
                </div>
                <div class="max-h-[600px] overflow-y-auto">
                    @if($consultations->count() > 0)
                        @foreach($consultations as $consultation)
                            <div class="p-4 border-b border-gray-200 last:border-b-0">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h6 class="text-gray-800 font-semibold">
                                            {{ $consultation->chief_complaint ?? 'No chief complaint' }}
                                            @if($consultation->type === 'emergency')
                                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-sm ml-2">Emergency</span>
                                            @endif
                                        </h6>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <span class="px-2 py-1 rounded-full text-sm {{ $consultation->type === 'emergency' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ ucfirst($consultation->type) }}
                                            </span>
                                            <span class="px-2 py-1 rounded-full text-sm {{ $consultation->status === 'completed' ? 'bg-green-100 text-green-800' : ($consultation->status === 'follow_up' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                                {{ ucfirst($consultation->status) }}
                                            </span>
                                            <span class="px-2 py-1 rounded-full text-sm {{ $consultation->priority === 'high' ? 'bg-red-100 text-red-800' : ($consultation->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                                {{ ucfirst($consultation->priority ?? 'normal') }}
                                            </span>
                                        </div>
                                        @if($consultation->diagnosis)
                                            <p class="mt-2 text-sm text-gray-600">
                                                <strong>Diagnosis:</strong> {{ $consultation->diagnosis }}
                                            </p>
                                        @endif
                                        @if($consultation->assessment)
                                            <p class="mt-2 text-sm text-gray-600">
                                                <strong>Assessment:</strong> {{ $consultation->assessment }}
                                            </p>
                                        @endif
                                        @if($consultation->temperature || $consultation->blood_pressure_systolic || $consultation->heart_rate)
                                            <div class="mt-2 bg-gray-50 p-3 rounded-lg">
                                                <strong class="text-sm text-gray-800">Vital Signs:</strong>
                                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-2 text-center text-sm">
                                                    @if($consultation->temperature)
                                                        <div>
                                                            <strong>Temp:</strong><br>
                                                            <span class="px-2 py-1 bg-gray-100 rounded-full">{{ $consultation->temperature }}°C</span>
                                                        </div>
                                                    @endif
                                                    @if($consultation->blood_pressure_systolic && $consultation->blood_pressure_diastolic)
                                                        <div>
                                                            <strong>BP:</strong><br>
                                                            <span class="px-2 py-1 bg-gray-100 rounded-full">{{ $consultation->blood_pressure_systolic }}/{{ $consultation->blood_pressure_diastolic }}</span>
                                                        </div>
                                                    @endif
                                                    @if($consultation->heart_rate)
                                                        <div>
                                                            <strong>HR:</strong><br>
                                                            <span class="px-2 py-1 bg-gray-100 rounded-full">{{ $consultation->heart_rate }} bpm</span>
                                                        </div>
                                                    @endif
                                                    @if($consultation->pain_level)
                                                        <div>
                                                            <strong>Pain:</strong><br>
                                                            <span class="px-2 py-1 bg-gray-100 rounded-full">{{ $consultation->pain_level }}/10</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                        @if($consultation->follow_up_required || $consultation->referral)
                                            <div class="mt-2 bg-yellow-50 p-3 rounded-lg text-sm">
                                                @if($consultation->follow_up_required)
                                                    <strong>Follow-up:</strong> {{ $consultation->follow_up_instructions ?? 'Additional follow-up needed' }}<br>
                                                @endif
                                                @if($consultation->referral)
                                                    <strong>Referral:</strong> {{ $consultation->referral }}
                                                @endif
                                            </div>
                                        @endif
                                        @if($consultation->nurse)
                                            <p class="mt-2 text-sm text-gray-600">
                                                <strong>Attending Nurse:</strong> {{ $consultation->nurse->name ?? 'N/A' }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-right text-gray-600 text-sm ml-4">
                                        {{ \Carbon\Carbon::parse($consultation->consultation_date)->format('M j, Y') }}<br>
                                        @if($consultation->consultation_time)
                                            <span>{{ \Carbon\Carbon::parse($consultation->consultation_time)->format('g:i A') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-gray-600 p-6">
                            <i class="fas fa-stethoscope text-3xl mb-2 text-gray-400"></i>
                            <p>No consultations found.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Appointment Results -->
            <div class="bg-white rounded-xl shadow-lg">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-calendar-check mr-2"></i>Appointment Results
                    </h3>
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">{{ $appointments->count() }} total</span>
                </div>
                <div class="max-h-[600px] overflow-y-auto">
                    @if($appointments->count() > 0)
                        @foreach($appointments as $appointment)
                            <div class="p-4 border-b border-gray-200 last:border-b-0">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h6 class="text-gray-800 font-semibold">{{ $appointment->reason ?? 'No reason specified' }}</h6>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">
                                                {{ ucfirst($appointment->appointment_type ?? 'general') }}
                                            </span>
                                            <span class="px-2 py-1 rounded-full text-sm {{ $appointment->status === 'completed' ? 'bg-green-100 text-green-800' : ($appointment->status === 'confirmed' ? 'bg-blue-100 text-blue-800' : ($appointment->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                            <span class="px-2 py-1 rounded-full text-sm {{ $appointment->priority === 'high' ? 'bg-red-100 text-red-800' : ($appointment->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                                {{ ucfirst($appointment->priority ?? 'normal') }}
                                            </span>
                                            @if($appointment->is_urgent)
                                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-sm">Urgent</span>
                                            @endif
                                        </div>
                                        @if($appointment->symptoms)
                                            <p class="mt-2 text-sm text-gray-600">
                                                <strong>Symptoms Reported:</strong> 
                                                @if(is_array($appointment->symptoms))
                                                    {{ implode(', ', $appointment->symptoms) }}
                                                @else
                                                    {{ $appointment->symptoms }}
                                                @endif
                                            </p>
                                        @endif
                                        @if($appointment->notes)
                                            <div class="mt-2 bg-gray-50 p-3 rounded-lg">
                                                <strong class="text-sm text-gray-800">Nurse Notes:</strong> 
                                                <span class="text-sm text-gray-600">{{ $appointment->notes }}</span>
                                            </div>
                                        @endif
                                        @if($appointment->follow_up_needed)
                                            <div class="mt-2 bg-yellow-50 p-3 rounded-lg text-sm">
                                                <strong>Follow-up Needed:</strong> 
                                                {{ $appointment->follow_up_instructions ?? 'Additional follow-up required' }}
                                            </div>
                                        @endif
                                        @if($appointment->nurse)
                                            <p class="mt-2 text-sm text-gray-600">
                                                <strong>Assigned Nurse:</strong> {{ $appointment->nurse->name ?? 'N/A' }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-right text-gray-600 text-sm ml-4">
                                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M j, Y') }}<br>
                                        @if($appointment->appointment_time)
                                            <span>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-gray-600 p-6">
                            <i class="fas fa-calendar-check text-3xl mb-2 text-gray-400"></i>
                            <p>No appointments found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection