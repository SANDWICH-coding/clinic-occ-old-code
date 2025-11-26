<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicalRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only nurses and deans can update medical records
        return $this->user() && in_array($this->user()->role, ['nurse', 'dean']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'blood_type' => ['nullable', 'string', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'height' => ['nullable', 'numeric', 'min:50', 'max:300'], // cm
            'weight' => ['nullable', 'numeric', 'min:20', 'max:500'], // kg
            'allergies' => ['nullable', 'string', 'max:1000'],
            
            // Boolean fields
            'has_been_pregnant' => ['nullable', 'boolean'],
            'has_undergone_surgery' => ['nullable', 'boolean'],
            'is_taking_maintenance_drugs' => ['nullable', 'boolean'],
            'has_been_hospitalized_6_months' => ['nullable', 'boolean'],
            'is_pwd' => ['nullable', 'boolean'],
            'is_fully_vaccinated' => ['nullable', 'boolean'],
            'has_received_booster' => ['nullable', 'boolean'],
            
            // Conditional text fields
            'surgery_details' => ['nullable', 'string', 'max:1000', 'required_if:has_undergone_surgery,true'],
            'maintenance_drugs_specify' => ['nullable', 'string', 'max:1000', 'required_if:is_taking_maintenance_drugs,true'],
            'hospitalization_details_6_months' => ['nullable', 'string', 'max:1000', 'required_if:has_been_hospitalized_6_months,true'],
            'pwd_disability_details' => ['nullable', 'string', 'max:1000', 'required_if:is_pwd,true'],
            
            // Health notes
            'notes_health_problems' => ['nullable', 'string', 'max:2000'],
            
            // Vaccination fields
            'vaccine_type' => ['nullable', 'string', 'max:100', 'required_if:is_fully_vaccinated,true'],
            'other_vaccine_type' => ['nullable', 'string', 'max:100'],
            'number_of_doses' => ['nullable', 'integer', 'min:0', 'max:10'],
            'number_of_boosters' => ['nullable', 'integer', 'min:0', 'max:10', 'required_if:has_received_booster,true'],
            'booster_type' => ['nullable', 'string', 'max:100'],
            
            // Emergency contacts
            'emergency_contact_name_1' => ['required', 'string', 'max:255'],
            'emergency_contact_number_1' => ['required', 'string', 'max:20', 'regex:/^[\d\s\-\+\(\)]+$/'],
            'emergency_contact_name_2' => ['nullable', 'string', 'max:255'],
            'emergency_contact_number_2' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\-\+\(\)]+$/'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'blood_type.in' => 'Please select a valid blood type.',
            'height.min' => 'Height must be at least 50 cm.',
            'height.max' => 'Height cannot exceed 300 cm.',
            'weight.min' => 'Weight must be at least 20 kg.',
            'weight.max' => 'Weight cannot exceed 500 kg.',
            'surgery_details.required_if' => 'Please provide surgery details when surgery history is indicated.',
            'maintenance_drugs_specify.required_if' => 'Please specify maintenance drugs when indicated.',
            'hospitalization_details_6_months.required_if' => 'Please provide hospitalization details when indicated.',
            'pwd_disability_details.required_if' => 'Please provide disability details when PWD status is indicated.',
            'vaccine_type.required_if' => 'Please specify vaccine type when fully vaccinated.',
            'number_of_boosters.required_if' => 'Please specify number of boosters when booster is received.',
            'emergency_contact_name_1.required' => 'Primary emergency contact name is required.',
            'emergency_contact_number_1.required' => 'Primary emergency contact number is required.',
            'emergency_contact_number_1.regex' => 'Please enter a valid phone number.',
            'emergency_contact_number_2.regex' => 'Please enter a valid phone number.',
        ];
    }

    /**
     * Get custom attribute names for validation messages.
     */
    public function attributes(): array
    {
        return [
            'emergency_contact_name_1' => 'primary emergency contact name',
            'emergency_contact_number_1' => 'primary emergency contact number',
            'emergency_contact_name_2' => 'secondary emergency contact name',
            'emergency_contact_number_2' => 'secondary emergency contact number',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean phone numbers
        if ($this->has('emergency_contact_number_1')) {
            $this->merge([
                'emergency_contact_number_1' => preg_replace('/[^\d\+\-\(\)\s]/', '', $this->emergency_contact_number_1),
            ]);
        }

        if ($this->has('emergency_contact_number_2')) {
            $this->merge([
                'emergency_contact_number_2' => preg_replace('/[^\d\+\-\(\)\s]/', '', $this->emergency_contact_number_2),
            ]);
        }
    }
}