@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="container mx-auto px-4 py-8 sm:py-12 min-h-screen flex items-center justify-center bg-gradient-to-b from-gray-50 to-gray-100">
    <div class="max-w-4xl w-full mx-auto bg-white rounded-3xl shadow-2xl overflow-hidden transition-all duration-500 animate-fade-in">
        <div class="flex flex-col lg:flex-row">
            <!-- Logo Section -->
            <div class="lg:w-1/2 flex flex-col items-center justify-center bg-gradient-to-r from-indigo-600 to-blue-600 p-8 lg:p-12">
                <a href="{{ url('/') }}" aria-label="Go to homepage">
                    <div class="relative">
                        <img src="https://scontent.fmnl13-4.fna.fbcdn.net/v/t39.30808-6/510989554_1291512086313864_1697600367017083003_n.jpg?_nc_cat=107&ccb=1-7&_nc_sid=6ee11a&_nc_ohc=mvFfUBM-k9EQ7kNvwEjttV0&_nc_oc=AdkTZ6gHs6e82Rebe2mTd9BqUbjJa6w8a978MXJsxRi_9GmGB_3K-vFOxy_Fot6qA-qoNWy3mFE0J16s7SFfZ5vf&_nc_zt=23&_nc_ht=scontent.fmnl13-4.fna&_nc_gid=GWM4j-IN5o5nsKJNAhaO1w&oh=00_AfiQT11yCbLwYxlCZtuujPdEH5tZaLe9rQ0aqQ51lh-hzA&oe=692A4E27"
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
                    <h2 class="text-3xl font-bold text-gray-900">Welcome Back</h2>
                    <p class="text-gray-600 mt-2">Sign in to your account</p>
                </div>
                
                <form method="POST" action="{{ route('login') }}" class="space-y-6" aria-label="Login form">
                    @csrf
                    
                    <!-- Email Field -->
                    <div class="animate-fade-in-up" style="animation-delay: 0.1s;">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
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

                    <!-- Password Field -->
                    <div class="animate-fade-in-up" style="animation-delay: 0.2s;">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required autocomplete="current-password"
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('password') border-red-400 @enderror"
                                   aria-describedby="password-error">
                            <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                                <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-2 flex items-center animate-error" id="password-error">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between animate-fade-in-up" style="animation-delay: 0.3s;">
                        <div class="flex items-center">
                            <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-200 rounded"
                                   aria-label="Remember me">
                            <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                        </div>
                        @if (Route::has('password.request'))
                            <!-- <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:underline">
                                Forgot password?
                            </a> -->
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div class="animate-fade-in-up" style="animation-delay: 0.4s;">
                        <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg transition duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transform hover:scale-105 flex items-center justify-center"
                                id="submit-btn" aria-label="Submit login">
                            <span id="submit-text">Sign In</span>
                            <svg class="w-5 h-5 ml-2 hidden animate-spin" id="submit-spinner" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Register Link -->
                    <div class="text-center animate-fade-in-up" style="animation-delay: 0.5s;">
                        <p class="text-sm text-gray-600">
                            Don't have an account?
                            <a href="{{ route('register') }}" class="text-indigo-600 hover:underline font-medium">Create account</a>
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
    
    .animate-fade-in { 
        animation: fadeIn 0.6s ease-out; 
    }
    
    .animate-fade-in-up { 
        animation: fadeInUp 0.6s ease-out; 
    }
    
    .animate-error { 
        animation: errorShake 0.3s ease-in-out; 
    }
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

    document.querySelector('form').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submit-btn');
        const submitText = document.getElementById('submit-text');
        const submitSpinner = document.getElementById('submit-spinner');
        
        // Prevent multiple submissions
        if (submitBtn.disabled) {
            e.preventDefault();
            return;
        }

        submitBtn.disabled = true;
        submitText.classList.add('opacity-50');
        submitSpinner.classList.remove('hidden');
    });
</script>
@endsection