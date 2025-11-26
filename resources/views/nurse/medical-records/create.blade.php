{{-- resources/views/nurse/medical-records/create.blade.php - ULTRA-FAST VERSION --}}
@extends('layouts.nurse-app')

@section('title', 'Create Medical Record - Nurse Portal')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Create Medical Record
        </h1>
        <p class="text-gray-600">Create a comprehensive medical record for a student</p>
    </div>

    <!-- Empty State Message -->
    @if($users->isEmpty())
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6 text-center">
        <div class="flex flex-col items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-lg font-medium text-blue-800 mb-2">All Students Have Medical Records</h3>
            <p class="text-blue-600 mb-4">Great news! All students in the system already have medical records created.</p>
            <div class="flex gap-3 flex-wrap justify-center">
                <a href="{{ route('nurse.medical-records.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    View All Records
                </a>
                <a href="{{ route('nurse.students.search') }}" class="inline-flex items-center px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Search Students
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Form Container -->
    <form action="{{ route('nurse.medical-records.store') }}" method="POST" class="space-y-6" id="medical-form">
        @csrf
        
        <!-- Patient Selection Section -->
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="bg-blue-600 px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Select Patient
                </h3>
            </div>
            <div class="p-6">
                <div class="mb-4 student-search-container">
                    <label for="student_search" class="block text-sm font-medium text-gray-700 mb-2">
                        Search Student <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="text" 
                            id="student_search" 
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Type student name or ID to search..."
                            autocomplete="off"
                            aria-describedby="search-hint"
                            @if($users->isEmpty()) disabled @endif
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <button type="button" id="clear-search" class="text-gray-400 hover:text-gray-600 hidden" title="Clear search" aria-label="Clear search">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <p id="search-hint" class="mt-1 text-sm text-gray-600">
                        @if($users->isEmpty())
                            No students available for medical record creation
                        @else
                            Start typing to search for students by name or student ID
                        @endif
                    </p>

                    <!-- Search Results Dropdown -->
                    <div id="search-results" class="hidden mt-2 border border-gray-300 rounded-lg bg-white shadow-lg max-h-60 overflow-y-auto z-50">
                        <!-- Results will be populated here by JavaScript -->
                    </div>
                </div>

                <!-- Selected Student Display -->
                <div id="selected-student" class="hidden mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-medium text-green-800" id="selected-student-name"></h4>
                            <p class="text-sm text-green-600" id="selected-student-details"></p>
                        </div>
                        <button type="button" id="clear-selection" class="text-green-600 hover:text-green-800 transition-colors" aria-label="Clear selection">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Hidden input for form submission -->
                <input type="hidden" name="user_id" id="user_id" value="{{ old('user_id', $selectedUserId ?? '') }}" required>
                
                @error('user_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if(!$users->isEmpty())
        <!-- Basic Medical Information Section -->
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="bg-green-600 px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    Basic Medical Information
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="blood_type" class="block text-sm font-medium text-gray-700 mb-2">Blood Type</label>
                        <select name="blood_type" id="blood_type" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('blood_type') border-red-500 @enderror">
                            <option value="">Select blood type...</option>
                            <option value="A+" {{ old('blood_type') == 'A+' ? 'selected' : '' }}>A+</option>
                            <option value="A-" {{ old('blood_type') == 'A-' ? 'selected' : '' }}>A-</option>
                            <option value="B+" {{ old('blood_type') == 'B+' ? 'selected' : '' }}>B+</option>
                            <option value="B-" {{ old('blood_type') == 'B-' ? 'selected' : '' }}>B-</option>
                            <option value="AB+" {{ old('blood_type') == 'AB+' ? 'selected' : '' }}>AB+</option>
                            <option value="AB-" {{ old('blood_type') == 'AB-' ? 'selected' : '' }}>AB-</option>
                            <option value="O+" {{ old('blood_type') == 'O+' ? 'selected' : '' }}>O+</option>
                            <option value="O-" {{ old('blood_type') == 'O-' ? 'selected' : '' }}>O-</option>
                        </select>
                        @error('blood_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="height" class="block text-sm font-medium text-gray-700 mb-2">Height (cm)</label>
                        <input type="number" name="height" id="height" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('height') border-red-500 @enderror" 
                               value="{{ old('height') }}" min="50" max="300" step="0.1" placeholder="e.g., 165.5">
                        @error('height')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">Weight (kg)</label>
                        <input type="number" name="weight" id="weight" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('weight') border-red-500 @enderror" 
                               value="{{ old('weight') }}" min="20" max="500" step="0.1" placeholder="e.g., 65.5">
                        @error('weight')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label for="allergies" class="block text-sm font-medium text-gray-700 mb-2">Allergies</label>
                    <textarea name="allergies" id="allergies" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('allergies') border-red-500 @enderror" 
                              rows="3" placeholder="List any known allergies...">{{ old('allergies') }}</textarea>
                    @error('allergies')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Medical History Section -->
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="bg-purple-600 px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Medical History
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <!-- Pregnancy -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <input type="hidden" name="has_been_pregnant" value="0">
                                <input type="checkbox" name="has_been_pregnant" id="has_been_pregnant" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" value="1" {{ old('has_been_pregnant') ? 'checked' : '' }}>
                                <label class="ml-3 text-sm font-medium text-gray-700" for="has_been_pregnant">
                                    Has been pregnant
                                </label>
                            </div>
                        </div>

                        <!-- Surgery -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-3">
                                <input type="hidden" name="has_undergone_surgery" value="0">
                                <input type="checkbox" name="has_undergone_surgery" id="has_undergone_surgery" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded toggle-trigger" 
                                       value="1" {{ old('has_undergone_surgery') ? 'checked' : '' }} data-target="surgery-details">
                                <label class="ml-3 text-sm font-medium text-gray-700" for="has_undergone_surgery">
                                    Has undergone surgery
                                </label>
                            </div>
                            <div class="surgery-details {{ old('has_undergone_surgery') ? '' : 'hidden' }}">
                                <label for="surgery_details" class="block text-sm font-medium text-gray-700 mb-2">Surgery Details</label>
                                <textarea name="surgery_details" id="surgery_details" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('surgery_details') border-red-500 @enderror" 
                                          rows="3" placeholder="Describe the surgery...">{{ old('surgery_details') }}</textarea>
                                @error('surgery_details')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Maintenance Drugs -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-3">
                                <input type="hidden" name="is_taking_maintenance_drugs" value="0">
                                <input type="checkbox" name="is_taking_maintenance_drugs" id="is_taking_maintenance_drugs" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded toggle-trigger" 
                                       value="1" {{ old('is_taking_maintenance_drugs') ? 'checked' : '' }} data-target="maintenance-drugs">
                                <label class="ml-3 text-sm font-medium text-gray-700" for="is_taking_maintenance_drugs">
                                    Taking maintenance drugs
                                </label>
                            </div>
                            <div class="maintenance-drugs {{ old('is_taking_maintenance_drugs') ? '' : 'hidden' }}">
                                <label for="maintenance_drugs_specify" class="block text-sm font-medium text-gray-700 mb-2">Specify Maintenance Drugs</label>
                                <textarea name="maintenance_drugs_specify" id="maintenance_drugs_specify" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('maintenance_drugs_specify') border-red-500 @enderror" 
                                          rows="3" placeholder="List maintenance drugs...">{{ old('maintenance_drugs_specify') }}</textarea>
                                @error('maintenance_drugs_specify')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <!-- Hospitalization -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-3">
                                <input type="hidden" name="has_been_hospitalized_6_months" value="0">
                                <input type="checkbox" name="has_been_hospitalized_6_months" id="has_been_hospitalized_6_months" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded toggle-trigger" 
                                       value="1" {{ old('has_been_hospitalized_6_months') ? 'checked' : '' }} data-target="hospitalization-details">
                                <label class="ml-3 text-sm font-medium text-gray-700" for="has_been_hospitalized_6_months">
                                    Hospitalized in last 6 months
                                </label>
                            </div>
                            <div class="hospitalization-details {{ old('has_been_hospitalized_6_months') ? '' : 'hidden' }}">
                                <label for="hospitalization_details_6_months" class="block text-sm font-medium text-gray-700 mb-2">Hospitalization Details</label>
                                <textarea name="hospitalization_details_6_months" id="hospitalization_details_6_months" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('hospitalization_details_6_months') border-red-500 @enderror" 
                                          rows="3" placeholder="Describe hospitalization...">{{ old('hospitalization_details_6_months') }}</textarea>
                                @error('hospitalization_details_6_months')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- PWD -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-3">
                                <input type="hidden" name="is_pwd" value="0">
                                <input type="checkbox" name="is_pwd" id="is_pwd" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded toggle-trigger" 
                                       value="1" {{ old('is_pwd') ? 'checked' : '' }} data-target="pwd-details">
                                <label class="ml-3 text-sm font-medium text-gray-700" for="is_pwd">
                                    Person with Disability (PWD)
                                </label>
                            </div>
                            <div class="pwd-details {{ old('is_pwd') ? '' : 'hidden' }}">
                                <label for="pwd_disability_details" class="block text-sm font-medium text-gray-700 mb-2">Disability Details</label>
                                <textarea name="pwd_disability_details" id="pwd_disability_details" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('pwd_disability_details') border-red-500 @enderror" 
                                          rows="3" placeholder="Describe disability...">{{ old('pwd_disability_details') }}</textarea>
                                @error('pwd_disability_details')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Health Problems Notes -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <label for="notes_health_problems" class="block text-sm font-medium text-gray-700 mb-2">Health Problems Notes</label>
                    <textarea name="notes_health_problems" id="notes_health_problems" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('notes_health_problems') border-red-500 @enderror" 
                              rows="3" placeholder="Any additional health problems or notes...">{{ old('notes_health_problems') }}</textarea>
                    @error('notes_health_problems')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Vaccination Information Section -->
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="bg-indigo-600 px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                    </svg>
                    Vaccination Information
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Vaccination Status -->
                    <div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-4">
                                <input type="hidden" name="is_fully_vaccinated" value="0">
                                <input type="checkbox" name="is_fully_vaccinated" id="is_fully_vaccinated" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded toggle-trigger" 
                                       value="1" {{ old('is_fully_vaccinated') ? 'checked' : '' }} data-target="vaccination-details">
                                <label class="ml-3 text-sm font-medium text-gray-700" for="is_fully_vaccinated">
                                    Fully Vaccinated
                                </label>
                            </div>
                            
                            <div class="vaccination-details space-y-4 {{ old('is_fully_vaccinated') ? '' : 'hidden' }}">
                                <div>
                                    <label for="vaccine_type" class="block text-sm font-medium text-gray-700 mb-2">Vaccine Type</label>
                                    <select name="vaccine_type" id="vaccine_type" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('vaccine_type') border-red-500 @enderror">
                                        <option value="">Select vaccine type...</option>
                                        <option value="Pfizer-BioNTech" {{ old('vaccine_type') == 'Pfizer-BioNTech' ? 'selected' : '' }}>Pfizer-BioNTech</option>
                                        <option value="Moderna" {{ old('vaccine_type') == 'Moderna' ? 'selected' : '' }}>Moderna</option>
                                        <option value="Sinovac" {{ old('vaccine_type') == 'Sinovac' ? 'selected' : '' }}>Sinovac</option>
                                        <option value="AstraZeneca" {{ old('vaccine_type') == 'AstraZeneca' ? 'selected' : '' }}>AstraZeneca</option>
                                        <option value="Johnson & Johnson" {{ old('vaccine_type') == 'Johnson & Johnson' ? 'selected' : '' }}>Johnson & Johnson</option>
                                        <option value="Other" {{ old('vaccine_type') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('vaccine_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="other-vaccine {{ old('vaccine_type') == 'Other' ? '' : 'hidden' }}">
                                    <label for="other_vaccine_type" class="block text-sm font-medium text-gray-700 mb-2">Other Vaccine Type</label>
                                    <input type="text" name="other_vaccine_type" id="other_vaccine_type" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('other_vaccine_type') border-red-500 @enderror" 
                                           value="{{ old('other_vaccine_type') }}" placeholder="Specify other vaccine type">
                                    @error('other_vaccine_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="number_of_doses" class="block text-sm font-medium text-gray-700 mb-2">Number of Doses</label>
                                    <input type="number" name="number_of_doses" id="number_of_doses" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('number_of_doses') border-red-500 @enderror" 
                                           value="{{ old('number_of_doses') }}" min="0" max="10" placeholder="e.g., 2">
                                    @error('number_of_doses')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booster Information -->
                    <div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-4">
                                <input type="hidden" name="has_received_booster" value="0">
                                <input type="checkbox" name="has_received_booster" id="has_received_booster" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded toggle-trigger" 
                                       value="1" {{ old('has_received_booster') ? 'checked' : '' }} data-target="booster-details">
                                <label class="ml-3 text-sm font-medium text-gray-700" for="has_received_booster">
                                    Received Booster
                                </label>
                            </div>

                            <div class="booster-details space-y-4 {{ old('has_received_booster') ? '' : 'hidden' }}">
                                <div>
                                    <label for="booster_type" class="block text-sm font-medium text-gray-700 mb-2">Booster Type</label>
                                    <select name="booster_type" id="booster_type" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('booster_type') border-red-500 @enderror">
                                        <option value="">Select booster type...</option>
                                        <option value="Pfizer-BioNTech" {{ old('booster_type') == 'Pfizer-BioNTech' ? 'selected' : '' }}>Pfizer-BioNTech</option>
                                        <option value="Moderna" {{ old('booster_type') == 'Moderna' ? 'selected' : '' }}>Moderna</option>
                                        <option value="Sinovac" {{ old('booster_type') == 'Sinovac' ? 'selected' : '' }}>Sinovac</option>
                                        <option value="AstraZeneca" {{ old('booster_type') == 'AstraZeneca' ? 'selected' : '' }}>AstraZeneca</option>
                                        <option value="Johnson & Johnson" {{ old('booster_type') == 'Johnson & Johnson' ? 'selected' : '' }}>Johnson & Johnson</option>
                                        <option value="Other" {{ old('booster_type') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('booster_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="other-booster {{ old('booster_type') == 'Other' ? '' : 'hidden' }}">
                                    <label for="other_booster_type" class="block text-sm font-medium text-gray-700 mb-2">Other Booster Type</label>
                                    <input type="text" name="other_booster_type" id="other_booster_type" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('other_booster_type') border-red-500 @enderror" 
                                           value="{{ old('other_booster_type') }}" placeholder="Specify other booster type">
                                    @error('other_booster_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="number_of_boosters" class="block text-sm font-medium text-gray-700 mb-2">Number of Boosters</label>
                                    <input type="number" name="number_of_boosters" id="number_of_boosters" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('number_of_boosters') border-red-500 @enderror" 
                                           value="{{ old('number_of_boosters') }}" min="0" max="10" placeholder="e.g., 1">
                                    @error('number_of_boosters')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency Contacts Section -->
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="bg-red-600 px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    Emergency Contacts
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Primary Contact -->
                    <div class="bg-red-50 p-4 rounded-lg">
                        <h6 class="text-sm font-medium text-red-800 mb-4">Primary Contact <span class="text-red-500">*</span></h6>
                        <div class="space-y-4">
                            <div>
                                <label for="emergency_contact_name_1" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                                <input type="text" name="emergency_contact_name_1" id="emergency_contact_name_1" 
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('emergency_contact_name_1') border-red-500 @enderror" 
                                       value="{{ old('emergency_contact_name_1') }}" required>
                                @error('emergency_contact_name_1')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="emergency_contact_number_1" class="block text-sm font-medium text-gray-700 mb-2">Phone Number <span class="text-red-500">*</span></label>
                                <input type="tel" name="emergency_contact_number_1" id="emergency_contact_number_1" 
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('emergency_contact_number_1') border-red-500 @enderror" 
                                       value="{{ old('emergency_contact_number_1') }}" required pattern="^\+?[\d\s-]{10,}$" title="Enter a valid phone number">
                                @error('emergency_contact_number_1')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="emergency_contact_relationship_1" class="block text-sm font-medium text-gray-700 mb-2">Relationship <span class="text-red-500">*</span></label>
                                <select name="emergency_contact_relationship_1" id="emergency_contact_relationship_1" 
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('emergency_contact_relationship_1') border-red-500 @enderror" required>
                                    <option value="">Select relationship...</option>
                                    <option value="Parent" {{ old('emergency_contact_relationship_1') == 'Parent' ? 'selected' : '' }}>Parent</option>
                                    <option value="Mother" {{ old('emergency_contact_relationship_1') == 'Mother' ? 'selected' : '' }}>Mother</option>
                                    <option value="Father" {{ old('emergency_contact_relationship_1') == 'Father' ? 'selected' : '' }}>Father</option>
                                    <option value="Guardian" {{ old('emergency_contact_relationship_1') == 'Guardian' ? 'selected' : '' }}>Guardian</option>
                                    <option value="Spouse" {{ old('emergency_contact_relationship_1') == 'Spouse' ? 'selected' : '' }}>Spouse</option>
                                    <option value="Sibling" {{ old('emergency_contact_relationship_1') == 'Sibling' ? 'selected' : '' }}>Sibling</option>
                                    <option value="Sister" {{ old('emergency_contact_relationship_1') == 'Sister' ? 'selected' : '' }}>Sister</option>
                                    <option value="Brother" {{ old('emergency_contact_relationship_1') == 'Brother' ? 'selected' : '' }}>Brother</option>
                                    <option value="Aunt" {{ old('emergency_contact_relationship_1') == 'Aunt' ? 'selected' : '' }}>Aunt</option>
                                    <option value="Uncle" {{ old('emergency_contact_relationship_1') == 'Uncle' ? 'selected' : '' }}>Uncle</option>
                                    <option value="Grandparent" {{ old('emergency_contact_relationship_1') == 'Grandparent' ? 'selected' : '' }}>Grandparent</option>
                                    <option value="Friend" {{ old('emergency_contact_relationship_1') == 'Friend' ? 'selected' : '' }}>Friend</option>
                                    <option value="Other" {{ old('emergency_contact_relationship_1') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('emergency_contact_relationship_1')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Secondary Contact -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h6 class="text-sm font-medium text-gray-600 mb-4">Secondary Contact (Optional)</h6>
                        <div class="space-y-4">
                            <div>
                                <label for="emergency_contact_name_2" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                                <input type="text" name="emergency_contact_name_2" id="emergency_contact_name_2" 
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('emergency_contact_name_2') border-red-500 @enderror" 
                                       value="{{ old('emergency_contact_name_2') }}">
                                @error('emergency_contact_name_2')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="emergency_contact_number_2" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" name="emergency_contact_number_2" id="emergency_contact_number_2" 
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('emergency_contact_number_2') border-red-500 @enderror" 
                                       value="{{ old('emergency_contact_number_2') }}" pattern="^\+?[\d\s-]{10,}$" title="Enter a valid phone number">
                                @error('emergency_contact_number_2')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="emergency_contact_relationship_2" class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                                <select name="emergency_contact_relationship_2" id="emergency_contact_relationship_2" 
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('emergency_contact_relationship_2') border-red-500 @enderror">
                                    <option value="">Select relationship...</option>
                                    <option value="Parent" {{ old('emergency_contact_relationship_2') == 'Parent' ? 'selected' : '' }}>Parent</option>
                                    <option value="Mother" {{ old('emergency_contact_relationship_2') == 'Mother' ? 'selected' : '' }}>Mother</option>
                                    <option value="Father" {{ old('emergency_contact_relationship_2') == 'Father' ? 'selected' : '' }}>Father</option>
                                    <option value="Guardian" {{ old('emergency_contact_relationship_2') == 'Guardian' ? 'selected' : '' }}>Guardian</option>
                                    <option value="Spouse" {{ old('emergency_contact_relationship_2') == 'Spouse' ? 'selected' : '' }}>Spouse</option>
                                    <option value="Sibling" {{ old('emergency_contact_relationship_2') == 'Sibling' ? 'selected' : '' }}>Sibling</option>
                                    <option value="Sister" {{ old('emergency_contact_relationship_2') == 'Sister' ? 'selected' : '' }}>Sister</option>
                                    <option value="Brother" {{ old('emergency_contact_relationship_2') == 'Brother' ? 'selected' : '' }}>Brother</option>
                                    <option value="Aunt" {{ old('emergency_contact_relationship_2') == 'Aunt' ? 'selected' : '' }}>Aunt</option>
                                    <option value="Uncle" {{ old('emergency_contact_relationship_2') == 'Uncle' ? 'selected' : '' }}>Uncle</option>
                                    <option value="Grandparent" {{ old('emergency_contact_relationship_2') == 'Grandparent' ? 'selected' : '' }}>Grandparent</option>
                                    <option value="Friend" {{ old('emergency_contact_relationship_2') == 'Friend' ? 'selected' : '' }}>Friend</option>
                                    <option value="Other" {{ old('emergency_contact_relationship_2') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('emergency_contact_relationship_2')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row gap-4 justify-end">
                <a href="{{ route('nurse.medical-records.index') }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 submit-btn transition-colors" @if($users->isEmpty()) disabled @endif>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="btn-text">Create Medical Record</span>
                    <span class="btn-loading hidden">Creating...</span>
                </button>
            </div>
            <p id="form-error" class="mt-4 text-sm text-red-600 hidden">Please fill all required fields before submitting.</p>
            <p id="form-success" class="mt-4 text-sm text-green-600 hidden">Medical record created successfully!</p>
            
            @if($users->isEmpty())
            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    Form disabled - No students available for medical record creation
                </p>
            </div>
            @endif
        </div>
        @endif
    </form>
</div>

<script>
// ULTRA-FAST JavaScript - Optimized for performance
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('medical-form');
    const submitBtn = form.querySelector('.submit-btn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    const studentSearch = document.getElementById('student_search');
    const searchResults = document.getElementById('search-results');
    const selectedStudent = document.getElementById('selected-student');
    const selectedStudentName = document.getElementById('selected-student-name');
    const selectedStudentDetails = document.getElementById('selected-student-details');
    const userIdInput = document.getElementById('user_id');
    const clearSearch = document.getElementById('clear-search');
    const clearSelection = document.getElementById('clear-selection');
    const formError = document.getElementById('form-error');
    const formSuccess = document.getElementById('form-success');

    let students = []; // This will store all students from the server

    // Debounce function for performance
    const debounce = (func, wait) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    };

    // Load students data from the original select options
    function loadStudents() {
        // Extract students from the original users data passed from controller
        students = [
            @foreach($users as $user)
            {
                id: "{{ $user->id }}",
                name: "{{ $user->full_name }}",
                studentId: "{{ $user->student_id }}",
                course: "{{ $user->course }}",
                searchText: "{{ strtolower($user->full_name . ' ' . $user->student_id) }}"
            },
            @endforeach
        ].filter(student => student.id); // Remove empty options
    }

    // Filter students based on search term
    function filterStudents(searchTerm) {
        if (!searchTerm) {
            hideResults();
            return;
        }

        const terms = searchTerm.toLowerCase().trim().split(/\s+/);
        const filtered = students.filter(student => 
            terms.every(term => 
                student.searchText.includes(term) ||
                student.name.toLowerCase().includes(term) ||
                student.studentId.toLowerCase().includes(term)
            )
        );

        displayResults(filtered);
    }

    // Display search results
    function displayResults(results) {
        if (results.length === 0) {
            searchResults.innerHTML = `
                <div class="p-6 text-center text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.47-.88-6.08-2.32M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="font-medium text-gray-600">No students found</p>
                    <p class="text-sm">Try a different search term</p>
                </div>
            `;
            searchResults.classList.remove('hidden');
            return;
        }

        const resultsHTML = results.map(student => `
            <div class="student-result" 
                 data-id="${student.id}" 
                 data-name="${student.name}" 
                 data-student-id="${student.studentId}" 
                 data-course="${student.course}">
                <div class="font-medium text-gray-900">${student.name}</div>
                <div class="text-sm text-gray-600 flex justify-between items-center">
                    <span>${student.studentId}</span>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">${student.course || 'No Course'}</span>
                </div>
            </div>
        `).join('');

        searchResults.innerHTML = resultsHTML;
        searchResults.classList.remove('hidden');

        // Add click event listeners to results
        document.querySelectorAll('.student-result').forEach(result => {
            result.addEventListener('click', () => selectStudent(result.dataset));
        });
    }

    // Hide search results
    function hideResults() {
        searchResults.classList.add('hidden');
    }

    // Select a student
    function selectStudent(student) {
        userIdInput.value = student.id;
        selectedStudentName.textContent = student.name;
        selectedStudentDetails.textContent = `${student.studentId} • ${student.course || 'No Course'}`;
        selectedStudent.classList.remove('hidden');
        
        studentSearch.value = '';
        hideResults();
        clearSearch.classList.add('hidden');
        
        // Update form validation
        studentSearch.setCustomValidity('');
        
        // Remove any existing error messages
        const errorElement = studentSearch.parentNode.querySelector('.text-red-600');
        if (errorElement) {
            errorElement.remove();
        }
    }

    // Clear selection
    function clearStudentSelection() {
        userIdInput.value = '';
        selectedStudent.classList.add('hidden');
        studentSearch.focus();
    }

    // Clear search
    function clearSearchInput() {
        studentSearch.value = '';
        studentSearch.focus();
        hideResults();
        clearSearch.classList.add('hidden');
    }

    // Toggle visibility of conditional fields
    const toggleFields = (target, isChecked, targetClass) => {
        const targetElement = form.querySelector('.' + targetClass);
        if (targetElement) {
            targetElement.classList.toggle('hidden', !isChecked);
            if (!isChecked) {
                targetElement.querySelectorAll('input, select, textarea').forEach(input => {
                    if (input.type !== 'checkbox') input.value = '';
                });
            }
        }
    };

    // Event Listeners for Student Search
    studentSearch.addEventListener('input', debounce((e) => {
        const searchTerm = e.target.value.trim();
        if (searchTerm) {
            filterStudents(searchTerm);
            clearSearch.classList.remove('hidden');
        } else {
            hideResults();
            clearSearch.classList.add('hidden');
        }
    }, 300));

    studentSearch.addEventListener('focus', () => {
        if (studentSearch.value.trim()) {
            filterStudents(studentSearch.value.trim());
        }
    });

    // Hide results when clicking outside
    document.addEventListener('click', (e) => {
        if (!studentSearch.contains(e.target) && !searchResults.contains(e.target)) {
            hideResults();
        }
    });

    // Keyboard navigation
    studentSearch.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            hideResults();
            studentSearch.blur();
        }
        
        if (e.key === 'ArrowDown' && !searchResults.classList.contains('hidden')) {
            const firstResult = searchResults.querySelector('.student-result');
            if (firstResult) firstResult.focus();
        }
        
        // Navigate through results with arrow keys
        if ((e.key === 'ArrowDown' || e.key === 'ArrowUp') && !searchResults.classList.contains('hidden')) {
            e.preventDefault();
            const results = Array.from(searchResults.querySelectorAll('.student-result'));
            const currentFocus = document.activeElement;
            let currentIndex = results.indexOf(currentFocus);
            
            if (e.key === 'ArrowDown') {
                currentIndex = currentIndex < results.length - 1 ? currentIndex + 1 : 0;
            } else {
                currentIndex = currentIndex > 0 ? currentIndex - 1 : results.length - 1;
            }
            
            if (results[currentIndex]) {
                results[currentIndex].focus();
            }
        }
        
        // Select with Enter key
        if (e.key === 'Enter' && document.activeElement.classList.contains('student-result')) {
            e.preventDefault();
            selectStudent(document.activeElement.dataset);
        }
    });

    clearSearch.addEventListener('click', clearSearchInput);
    clearSelection.addEventListener('click', clearStudentSelection);

    // Handle form changes with debouncing
    form.addEventListener('change', debounce(e => {
        const target = e.target;

        // Toggle fields for checkboxes
        if (target.classList.contains('toggle-trigger')) {
            toggleFields(target, target.checked, target.dataset.target);
        }

        // Handle vaccine type
        if (target.id === 'vaccine_type') {
            toggleFields(target, target.value === 'Other', 'other-vaccine');
        }

        // Handle booster type
        if (target.id === 'booster_type') {
            toggleFields(target, target.value === 'Other', 'other-booster');
        }
    }, 100));

    // Client-side form validation
    form.addEventListener('submit', e => {
        let valid = true;
        formError.classList.add('hidden');
        formSuccess.classList.add('hidden');

        // Validate student selection
        if (!userIdInput.value) {
            valid = false;
            studentSearch.classList.add('border-red-500');
            const existingError = studentSearch.parentNode.querySelector('.text-red-600');
            if (!existingError) {
                const errorP = document.createElement('p');
                errorP.className = 'mt-1 text-sm text-red-600';
                errorP.textContent = 'Please select a student';
                studentSearch.parentNode.appendChild(errorP);
            }
        } else {
            studentSearch.classList.remove('border-red-500');
            const error = studentSearch.parentNode.querySelector('.text-red-600');
            if (error) error.remove();
        }

        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (field.id !== 'student_search' && !field.value.trim()) {
                valid = false;
                field.classList.add('border-red-500');
                const error = field.nextElementSibling;
                if (error && error.tagName === 'P') {
                    error.textContent = 'This field is required';
                } else {
                    const errorP = document.createElement('p');
                    errorP.className = 'mt-1 text-sm text-red-600';
                    errorP.textContent = 'This field is required';
                    field.parentNode.insertBefore(errorP, field.nextSibling);
                }
            } else if (field.id !== 'student_search') {
                field.classList.remove('border-red-500');
                const error = field.nextElementSibling;
                if (error && error.tagName === 'P' && error.textContent === 'This field is required') error.remove();
            }
        });

        // Validate phone numbers
        const phoneInputs = form.querySelectorAll('input[type="tel"]');
        phoneInputs.forEach(input => {
            if (input.value && !/^\+?[\d\s-]{10,}$/.test(input.value)) {
                valid = false;
                input.classList.add('border-red-500');
                const error = input.nextElementSibling;
                if (error && error.tagName === 'P') {
                    error.textContent = 'Please enter a valid phone number';
                } else {
                    const errorP = document.createElement('p');
                    errorP.className = 'mt-1 text-sm text-red-600';
                    errorP.textContent = 'Please enter a valid phone number';
                    input.parentNode.insertBefore(errorP, input.nextSibling);
                }
            } else {
                input.classList.remove('border-red-500');
                const error = input.nextElementSibling;
                if (error && error.tagName === 'P' && error.textContent === 'Please enter a valid phone number') error.remove();
            }
        });

        if (!valid) {
            e.preventDefault();
            formError.classList.remove('hidden');
            return;
        }

        // Show loading state
        submitBtn.disabled = true;
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');

        // Simulate success (replace with actual AJAX if needed)
        setTimeout(() => {
            submitBtn.disabled = false;
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
            formSuccess.classList.remove('hidden');
        }, 1000); // Simulated delay
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', e => {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            form.submit();
        }
        if (e.key === 'Escape' && studentSearch.value) {
            clearSearchInput();
        }
    });

    // Initialize
    loadStudents();

    // If there's a pre-selected user, show it
    const initialUserId = userIdInput.value;
    if (initialUserId) {
        const initialStudent = students.find(s => s.id === initialUserId);
        if (initialStudent) {
            selectStudent(initialStudent);
        }
    }
});
</script>

<style>
/* ULTRA-FAST CSS - Optimized for performance and accessibility */

/* Base styles */
* {
    transition: none !important;
    animation: none !important;
}

/* Search container positioning */
.student-search-container {
    position: relative;
}

/* Search results styling - FIXED POSITIONING */
#search-results {
    position: absolute;
    width: 100%;
    left: 0;
    top: 100%;
    margin-top: 4px;
    z-index: 1000;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    max-height: 300px;
    overflow-y: auto;
}

.student-result {
    transition: background-color 0.15s ease;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    padding: 12px 16px;
}

.student-result:last-child {
    border-bottom: none;
}

.student-result:hover {
    background-color: #eff6ff;
}

.student-result:active {
    background-color: #dbeafe;
}

.student-result:focus {
    outline: 2px solid #3b82f6;
    outline-offset: -2px;
}

/* Selected student display */
#selected-student {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Clear button animation */
#clear-search, #clear-selection {
    transition: color 0.15s ease, transform 0.15s ease;
}

#clear-search:hover, #clear-selection:hover {
    transform: scale(1.1);
}

/* Focus states */
#student_search:focus {
    outline: none;
    ring: 2px;
    ring-color: #3b82f6;
    border-color: #3b82f6;
}

/* Responsive grid */
@media (max-width: 768px) {
    .grid-cols-1\.md\:grid-cols-2,
    .grid-cols-1\.md\:grid-cols-3 {
        grid-template-columns: 1fr;
    }
    
    .flex-col\.sm\:flex-row {
        flex-direction: column;
    }
    
    .gap-6 { gap: 1rem; }
    .gap-8 { gap: 1.5rem; }
    
    #search-results {
        width: 100%;
        position: fixed;
        left: 50%;
        transform: translateX(-50%);
        width: calc(100vw - 2rem);
        max-width: 400px;
    }
}

/* Focus states for accessibility */
:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

:focus:not(:focus-visible) {
    outline: none;
}

/* Hide elements */
.hidden { display: none !important; }

/* Hover states */
.hover\:bg-blue-700:hover { background-color: #1d4ed8; }
.hover\:bg-green-700:hover { background-color: #15803d; }
.hover\:bg-gray-50:hover { background-color: #f9fafb; }
.hover\:bg-gray-300:hover { background-color: #d1d5db; }

/* Border styles */
.border { border-width: 1px; }
.border-gray-200 { border-color: #e5e7eb; }
.border-gray-300 { border-color: #d1d5db; }
.border-red-500 { border-color: #ef4444; }

/* Focus rings */
.focus\:ring-2:focus { box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5); }
.focus\:ring-blue-500:focus { box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5); }
.focus\:border-blue-500:focus { border-color: #3b82f6; }

/* Optimize rendering */
.bg-white {
    will-change: auto;
    contain: layout style;
}

/* Print optimization */
@media print {
    .no-print, button, .bg-blue-600, .bg-green-600, .bg-red-600, .bg-purple-600, .bg-indigo-600 {
        display: none !important;
    }
    
    .border { border: 1px solid #000 !important; }
    
    .bg-white { background: #fff !important; }
}

/* Accessibility - reduced motion */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
}

/* High contrast support */
@media (prefers-contrast: high) {
    .text-gray-600 { color: #000; }
    .text-gray-500 { color: #333; }
    .bg-gray-50 { background-color: #f0f0f0; border: 1px solid #000; }
    .bg-blue-100 { background-color: #bfdbfe; }
}

/* Form styling */
input, select, textarea {
    appearance: none;
    background-color: #fff;
    font-size: 1rem;
}

input:focus, select:focus, textarea:focus {
    outline: none;
}

/* Button styling */
button, .inline-flex {
    cursor: pointer;
    user-select: none;
}

button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

/* Container optimization */
.container {
    max-width: 100%;
}

@media (min-width: 640px) { .container { max-width: 640px; } }
@media (min-width: 768px) { .container { max-width: 768px; } }
@media (min-width: 1024px) { .container { max-width: 1024px; } }
@media (min-width: 1280px) { .container { max-width: 1280px; } }
@media (min-width: 1536px) { .container { max-width: 1536px; } }

/* Custom scrollbar for search results */
#search-results::-webkit-scrollbar {
    width: 6px;
}

#search-results::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

#search-results::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

#search-results::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Transition utilities */
.transition-colors {
    transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}
</style>
@endsection