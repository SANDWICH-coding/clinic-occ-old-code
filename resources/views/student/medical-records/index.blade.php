@extends('layouts.app')

@section('title', 'Medical Records')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900">
                <span class="gradient-text bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    Medical Records
                </span>
            </h1>
            <p class="text-gray-600 mt-2">Complete health profile and medical information</p>
        </div>
        
        @if($medicalRecord)
        <div class="flex items-center space-x-3">
            <a href="{{ route('student.medical-records.edit', $medicalRecord->id) }}"
               class="flex items-center bg-white text-blue-600 border border-blue-200 hover:bg-blue-50 font-medium py-3 px-5 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md">
                <i class="fas fa-edit mr-2"></i>
                Edit Record
            </a>
        </div>
        @endif
    </div>

    <!-- Session Messages -->
    @if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-start">
        <div class="flex-shrink-0">
            <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
        </div>
        <div>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start">
        <div class="flex-shrink-0">
            <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
        </div>
        <div>
            <p class="text-red-800 font-medium">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    @if(!$medicalRecord)
    <!-- No Medical Record State -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 md:p-12 text-center">
        <div class="w-24 h-24 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6 border-2 border-blue-200">
            <i class="fas fa-file-medical-alt text-blue-500 text-3xl"></i>
        </div>
        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">No Medical Record Found</h2>
        <p class="text-gray-600 mb-8 max-w-lg mx-auto leading-relaxed">You haven't created your medical record yet. Set up your profile to access health services and ensure your safety in emergencies.</p>
        <a href="{{ route('student.medical-records.create') }}"
           class="inline-flex items-center bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 px-8 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
            <i class="fas fa-plus mr-2"></i>
            Create Medical Record
        </a>
    </div>
    @else
    <!-- Medical Record Exists -->
    <div class="space-y-6">
        <!-- Profile Header Card -->
        <div class="medical-card bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="gradient-bg text-white p-6 md:p-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-user-md text-white"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl md:text-3xl font-bold">{{ $medicalRecord->user->full_name ?? $medicalRecord->user->name ?? 'Unknown User' }}</h2>
                                <p class="opacity-90 text-sm md:text-base">{{ $medicalRecord->user->email ?? 'No email provided' }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-4">
                            @if($medicalRecord->user->student_id ?? false)
                                <span class="bg-white/20 px-3 py-1 rounded-full text-sm">ID: {{ $medicalRecord->user->student_id }}</span>
                            @endif
                            @if($medicalRecord->user->course ?? false)
                                <span class="bg-white/20 px-3 py-1 rounded-full text-sm">{{ $medicalRecord->user->course }} - Year {{ $medicalRecord->user->year_level ?? 'N/A' }}</span>
                            @endif
                        </div>
                    </div>
                    @if(($stats['bmi'] ?? false) && $stats['bmi_category'] ?? false)
                    <div class="text-center mt-4 md:mt-0">
                        <div class="text-4xl md:text-5xl font-extrabold bg-white/20 rounded-full px-6 py-4 inline-block">
                            {{ number_format($stats['bmi'], 1) }}
                        </div>
                        <div class="opacity-90 text-sm md:text-base mt-2">BMI • {{ $stats['bmi_category'] }}</div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Health Stats -->
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl text-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-tint text-blue-600"></i>
                        </div>
                        <div class="text-sm font-medium text-blue-700 mb-1">Blood Type</div>
                        <div class="text-lg font-bold text-blue-600">{{ $medicalRecord->blood_type ?: 'Not Set' }}</div>
                    </div>
                    
                    <div class="bg-green-50 border border-green-100 p-4 rounded-xl text-center">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-ruler-vertical text-green-600"></i>
                        </div>
                        <div class="text-sm font-medium text-green-700 mb-1">Height</div>
                        <div class="text-lg font-bold text-green-600">{{ $medicalRecord->height ? $medicalRecord->height . ' cm' : 'Not Set' }}</div>
                    </div>
                    
                    <div class="bg-purple-50 border border-purple-100 p-4 rounded-xl text-center">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-weight text-purple-600"></i>
                        </div>
                        <div class="text-sm font-medium text-purple-700 mb-1">Weight</div>
                        <div class="text-lg font-bold text-purple-600">{{ $medicalRecord->weight ? $medicalRecord->weight . ' kg' : 'Not Set' }}</div>
                    </div>
                    
                    <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl text-center">
                        <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-heartbeat text-amber-600"></i>
                        </div>
                        <div class="text-sm font-medium text-amber-700 mb-1">Risk Level</div>
                        <div class="text-lg font-bold {{ ($stats['health_risk_level'] ?? 'Unknown') === 'High' ? 'text-red-600' : (($stats['health_risk_level'] ?? 'Unknown') === 'Medium' ? 'text-orange-600' : 'text-green-600') }}">
                            {{ $stats['health_risk_level'] ?? 'Unknown' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Allergies Alert -->
        @if($medicalRecord->allergies && $medicalRecord->allergies !== 'none' && $medicalRecord->allergies !== 'None')
        <div class="medical-card bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 p-6 rounded-xl">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-red-900 text-lg mb-2">Allergies Alert</h3>
                    <p class="text-red-800 font-medium leading-relaxed">{{ $medicalRecord->allergies }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Medical History & Vaccination -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Medical History -->
            <div class="medical-card bg-white border border-gray-200 p-6 rounded-xl">
                <h4 class="font-bold text-gray-900 mb-6 text-lg flex items-center">
                    <i class="fas fa-history text-blue-600 mr-3"></i>
                    Medical History
                </h4>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-700">Previous pregnancy</span>
                        <span class="status-badge {{ $medicalRecord->has_been_pregnant ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800' }}">
                            {{ $medicalRecord->has_been_pregnant ? 'Yes' : 'No' }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-700">Previous surgery</span>
                        <span class="status-badge {{ $medicalRecord->has_undergone_surgery ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800' }}">
                            {{ $medicalRecord->has_undergone_surgery ? 'Yes' : 'No' }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-700">Maintenance drugs</span>
                        <span class="status-badge {{ $medicalRecord->is_taking_maintenance_drugs ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800' }}">
                            {{ $medicalRecord->is_taking_maintenance_drugs ? 'Yes' : 'No' }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-700">Person with disability</span>
                        <span class="status-badge {{ $medicalRecord->is_pwd ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800' }}">
                            {{ $medicalRecord->is_pwd ? 'Yes' : 'No' }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between items-center p-4 {{ $medicalRecord->has_been_hospitalized_6_months ? 'bg-red-50 border border-red-200' : 'bg-gray-50' }} rounded-lg">
                        <span class="text-sm text-gray-700">Recent hospitalization (6 months)</span>
                        <span class="status-badge {{ $medicalRecord->has_been_hospitalized_6_months ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ $medicalRecord->has_been_hospitalized_6_months ? 'Yes' : 'No' }}
                        </span>
                    </div>

                    <!-- Surgery Details -->
                    @if($medicalRecord->has_undergone_surgery && $medicalRecord->surgery_details)
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h5 class="font-semibold text-blue-800 mb-2 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            Surgery Details
                        </h5>
                        <p class="text-sm text-blue-700">{{ $medicalRecord->surgery_details }}</p>
                    </div>
                    @endif

                    <!-- Maintenance Drugs Details -->
                    @if($medicalRecord->is_taking_maintenance_drugs && $medicalRecord->maintenance_drugs_specify)
                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                        <h5 class="font-semibold text-green-800 mb-2 flex items-center">
                            <i class="fas fa-pills mr-2"></i>
                            Current Medications
                        </h5>
                        <p class="text-sm text-green-700">{{ $medicalRecord->maintenance_drugs_specify }}</p>
                    </div>
                    @endif

                    <!-- Hospitalization Details -->
                    @if($medicalRecord->has_been_hospitalized_6_months && $medicalRecord->hospitalization_details_6_months)
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <h5 class="font-semibold text-red-800 mb-2 flex items-center">
                            <i class="fas fa-hospital mr-2"></i>
                            Hospitalization Details
                        </h5>
                        <p class="text-sm text-red-700">{{ $medicalRecord->hospitalization_details_6_months }}</p>
                    </div>
                    @endif

                    <!-- Past Illnesses -->
                    @if($medicalRecord->past_illnesses && $medicalRecord->past_illnesses !== 'none' && $medicalRecord->past_illnesses !== 'None')
                    <div class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                        <h5 class="font-semibold text-purple-800 mb-2 flex items-center">
                            <i class="fas fa-file-medical mr-2"></i>
                            Past Illnesses
                        </h5>
                        <p class="text-sm text-purple-700">{{ $medicalRecord->past_illnesses }}</p>
                    </div>
                    @endif

                    <!-- Family History -->
                    @if($medicalRecord->family_history_details)
                    <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                        <h5 class="font-semibold text-indigo-800 mb-2 flex items-center">
                            <i class="fas fa-users mr-2"></i>
                            Family Medical History
                        </h5>
                        <p class="text-sm text-indigo-700">{{ $medicalRecord->family_history_details }}</p>
                    </div>
                    @endif

                    <!-- Health Notes -->
                    @if($medicalRecord->notes_health_problems)
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                        <h5 class="font-semibold text-amber-800 mb-2 flex items-center">
                            <i class="fas fa-sticky-note mr-2"></i>
                            Additional Health Notes
                        </h5>
                        <p class="text-sm text-amber-700">{{ $medicalRecord->notes_health_problems }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Vaccination Status -->
            <div class="medical-card bg-white border border-gray-200 p-6 rounded-xl">
                <h4 class="font-bold text-gray-900 mb-6 text-lg flex items-center">
                    <i class="fas fa-syringe text-green-600 mr-3"></i>
                    Vaccination Status
                </h4>
                <div class="space-y-4">
                    <!-- Primary Vaccination Status -->
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-700">COVID-19 vaccination</span>
                        <div class="flex items-center">
                            <span class="w-3 h-3 {{ $medicalRecord->is_fully_vaccinated ? 'bg-green-500' : 'bg-red-500' }} rounded-full mr-2"></span>
                            <span class="status-badge {{ $medicalRecord->is_fully_vaccinated ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $medicalRecord->is_fully_vaccinated ? 'Complete' : 'Incomplete' }}
                            </span>
                        </div>
                    </div>
                    
                    @if($medicalRecord->is_fully_vaccinated)
                        <!-- Vaccine Details -->
                        @if($medicalRecord->vaccine_type || $medicalRecord->vaccine_name)
                            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-green-800">Vaccine Type</span>
                                    <span class="text-sm font-bold text-green-900">{{ $medicalRecord->vaccine_type ?: $medicalRecord->vaccine_name }}</span>
                                </div>
                            </div>
                        @endif
                        
                        @if($medicalRecord->number_of_doses)
                            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-green-800">Number of Doses</span>
                                    <span class="text-sm font-bold text-green-900">{{ $medicalRecord->number_of_doses }}</span>
                                </div>
                            </div>
                        @endif
                        
                        @if($medicalRecord->vaccine_date)
                            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-green-800">Last Dose Date</span>
                                    <span class="text-sm font-bold text-green-900">{{ $medicalRecord->vaccine_date->format('M j, Y') }}</span>
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Booster Information -->
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <h5 class="font-semibold text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-shield-alt text-blue-600 mr-2"></i>
                            Booster Information
                        </h5>
                        
                        @if($medicalRecord->number_of_boosters && $medicalRecord->number_of_boosters !== 'None')
                            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg mb-3">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-blue-800">Number of Boosters</span>
                                    <span class="text-sm font-bold text-blue-900">{{ $medicalRecord->number_of_boosters }}</span>
                                </div>
                            </div>
                        @endif
                        
                        @if($medicalRecord->booster_type)
                            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-blue-800">Booster Type</span>
                                    <span class="text-sm font-bold text-blue-900">{{ $medicalRecord->booster_type }}</span>
                                </div>
                            </div>
                        @endif

                        @if(!$medicalRecord->number_of_boosters || $medicalRecord->number_of_boosters === 'None')
                            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-center">
                                <i class="fas fa-info-circle text-gray-400 text-xl mb-2"></i>
                                <p class="text-sm text-gray-600">No booster shots recorded</p>
                            </div>
                        @endif
                    </div>

                    <!-- Vaccination Notes -->
                    @if($medicalRecord->notes_vaccination)
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                        <h5 class="font-semibold text-amber-800 mb-2 flex items-center">
                            <i class="fas fa-sticky-note mr-2"></i>
                            Vaccination Notes
                        </h5>
                        <p class="text-sm text-amber-700">{{ $medicalRecord->notes_vaccination }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Emergency Contacts -->
        <div class="medical-card bg-white border border-gray-200 p-6 rounded-xl">
            <h4 class="font-bold text-gray-900 mb-6 text-lg flex items-center">
                <i class="fas fa-address-book text-blue-600 mr-3"></i>
                Emergency Contacts
            </h4>
            
            @if($medicalRecord->emergency_contact_name_1 || $medicalRecord->emergency_contact_name_2)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($medicalRecord->emergency_contact_name_1)
                        <div class="bg-blue-50 border border-blue-200 p-6 rounded-xl">
                            <div class="flex items-start mb-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-1">Primary Contact</div>
                                    <div class="font-bold text-gray-800 text-lg">{{ $medicalRecord->emergency_contact_name_1 }}</div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                @if($medicalRecord->emergency_contact_relationship_1)
                                    <div class="flex items-center text-sm text-gray-700">
                                        <i class="fas fa-link text-gray-500 mr-2"></i>
                                        {{ ucfirst($medicalRecord->emergency_contact_relationship_1) }}
                                    </div>
                                @endif
                                @if($medicalRecord->emergency_contact_number_1)
                                    <a href="tel:{{ $medicalRecord->emergency_contact_number_1 }}"
                                       class="flex items-center text-blue-700 hover:text-blue-900 text-sm font-medium transition-colors duration-200">
                                        <i class="fas fa-phone-alt mr-2"></i>
                                        {{ $medicalRecord->emergency_contact_number_1 }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    @if($medicalRecord->emergency_contact_name_2)
                        <div class="bg-indigo-50 border border-indigo-200 p-6 rounded-xl">
                            <div class="flex items-start mb-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-user-friends text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs font-semibold text-indigo-700 uppercase tracking-wide mb-1">Secondary Contact</div>
                                    <div class="font-bold text-gray-800 text-lg">{{ $medicalRecord->emergency_contact_name_2 }}</div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                @if($medicalRecord->emergency_contact_relationship_2)
                                    <div class="flex items-center text-sm text-gray-700">
                                        <i class="fas fa-link text-gray-500 mr-2"></i>
                                        {{ ucfirst($medicalRecord->emergency_contact_relationship_2) }}
                                    </div>
                                @endif
                                @if($medicalRecord->emergency_contact_number_2)
                                    <a href="tel:{{ $medicalRecord->emergency_contact_number_2 }}"
                                       class="flex items-center text-indigo-700 hover:text-indigo-900 text-sm font-medium transition-colors duration-200">
                                        <i class="fas fa-phone-alt mr-2"></i>
                                        {{ $medicalRecord->emergency_contact_number_2 }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                    </div>
                    <h5 class="text-gray-600 font-medium mb-2">No Emergency Contacts</h5>
                    <p class="text-sm text-gray-500">Please add emergency contacts for your safety.</p>
                </div>
            @endif
        </div>

        <!-- Missing Information Alert -->
        @if(($stats['missing_fields'] ?? false) && count($stats['missing_fields']) > 0)
        <div class="medical-card bg-gradient-to-r from-yellow-50 to-amber-50 border-l-4 border-yellow-500 p-6 rounded-xl">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-exclamation-circle text-yellow-600"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-yellow-900 text-lg mb-3">Missing Information</h3>
                    <p class="text-yellow-800 mb-4">Please complete the following fields to have a complete medical record:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
                        @foreach($stats['missing_fields'] as $field)
                            <div class="flex items-center p-2 bg-white rounded-lg border border-yellow-200">
                                <i class="fas fa-circle text-yellow-500 mr-2 text-xs"></i>
                                <span class="text-sm text-yellow-800">{{ ucwords(str_replace('_', ' ', $field)) }}</span>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('student.medical-records.edit', $medicalRecord->id) }}"
                       class="inline-flex items-center bg-yellow-600 hover:bg-yellow-700 text-white font-semibold px-6 py-2.5 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="fas fa-edit mr-2"></i>
                        Complete Missing Fields
                    </a>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Record Information Footer -->
        <div class="medical-card bg-white border border-gray-200 p-6 rounded-xl">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="font-semibold text-gray-800 mb-1">Record Created</p>
                    <p class="text-gray-600">{{ $medicalRecord->created_at->format('M j, Y \a\t g:i A') }}</p>
                </div>
                @if($medicalRecord->updated_at != $medicalRecord->created_at)
                <div>
                    <p class="font-semibold text-gray-800 mb-1">Last Updated</p>
                    <p class="text-gray-600">{{ $medicalRecord->updated_at->format('M j, Y \a\t g:i A') }}</p>
                </div>
                @endif
                <div class="md:text-right">
                    <div class="flex items-center justify-end">
                        <div class="relative w-16 h-16 mr-4">
                            <svg class="w-16 h-16 progress-ring" viewBox="0 0 100 100">
                                <circle class="text-gray-200 stroke-current" stroke-width="8" cx="50" cy="50" r="40" fill="transparent"></circle>
                                <circle class="progress-ring-circle text-blue-600 stroke-current" stroke-width="8" stroke-linecap="round" cx="50" cy="50" r="40" fill="transparent" 
                                        stroke-dasharray="251.2" 
                                        stroke-dashoffset="{{ 251.2 - (251.2 * ($stats['completion_percentage'] ?? 0)) / 100 }}">
                                </circle>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-lg font-bold text-blue-600">{{ $stats['completion_percentage'] ?? 0 }}%</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide font-medium">Completion</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Floating Action Button (for mobile) -->
@if($medicalRecord)
<div class="floating-action md:hidden">
    <a href="{{ route('student.medical-records.edit', $medicalRecord->id) }}"
       class="flex items-center justify-center w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg transition-all duration-200 transform hover:scale-110">
        <i class="fas fa-edit text-xl"></i>
    </a>
</div>
@endif

<style>
    .medical-card {
        transition: all 0.3s ease;
        border-radius: 16px;
        overflow: hidden;
    }
    
    .medical-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .gradient-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .progress-ring {
        transform: rotate(-90deg);
    }
    
    .progress-ring-circle {
        transition: stroke-dashoffset 0.5s ease;
        transform-origin: 50% 50%;
    }
    
    .floating-action {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 50;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const progressRing = document.querySelector('.progress-ring-circle');
        if (progressRing) {
            // Animation is handled by CSS transition
        }
    });
</script>
@endsection