<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->full_name }} - Student Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg mb-6">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">Student Profile</h1>
                <div class="flex space-x-4">
                    <a href="{{ route('nurse.dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    <a href="{{ route('nurse.students.search') }}" class="text-gray-600 hover:text-gray-900">Back to Students</a>
                    <a href="{{ route('chat.index') }}" class="text-blue-600 hover:text-blue-900">Back to Chat</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-6">
        <!-- Student Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-4">
                    <div class="h-20 w-20 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-2xl font-bold">
                        {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900">{{ $user->full_name }}</h2>
                        <p class="text-gray-600">Student ID: {{ $user->student_id }}</p>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                </div>
                <div class="text-right">
                    @if($medicalRecord)
                        <a href="{{ route('nurse.medical-records.show', $medicalRecord->id) }}" 
                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            View Full Medical Record
                        </a>
                    @else
                        <a href="{{ route('nurse.medical-records.create-for', $user->id) }}" 
                           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Create Medical Record
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        @if($medicalRecord)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 mb-1">Health Risk Level</div>
                <div class="text-2xl font-bold {{ $stats['risk_level'] == 'High' ? 'text-red-600' : ($stats['risk_level'] == 'Medium' ? 'text-yellow-600' : 'text-green-600') }}">
                    {{ $stats['risk_level'] }}
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 mb-1">Record Completion</div>
                <div class="text-2xl font-bold text-blue-600">{{ $stats['completion_rate'] }}%</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 mb-1">BMI</div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ $stats['bmi'] ? number_format($stats['bmi'], 1) : 'N/A' }}
                </div>
                <div class="text-xs text-gray-500">{{ $stats['bmi_category'] ?? '' }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 mb-1">Last Updated</div>
                <div class="text-sm font-semibold text-gray-900">
                    {{ $stats['last_updated']->format('M d, Y') }}
                </div>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Academic Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Academic Information</h3>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Course:</span>
                        <span class="font-semibold">{{ $user->course ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Year Level:</span>
                        <span class="font-semibold">{{ $user->year_level ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Section:</span>
                        <span class="font-semibold">{{ $user->section ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Phone:</span>
                        <span class="font-semibold">{{ $user->phone ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Medical Summary -->
            @if($medicalRecord)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Medical Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Blood Type:</span>
                        <span class="font-semibold">{{ $medicalRecord->blood_type ?? 'Not recorded' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Height:</span>
                        <span class="font-semibold">{{ $medicalRecord->height ? $medicalRecord->height . ' cm' : 'Not recorded' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Weight:</span>
                        <span class="font-semibold">{{ $medicalRecord->weight ? $medicalRecord->weight . ' kg' : 'Not recorded' }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Vaccination Status:</span>
                        <span class="font-semibold {{ $medicalRecord->is_fully_vaccinated ? 'text-green-600' : 'text-red-600' }}">
                            {{ $medicalRecord->is_fully_vaccinated ? 'Fully Vaccinated' : 'Not Fully Vaccinated' }}
                        </span>
                    </div>
                </div>
            </div>
            @endif
        </div>

        @if($medicalRecord)
        <!-- Health Alerts -->
        <div class="mt-6 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Health Alerts & Conditions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($medicalRecord->allergies)
                <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-red-800">Allergies</p>
                            <p class="text-sm text-red-700">{{ $medicalRecord->allergies }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($medicalRecord->is_pwd)
                <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-blue-800">Person with Disability</p>
                            <p class="text-sm text-blue-700">{{ $medicalRecord->pwd_disability_details ?? 'PWD registered' }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($medicalRecord->is_taking_maintenance_drugs)
                <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-yellow-800">Maintenance Medication</p>
                            <p class="text-sm text-yellow-700">{{ $medicalRecord->maintenance_drugs_specify ?? 'On maintenance drugs' }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Emergency Contacts -->
        <div class="mt-6 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Emergency Contacts</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($medicalRecord->emergency_contact_name_1)
                <div class="border-l-4 border-red-500 pl-4">
                    <p class="text-sm text-gray-600">Primary Contact</p>
                    <p class="font-bold text-lg">{{ $medicalRecord->emergency_contact_name_1 }}</p>
                    <p class="text-gray-700">{{ $medicalRecord->emergency_contact_number_1 }}</p>
                    <p class="text-sm text-gray-500">{{ $medicalRecord->emergency_contact_relationship_1 }}</p>
                </div>
                @endif

                @if($medicalRecord->emergency_contact_name_2)
                <div class="border-l-4 border-orange-500 pl-4">
                    <p class="text-sm text-gray-600">Secondary Contact</p>
                    <p class="font-bold text-lg">{{ $medicalRecord->emergency_contact_name_2 }}</p>
                    <p class="text-gray-700">{{ $medicalRecord->emergency_contact_number_2 }}</p>
                    <p class="text-sm text-gray-500">{{ $medicalRecord->emergency_contact_relationship_2 }}</p>
                </div>
                @endif
            </div>
        </div>
        @else
        <!-- No Medical Record -->
        <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded">
            <div class="flex items-center">
                <svg class="h-6 w-6 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                </svg>
                <div>
                    <p class="font-bold text-yellow-800">No Medical Record Found</p>
                    <p class="text-sm text-yellow-700 mt-1">This student doesn't have a medical record yet. You can create one to start tracking their health information.</p>
                    <a href="{{ route('nurse.medical-records.create-for', $user->id) }}" 
                       class="inline-block mt-3 px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                        Create Medical Record Now
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</body>
</html>