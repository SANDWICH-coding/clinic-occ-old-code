{{-- resources/views/student/medical-records/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create My Medical Record')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header Section --}}
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                        <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            Create Medical Record
                        </span>
                    </h1>
                    <p class="text-gray-600 mt-2 text-sm sm:text-base">Set up your complete health profile and medical information</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('student.medical-records.index') }}" 
                       class="flex items-center justify-center sm:justify-start bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 font-medium py-2.5 px-4 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md text-sm sm:text-base">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <span class="hidden sm:inline">Back to Records</span>
                        <span class="sm:hidden">Back</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Information Notice --}}
        <div class="bg-blue-50 border-l-4 border-blue-400 rounded-r-lg p-4 mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-blue-800">Important Information</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>This information will be used for your healthcare at Opol Community College</li>
                            <li>Please provide accurate information to ensure proper medical care</li>
                            <li>After creating your record, please visit the clinic for verification and any required medical assessments</li>
                            <li>All information is kept confidential and secure</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <form action="{{ route('student.medical-records.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Section: Basic Information --}}
            <div class="medical-card bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="gradient-bg text-white px-4 sm:px-6 py-4">
                    <h2 class="text-lg sm:text-xl font-semibold flex items-center">
                        <i class="fas fa-user-circle mr-2"></i>
                        Basic Information
                    </h2>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        {{-- Blood Type --}}
                        <div class="sm:col-span-1">
                            <label for="blood_type" class="block text-sm font-medium text-gray-700 mb-2">Blood Type</label>
                            <select name="blood_type" id="blood_type"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base">
                                <option value="">Select Blood Type</option>
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
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Height and Weight --}}
                        <div class="sm:col-span-1">
                            <label for="height" class="block text-sm font-medium text-gray-700 mb-2">Height (cm)</label>
                            <input type="number" name="height" id="height" step="0.1" min="50" max="300"
                                   value="{{ old('height') }}" placeholder="e.g., 170"
                                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base">
                            @error('height')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-1">
                            <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">Weight (kg)</label>
                            <input type="number" name="weight" id="weight" step="0.1" min="20" max="500"
                                   value="{{ old('weight') }}" placeholder="e.g., 65"
                                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base">
                            @error('weight')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- BMI Display --}}
                        <div id="bmi-display" class="hidden sm:col-span-full lg:col-span-1 lg:col-start-3 p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-200 transition-all duration-300">
                            <div class="text-center">
                                <div class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Your BMI</div>
                                <div class="text-2xl sm:text-3xl font-bold text-blue-600 mb-1" id="bmi-value"></div>
                                <div class="text-xs font-medium px-2 py-1 rounded-full" id="bmi-category"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section: Medical History --}}
            <div class="medical-card bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="gradient-bg text-white px-4 sm:px-6 py-4">
                    <h2 class="text-lg sm:text-xl font-semibold flex items-center">
                        <i class="fas fa-file-medical mr-2"></i>
                        Medical History
                    </h2>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="space-y-6">
                        {{-- Allergies --}}
                        <div>
                            <label for="allergies" class="block text-sm font-medium text-gray-700 mb-2">Allergies</label>
                            <textarea name="allergies" id="allergies" rows="3"
                                      class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base resize-vertical min-h-[100px]"
                                      placeholder="e.g., Penicillin, Peanuts, Bee stings">{{ old('allergies') }}</textarea>
                            @error('allergies')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Past Illnesses --}}
                        <div>
                            <label for="past_illnesses" class="block text-sm font-medium text-gray-700 mb-2">Past Serious Illnesses / Injuries</label>
                            <textarea name="past_illnesses" id="past_illnesses" rows="3"
                                      class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base resize-vertical min-h-[100px]"
                                      placeholder="e.g., Asthma, Diabetes, Hepatitis B">{{ old('past_illnesses') }}</textarea>
                            @error('past_illnesses')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Medical Conditions Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            {{-- Has been pregnant (Female Only) --}}
                            <div class="bg-gray-50 p-4 rounded-xl">
                                <div class="flex items-start space-x-3">
                                    <input type="checkbox" name="has_been_pregnant" id="has_been_pregnant" value="1"
                                           {{ old('has_been_pregnant') ? 'checked' : '' }}
                                           class="mt-1 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                                    <div class="flex-1">
                                        <label for="has_been_pregnant" class="block text-sm font-medium text-gray-700">Have you been pregnant?</label>
                                        <p class="text-xs text-gray-500 mt-1 italic">For female students only</p>
                                    </div>
                                </div>
                                @error('has_been_pregnant')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Has undergone surgery --}}
                            <div class="bg-gray-50 p-4 rounded-xl">
                                <div class="flex items-start space-x-3">
                                    <input type="checkbox" name="has_undergone_surgery" id="surgery_checkbox" value="1"
                                           {{ old('has_undergone_surgery') ? 'checked' : '' }}
                                           onchange="toggleSurgeryDetails()"
                                           class="mt-1 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                                    <label for="surgery_checkbox" class="block text-sm font-medium text-gray-700">Have you undergone any major surgery?</label>
                                </div>
                                @error('has_undergone_surgery')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Maintenance Drugs --}}
                            <div class="bg-gray-50 p-4 rounded-xl">
                                <div class="flex items-start space-x-3">
                                    <input type="checkbox" name="is_taking_maintenance_drugs" id="maintenance_checkbox" value="1"
                                           {{ old('is_taking_maintenance_drugs') ? 'checked' : '' }}
                                           onchange="toggleMaintenanceDrugs()"
                                           class="mt-1 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                                    <label for="maintenance_checkbox" class="block text-sm font-medium text-gray-700">Are you currently taking any maintenance drugs?</label>
                                </div>
                                @error('is_taking_maintenance_drugs')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Hospitalized in the last 6 months --}}
                            <div class="bg-gray-50 p-4 rounded-xl">
                                <div class="flex items-start space-x-3">
                                    <input type="checkbox" name="has_been_hospitalized_6_months" id="hospitalization_checkbox" value="1"
                                           {{ old('has_been_hospitalized_6_months') ? 'checked' : '' }}
                                           onchange="toggleHospitalization()"
                                           class="mt-1 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                                    <label for="hospitalization_checkbox" class="block text-sm font-medium text-gray-700">Have you been hospitalized in the last 6 months?</label>
                                </div>
                                @error('has_been_hospitalized_6_months')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- PWD Status --}}
                            <div class="bg-gray-50 p-4 rounded-xl sm:col-span-2">
                                <div class="flex items-start space-x-3">
                                    <input type="checkbox" name="is_pwd" id="pwd_checkbox" value="1"
                                           {{ old('is_pwd') ? 'checked' : '' }}
                                           onchange="togglePWDDetails()"
                                           class="mt-1 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                                    <label for="pwd_checkbox" class="block text-sm font-medium text-gray-700">Are you a Person with Disability (PWD)?</label>
                                </div>
                                @error('is_pwd')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Conditional Fields --}}
                        {{-- Surgery Details --}}
                        <div id="surgery_details_div" class="hidden transition-all duration-300 opacity-0 transform -translate-y-2">
                            <label for="surgery_details" class="block text-sm font-medium text-gray-700 mb-2">Details of Surgery</label>
                            <textarea name="surgery_details" id="surgery_details" rows="2"
                                      class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base resize-vertical min-h-[80px]"
                                      placeholder="e.g., Appendectomy in 2022">{{ old('surgery_details') }}</textarea>
                            @error('surgery_details')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Maintenance Drugs Details --}}
                        <div id="maintenance_drugs_div" class="hidden transition-all duration-300 opacity-0 transform -translate-y-2">
                            <label for="maintenance_drugs_specify" class="block text-sm font-medium text-gray-700 mb-2">Specify Drugs</label>
                            <textarea name="maintenance_drugs_specify" id="maintenance_drugs_specify" rows="2"
                                      class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base resize-vertical min-h-[80px]"
                                      placeholder="e.g., Losartan, Metformin">{{ old('maintenance_drugs_specify') }}</textarea>
                            @error('maintenance_drugs_specify')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Hospitalization Details --}}
                        <div id="hospitalization_details_div" class="hidden transition-all duration-300 opacity-0 transform -translate-y-2">
                            <label for="hospitalization_details_6_months" class="block text-sm font-medium text-gray-700 mb-2">Details of Hospitalization</label>
                            <textarea name="hospitalization_details_6_months" id="hospitalization_details_6_months" rows="2"
                                      class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base resize-vertical min-h-[80px]"
                                      placeholder="e.g., Dengue Fever, November 2023">{{ old('hospitalization_details_6_months') }}</textarea>
                            @error('hospitalization_details_6_months')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- PWD Details --}}
                        <div id="pwd_details_div" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 transition-all duration-300 opacity-0 transform -translate-y-2">
                            <div>
                                <label for="pwd_id" class="block text-sm font-medium text-gray-700 mb-2">PWD ID Number</label>
                                <input type="text" name="pwd_id" id="pwd_id"
                                       value="{{ old('pwd_id') }}"
                                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base">
                                @error('pwd_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="pwd_reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for PWD Status</label>
                                <textarea name="pwd_reason" id="pwd_reason" rows="2"
                                          class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base resize-vertical min-h-[80px]"
                                          placeholder="e.g., Visual impairment, mobility issues">{{ old('pwd_reason') }}</textarea>
                                @error('pwd_reason')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Notes/Other Health Problems --}}
                        <div>
                            <label for="notes_health_problems" class="block text-sm font-medium text-gray-700 mb-2">Other Health Problems/Notes</label>
                            <textarea name="notes_health_problems" id="notes_health_problems" rows="3"
                                      class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base resize-vertical min-h-[100px]"
                                      placeholder="e.g., History of migraines, mild scoliosis">{{ old('notes_health_problems') }}</textarea>
                            @error('notes_health_problems')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section: Vaccination Information --}}
            <div class="medical-card bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="gradient-bg text-white px-4 sm:px-6 py-4">
                    <h2 class="text-lg sm:text-xl font-semibold flex items-center">
                        <i class="fas fa-syringe mr-2"></i>
                        COVID-19 Vaccination Information
                    </h2>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="space-y-6">
                        {{-- Vaccination Status --}}
                        <div class="bg-gray-50 p-4 rounded-xl">
                            <div class="flex items-start space-x-3">
                                <input type="checkbox" name="is_fully_vaccinated" id="is_fully_vaccinated" value="1"
                                       {{ old('is_fully_vaccinated') ? 'checked' : '' }}
                                       onchange="toggleVaccinationDetails()"
                                       class="mt-1 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                                <label for="is_fully_vaccinated" class="block text-sm font-medium text-gray-700">Are you fully vaccinated against COVID-19?</label>
                            </div>
                        </div>

                        {{-- Vaccination Details --}}
                        <div id="vaccination_details" class="hidden space-y-4 transition-all duration-300 opacity-0 transform -translate-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                <div>
                                    <label for="vaccine_name" class="block text-sm font-medium text-gray-700 mb-2">Vaccine Name/Brand</label>
                                    <select name="vaccine_name" id="vaccine_name" 
                                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base">
                                        <option value="">Select Vaccine</option>
                                        <option value="Pfizer-BioNTech" {{ old('vaccine_name') == 'Pfizer-BioNTech' ? 'selected' : '' }}>Pfizer-BioNTech</option>
                                        <option value="Moderna" {{ old('vaccine_name') == 'Moderna' ? 'selected' : '' }}>Moderna</option>
                                        <option value="AstraZeneca" {{ old('vaccine_name') == 'AstraZeneca' ? 'selected' : '' }}>AstraZeneca</option>
                                        <option value="Johnson & Johnson" {{ old('vaccine_name') == 'Johnson & Johnson' ? 'selected' : '' }}>Johnson & Johnson</option>
                                        <option value="Sinovac" {{ old('vaccine_name') == 'Sinovac' ? 'selected' : '' }}>Sinovac</option>
                                        <option value="Sinopharm" {{ old('vaccine_name') == 'Sinopharm' ? 'selected' : '' }}>Sinopharm</option>
                                        <option value="Other" {{ old('vaccine_name') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('vaccine_name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="vaccine_date" class="block text-sm font-medium text-gray-700 mb-2">Date of Last Dose</label>
                                    <input type="date" name="vaccine_date" id="vaccine_date" 
                                           value="{{ old('vaccine_date') }}"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base">
                                    @error('vaccine_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                <div>
                                    <label for="number_of_doses" class="block text-sm font-medium text-gray-700 mb-2">Number of Doses</label>
                                    <input type="number" name="number_of_doses" id="number_of_doses" min="0" max="5" value="{{ old('number_of_doses') }}"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base">
                                    @error('number_of_doses')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Booster Checkbox --}}
                                <div class="bg-gray-50 p-4 rounded-xl">
                                    <div class="flex items-start space-x-3">
                                        <input type="checkbox" name="has_received_booster" id="has_received_booster" value="1"
                                               {{ old('has_received_booster') ? 'checked' : '' }}
                                               onchange="toggleBoosterDetails()"
                                               class="mt-1 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                                        <label for="has_received_booster" class="block text-sm font-medium text-gray-700">
                                            Have you received booster shots?
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Booster Details --}}
                            <div id="booster_details" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 transition-all duration-300 opacity-0 transform -translate-y-2">
                                <div>
                                    <label for="number_of_boosters" class="block text-sm font-medium text-gray-700 mb-2">Number of Boosters</label>
                                    <input type="number" name="number_of_boosters" id="number_of_boosters" min="0" max="5" value="{{ old('number_of_boosters') }}"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base">
                                    @error('number_of_boosters')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="booster_type" class="block text-sm font-medium text-gray-700 mb-2">Booster Type</label>
                                    <select name="booster_type" id="booster_type" 
                                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base">
                                        <option value="">Select Booster Type</option>
                                        <option value="Pfizer-BioNTech" {{ old('booster_type') == 'Pfizer-BioNTech' ? 'selected' : '' }}>Pfizer-BioNTech</option>
                                        <option value="Moderna" {{ old('booster_type') == 'Moderna' ? 'selected' : '' }}>Moderna</option>
                                        <option value="AstraZeneca" {{ old('booster_type') == 'AstraZeneca' ? 'selected' : '' }}>AstraZeneca</option>
                                        <option value="Sinovac" {{ old('booster_type') == 'Sinovac' ? 'selected' : '' }}>Sinovac</option>
                                        <option value="None" {{ old('booster_type') == 'None' ? 'selected' : '' }}>None</option>
                                        <option value="Other" {{ old('booster_type') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('booster_type')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section: Emergency Contact --}}
            <div class="medical-card bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="gradient-bg text-white px-4 sm:px-6 py-4">
                    <h2 class="text-lg sm:text-xl font-semibold flex items-center">
                        <i class="fas fa-address-book mr-2"></i>
                        Emergency Contact
                    </h2>
                </div>
                <div class="p-4 sm:p-6">
                    <p class="text-sm text-gray-600 mb-6">Please provide at least one emergency contact.</p>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Primary Contact (Required) --}}
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-4 sm:p-6 rounded-xl border border-blue-200">
                            <h3 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                                <i class="fas fa-star text-yellow-500 mr-2"></i>
                                Primary Contact
                                <span class="text-red-500 ml-1">*</span>
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="emergency_contact_name_1" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                    <input type="text" name="emergency_contact_name_1" id="emergency_contact_name_1" 
                                           value="{{ old('emergency_contact_name_1') }}" required
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base">
                                    @error('emergency_contact_name_1')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="emergency_contact_number_1" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                    <input type="text" name="emergency_contact_number_1" id="emergency_contact_number_1" 
                                           value="{{ old('emergency_contact_number_1') }}" required
                                           placeholder="e.g., +63 912 345 6789"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base">
                                    @error('emergency_contact_number_1')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="emergency_contact_relationship_1" class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                                    <select name="emergency_contact_relationship_1" id="emergency_contact_relationship_1" required
                                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base">
                                        <option value="">Select Relationship</option>
                                        <option value="parent" {{ old('emergency_contact_relationship_1') == 'parent' ? 'selected' : '' }}>Parent</option>
                                        <option value="mother" {{ old('emergency_contact_relationship_1') == 'mother' ? 'selected' : '' }}>Mother</option>
                                        <option value="father" {{ old('emergency_contact_relationship_1') == 'father' ? 'selected' : '' }}>Father</option>
                                        <option value="spouse" {{ old('emergency_contact_relationship_1') == 'spouse' ? 'selected' : '' }}>Spouse</option>
                                        <option value="sibling" {{ old('emergency_contact_relationship_1') == 'sibling' ? 'selected' : '' }}>Sibling</option>
                                        <option value="child" {{ old('emergency_contact_relationship_1') == 'child' ? 'selected' : '' }}>Child</option>
                                        <option value="guardian" {{ old('emergency_contact_relationship_1') == 'guardian' ? 'selected' : '' }}>Guardian</option>
                                        <option value="friend" {{ old('emergency_contact_relationship_1') == 'friend' ? 'selected' : '' }}>Friend</option>
                                        <option value="other" {{ old('emergency_contact_relationship_1') == 'other' ? 'selected' : '' }}>Other Relative</option>
                                    </select>
                                    @error('emergency_contact_relationship_1')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        {{-- Secondary Contact (Optional) --}}
                        <div class="bg-gradient-to-br from-gray-50 to-blue-50 p-4 sm:p-6 rounded-xl border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                                <i class="fas fa-user-friends text-gray-500 mr-2"></i>
                                Secondary Contact
                                <span class="text-gray-500 text-sm font-normal ml-2">(Optional)</span>
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="emergency_contact_name_2" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                    <input type="text" name="emergency_contact_name_2" id="emergency_contact_name_2" 
                                           value="{{ old('emergency_contact_name_2') }}"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base">
                                    @error('emergency_contact_name_2')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="emergency_contact_number_2" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                    <input type="text" name="emergency_contact_number_2" id="emergency_contact_number_2" 
                                           value="{{ old('emergency_contact_number_2') }}"
                                           placeholder="e.g., +63 912 345 6789"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base">
                                    @error('emergency_contact_number_2')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="emergency_contact_relationship_2" class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                                    <select name="emergency_contact_relationship_2" id="emergency_contact_relationship_2"
                                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-sm sm:text-base">
                                        <option value="">Select Relationship</option>
                                        <option value="parent" {{ old('emergency_contact_relationship_2') == 'parent' ? 'selected' : '' }}>Parent</option>
                                        <option value="mother" {{ old('emergency_contact_relationship_2') == 'mother' ? 'selected' : '' }}>Mother</option>
                                        <option value="father" {{ old('emergency_contact_relationship_2') == 'father' ? 'selected' : '' }}>Father</option>
                                        <option value="spouse" {{ old('emergency_contact_relationship_2') == 'spouse' ? 'selected' : '' }}>Spouse</option>
                                        <option value="sibling" {{ old('emergency_contact_relationship_2') == 'sibling' ? 'selected' : '' }}>Sibling</option>
                                        <option value="child" {{ old('emergency_contact_relationship_2') == 'child' ? 'selected' : '' }}>Child</option>
                                        <option value="guardian" {{ old('emergency_contact_relationship_2') == 'guardian' ? 'selected' : '' }}>Guardian</option>
                                        <option value="friend" {{ old('emergency_contact_relationship_2') == 'friend' ? 'selected' : '' }}>Friend</option>
                                        <option value="other" {{ old('emergency_contact_relationship_2') == 'other' ? 'selected' : '' }}>Other Relative</option>
                                    </select>
                                    @error('emergency_contact_relationship_2')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Terms and Conditions --}}
            <div class="medical-card bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 sm:p-6">
                    <div class="flex items-start space-x-4">
                        <input type="checkbox" id="terms_agreement" required
                               class="mt-1 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-2">
                        <div class="flex-1">
                            <label for="terms_agreement" class="text-sm text-gray-700">
                                <span class="font-medium text-gray-900">I confirm that:</span>
                                <ul class="mt-3 list-disc pl-5 space-y-2">
                                    <li class="text-sm">All information provided is accurate and complete to the best of my knowledge</li>
                                    <li class="text-sm">I understand this information will be used for my healthcare at Opol Community College</li>
                                    <li class="text-sm">I will update this information if there are any changes to my health status</li>
                                    <li class="text-sm">I will visit the clinic for verification and any required medical assessments</li>
                                </ul>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Buttons --}}
            <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6">
                <a href="{{ route('student.medical-records.index') }}" 
                   class="order-2 sm:order-1 bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 font-medium py-3 px-6 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md text-center text-sm sm:text-base min-h-[44px] flex items-center justify-center">
                    Cancel
                </a>
                <button type="submit" class="order-1 sm:order-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium py-3 px-8 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base min-h-[44px]">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Create Medical Record
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .medical-card {
        transition: all 0.3s ease;
    }
    
    @media (min-width: 640px) {
        .medical-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 5px 10px -5px rgba(0, 0, 0, 0.04);
        }
    }
    
    .gradient-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    /* Mobile-specific styles */
    @media (max-width: 640px) {
        .medical-card {
            margin-left: -1rem;
            margin-right: -1rem;
            border-radius: 0;
            border-left: none;
            border-right: none;
        }
        
        input, select, textarea {
            font-size: 16px; /* Prevents zoom on iOS */
        }
        
        .min-h-\[44px\] {
            min-height: 44px; /* Touch-friendly button height */
        }
    }
    
    /* Smooth transitions for conditional fields */
    .conditional-field {
        transition: all 0.3s ease;
        opacity: 0;
        transform: translateY(-10px);
    }
    
    .conditional-field.show {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<script>
    // Enhanced toggle functions with smooth animations
    function toggleElement(checkboxId, elementId) {
        const checkbox = document.getElementById(checkboxId);
        const element = document.getElementById(elementId);
        
        if (checkbox.checked) {
            element.classList.remove('hidden');
            setTimeout(() => {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, 10);
        } else {
            element.style.opacity = '0';
            element.style.transform = 'translateY(-10px)';
            setTimeout(() => element.classList.add('hidden'), 300);
        }
    }

    function toggleVaccinationDetails() {
        toggleElement('is_fully_vaccinated', 'vaccination_details');
    }
    
    function toggleSurgeryDetails() {
        toggleElement('surgery_checkbox', 'surgery_details_div');
    }

    function toggleMaintenanceDrugs() {
        toggleElement('maintenance_checkbox', 'maintenance_drugs_div');
    }

    function toggleHospitalization() {
        toggleElement('hospitalization_checkbox', 'hospitalization_details_div');
    }

    function togglePWDDetails() {
        toggleElement('pwd_checkbox', 'pwd_details_div');
    }

    function toggleBoosterDetails() {
        toggleElement('has_received_booster', 'booster_details');
    }
    
    // Enhanced BMI Calculator with color coding
    function calculateBMI() {
        const height = parseFloat(document.getElementById('height').value);
        const weight = parseFloat(document.getElementById('weight').value);
        const bmiDisplay = document.getElementById('bmi-display');
        const bmiValue = document.getElementById('bmi-value');
        const bmiCategory = document.getElementById('bmi-category');
        
        if (height && weight && height > 0 && weight > 0) {
            const heightInMeters = height / 100;
            const bmi = (weight / (heightInMeters * heightInMeters));
            
            bmiValue.textContent = bmi.toFixed(1);
            
            // Determine BMI category with colors
            let category = '';
            let colorClass = '';
            if (bmi < 18.5) {
                category = 'Underweight';
                colorClass = 'text-yellow-600 bg-yellow-100';
            } else if (bmi < 25) {
                category = 'Normal weight';
                colorClass = 'text-green-600 bg-green-100';
            } else if (bmi < 30) {
                category = 'Overweight';
                colorClass = 'text-orange-600 bg-orange-100';
            } else {
                category = 'Obese';
                colorClass = 'text-red-600 bg-red-100';
            }
            
            bmiCategory.textContent = category;
            bmiCategory.className = `text-xs font-medium px-2 py-1 rounded-full ${colorClass}`;
            
            // Show with animation
            bmiDisplay.classList.remove('hidden');
            setTimeout(() => {
                bmiDisplay.style.opacity = '1';
                bmiDisplay.style.transform = 'scale(1)';
            }, 10);
        } else {
            bmiDisplay.style.opacity = '0';
            bmiDisplay.style.transform = 'scale(0.95)';
            setTimeout(() => bmiDisplay.classList.add('hidden'), 300);
        }
    }

    // Add event listeners for BMI calculation
    document.getElementById('height').addEventListener('input', calculateBMI);
    document.getElementById('weight').addEventListener('input', calculateBMI);

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize BMI if values exist
        if (document.getElementById('height').value && document.getElementById('weight').value) {
            calculateBMI();
        }
        
        // Initialize conditional fields based on old values
        const conditionalFields = [
            { checkbox: 'is_fully_vaccinated', fn: toggleVaccinationDetails },
            { checkbox: 'surgery_checkbox', fn: toggleSurgeryDetails },
            { checkbox: 'maintenance_checkbox', fn: toggleMaintenanceDrugs },
            { checkbox: 'hospitalization_checkbox', fn: toggleHospitalization },
            { checkbox: 'pwd_checkbox', fn: togglePWDDetails },
            { checkbox: 'has_received_booster', fn: toggleBoosterDetails }
        ];

        conditionalFields.forEach(field => {
            const checkbox = document.getElementById(field.checkbox);
            if (checkbox && checkbox.checked) {
                // Small delay to ensure DOM is ready
                setTimeout(() => field.fn(), 100);
            }
        });

        // Add smooth transitions to all conditional fields
        const conditionalElements = document.querySelectorAll('[id$="_div"]');
        conditionalElements.forEach(el => {
            el.style.transition = 'all 0.3s ease';
        });

        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const terms = document.getElementById('terms_agreement');
            if (!terms.checked) {
                e.preventDefault();
                // Enhanced error display
                const errorDiv = document.createElement('div');
                errorDiv.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-lg shadow-lg z-50 max-w-sm w-full';
                errorDiv.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                        <div>
                            <p class="font-medium">Please agree to the terms and conditions</p>
                            <p class="text-sm mt-1">You must accept the terms before submitting</p>
                        </div>
                    </div>
                `;
                document.body.appendChild(errorDiv);
                
                // Scroll to terms
                terms.closest('.medical-card').scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'center'
                });
                
                // Add visual feedback to terms checkbox
                terms.classList.add('ring-2', 'ring-red-500');
                
                // Remove error after 5 seconds
                setTimeout(() => {
                    errorDiv.remove();
                    terms.classList.remove('ring-2', 'ring-red-500');
                }, 5000);
                
                terms.focus();
            }
        });

        // Enhanced input focus effects
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-blue-200', 'bg-blue-50');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-blue-200', 'bg-blue-50');
            });
        });
    });

    // Performance optimization: Debounce BMI calculation
    let bmiTimeout;
    function debouncedCalculateBMI() {
        clearTimeout(bmiTimeout);
        bmiTimeout = setTimeout(calculateBMI, 300);
    }

    // Update BMI event listeners to use debounced version
    document.getElementById('height').addEventListener('input', debouncedCalculateBMI);
    document.getElementById('weight').addEventListener('input', debouncedCalculateBMI);
</script>
@endsection