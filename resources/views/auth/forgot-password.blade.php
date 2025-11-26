@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Forgot Your Password?</h1>
    
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        
        <div class="mb-4">
            <p class="text-gray-600 mb-4">Enter your email address and we'll send you a link to reset your password.</p>
            
            <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-4">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                Send Reset Link
            </button>
        </div>
        
        <div class="text-center">
            <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">Back to login</a>
        </div>
    </form>
</div>
@endsection