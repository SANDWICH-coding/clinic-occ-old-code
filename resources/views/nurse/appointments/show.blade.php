@extends('layouts.nurse-app')

@section('title', 'Appointment Details')

@push('styles')
<style>
    .detail-card {
        @apply bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden;
    }

    .detail-header {
        @apply bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200;
    }

    .detail-header h3 {
        @apply text-lg font-semibold text-gray-900 flex items-center space-x-2;
    }

    .detail-row {
        @apply flex justify-between items-start py-4 px-6 border-b border-gray-100 last:border-b-0;
    }

    .detail-label {
        @apply text-sm font-medium text-gray-600 flex-shrink-0;
    }

    .detail-value {
        @apply text-sm text-gray-900 font-medium flex-1 ml-4 text-right;
    }

    .badge-base {
        @apply inline-flex items-center px-3 py-1 rounded-full text-xs font-medium;
    }

    .timeline-container {
        @apply space-y-4;
    }

    .timeline-item {
        @apply flex items-start space-x-4;
    }

    .timeline-dot {
        @apply w-3 h-3 rounded-full mt-2 flex-shrink-0;
    }

    .timeline-content {
        @apply flex-1;
    }

    .timeline-label {
        @apply font-semibold text-gray-900 text-sm;
    }

    .timeline-date {
        @apply text-xs text-gray-500 mt-1;
    }

    .status-indicator {
        @apply w-2 h-2 rounded-full mr-2 inline-block;
    }

    .info-box {
        @apply bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg;
    }

    .warning-box {
        @apply bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg;
    }

    .grid-2-cols {
        @apply grid grid-cols-1 md:grid-cols-2 gap-6;
    }

    /* Enhanced Recent Appointments Styling */
    .appointments-list {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .appointments-list::-webkit-scrollbar {
        display: none;
    }

    .appointment-item {
        @apply border-b border-gray-100 p-4 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 cursor-pointer last:border-b-0;
    }

    .appointment-item:first-child {
        @apply border-t border-gray-100;
    }

    .appointment-reason {
        @apply font-semibold text-sm text-gray-900 mb-2;
    }

    .appointment-meta {
        @apply text-xs text-gray-600 flex items-center space-x-2;
    }

    /* Stats Cards Enhancement */
    .stat-card {
        @apply p-4 rounded-lg transition-all duration-200 hover:shadow-md;
    }

    .stat-card-blue {
        @apply bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200;
    }

    .stat-card-green {
        @apply bg-gradient-to-br from-green-50 to-green-100 border border-green-200;
    }

    .stat-card-red {
        @apply bg-gradient-to-br from-red-50 to-red-100 border border-red-200;
    }

    .stat-label {
        @apply text-xs sm:text-sm text-gray-700 font-medium;
    }

    .stat-value {
        @apply text-2xl sm:text-3xl font-bold mt-1;
    }

    @media (max-width: 768px) {
        .detail-row {
            @apply flex-col;
        }

        .detail-label {
            @apply mb-2;
        }

        .detail-value {
            @apply ml-0 text-left;
        }
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Appointment Details</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Complete appointment information and student history</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
            <a href="{{ route('nurse.appointments.index') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition text-center text-sm font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
            <a href="{{ route('nurse.appointments.calendar') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition text-center text-sm font-medium">
                <i class="fas fa-calendar-alt mr-2"></i>Calendar View
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Left Column: Appointment & Student Info -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Appointment Status Alert -->
            <div class="detail-card">
                <div class="detail-header">
                    <h3>
                        <i class="fas fa-calendar-check text-blue-600"></i>
                        <span>Appointment Status</span>
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-600 font-medium">Current Status:</span>
                        <span class="{{ $appointment->status_badge_class }} badge-base">
                            {{ $appointment->status_display }}
                        </span>
                    </div>
                    @if($appointment->is_urgent)
                        <div class="warning-box">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-amber-600 mr-2"></i>
                                <span class="text-sm text-amber-900"><strong>Urgent:</strong> This appointment requires immediate attention</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Appointment Information -->
            <div class="detail-card">
                <div class="detail-header">
                    <h3>
                        <i class="fas fa-clipboard-list text-blue-600"></i>
                        <span>Appointment Information</span>
                    </h3>
                </div>
                <div class="divide-y">
                    <div class="detail-row">
                        <span class="detail-label">Date & Time</span>
                        <span class="detail-value">
                            <i class="fas fa-calendar mr-2 text-blue-600"></i>
                            {{ $appointment->formatted_date_time }}
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Appointment Type</span>
                        <span class="detail-value">
                            <span class="{{ $appointment->appointment_type_badge_class }} badge-base">
                                {{ $appointment->appointment_type_display }}
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Reason for Visit</span>
                        <span class="detail-value">{{ $appointment->reason ?? 'N/A' }}</span>
                    </div>
                    @if($appointment->symptoms)
                        <div class="detail-row">
                            <span class="detail-label">Symptoms</span>
                            <span class="detail-value">{{ $appointment->symptoms }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Student Information - Comprehensive -->
            <div class="detail-card">
                <div class="detail-header">
                    <h3>
                        <i class="fas fa-user-circle text-blue-600"></i>
                        <span>Student Information</span>
                    </h3>
                </div>
                <div class="divide-y">
                    @php
                        $student = $appointment->user;
                        $medicalRecord = $student->medicalRecord;
                        $emergencyContacts = $student->emergency_contacts;
                        $vaccination = $student->getVaccinationStatus();
                    @endphp

                    <!-- Basic Information -->
                    <div class="detail-row bg-gray-50 font-semibold">
                        <span class="detail-label"><i class="fas fa-id-card mr-2 text-blue-600"></i>Basic Information</span>
                        <span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Full Name</span>
                        <span class="detail-value font-bold">{{ $student->full_name ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Student ID</span>
                        <span class="detail-value">{{ $student->student_id ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email</span>
                        <span class="detail-value">
                            <a href="mailto:{{ $student->email }}" class="text-blue-600 hover:text-blue-800 break-all">
                                {{ $student->email ?? 'N/A' }}
                            </a>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone</span>
                        <span class="detail-value">
                            @if($student->phone)
                                <a href="tel:{{ $student->phone }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $student->formatted_phone ?? $student->phone }}
                                </a>
                            @else
                                N/A
                            @endif
                        </span>
                    </div>

                    <!-- Personal Details -->
                    <div class="detail-row bg-gray-50 font-semibold">
                        <span class="detail-label"><i class="fas fa-user mr-2 text-blue-600"></i>Personal Details</span>
                        <span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date of Birth</span>
                        <span class="detail-value">
                            @if($student->date_of_birth)
                                {{ $student->date_of_birth->format('M d, Y') }}
                                <span class="text-gray-500">({{ $student->age ?? $student->date_of_birth->age }} years)</span>
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Gender</span>
                        <span class="detail-value">
                            @if($student->gender)
                                <span class="badge-base {{ $student->gender == 'male' ? 'bg-blue-100 text-blue-800' : ($student->gender == 'female' ? 'bg-pink-100 text-pink-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($student->gender) }}
                                </span>
                            @else
                                N/A
                            @endif
                        </span>
                    </div>

                    <!-- Academic Information -->
                    <div class="detail-row bg-gray-50 font-semibold">
                        <span class="detail-label"><i class="fas fa-graduation-cap mr-2 text-blue-600"></i>Academic Information</span>
                        <span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Course/Program</span>
                        <span class="detail-value">
                            @if($student->course)
                                {{ $student->course }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Year Level</span>
                        <span class="detail-value">
                            @if($student->year_level)
                                <span class="badge-base bg-indigo-100 text-indigo-800">
                                    {{ $student->year_level }}
                                </span>
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Section</span>
                        <span class="detail-value">{{ $student->section ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Academic Year</span>
                        <span class="detail-value">
                            @if($student->academic_year)
                                {{ $student->academic_year }}-{{ $student->academic_year + 1 }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Department</span>
                        <span class="detail-value">
                            @if($student->department)
                                {{ $student->department_name ?? $student->department }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Academic Info</span>
                        <span class="detail-value">{{ $student->academic_info ?? 'N/A' }}</span>
                    </div>

                    <!-- Address Information -->
                    <div class="detail-row bg-gray-50 font-semibold">
                        <span class="detail-label"><i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>Address Information</span>
                        <span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address</span>
                        <span class="detail-value">{{ $student->address ?? 'N/A' }}</span>
                    </div>

                    <!-- Medical Information -->
                    <div class="detail-row bg-gray-50 font-semibold">
                        <span class="detail-label"><i class="fas fa-heartbeat mr-2 text-blue-600"></i>Medical Information</span>
                        <span></span>
                    </div>
                    
                    @if($medicalRecord)
                        <div class="detail-row">
                            <span class="detail-label">Blood Type</span>
                            <span class="detail-value">
                                @if($medicalRecord->blood_type)
                                    <span class="badge-base bg-red-100 text-red-800">{{ $medicalRecord->blood_type }}</span>
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Height</span>
                            <span class="detail-value">
                                @if($medicalRecord->height)
                                    {{ $medicalRecord->height }} cm
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Weight</span>
                            <span class="detail-value">
                                @if($medicalRecord->weight)
                                    {{ $medicalRecord->weight }} kg
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Allergies</span>
                            <span class="detail-value">
                                @if($medicalRecord->allergies)
                                    <span class="badge-base bg-amber-100 text-amber-800">{{ $medicalRecord->allergies }}</span>
                                @else
                                    <span class="text-green-600">None Known</span>
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Past Illnesses</span>
                            <span class="detail-value">{{ $medicalRecord->past_illnesses ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Current Medications</span>
                            <span class="detail-value">{{ $medicalRecord->current_medications ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Family Medical History</span>
                            <span class="detail-value">{{ $medicalRecord->family_medical_history ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Is PWD</span>
                            <span class="detail-value">
                                @if($medicalRecord->is_pwd)
                                    <span class="badge-base bg-purple-100 text-purple-800">Yes</span>
                                @else
                                    <span class="badge-base bg-gray-100 text-gray-800">No</span>
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Vaccination Status</span>
                            <span class="detail-value">
                                @if($vaccination['is_vaccinated'])
                                    <span class="badge-base bg-green-100 text-green-800">
                                        Fully Vaccinated
                                        @if($vaccination['vaccine_type'])
                                            ({{ $vaccination['vaccine_type'] }})
                                        @endif
                                    </span>
                                @else
                                    <span class="badge-base bg-yellow-100 text-yellow-800">Not Vaccinated</span>
                                @endif
                            </span>
                        </div>
                    @else
                        <div class="detail-row">
                            <span class="detail-label">Medical Record</span>
                            <span class="detail-value">
                                <span class="badge-base bg-yellow-100 text-yellow-800">No Medical Record Found</span>
                            </span>
                        </div>
                    @endif

                    <!-- Emergency Contacts -->
                    <div class="detail-row bg-gray-50 font-semibold">
                        <span class="detail-label"><i class="fas fa-phone-alt mr-2 text-blue-600"></i>Emergency Contacts</span>
                        <span></span>
                    </div>
                    
                    @if(!empty($emergencyContacts))
                        @foreach($emergencyContacts as $contact)
                            <div class="detail-row">
                                <span class="detail-label capitalize">{{ $contact['type'] ?? 'Contact' }}</span>
                                <span class="detail-value">
                                    <div class="text-right">
                                        <div class="font-semibold">{{ $contact['name'] ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-600">
                                            @if(isset($contact['number']))
                                                <a href="tel:{{ $contact['number'] }}" class="hover:text-blue-600">
                                                    {{ $student->formatPhoneNumber($contact['number']) }}
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </div>
                                </span>
                            </div>
                        @endforeach
                    @else
                        <div class="detail-row">
                            <span class="detail-label">Contacts</span>
                            <span class="detail-value text-gray-500">No emergency contacts registered</span>
                        </div>
                    @endif

                    <!-- Account Information -->
                    <div class="detail-row bg-gray-50 font-semibold">
                        <span class="detail-label"><i class="fas fa-info-circle mr-2 text-blue-600"></i>Account Information</span>
                        <span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Role</span>
                        <span class="detail-value">
                            <span class="badge-base bg-blue-100 text-blue-800 capitalize">{{ $student->role }}</span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email Verified</span>
                        <span class="detail-value">
                            @if($student->email_verified_at)
                                <span class="badge-base bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    {{ $student->email_verified_at->format('M d, Y') }}
                                </span>
                            @else
                                <span class="badge-base bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Not Verified
                                </span>
                            @endif
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Member Since</span>
                        <span class="detail-value">
                            @if($student->created_at)
                                {{ $student->created_at->format('M d, Y') }}
                                <span class="text-gray-500 text-xs">({{ $student->created_at->diffForHumans() }})</span>
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Last Updated</span>
                        <span class="detail-value">
                            @if($student->updated_at)
                                {{ $student->updated_at->format('M d, Y') }}
                                <span class="text-gray-500 text-xs">({{ $student->updated_at->diffForHumans() }})</span>
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="detail-card">
                <div class="detail-header">
                    <h3>
                        <i class="fas fa-magic text-blue-600"></i>
                        <span>Quick Actions</span>
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @if($appointment->isPending())
                            <form action="{{ route('nurse.appointments.accept', $appointment) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition font-medium text-sm flex items-center justify-center space-x-2">
                                    <i class="fas fa-check"></i>
                                    <span>Accept</span>
                                </button>
                            </form>
                        @endif

                        @if($appointment->canBeRescheduledByNurse())
                            <button onclick="openRescheduleModal()" class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium text-sm flex items-center justify-center space-x-2">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Reschedule</span>
                            </button>
                        @endif

                        @if($appointment->canBeCancelled())
                            <form action="{{ route('nurse.appointments.cancel', $appointment) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-medium text-sm flex items-center justify-center space-x-2">
                                    <i class="fas fa-ban"></i>
                                    <span>Cancel</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Timeline & History -->
        <div class="lg:col-span-1 space-y-6">

            <!-- Appointment Timeline -->
            <div class="detail-card">
                <div class="detail-header">
                    <h3>
                        <i class="fas fa-history text-blue-600"></i>
                        <span>Appointment Timeline</span>
                    </h3>
                </div>
                <div class="p-6">
                    <div class="timeline-container">
                        @if($appointment->created_at)
                            <div class="timeline-item">
                                <div class="timeline-dot bg-blue-500"></div>
                                <div class="timeline-content">
                                    <div class="timeline-label">Created</div>
                                    <div class="timeline-date">{{ $appointment->created_at->format('M d, Y H:i') }}</div>
                                </div>
                            </div>
                        @endif

                        @if($appointment->accepted_at)
                            <div class="timeline-item">
                                <div class="timeline-dot bg-green-500"></div>
                                <div class="timeline-content">
                                    <div class="timeline-label">Accepted</div>
                                    <div class="timeline-date">{{ $appointment->accepted_at->format('M d, Y H:i') }}</div>
                                </div>
                            </div>
                        @endif

                        @if($appointment->rescheduled_at)
                            <div class="timeline-item">
                                <div class="timeline-dot bg-yellow-500"></div>
                                <div class="timeline-content">
                                    <div class="timeline-label">Rescheduled</div>
                                    <div class="timeline-date">{{ $appointment->rescheduled_at->format('M d, Y H:i') }}</div>
                                </div>
                            </div>
                        @endif

                        @if($appointment->completed_at)
                            <div class="timeline-item">
                                <div class="timeline-dot bg-emerald-500"></div>
                                <div class="timeline-content">
                                    <div class="timeline-label">Completed</div>
                                    <div class="timeline-date">{{ $appointment->completed_at->format('M d, Y H:i') }}</div>
                                </div>
                            </div>
                        @endif

                        @if($appointment->cancelled_at)
                            <div class="timeline-item">
                                <div class="timeline-dot bg-red-500"></div>
                                <div class="timeline-content">
                                    <div class="timeline-label">Cancelled</div>
                                    <div class="timeline-date">{{ $appointment->cancelled_at->format('M d, Y H:i') }}</div>
                                </div>
                            </div>
                        @endif

                        @if(!$appointment->created_at && !$appointment->accepted_at && !$appointment->completed_at && !$appointment->cancelled_at)
                            <div class="text-center py-4 text-gray-500">
                                <i class="fas fa-hourglass-half text-2xl mb-2"></i>
                                <p class="text-sm">No events yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Student Consultation History -->
            <div class="detail-card">
                <div class="detail-header">
                    <h3>
                        <i class="fas fa-list text-blue-600"></i>
                        <span>Recent Appointments</span>
                    </h3>
                </div>
                <div class="max-h-96 overflow-y-auto appointments-list bg-gradient-to-b from-blue-50 to-white">
                    @php
                        $recentAppointments = \App\Models\Appointment::where('user_id', $appointment->user_id)
                            ->orderBy('appointment_date', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp

                    @forelse($recentAppointments as $apt)
                        <div class="appointment-item @if($apt->id === $appointment->id) bg-gradient-to-r from-blue-100 to-indigo-100 @endif" 
                             @if($apt->id !== $appointment->id) onclick="window.location.href='{{ route('nurse.appointments.show', $apt) }}'" @endif>
                            <div class="flex items-start justify-between mb-2">
                                <span class="appointment-reason">{{ $apt->reason }}</span>
                                <span class="text-xs {{ $apt->status_badge_class }} px-2 py-1 rounded-full">{{ $apt->status_display }}</span>
                            </div>
                            <div class="appointment-meta">
                                <i class="fas fa-calendar text-blue-600"></i>
                                <span>{{ $apt->formatted_date_time }}</span>
                            </div>
                            @if($apt->id === $appointment->id)
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs rounded-full font-semibold">
                                        <i class="fas fa-check-circle mr-1"></i>Current Appointment
                                    </span>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-3 text-gray-300"></i>
                            <p class="text-sm font-medium">No other appointments</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Student Stats -->
            <div class="detail-card">
                <div class="detail-header">
                    <h3>
                        <i class="fas fa-chart-bar text-blue-600"></i>
                        <span>Student Stats</span>
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    @php
                        $totalAppointments = \App\Models\Appointment::where('user_id', $appointment->user_id)->count();
                        $completedAppointments = \App\Models\Appointment::where('user_id', $appointment->user_id)->where('status', 'completed')->count();
                        $cancelledAppointments = \App\Models\Appointment::where('user_id', $appointment->user_id)->where('status', 'cancelled')->count();
                    @endphp

                    <div class="stat-card stat-card-blue">
                        <div class="flex items-center justify-between">
                            <span class="stat-label">Total Appointments</span>
                            <i class="fas fa-calendar text-blue-600 text-lg"></i>
                        </div>
                        <div class="stat-value text-blue-700">{{ $totalAppointments }}</div>
                    </div>
                    
                    <div class="stat-card stat-card-green">
                        <div class="flex items-center justify-between">
                            <span class="stat-label">Completed</span>
                            <i class="fas fa-check-circle text-green-600 text-lg"></i>
                        </div>
                        <div class="stat-value text-green-700">{{ $completedAppointments }}</div>
                    </div>
                    
                    <div class="stat-card stat-card-red">
                        <div class="flex items-center justify-between">
                            <span class="stat-label">Cancelled</span>
                            <i class="fas fa-times-circle text-red-600 text-lg"></i>
                        </div>
                        <div class="stat-value text-red-700">{{ $cancelledAppointments }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Reschedule Modal -->
<div id="rescheduleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl mx-auto my-4 sm:my-8 transform transition-all max-h-[95vh] overflow-y-auto">
        <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 sticky top-0">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-blue-600 text-lg"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900">Reschedule Appointment</h3>
            </div>
            <button onclick="closeRescheduleModal()" class="text-gray-400 hover:text-gray-600 p-2">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form action="{{ route('nurse.appointments.reschedule', $appointment) }}" method="POST" class="p-4 sm:p-6" id="rescheduleForm">
            @csrf
            @method('PATCH')

            <div class="space-y-4 mb-6">
                <div>
                    <label for="new_date" class="block text-sm font-semibold text-gray-900 mb-2">New Date <span class="text-red-500">*</span></label>
                    <input type="date" name="new_appointment_date" id="new_date" required min="{{ today()->format('Y-m-d') }}" max="{{ today()->addDays(30)->format('Y-m-d') }}" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="new_time" class="block text-sm font-semibold text-gray-900 mb-2">New Time <span class="text-red-500">*</span></label>
                    <input type="time" name="new_appointment_time" id="new_time" required class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="reason" class="block text-sm font-semibold text-gray-900 mb-2">Reason <span class="text-red-500">*</span></label>
                    <textarea name="reschedule_reason" id="reason" rows="3" required minlength="10" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" placeholder="Reason for rescheduling..."></textarea>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeRescheduleModal()" class="flex-1 px-4 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-indigo-700">Confirm</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openRescheduleModal() {
        document.getElementById('rescheduleModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeRescheduleModal() {
        document.getElementById('rescheduleModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.getElementById('rescheduleModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeRescheduleModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeRescheduleModal();
    });
</script>
@endpush