@extends('layouts.app')

@section('title', 'Student Registration')

@section('content')
<div class="container mx-auto px-4 py-8 sm:py-12 min-h-screen flex items-center justify-center bg-gradient-to-b from-gray-50 to-gray-100">
    <div class="max-w-4xl w-full mx-auto bg-white rounded-3xl shadow-2xl overflow-hidden transition-all duration-500 animate-fade-in">
        <div class="flex flex-col lg:flex-row">
            <!-- Logo Section -->
            <div class="lg:w-1/2 flex flex-col items-center justify-center bg-gradient-to-r from-indigo-600 to-blue-600 p-8 lg:p-12">
                <a href="{{ url('/') }}" aria-label="Go to homepage">
                    <div class="relative">
                        <img src="https://scontent.fmnl13-4.fna.fbcdn.net/v/t39.30808-6/510989554_1291512086313864_1697600367017083003_n.jpg?_nc_cat=107&ccb=1-7&_nc_sid=6ee11a&_nc_ohc=ufmPlgigja8Q7kNvwFvg5_p&_nc_oc=AdkVGda15Irn9ds6nkgslNmKCetlzlny0VdM-oCiXljsPuA_WwSVFdsUHElwWJ4z75CCUtqqr17hUsVOILYkX4js&_nc_zt=23&_nc_ht=scontent.fmnl13-4.fna&_nc_gid=IyES0_5ro_a-G226jK3uEw&oh=00_Afc92WGc1iQFxcshYjNaH9_qKTAmLIotAMwGl8u67osuLQ&oe=68F397E7"
                             alt="Opol Community College Logo" 
                             class="h-40 w-40 lg:h-48 lg:w-48 rounded-full object-cover border-4 border-white shadow-lg hover:scale-105 transition-transform duration-300"
                             aria-hidden="true">
                        <!-- Optional: Add a subtle glow effect -->
                        <div class="absolute inset-0 rounded-full border-2 border-blue-200 opacity-50 animate-pulse"></div>
                    </div>
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white text-center mt-6">OPOL COMMUNITY COLLEGE</h1>
                <p class="text-blue-100 text-sm mt-2 text-center">Health Management System</p>
            </div>

            <!-- Form Section -->
            <div class="lg:w-1/2 p-6 sm:p-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Create Account</h2>
                    <p class="text-gray-600 mt-2">Join our health management system</p>
                </div>
                
                <form method="POST" action="{{ route('register') }}" class="space-y-6" aria-label="Student registration form">
                    @csrf
                    <!-- First & Last Name -->
                    <div class="grid md:grid-cols-2 gap-4 animate-fade-in-up" style="animation-delay: 0.1s;">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('first_name') border-red-400 @enderror"
                                   aria-describedby="first_name-error">
                            @error('first_name')
                                <p class="text-red-500 text-xs mt-2 flex items-center animate-error" id="first_name-error">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('last_name') border-red-400 @enderror"
                                   aria-describedby="last_name-error">
                            @error('last_name')
                                <p class="text-red-500 text-xs mt-2 flex items-center animate-error" id="last_name-error">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="animate-fade-in-up" style="animation-delay: 0.2s;">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('email') border-red-400 @enderror"
                               aria-describedby="email-error">
                        @error('email')
                            <p class="text-red-500 text-xs mt-2 flex items-center animate-error" id="email-error">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Password & Confirmation -->
                    <div class="grid md:grid-cols-2 gap-4 animate-fade-in-up" style="animation-delay: 0.3s;">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required
                                       class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('password') border-red-400 @enderror"
                                       aria-describedby="password-error" oninput="checkPasswordStrength(this.value)">
                                <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                                    <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                            <div id="password-strength" class="text-xs mt-2 text-gray-500"></div>
                            @error('password')
                                <p class="text-red-500 text-xs mt-2 flex items-center animate-error" id="password-error">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" required
                                       class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300"
                                       aria-describedby="password_confirmation-error">
                                <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700" onclick="togglePassword('password_confirmation', this)" aria-label="Toggle confirm password visibility">
                                    <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Student ID -->
                    <div class="animate-fade-in-up" style="animation-delay: 0.4s;">
                        <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Student ID</label>
                        <input type="text" id="student_id" name="student_id" value="{{ old('student_id') }}" required
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('student_id') border-red-400 @enderror"
                               aria-describedby="student_id-error">
                        @error('student_id')
                            <p class="text-red-500 text-xs mt-2 flex items-center animate-error" id="student_id-error">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $message }}
                                </p>
                        @enderror
                    </div>

                    <!-- Phone & DOB -->
                    <div class="grid md:grid-cols-2 gap-4 animate-fade-in-up" style="animation-delay: 0.5s;">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                                   placeholder="e.g., +639123456789"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('phone') border-red-400 @enderror"
                                   aria-describedby="phone-error">
                            @error('phone')
                                <p class="text-red-500 text-xs mt-2 flex items-center animate-error" id="phone-error">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        <div>
                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('date_of_birth') border-red-400 @enderror"
                                   aria-describedby="date_of_birth-error">
                            @error('date_of_birth')
                                <p class="text-red-500 text-xs mt-2 flex items-center animate-error" id="date_of_birth-error">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Gender & Course -->
                    <div class="grid md:grid-cols-2 gap-4 animate-fade-in-up" style="animation-delay: 0.6s;">
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                            <select id="gender" name="gender"
                                    class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('gender') border-red-400 @enderror"
                                    aria-describedby="gender-error">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender')
                                <p class="text-red-500 text-xs mt-2 flex items-center animate-error" id="gender-error">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        <div>
                            <label for="course" class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                            <select id="course" name="course" required
                                    class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('course') border-red-400 @enderror"
                                    aria-describedby="course-error">
                                <option value="">Select Course</option>
                                <option value="BSIT" {{ old('course') == 'BSIT' ? 'selected' : '' }}>BSIT — Information Technology</option>
                                <option value="BSBA-MM" {{ old('course') == 'BSBA-MM' ? 'selected' : '' }}>BSBA (MM) — Marketing Management</option>
                                <option value="BSBA-FM" {{ old('course') == 'BSBA-FM' ? 'selected' : '' }}>BSBA (FM) — Finance Management</option>
                                <option value="BSED" {{ old('course') == 'BSED' ? 'selected' : '' }}>BSED — Secondary Education (High School)</option>
                                <option value="BEED" {{ old('course') == 'BEED' ? 'selected' : '' }}>BEED — Elementary Education</option>
                            </select>
                            @error('course')
                                <p class="text-red-500 text-xs mt-2 flex items-center animate-error" id="course-error">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Year Level & Section -->
                    <div class="grid md:grid-cols-2 gap-4 animate-fade-in-up" style="animation-delay: 0.7s;">
                        <div>
                            <label for="year_level" class="block text-sm font-medium text-gray-700 mb-2">Year Level</label>
                            <select id="year_level" name="year_level" required
                                    class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('year_level') border-red-400 @enderror"
                                    aria-describedby="year_level-error">
                                <option value="">Select Year Level</option>
                                <option value="1st year" {{ old('year_level') == '1st year' ? 'selected' : '' }}>1st Year</option>
                                <option value="2nd year" {{ old('year_level') == '2nd year' ? 'selected' : '' }}>2nd Year</option>
                                <option value="3rd year" {{ old('year_level') == '3rd year' ? 'selected' : '' }}>3rd Year</option>
                                <option value="4th year" {{ old('year_level') == '4th year' ? 'selected' : '' }}>4th Year</option>
                            </select>
                            @error('year_level')
                                <p class="text-red-500 text-xs mt-2 flex items-center animate-error" id="year_level-error">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        <div>
                            <label for="section" class="block text-sm font-medium text-gray-700 mb-2">Section</label>
                            <input type="text" id="section" name="section" value="{{ old('section') }}"
                                   placeholder="e.g., A, B, C, 1, 2"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('section') border-red-400 @enderror"
                                   aria-describedby="section-error">
                            @error('section')
                                <p class="text-red-500 text-xs mt-2 flex items-center animate-error" id="section-error">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="animate-fade-in-up" style="animation-delay: 0.8s;">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea id="address" name="address" rows="3"
                                  class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('address') border-red-400 @enderror"
                                  aria-describedby="address-error">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="text-red-500 text-xs mt-2 flex items-center animate-error" id="address-error">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="animate-fade-in-up" style="animation-delay: 0.9s;">
                        <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg transition duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transform hover:scale-105 flex items-center justify-center"
                                id="submit-btn" aria-label="Submit registration">
                            <span id="submit-text">Create Account</span>
                            <svg class="w-5 h-5 ml-2 hidden animate-spin" id="submit-spinner" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Already have account -->
                    <div class="text-center animate-fade-in-up" style="animation-delay: 1.0s;">
                        <p class="text-sm text-gray-600">
                            Already have an account?
                            <a href="{{ route('login') }}" class="text-indigo-600 hover:underline font-medium">Sign in here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes errorShake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    .animate-fade-in { animation: fadeIn 0.6s ease-out; }
    .animate-fade-in-up { animation: fadeInUp 0.6s ease-out; }
    .animate-error { animation: errorShake 0.3s ease-in-out; }
</style>

<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('.eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.125-3.175m3.55-2.65A10.05 10.05 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-2.125 3.175m-3.55 2.65M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
        }
    }

    function checkPasswordStrength(password) {
        const strengthDiv = document.getElementById('password-strength');
        let strength = 'Weak';
        let color = 'text-red-500';
        if (password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) {
            strength = 'Strong';
            color = 'text-green-500';
        } else if (password.length >= 6) {
            strength = 'Moderate';
            color = 'text-yellow-500';
        }
        strengthDiv.innerHTML = password ? `Password Strength: <span class="${color}">${strength}</span>` : '';
    }

    document.querySelector('form').addEventListener('submit', function() {
        const submitBtn = document.getElementById('submit-btn');
        const submitText = document.getElementById('submit-text');
        const submitSpinner = document.getElementById('submit-spinner');
        submitBtn.disabled = true;
        submitText.classList.add('opacity-50');
        submitSpinner.classList.remove('hidden');
        setTimeout(() => {
            submitBtn.disabled = false;
            submitText.classList.remove('opacity-50');
            submitSpinner.classList.add('hidden');
        }, 2000); // Simulate submission delay
    });
</script>
@endsection