{{-- resources/views/nurse/medical-records/edit.blade.php --}}
@extends('layouts.nurse-app')

@section('title', 'Edit Medical Record - ' . $medicalRecord->user->full_name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center">
            <div class="p-2 bg-orange-100 rounded-lg mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Edit Medical Record</h1>
                <p class="text-gray-600 mt-1">Update medical record for {{ $medicalRecord->user->full_name }}</p>
            </div>
        </div>
    </div>

    <!-- Patient Information Display -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
        <h3 class="text-xl font-semibold text-blue-800 mb-4">Patient Information</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm text-gray-700">
            <div>
                <span class="font-medium text-blue-700">Name:</span> {{ $medicalRecord->user->full_name }}
            </div>
            <div>
                <span class="font-medium text-blue-700">Student ID:</span> {{ $medicalRecord->user->student_id }}
            </div>
            <div>
                <span class="font-medium text-blue-700">Course:</span> {{ $medicalRecord->user->course ?? 'Not specified' }}
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <form action="{{ route('nurse.medical-records.update', $medicalRecord) }}" method="POST" class="space-y-6" id="medical-form">
        @csrf
        @method('PUT')

        <!-- Basic Medical Information Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-blue-600 px-6 py-4">
                <h3 class="text-xl font-semibold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    Basic Medical Information
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="blood_type" class="block text-sm font-medium text-gray-700 mb-2">Blood Type</label>
                        <select name="blood_type" id="blood_type" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('blood_type') border-red-500 @enderror" aria-label="Select blood type">
                            <option value="">Select blood type...</option>
                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type)
                                <option value="{{ $type }}" {{ old('blood_type', $medicalRecord->blood_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('blood_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="height" class="block text-sm font-medium text-gray-700 mb-2">Height (cm)</label>
                        <input type="number" name="height" id="height" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('height') border-red-500 @enderror" 
                               value="{{ old('height', $medicalRecord->height) }}" min="50" max="300" step="0.1" placeholder="e.g., 165.5" aria-label="Enter height in centimeters">
                        @error('height')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">Weight (kg)</label>
                        <input type="number" name="weight" id="weight" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('weight') border-red-500 @enderror" 
                               value="{{ old('weight', $medicalRecord->weight) }}" min="20" max="500" step="0.1" placeholder="e.g., 65.5" aria-label="Enter weight in kilograms">
                        @error('weight')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label for="allergies" class="block text-sm font-medium text-gray-700 mb-2">Allergies</label>
                    <textarea name="allergies" id="allergies" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('allergies') border-red-500 @enderror" 
                              rows="4" placeholder="List any known allergies..." aria-label="Enter known allergies">{{ old('allergies', $medicalRecord->allergies) }}</textarea>
                    @error('allergies')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Medical History Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-purple-600 px-6 py-4">
                <h3 class="text-xl font-semibold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Medical History
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <!-- Pregnancy -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <input type="hidden" name="has_been_pregnant" value="0">
                                <input type="checkbox" name="has_been_pregnant" id="has_been_pregnant" 
                                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                       value="1" {{ old('has_been_pregnant', $medicalRecord->has_been_pregnant) ? 'checked' : '' }} aria-label="Has been pregnant">
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
                                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded toggle-trigger" 
                                       value="1" {{ old('has_undergone_surgery', $medicalRecord->has_undergone_surgery) ? 'checked' : '' }} data-target="surgery-details" aria-label="Has undergone surgery">
                                <label class="ml-3 text-sm font-medium text-gray-700" for="has_undergone_surgery">
                                    Has undergone surgery
                                </label>
                            </div>
                            <div class="surgery-details transition-opacity duration-300 {{ old('has_undergone_surgery', $medicalRecord->has_undergone_surgery) ? 'opacity-100' : 'opacity-0 hidden' }}">
                                <label for="surgery_details" class="block text-sm font-medium text-gray-700 mb-2">Surgery Details</label>
                                <textarea name="surgery_details" id="surgery_details" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('surgery_details') border-red-500 @enderror" 
                                          rows="4" placeholder="Describe the surgery..." aria-label="Enter surgery details">{{ old('surgery_details', $medicalRecord->surgery_details) }}</textarea>
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
                                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded toggle-trigger" 
                                       value="1" {{ old('is_taking_maintenance_drugs', $medicalRecord->is_taking_maintenance_drugs) ? 'checked' : '' }} data-target="maintenance-drugs" aria-label="Taking maintenance drugs">
                                <label class="ml-3 text-sm font-medium text-gray-700" for="is_taking_maintenance_drugs">
                                    Taking maintenance drugs
                                </label>
                            </div>
                            <div class="maintenance-drugs transition-opacity duration-300 {{ old('is_taking_maintenance_drugs', $medicalRecord->is_taking_maintenance_drugs) ? 'opacity-100' : 'opacity-0 hidden' }}">
                                <label for="maintenance_drugs_specify" class="block text-sm font-medium text-gray-700 mb-2">Specify Maintenance Drugs</label>
                                <textarea name="maintenance_drugs_specify" id="maintenance_drugs_specify" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('maintenance_drugs_specify') border-red-500 @enderror" 
                                          rows="4" placeholder="List maintenance drugs..." aria-label="Enter maintenance drugs details">{{ old('maintenance_drugs_specify', $medicalRecord->maintenance_drugs_specify) }}</textarea>
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
                                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded toggle-trigger" 
                                       value="1" {{ old('has_been_hospitalized_6_months', $medicalRecord->has_been_hospitalized_6_months) ? 'checked' : '' }} data-target="hospitalization-details" aria-label="Hospitalized in last 6 months">
                                <label class="ml-3 text-sm font-medium text-gray-700" for="has_been_hospitalized_6_months">
                                    Hospitalized in last 6 months
                                </label>
                            </div>
                            <div class="hospitalization-details transition-opacity duration-300 {{ old('has_been_hospitalized_6_months', $medicalRecord->has_been_hospitalized_6_months) ? 'opacity-100' : 'opacity-0 hidden' }}">
                                <label for="hospitalization_details_6_months" class="block text-sm font-medium text-gray-700 mb-2">Hospitalization Details</label>
                                <textarea name="hospitalization_details_6_months" id="hospitalization_details_6_months" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('hospitalization_details_6_months') border-red-500 @enderror" 
                                          rows="4" placeholder="Describe hospitalization..." aria-label="Enter hospitalization details">{{ old('hospitalization_details_6_months', $medicalRecord->hospitalization_details_6_months) }}</textarea>
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
                                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded toggle-trigger" 
                                       value="1" {{ old('is_pwd', $medicalRecord->is_pwd) ? 'checked' : '' }} data-target="pwd-details" aria-label="Person with Disability (PWD)">
                                <label class="ml-3 text-sm font-medium text-gray-700" for="is_pwd">
                                    Person with Disability (PWD)
                                </label>
                            </div>
                            <div class="pwd-details transition-opacity duration-300 {{ old('is_pwd', $medicalRecord->is_pwd) ? 'opacity-100' : 'opacity-0 hidden' }}">
                                <label for="pwd_disability_details" class="block text-sm font-medium text-gray-700 mb-2">Disability Details</label>
                                <textarea name="pwd_disability_details" id="pwd_disability_details" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('pwd_disability_details') border-red-500 @enderror" 
                                          rows="4" placeholder="Describe disability..." aria-label="Enter disability details">{{ old('pwd_disability_details', $medicalRecord->pwd_disability_details) }}</textarea>
                                @error('pwd_disability_details')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Past Illnesses -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <label for="past_illnesses" class="block text-sm font-medium text-gray-700 mb-2">Past Illnesses</label>
                    <textarea name="past_illnesses" id="past_illnesses" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('past_illnesses') border-red-500 @enderror" 
                              rows="4" placeholder="List any past illnesses..." aria-label="Enter past illnesses">{{ old('past_illnesses', $medicalRecord->past_illnesses) }}</textarea>
                    @error('past_illnesses')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Health Problems Notes -->
                <div class="mt-6">
                    <label for="notes_health_problems" class="block text-sm font-medium text-gray-700 mb-2">Health Problems Notes</label>
                    <textarea name="notes_health_problems" id="notes_health_problems" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('notes_health_problems') border-red-500 @enderror" 
                              rows="4" placeholder="Any additional health problems or notes..." aria-label="Enter additional health notes">{{ old('notes_health_problems', $medicalRecord->notes_health_problems) }}</textarea>
                    @error('notes_health_problems')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Vaccination Information Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-indigo-600 px-6 py-4">
                <h3 class="text-xl font-semibold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
                    </svg>
                    Vaccination Information
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Primary Vaccination -->
                    <div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-4">
                                <input type="hidden" name="is_fully_vaccinated" value="0">
                                <input type="checkbox" name="is_fully_vaccinated" id="is_fully_vaccinated" 
                                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded toggle-trigger" 
                                       value="1" {{ old('is_fully_vaccinated', $medicalRecord->is_fully_vaccinated) ? 'checked' : '' }} data-target="vaccination-details" aria-label="Fully vaccinated">
                                <label class="ml-3 text-sm font-medium text-gray-700" for="is_fully_vaccinated">
                                    Fully Vaccinated
                                </label>
                            </div>
                            <div class="vaccination-details space-y-4 transition-opacity duration-300 {{ old('is_fully_vaccinated', $medicalRecord->is_fully_vaccinated) ? 'opacity-100' : 'opacity-0 hidden' }}">
                                <div>
                                    <label for="vaccine_name" class="block text-sm font-medium text-gray-700 mb-2">Vaccine Type</label>
                                    <select name="vaccine_name" id="vaccine_name" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('vaccine_name') border-red-500 @enderror" aria-label="Select vaccine type">
                                        <option value="">Select vaccine type...</option>
                                        <option value="Pfizer-BioNTech" {{ old('vaccine_name', $medicalRecord->vaccine_name) == 'Pfizer-BioNTech' ? 'selected' : '' }}>Pfizer-BioNTech</option>
                                        <option value="Moderna" {{ old('vaccine_name', $medicalRecord->vaccine_name) == 'Moderna' ? 'selected' : '' }}>Moderna</option>
                                        <option value="Sinovac" {{ old('vaccine_name', $medicalRecord->vaccine_name) == 'Sinovac' ? 'selected' : '' }}>Sinovac</option>
                                        <option value="Sinopharm" {{ old('vaccine_name', $medicalRecord->vaccine_name) == 'Sinopharm' ? 'selected' : '' }}>Sinopharm</option>
                                        <option value="AstraZeneca" {{ old('vaccine_name', $medicalRecord->vaccine_name) == 'AstraZeneca' ? 'selected' : '' }}>AstraZeneca</option>
                                        <option value="Johnson & Johnson" {{ old('vaccine_name', $medicalRecord->vaccine_name) == 'Johnson & Johnson' ? 'selected' : '' }}>Johnson & Johnson</option>
                                        <option value="COVAXIN" {{ old('vaccine_name', $medicalRecord->vaccine_name) == 'COVAXIN' ? 'selected' : '' }}>COVAXIN</option>
                                        <option value="Other" {{ old('vaccine_name', $medicalRecord->vaccine_name) == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('vaccine_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="other-vaccine transition-opacity duration-300 {{ old('vaccine_name', $medicalRecord->vaccine_name) == 'Other' ? 'opacity-100' : 'opacity-0 hidden' }}">
                                    <label for="other_vaccine_type" class="block text-sm font-medium text-gray-700 mb-2">Other Vaccine Type</label>
                                    <input type="text" name="other_vaccine_type" id="other_vaccine_type" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('other_vaccine_type') border-red-500 @enderror" 
                                           value="{{ old('other_vaccine_type', $medicalRecord->other_vaccine_type ?? '') }}" placeholder="Specify other vaccine type" aria-label="Enter other vaccine type">
                                    @error('other_vaccine_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="vaccine_date" class="block text-sm font-medium text-gray-700 mb-2">Vaccination Date</label>
                                    <input type="date" name="vaccine_date" id="vaccine_date" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('vaccine_date') border-red-500 @enderror" 
                                           value="{{ old('vaccine_date', $medicalRecord->vaccine_date ? $medicalRecord->vaccine_date->format('Y-m-d') : '') }}" max="{{ date('Y-m-d') }}" aria-label="Enter vaccination date">
                                    @error('vaccine_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="number_of_doses" class="block text-sm font-medium text-gray-700 mb-2">Number of Doses</label>
                                    <select name="number_of_doses" id="number_of_doses" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('number_of_doses') border-red-500 @enderror" aria-label="Select number of doses">
                                        <option value="">Select number of doses...</option>
                                        <option value="1 dose" {{ old('number_of_doses', $medicalRecord->number_of_doses) == '1 dose' ? 'selected' : '' }}>1 dose</option>
                                        <option value="2 doses" {{ old('number_of_doses', $medicalRecord->number_of_doses) == '2 doses' ? 'selected' : '' }}>2 doses</option>
                                        <option value="N/A" {{ old('number_of_doses', $medicalRecord->number_of_doses) == 'N/A' ? 'selected' : '' }}>N/A</option>
                                    </select>
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
                                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded toggle-trigger" 
                                       value="1" {{ old('has_received_booster', $medicalRecord->has_received_booster || $medicalRecord->booster_type || $medicalRecord->number_of_boosters) ? 'checked' : '' }} data-target="booster-details" aria-label="Received booster">
                                <label class="ml-3 text-sm font-medium text-gray-700" for="has_received_booster">
                                    Received Booster
                                </label>
                            </div>
                            <div class="booster-details space-y-4 transition-opacity duration-300 {{ old('has_received_booster', $medicalRecord->has_received_booster || $medicalRecord->booster_type || $medicalRecord->number_of_boosters) ? 'opacity-100' : 'opacity-0 hidden' }}">
                                <div>
                                    <label for="booster_type" class="block text-sm font-medium text-gray-700 mb-2">Booster Type</label>
                                    <select name="booster_type" id="booster_type" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('booster_type') border-red-500 @enderror" aria-label="Select booster type">
                                        <option value="">Select booster type...</option>
                                        <option value="Pfizer-BioNTech" {{ old('booster_type', $medicalRecord->booster_type) == 'Pfizer-BioNTech' ? 'selected' : '' }}>Pfizer-BioNTech</option>
                                        <option value="Moderna" {{ old('booster_type', $medicalRecord->booster_type) == 'Moderna' ? 'selected' : '' }}>Moderna</option>
                                        <option value="Sinovac" {{ old('booster_type', $medicalRecord->booster_type) == 'Sinovac' ? 'selected' : '' }}>Sinovac</option>
                                        <option value="Sinopharm" {{ old('booster_type', $medicalRecord->booster_type) == 'Sinopharm' ? 'selected' : '' }}>Sinopharm</option>
                                        <option value="AstraZeneca" {{ old('booster_type', $medicalRecord->booster_type) == 'AstraZeneca' ? 'selected' : '' }}>AstraZeneca</option>
                                        <option value="Johnson & Johnson" {{ old('booster_type', $medicalRecord->booster_type) == 'Johnson & Johnson' ? 'selected' : '' }}>Johnson & Johnson</option>
                                        <option value="COVAXIN" {{ old('booster_type', $medicalRecord->booster_type) == 'COVAXIN' ? 'selected' : '' }}>COVAXIN</option>
                                        <option value="Other" {{ old('booster_type', $medicalRecord->booster_type) == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('booster_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="other-booster transition-opacity duration-300 {{ old('booster_type', $medicalRecord->booster_type) == 'Other' ? 'opacity-100' : 'opacity-0 hidden' }}">
                                    <label for="other_booster_type" class="block text-sm font-medium text-gray-700 mb-2">Other Booster Type</label>
                                    <input type="text" name="other_booster_type" id="other_booster_type" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('other_booster_type') border-red-500 @enderror" 
                                           value="{{ old('other_booster_type', $medicalRecord->other_booster_type ?? '') }}" placeholder="Specify other booster type" aria-label="Enter other booster type">
                                    @error('other_booster_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="number_of_boosters" class="block text-sm font-medium text-gray-700 mb-2">Number of Boosters</label>
                                    <select name="number_of_boosters" id="number_of_boosters" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('number_of_boosters') border-red-500 @enderror" aria-label="Select number of boosters">
                                        <option value="">Select number of boosters...</option>
                                        <option value="1 dose" {{ old('number_of_boosters', $medicalRecord->number_of_boosters) == '1 dose' ? 'selected' : '' }}>1 dose</option>
                                        <option value="2 doses" {{ old('number_of_boosters', $medicalRecord->number_of_boosters) == '2 doses' ? 'selected' : '' }}>2 doses</option>
                                        <option value="None" {{ old('number_of_boosters', $medicalRecord->number_of_boosters) == 'None' ? 'selected' : '' }}>None</option>
                                    </select>
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
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-red-600 px-6 py-4">
                <h3 class="text-xl font-semibold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    Emergency Contacts
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Primary Contact -->
                    <div class="bg-red-50 p-4 rounded-lg">
                        <h4 class="text-lg font-medium text-red-800 mb-4">Primary Contact <span class="text-red-500">*</span></h4>
                        <div class="space-y-4">
                            <div>
                                <label for="emergency_contact_name_1" class="block text-sm font-medium text-gray-700 mb-2">
                                    Name <span class="text-red-500">*</span>
                                    <span class="sr-only">Required</span>
                                </label>
                                <input type="text" name="emergency_contact_name_1" id="emergency_contact_name_1" 
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('emergency_contact_name_1') border-red-500 @enderror" 
                                       value="{{ old('emergency_contact_name_1', $medicalRecord->emergency_contact_name_1) }}" required aria-label="Enter primary contact name">
                                @error('emergency_contact_name_1')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="emergency_contact_number_1" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number <span class="text-red-500">*</span>
                                    <span class="sr-only">Required</span>
                                </label>
                                <input type="text" name="emergency_contact_number_1" id="emergency_contact_number_1" 
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('emergency_contact_number_1') border-red-500 @enderror" 
                                       value="{{ old('emergency_contact_number_1', $medicalRecord->emergency_contact_number_1) }}" required aria-label="Enter primary contact phone number">
                                @error('emergency_contact_number_1')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="emergency_contact_relationship_1" class="block text-sm font-medium text-gray-700 mb-2">
                                    Relationship <span class="text-red-500">*</span>
                                    <span class="sr-only">Required</span>
                                </label>
                                <select name="emergency_contact_relationship_1" id="emergency_contact_relationship_1" 
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('emergency_contact_relationship_1') border-red-500 @enderror" required aria-label="Select primary contact relationship">
                                    <option value="">Select relationship...</option>
                                    @foreach(['Parent', 'Mother', 'Father', 'Guardian', 'Spouse', 'Sibling', 'Sister', 'Brother', 'Aunt', 'Uncle', 'Grandparent', 'Friend', 'Other'] as $relationship)
                                        <option value="{{ $relationship }}" {{ old('emergency_contact_relationship_1', $medicalRecord->emergency_contact_relationship_1) == $relationship ? 'selected' : '' }}>{{ $relationship }}</option>
                                    @endforeach
                                </select>
                                @error('emergency_contact_relationship_1')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Secondary Contact -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-lg font-medium text-gray-700 mb-4">Secondary Contact (Optional)</h4>
                        <div class="space-y-4">
                            <div>
                                <label for="emergency_contact_name_2" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                                <input type="text" name="emergency_contact_name_2" id="emergency_contact_name_2" 
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                       value="{{ old('emergency_contact_name_2', $medicalRecord->emergency_contact_name_2) }}" aria-label="Enter secondary contact name">
                            </div>
                            <div>
                                <label for="emergency_contact_number_2" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="text" name="emergency_contact_number_2" id="emergency_contact_number_2" 
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                       value="{{ old('emergency_contact_number_2', $medicalRecord->emergency_contact_number_2) }}" aria-label="Enter secondary contact phone number">
                            </div>
                            <div>
                                <label for="emergency_contact_relationship_2" class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                                <select name="emergency_contact_relationship_2" id="emergency_contact_relationship_2" 
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" aria-label="Select secondary contact relationship">
                                    <option value="">Select relationship...</option>
                                    @foreach(['Parent', 'Mother', 'Father', 'Guardian', 'Spouse', 'Sibling', 'Sister', 'Brother', 'Aunt', 'Uncle', 'Grandparent', 'Friend', 'Other'] as $relationship)
                                        <option value="{{ $relationship }}" {{ old('emergency_contact_relationship_2', $medicalRecord->emergency_contact_relationship_2) == $relationship ? 'selected' : '' }}>{{ $relationship }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row gap-4 justify-end">
                <a href="{{ route('nurse.medical-records.show', $medicalRecord) }}" 
                   class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200" 
                   aria-label="Cancel and return to medical record">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200" 
                        aria-label="Update medical record">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Medical Record
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// JavaScript for form interactions
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('medical-form');

    // Single event listener using event delegation
    form.addEventListener('change', function(e) {
        const target = e.target;

        // Handle toggle triggers
        if (target.classList.contains('toggle-trigger')) {
            const targetClass = target.dataset.target;
            const targetElement = form.querySelector('.' + targetClass);

            if (targetElement) {
                if (target.checked) {
                    targetElement.classList.remove('hidden', 'opacity-0');
                    targetElement.classList.add('opacity-100');
                } else {
                    targetElement.classList.add('hidden', 'opacity-0');
                    targetElement.classList.remove('opacity-100');
                    const inputs = targetElement.querySelectorAll('input, select, textarea');
                    inputs.forEach(input => {
                        if (input.type !== 'checkbox') {
                            input.value = '';
                        }
                    });
                }
            }
        }

        // Handle vaccine name selection
        if (target.id === 'vaccine_name') {
            const otherVaccineDiv = form.querySelector('.other-vaccine');
            if (otherVaccineDiv) {
                if (target.value === 'Other') {
                    otherVaccineDiv.classList.remove('hidden', 'opacity-0');
                    otherVaccineDiv.classList.add('opacity-100');
                } else {
                    otherVaccineDiv.classList.add('hidden', 'opacity-0');
                    otherVaccineDiv.classList.remove('opacity-100');
                    const otherInput = otherVaccineDiv.querySelector('input');
                    if (otherInput) otherInput.value = '';
                }
            }
        }

        // Handle booster type selection
        if (target.id === 'booster_type') {
            const otherBoosterDiv = form.querySelector('.other-booster');
            if (otherBoosterDiv) {
                if (target.value === 'Other') {
                    otherBoosterDiv.classList.remove('hidden', 'opacity-0');
                    otherBoosterDiv.classList.add('opacity-100');
                } else {
                    otherBoosterDiv.classList.add('hidden', 'opacity-0');
                    otherBoosterDiv.classList.remove('opacity-100');
                    const otherInput = otherBoosterDiv.querySelector('input');
                    if (otherInput) otherInput.value = '';
                }
            }
        }
    });

    // Initialize form state on page load
    document.querySelectorAll('.toggle-trigger').forEach(trigger => {
        if (trigger.checked) {
            const targetClass = trigger.dataset.target;
            const targetElement = form.querySelector('.' + targetClass);
            if (targetElement) {
                targetElement.classList.remove('hidden', 'opacity-0');
                targetElement.classList.add('opacity-100');
            }
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            form.submit();
        }
    });
});
</script>
@endsection