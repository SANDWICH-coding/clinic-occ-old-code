<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function showChangePasswordForm()
    {
        // Use your existing view path
        return view('student.change-password');
    }

    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $user = Auth::user();

        // Double-check current password (Form Request should handle this, but extra security)
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);

        // Log the password change
        \Log::info('Password changed for user: ' . $user->id . ' (' . $user->email . ') by ' . $user->role);

        // Role-based redirection - Fixed route names
        $redirectRoute = match($user->role) {
            'student' => 'student.dashboard',
            'nurse' => 'nurse.dashboard',
            'dean' => 'dean.dashboard',
            default => 'dashboard'
        };

        return redirect()->route($redirectRoute)
            ->with('success', 'Password changed successfully!');
    }
}