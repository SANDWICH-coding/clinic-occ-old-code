<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->must_change_password && $user->isStudent()) {
                return redirect()->route('student.change-password')
                    ->with('warning', 'You must change your password before proceeding.');
            }

            return $this->authenticated($request, $user);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->isStudent()) {
            return redirect()->route('student.dashboard'); // ← CORRECT
        } elseif ($user->isNurse()) {
            return redirect()->route('nurse.dashboard'); // ← CORRECT
        } elseif ($user->isDean()) {
            return redirect()->route('dean.dashboard'); // ← CORRECT
        }

        return redirect('/');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $currentAcademicYear = $this->getCurrentAcademicYear();
        
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
            'student_id' => $request->student_id,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'address' => $request->address,
            'course' => $request->course,
            'year_level' => $request->year_level,
            'section' => $request->section,
            'academic_year' => $currentAcademicYear,
            'year_level_updated_at' => now(),
            'must_change_password' => false,
        ]);

        Auth::login($user);

        // FIXED: Changed from 'dashboard.student' to 'student.dashboard'
        return redirect()->route('student.dashboard')
            ->with('success', 'Registration successful! Welcome to the Digital Health Records System.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Get current academic year based on current date
     */
    private function getCurrentAcademicYear()
    {
        $now = now();
        // Academic year typically starts in August/September
        // If current month is >= August, it's the new academic year
        if ($now->month >= 8) {
            return $now->year;
        } else {
            return $now->year - 1;
        }
    }
}