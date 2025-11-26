{{-- resources/views/auth/change-password.blade.php --}}
@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="container mx-auto px-4 py-8 sm:py-12 min-h-screen flex items-center justify-center bg-gradient-to-b from-gray-50 to-gray-100">
    <div class="max-w-md w-full mx-auto bg-white rounded-3xl shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 py-6 px-8">
            <div class="flex items-center">
                <div class="p-2 bg-white/20 rounded-xl mr-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Change Your Password</h1>
                    <p class="text-sm text-blue-100 mt-1">Update your password to keep your account secure.</p>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mx-6 mt-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mx-6 mt-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <ul class="text-sm text-red-800 list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form Section -->
        <div class="p-6 sm:p-8">
            <form method="POST" action="{{ route('student.update-password') }}" class="space-y-6" aria-label="Change password form">
                @csrf
                
                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Current Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               required
                               autocomplete="current-password"
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('current_password') border-red-400 ring-2 ring-red-200 @enderror"
                               aria-describedby="current_password-error"
                               placeholder="Enter your current password">
                        <button type="button" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors"
                                onclick="togglePassword('current_password', this)" 
                                aria-label="Toggle current password visibility">
                            <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-red-500 text-xs mt-2 flex items-center" id="current_password-error">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                        New Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               required
                               autocomplete="new-password"
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('new_password') border-red-400 ring-2 ring-red-200 @enderror"
                               aria-describedby="new_password-error" 
                               oninput="checkPasswordStrength(this.value)"
                               placeholder="Create a strong password">
                        <button type="button" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors"
                                onclick="togglePassword('new_password', this)" 
                                aria-label="Toggle new password visibility">
                            <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <div id="password-strength" class="text-xs mt-2 text-gray-500"></div>
                    @error('new_password')
                        <p class="text-red-500 text-xs mt-2 flex items-center" id="new_password-error">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">
                        Password must be at least 8 characters with uppercase, lowercase, numbers, and symbols.
                    </p>
                </div>

                <!-- Confirm New Password -->
                <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm New Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="new_password_confirmation" 
                               name="new_password_confirmation" 
                               required
                               autocomplete="new-password"
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 hover:border-indigo-300 @error('new_password_confirmation') border-red-400 ring-2 ring-red-200 @enderror"
                               aria-describedby="new_password_confirmation-error"
                               placeholder="Confirm your new password">
                        <button type="button" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors"
                                onclick="togglePassword('new_password_confirmation', this)" 
                                aria-label="Toggle confirm password visibility">
                            <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @error('new_password_confirmation')
                        <p class="text-red-500 text-xs mt-2 flex items-center" id="new_password_confirmation-error">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col space-y-3">
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-3 px-6 rounded-lg transition duration-300 flex items-center justify-center"
                            id="submit-btn" 
                            aria-label="Submit password change">
                        <span id="submit-text">Change Password</span>
                        <svg class="w-5 h-5 ml-2 hidden animate-spin" id="submit-spinner" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                    
                    <a href="{{ route('student.dashboard') }}" 
                       class="w-full bg-gray-500 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 text-center flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Dashboard
                    </a>
                </div>
            </form>
        </div>

        <!-- Footer Info -->
        <div class="px-6 pb-6 text-center text-xs text-gray-500">
            <p>Your security is our priority. Use a strong, unique password.</p>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    
    // Cache DOM elements
    const elements = {
        strengthDiv: document.getElementById('password-strength'),
        submitBtn: document.getElementById('submit-btn'),
        submitText: document.getElementById('submit-text'),
        submitSpinner: document.getElementById('submit-spinner'),
        form: document.querySelector('form'),
        newPasswordInput: document.getElementById('new_password'),
        confirmPasswordInput: document.getElementById('new_password_confirmation')
    };
    
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('.eye-icon');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.125-3.175m3.55-2.65A10.05 10.05 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-2.125 3.175m-3.55 2.65M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
            `;
            button.setAttribute('aria-label', 'Hide password');
        } else {
            input.type = 'password';
            icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            `;
            button.setAttribute('aria-label', 'Show password');
        }
    }
    
    function checkPasswordStrength(password) {
        if (!elements.strengthDiv || !password) {
            if (elements.strengthDiv) {
                elements.strengthDiv.innerHTML = '<span class="text-gray-400">Password strength will appear here</span>';
            }
            return;
        }
        
        let strength = 'Weak';
        let color = 'text-red-500';
        let score = 0;
        
        // Length check
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        
        // Character variety checks
        if (/[A-Z]/.test(password)) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        if (score >= 4) {
            strength = 'Strong';
            color = 'text-green-500';
        } else if (score >= 3) {
            strength = 'Good';
            color = 'text-blue-500';
        } else if (score >= 2) {
            strength = 'Fair';
            color = 'text-yellow-500';
        }
        
        elements.strengthDiv.innerHTML = `
            <span class="font-medium">Password Strength:</span> 
            <span class="${color} font-semibold">${strength}</span>
            <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                <div class="bg-${color === 'text-green-500' ? 'green' : color === 'text-blue-500' ? 'blue' : color === 'text-yellow-500' ? 'yellow' : 'red'}-500 h-1 rounded-full transition-all duration-300" 
                     style="width: ${Math.min((score / 5) * 100, 100)}%"></div>
            </div>
        `;
    }
    
    // Confirm password matching check
    function checkPasswordMatch() {
        const newPass = elements.newPasswordInput?.value;
        const confirmPass = elements.confirmPasswordInput?.value;
        
        if (newPass && confirmPass && newPass !== confirmPass) {
            elements.confirmPasswordInput.classList.add('border-red-400', 'ring-2', 'ring-red-200');
            elements.confirmPasswordInput.classList.remove('border-gray-200', 'focus:border-indigo-500');
        } else {
            elements.confirmPasswordInput?.classList.remove('border-red-400', 'ring-2', 'ring-red-200');
            elements.confirmPasswordInput?.classList.add('border-gray-200', 'focus:border-indigo-500');
        }
    }
    
    // Form submission handler
    if (elements.form) {
        elements.form.addEventListener('submit', function(e) {
            // Basic client-side validation
            const currentPass = document.getElementById('current_password').value;
            const newPass = elements.newPasswordInput?.value;
            const confirmPass = elements.confirmPasswordInput?.value;
            
            if (!currentPass) {
                e.preventDefault();
                alert('Please enter your current password.');
                return false;
            }
            
            if (!newPass || newPass.length < 8) {
                e.preventDefault();
                alert('New password must be at least 8 characters long.');
                return false;
            }
            
            if (newPass !== confirmPass) {
                e.preventDefault();
                alert('Passwords do not match.');
                return false;
            }
            
            // Show loading state
            if (elements.submitBtn && elements.submitText && elements.submitSpinner) {
                elements.submitBtn.disabled = true;
                elements.submitText.classList.add('opacity-50');
                elements.submitSpinner.classList.remove('hidden');
                elements.submitBtn.innerHTML = 'Changing Password...';
            }
        });
    }
    
    // Event listeners
    if (elements.newPasswordInput) {
        elements.newPasswordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            checkPasswordMatch();
        });
    }
    
    if (elements.confirmPasswordInput) {
        elements.confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    }
    
    // Initialize password strength display
    if (elements.strengthDiv) {
        checkPasswordStrength('');
    }
    
    // Make functions globally available
    window.togglePassword = togglePassword;
    window.checkPasswordStrength = checkPasswordStrength;
})();
</script>

<style>
/* Custom styles for better visual feedback */
input[type="password"]:focus + button svg,
input[type="text"]:focus + button svg {
    color: theme('colors.indigo.600');
}

input.border-red-400 + button svg {
    color: theme('colors.red.500');
}

#password-strength {
    font-feature-settings: 'pnum' 1;
}
</style>
@endsection