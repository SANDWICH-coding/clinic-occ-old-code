<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Consultation extends Model
{
    use HasFactory;

    // Consultation types - UPDATED to only include Walk-in and Scheduled Appointment
    const TYPE_WALK_IN = 'walk_in';
    const TYPE_APPOINTMENT = 'appointment';

    // Status constants - UPDATED to match migration
    const STATUS_REGISTERED = 'registered';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Priority levels - UPDATED to match migration
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    // ============= ARRAY CONSTANTS FOR BLADE TEMPLATES =============
    
    /**
     * Status options array for dropdowns
     */
    const STATUS = [
        self::STATUS_REGISTERED => 'Registered',
        self::STATUS_IN_PROGRESS => 'In Progress',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    /**
     * Type options array for dropdowns - UPDATED
     */
    const TYPE = [
        self::TYPE_WALK_IN => 'Walk-in Consultation',
        self::TYPE_APPOINTMENT => 'Scheduled Appointment',
    ];

    /**
     * Priority options array for dropdowns
     */
    const PRIORITY = [
        self::PRIORITY_LOW => 'Low Priority',
        self::PRIORITY_MEDIUM => 'Medium',
        self::PRIORITY_HIGH => 'High Priority',
        self::PRIORITY_CRITICAL => 'Critical',
    ];

    protected $fillable = [
        // References
        'student_id',
        'nurse_id',
        
        // Basic consultation info
        'type',
        'status',
        'priority',
        
        // Initial complaint and symptoms
        'chief_complaint',
        'symptoms_description',
        'pain_level',
        'initial_notes',
        
        // Vital signs
        'temperature',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'heart_rate',
        'respiratory_rate',
        'oxygen_saturation',
        'weight',
        'height',
        
        // Treatment & Diagnosis
        'diagnosis',
        'treatment_provided',
        'medications_given',
        'procedures_performed',
        'home_care_instructions',
        
        // Timing
        'consultation_date',
    ];

    protected $casts = [
        'consultation_date' => 'datetime',
        'temperature' => 'decimal:1',
        'blood_pressure_systolic' => 'integer',
        'blood_pressure_diastolic' => 'integer',
        'heart_rate' => 'integer',
        'respiratory_rate' => 'integer',
        'oxygen_saturation' => 'decimal:2',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'pain_level' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_REGISTERED,
        'type' => self::TYPE_WALK_IN, // Changed from TYPE_ROUTINE to TYPE_WALK_IN
        'priority' => self::PRIORITY_MEDIUM,
        'pain_level' => 0,
    ];

    // ============= RELATIONSHIPS =============

    /**
     * Get the student being consulted
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the nurse conducting the consultation
     */
    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    // ============= MEDICAL INFORMATION METHODS =============

    /**
     * Get comprehensive medical history for consultation view
     */
    public function getMedicalHistory(): array
    {
        $medicalRecord = $this->student?->medicalRecord;
        
        if (!$medicalRecord) {
            return [
                'has_record' => false,
                'message' => 'No medical record on file',
                'emergency_contacts' => [],
                'health_risks' => [],
                'vaccination_status' => null,
                'bmi_info' => null
            ];
        }

        return [
            'has_record' => true,
            'basic_info' => [
                'blood_type' => $medicalRecord->blood_type ?? 'Not specified',
                'height' => $medicalRecord->height ? $medicalRecord->height . ' cm' : 'Not recorded',
                'weight' => $medicalRecord->weight ? $medicalRecord->weight . ' kg' : 'Not recorded',
                'age' => $this->student?->age ?? 'Unknown',
                'gender' => $this->student?->gender ?? 'Not specified',
            ],
            'medical_conditions' => [
                'allergies' => $medicalRecord->allergies ?? 'None reported',
                'past_illnesses' => $medicalRecord->past_illnesses ?? 'None reported',
                'chronic_conditions' => $this->getChronicConditions($medicalRecord),
                'maintenance_drugs' => $medicalRecord->maintenance_drugs_specify ?? 'None',
                'is_taking_maintenance' => $medicalRecord->is_taking_maintenance_drugs ?? false,
            ],
            'surgical_history' => [
                'has_surgery' => $medicalRecord->has_undergone_surgery ?? false,
                'surgery_details' => $medicalRecord->surgery_details ?? 'None',
            ],
            'recent_hospitalization' => [
                'has_hospitalization' => $medicalRecord->has_been_hospitalized_6_months ?? false,
                'hospitalization_details' => $medicalRecord->hospitalization_details_6_months ?? 'None',
            ],
            'disability_info' => [
                'is_pwd' => $medicalRecord->is_pwd ?? false,
                'pwd_details' => $medicalRecord->pwd_disability_details ?? null,
                'pwd_reason' => $medicalRecord->pwd_reason ?? null,
            ],
            'family_history' => $medicalRecord->family_history_details ?? 'Not provided',
            'vaccination_status' => $medicalRecord->getVaccinationStatus() ?? 'Unknown',
            'bmi_info' => [
                'bmi' => $medicalRecord->calculateBMI(),
                'category' => $medicalRecord->getBMICategory(),
                'status_color' => $medicalRecord->getBMIStatusColor(),
            ],
            'emergency_contacts' => [
                'primary' => [
                    'name' => $medicalRecord->emergency_contact_name_1 ?? 'Not provided',
                    'phone' => $medicalRecord->emergency_contact_number_1 ?? 'Not provided',
                    'relationship' => $medicalRecord->emergency_contact_relationship_1 ?? 'Not specified'
                ],
                'secondary' => [
                    'name' => $medicalRecord->emergency_contact_name_2 ?? 'Not provided',
                    'phone' => $medicalRecord->emergency_contact_number_2 ?? 'Not provided',
                    'relationship' => $medicalRecord->emergency_contact_relationship_2 ?? 'Not specified'
                ]
            ],
            'health_risks' => $medicalRecord->getHealthRisks() ?? [],
            'risk_indicators' => $medicalRecord->getHealthRiskIndicators() ?? [],
            'completeness' => $medicalRecord->getCompletenessPercentage() ?? 0,
            'last_updated' => $medicalRecord->updated_at?->format('M d, Y g:i A'),
            'needs_review' => $medicalRecord->needsReview() ?? false,
        ];
    }

    /**
     * Get medical alerts for consultation
     */
    public function getMedicalAlerts(): array
    {
        $alerts = [];
        $medicalRecord = $this->student?->medicalRecord;
        
        if (!$medicalRecord) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'No Medical Record',
                'message' => 'Student has no medical record on file',
                'priority' => 'high',
                'action_required' => 'Create medical record after consultation'
            ];
            return $alerts;
        }
        
        // Critical allergies alert
        if (!empty($medicalRecord->allergies)) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'ALLERGIES',
                'message' => $medicalRecord->allergies,
                'priority' => 'critical',
                'action_required' => 'Verify before prescribing medications'
            ];
        }
        
        // Maintenance medications
        if ($medicalRecord->is_taking_maintenance_drugs && !empty($medicalRecord->maintenance_drugs_specify)) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Current Medications',
                'message' => $medicalRecord->maintenance_drugs_specify,
                'priority' => 'medium',
                'action_required' => 'Check for drug interactions'
            ];
        }
        
        // Recent hospitalization
        if ($medicalRecord->has_been_hospitalized_6_months) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Recent Hospitalization',
                'message' => $medicalRecord->hospitalization_details_6_months ?? 'Hospitalized within last 6 months',
                'priority' => 'high',
                'action_required' => 'Consider recent medical history'
            ];
        }
        
        // Disability considerations
        if ($medicalRecord->is_pwd) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Person with Disability',
                'message' => $medicalRecord->pwd_disability_details ?? $medicalRecord->pwd_reason ?? 'Special needs consideration required',
                'priority' => 'medium',
                'action_required' => 'Accommodate special needs'
            ];
        }
        
        // Emergency contacts missing
        if (empty($medicalRecord->emergency_contact_name_1) || empty($medicalRecord->emergency_contact_number_1)) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Missing Emergency Contacts',
                'message' => 'No emergency contact information available',
                'priority' => 'medium',
                'action_required' => 'Obtain emergency contact details'
            ];
        }
        
        // Unvaccinated status
        if (!$medicalRecord->is_fully_vaccinated) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Vaccination Status',
                'message' => 'Student is not fully vaccinated',
                'priority' => 'low',
                'action_required' => 'Consider vaccination recommendations'
            ];
        }

        // BMI concerns
        $bmiCategory = $medicalRecord->getBMICategory();
        if (in_array($bmiCategory, ['Underweight', 'Obese'])) {
            $alerts[] = [
                'type' => $bmiCategory === 'Obese' ? 'warning' : 'info',
                'title' => 'BMI Concern',
                'message' => "BMI indicates {$bmiCategory} ({$medicalRecord->calculateBMI()})",
                'priority' => $bmiCategory === 'Obese' ? 'medium' : 'low',
                'action_required' => 'Consider nutritional counseling'
            ];
        }
        
        return $alerts;
    }

    /**
     * Get vital signs summary for display
     */
    public function getVitalSignsSummary(): array
    {
        $vitals = [];
        
        if ($this->temperature) {
            $vitals['Temperature'] = $this->temperature . '°C';
        }
        
        if ($this->blood_pressure_systolic && $this->blood_pressure_diastolic) {
            $vitals['Blood Pressure'] = $this->blood_pressure_systolic . '/' . $this->blood_pressure_diastolic . ' mmHg';
        }
        
        if ($this->heart_rate) {
            $vitals['Heart Rate'] = $this->heart_rate . ' BPM';
        }
        
        if ($this->respiratory_rate) {
            $vitals['Respiratory Rate'] = $this->respiratory_rate . ' per min';
        }
        
        if ($this->oxygen_saturation) {
            $vitals['Oxygen Saturation'] = $this->oxygen_saturation . '%';
        }

        if ($this->weight) {
            $vitals['Weight'] = $this->weight . ' kg';
        }

        if ($this->height) {
            $vitals['Height'] = $this->height . ' cm';
        }

        if ($this->weight && $this->height) {
            $bmi = $this->calculateBMI($this->weight, $this->height);
            $vitals['BMI'] = round($bmi, 1) . ' (' . $this->getBMICategory($bmi) . ')';
        }

        return $vitals;
    }

    // ============= ENHANCED VITAL SIGNS METHODS =============

    /**
     * Validate vital signs before saving
     */
    public function validateVitalSigns(array $vitals): array
    {
        $errors = [];

        if (isset($vitals['temperature']) && ($vitals['temperature'] < 35.0 || $vitals['temperature'] > 42.0)) {
            $errors[] = 'Temperature must be between 35.0°C and 42.0°C';
        }

        if (isset($vitals['blood_pressure_systolic']) && ($vitals['blood_pressure_systolic'] < 70 || $vitals['blood_pressure_systolic'] > 250)) {
            $errors[] = 'Systolic blood pressure must be between 70 and 250 mmHg';
        }

        if (isset($vitals['blood_pressure_diastolic']) && ($vitals['blood_pressure_diastolic'] < 40 || $vitals['blood_pressure_diastolic'] > 150)) {
            $errors[] = 'Diastolic blood pressure must be between 40 and 150 mmHg';
        }

        if (isset($vitals['heart_rate']) && ($vitals['heart_rate'] < 30 || $vitals['heart_rate'] > 200)) {
            $errors[] = 'Heart rate must be between 30 and 200 BPM';
        }

        if (isset($vitals['respiratory_rate']) && ($vitals['respiratory_rate'] < 8 || $vitals['respiratory_rate'] > 40)) {
            $errors[] = 'Respiratory rate must be between 8 and 40 per minute';
        }

        if (isset($vitals['oxygen_saturation']) && ($vitals['oxygen_saturation'] < 70 || $vitals['oxygen_saturation'] > 100)) {
            $errors[] = 'Oxygen saturation must be between 70% and 100%';
        }

        if (isset($vitals['weight']) && ($vitals['weight'] < 20 || $vitals['weight'] > 300)) {
            $errors[] = 'Weight must be between 20kg and 300kg';
        }

        if (isset($vitals['height']) && ($vitals['height'] < 100 || $vitals['height'] > 250)) {
            $errors[] = 'Height must be between 100cm and 250cm';
        }

        if (isset($vitals['pain_level']) && ($vitals['pain_level'] < 0 || $vitals['pain_level'] > 10)) {
            $errors[] = 'Pain level must be between 0 and 10';
        }

        return $errors;
    }

    /**
     * Update vital signs with validation
     */
    public function updateVitalSigns(array $vitals): bool
    {
        $errors = $this->validateVitalSigns($vitals);

        if (!empty($errors)) {
            Log::warning('Vital signs validation failed', ['consultation_id' => $this->id, 'errors' => $errors]);
            return false;
        }

        $updateData = [];
        foreach ($vitals as $key => $value) {
            if ($value !== null && $value !== '') {
                $updateData[$key] = $value;
            }
        }

        if (!empty($updateData)) {
            $this->update($updateData);
            $this->logAudit('Vital signs updated', $updateData);
        }

        return true;
    }

    /**
     * Calculate BMI from weight and height
     */
    public function calculateBMI(?float $weight = null, ?float $height = null): ?float
    {
        $weight = $weight ?? $this->weight;
        $height = $height ?? $this->height;
        
        if (!$weight || !$height || $height == 0) {
            return null;
        }

        // Convert height from cm to meters
        $heightInMeters = $height / 100;
        return $weight / ($heightInMeters * $heightInMeters);
    }

    /**
     * Get BMI category
     */
    public function getBMICategory(?float $bmi = null): string
    {
        $bmi = $bmi ?? $this->calculateBMI();
        
        if (!$bmi) {
            return 'Unknown';
        }

        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25) return 'Normal';
        if ($bmi < 30) return 'Overweight';
        return 'Obese';
    }

    /**
     * Get BMI status color
     */
    public function getBMIStatusColor(?float $bmi = null): string
    {
        $bmi = $bmi ?? $this->calculateBMI();
        
        if (!$bmi) {
            return 'secondary';
        }

        if ($bmi < 18.5) return 'warning';
        if ($bmi < 25) return 'success';
        if ($bmi < 30) return 'warning';
        return 'danger';
    }

    // ============= AUDIT LOGGING =============

    /**
     * Log audit trail for critical actions
     */
    protected function logAudit(string $action, array $details): void
    {
        Log::info('Consultation audit', [
            'consultation_id' => $this->id,
            'action' => $action,
            'details' => $details,
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id() ?? 'system',
        ]);
    }

    /**
     * Get chronic conditions from medical record
     */
    private function getChronicConditions($medicalRecord): string
    {
        $conditions = [];
        
        if ($medicalRecord->is_pwd && $medicalRecord->pwd_disability_details) {
            $conditions[] = $medicalRecord->pwd_disability_details;
        }
        
        if ($medicalRecord->has_been_pregnant ?? false) {
            $conditions[] = 'History of pregnancy';
        }
        
        if (!empty($medicalRecord->chronic_conditions)) {
            $conditions[] = $medicalRecord->chronic_conditions;
        }
        
        return empty($conditions) ? 'None reported' : implode(', ', $conditions);
    }

    // ============= ACCESSOR ATTRIBUTES =============

    public function getFormattedConsultationDateAttribute(): ?string
    {
        return $this->consultation_date ? $this->consultation_date->format('M d, Y g:i A') : null;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        $classes = [
            self::STATUS_REGISTERED => 'bg-blue-100 text-blue-800',
            self::STATUS_IN_PROGRESS => 'bg-orange-100 text-orange-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
        ];

        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getStatusDisplayAttribute(): string
    {
        return self::STATUS[$this->status] ?? 'Unknown';
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        $classes = [
            self::PRIORITY_CRITICAL => 'bg-red-100 text-red-800 border-red-200',
            self::PRIORITY_HIGH => 'bg-orange-100 text-orange-800 border-orange-200',
            self::PRIORITY_MEDIUM => 'bg-blue-100 text-blue-800 border-blue-200',
            self::PRIORITY_LOW => 'bg-gray-100 text-gray-800 border-gray-200',
        ];

        return $classes[$this->priority] ?? 'bg-blue-100 text-blue-800 border-blue-200';
    }

    public function getPriorityDisplayAttribute(): string
    {
        return self::PRIORITY[$this->priority] ?? 'Medium';
    }

    public function getTypeDisplayAttribute(): string
    {
        return self::TYPE[$this->type] ?? 'Consultation';
    }

    public function getPainLevelDisplayAttribute(): string
    {
        if ($this->pain_level === null) {
            return 'Not specified';
        }

        $levels = [
            0 => 'No pain',
            1 => 'Very mild',
            2 => 'Mild',
            3 => 'Mild to moderate',
            4 => 'Moderate',
            5 => 'Moderate to severe',
            6 => 'Severe',
            7 => 'Very severe',
            8 => 'Intense',
            9 => 'Very intense',
            10 => 'Worst possible',
        ];

        return $levels[$this->pain_level] ?? 'Unknown';
    }

    /**
     * Get status color for display
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_REGISTERED => 'primary',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get priority color for display
     */
    public function getPriorityColor(): string
    {
        return match($this->priority) {
            self::PRIORITY_CRITICAL => 'danger',
            self::PRIORITY_HIGH => 'warning',
            self::PRIORITY_MEDIUM => 'info',
            self::PRIORITY_LOW => 'secondary',
            default => 'info'
        };
    }

    /**
     * Check if consultation can be edited
     */
    public function canEdit(): bool
    {
        return !$this->isCompleted() && auth()->check() && auth()->user()->isNurse();
    }

    // ============= SCOPES =============

    public function scopeRegistered($query) { return $query->where('status', self::STATUS_REGISTERED); }
    public function scopeInProgress($query) { return $query->where('status', self::STATUS_IN_PROGRESS); }
    public function scopeCompleted($query) { return $query->where('status', self::STATUS_COMPLETED); }
    public function scopeCancelled($query) { return $query->where('status', self::STATUS_CANCELLED); }

    public function scopeToday($query)
    {
        return $query->whereDate('consultation_date', Carbon::today());
    }

    public function scopeWalkIn($query)
    {
        return $query->where('type', self::TYPE_WALK_IN);
    }

    public function scopeAppointment($query)
    {
        return $query->where('type', self::TYPE_APPOINTMENT);
    }

    public function scopeCritical($query)
    {
        return $query->where('priority', self::PRIORITY_CRITICAL);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', self::PRIORITY_HIGH);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByNurse($query, $nurseId)
    {
        return $query->where('nurse_id', $nurseId);
    }

    // ============= STATUS METHODS =============

    public function isRegistered(): bool { return $this->status === self::STATUS_REGISTERED; }
    public function isInProgress(): bool { return $this->status === self::STATUS_IN_PROGRESS; }
    public function isCompleted(): bool { return $this->status === self::STATUS_COMPLETED; }
    public function isCancelled(): bool { return $this->status === self::STATUS_CANCELLED; }

    public function isWalkIn(): bool { return $this->type === self::TYPE_WALK_IN; }
    public function isAppointment(): bool { return $this->type === self::TYPE_APPOINTMENT; }
    public function isCritical(): bool { return $this->priority === self::PRIORITY_CRITICAL; }
    public function isHighPriority(): bool { return $this->priority === self::PRIORITY_HIGH; }

    // ============= BUSINESS LOGIC METHODS =============

    public function canStart(): bool
    {
        return $this->status === self::STATUS_REGISTERED;
    }

    public function canComplete(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function canCancel(): bool
    {
        return !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function requiresSpecialAttention(): array
    {
        $alerts = [];
        $medicalRecord = $this->student?->medicalRecord;
        
        if ($medicalRecord) {
            if (!empty($medicalRecord->allergies)) {
                $alerts[] = 'Patient has known allergies';
            }
            
            if ($medicalRecord->is_taking_maintenance_drugs) {
                $alerts[] = 'Patient is on maintenance medications';
            }
            
            if ($medicalRecord->has_been_hospitalized_6_months) {
                $alerts[] = 'Recent hospitalization within 6 months';
            }
            
            if ($medicalRecord->is_pwd) {
                $alerts[] = 'Patient is a person with disability';
            }
        }
        
        if ($this->isCritical() || $this->isHighPriority()) {
            $alerts[] = 'High priority consultation';
        }
        
        if ($this->pain_level >= 7) {
            $alerts[] = 'Patient reports severe pain';
        }
        
        return $alerts;
    }

    // ============= ACTION METHODS =============

    /**
     * Start the consultation
     */
    public function startConsultation(): bool
    {
        if (!$this->canStart()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
        ]);

        $this->logAudit('Consultation started', ['status' => self::STATUS_IN_PROGRESS]);
        return true;
    }

    /**
     * Complete the consultation
     */
    public function completeConsultation(array $data = []): bool
    {
        if (!$this->canComplete()) {
            return false;
        }

        $updateData = array_merge($data, [
            'status' => self::STATUS_COMPLETED,
        ]);

        $this->update($updateData);
        $this->logAudit('Consultation completed', $updateData);
        
        return true;
    }

    /**
     * Cancel the consultation
     */
    public function cancelConsultation(string $reason = null): bool
    {
        if (!$this->canCancel()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'initial_notes' => $reason ? ($this->initial_notes . "\n\nCancellation reason: " . $reason) : $this->initial_notes,
        ]);

        $this->logAudit('Consultation cancelled', ['reason' => $reason]);
        return true;
    }

    // ============= STATIC METHODS =============

    public static function getStatusOptions(): array
    {
        return self::STATUS;
    }

    public static function getTypeOptions(): array
    {
        return self::TYPE;
    }

    public static function getPriorityOptions(): array
    {
        return self::PRIORITY;
    }

    public static function getPainLevelOptions(): array
    {
        return [
            0 => '0 - No pain',
            1 => '1 - Very mild',
            2 => '2 - Mild',
            3 => '3 - Mild to moderate',
            4 => '4 - Moderate',
            5 => '5 - Moderate to severe',
            6 => '6 - Severe',
            7 => '7 - Very severe',
            8 => '8 - Intense',
            9 => '9 - Very intense',
            10 => '10 - Worst possible',
        ];
    }

    /**
     * Get daily consultation statistics
     */
    public static function getDailyStats($date = null): array
    {
        $date = $date ?? Carbon::today();
        
        $query = self::whereDate('consultation_date', $date);

        return [
            'total' => $query->count(),
            'walk_in' => $query->clone()->where('type', self::TYPE_WALK_IN)->count(),
            'appointment' => $query->clone()->where('type', self::TYPE_APPOINTMENT)->count(),
            'registered' => $query->clone()->where('status', self::STATUS_REGISTERED)->count(),
            'in_progress' => $query->clone()->where('status', self::STATUS_IN_PROGRESS)->count(),
            'completed' => $query->clone()->where('status', self::STATUS_COMPLETED)->count(),
            'cancelled' => $query->clone()->where('status', self::STATUS_CANCELLED)->count(),
            'critical' => $query->clone()->where('priority', self::PRIORITY_CRITICAL)->count(),
            'high_priority' => $query->clone()->where('priority', self::PRIORITY_HIGH)->count(),
        ];
    }

    // ============= UTILITY METHODS =============

    public function getPatientName(): string
    {
        return $this->student ? ($this->student->full_name ?? 'Unknown Patient') : 'Unknown Patient';
    }

    public function getPatientStudentId(): string
    {
        return $this->student ? ($this->student->student_id ?? 'Unknown') : 'Unknown';
    }

    public function getNurseName(): string
    {
        return $this->nurse ? ($this->nurse->full_name ?? 'Not assigned') : 'Not assigned';
    }

    public function hasVitalSigns(): bool
    {
        return !empty($this->temperature) || !empty($this->blood_pressure_systolic) || 
               !empty($this->heart_rate) || !empty($this->respiratory_rate) ||
               !empty($this->oxygen_saturation) || !empty($this->weight) || !empty($this->height);
    }

    public function isToday(): bool
    {
        return $this->consultation_date->isToday();
    }

    /**
     * Create walk-in consultation
     */
    public static function createWalkIn(User $student, User $nurse, array $data): self
    {
        $consultation = self::create([
            'student_id' => $student->id,
            'nurse_id' => $nurse->id,
            'type' => self::TYPE_WALK_IN,
            'status' => self::STATUS_REGISTERED,
            'priority' => $data['priority'] ?? self::PRIORITY_MEDIUM,
            'chief_complaint' => $data['chief_complaint'],
            'symptoms_description' => $data['symptoms_description'] ?? null,
            'pain_level' => $data['pain_level'] ?? 0,
            'initial_notes' => $data['initial_notes'] ?? null,
            'consultation_date' => now(),
        ]);

        $consultation->logAudit('Walk-in consultation created', [
            'student_id' => $student->id,
            'priority' => $data['priority'] ?? self::PRIORITY_MEDIUM,
        ]);

        return $consultation;
    }

    /**
     * Create appointment consultation
     */
    public static function createAppointment(User $student, User $nurse, array $data): self
    {
        $consultation = self::create([
            'student_id' => $student->id,
            'nurse_id' => $nurse->id,
            'type' => self::TYPE_APPOINTMENT,
            'status' => self::STATUS_REGISTERED,
            'priority' => $data['priority'] ?? self::PRIORITY_MEDIUM,
            'chief_complaint' => $data['chief_complaint'],
            'symptoms_description' => $data['symptoms_description'] ?? null,
            'pain_level' => $data['pain_level'] ?? 0,
            'initial_notes' => $data['initial_notes'] ?? null,
            'consultation_date' => now(),
        ]);

        $consultation->logAudit('Appointment consultation created', [
            'student_id' => $student->id,
            'priority' => $data['priority'] ?? self::PRIORITY_MEDIUM,
        ]);

        return $consultation;
    }

    // ============= ENHANCED CONSULTATION VIEW METHODS =============

    /**
     * Get comprehensive consultation data for the conduct view
     */
    public function getConsultationViewData(): array
    {
        return [
            'basic_info' => [
                'student' => $this->student,
                'nurse' => $this->nurse,
                'consultation' => $this,
                'status' => $this->status_display,
                'priority' => $this->priority_display,
                'type' => $this->type_display,
                'consultation_date' => $this->formatted_consultation_date,
                'pain_level' => $this->pain_level_display,
            ],
            'medical_history' => $this->getMedicalHistory(),
            'medical_alerts' => $this->getMedicalAlerts(),
            'vital_signs_summary' => $this->getVitalSignsSummary(),
        ];
    }

    /**
     * Get medical summary for quick reference
     */
    public function getMedicalSummary(): array
    {
        $medicalRecord = $this->student?->medicalRecord;
        
        return [
            'allergies' => $medicalRecord?->allergies ?? 'None reported',
            'current_medications' => $medicalRecord?->maintenance_drugs_specify ?? 'None',
            'blood_type' => $medicalRecord?->blood_type ?? 'Not specified',
            'emergency_contact' => $medicalRecord?->emergency_contact_name_1 ?? 'Not available',
            'health_risk_level' => $this->student?->getHealthRiskLevel() ?? 'Unknown',
            'vaccination_status' => $medicalRecord?->is_fully_vaccinated ? 'Vaccinated' : 'Not vaccinated',
        ];
    }
}