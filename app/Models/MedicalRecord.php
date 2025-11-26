<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'created_by',
        'is_auto_created',
        'blood_type',
        'height',
        'weight',
        'allergies',
        'past_illnesses',
        'has_been_pregnant',
        'has_undergone_surgery',
        'surgery_details',
        'is_taking_maintenance_drugs',
        'maintenance_drugs_specify',
        'has_been_hospitalized_6_months',
        'hospitalization_details_6_months',
        'is_pwd',
        'pwd_id',
        'pwd_reason',
        'pwd_disability_details',
        'notes_health_problems',
        'family_history_details',
        'is_fully_vaccinated',
        'vaccine_type',
        'vaccine_name',
        'other_vaccine_type',
        'vaccine_date',
        'number_of_doses',
        'has_received_booster',
        'number_of_boosters',
        'booster_type',
        'emergency_contact_name_1',
        'emergency_contact_number_1',
        'emergency_contact_relationship_1',
        'emergency_contact_name_2',
        'emergency_contact_number_2',
        'emergency_contact_relationship_2',
    ];

    protected $casts = [
        'has_been_pregnant' => 'boolean',
        'has_undergone_surgery' => 'boolean',
        'is_taking_maintenance_drugs' => 'boolean',
        'has_been_hospitalized_6_months' => 'boolean',
        'is_pwd' => 'boolean',
        'is_fully_vaccinated' => 'boolean',
        'has_received_booster' => 'boolean',
        'is_auto_created' => 'boolean',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        // ✅ REMOVED: 'number_of_doses' => 'integer',
        // ✅ REMOVED: 'number_of_boosters' => 'integer',
        // These are ENUM fields in the database, not integers
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'vaccine_date' => 'date',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'vaccine_date',
    ];

    protected $appends = [
        'patient_name',
        'creator_name',
        'formatted_created_date',
        'formatted_updated_date',
        'emergency_contacts',
        'patient_age',
    ];

    // ============= RELATIONSHIPS =============

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ============= ACCESSORS & MUTATORS =============

    public function getPatientNameAttribute(): string
    {
        return $this->user ? $this->user->full_name : 'Unknown Patient';
    }

    public function getCreatorNameAttribute(): string
    {
        return $this->createdBy ? $this->createdBy->full_name : 'System';
    }

    public function getEmergencyContactsAttribute(): array
    {
        $contacts = [];
        
        if ($this->emergency_contact_name_1 && $this->emergency_contact_number_1) {
            $contacts['primary'] = [
                'name' => $this->emergency_contact_name_1,
                'number' => $this->emergency_contact_number_1,
                'relationship' => $this->emergency_contact_relationship_1,
            ];
        }
        
        if ($this->emergency_contact_name_2 && $this->emergency_contact_number_2) {
            $contacts['secondary'] = [
                'name' => $this->emergency_contact_name_2,
                'number' => $this->emergency_contact_number_2,
                'relationship' => $this->emergency_contact_relationship_2,
            ];
        }
        
        return $contacts;
    }

    public function getFormattedCreatedDateAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('M j, Y h:i A') : 'N/A';
    }

    public function getFormattedUpdatedDateAttribute(): string
    {
        return $this->updated_at ? $this->updated_at->format('M j, Y h:i A') : 'N/A';
    }

    public function getPatientAgeAttribute(): ?int
    {
        return $this->user && $this->user->date_of_birth 
            ? Carbon::parse($this->user->date_of_birth)->age 
            : null;
    }

    // ✅ NEW: Helper methods for ENUM fields
    
    /**
     * Get number of doses as a clean display value
     */
    public function getNumberOfDosesDisplayAttribute(): ?string
    {
        if (!$this->number_of_doses || $this->number_of_doses === 'N/A') {
            return null;
        }
        return $this->number_of_doses;
    }

    /**
     * Get number of boosters as a clean display value
     */
    public function getNumberOfBoostersDisplayAttribute(): ?string
    {
        if (!$this->number_of_boosters || $this->number_of_boosters === 'None') {
            return null;
        }
        return $this->number_of_boosters;
    }

    /**
     * Check if actually has booster data (regardless of boolean flag)
     */
    public function hasBoosterData(): bool
    {
        return !empty($this->booster_type) || 
               ($this->number_of_boosters && $this->number_of_boosters !== 'None');
    }

    // ============= BMI CALCULATIONS =============

    public function calculateBMI(): ?float
    {
        if (!$this->height || !$this->weight || $this->height <= 0 || $this->weight <= 0) {
            return null;
        }
        
        $heightInMeters = $this->height / 100;
        return round($this->weight / ($heightInMeters ** 2), 2);
    }

    public function getBMICategory(): ?string
    {
        $bmi = $this->calculateBMI();
        
        if ($bmi === null) {
            return null;
        }

        if ($bmi < 18.5) {
            return 'Underweight';
        } elseif ($bmi < 25) {
            return 'Normal weight';
        } elseif ($bmi < 30) {
            return 'Overweight';
        } else {
            return 'Obese';
        }
    }

    public function getBMIStatusColor(): string
    {
        $category = $this->getBMICategory();
        
        return match($category) {
            'Underweight' => 'text-blue-600',
            'Normal weight' => 'text-green-600',
            'Overweight' => 'text-yellow-600',
            'Obese' => 'text-red-600',
            default => 'text-gray-600'
        };
    }

    // ============= HEALTH STATUS METHODS =============

    public function hasHighRiskConditions(): bool
    {
        return $this->has_been_hospitalized_6_months ||
               $this->is_taking_maintenance_drugs ||
               $this->is_pwd ||
               !empty($this->allergies) ||
               $this->getBMICategory() === 'Obese';
    }

    public function getHealthRisks(): array
    {
        $risks = [];
        
        if ($this->has_been_hospitalized_6_months) {
            $risks[] = 'Recent hospitalization';
        }
        
        if ($this->is_taking_maintenance_drugs) {
            $risks[] = 'On maintenance medication';
        }
        
        if ($this->is_pwd) {
            $risks[] = 'Person with disability';
        }
        
        if (!empty($this->allergies)) {
            $risks[] = 'Has allergies';
        }
        
        if ($this->getBMICategory() === 'Obese') {
            $risks[] = 'Obesity (BMI > 30)';
        }
        
        if ($this->getBMICategory() === 'Underweight') {
            $risks[] = 'Underweight (BMI < 18.5)';
        }
        
        return $risks;
    }

    public function getHealthRiskIndicators(): array
    {
        $indicators = [];
        
        if ($this->has_been_hospitalized_6_months) {
            $indicators[] = [
                'type' => 'warning',
                'description' => 'Recently hospitalized within 6 months',
                'details' => $this->hospitalization_details_6_months
            ];
        }
        
        if ($this->is_taking_maintenance_drugs) {
            $indicators[] = [
                'type' => 'info',
                'description' => 'Currently on maintenance medication',
                'details' => $this->maintenance_drugs_specify
            ];
        }
        
        if ($this->getBMICategory() === 'Obese') {
            $indicators[] = [
                'type' => 'warning',
                'description' => 'BMI indicates obesity (≥30)',
                'details' => 'BMI: ' . $this->calculateBMI()
            ];
        }
        
        if ($this->getBMICategory() === 'Underweight') {
            $indicators[] = [
                'type' => 'warning',
                'description' => 'BMI indicates underweight (<18.5)',
                'details' => 'BMI: ' . $this->calculateBMI()
            ];
        }
        
        return $indicators;
    }

    public function canBeEditedBy($user): bool
    {
        if ($user->role === 'student' && $this->user_id === $user->id) {
            return true;
        }
        
        if (in_array($user->role, ['nurse', 'dean'])) {
            return true;
        }
        
        return false;
    }

    public function getVaccinationStatus(): array
    {
        return [
            'is_vaccinated' => $this->is_fully_vaccinated,
            'vaccine_type' => $this->vaccine_type ?? $this->vaccine_name,
            'other_vaccine' => $this->other_vaccine_type,
            'doses' => $this->number_of_doses,
            'has_booster' => $this->has_received_booster || $this->hasBoosterData(),
            'booster_type' => $this->booster_type,
            'booster_count' => $this->number_of_boosters,
            'vaccine_date' => $this->vaccine_date,
        ];
    }

    // ============= QUERY SCOPES =============

    public function scopeHighRisk(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('has_been_hospitalized_6_months', true)
              ->orWhere('is_taking_maintenance_drugs', true)
              ->orWhere('is_pwd', true)
              ->orWhereNotNull('allergies');
        });
    }

    public function scopeByBloodType(Builder $query, string $bloodType): Builder
    {
        return $query->where('blood_type', $bloodType);
    }

    public function scopeVaccinated(Builder $query): Builder
    {
        return $query->where('is_fully_vaccinated', true);
    }

    public function scopeWithBooster(Builder $query): Builder
    {
        return $query->where(function($q) {
            $q->where('has_received_booster', true)
              ->orWhereNotNull('booster_type')
              ->orWhere(function($subQ) {
                  $subQ->whereNotNull('number_of_boosters')
                       ->where('number_of_boosters', '!=', 'None');
              });
        });
    }

    public function scopeCreatedBetween(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // ============= VALIDATION METHODS =============

    public function isComplete(): bool
    {
        $requiredFields = [
            'blood_type',
            'height',
            'weight',
            'emergency_contact_name_1',
            'emergency_contact_number_1',
            'emergency_contact_relationship_1',
        ];
        
        foreach ($requiredFields as $field) {
            if (empty($this->{$field})) {
                return false;
            }
        }
        
        return true;
    }

    public function getMissingFields(): array
    {
        $requiredFields = [
            'blood_type' => 'Blood Type',
            'height' => 'Height',
            'weight' => 'Weight',
            'emergency_contact_name_1' => 'Primary Emergency Contact Name',
            'emergency_contact_number_1' => 'Primary Emergency Contact Number',
            'emergency_contact_relationship_1' => 'Primary Emergency Contact Relationship',
        ];
        
        $missing = [];
        
        foreach ($requiredFields as $field => $label) {
            if (empty($this->{$field})) {
                $missing[] = $label;
            }
        }
        
        return $missing;
    }

    // ============= HELPER METHODS =============

    public function needsReview(): bool
    {
        return $this->updated_at && $this->updated_at->diffInMonths(now()) >= 6;
    }

    public function getCompletenessPercentage(): int
    {
        $totalFields = [
            'blood_type',
            'height',
            'weight',
            'allergies',
            'surgery_details',
            'maintenance_drugs_specify',
            'hospitalization_details_6_months',
            'pwd_disability_details',
            'notes_health_problems',
            'vaccine_type',
            'emergency_contact_name_1',
            'emergency_contact_number_1',
            'emergency_contact_relationship_1',
            'emergency_contact_name_2',
            'emergency_contact_number_2',
            'emergency_contact_relationship_2',
        ];
        
        $completedFields = 0;
        
        foreach ($totalFields as $field) {
            if (!empty($this->{$field})) {
                $completedFields++;
            }
        }
        
        return round(($completedFields / count($totalFields)) * 100);
    }

    public function getHealthSummary(): array
    {
        $summary = [
            'conditions' => [],
            'medications' => [],
            'allergies' => [],
            'disabilities' => [],
            'vaccination' => $this->getVaccinationStatus(),
            'bmi_info' => [
                'bmi' => $this->calculateBMI(),
                'category' => $this->getBMICategory(),
                'status_color' => $this->getBMIStatusColor(),
            ],
            'risk_level' => $this->hasHighRiskConditions() ? 'High' : 'Normal',
            'completeness' => $this->getCompletenessPercentage(),
            'emergency_contacts' => $this->emergency_contacts,
        ];
        
        if ($this->has_undergone_surgery && $this->surgery_details) {
            $summary['conditions'][] = 'Surgical History: ' . $this->surgery_details;
        }
        
        if ($this->has_been_hospitalized_6_months && $this->hospitalization_details_6_months) {
            $summary['conditions'][] = 'Recent Hospitalization: ' . $this->hospitalization_details_6_months;
        }
        
        if ($this->has_been_pregnant) {
            $summary['conditions'][] = 'Pregnancy History';
        }
        
        if ($this->is_taking_maintenance_drugs && $this->maintenance_drugs_specify) {
            $summary['medications'][] = $this->maintenance_drugs_specify;
        }
        
        if ($this->allergies) {
            $summary['allergies'] = explode(',', $this->allergies);
            $summary['allergies'] = array_map('trim', $summary['allergies']);
        }
        
        if ($this->is_pwd && ($this->pwd_disability_details || $this->pwd_reason)) {
            $summary['disabilities'][] = $this->pwd_disability_details ?: $this->pwd_reason;
        }
        
        return $summary;
    }

    // ============= STATIC METHODS =============

    public static function getBloodTypeOptions(): array
    {
        return [
            'A+' => 'A+',
            'A-' => 'A-',
            'B+' => 'B+',
            'B-' => 'B-',
            'AB+' => 'AB+',
            'AB-' => 'AB-',
            'O+' => 'O+',
            'O-' => 'O-',
        ];
    }

    public static function getVaccineTypeOptions(): array
    {
        return [
            'Pfizer-BioNTech' => 'Pfizer-BioNTech',
            'Moderna' => 'Moderna',
            'AstraZeneca' => 'AstraZeneca',
            'Johnson & Johnson' => 'Johnson & Johnson',
            'Sinopharm' => 'Sinopharm',
            'Sinovac' => 'Sinovac',
            'COVAXIN' => 'COVAXIN',
            'Covovax' => 'Covovax',
            'Sputnik V' => 'Sputnik V',
            'Other' => 'Other',
        ];
    }

    public static function getEmergencyRelationshipOptions(): array
    {
        return [
            'parent' => 'Parent',
            'mother' => 'Mother',
            'father' => 'Father',
            'spouse' => 'Spouse',
            'sibling' => 'Sibling',
            'child' => 'Child',
            'guardian' => 'Guardian',
            'friend' => 'Friend',
            'other' => 'Other Relative',
        ];
    }

    public static function getStatistics(): array
    {
        $total = self::count();
        $vaccinated = self::where('is_fully_vaccinated', true)->count();
        $highRisk = self::highRisk()->count();
        $needsReview = self::where('updated_at', '<', now()->subMonths(6))->count();
        
        return [
            'total_records' => $total,
            'vaccinated_count' => $vaccinated,
            'vaccination_rate' => $total > 0 ? round(($vaccinated / $total) * 100, 1) : 0,
            'high_risk_count' => $highRisk,
            'high_risk_rate' => $total > 0 ? round(($highRisk / $total) * 100, 1) : 0,
            'needs_review_count' => $needsReview,
            'needs_review_rate' => $total > 0 ? round(($needsReview / $total) * 100, 1) : 0,
        ];
    }

    public static function getBloodTypeDistribution(): array
    {
        return self::selectRaw('blood_type, COUNT(*) as count')
            ->whereNotNull('blood_type')
            ->groupBy('blood_type')
            ->orderBy('count', 'desc')
            ->pluck('count', 'blood_type')
            ->toArray();
    }

    public static function getVaccinationStats(): array
    {
        $vaccineTypes = self::selectRaw('
                COALESCE(vaccine_type, vaccine_name) as vaccine_name, 
                COUNT(*) as count
            ')
            ->where('is_fully_vaccinated', true)
            ->whereNotNull(\DB::raw('COALESCE(vaccine_type, vaccine_name)'))
            ->groupBy(\DB::raw('COALESCE(vaccine_type, vaccine_name)'))
            ->orderBy('count', 'desc')
            ->pluck('count', 'vaccine_name')
            ->toArray();
            
        $boosterStats = [
            'with_booster' => self::where(function($q) {
                $q->where('has_received_booster', true)
                  ->orWhereNotNull('booster_type')
                  ->orWhere(function($subQ) {
                      $subQ->whereNotNull('number_of_boosters')
                           ->where('number_of_boosters', '!=', 'None');
                  });
            })->count(),
            'without_booster' => self::where('is_fully_vaccinated', true)
                ->where('has_received_booster', false)
                ->whereNull('booster_type')
                ->where(function($q) {
                    $q->whereNull('number_of_boosters')
                      ->orWhere('number_of_boosters', 'None');
                })
                ->count(),
        ];
        
        return [
            'vaccine_types' => $vaccineTypes,
            'booster_stats' => $boosterStats,
        ];
    }
}