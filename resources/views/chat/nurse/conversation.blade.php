{{-- resources/views/chat/nurse/conversation.blade.php --}}
@extends('layouts.nurse-app')

@section('title', 'Chat with ' . ($otherParticipant->full_name ?? 'Student'))

@section('content')
<div class="container mx-auto sm:px-6 py-8 max-w-7xl">
    <div class="bg-white rounded-xl shadow-md border border-gray-100" style="height: calc(100vh - 200px); min-height: 600px;">
        <div class="grid grid-cols-1 lg:grid-cols-12 h-full">
            <!-- Main Chat Area -->
            <div class="col-span-1 lg:col-span-8 border-r border-gray-100 flex flex-col h-full">
                <!-- Header -->
                <div class="p-4 sm:p-6 border-b border-gray-100 bg-blue-50 flex-shrink-0">
                    <div class="flex items-center justify-between mb-4">
                        <a href="{{ route('chat.index') }}" class="flex items-center gap-2 text-blue-600 hover:text-blue-800 transition-colors" aria-label="Back to all conversations">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span class="font-semibold text-sm sm:text-base">Back to Conversations</span>
                        </a>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Nurse View</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold shadow-md">
                            {{ strtoupper(substr($otherParticipant->first_name ?? 'S', 0, 1)) }}{{ strtoupper(substr($otherParticipant->last_name ?? 'T', 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="font-semibold text-lg text-gray-800">{{ $otherParticipant->full_name ?? 'Student' }}</h2>
                            <p class="text-sm text-gray-600">
                                {{ $otherParticipant->student_id ?? 'N/A' }} • {{ $otherParticipant->course ?? 'N/A' }}
                                @if($otherParticipant->year_level ?? false)
                                    • Year {{ $otherParticipant->year_level }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Messages List - Fixed scrolling container -->
                <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 min-h-0" 
                     id="messages-container" 
                     role="region" 
                     aria-live="polite"
                     style="max-height: calc(100vh - 400px);">
                    @forelse($messages ?? [] as $message)
                        <div class="flex {{ ($message->sender_id ?? 0) == auth()->id() ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message->id }}">
                            <div class="max-w-xs sm:max-w-md lg:max-w-lg rounded-lg px-4 py-3 {{ ($message->sender_id ?? 0) == auth()->id() ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800' }} shadow-sm">
                                @if($message->message ?? false)
                                    <p class="text-sm whitespace-pre-wrap break-words">{!! nl2br(e($message->message)) !!}</p>
                                @endif
                                <p class="text-xs mt-1 opacity-70 {{ ($message->sender_id ?? 0) == auth()->id() ? 'text-blue-100' : 'text-gray-600' }}">
                                    {{ ($message->created_at ?? now())->format('M d, g:i A') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-8 h-full flex items-center justify-center">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <p class="text-lg font-medium">Start a Conversation</p>
                                <p class="text-sm">Send a message to begin chatting with this student</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Message Input -->
                <div class="p-4 sm:p-6 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                    <form id="message-form" class="flex gap-2" aria-label="Send message form">
                        @csrf
                        <input type="hidden" name="conversation_id" value="{{ $conversation->id ?? '' }}">
                        <div class="flex-1">
                            <input
                                type="text"
                                name="message"
                                id="message-input"
                                placeholder="Type your message to {{ $otherParticipant->first_name ?? 'Student' }}..."
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                autocomplete="off"
                                aria-label="Message input"
                                maxlength="1000"
                            >
                        </div>
                        <button
                            type="submit"
                            class="px-4 sm:px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                            id="send-button"
                            aria-label="Send message"
                        >
                            Send
                        </button>
                    </form>
                    <p class="text-xs text-gray-500 mt-2">Professional communication guidelines apply.</p>
                </div>
            </div>

            <!-- Student Details Panel - SINGLE SCROLLBAR FOR ALL CONTENT -->
            <div class="col-span-1 lg:col-span-4 overflow-y-auto bg-gray-50 scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100" role="complementary" aria-label="Student information">
                <div class="p-4 sm:p-6 space-y-4">
                    <!-- Student Basic Info -->
                    <div class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:bg-gray-50 transition-colors">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Student Information
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Student ID:</span>
                                <span class="font-medium text-gray-800">{{ $otherParticipant->student_id ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Full Name:</span>
                                <span class="font-medium text-gray-800">{{ $otherParticipant->full_name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="font-medium">
                                    @if($otherParticipant->email ?? false)
                                        <a href="mailto:{{ $otherParticipant->email }}" class="text-blue-600 hover:underline">{{ $otherParticipant->email }}</a>
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Phone:</span>
                                <span class="font-medium">
                                    @if($otherParticipant->phone ?? false)
                                        <a href="tel:{{ $otherParticipant->phone }}" class="text-blue-600 hover:underline">{{ $otherParticipant->phone }}</a>
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date of Birth:</span>
                                <span class="font-medium">
                                    @if($otherParticipant->date_of_birth ?? false)
                                        {{ \Carbon\Carbon::parse($otherParticipant->date_of_birth)->format('M d, Y') }}
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Age:</span>
                                <span class="font-medium">{{ $otherParticipant->age ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Gender:</span>
                                <span class="font-medium">{{ $otherParticipant->gender ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Course:</span>
                                <span class="font-medium">{{ $otherParticipant->course ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Year Level:</span>
                                <span class="font-medium">{{ $otherParticipant->year_level ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Section:</span>
                                <span class="font-medium">{{ $otherParticipant->section ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Address:</span>
                                <span class="font-medium">{{ $otherParticipant->address ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Academic Year:</span>
                                <span class="font-medium">{{ $otherParticipant->academic_year ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Record Summary -->
                    @if(($otherParticipant->medicalRecord ?? false) && $otherParticipant->medicalRecord)
                    <div class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:bg-gray-50 transition-colors">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Medical Record Summary
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Blood Type:</span>
                                <span class="font-medium text-gray-800">{{ $otherParticipant->medicalRecord->blood_type ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Height:</span>
                                <span class="font-medium text-gray-800">{{ $otherParticipant->medicalRecord->height ?? 'N/A' }} cm</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Weight:</span>
                                <span class="font-medium text-gray-800">{{ $otherParticipant->medicalRecord->weight ?? 'N/A' }} kg</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">BMI:</span>
                                <span class="font-medium text-gray-800">{{ $otherParticipant->medicalRecord->bmi ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Blood Pressure:</span>
                                <span class="font-medium text-gray-800">{{ $otherParticipant->medicalRecord->blood_pressure ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Chronic Conditions:</span>
                                <span class="font-medium text-gray-800">{{ $otherParticipant->medicalRecord->chronic_conditions ?? 'None' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Allergies:</span>
                                <span class="font-medium text-red-600">{{ $otherParticipant->medicalRecord->allergies ?? 'None' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Medications:</span>
                                <span class="font-medium text-gray-800">{{ ($otherParticipant->medicalRecord->is_taking_maintenance_drugs ?? false) ? ($otherParticipant->medicalRecord->medications ?? 'Yes') : 'None' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Past Hospitalizations:</span>
                                <span class="font-medium text-gray-800">{{ ($otherParticipant->medicalRecord->has_been_hospitalized_6_months ?? false) ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Vaccination Status:</span>
                                <span class="font-medium text-gray-800">{{ ($otherParticipant->medicalRecord->is_fully_vaccinated ?? false) ? 'Fully Vaccinated' : 'Not Fully Vaccinated' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Last Updated:</span>
                                <span class="font-medium text-gray-800">{{ ($otherParticipant->medicalRecord->updated_at ?? now())->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200">
                        <p class="text-sm text-yellow-800 font-medium">No medical record found</p>
                    </div>
                    @endif

                    <!-- Emergency Contacts -->
                    @if(($otherParticipant->medicalRecord ?? false) && (($otherParticipant->medicalRecord->emergency_contact_name_1 ?? false) || ($otherParticipant->medicalRecord->emergency_contact_name_2 ?? false)))
                    <div class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:bg-gray-50 transition-colors">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            Emergency Contacts
                        </h3>
                        <div class="space-y-3">
                            @if($otherParticipant->medicalRecord->emergency_contact_name_1 ?? false)
                            <div class="text-sm">
                                <p class="text-gray-600 font-medium">{{ $otherParticipant->medicalRecord->emergency_contact_relationship_1 ?? 'Primary Contact' }}</p>
                                <p class="text-gray-800">{{ $otherParticipant->medicalRecord->emergency_contact_name_1 }}</p>
                                <p class="text-blue-600">
                                    @if($otherParticipant->medicalRecord->emergency_contact_number_1 ?? false)
                                        <a href="tel:{{ $otherParticipant->medicalRecord->emergency_contact_number_1 }}" class="hover:underline">{{ $otherParticipant->medicalRecord->emergency_contact_number_1 }}</a>
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                            @endif
                            @if($otherParticipant->medicalRecord->emergency_contact_name_2 ?? false)
                            <div class="text-sm">
                                <p class="text-gray-600 font-medium">{{ $otherParticipant->medicalRecord->emergency_contact_relationship_2 ?? 'Secondary Contact' }}</p>
                                <p class="text-gray-800">{{ $otherParticipant->medicalRecord->emergency_contact_name_2 }}</p>
                                <p class="text-blue-600">
                                    @if($otherParticipant->medicalRecord->emergency_contact_number_2 ?? false)
                                        <a href="tel:{{ $otherParticipant->medicalRecord->emergency_contact_number_2 }}" class="hover:underline">{{ $otherParticipant->medicalRecord->emergency_contact_number_2 }}</a>
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200">
                        <p class="text-sm text-yellow-800 font-medium">No emergency contacts found</p>
                    </div>
                    @endif

                    <!-- Symptom History -->
                    @php
                        $symptomLogs = $otherParticipant->id ? \App\Models\SymptomLog::where('user_id', $otherParticipant->id)
                            ->orderBy('created_at', 'desc')
                            ->get() : collect();
                    @endphp
                    @if($symptomLogs->count() > 0)
                    <div class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:bg-gray-50 transition-colors">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2V9a2 2 0 00-2-2h-2a2 2 0 00-2 2v10" />
                            </svg>
                            Symptom History
                        </h3>
                        <div class="space-y-3">
                            @foreach($symptomLogs as $symptom)
                            <div class="text-sm border-l-4 border-blue-400 pl-3 py-2 bg-blue-50 rounded-r-lg">
                                <p class="font-medium text-gray-800 flex items-center gap-2">
                                    {{ $symptom->created_at->format('M d, Y - g:i A') }}
                                    @if($symptom->is_emergency ?? false)
                                        <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">Emergency</span>
                                    @endif
                                </p>
                                <p class="text-gray-600 mt-1">
                                    @if(is_array($symptom->symptoms ?? null))
                                        {{ implode(', ', $symptom->symptoms) }}
                                    @else
                                        {{ $symptom->symptoms ?? 'N/A' }}
                                    @endif
                                </p>
                                <p class="text-gray-600 mt-1">Severity: {{ ucfirst($symptom->severity ?? 'N/A') }}</p>
                                <p class="text-gray-600 mt-1">Duration: {{ $symptom->duration ?? 'N/A' }}</p>
                                <p class="text-gray-600 mt-1">Notes: {{ $symptom->notes ?? 'No additional details' }}</p>
                                <p class="text-gray-600 mt-1">Emergency: <span class="{{ ($symptom->is_emergency ?? false) ? 'text-red-600' : 'text-green-600' }}">{{ ($symptom->is_emergency ?? false) ? 'Yes' : 'No' }}</span></p>
                                <p class="text-gray-600 mt-1">Reviewed: {{ ($symptom->nurse_reviewed ?? false) ? 'Yes by ' . (($symptom->reviewedBy->full_name ?? false) ? $symptom->reviewedBy->full_name : 'Nurse') . ' on ' . (($symptom->reviewed_at ?? false) ? $symptom->reviewed_at->format('M d, Y') : 'N/A') : 'No' }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200">
                        <p class="text-sm text-yellow-800 font-medium">No symptom logs found</p>
                    </div>
                    @endif

                    <!-- Consultation History -->
                    @php
                        $consultations = $otherParticipant->id ? \App\Models\Consultation::where('student_id', $otherParticipant->id)
                            ->with(['nurse'])
                            ->orderBy('created_at', 'desc')
                            ->get() : collect();
                    @endphp
                    @if($consultations->count() > 0)
                    <div class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:bg-gray-50 transition-colors">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Consultation History
                        </h3>
                        <div class="space-y-3">
                            @foreach($consultations as $consultation)
                            <div class="text-sm border-l-4 border-indigo-400 pl-3 py-2 bg-indigo-50 rounded-r-lg">
                                <p class="font-medium text-gray-800 flex items-center gap-2">
                                    {{ $consultation->created_at->format('M d, Y - g:i A') }}
                                    @if(($consultation->priority ?? '') == 'emergency')
                                        <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">Emergency</span>
                                    @endif
                                </p>
                                <p class="text-gray-600 mt-1">Type: {{ ucfirst($consultation->type ?? 'N/A') }}</p>
                                <p class="text-gray-600 mt-1">Status: {{ ucfirst(str_replace('_', ' ', $consultation->status ?? 'N/A')) }}</p>
                                <p class="text-gray-600 mt-1">Priority: {{ ucfirst($consultation->priority ?? 'N/A') }}</p>
                                <p class="text-gray-600 mt-1">Chief Complaint: {{ $consultation->chief_complaint ?? 'N/A' }}</p>
                                <p class="text-gray-600 mt-1">Diagnosis: {{ $consultation->diagnosis ?? 'N/A' }}</p>
                                <p class="text-gray-600 mt-1">Symptoms: {{ $consultation->symptoms_description ?? 'N/A' }}</p>
                                <p class="text-gray-600 mt-1">Vital Signs:</p>
                                <ul class="list-disc pl-5 text-gray-600 text-sm">
                                    <li>Temperature: {{ $consultation->temperature ? $consultation->temperature . ' °C' : 'N/A' }}</li>
                                    <li>Blood Pressure: {{ ($consultation->blood_pressure_systolic && $consultation->blood_pressure_diastolic) ? $consultation->blood_pressure_systolic . '/' . $consultation->blood_pressure_diastolic . ' mmHg' : 'N/A' }}</li>
                                    <li>Heart Rate: {{ $consultation->heart_rate ? $consultation->heart_rate . ' BPM' : 'N/A' }}</li>
                                    <li>Weight: {{ $consultation->weight ? $consultation->weight . ' kg' : 'N/A' }}</li>
                                    <li>Height: {{ $consultation->height ? $consultation->height . ' cm' : 'N/A' }}</li>
                                </ul>
                                <p class="text-gray-600 mt-1">Follow-up Required: {{ ($consultation->follow_up_required ?? false) ? 'Yes' : 'No' }}</p>
                                <p class="text-gray-600 mt-1">Referral Issued: {{ ($consultation->referral_issued ?? false) ? 'Yes' : 'No' }}</p>
                                <p class="text-gray-600 mt-1">Nurse: {{ ($consultation->nurse->full_name ?? false) ? $consultation->nurse->full_name : 'N/A' }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200">
                        <p class="text-sm text-yellow-800 font-medium">No consultations found</p>
                    </div>
                    @endif

                    <!-- Appointment History -->
                    @php
                        $appointments = $otherParticipant->id ? \App\Models\Appointment::where('user_id', $otherParticipant->id)
                            ->with(['nurse', 'acceptedBy', 'completedBy', 'rescheduledBy', 'cancelledBy', 'rejectedBy'])
                            ->orderBy('appointment_date', 'desc')
                            ->orderBy('appointment_time', 'desc')
                            ->get() : collect();
                    @endphp
                    @if($appointments->count() > 0)
                    <div class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:bg-gray-50 transition-colors">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Appointment History
                        </h3>
                        <div class="space-y-3">
                            @foreach($appointments as $appointment)
                            <div class="text-sm border-l-4 border-green-400 pl-3 py-2 bg-green-50 rounded-r-lg">
                                <p class="font-medium text-gray-800 flex items-center gap-2">
                                    {{ $appointment->appointment_date->format('M d, Y') }} at {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}
                                    @if($appointment->is_urgent ?? false)
                                        <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">Urgent</span>
                                    @endif
                                </p>
                                <p class="text-gray-600 mt-1">Type: {{ ucfirst($appointment->appointment_type ?? 'N/A') }}</p>
                                <p class="text-gray-600 mt-1">Status: {{ ucfirst($appointment->status ?? 'N/A') }}</p>
                                <p class="text-gray-600 mt-1">Reason: {{ $appointment->reason ?? 'N/A' }}</p>
                                <p class="text-gray-600 mt-1">Symptoms: {{ $appointment->symptoms ?? 'N/A' }}</p>
                                <p class="text-gray-600 mt-1">Priority: {{ $appointment->priority ? ucfirst($appointment->priority) : 'N/A' }}</p>
                                <p class="text-gray-600 mt-1">Urgent: {{ ($appointment->is_urgent ?? false) ? 'Yes' : 'No' }}</p>
                                <p class="text-gray-600 mt-1">Follow-up: {{ ($appointment->is_follow_up ?? false) ? 'Yes' : 'No' }}</p>
                                <p class="text-gray-600 mt-1">Nurse: {{ ($appointment->nurse->full_name ?? false) ? $appointment->nurse->full_name : 'Unassigned' }}</p>
                                @if(($appointment->status ?? '') == 'rejected' && ($appointment->rejection_reason ?? false))
                                <p class="text-gray-600 mt-1">Rejection Reason: {{ $appointment->rejection_reason }}</p>
                                @endif
                                @if(($appointment->status ?? '') == 'cancelled' && ($appointment->cancellation_reason ?? false))
                                <p class="text-gray-600 mt-1">Cancellation Reason: {{ $appointment->cancellation_reason }}</p>
                                @endif
                                @if($appointment->notes ?? false)
                                <p class="text-gray-600 mt-1">Notes: {{ $appointment->notes }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200">
                        <p class="text-sm text-yellow-800 font-medium">No appointments found</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const messagesContainer = document.getElementById('messages-container');
    const sendButton = document.getElementById('send-button');
    const conversationId = '{{ $conversation->id ?? "" }}';
    let refreshInterval;
    let isUserScrolling = false;
    let scrollTimeout;
    let isRefreshing = false;

    // Enhanced scroll to bottom function
    const scrollToBottom = (force = false) => {
        if (messagesContainer && (!isUserScrolling || force)) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    };
    
    // Initial scroll with delay to ensure DOM is fully rendered
    setTimeout(() => scrollToBottom(true), 300);

    // Detect if user is manually scrolling
    if (messagesContainer) {
        messagesContainer.addEventListener('scroll', () => {
            const isAtBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop <= messagesContainer.clientHeight + 100;
            isUserScrolling = !isAtBottom;
            
            // Reset scrolling flag after 3 seconds of no scrolling
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                isUserScrolling = false;
            }, 3000);
        });
    }

    // AUTO-REFRESH MESSAGES EVERY 3 SECONDS
    async function refreshMessages() {
        if (!conversationId || isRefreshing) return;

        isRefreshing = true;

        try {
            const response = await fetch(`/api/chat/${conversationId}/messages`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                // Add cache busting to prevent browser caching
                cache: 'no-cache'
            });

            if (!response.ok) throw new Error('Failed to fetch messages');

            const data = await response.json();

            if (data.success && data.messages) {
                updateMessagesUI(data.messages);
            }
        } catch (error) {
            console.error('Error refreshing messages:', error);
        } finally {
            isRefreshing = false;
        }
    }

    // Update messages in the UI
    function updateMessagesUI(messages) {
        if (!messagesContainer) return;

        // Store current message IDs
        const currentMessageIds = Array.from(messagesContainer.querySelectorAll('[data-message-id]'))
            .map(el => el.getAttribute('data-message-id'));

        // Check if there are new messages
        const newMessages = messages.filter(msg => !currentMessageIds.includes(msg.id.toString()));

        if (newMessages.length > 0) {
            // Add new messages
            newMessages.forEach(message => {
                addMessageToUI(message, false);
            });

            // Scroll to bottom if user is not scrolling
            scrollToBottom();
            
            console.log(`${newMessages.length} new message(s) loaded at:`, new Date().toLocaleTimeString());
        }
    }

    // Handle form submission
    if (messageForm) {
        messageForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = messageInput?.value.trim();

            if (!message) {
                alert('Please enter a message');
                return;
            }

            if (!conversationId) {
                alert('Error: Conversation not found');
                return;
            }

            if (sendButton) {
                sendButton.disabled = true;
                sendButton.textContent = 'Sending...';
            }

            try {
                const response = await fetch(`/api/chat/${conversationId}/message`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: message })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.error || `HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    if (messageInput) messageInput.value = '';
                    if (data.message) {
                        addMessageToUI(data.message, true);
                        scrollToBottom(true);
                    }
                } else {
                    throw new Error(data.error || 'Failed to send message');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(`Failed to send message: ${error.message}`);
            } finally {
                if (sendButton) {
                    sendButton.disabled = false;
                    sendButton.textContent = 'Send';
                }
                if (messageInput) messageInput.focus();
            }
        });
    }

    // Add message to UI
    const addMessageToUI = (message, isNewlySent = false) => {
        if (!messagesContainer || !message) return;

        // Check if message already exists
        const existingMessage = messagesContainer.querySelector(`[data-message-id="${message.id}"]`);
        if (existingMessage) return;

        // Remove empty state if it exists
        const emptyState = messagesContainer.querySelector('.text-center.text-gray-500');
        if (emptyState) {
            emptyState.remove();
        }

        const messageDiv = document.createElement('div');
        const isOwnMessage = (message.sender_id || 0) === {{ auth()->id() }};
        messageDiv.className = `flex ${isOwnMessage ? 'justify-end' : 'justify-start'}`;
        messageDiv.setAttribute('data-message-id', message.id);

        let messageContent = '';
        if (message.message) {
            messageContent += `<p class="text-sm whitespace-pre-wrap break-words">${sanitizeMessage(message.message)}</p>`;
        }
        
        const timeText = isNewlySent ? 'Just now' : formatMessageTime(message.created_at);
        
        messageContent += `
            <p class="text-xs mt-1 opacity-70 ${isOwnMessage ? 'text-blue-100' : 'text-gray-600'}">
                ${timeText}
            </p>
        `;

        messageDiv.innerHTML = `
            <div class="max-w-xs sm:max-w-md lg:max-w-lg rounded-lg px-4 py-3 ${isOwnMessage ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800'} shadow-sm">
                ${messageContent}
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
    };

    // Format message time
    const formatMessageTime = (timestamp) => {
        try {
            const date = new Date(timestamp);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);

            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;

            // Format as "Nov 24, 8:45 PM"
            const options = { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true };
            return date.toLocaleString('en-US', options);
        } catch (e) {
            return timestamp;
        }
    };

    // Sanitize message to prevent XSS
    const sanitizeMessage = (text) => {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/\n/g, '<br>');
    };

    // Handle Enter key for sending message
    if (messageInput) {
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (messageForm) {
                    messageForm.dispatchEvent(new Event('submit'));
                }
            }
        });
    }

    // Focus input
    if (messageInput) {
        messageInput.focus();
    }

    // Start auto-refresh every 3 seconds (3000 milliseconds)
    if (conversationId) {
        refreshInterval = setInterval(refreshMessages, 3000);
        console.log('✅ Auto-refresh started (3 seconds) for conversation:', conversationId);
        
        // Initial refresh after 1 second
        setTimeout(refreshMessages, 1000);
    }

    // Stop auto-refresh when user leaves the page
    window.addEventListener('beforeunload', () => {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });

    // Pause auto-refresh when tab is not visible
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
                console.log('⏸️ Auto-refresh paused (tab hidden)');
            }
        } else {
            if (!refreshInterval && conversationId) {
                refreshInterval = setInterval(refreshMessages, 3000);
                refreshMessages(); // Refresh immediately when tab becomes visible
                console.log('▶️ Auto-refresh resumed (tab visible)');
            }
        }
    });

    // WebSocket for real-time updates (only if Echo is available)
    if (typeof Echo !== 'undefined' && conversationId) {
        try {
            Echo.private(`chat.${conversationId}`)
                .listen('MessageSent', (e) => {
                    if (e.message && (e.message.sender_id || 0) !== {{ auth()->id() }}) {
                        addMessageToUI(e.message, false);
                        scrollToBottom();
                    }
                });
        } catch (error) {
            console.error('WebSocket error:', error);
        }
    }

    // Clean up on page unload
    window.addEventListener('beforeunload', () => {
        clearTimeout(scrollTimeout);
    });
});
</script>
@endpush