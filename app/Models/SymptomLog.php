<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class SymptomLog extends Model
{
    use SoftDeletes;

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_FOLLOW_UP_NEEDED = 'follow_up_needed';
    const STATUS_REFERRED = 'referred';

    protected $fillable = [
        'user_id',
        'student_id',
        'symptoms',
        'possible_illnesses',
        'is_emergency',
        'severity_rating',
        'notes',
        'follow_up_notes',
        'location',
        'vital_signs',
        'logged_at',
        'symptoms_started_at',
        'duration_hours',
        'status',
        'reviewed_by',
        'reviewed_at',
        'staff_notes',
        'recommendations',
        'requires_follow_up',
        'follow_up_scheduled_at',
        'related_appointment_id'
    ];
    
    protected $casts = [
        'symptoms' => 'array',
        'possible_illnesses' => 'array',
        'vital_signs' => 'array',
        'is_emergency' => 'boolean',
        'requires_follow_up' => 'boolean',
        'severity_rating' => 'integer',
        'duration_hours' => 'integer',
        'logged_at' => 'datetime',
        'symptoms_started_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'follow_up_scheduled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    protected $attributes = [
        'is_emergency' => false,
        'requires_follow_up' => false,
        'status' => self::STATUS_ACTIVE,
    ];

    // ============= RELATIONSHIPS =============
    /**
 * Relationship to linked appointment
 */
public function appointment(): BelongsTo
{
    return $this->belongsTo(Appointment::class, 'related_appointment_id');
}

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function relatedAppointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'related_appointment_id');
    }

    // ADD THIS RELATIONSHIP TO FIX THE ERROR
    public function symptom(): BelongsTo
    {
        return $this->belongsTo(Symptom::class);
    }

    // ============= SCOPES =============

    public function scopeActive($query) { return $query->where('status', self::STATUS_ACTIVE); }
    public function scopeResolved($query) { return $query->where('status', self::STATUS_RESOLVED); }
    public function scopeNeedsFollowUp($query) { return $query->where('status', self::STATUS_FOLLOW_UP_NEEDED); }
    public function scopeReferred($query) { return $query->where('status', self::STATUS_REFERRED); }

    public function scopeEmergency($query) { return $query->where('is_emergency', true); }
    public function scopeRequiresFollowUp($query) { return $query->where('requires_follow_up', true); }
    public function scopeHighSeverity($query, $minSeverity = 4) { return $query->where('severity_rating', '>=', $minSeverity); }
    
    public function scopeUnreviewed($query) { return $query->whereNull('reviewed_by'); }
    public function scopeReviewed($query) { return $query->whereNotNull('reviewed_by'); }

    public function scopeByUser($query, $userId) { return $query->where('user_id', $userId); }
    public function scopeByStudentId($query, $studentId) { return $query->where('student_id', $studentId); }

    public function scopeToday($query) { return $query->whereDate('logged_at', today()); }
    public function scopeThisWeek($query) { return $query->whereBetween('logged_at', [now()->startOfWeek(), now()->endOfWeek()]); }
    public function scopeThisMonth($query) { return $query->whereMonth('logged_at', now()->month); }

    public function scopeBetweenDates($query, $startDate, $endDate) {
        return $query->whereBetween('logged_at', [$startDate, $endDate]);
    }

    public function scopeRecent($query, $limit = 10) {
        return $query->orderBy('logged_at', 'desc')->limit($limit);
    }

    // ============= ACCESSORS =============

    protected function patientName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user ? $this->user->full_name : 'Unknown Patient',
        );
    }

    protected function reviewerName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->reviewedBy ? $this->reviewedBy->full_name : 'Not reviewed',
        );
    }

    protected function formattedLoggedAt(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->logged_at ? $this->logged_at->format('M d, Y h:i A') : 'N/A',
        );
    }

    protected function formattedSymptomsStarted(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->symptoms_started_at ? $this->symptoms_started_at->format('M d, Y h:i A') : 'Unknown',
        );
    }

    protected function durationDisplay(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->duration_hours) {
                    return 'Unknown duration';
                }
                
                if ($this->duration_hours < 24) {
                    return $this->duration_hours . ' hours';
                }
                
                $days = floor($this->duration_hours / 24);
                $hours = $this->duration_hours % 24;
                
                return $days . ' days' . ($hours > 0 ? ', ' . $hours . ' hours' : '');
            },
        );
    }

    // ============= STATUS METHODS =============

    public function isActive(): bool { return $this->status === self::STATUS_ACTIVE; }
    public function isResolved(): bool { return $this->status === self::STATUS_RESOLVED; }
    public function needsFollowUp(): bool { return $this->status === self::STATUS_FOLLOW_UP_NEEDED; }
    public function isReferred(): bool { return $this->status === self::STATUS_REFERRED; }

    public function isReviewed(): bool { return !is_null($this->reviewed_by); }
    public function isUnreviewed(): bool { return is_null($this->reviewed_by); }

    public function canBeReviewed(): bool { return $this->isUnreviewed() && $this->isActive(); }
    public function canBeResolved(): bool { return $this->isActive() && $this->isReviewed(); }

    // ============= HELPER METHODS =============

    public function getSeverityDisplayAttribute(): string
    {
        if (!$this->severity_rating) {
            return 'Not rated';
        }
        
        return match($this->severity_rating) {
            1 => 'Very Mild',
            2 => 'Mild',
            3 => 'Moderate',
            4 => 'Severe',
            5 => 'Very Severe',
            default => 'Unknown'
        };
    }

    public function getSeverityColorAttribute(): string
    {
        if (!$this->severity_rating) {
            return 'gray';
        }
        
        return match($this->severity_rating) {
            1 => 'green',
            2 => 'blue',
            3 => 'yellow',
            4 => 'orange',
            5 => 'red',
            default => 'gray'
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_FOLLOW_UP_NEEDED => 'Follow-up Needed',
            self::STATUS_REFERRED => 'Referred',
            default => 'Unknown'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'yellow',
            self::STATUS_RESOLVED => 'green',
            self::STATUS_FOLLOW_UP_NEEDED => 'orange',
            self::STATUS_REFERRED => 'blue',
            default => 'gray'
        };
    }

    public function hasVitalSigns(): bool
    {
        return !empty($this->vital_signs);
    }

    public function getVitalSign(string $sign): ?float
    {
        return $this->vital_signs[$sign] ?? null;
    }

    public function getTemperature(): ?float
    {
        return $this->getVitalSign('temperature');
    }

    public function getBloodPressure(): ?array
    {
        $systolic = $this->getVitalSign('bp_systolic');
        $diastolic = $this->getVitalSign('bp_diastolic');
        
        if (!$systolic || !$diastolic) {
            return null;
        }
        
        return [
            'systolic' => $systolic,
            'diastolic' => $diastolic,
            'formatted' => $systolic . '/' . $diastolic
        ];
    }

    public function isOverdue(): bool
    {
        if (!$this->requires_follow_up || !$this->follow_up_scheduled_at) {
            return false;
        }
        
        return $this->follow_up_scheduled_at->isPast();
    }

    // Add this method to provide a reliable way to get the student name
    public function getStudentNameAttribute()
    {
        if ($this->user) {
            return $this->user->full_name;
        }
        
        // If user relationship doesn't exist, try to find the user by student_id
        if ($this->student_id) {
            $user = User::where('student_id', $this->student_id)->first();
            if ($user) {
                return $user->full_name;
            }
        }
        
        return 'Unknown Student';
    }

    // Add this method to get a reliable student identifier
    public function getStudentIdentifierAttribute()
    {
        if ($this->student_id) {
            return $this->student_id;
        }
        
        if ($this->user && $this->user->student_id) {
            return $this->user->student_id;
        }
        
        if ($this->user) {
            return 'User #' . $this->user->id;
        }
        
        return 'No ID';
    }

    // ============= STATIC METHODS =============

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_FOLLOW_UP_NEEDED => 'Follow-up Needed',
            self::STATUS_REFERRED => 'Referred'
        ];
    }

    public static function getSeverityOptions(): array
    {
        return [
            1 => 'Very Mild',
            2 => 'Mild',
            3 => 'Moderate',
            4 => 'Severe',
            5 => 'Very Severe'
        ];
    }
}