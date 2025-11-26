<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'student_id' => 'required|string|unique:users|max:20',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'course' => 'required|in:BSIT,BSBA-MM,BSBA-FM,BSED,BEED',
            'year_level' => 'required|in:1st year,2nd year,3rd year,4th year',
            'section' => 'nullable|string|max:10',
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'student_id.required' => 'Student ID is required.',
            'student_id.unique' => 'This Student ID is already registered.',
            'course.required' => 'Please select a course.',
            'course.in' => 'Please select a valid course.',
            'year_level.required' => 'Please select your year level.',
            'year_level.in' => 'Please select a valid year level.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'section.max' => 'Section must not exceed 10 characters.',
        ];
    }
}