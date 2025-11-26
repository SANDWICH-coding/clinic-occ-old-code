<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Appointment;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Students can create appointments for themselves
        // Nurses and deans can create appointments for any user
        $user = $this->user();
        
        if (!$user) {
            return false;
        }

        // If user is student, they can only create appointments for themselves
        if ($user->isStudent()) {
            return !$this->has('user_id') || $this->input('user_id') == $user->id;
        }

        // Nurses and deans can create appointments for anyone
        return $user->isNurse() || $user->isDean();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    // If current user is student, they can only create for themselves
                    if ($this->user()->isStudent() && $value != $this->user()->id) {
                        $fail('You can only create appointments for yourself.');
                    }
                },
            ],
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:' . Carbon::now()->addMonths(3)->format('Y-m-d'), // Max 3 months ahead
            ],
            'appointment_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    // Validate business hours (8 AM to 5 PM)
                    $time = Carbon::createFromFormat('H:i', $value);
                    $startTime = Carbon::createFromTime(8, 0); // 8:00 AM
                    $endTime = Carbon::createFromTime(17, 0);   // 5:00 PM
                    
                    if ($time->lt($startTime) || $time->gt($endTime)) {
                        $fail('Appointment time must be between 8:00 AM and 5:00 PM.');
                    }
                },
            ],
            'reason' => ['required', 'string', 'max:500'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:5'], // 1 = highest, 5 = lowest
            'notes' => ['nullable', 'string', 'max:1000'],
            'nurse_id' => ['nullable', 'integer', 'exists:users,id', function ($attribute, $value, $fail) {
                if ($value) {
                    $nurse = \App\Models\User::find($value);
                    if (!$nurse || !$nurse->isNurse()) {
                        $fail('The selected nurse is invalid.');
                    }
                }
            }],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'The selected user does not exist.',
            'appointment_date.required' => 'Please select an appointment date.',
            'appointment_date.after_or_equal' => 'Appointment date cannot be in the past.',
            'appointment_date.before_or_equal' => 'Appointments can only be scheduled up to 3 months in advance.',
            'appointment_time.required' => 'Please select an appointment time.',
            'appointment_time.date_format' => 'Please enter a valid time format (HH:MM).',
            'reason.required' => 'Please provide a reason for the appointment.',
            'reason.max' => 'Reason cannot exceed 500 characters.',
            'priority.min' => 'Priority must be between 1 and 5.',
            'priority.max' => 'Priority must be between 1 and 5.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
            'nurse_id.exists' => 'The selected nurse does not exist.',
        ];
    }

    /**
     * Get custom attribute names for validation messages.
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'patient',
            'appointment_date' => 'date',
            'appointment_time' => 'time',
            'nurse_id' => 'assigned nurse',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If no user_id provided and current user is student, set to current user
        if (!$this->has('user_id') && $this->user()->isStudent()) {
            $this->merge([
                'user_id' => $this->user()->id,
            ]);
        }

        // Set default priority if not provided
        if (!$this->has('priority')) {
            $this->merge([
                'priority' => 3, // Normal priority
            ]);
        }

        // Ensure appointment_time is in correct format
        if ($this->has('appointment_time')) {
            $time = $this->input('appointment_time');
            // Handle if time comes with seconds
            if (strlen($time) > 5) {
                $this->merge([
                    'appointment_time' => substr($time, 0, 5),
                ]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check for conflicting appointments
            if (!$validator->errors()->has('appointment_date') && !$validator->errors()->has('appointment_time')) {
                $existingAppointment = Appointment::where('appointment_date', $this->appointment_date)
                    ->where('appointment_time', $this->appointment_time)
                    ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
                    ->first();

                if ($existingAppointment) {
                    $validator->errors()->add('appointment_time', 'This time slot is already booked. Please choose a different time.');
                }
            }

            // Check if user already has an appointment on the same day
            if ($this->user_id && !$validator->errors()->has('appointment_date')) {
                $existingUserAppointment = Appointment::where('user_id', $this->user_id)
                    ->where('appointment_date', $this->appointment_date)
                    ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
                    ->first();

                if ($existingUserAppointment) {
                    $validator->errors()->add('appointment_date', 'You already have an appointment scheduled for this date.');
                }
            }
        });
    }
}