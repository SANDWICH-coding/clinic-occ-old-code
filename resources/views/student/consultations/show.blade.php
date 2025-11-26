{{-- resources/views/student/consultations/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Consultation Details')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Consultation Details</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Consultation ID: #{{ $consultation->id }} • 
                        {{ $consultation->created_at->format('F j, Y - g:i A') }}
                    </p>
                </div>
                <a href="{{ route('student.consultations.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Consultations
                </a>
            </div>
        </div>

        {{-- Status Banner --}}
        <div class="mb-6 rounded-lg p-4 {{ $consultation->status === 'completed' ? 'bg-green-50 border border-green-200' : 
                                           ($consultation->status === 'in_progress' ? 'bg-blue-50 border border-blue-200' : 
                                           'bg-yellow-50 border border-yellow-200') }}">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    @if($consultation->status === 'completed')
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @elseif($consultation->status === 'in_progress')
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    @endif
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium {{ $consultation->status === 'completed' ? 'text-green-800' : 
                                                       ($consultation->status === 'in_progress' ? 'text-blue-800' : 
                                                       'text-yellow-800') }}">
                        Status: {{ ucfirst(str_replace('_', ' ', $consultation->status)) }}
                    </h3>
                    <div class="mt-1 text-sm {{ $consultation->status === 'completed' ? 'text-green-700' : 
                                                 ($consultation->status === 'in_progress' ? 'text-blue-700' : 
                                                 'text-yellow-700') }}">
                        @if($consultation->status === 'completed')
                            {{-- Use consultation_date instead of consultation_ended_at --}}
                            Your consultation was completed on {{ \Carbon\Carbon::parse($consultation->consultation_date)->format('F j, Y \a\t g:i A') }}
                        @elseif($consultation->status === 'in_progress')
                            Your consultation is currently in progress
                        @else
                            Your consultation is {{ strtolower(str_replace('_', ' ', $consultation->status)) }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Chief Complaint & Symptoms --}}
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Chief Complaint
                    </h2>
                    <p class="text-gray-700 mb-4">{{ $consultation->chief_complaint }}</p>
                    
                    @if($consultation->symptoms_description)
                        <div class="border-t pt-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Symptoms Description</h3>
                            <p class="text-gray-700 whitespace-pre-line">{{ $consultation->symptoms_description }}</p>
                        </div>
                    @endif

                    @if($consultation->pain_level)
                        <div class="border-t pt-4 mt-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Pain Level</h3>
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="h-3 rounded-full {{ $consultation->pain_level <= 3 ? 'bg-green-500' : 
                                                                         ($consultation->pain_level <= 6 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                             style="width: {{ ($consultation->pain_level / 10) * 100 }}%"></div>
                                    </div>
                                </div>
                                <span class="ml-3 text-lg font-bold {{ $consultation->pain_level <= 3 ? 'text-green-600' : 
                                                                        ($consultation->pain_level <= 6 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $consultation->pain_level }}/10
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Vital Signs --}}
                @if($consultation->temperature || $consultation->blood_pressure_systolic || $consultation->heart_rate || $consultation->oxygen_saturation || $consultation->respiratory_rate || $consultation->weight || $consultation->height)
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            Vital Signs
                        </h2>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            @if($consultation->temperature)
                                <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-lg p-4 border border-red-100">
                                    <p class="text-xs text-gray-600 mb-1">Temperature</p>
                                    <p class="text-xl font-bold text-gray-900">{{ $consultation->temperature }}</p>
                                    <p class="text-xs text-gray-500">°C</p>
                                </div>
                            @endif

                            @if($consultation->blood_pressure_systolic && $consultation->blood_pressure_diastolic)
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-100">
                                    <p class="text-xs text-gray-600 mb-1">Blood Pressure</p>
                                    <p class="text-xl font-bold text-gray-900">
                                        {{ $consultation->blood_pressure_systolic }}/{{ $consultation->blood_pressure_diastolic }}
                                    </p>
                                    <p class="text-xs text-gray-500">mmHg</p>
                                </div>
                            @endif

                            @if($consultation->heart_rate)
                                <div class="bg-gradient-to-br from-pink-50 to-rose-50 rounded-lg p-4 border border-pink-100">
                                    <p class="text-xs text-gray-600 mb-1">Heart Rate</p>
                                    <p class="text-xl font-bold text-gray-900">{{ $consultation->heart_rate }}</p>
                                    <p class="text-xs text-gray-500">BPM</p>
                                </div>
                            @endif

                            @if($consultation->oxygen_saturation)
                                <div class="bg-gradient-to-br from-cyan-50 to-blue-50 rounded-lg p-4 border border-cyan-100">
                                    <p class="text-xs text-gray-600 mb-1">O₂ Saturation</p>
                                    <p class="text-xl font-bold text-gray-900">{{ $consultation->oxygen_saturation }}</p>
                                    <p class="text-xs text-gray-500">%</p>
                                </div>
                            @endif

                            @if($consultation->respiratory_rate)
                                <div class="bg-gradient-to-br from-teal-50 to-emerald-50 rounded-lg p-4 border border-teal-100">
                                    <p class="text-xs text-gray-600 mb-1">Respiratory Rate</p>
                                    <p class="text-xl font-bold text-gray-900">{{ $consultation->respiratory_rate }}</p>
                                    <p class="text-xs text-gray-500">per min</p>
                                </div>
                            @endif

                            @if($consultation->weight)
                                <div class="bg-gradient-to-br from-purple-50 to-violet-50 rounded-lg p-4 border border-purple-100">
                                    <p class="text-xs text-gray-600 mb-1">Weight</p>
                                    <p class="text-xl font-bold text-gray-900">{{ $consultation->weight }}</p>
                                    <p class="text-xs text-gray-500">kg</p>
                                </div>
                            @endif

                            @if($consultation->height)
                                <div class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded-lg p-4 border border-amber-100">
                                    <p class="text-xs text-gray-600 mb-1">Height</p>
                                    <p class="text-xl font-bold text-gray-900">{{ $consultation->height }}</p>
                                    <p class="text-xs text-gray-500">cm</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Diagnosis & Treatment --}}
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Diagnosis & Treatment
                    </h2>

                    @if($consultation->diagnosis)
                        <div class="mb-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Diagnosis</h3>
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded">
                                <p class="text-gray-900 font-medium whitespace-pre-line">{{ $consultation->diagnosis }}</p>
                            </div>
                        </div>
                    @endif

                    @if($consultation->treatment_provided)
                        <div class="mb-4 {{ $consultation->diagnosis ? 'border-t pt-4' : '' }}">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Treatment Provided</h3>
                            <p class="text-gray-700 whitespace-pre-line">{{ $consultation->treatment_provided }}</p>
                        </div>
                    @endif

                    @if($consultation->medications_given)
                        <div class="mb-4 border-t pt-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">
                                <span class="inline-flex items-center">
                                    <svg class="h-4 w-4 mr-1 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                    </svg>
                                    Medications Given
                                </span>
                            </h3>
                            <div class="bg-purple-50 border border-purple-100 rounded-lg p-3">
                                <p class="text-gray-700 whitespace-pre-line">{{ $consultation->medications_given }}</p>
                            </div>
                        </div>
                    @endif

                    @if($consultation->procedures_performed)
                        <div class="mb-4 border-t pt-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Procedures Performed</h3>
                            <p class="text-gray-700 whitespace-pre-line">{{ $consultation->procedures_performed }}</p>
                        </div>
                    @endif

                    @if($consultation->home_care_instructions)
                        <div class="border-t pt-4 bg-blue-50 -m-6 mt-4 p-6 rounded-b-lg">
                            <h3 class="text-sm font-medium text-blue-900 mb-2 flex items-center">
                                <svg class="h-5 w-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Home Care Instructions
                            </h3>
                            <p class="text-blue-800 whitespace-pre-line leading-relaxed">{{ $consultation->home_care_instructions }}</p>
                        </div>
                    @endif

                    @if(!$consultation->diagnosis && !$consultation->treatment_provided && !$consultation->medications_given && !$consultation->procedures_performed && !$consultation->home_care_instructions)
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">
                                @if($consultation->status === 'completed')
                                    No treatment or diagnosis information was recorded for this consultation.
                                @else
                                    Diagnosis and treatment details will be available after the consultation is completed.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Physical Examination (if available) --}}
                @if($consultation->physical_examination)
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="h-5 w-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Physical Examination
                        </h2>
                        <p class="text-gray-700 whitespace-pre-line">{{ $consultation->physical_examination }}</p>
                    </div>
                @endif

                {{-- Follow-up Information --}}
                @if($consultation->follow_up_required)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-yellow-900 mb-4 flex items-center">
                            <svg class="h-5 w-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Follow-up Required
                        </h2>

                        @if($consultation->follow_up_instructions)
                            <div class="mb-3">
                                <p class="text-yellow-800 font-medium">{{ $consultation->follow_up_instructions }}</p>
                            </div>
                        @endif

                        @if($consultation->recommended_follow_up_date)
                            <div class="flex items-center text-yellow-700 bg-yellow-100 rounded-md p-3">
                                <svg class="h-5 w-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm">
                                    Recommended follow-up date: 
                                    <strong class="text-yellow-900">{{ \Carbon\Carbon::parse($consultation->recommended_follow_up_date)->format('F j, Y') }}</strong>
                                </span>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Referral Information --}}
                @if($consultation->referral_issued)
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-purple-900 mb-4 flex items-center">
                            <svg class="h-5 w-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Referral Issued
                        </h2>

                        @if($consultation->referred_to)
                            <div class="mb-3">
                                <h3 class="text-sm font-medium text-purple-700 mb-1">Referred To</h3>
                                <p class="text-purple-900 font-medium text-lg">{{ $consultation->referred_to }}</p>
                            </div>
                        @endif

                        @if($consultation->referral_reason)
                            <div class="mb-3 border-t border-purple-200 pt-3">
                                <h3 class="text-sm font-medium text-purple-700 mb-1">Reason for Referral</h3>
                                <p class="text-purple-800">{{ $consultation->referral_reason }}</p>
                            </div>
                        @endif

                        @if($consultation->referral_urgency)
                            <div class="flex items-center text-purple-700 bg-purple-100 rounded-md p-3">
                                <svg class="h-5 w-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm">
                                    Urgency Level: <strong class="text-purple-900">{{ ucfirst($consultation->referral_urgency) }}</strong>
                                </span>
                            </div>
                        @endif

                        @if($consultation->referral_notes)
                            <div class="mt-3 pt-3 border-t border-purple-200">
                                <h3 class="text-sm font-medium text-purple-700 mb-1">Additional Notes</h3>
                                <p class="text-purple-800 text-sm whitespace-pre-line">{{ $consultation->referral_notes }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Additional Notes Section --}}
                @if($consultation->consultation_notes)
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="h-5 w-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                            Additional Notes
                        </h2>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-gray-700 whitespace-pre-line">{{ $consultation->consultation_notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                
                {{-- Consultation Info Card --}}
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Consultation Information</h2>
                    
                    <div class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                             {{ $consultation->type === 'walk_in' ? 'bg-yellow-100 text-yellow-800' : 
                                                ($consultation->type === 'emergency' ? 'bg-red-100 text-red-800' : 
                                                'bg-blue-100 text-blue-800') }}">
                                    {{ ucfirst(str_replace('_', ' ', $consultation->type)) }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Priority</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                             {{ $consultation->priority === 'emergency' ? 'bg-red-100 text-red-800' : 
                                                ($consultation->priority === 'high' ? 'bg-orange-100 text-orange-800' : 
                                                'bg-green-100 text-green-800') }}">
                                    {{ ucfirst($consultation->priority) }}
                                </span>
                            </dd>
                        </div>

                        @if($consultation->nurse)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Attended By</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span class="text-blue-600 font-medium text-xs">
                                                {{ substr($consultation->nurse->full_name, 0, 1) }}
                                            </span>
                                        </div>
                                        <span class="ml-2">{{ $consultation->nurse->full_name }}</span>
                                    </div>
                                </dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Registered At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $consultation->created_at->format('F j, Y') }}<br>
                                <span class="text-gray-500">{{ $consultation->created_at->format('g:i A') }}</span>
                            </dd>
                        </div>

                        @if($consultation->started_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Started At</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $consultation->started_at->format('g:i A') }}
                                </dd>
                            </div>
                        @endif

                        {{-- Updated Completed At section --}}
                        @if($consultation->status === 'completed')
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Completed At</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($consultation->consultation_date)->format('F j, Y') }}<br>
                                    <span class="text-gray-500">{{ \Carbon\Carbon::parse($consultation->consultation_date)->format('g:i A') }}</span>
                                </dd>
                            </div>
                        @endif

                        @if($consultation->consultation_duration_minutes)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Duration</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <span class="inline-flex items-center text-blue-700">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $consultation->consultation_duration_minutes }} minutes
                                    </span>
                                </dd>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Parent Notification (if applicable) --}}
                @if($consultation->parent_notified || $consultation->parent_pickup_required)
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
                        <h2 class="text-sm font-semibold text-orange-900 mb-3 flex items-center">
                            <svg class="h-5 w-5 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            Parent/Guardian Information
                        </h2>
                        
                        @if($consultation->parent_notified)
                            <div class="flex items-start mb-3">
                                <svg class="h-5 w-5 text-green-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm text-orange-800">
                                    Your parent/guardian has been notified about this consultation.
                                </p>
                            </div>
                        @endif

                        @if($consultation->parent_pickup_required)
                            <div class="flex items-start bg-orange-100 rounded-md p-3">
                                <svg class="h-5 w-5 text-orange-700 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p class="text-sm text-orange-900 font-medium">
                                    Parent/Guardian pickup is required.
                                </p>
                            </div>
                        @endif

                        @if($consultation->parent_communication_notes)
                            <div class="mt-3 pt-3 border-t border-orange-200">
                                <h3 class="text-xs font-medium text-orange-700 mb-1">Communication Notes</h3>
                                <p class="text-sm text-orange-800">{{ $consultation->parent_communication_notes }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Quick Actions --}}
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                        <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Quick Actions
                    </h2>
                    
                    <div class="space-y-2">
                        <a href="{{ route('student.consultations.index') }}" 
                           class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                            <span class="flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                View All Consultations
                            </span>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        @if($consultation->status === 'completed')
                            <button onclick="window.print()" 
                                    class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                <span class="flex items-center">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                    Print Consultation
                                </span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Health Tips (if completed) --}}
                @if($consultation->status === 'completed')
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-lg p-6">
                        <h2 class="text-sm font-semibold text-green-900 mb-3 flex items-center">
                            <svg class="h-5 w-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Health Reminder
                        </h2>
                        <div class="space-y-2 text-sm text-green-800">
                            <p class="flex items-start">
                                <svg class="h-4 w-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Follow the prescribed treatment plan
                            </p>
                            <p class="flex items-start">
                                <svg class="h-4 w-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Take medications as directed
                            </p>
                            <p class="flex items-start">
                                <svg class="h-4 w-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Rest and stay hydrated
                            </p>
                            <p class="flex items-start">
                                <svg class="h-4 w-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Return if symptoms worsen
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, nav, header, footer, button {
        display: none !important;
    }
    body {
        background: white !important;
    }
    .bg-gradient-to-br {
        background: white !important;
    }
}
</style>
@endsection