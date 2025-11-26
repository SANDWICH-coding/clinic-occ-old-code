{{-- resources/views/dashboards/student.blade.php --}}
@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Dashboard Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome, {{ auth()->user()->full_name }}</h1>
        <p class="text-gray-500">Here's your academic and health overview</p>
    </div>

    <!-- Alert Notifications -->
    @if(auth()->user()->must_change_password || auth()->user()->needsYearLevelUpdate())
    <div class="mb-6 space-y-3">
        @if(auth()->user()->must_change_password)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-amber-900 mb-1">Password Update Required</p>
                    <p class="text-sm text-amber-700 mb-2">For security, please update your password</p>
                    <a href="{{ route('student.change-password') }}" class="inline-flex items-center gap-1 text-sm font-medium text-amber-700 hover:text-amber-800">
                        Change Password
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        @endif
        
        @if(auth()->user()->needsYearLevelUpdate())
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-blue-900 mb-1">Academic Year Update Needed</p>
                    <p class="text-sm text-blue-700 mb-2">Please update your academic information</p>
                    <a href="{{ route('student.academic-info') }}" class="inline-flex items-center gap-1 text-sm font-medium text-blue-700 hover:text-blue-800">
                        Update Information
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        @endif
    </div>
    @endif

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Profile Cards -->
        <div class="space-y-6">
            <!-- Student Profile Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 h-24"></div>
                <div class="px-6 pb-6">
                    <div class="-mt-12 mb-4">
                        <div class="h-20 w-20 rounded-2xl bg-white shadow-lg flex items-center justify-center border-4 border-white">
                            <span class="text-3xl font-bold text-blue-600">{{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}</span>
                        </div>
                    </div>
                    
                    <h2 class="text-xl font-bold text-gray-900 mb-1">{{ auth()->user()->full_name }}</h2>
                    <p class="text-sm text-gray-500 mb-4">{{ auth()->user()->student_id }}</p>
                    
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="text-gray-600 truncate">{{ auth()->user()->email }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span class="text-gray-600">{{ auth()->user()->formatted_phone }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-gray-600">{{ auth()->user()->age ? auth()->user()->age . ' years old' : 'Age not provided' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Information Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Academic Info</h3>
                    <a href="{{ route('student.academic-info') }}" class="text-blue-600 hover:text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </a>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Course</p>
                        <p class="font-semibold text-gray-900">
                            @php
                                $options = \App\Models\User::getCourseOptions();
                                echo $options[auth()->user()->course] ?? 'Not specified';
                            @endphp
                        </p>
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Year & Section</p>
                        <p class="font-semibold text-gray-900">
                            {{ auth()->user()->year_level ? ucfirst(auth()->user()->year_level) : 'Not specified' }}
                            {{ auth()->user()->section ? ' • Section ' . auth()->user()->section : '' }}
                        </p>
                    </div>
                    
                    @if(auth()->user()->academic_year)
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Academic Year</p>
                        <p class="font-semibold text-gray-900">
                            {{ auth()->user()->academic_year }}-{{ auth()->user()->academic_year + 1 }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Health Overview Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Health Overview</h3>
                    <a href="{{ route('student.medical-records.index') }}" class="text-blue-600 hover:text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
                
                @if(auth()->user()->medicalRecord)
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Blood Type</span>
                        <span class="font-semibold text-gray-900">{{ auth()->user()->medicalRecord->blood_type ?? 'Not recorded' }}</span>
                    </div>
                    
                    @if(auth()->user()->medicalRecord->height && auth()->user()->medicalRecord->weight)
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">BMI</span>
                        <div class="text-right">
                            <span class="font-semibold text-gray-900">{{ auth()->user()->medicalRecord->calculateBMI() }}</span>
                            @if(auth()->user()->medicalRecord->calculateBMI())
                            <span class="block text-xs text-gray-500">{{ auth()->user()->medicalRecord->getBMICategory() }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-600">Vaccination</span>
                        @if(auth()->user()->medicalRecord->is_fully_vaccinated)
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Fully Vaccinated
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-medium">
                                Incomplete
                            </span>
                        @endif
                    </div>
                </div>
                @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p class="text-gray-600 font-medium mb-1">No medical record</p>
                    <p class="text-sm text-gray-500">Visit the clinic to create your record</p>
                </div>
                @endif
            </div>
        </div>
 
        <!-- Right Column - Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Quick Actions Grid -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-bold text-gray-900 mb-6">Quick Actions</h3>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Medical Records Card - ADD THIS -->
        <a href="{{ route('student.medical-records.index') }}" class="group relative overflow-hidden rounded-xl border-2 border-blue-100 hover:border-blue-300 p-5 transition-all hover:shadow-md">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-gray-900 mb-1">Medical Records</h4>
                    <p class="text-sm text-gray-600">View health history</p>
                </div>
            </div>
        </a>
        
        <a href="{{ route('student.appointments.index') }}" class="group relative overflow-hidden rounded-xl border-2 border-purple-100 hover:border-purple-300 p-5 transition-all hover:shadow-md">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-gray-900 mb-1">Appointments</h4>
                    <p class="text-sm text-gray-600">Schedule visits</p>
                </div>
            </div>
        </a>
        
        <a href="{{ route('student.consultations.index') }}" class="group relative overflow-hidden rounded-xl border-2 border-indigo-100 hover:border-indigo-300 p-5 transition-all hover:shadow-md">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-gray-900 mb-1">My Consultations</h4>
                    <p class="text-sm text-gray-600">View consultations</p>
                </div>
            </div>
        </a>
        
        
        
        <a href="{{ route('student.symptom-logs.index') }}" class="group relative overflow-hidden rounded-xl border-2 border-orange-100 hover:border-orange-300 p-5 transition-all hover:shadow-md">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-gray-900 mb-1">Symptom history</h4>
                    <p class="text-sm text-gray-600">Track symptoms</p>
                </div>
            </div>
        </a>
    </div>
</div>
            <!-- Progress Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Academic Progress -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white">
                    <h3 class="font-bold text-lg mb-4 opacity-90">Academic Progress</h3>
                    
                    @if(auth()->user()->year_level)
                        @php
                            $yearLevels = ['1st year', '2nd year', '3rd year', '4th year'];
                            $currentIndex = array_search(auth()->user()->year_level, $yearLevels);
                            $progress = ($currentIndex + 1) / count($yearLevels) * 100;
                        @endphp
                        
                        <div class="flex items-center gap-6">
                            <div class="relative w-24 h-24">
                                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                    <circle cx="18" cy="18" r="16" fill="none" class="stroke-white opacity-20" stroke-width="3"></circle>
                                    <circle cx="18" cy="18" r="16" fill="none" class="stroke-white" stroke-width="3" stroke-dasharray="{{ $progress }}, 100" stroke-linecap="round"></circle>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-2xl font-bold">{{ number_format($progress, 0) }}%</span>
                                </div>
                            </div>
                            <div>
                                <p class="font-bold text-xl mb-1">{{ ucfirst(auth()->user()->year_level) }}</p>
                                <p class="text-sm opacity-90">{{ 4 - ($currentIndex + 1) }} years remaining</p>
                            </div>
                        </div>
                    @else
                        <p class="opacity-90">Academic level not set</p>
                    @endif
                </div>

                <!-- Health Status Card - REMOVED -->
                {{-- Health Status section has been removed as requested --}}
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-lg text-gray-900 mb-4">Recent Activity</h3>
                
                @forelse(auth()->user()->appointments()->orderBy('created_at', 'desc')->limit(5)->get() as $appointment)
                    <div class="flex items-start gap-3 py-3 border-b border-gray-100 last:border-0">
                        <div class="flex-shrink-0 w-2 h-2 mt-2 rounded-full bg-blue-500"></div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">Appointment {{ ucfirst($appointment->status) }}</p>
                            <p class="text-sm text-gray-500">{{ $appointment->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-gray-500 font-medium">No recent activity</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Floating Chat Button - Draggable -->
<div id="chatButton" class="fixed bottom-6 right-6 z-50 cursor-move">
    <a href="{{ route('chat.index') }}" 
       id="chatButtonLink"
       class="group flex items-center gap-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-full shadow-2xl hover:shadow-blue-300 transition-all duration-300 transform hover:scale-105 p-4">
        
        <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            
            <!-- Unread Badge -->
            <span id="chat-unread-badge" class="hidden absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                0
            </span>
            
            <!-- Pulse -->
            <span class="absolute -top-1 -right-1 flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-400"></span>
            </span>
        </div>
        
        <div class="pr-1 max-w-0 overflow-hidden group-hover:max-w-xs transition-all duration-300 whitespace-nowrap">
            <span class="font-semibold">Chat with Nurse</span>
        </div>
    </a>
</div>

@endsection

@push('scripts')
<script>
// Update chat unread count
function updateChatUnreadCount() {
    fetch('/api/chat/unread-count')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('chat-unread-badge');
            if (data.unread_count > 0) {
                badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        })
        .catch(error => console.error('Error fetching unread count:', error));
}

// Update every 30 seconds
updateChatUnreadCount();
setInterval(updateChatUnreadCount, 30000);
</script>
@endpush