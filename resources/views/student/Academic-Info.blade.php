@extends('layouts.app')

@section('title', 'Update Academic Information')

@section('content')
<div class="container mx-auto px-4 py-8 sm:py-12 min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-2xl w-full mx-auto bg-white rounded-2xl shadow-xl p-6 sm:p-8 transition-all duration-300 animate-fade-in">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Update Academic Information</h1>
            <a href="{{ route('student.dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center transition-colors duration-300" aria-label="Go back to dashboard">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Go Back
            </a>
        </div>

        <!-- SUCCESS/ERROR MESSAGES -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center animate-fade-in">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
                <button type="button" class="ml-auto text-green-600 hover:text-green-800" onclick="this.parentElement.style.display='none'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center animate-fade-in">
                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-red-800 font-medium">{{ session('error') }}</span>
                <button type="button" class="ml-auto text-red-600 hover:text-red-800" onclick="this.parentElement.style.display='none'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg animate-fade-in">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-red-800 font-medium">Please fix the following errors:</span>
                </div>
                <ul class="list-disc list-inside text-red-700 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Current Information Section -->
        <div class="bg-gray-100 rounded-xl p-6 mb-8 transition-all duration-300 animate-fade-in-up">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Current Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm text-gray-700">
                @php
                    $courseOptions = \App\Models\User::getCourseOptions();
                    $courseCode = auth()->user()->course;
                    $courseLabel = array_key_exists($courseCode, $courseOptions) ? $courseOptions[$courseCode] : ($courseCode ?: 'Not set');
                @endphp
                <div>
                    <span class="font-medium text-gray-800">Course:</span> {{ $courseLabel }}
                </div>
                <div>
                    <span class="font-medium text-gray-800">Year Level:</span> {{ auth()->user()->year_level ? ucfirst(auth()->user()->year_level) : 'Not set' }}
                </div>
                <div>
                    <span class="font-medium text-gray-800">Section:</span> {{ auth()->user()->section ? 'Section ' . auth()->user()->section : 'Not assigned' }}
                </div>
                <div>
                    <span class="font-medium text-gray-800">Academic Year:</span>
                    @if(auth()->user()->academic_year)
                        {{ auth()->user()->academic_year }}-{{ auth()->user()->academic_year + 1 }}
                    @else
                        Not set
                    @endif
                </div>
            </div>
            @if(auth()->user()->year_level_updated_at)
                <p class="text-xs text-gray-500 mt-4">
                    Last updated: {{ auth()->user()->year_level_updated_at->format('M j, Y') }}
                </p>
            @endif
        </div>

        <!-- Update Form -->
        <div class="border border-gray-200 rounded-xl p-6 mb-8">
            <h3 class="text-xl font-semibold text-gray-900 mb-6">Update Your Information</h3>
            <form method="POST" action="{{ route('student.academic-info.update') }}" class="space-y-6" aria-label="Update academic information form">
                @csrf
                <div>
                    <label for="course" class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                    <select id="course" name="course" required class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300 hover:border-blue-400" aria-describedby="course-error">
                        @foreach(App\Models\User::getCourseOptions() as $value => $label)
                            <option value="{{ $value }}" {{ auth()->user()->course === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('course')
                        <p class="text-red-500 text-xs mt-2 flex items-center" id="course-error">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="year_level" class="block text-sm font-medium text-gray-700 mb-2">Year Level</label>
                    <select id="year_level" name="year_level" required class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300 hover:border-blue-400" aria-describedby="year_level-error">
                        @foreach(App\Models\User::getYearLevelOptions() as $value => $label)
                            <option value="{{ $value }}" {{ auth()->user()->year_level == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('year_level')
                        <p class="text-red-500 text-xs mt-2 flex items-center" id="year_level-error">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="section" class="block text-sm font-medium text-gray-700 mb-2">Section</label>
                    <input type="text" id="section" name="section" value="{{ auth()->user()->section }}" placeholder="e.g., A, B, C, 1, 2"
                           class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300 hover:border-blue-400" aria-describedby="section-error">
                    @error('section')
                        <p class="text-red-500 text-xs mt-2 flex items-center" id="section-error">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105">
                    Update Information
                </button>
            </form>
        </div>

        <!-- Footer Navigation -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 text-sm">
            <a href="{{ route('student.dashboard') }}" 
               class="w-full sm:w-auto bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition duration-300 text-center transform hover:scale-105" 
               aria-label="Back to student dashboard">
                ← Back to Dashboard
            </a>
            <p class="text-gray-500">Need help? <a href="mailto:registrar@school.edu" class="text-blue-600 hover:underline">Contact the registrar's office</a>.</p>
        </div>
    </div>
</div>

<!-- Add JavaScript for auto-hiding messages -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide success/error messages after 5 seconds
        const messages = document.querySelectorAll('[class*="bg-green-50"], [class*="bg-red-50"]');
        messages.forEach(message => {
            setTimeout(() => {
                message.style.opacity = '0';
                message.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    if (message.parentNode) {
                        message.remove();
                    }
                }, 500);
            }, 5000);
        });
    });
</script>

<style>
    /* Reduced motion for performance */
    @media (max-width: 768px) {
        .transform, .hover\:scale-105:hover {
            transform: none !important;
        }
        
        .transition, .duration-300 {
            transition: none !important;
        }
    }
    
    /* GPU acceleration */
    .animate-fade-in,
    .animate-fade-in-up {
        will-change: transform, opacity;
        backface-visibility: hidden;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateZ(0); }
        to { opacity: 1; transform: translateZ(0); }
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px) translateZ(0); }
        to { opacity: 1; transform: translateY(0) translateZ(0); }
    }
    
    .animate-fade-in { animation: fadeIn 0.3s ease-out; }
    .animate-fade-in-up { animation: fadeInUp 0.3s ease-out; }
</style>
@endsection