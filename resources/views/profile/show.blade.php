@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Student Profile</h1>
        <p class="text-gray-500 mt-2">Manage your personal information and account settings</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
        <nav class="flex overflow-x-auto" aria-label="Profile sections">
            <button onclick="showTab('personal')" class="tab-button active flex-1 min-w-max py-4 px-6 font-semibold text-sm transition-all duration-200" data-tab="personal">
                <div class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Personal Details</span>
                </div>
            </button>
            <button onclick="showTab('academic')" class="tab-button flex-1 min-w-max py-4 px-6 font-semibold text-sm transition-all duration-200" data-tab="academic">
                <div class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <span>Academic Info</span>
                </div>
            </button>
            <button onclick="showTab('security')" class="tab-button flex-1 min-w-max py-4 px-6 font-semibold text-sm transition-all duration-200" data-tab="security">
                <div class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span>Security</span>
                </div>
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="tab-content-wrapper">
        <!-- Personal Details Tab -->
        <div id="personal-tab" class="tab-content active">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Profile Header -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 h-32 relative">
                    <div class="absolute -bottom-12 left-6">
                        <div class="h-24 w-24 rounded-2xl bg-white shadow-lg flex items-center justify-center border-4 border-white">
                            <span class="text-4xl font-bold text-blue-600">{{ strtoupper(substr($user->first_name, 0, 1)) }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-16 px-6 pb-6">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $user->full_name }}</h2>
                        <p class="text-gray-500 font-mono text-sm">{{ $user->student_id ?? 'N/A' }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- First Name -->
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">First Name</label>
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <p class="text-gray-900 font-medium">{{ $user->first_name }}</p>
                            </div>
                        </div>

                        <!-- Last Name -->
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Last Name</label>
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <p class="text-gray-900 font-medium">{{ $user->last_name }}</p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Email Address</label>
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <p class="text-gray-900 font-medium break-all">{{ $user->email }}</p>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Phone Number</label>
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <p class="text-gray-900 font-medium">{{ $user->phone ?? 'Not provided' }}</p>
                            </div>
                        </div>

                        <!-- Date of Birth -->
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Date of Birth</label>
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <p class="text-gray-900 font-medium">
                                    {{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('F d, Y') : 'Not provided' }}
                                </p>
                            </div>
                        </div>

                        <!-- Age -->
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Age</label>
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <p class="text-gray-900 font-medium">
                                    {{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->age . ' years old' : 'Not provided' }}
                                </p>
                            </div>
                        </div>

                        <!-- Gender -->
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Gender</label>
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <p class="text-gray-900 font-medium">{{ ucfirst($user->gender ?? 'Not specified') }}</p>
                            </div>
                        </div>

                        <!-- Course -->
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Course</label>
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <p class="text-gray-900 font-medium">
                                    @if($user->course)
                                        @php
                                            $courseLabels = [
                                                'BSIT' => 'BS Information Technology',
                                                'BSBA-MM' => 'BS Business Admin - Marketing Management',
                                                'BSBA-FM' => 'BS Business Admin - Financial Management',
                                                'BSED' => 'BS Secondary Education',
                                                'BEED' => 'BS Elementary Education'
                                            ];
                                        @endphp
                                        {{ $courseLabels[$user->course] ?? $user->course }}
                                    @else
                                        Not assigned
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Year Level -->
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Year Level</label>
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                @if($user->year_level)
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold bg-indigo-100 text-indigo-700">
                                        {{ ucfirst($user->year_level) }}
                                    </span>
                                @else
                                    <p class="text-gray-900 font-medium">Not assigned</p>
                                @endif
                            </div>
                        </div>

                        <!-- Section -->
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Section</label>
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <p class="text-gray-900 font-medium">
                                    {{ $user->section ? 'Section ' . $user->section : 'Not assigned' }}
                                </p>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Address</label>
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <p class="text-gray-900 font-medium">{{ $user->address ?? 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Info Tab -->
        <div id="academic-tab" class="tab-content hidden">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Academic Information</h2>
                    <a href="{{ route('student.academic-info') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-semibold transition-colors shadow-sm hover:shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Update Info
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Department -->
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Department</label>
                        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                            @if($user->department)
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold bg-purple-100 text-purple-700">
                                    {{ $user->department }}
                                </span>
                            @else
                                <p class="text-gray-900 font-medium">Not assigned</p>
                            @endif
                        </div>
                    </div>

                    <!-- Course Program -->
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Course Program</label>
                        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                            <p class="text-gray-900 font-medium">
                                @if($user->course)
                                    @php
                                        $courseLabels = [
                                            'BSIT' => 'BS Information Technology',
                                            'BSBA-MM' => 'BS Business Admin - Marketing Management',
                                            'BSBA-FM' => 'BS Business Admin - Financial Management',
                                            'BSED' => 'BS Secondary Education',
                                            'BEED' => 'BS Elementary Education'
                                        ];
                                    @endphp
                                    {{ $courseLabels[$user->course] ?? $user->course }}
                                @else
                                    Not assigned
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Year Level -->
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Year Level</label>
                        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                            @if($user->year_level)
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold bg-indigo-100 text-indigo-700">
                                    {{ ucfirst($user->year_level) }}
                                </span>
                            @else
                                <p class="text-gray-900 font-medium">Not assigned</p>
                            @endif
                        </div>
                    </div>

                    <!-- Section -->
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Section</label>
                        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                            <p class="text-gray-900 font-medium">
                                {{ $user->section ? 'Section ' . $user->section : 'Not assigned' }}
                            </p>
                        </div>
                    </div>

                    <!-- Academic Year -->
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Academic Year</label>
                        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                            <p class="text-gray-900 font-medium">
                                @if($user->academic_year)
                                    {{ $user->academic_year }}-{{ $user->academic_year + 1 }}
                                @else
                                    Not set
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Enrollment Status -->
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Enrollment Status</label>
                        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-sm font-semibold bg-green-100 text-green-700">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Active
                            </span>
                        </div>
                    </div>

                    @if($user->year_level_updated_at)
                    <!-- Last Updated -->
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Academic Info Last Updated</label>
                        <div class="bg-blue-50 rounded-xl px-4 py-3 border border-blue-100">
                            <p class="text-blue-900 font-medium flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $user->year_level_updated_at->format('F d, Y \a\t g:i A') }}
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Security Tab -->
        <div id="security-tab" class="tab-content hidden">
            <div class="space-y-6">
                <!-- Password Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 mb-2">Password</h2>
                            <p class="text-gray-600">Update your password regularly to keep your account secure</p>
                        </div>
                    </div>

                    @if($user->password_changed_at)
                    <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100 mb-4">
                        <p class="text-sm text-gray-600 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Last changed: {{ \Carbon\Carbon::parse($user->password_changed_at)->format('F d, Y') }}
                        </p>
                    </div>
                    @endif

                    @if($user->must_change_password)
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-amber-900 mb-1">Password Update Required</p>
                                <p class="text-sm text-amber-700">You are required to change your password for security purposes</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <a href="{{ route('student.change-password') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-semibold transition-colors shadow-sm hover:shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        Change Password
                    </a>
                </div>

                <!-- Email Verification -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Email Verification</h2>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-900 font-medium mb-2">{{ $user->email }}</p>
                            @if($user->email_verified_at)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-sm font-semibold bg-green-100 text-green-700">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Verified on {{ \Carbon\Carbon::parse($user->email_verified_at)->format('M d, Y') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-sm font-semibold bg-red-100 text-red-700">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    Not Verified
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Account Activity -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Account Activity</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-blue-700 font-medium mb-1">Account Created</p>
                                    <p class="text-blue-900 font-bold">{{ $user->created_at->format('F d, Y') }}</p>
                                    <p class="text-xs text-blue-600 mt-1">{{ $user->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-purple-700 font-medium mb-1">Profile Updated</p>
                                    <p class="text-purple-900 font-bold">{{ $user->updated_at->format('F d, Y') }}</p>
                                    <p class="text-xs text-purple-600 mt-1">{{ $user->updated_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
        tab.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    
    // Show selected tab
    const selectedTab = document.getElementById(`${tabName}-tab`);
    if (selectedTab) {
        selectedTab.classList.remove('hidden');
        selectedTab.classList.add('active');
    }
    
    // Add active class to clicked button
    const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
    if (activeButton) {
        activeButton.classList.add('active');
    }
}

// Initialize first tab as active
document.addEventListener('DOMContentLoaded', function() {
    showTab('personal');
});
</script>

<style>
.tab-button {
    position: relative;
    color: #6b7280;
    background-color: transparent;
    transition: all 0.2s ease;
}

.tab-button::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: transparent;
    transition: all 0.2s ease;
}

.tab-button:hover {
    color: #374151;
    background-color: #f9fafb;
}

.tab-button.active {
    color: #2563eb;
    background-color: #eff6ff;
}

.tab-button.active::after {
    background: linear-gradient(to right, #3b82f6, #2563eb);
}

.tab-content {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endsection