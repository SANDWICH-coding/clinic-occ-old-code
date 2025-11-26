@extends('layouts.nurse-app')

@section('title', 'Create Consultation')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl font-bold text-gray-800">New Consultation</h1>
            <p class="text-sm text-gray-500">Register a new student consultation</p>
        </div>
    </div>

    <!-- Form wrapping the entire content -->
    <form id="consultationForm" method="POST" action="{{ route('nurse.consultations.store') }}">
        @csrf
        
        <!-- Hidden student_id field at the top of the form -->
        <input type="hidden" name="student_id" id="student_id" value="{{ old('student_id') }}">

        <!-- Main grid with fixed height for independent scrolling -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-180px)]">
            
            <!-- LEFT COLUMN: Two flex sections -->
            <div class="lg:col-span-1 flex flex-col overflow-hidden gap-6">
                
                <!-- Search Student Section - Fixed Height -->
                <div class="bg-white shadow-md rounded-lg flex-shrink-0">
                    <div class="bg-blue-600 text-white px-6 py-3 rounded-t-lg">
                        <h6 class="text-lg font-semibold">Search Student</h6>
                    </div>
                    <div class="p-6">
                        <!-- Student Search -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search Student <span class="text-red-500">*</span></label>
                            
                            <!-- Validation Error Display -->
                            @error('student_id')
                                <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded-md">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-red-700 text-sm font-medium">{{ $message }}</span>
                                    </div>
                                </div>
                            @enderror
                            
                            <!-- Selected Student Badge -->
                            <div id="selectedStudentBadge" class="hidden mb-3">
                                <div class="bg-green-50 border border-green-200 rounded-md p-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <div>
                                                <span class="font-medium text-green-800">Selected Student: </span>
                                                <span id="selectedStudentName" class="text-green-700"></span>
                                            </div>
                                        </div>
                                        <button type="button" onclick="clearStudentSelection()" class="text-green-600 hover:text-green-800 text-sm font-medium underline">
                                            Change Student
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Search Input -->
                            <div id="studentSearchContainer">
                                <div class="relative">
                                    <input type="text" id="studentSearch" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter student ID, name, or email..." autocomplete="off">
                                    <button class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" type="button" onclick="clearStudentSearchInput()">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                                <div id="studentResults" class="absolute z-20 w-full bg-white border border-gray-200 rounded-md mt-1 max-h-48 overflow-y-auto shadow-lg hidden">
                                    <!-- Search results will be populated here -->
                                </div>
                                <div id="searchLoading" class="hidden mt-2">
                                    <div class="flex items-center justify-center text-gray-500">
                                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-sm">Searching students...</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Search Instructions -->
                            <div class="mt-2 text-xs text-gray-500">
                                <p>Start typing to search for students by ID, name, or email address.</p>
                            </div>
                        </div>

                        <!-- Today's Appointments Section -->
                        @if(isset($todaysAppointments) && $todaysAppointments->count() > 0)
                        <div id="todaysAppointmentsSection" class="mt-6">
                            <h6 class="text-sm font-medium text-gray-700 mb-2">Today's Appointments</h6>
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                @foreach($todaysAppointments as $appointment)
                                <div class="bg-blue-50 border border-blue-200 rounded-md p-3 cursor-pointer hover:bg-blue-100 transition-colors" 
                                     onclick="selectAppointmentStudent({{ $appointment['student_id'] }}, '{{ addslashes($appointment['student_name']) }}', '{{ addslashes($appointment['student_number']) }}')">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="font-medium text-blue-900 text-sm">{{ $appointment['student_name'] }}</div>
                                            <div class="text-xs text-blue-700 mt-1">
                                                <span class="inline-block bg-blue-100 px-2 py-1 rounded">ID: {{ $appointment['student_number'] }}</span>
                                            </div>
                                            <div class="text-xs text-blue-600 mt-1">
                                                <strong>Time:</strong> {{ $appointment['appointment_time'] }}
                                            </div>
                                            @if($appointment['reason'])
                                            <div class="text-xs text-blue-600 mt-1">
                                                <strong>Reason:</strong> {{ Str::limit($appointment['reason'], 50) }}
                                            </div>
                                            @endif
                                        </div>
                                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded whitespace-nowrap">Appointment</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Student Medical Data - Scrollable Section -->
                <div id="studentMedicalData" class="bg-white shadow-md rounded-lg hidden flex-1 overflow-y-auto custom-scroll">
                    <div class="bg-blue-600 text-white px-6 py-3 rounded-t-lg sticky top-0 z-10">
                        <h6 class="text-lg font-semibold">Student Medical Information</h6>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Consultation Type Indicator -->
                        <div id="consultationTypeIndicator" class="hidden p-4 rounded-md border-l-4">
                            <div class="flex items-center">
                                <svg id="typeIndicatorIcon" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <span id="typeIndicatorText" class="text-sm font-medium"></span>
                                    <div id="typeIndicatorSubtext" class="text-xs mt-1"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Student Details -->
                        <div class="border-b pb-6">
                            <h6 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4">Student Details</h6>
                            <div class="grid grid-cols-1 gap-4">
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Full Name</p>
                                        <p class="text-sm text-gray-800 font-medium"><span id="studentFullName">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">First Name</p>
                                        <p class="text-sm text-gray-800"><span id="studentFirstName">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Last Name</p>
                                        <p class="text-sm text-gray-800"><span id="studentLastName">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Student ID</p>
                                        <p class="text-sm text-gray-800 font-medium"><span id="studentId">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Email</p>
                                        <p class="text-sm text-gray-800"><span id="studentEmail">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Phone</p>
                                        <p class="text-sm text-gray-800"><span id="studentPhone">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Gender</p>
                                        <p class="text-sm text-gray-800 font-medium"><span id="studentGender">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Age</p>
                                        <p class="text-sm text-gray-800 font-medium"><span id="studentAge">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Date of Birth</p>
                                        <p class="text-sm text-gray-800"><span id="studentBirthDate">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Address</p>
                                        <p class="text-sm text-gray-800"><span id="studentAddress">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Course</p>
                                        <p class="text-sm text-gray-800"><span id="studentCourse">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Year Level</p>
                                        <p class="text-sm text-gray-800"><span id="studentYearLevel">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Section</p>
                                        <p class="text-sm text-gray-800"><span id="studentSection">No record</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Medical Record Summary -->
                        <div class="border-b pb-6">
                            <h6 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4">Medical Summary</h6>
                            <div class="grid grid-cols-1 gap-4">
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Blood Type</p>
                                        <p class="text-sm text-gray-800 font-medium"><span id="summaryBloodType">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Height</p>
                                        <p class="text-sm text-gray-800"><span id="summaryHeight">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Weight</p>
                                        <p class="text-sm text-gray-800"><span id="summaryWeight">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">BMI</p>
                                        <p class="text-sm text-gray-800"><span id="summaryBMI">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Blood Pressure</p>
                                        <p class="text-sm text-gray-800"><span id="summaryBloodPressure">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Allergies</p>
                                        <p class="text-sm text-gray-800"><span id="summaryAllergies">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Current Medications</p>
                                        <p class="text-sm text-gray-800"><span id="summaryMedications">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Vaccination Status</p>
                                        <p class="text-sm text-gray-800"><span id="summaryVaccination">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Vaccine Name</p>
                                        <p class="text-sm text-gray-800"><span id="summaryVaccineName">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Vaccine Date</p>
                                        <p class="text-sm text-gray-800"><span id="summaryVaccineDate">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Booster Type</p>
                                        <p class="text-sm text-gray-800"><span id="summaryBoosterType">No record</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Past Illnesses</p>
                                        <p class="text-sm text-gray-800"><span id="summaryPastIllnesses">No record</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PWD Information -->
                        <div class="border-b pb-6">
                            <h6 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4">PWD Information</h6>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">PWD Status</p>
                                    <p class="text-sm text-gray-800 font-medium"><span id="summaryPWDStatus">No record</span></p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">PWD ID</p>
                                    <p class="text-sm text-gray-800"><span id="summaryPWDId">No record</span></p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Disability Details</p>
                                    <p class="text-sm text-gray-800"><span id="summaryDisabilityDetails">No record</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contacts -->
                        <div class="border-b pb-6">
                            <h6 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4">Emergency Contacts</h6>
                            <div class="space-y-4">
                                <div class="bg-red-50 p-3 rounded-md">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Primary Contact</p>
                                    <p class="text-sm text-gray-800 font-medium"><span id="summaryEmergency1Name">No record</span></p>
                                    <p class="text-sm text-gray-600"><span id="summaryEmergency1Phone">No record</span></p>
                                    <p class="text-xs text-gray-500"><span id="summaryEmergency1Relationship">No record</span></p>
                                </div>
                                <div class="bg-red-50 p-3 rounded-md">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Secondary Contact</p>
                                    <p class="text-sm text-gray-800 font-medium"><span id="summaryEmergency2Name">No record</span></p>
                                    <p class="text-sm text-gray-600"><span id="summaryEmergency2Phone">No record</span></p>
                                    <p class="text-xs text-gray-500"><span id="summaryEmergency2Relationship">No record</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Combined History Section -->
                        <div>
                            <h6 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4">Student History</h6>
                            
                            <!-- Recent Consultations -->
                            <div class="mb-4">
                                <h6 class="text-xs font-medium text-gray-600 mb-2 flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    Recent Consultations
                                </h6>
                                <div id="recentConsultations" class="text-sm text-gray-600 space-y-2">
                                    <div class="text-center py-2 text-gray-400 text-xs">
                                        No record
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Symptoms -->
                            <div class="mb-4">
                                <h6 class="text-xs font-medium text-gray-600 mb-2 flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                    Recent Symptoms
                                </h6>
                                <div id="recentSymptoms" class="text-sm text-gray-600 space-y-2">
                                    <div class="text-center py-2 text-gray-400 text-xs">
                                        No record
                                    </div>
                                </div>
                            </div>

                            <!-- Appointment History -->
                            <div>
                                <h6 class="text-xs font-medium text-gray-600 mb-2 flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Recent Appointments
                                </h6>
                                <div id="recentAppointments" class="text-sm text-gray-600 space-y-2">
                                    <div class="text-center py-2 text-gray-400 text-xs">
                                        No record
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Consultation Form - Scrollable -->
            <div class="lg:col-span-2 overflow-y-auto custom-scroll">
                <div class="bg-white shadow-md rounded-lg">
                    <div class="bg-blue-600 text-white px-6 py-3 rounded-t-lg sticky top-0 z-10">
                        <h6 class="text-lg font-semibold">Consultation Details</h6>
                    </div>
                    <div class="p-6">
                        <!-- Flashed Session Messages -->
                        @if (session('error'))
                            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-md border border-red-300">
                                <i class="fas fa-exclamation-triangle mr-2"></i>{!! session('error') !!}
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-md border border-green-300">
                                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                            </div>
                        @endif

                        <!-- Consultation Type and Priority -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                                <select name="type" id="typeSelect" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select Type</option>
                                    @foreach($consultationTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Priority <span class="text-red-500">*</span></label>
                                <select name="priority" id="prioritySelect" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select Priority</option>
                                    @foreach($consultationPriorities as $key => $label)
                                        <option value="{{ $key }}" {{ old('priority') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('priority')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Chief Complaint and Pain Level -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Chief Complaint <span class="text-red-500">*</span></label>
                                <textarea name="chief_complaint" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="2" placeholder="Main reason for visit..." required>{{ old('chief_complaint') }}</textarea>
                                @error('chief_complaint')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pain Level (1-10)</label>
                                <div class="flex items-center">
                                    <input type="range" name="pain_level" id="painLevel" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer" min="0" max="10" value="{{ old('pain_level', 0) }}">
                                    <span id="painValue" class="ml-3 font-bold text-gray-800">{{ old('pain_level', 0) }}</span>
                                </div>
                                <div class="text-sm text-gray-500 mt-1"></div>
                                <div class="mt-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" id="noPain" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" {{ old('pain_level', 0) == 0 ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-500">No pain reported</span>
                                    </label>
                                </div>
                                @error('pain_level')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Symptoms Description -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Symptoms Description</label>
                            <textarea name="symptoms_description" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="3" placeholder="Detailed description of symptoms, onset, duration, severity...">{{ old('symptoms_description') }}</textarea>
                            @error('symptoms_description')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Vital Signs -->
                        <div class="bg-gray-50 rounded-md p-4 mb-4">
                            <h6 class="text-lg font-semibold text-gray-800 mb-3">
                                <svg class="w-5 h-5 inline-block mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                Vital Signs
                            </h6>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Temperature (°C)</label>
                                    <input type="number" name="vital_signs[temperature]" step="0.1" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="36.6" value="{{ old('vital_signs.temperature') }}">
                                    @error('vital_signs.temperature')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Blood Pressure</label>
                                    <div class="flex items-center">
                                        <input type="number" name="vital_signs[blood_pressure_systolic]" class="w-20 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="120" value="{{ old('vital_signs.blood_pressure_systolic') }}">
                                        <span class="mx-2 text-gray-500">/</span>
                                        <input type="number" name="vital_signs[blood_pressure_diastolic]" class="w-20 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="80" value="{{ old('vital_signs.blood_pressure_diastolic') }}">
                                        <span class="ml-2 text-sm text-gray-500">mmHg</span>
                                    </div>
                                    @error('vital_signs.blood_pressure_systolic')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                    @error('vital_signs.blood_pressure_diastolic')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Heart Rate</label>
                                    <input type="number" name="vital_signs[heart_rate]" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="72" value="{{ old('vital_signs.heart_rate') }}">
                                    <div class="text-sm text-gray-500 mt-1">BPM</div>
                                    @error('vital_signs.heart_rate')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">O₂ Saturation</label>
                                    <input type="number" name="vital_signs[oxygen_saturation]" step="0.1" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="98" value="{{ old('vital_signs.oxygen_saturation') }}">
                                    <div class="text-sm text-gray-500 mt-1">%</div>
                                    @error('vital_signs.oxygen_saturation')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Respiratory Rate</label>
                                    <input type="number" name="vital_signs[respiratory_rate]" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="16" value="{{ old('vital_signs.respiratory_rate') }}">
                                    <div class="text-sm text-gray-500 mt-1">per min</div>
                                    @error('vital_signs.respiratory_rate')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Weight</label>
                                    <input type="number" name="vital_signs[weight]" step="0.1" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="60" value="{{ old('vital_signs.weight') }}">
                                    <div class="text-sm text-gray-500 mt-1">kg</div>
                                    @error('vital_signs.weight')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Height</label>
                                    <input type="number" name="vital_signs[height]" step="0.1" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="170" value="{{ old('vital_signs.height') }}">
                                    <div class="text-sm text-gray-500 mt-1">cm</div>
                                    @error('vital_signs.height')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Treatment & Diagnosis Section -->
                        <div class="bg-white border border-gray-200 rounded-md p-4 mb-4">
                            <h6 class="text-lg font-semibold text-gray-800 mb-3">
                                <svg class="w-5 h-5 inline-block mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                Treatment & Diagnosis
                            </h6>
                            
                            <!-- Diagnosis -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Diagnosis <span class="text-red-500">*</span></label>
                                <textarea name="diagnosis" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="2" placeholder="Primary diagnosis or assessment..." required>{{ old('diagnosis') }}</textarea>
                                @error('diagnosis')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Treatment Provided -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Treatment Provided <span class="text-red-500">*</span></label>
                                <textarea name="treatment_provided" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="2" placeholder="Describe treatment given during consultation..." required>{{ old('treatment_provided') }}</textarea>
                                @error('treatment_provided')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Medications Given -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Medications Given</label>
                                <textarea name="medications_given" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="2" placeholder="List medications administered with dosage and instructions...">{{ old('medications_given') }}</textarea>
                                @error('medications_given')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Procedures Performed -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Procedures Performed</label>
                                <textarea name="procedures_performed" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="2" placeholder="List any medical procedures performed...">{{ old('procedures_performed') }}</textarea>
                                @error('procedures_performed')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Home Care Instructions -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Home Care Instructions</label>
                                <textarea name="home_care_instructions" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="2" placeholder="Instructions for home care and follow-up...">{{ old('home_care_instructions') }}</textarea>
                                @error('home_care_instructions')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Initial Notes -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Initial Notes</label>
                            <textarea name="initial_notes" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="3" placeholder="Any initial observations, parent concerns, or relevant information...">{{ old('initial_notes') }}</textarea>
                            @error('initial_notes')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-between">
                            <a href="{{ route('nurse.consultations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-400 text-white rounded-md cursor-not-allowed transition" id="submitBtn" disabled>
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                </svg>
                                Register Consultation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Add route configuration at the top
const ROUTES = {
    searchStudents: "{{ route('nurse.consultations.students.search') }}",
    checkStudentStatus: "{{ route('nurse.consultations.check-student-status', ['studentId' => ':studentId']) }}",
    getStudentMedicalData: "{{ route('nurse.consultations.students.medical-data', ['studentId' => ':studentId']) }}"
};

// Global variable to track selected student and prevent memory leaks
let selectedStudent = null;
let searchTimeout = null;
let activeSearchController = null;
let activeMedicalDataController = null;
let activeStatusController = null;

// Resource cleanup function
function cleanupResources(type = 'all') {
    console.log('Cleaning up resources:', type);
    
    if (type === 'all' || type === 'search') {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
            searchTimeout = null;
        }
        if (activeSearchController) {
            console.log('Aborting search request');
            activeSearchController.abort();
            activeSearchController = null;
        }
    }
    
    if (type === 'all' || type === 'medical') {
        if (activeMedicalDataController) {
            console.log('Aborting medical data request');
            activeMedicalDataController.abort();
            activeMedicalDataController = null;
        }
    }
    
    if (type === 'all' || type === 'status') {
        if (activeStatusController) {
            console.log('Aborting status request');
            activeStatusController.abort();
            activeStatusController = null;
        }
    }
}

// Enhanced search with better debouncing and error handling
function performStudentSearch(searchTerm) {
    console.log('Performing search for:', searchTerm);
    
    if (activeSearchController) {
        console.log('Cancelling previous search request');
        activeSearchController.abort();
    }
    
    const studentResults = document.getElementById('studentResults');
    const searchLoading = document.getElementById('searchLoading');
    
    searchLoading.style.display = 'block';
    studentResults.style.display = 'none';
    
    activeSearchController = new AbortController();
    
    fetch(`${ROUTES.searchStudents}?search=${encodeURIComponent(searchTerm)}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
        signal: activeSearchController.signal
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        console.log('Search results received:', data.students?.length || 0, 'students');
        searchLoading.style.display = 'none';
        
        if (data.students && data.students.length > 0) {
            studentResults.innerHTML = data.students.map(student => `
                <div class="border-b last:border-b-0">
                    <a class="block px-4 py-3 hover:bg-gray-50 cursor-pointer transition duration-150" 
                       onclick="selectStudent(${student.id}, '${escapeHtml(student.full_name)}', '${escapeHtml(student.student_id)}', ${student.has_active_consultation})">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">${escapeHtml(student.full_name)}</div>
                                <div class="text-sm text-gray-500 mt-1">
                                    <span class="inline-block bg-gray-100 px-2 py-1 rounded text-xs">ID: ${escapeHtml(student.student_id)}</span>
                                    ${student.email ? `<span class="ml-2">${escapeHtml(student.email)}</span>` : ''}
                                </div>
                            </div>
                            ${student.has_active_consultation ? 
                                '<span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded whitespace-nowrap">Active Consultation</span>' : 
                                '<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded whitespace-nowrap">Available</span>'
                            }
                        </div>
                    </a>
                </div>
            `).join('');
            studentResults.style.display = 'block';
        } else {
            studentResults.innerHTML = `
                <div class="px-4 py-6 text-center text-gray-500">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p>No students found</p>
                </div>
            `;
            studentResults.style.display = 'block';
        }
    })
    .catch(error => {
        if (error.name === 'AbortError') {
            console.log('Search request was cancelled for new search');
            return;
        }
        console.error('Search error:', error);
        searchLoading.style.display = 'none';
        showAlert('Error searching students. Please try again.', 'error');
    });
}

// Student selection functions
function selectAppointmentStudent(studentId, studentName, studentNumber) {
    console.log('Selecting appointment student:', { studentId, studentName, studentNumber });
    
    cleanupResources('search');
    selectStudent(studentId, studentName, studentNumber, false);
    autoSelectConsultationType('appointment');
    window.updateFormState();
    
    setTimeout(() => {
        loadStudentMedicalData(studentId);
    }, 100);
}

function selectStudent(studentId, studentName, studentNumber, hasActiveConsultation = false) {
    console.log('Selecting student:', { studentId, studentName, studentNumber, hasActiveConsultation });

    if (!studentId || !studentName) {
        console.error('Invalid student data provided');
        showAlert('Error: Invalid student data', 'error');
        return;
    }

    try {
        selectedStudent = {
            id: studentId,
            name: studentName,
            number: studentNumber,
            hasActiveConsultation: hasActiveConsultation
        };

        document.getElementById('student_id').value = studentId;
        
        const selectedStudentBadge = document.getElementById('selectedStudentBadge');
        const selectedStudentName = document.getElementById('selectedStudentName');
        const studentSearchContainer = document.getElementById('studentSearchContainer');
        const studentResults = document.getElementById('studentResults');
        const medicalDataSection = document.getElementById('studentMedicalData');
        const todaysAppointmentsSection = document.getElementById('todaysAppointmentsSection');

        if (selectedStudentBadge) selectedStudentBadge.classList.remove('hidden');
        if (selectedStudentName) selectedStudentName.textContent = `${studentName} (${studentNumber})`;
        if (studentSearchContainer) studentSearchContainer.style.display = 'none';
        if (studentResults) studentResults.style.display = 'none';
        if (medicalDataSection) medicalDataSection.classList.remove('hidden');
        if (todaysAppointmentsSection) todaysAppointmentsSection.style.display = 'none';

        setTimeout(() => {
            loadStudentMedicalData(studentId);
            autoDetectConsultationType(studentId);
            window.updateFormState();
        }, 100);

        if (hasActiveConsultation) {
            showAlert('This student already has an active consultation. Please review before creating a new one.', 'warning');
        } else {
            showAlert('Student selected successfully! Loading medical data...', 'success');
        }
    } catch (error) {
        console.error('Error in selectStudent:', error);
        showAlert('Error selecting student. Please try again.', 'error');
    }
}

function clearStudentSelection() {
    console.log('Clearing student selection');
    
    cleanupResources('search');
    selectedStudent = null;
    document.getElementById('student_id').value = '';
    
    const selectedStudentBadge = document.getElementById('selectedStudentBadge');
    const studentSearchContainer = document.getElementById('studentSearchContainer');
    const studentMedicalData = document.getElementById('studentMedicalData');
    const studentSearch = document.getElementById('studentSearch');
    const consultationTypeIndicator = document.getElementById('consultationTypeIndicator');
    const typeSelect = document.getElementById('typeSelect');
    const todaysAppointmentsSection = document.getElementById('todaysAppointmentsSection');
    const studentResults = document.getElementById('studentResults');
    
    if (selectedStudentBadge) selectedStudentBadge.classList.add('hidden');
    if (studentSearchContainer) studentSearchContainer.style.display = 'block';
    if (studentMedicalData) studentMedicalData.classList.add('hidden');
    if (studentSearch) studentSearch.value = '';
    if (consultationTypeIndicator) consultationTypeIndicator.classList.add('hidden');
    if (typeSelect) typeSelect.value = '';
    if (studentResults) studentResults.style.display = 'none';
    if (todaysAppointmentsSection) todaysAppointmentsSection.style.display = 'block';
    
    window.updateFormState();
    showAlert('Student selection cleared. You can now select a different student.', 'info');
}

function clearStudentSearchInput() {
    const studentSearch = document.getElementById('studentSearch');
    const studentResults = document.getElementById('studentResults');
    const searchLoading = document.getElementById('searchLoading');
    
    if (studentSearch) studentSearch.value = '';
    if (studentResults) studentResults.style.display = 'none';
    if (searchLoading) searchLoading.style.display = 'none';
    
    if (activeSearchController) {
        activeSearchController.abort();
        activeSearchController = null;
    }
}

// Medical data functions
function loadStudentMedicalData(studentId) {
    console.log('Loading medical data for student ID:', studentId);
    
    if (!studentId) {
        console.error('No student ID provided for medical data');
        return;
    }

    if (activeMedicalDataController) {
        console.log('Cancelling previous medical data request');
        activeMedicalDataController.abort();
    }

    const medicalDataSection = document.getElementById('studentMedicalData');
    if (medicalDataSection) {
        medicalDataSection.innerHTML = `
            <div class="bg-blue-600 text-white px-6 py-3 rounded-t-lg sticky top-0 z-10">
                <h6 class="text-lg font-semibold">Student Medical Information</h6>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-center py-8">
                    <svg class="animate-spin h-8 w-8 text-blue-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-600">Loading medical data...</span>
                </div>
            </div>
        `;
    }

    const url = ROUTES.getStudentMedicalData.replace(':studentId', studentId);
    activeMedicalDataController = new AbortController();
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        signal: activeMedicalDataController.signal
    })
    .then(response => {
        console.log('Medical data response status:', response.status);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        console.log('Medical data loaded successfully:', data);
        if (data.error) throw new Error(data.error);
        updateMedicalDataPanel(data);
    })
    .catch(error => {
        if (error.name === 'AbortError') {
            console.log('Medical data request was aborted - new request started');
            return;
        }
        console.error('Error loading medical data:', error);
        showAlert('Error loading student medical data: ' + error.message, 'error');
        
        if (medicalDataSection) {
            medicalDataSection.innerHTML = `
                <div class="bg-blue-600 text-white px-6 py-3 rounded-t-lg sticky top-0 z-10">
                    <h6 class="text-lg font-semibold">Student Medical Information</h6>
                </div>
                <div class="p-6 text-center text-red-500">
                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="font-medium">Failed to load medical data</p>
                    <p class="text-sm mt-1">${error.message}</p>
                    <button onclick="loadStudentMedicalData(${studentId})" class="mt-3 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition text-sm">
                        Retry Loading
                    </button>
                </div>
            `;
        }
    });
}

function updateMedicalDataPanel(data) {
    const medicalDataSection = document.getElementById('studentMedicalData');
    if (!medicalDataSection) return;

    const student = data.student || {};
    const medicalRecord = data.medical_record || {};
    const medicalSummary = data.medical_summary || {};

    const fragment = document.createDocumentFragment();
    const container = document.createElement('div');
    
    container.innerHTML = `
        <div class="bg-blue-600 text-white px-6 py-3 rounded-t-lg sticky top-0 z-10">
            <h6 class="text-lg font-semibold">Student Medical Information</h6>
        </div>
        <div class="p-6 space-y-6">
            <!-- Consultation Type Indicator -->
            <div id="consultationTypeIndicator" class="hidden p-4 rounded-md border-l-4">
                <div class="flex items-center">
                    <svg id="typeIndicatorIcon" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <span id="typeIndicatorText" class="text-sm font-medium"></span>
                        <div id="typeIndicatorSubtext" class="text-xs mt-1"></div>
                    </div>
                </div>
            </div>

            <!-- Student Details -->
            <div class="border-b pb-6">
                <h6 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4">Student Details</h6>
                <div class="grid grid-cols-1 gap-4">
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Full Name</p>
                            <p class="text-sm text-gray-800 font-medium">${escapeHtml(student.full_name || 'No record')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">First Name</p>
                            <p class="text-sm text-gray-800">${escapeHtml(student.first_name || 'No record')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Last Name</p>
                            <p class="text-sm text-gray-800">${escapeHtml(student.last_name || 'No record')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Student ID</p>
                            <p class="text-sm text-gray-800 font-medium">${escapeHtml(student.student_id || 'No record')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Email</p>
                            <p class="text-sm text-gray-800">${escapeHtml(student.email || 'No record')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Phone</p>
                            <p class="text-sm text-gray-800">${escapeHtml(student.phone || 'Not provided')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Gender</p>
                            <p class="text-sm text-gray-800 font-medium">${escapeHtml(student.gender || 'Not specified')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Age</p>
                            <p class="text-sm text-gray-800 font-medium">${student.age ? `${student.age} years` : 'Not specified'}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Date of Birth</p>
                            <p class="text-sm text-gray-800">${escapeHtml(student.birth_date || student.date_of_birth || 'Not provided')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Address</p>
                            <p class="text-sm text-gray-800">${escapeHtml(student.address || 'Not provided')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Course</p>
                            <p class="text-sm text-gray-800">${escapeHtml(student.course || 'Not specified')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Year Level</p>
                            <p class="text-sm text-gray-800">${escapeHtml(student.year_level || 'Not specified')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Section</p>
                            <p class="text-sm text-gray-800">${escapeHtml(student.section || 'Not specified')}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical Record Summary -->
            <div class="border-b pb-6">
                <h6 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4">Medical Summary</h6>
                <div class="grid grid-cols-1 gap-4">
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Blood Type</p>
                            <p class="text-sm text-gray-800 font-medium">${escapeHtml(medicalSummary.blood_type || medicalRecord.blood_type || 'Not recorded')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Height</p>
                            <p class="text-sm text-gray-800">${escapeHtml(medicalSummary.height || (medicalRecord.height ? `${medicalRecord.height} cm` : 'Not recorded'))}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Weight</p>
                            <p class="text-sm text-gray-800">${escapeHtml(medicalSummary.weight || (medicalRecord.weight ? `${medicalRecord.weight} kg` : 'Not recorded'))}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">BMI</p>
                            <p class="text-sm text-gray-800">${escapeHtml(medicalSummary.bmi || medicalRecord.bmi || 'Not calculated')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Blood Pressure</p>
                            <p class="text-sm text-gray-800">${escapeHtml(medicalSummary.blood_pressure || 'Not recorded')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Allergies</p>
                            <p class="text-sm text-gray-800">${escapeHtml(medicalSummary.allergies || medicalRecord.allergies || 'None recorded')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Current Medications</p>
                            <p class="text-sm text-gray-800">${escapeHtml(medicalSummary.medications || medicalRecord.current_medications || 'None recorded')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Vaccination Status</p>
                            <p class="text-sm text-gray-800">${escapeHtml(medicalSummary.vaccination_status || (medicalRecord.is_fully_vaccinated ? 'Fully Vaccinated' : 'Not Fully Vaccinated'))}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Vaccine Name</p>
                            <p class="text-sm text-gray-800">${escapeHtml(medicalRecord.vaccine_name || medicalRecord.vaccine_type || 'No record')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Vaccine Date</p>
                            <p class="text-sm text-gray-800">${escapeHtml(medicalRecord.vaccine_date || 'No record')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Booster Type</p>
                            <p class="text-sm text-gray-800">${escapeHtml(medicalRecord.booster_type || 'No record')}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Past Illnesses</p>
                            <p class="text-sm text-gray-800">${escapeHtml(medicalSummary.past_hospitalizations || medicalRecord.past_illnesses || 'None recorded')}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PWD Information -->
            <div class="border-b pb-6">
                <h6 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4">PWD Information</h6>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">PWD Status</p>
                        <p class="text-sm text-gray-800 font-medium">${medicalRecord.is_pwd ? 'Yes' : 'No'}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">PWD ID</p>
                        <p class="text-sm text-gray-800">${escapeHtml(medicalRecord.pwd_id || 'No record')}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Disability Details</p>
                        <p class="text-sm text-gray-800">${escapeHtml(medicalRecord.pwd_disability_details || medicalRecord.pwd_reason || 'No record')}</p>
                    </div>
                </div>
            </div>

            <!-- Emergency Contacts -->
            <div class="border-b pb-6">
                <h6 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4">Emergency Contacts</h6>
                <div class="space-y-4">
                    <div class="bg-red-50 p-3 rounded-md">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Primary Contact</p>
                        <p class="text-sm text-gray-800 font-medium">${escapeHtml((medicalRecord.emergency_contact_1 && medicalRecord.emergency_contact_1.name) || 'No record')}</p>
                        <p class="text-sm text-gray-600">${escapeHtml((medicalRecord.emergency_contact_1 && medicalRecord.emergency_contact_1.phone) || 'No record')}</p>
                        <p class="text-xs text-gray-500">${escapeHtml((medicalRecord.emergency_contact_1 && medicalRecord.emergency_contact_1.relationship) ? `Relationship: ${medicalRecord.emergency_contact_1.relationship}` : 'No record')}</p>
                    </div>
                    <div class="bg-red-50 p-3 rounded-md">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Secondary Contact</p>
                        <p class="text-sm text-gray-800 font-medium">${escapeHtml((medicalRecord.emergency_contact_2 && medicalRecord.emergency_contact_2.name) || 'No record')}</p>
                        <p class="text-sm text-gray-600">${escapeHtml((medicalRecord.emergency_contact_2 && medicalRecord.emergency_contact_2.phone) || 'No record')}</p>
                        <p class="text-xs text-gray-500">${escapeHtml((medicalRecord.emergency_contact_2 && medicalRecord.emergency_contact_2.relationship) ? `Relationship: ${medicalRecord.emergency_contact_2.relationship}` : 'No record')}</p>
                    </div>
                </div>
            </div>

            <!-- Combined History Section -->
            <div>
                <h6 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4">Student History</h6>
                ${updateHistorySectionsHTML(data)}
            </div>
        </div>
    `;

    while (container.firstChild) {
        fragment.appendChild(container.firstChild);
    }
    
    medicalDataSection.innerHTML = '';
    medicalDataSection.appendChild(fragment);
    console.log('Medical data panel updated successfully');
}

function updateHistorySectionsHTML(data) {
    const consultations = data.recent_consultations || [];
    const symptoms = data.recent_symptoms || [];
    const appointments = data.recent_appointments || [];

    return `
        <!-- Recent Consultations -->
        <div class="mb-4">
            <h6 class="text-xs font-medium text-gray-600 mb-2 flex items-center">
                <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Recent Consultations
            </h6>
            <div class="text-sm text-gray-600 space-y-2">
                ${consultations.length > 0 ? consultations.slice(0, 3).map(consultation => 
                    `<div class="bg-blue-50 border border-blue-100 rounded-md p-2 text-xs">
                        <div class="flex justify-between items-start mb-1">
                            <div class="font-medium text-blue-800 truncate">${escapeHtml(consultation.chief_complaint || 'No record')}</div>
                            <span class="bg-blue-200 text-blue-800 px-1 py-0.5 rounded text-xs">${consultation.priority || 'N/A'}</span>
                        </div>
                        <div class="text-blue-600">${consultation.consultation_date || consultation.date || 'No date'}</div>
                    </div>`
                ).join('') : '<div class="text-center py-2 text-gray-400 text-xs">No record</div>'}
            </div>
        </div>

        <!-- Recent Symptoms -->
        <div class="mb-4">
            <h6 class="text-xs font-medium text-gray-600 mb-2 flex items-center">
                <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                Recent Symptoms
            </h6>
            <div class="text-sm text-gray-600 space-y-2">
                ${symptoms.length > 0 ? symptoms.slice(0, 3).map(symptom => 
                    `<div class="bg-green-50 border border-green-100 rounded-md p-2 text-xs">
                        <div class="flex justify-between items-start mb-1">
                            <div class="font-medium text-green-800 truncate">${escapeHtml(symptom.symptoms || symptom.description || 'No record')}</div>
                        </div>
                        <div class="text-green-600">${symptom.logged_at || symptom.created_at || 'No date'}</div>
                    </div>`
                ).join('') : '<div class="text-center py-2 text-gray-400 text-xs">No record</div>'}
            </div>
        </div>

        <!-- Appointment History -->
        <div>
            <h6 class="text-xs font-medium text-gray-600 mb-2 flex items-center">
                <svg class="w-4 h-4 mr-1 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Recent Appointments
            </h6>
            <div class="text-sm text-gray-600 space-y-2">
                ${appointments.length > 0 ? appointments.slice(0, 3).map(appointment => 
                    `<div class="bg-purple-50 border border-purple-100 rounded-md p-2 text-xs">
                        <div class="flex justify-between items-start mb-1">
                            <div class="font-medium text-purple-800 truncate">${escapeHtml(appointment.reason || 'No record')}</div>
                            <span class="bg-purple-200 text-purple-800 px-1 py-0.5 rounded text-xs">${appointment.status_display || appointment.status || 'Unknown'}</span>
                        </div>
                        <div class="text-purple-600">${appointment.date || appointment.appointment_date || 'No date'}</div>
                    </div>`
                ).join('') : '<div class="text-center py-2 text-gray-400 text-xs">No record</div>'}
            </div>
        </div>
    `;
}

// Consultation type functions
function autoDetectConsultationType(studentId) {
    const url = ROUTES.checkStudentStatus.replace(':studentId', studentId);
    
    if (activeStatusController) {
        activeStatusController.abort();
    }
    
    activeStatusController = new AbortController();
    
    fetch(url, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
        signal: activeStatusController.signal
    })
    .then(response => response.json())
    .then(data => {
        if (data.has_appointment_today) {
            autoSelectConsultationType('appointment', data);
        } else {
            autoSelectConsultationType('walk_in', data);
        }
    })
    .catch(error => {
        if (error.name === 'AbortError') return;
        console.error('Error detecting consultation type:', error);
        autoSelectConsultationType('walk_in');
    });
}

function autoSelectConsultationType(type, data = null) {
    const typeSelect = document.getElementById('typeSelect');
    const indicator = document.getElementById('consultationTypeIndicator');
    const indicatorIcon = document.getElementById('typeIndicatorIcon');
    const indicatorText = document.getElementById('typeIndicatorText');
    const indicatorSubtext = document.getElementById('typeIndicatorSubtext');

    if (!typeSelect) return;

    if (typeSelect.value === '') {
        typeSelect.value = type;
        
        if (indicator) {
            indicator.classList.remove('hidden');
            
            if (type === 'appointment') {
                indicator.className = 'p-4 rounded-md border-l-4 bg-blue-50 border-blue-200 text-blue-700';
                if (indicatorIcon) indicatorIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>';
                if (indicatorText) indicatorText.textContent = 'Appointment Consultation';
                
                if (data && data.appointment_details) {
                    const appointment = data.appointment_details;
                    try {
                        const appointmentTime = new Date(appointment.appointment_date + ' ' + appointment.appointment_time).toLocaleString();
                        if (indicatorSubtext) indicatorSubtext.textContent = `Scheduled for: ${appointmentTime}`;
                    } catch (e) {
                        if (indicatorSubtext) indicatorSubtext.textContent = 'Student has a scheduled appointment today';
                    }
                } else {
                    if (indicatorSubtext) indicatorSubtext.textContent = 'Student has a scheduled appointment today';
                }
            } else {
                indicator.className = 'p-4 rounded-md border-l-4 bg-green-50 border-green-200 text-green-700';
                if (indicatorIcon) indicatorIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>';
                if (indicatorText) indicatorText.textContent = 'Walk-in Consultation';
                if (indicatorSubtext) indicatorSubtext.textContent = 'Student does not have a scheduled appointment today';
            }
        }
    }
}

// Utility functions
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showAlert(message, type = 'info') {
    const existingAlert = document.querySelector('.custom-alert');
    if (existingAlert) existingAlert.remove();

    const alertDiv = document.createElement('div');
    alertDiv.className = `custom-alert fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg transition-all duration-300 transform translate-x-full ${
        type === 'error' ? 'bg-red-100 border-l-4 border-red-500 text-red-700' : 
        type === 'warning' ? 'bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700' : 
        type === 'success' ? 'bg-green-100 border-l-4 border-green-500 text-green-700' : 
        'bg-blue-100 border-l-4 border-blue-500 text-blue-700'
    }`;
    
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
    setTimeout(() => alertDiv.classList.remove('translate-x-full'), 10);
    setTimeout(() => {
        alertDiv.classList.add('translate-x-full');
        setTimeout(() => alertDiv.remove(), 300);
    }, 5000);
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    const studentSearch = document.getElementById('studentSearch');
    const studentResults = document.getElementById('studentResults');
    const searchLoading = document.getElementById('searchLoading');
    const studentIdInput = document.getElementById('student_id');
    const painLevel = document.getElementById('painLevel');
    const painValue = document.getElementById('painValue');
    const noPain = document.getElementById('noPain');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('consultationForm');

    // Check for existing student selection on page load
    const existingStudentId = studentIdInput.value;
    if (existingStudentId) {
        console.log('Found existing student ID from validation:', existingStudentId);
        setTimeout(() => {
            loadStudentMedicalData(existingStudentId);
        }, 500);
    }

    // Enhanced form state validation
    window.updateFormState = function() {
        const chiefComplaint = form.querySelector('textarea[name="chief_complaint"]').value.trim();
        const type = form.querySelector('select[name="type"]').value;
        const priority = form.querySelector('select[name="priority"]').value;
        const diagnosis = form.querySelector('textarea[name="diagnosis"]').value.trim();
        const treatment = form.querySelector('textarea[name="treatment_provided"]').value.trim();
        const hasStudent = studentIdInput.value !== '';

        const isValid = chiefComplaint && type && priority && diagnosis && treatment && hasStudent;
        submitBtn.disabled = !isValid;
        
        if (isValid) {
            submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
            submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
        } else {
            submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
            submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
        }
    };

    // Student search functionality
    studentSearch.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        if (searchTerm.length < 2) {
            studentResults.style.display = 'none';
            searchLoading.style.display = 'none';
            if (activeSearchController) {
                activeSearchController.abort();
                activeSearchController = null;
            }
            return;
        }

        searchTimeout = setTimeout(() => {
            performStudentSearch(searchTerm);
        }, 400);
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!studentSearch.contains(e.target) && !studentResults.contains(e.target)) {
            studentResults.style.display = 'none';
        }
    });

    // Pain level functionality
    painLevel.addEventListener('input', function() {
        painValue.textContent = this.value;
        noPain.checked = this.value == 0;
        updateFormState();
    });

    noPain.addEventListener('change', function() {
        if (this.checked) {
            painLevel.value = 0;
            painValue.textContent = 0;
        }
        updateFormState();
    });

    // Form validation
    form.addEventListener('change', updateFormState);
    form.addEventListener('input', updateFormState);

    // Form submission
    form.addEventListener('submit', function(e) {
        if (!studentIdInput.value) {
            e.preventDefault();
            showAlert('Please select a student before submitting the consultation.', 'error');
            return;
        }
        
        // Debug logging
        console.log('=== FORM SUBMISSION DEBUG ===');
        const formData = new FormData(form);
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processing...
        `;
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        cleanupResources('all');
    });

    // Initial form state update
    updateFormState();
});
</script>

<style>
.custom-alert {
    min-width: 300px;
    max-width: 500px;
}

/* Custom scrollbar styling */
.custom-scroll {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.custom-scroll::-webkit-scrollbar {
    width: 6px;
}

.custom-scroll::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 3px;
}

.custom-scroll::-webkit-scrollbar-thumb {
    background-color: #cbd5e0;
    border-radius: 3px;
}

.custom-scroll::-webkit-scrollbar-thumb:hover {
    background-color: #a0aec0;
}

/* Ensure proper scrolling behavior */
.h-\[calc\(100vh-180px\)\] {
    height: calc(100vh - 180px);
}

/* Sticky headers for better UX */
.sticky {
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Ensure proper flex behavior */
.flex-1 {
    flex: 1 1 0%;
}

.flex-shrink-0 {
    flex-shrink: 0;
}

.overflow-hidden {
    overflow: hidden;
}

.overflow-y-auto {
    overflow-y: auto;
}
</style>
@endsection