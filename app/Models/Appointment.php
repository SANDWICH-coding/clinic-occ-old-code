<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Appointment extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_RESCHEDULED = 'rescheduled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FOLLOW_UP_PENDING = 'follow_up_pending';
    const STATUS_RESCHEDULE_REQUESTED = 'reschedule_requested';
    const STATUS_REJECTED = 'rejected';

    // Appointment type constants
    const TYPE_SCHEDULED = 'scheduled';
    const TYPE_WALK_IN = 'walk_in';
    const TYPE_FOLLOW_UP = 'follow_up';
    const TYPE_EMERGENCY = 'emergency';

    // Priority constants
    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAL = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_URGENT = 4;
    const PRIORITY_EMERGENCY = 5;

    // Clinic hours constants - Updated to match real clinic hours
    const CLINIC_MORNING_START = '09:00';
    const CLINIC_MORNING_END = '11:00';
    const CLINIC_AFTERNOON_START = '13:30';
    const CLINIC_AFTERNOON_END = '17:00';
    const SLOT_DURATION_MINUTES = 60; // Changed from 30 to 60
    const MAX_SLOTS_PER_DAY = 16;

    protected $fillable = [
        'user_id',
        'appointment_date',
        'appointment_time',
        'reason',
        'status',
        'symptoms',
        'notes',
        'preferred_time',
        'is_urgent',
        'symptoms',
        'appointment_type',
        'nurse_id',
        'priority',
        'accepted_by',
        'accepted_at',
        'rescheduled_by',
        'rescheduled_at',
        'reschedule_reason',
        'requires_student_confirmation',
        'student_confirmed_at',
        'reschedule_request_reason',
        'student_requested_reschedule_at',
        'student_preferred_new_date',
        'student_preferred_new_time',
        'completed_by',
        'completed_at',
        'is_follow_up',
        'parent_appointment_id',
        'created_by_nurse',
        'student_accepted_followup_at',
        'decline_reason',
        'student_declined_followup_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'updated_by',
        'feedback',
        'rating',
        'feedback_submitted_at',
        'rating_submitted_at'
    ];

    protected $casts = [
        'appointment_date' => 'date',  // ✅ Just 'date'
        'student_preferred_new_date' => 'date',  // ✅ Just 'date'
        'accepted_at' => 'datetime',
        'rescheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'rejected_at' => 'datetime',
        'student_confirmed_at' => 'datetime',
        'student_requested_reschedule_at' => 'datetime',
        'student_accepted_followup_at' => 'datetime',
        'student_declined_followup_at' => 'datetime',
        'feedback_submitted_at' => 'datetime',
        'rating_submitted_at' => 'datetime',
        'requires_student_confirmation' => 'boolean',
        'is_urgent' => 'boolean',
        'is_follow_up' => 'boolean',
        'priority' => 'integer',
        'rating' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'appointment_type' => self::TYPE_SCHEDULED,
        'is_urgent' => false,
        'is_follow_up' => false,
        'requires_student_confirmation' => false,
        'priority' => self::PRIORITY_NORMAL,
    ];

    protected $appends = [
        'formatted_date',
        'formatted_time',
        'formatted_date_time',
        'status_badge_class',
        'status_display',
        'priority_display',
        'priority_badge_class',
        'is_overdue',
        'can_be_cancelled',
        'can_be_rescheduled',
        'duration',
        'patient_name',
        'patient_student_id',
        'appointment_type_display',
        'urgency_display',
        'urgency_badge_class',
        'full_appointment_type_display',
        'appointment_type_badge_class',
        'formatted_symptoms',
    ];

    // ============= RELATIONSHIPS =============
    /**
 * Get the symptom log associated with this appointment
 */
public function symptomLog(): BelongsTo
{
    return $this->belongsTo(SymptomLog::class, 'related_appointment_id');
}

/**
 * Get formatted symptoms as array
 */
public function getFormattedSymptomsAttribute(): array
{
    if (!$this->symptoms) {
        return [];
    }
    
    return array_filter(array_map('trim', explode(',', $this->symptoms)));
}

/**
 * Helper method to check if appointment has symptoms
 */
public function hasSymptoms(): bool
{
    return !empty($this->symptoms);
}

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function rescheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rescheduled_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function createdByNurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_nurse');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'parent_appointment_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(Appointment::class, 'parent_appointment_id');
    }

    /**
     * Check if the appointment has any follow-up appointments
     */
    public function hasFollowUps(): bool
    {
        return $this->followUps()->exists();
    }

    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class);
    }

    // ============= ACCESSORS =============

    public function getFormattedDateAttribute(): string
    {
        return $this->appointment_date ? $this->appointment_date->format('M d, Y') : 'Date TBD';
    }

   public function getAppointmentTimeAttribute($value)
{
    if (!$value) return null;
    
    try {
        return Carbon::parse($value)->format('H:i:s'); // FIXED: Return in storage format
    } catch (\Exception $e) {
        return $value;
    }
}

public function setAppointmentTimeAttribute($value)
{
    if ($value) {
        try {
            // Ensure the time is in proper format
            $this->attributes['appointment_time'] = Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            // If parsing fails, use as-is but ensure it's a string
            $this->attributes['appointment_time'] = (string) $value;
        }
    } else {
        $this->attributes['appointment_time'] = null;
    }
}

    public function getFormattedTimeAttribute(): string
{
    if (!$this->appointment_time) {
        return 'Time TBD';
    }

    try {
        return Carbon::parse($this->appointment_time)->format('g:i A');
    } catch (\Exception $e) {
        // Fallback: try to parse as is
        try {
            return Carbon::createFromFormat('H:i:s', $this->appointment_time)->format('g:i A');
        } catch (\Exception $e2) {
            return 'Invalid Time';
        }
    }
}

    public function getFormattedDateTimeAttribute(): string
    {
        return $this->formatted_date . ' at ' . $this->formatted_time;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            self::STATUS_CONFIRMED => 'bg-blue-100 text-blue-800 border-blue-200',
            self::STATUS_RESCHEDULED => 'bg-orange-100 text-orange-800 border-orange-200',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800 border-green-200',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800 border-red-200',
            self::STATUS_FOLLOW_UP_PENDING => 'bg-purple-100 text-purple-800 border-purple-200',
            self::STATUS_RESCHEDULE_REQUESTED => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            self::STATUS_REJECTED => 'bg-gray-100 text-gray-800 border-gray-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending Review',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_RESCHEDULED => 'Rescheduled',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_FOLLOW_UP_PENDING => 'Follow-up Pending',
            self::STATUS_RESCHEDULE_REQUESTED => 'Reschedule Requested',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Unknown Status',
        };
    }

    public function getPriorityDisplayAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
            self::PRIORITY_EMERGENCY => 'Emergency',
            default => 'Unknown',
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'bg-gray-100 text-gray-800 border-gray-200',
            self::PRIORITY_NORMAL => 'bg-blue-100 text-blue-800 border-blue-200',
            self::PRIORITY_HIGH => 'bg-orange-100 text-orange-800 border-orange-200',
            self::PRIORITY_URGENT => 'bg-red-100 text-red-800 border-red-200',
            self::PRIORITY_EMERGENCY => 'bg-purple-100 text-purple-800 border-purple-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_PENDING])) {
            return false;
        }

        if (!$this->appointment_date || !$this->appointment_time) {
            return false;
        }

        try {
            $appointmentDateTime = Carbon::parse($this->appointment_date->format('Y-m-d') . ' ' . $this->appointment_time);
            return $appointmentDateTime->isPast();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getCanBeCancelledAttribute(): bool
    {
        return $this->canBeCancelled();
    }

    public function getCanBeRescheduledAttribute(): bool
    {
        return $this->canBeRescheduledByStudent() || $this->canBeRescheduledByNurse();
    }

    public function getDurationAttribute(): int
    {
        return self::SLOT_DURATION_MINUTES;
    }

    /**
     * Get the patient's full name as an accessor
     */
    public function getPatientNameAttribute(): string
    {
        return $this->getPatientName();
    }

    /**
     * Get the patient's student ID as an accessor
     */
    public function getPatientStudentIdAttribute(): ?string
    {
        return $this->user?->student_id;
    }

    /**
     * Get appointment type display name as an accessor
     */
    public function getAppointmentTypeDisplayAttribute(): string
    {
        return match ($this->appointment_type) {
            self::TYPE_SCHEDULED => 'Scheduled',
            self::TYPE_WALK_IN => 'Walk-in',
            self::TYPE_FOLLOW_UP => 'Follow-up',
            self::TYPE_EMERGENCY => 'Emergency',
            default => ucfirst($this->appointment_type),
        };
    }

    /**
     * Get full appointment type display (includes urgency indicator)
     */
    public function getFullAppointmentTypeDisplayAttribute(): string
    {
        $type = $this->appointment_type_display;
        if ($this->is_urgent || $this->priority >= self::PRIORITY_HIGH) {
            $type .= ' ⚠️';
        }
        return $type;
    }

    /**
     * Get appointment type badge class
     */
    public function getAppointmentTypeBadgeClassAttribute(): string
    {
        return match ($this->appointment_type) {
            self::TYPE_SCHEDULED => 'bg-blue-100 text-blue-800 border-blue-200',
            self::TYPE_WALK_IN => 'bg-green-100 text-green-800 border-green-200',
            self::TYPE_FOLLOW_UP => 'bg-purple-100 text-purple-800 border-purple-200',
            self::TYPE_EMERGENCY => 'bg-red-100 text-red-800 border-red-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    /**
     * Get urgency display
     */
    public function getUrgencyDisplayAttribute(): string
    {
        if ($this->priority >= self::PRIORITY_EMERGENCY) {
            return 'Emergency';
        } elseif ($this->priority >= self::PRIORITY_URGENT) {
            return 'Urgent';
        } elseif ($this->priority >= self::PRIORITY_HIGH || $this->is_urgent) {
            return 'High Priority';
        }
        return 'Routine';
    }

    /**
     * Get urgency badge class
     */
    public function getUrgencyBadgeClassAttribute(): string
    {
        if ($this->priority >= self::PRIORITY_EMERGENCY) {
            return 'bg-red-100 text-red-800 border-red-200';
        } elseif ($this->priority >= self::PRIORITY_URGENT) {
            return 'bg-orange-100 text-orange-800 border-orange-200';
        } elseif ($this->priority >= self::PRIORITY_HIGH || $this->is_urgent) {
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        }
        return 'bg-green-100 text-green-800 border-green-200';
    }

    /**
     * Get previous consultations for the patient, excluding current one
     */
    public function getPreviousConsultationsAttribute()
    {
        return $this->user->consultations()
            ->where('status', 'completed')
            ->where('id', '!=', $this->consultation?->id ?? 0)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    // ============= PATIENT HELPER METHODS =============

    /**
     * Get the patient's full name
     */
    public function getPatientName(): string
    {
        if (!$this->user) {
            return 'Unknown Patient';
        }

        $firstName = $this->user->first_name ?? '';
        $lastName = $this->user->last_name ?? '';

        return trim($firstName . ' ' . $lastName) ?: 'Unknown Patient';
    }

    /**
     * Get the patient's student ID
     */
    public function getPatientStudentId(): ?string
    {
        return $this->user?->student_id;
    }

    /**
     * Get patient display name (short format for calendar pills)
     */
    public function getPatientDisplayName(): string
    {
        $name = $this->getPatientName();
        return Str::limit($name, 15, '...');
    }

    /**
     * Get patient info with student ID (for detailed views)
     */
    public function getPatientInfo(): string
    {
        $name = $this->getPatientName();
        $studentId = $this->getPatientStudentId();
        
        if ($studentId) {
            return "{$name} ({$studentId})";
        }
        
        return $name;
    }

    // ============= STATUS METHODS =============

    public function isPending(): bool 
    { 
        return $this->status === self::STATUS_PENDING; 
    }

    public function isConfirmed(): bool 
    { 
        return $this->status === self::STATUS_CONFIRMED; 
    }

    public function isRescheduled(): bool 
    { 
        return $this->status === self::STATUS_RESCHEDULED; 
    }

    public function isCompleted(): bool 
    { 
        return $this->status === self::STATUS_COMPLETED; 
    }

    public function isCancelled(): bool 
    { 
        return $this->status === self::STATUS_CANCELLED; 
    }

    public function isFollowUpPending(): bool 
    { 
        return $this->is_follow_up && $this->status === self::STATUS_FOLLOW_UP_PENDING; 
    }

    public function isRescheduleRequested(): bool 
    { 
        return $this->status === self::STATUS_RESCHEDULE_REQUESTED; 
    }

    public function isRejected(): bool 
    { 
        return $this->status === self::STATUS_REJECTED; 
    }

    /**
     * Check if the appointment requires nurse action (e.g., can be rejected)
     */
    public function requiresNurseAction(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_RESCHEDULE_REQUESTED,
            self::STATUS_FOLLOW_UP_PENDING,
        ]);
    }

    /**
     * Check if appointment can be cancelled
     * Can be cancelled if: pending/confirmed/rescheduled and is in the future
     */
    public function canBeCancelled(): bool
    {
        $cancellableStatuses = [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_RESCHEDULED,
            self::STATUS_FOLLOW_UP_PENDING,
            self::STATUS_RESCHEDULE_REQUESTED
        ];

        if (!in_array($this->status, $cancellableStatuses)) {
            return false;
        }

        // Cannot cancel past appointments
        if ($this->appointment_date && $this->appointment_date->isPast()) {
            return false;
        }

        // Emergency appointments have special rules
        if ($this->appointment_type === self::TYPE_EMERGENCY) {
            return $this->status === self::STATUS_PENDING; // Only pending emergencies can be cancelled
        }

        return true;
    }

    /**
     * Check if student can reschedule their appointment
     */
    public function canBeRescheduledByStudent(): bool
    {
        $reschedulableStatuses = [
            self::STATUS_CONFIRMED,
            self::STATUS_FOLLOW_UP_PENDING
        ];

        return in_array($this->status, $reschedulableStatuses) && 
               $this->appointment_date && $this->appointment_date->isFuture();
    }

    /**
     * Check if nurse can reschedule the appointment
     */
    public function canBeRescheduledByNurse(): bool
    {
        $reschedulableStatuses = [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_RESCHEDULE_REQUESTED
        ];

        return in_array($this->status, $reschedulableStatuses);
    }

    /**
     * Check if appointment requires student confirmation
     */
    public function requiresStudentConfirmation(): bool
    {
        return $this->requires_student_confirmation && 
               $this->status === self::STATUS_RESCHEDULED;
    }

    /**
     * Check if appointment can be accepted by nurse
     */
    public function canBeAccepted(): bool
    {
        return $this->isPending() && is_null($this->nurse_id);
    }

    /**
     * Check if appointment can be marked as completed
     */
    public function canBeCompleted(): bool
    {
        $completableStatuses = [
            self::STATUS_CONFIRMED,
            self::STATUS_RESCHEDULED
        ];

        return in_array($this->status, $completableStatuses) && 
               $this->appointment_date && 
               $this->appointment_date->isToday();
    }

    /**
     * Check if appointment can be started as consultation
     */
    public function canStartConsultation(): bool
    {
        $startableStatuses = [
            self::STATUS_CONFIRMED,
            self::STATUS_RESCHEDULED
        ];

        return in_array($this->status, $startableStatuses) && 
               $this->appointment_date && 
               $this->appointment_date->isToday();
    }

    /**
     * Check if appointment is urgent based on priority or urgent flag
     */
    public function isUrgent(): bool
    {
        return $this->is_urgent || $this->priority >= self::PRIORITY_HIGH;
    }

    // ============= BUSINESS LOGIC METHODS =============

    /**
     * Check if time is within clinic operating hours
     */
  /**
 * Check if time is within clinic operating hours
 */
public static function isWithinClinicHours(string $time): bool
{
    try {
        // Normalize the time input
        $timeCarbon = Carbon::parse($time);
        $timeString = $timeCarbon->format('H:i:s');
        
        // Define clinic hours with seconds
        $validTimes = [
            '09:00:00', '10:00:00', // Morning slots
            '13:30:00', '14:30:00', '15:30:00', '16:30:00' // Afternoon slots
        ];

        return in_array($timeString, $validTimes);
        
    } catch (\Exception $e) {
        Log::warning('Error validating clinic hours', [
            'time' => $time,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

   /**
 * Get available time slots for a specific date - IMPROVED VERSION
 */
public static function getAvailableTimeSlots(string $date, ?int $nurseId = null, ?int $excludeAppointmentId = null): array
{
    try {
        // Validate date format first
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            Log::warning('Invalid date format in getAvailableTimeSlots', ['date' => $date]);
            return [];
        }

        $dateCarbon = Carbon::createFromFormat('Y-m-d', $date);
        if (!$dateCarbon || $dateCarbon->format('Y-m-d') !== $date) {
            Log::warning('Invalid date in getAvailableTimeSlots', ['date' => $date]);
            return [];
        }
        
        // Check if it's a weekend
        if ($dateCarbon->isWeekend()) {
            return [];
        }

        // Check if date is in the past
        if ($dateCarbon->lt(today())) {
            return [];
        }

        $slots = [];
        
        // Morning slots: 09:00, 10:00 (2 slots with 1-hour intervals)
        $morningStart = Carbon::parse($date . ' ' . self::CLINIC_MORNING_START);
        $morningEnd = Carbon::parse($date . ' ' . self::CLINIC_MORNING_END);
        
        $current = $morningStart->copy();
        while ($current->lt($morningEnd)) {
            $slots[] = [
                'value' => $current->format('H:i:s'),
                'label' => $current->format('g:i A'),
                'period' => 'morning',
                'full_datetime' => $current->format('Y-m-d H:i:s')
            ];
            $current->addHour();
        }

        // Afternoon slots: 13:30, 14:30, 15:30, 16:30 (4 slots with 1-hour intervals)
        $afternoonStart = Carbon::parse($date . ' ' . self::CLINIC_AFTERNOON_START);
        $afternoonEnd = Carbon::parse($date . ' ' . self::CLINIC_AFTERNOON_END);
        
        $current = $afternoonStart->copy();
        while ($current->lt($afternoonEnd)) {
            $slots[] = [
                'value' => $current->format('H:i:s'),
                'label' => $current->format('g:i A'),
                'period' => 'afternoon',
                'full_datetime' => $current->format('Y-m-d H:i:s')
            ];
            $current->addHour();
        }

        // Get booked times for this date and nurse
        $bookedQuery = self::where('appointment_date', $date)
            ->whereIn('status', [
                self::STATUS_PENDING, 
                self::STATUS_CONFIRMED, 
                self::STATUS_RESCHEDULED
            ])
            ->whereNotNull('appointment_time');

        // Filter by nurse if specified
        if ($nurseId !== null) {
            $bookedQuery->where(function($q) use ($nurseId) {
                $q->whereNull('nurse_id')
                  ->orWhere('nurse_id', $nurseId);
            });
        }

        // Exclude current appointment if editing
        if ($excludeAppointmentId !== null) {
            $bookedQuery->where('id', '!=', $excludeAppointmentId);
        }

        $bookedAppointments = $bookedQuery->get();
        $bookedTimes = $bookedAppointments->pluck('appointment_time')->map(function($time) {
            try {
                return Carbon::parse($time)->format('H:i:s');
            } catch (\Exception $e) {
                Log::warning('Invalid appointment_time format', ['time' => $time]);
                return null;
            }
        })->filter()->toArray();

        // Add availability info to each slot
        $availableSlots = array_map(function($slot) use ($bookedTimes) {
            $isAvailable = !in_array($slot['value'], $bookedTimes);
            return array_merge($slot, [
                'is_available' => $isAvailable,
                'is_booked' => !$isAvailable
            ]);
        }, $slots);

        // Return ALL slots with availability info
        return array_values($availableSlots);

    } catch (\Exception $e) {
        Log::error('Error getting available time slots', [
            'error' => $e->getMessage(),
            'date' => $date,
            'nurse_id' => $nurseId,
            'exclude_id' => $excludeAppointmentId,
            'trace' => $e->getTraceAsString()
        ]);
        return [];
    }
}

    /**
     * Cancel appointment with reason
     */
    public function cancel($userId, string $reason): bool
    {
        if (!$this->canBeCancelled()) {
            Log::warning('Cannot cancel appointment', [
                'appointment_id' => $this->id,
                'status' => $this->status,
                'date' => $this->appointment_date?->format('Y-m-d'),
                'reason' => 'Not cancellable'
            ]);
            return false;
        }

        try {
            $success = $this->update([
                'status' => self::STATUS_CANCELLED,
                'cancelled_by' => $userId,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'updated_by' => $userId,
                'notes' => ($this->notes ?? '') . "\n\n" . 
                          now()->format('M j, Y g:i A') . " - Cancelled by " . 
                          ($userId == auth()->id() ? 'user' : 'staff') . 
                          ": " . Str::limit($reason, 200)
            ]);

            if ($success) {
                Log::info('Appointment cancelled successfully', [
                    'appointment_id' => $this->id,
                    'cancelled_by' => $userId,
                    'reason' => Str::limit($reason, 100),
                    'date' => $this->appointment_date?->format('Y-m-d H:i:s')
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Error cancelling appointment', [
                'appointment_id' => $this->id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Reject appointment with reason
     */
    public function reject($userId, string $reason): bool
    {
        if (!$this->isPending()) {
            Log::warning('Cannot reject appointment - not pending', [
                'appointment_id' => $this->id,
                'status' => $this->status,
                'reason' => 'Not pending'
            ]);
            return false;
        }

        try {
            $success = $this->update([
                'status' => self::STATUS_REJECTED,
                'rejected_by' => $userId,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
                'updated_by' => $userId,
                'notes' => ($this->notes ?? '') . "\n\n" . 
                          now()->format('M j, Y g:i A') . " - Rejected by staff: " . 
                          Str::limit($reason, 200)
            ]);

            if ($success) {
                Log::info('Appointment rejected successfully', [
                    'appointment_id' => $this->id,
                    'rejected_by' => $userId,
                    'reason' => Str::limit($reason, 100),
                    'date' => $this->appointment_date?->format('Y-m-d H:i:s')
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Error rejecting appointment', [
                'appointment_id' => $this->id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Complete appointment
     */
    public function complete($userId): bool
    {
        if (!$this->canBeCompleted()) {
            Log::warning('Cannot complete appointment', [
                'appointment_id' => $this->id,
                'status' => $this->status,
                'date' => $this->appointment_date?->format('Y-m-d'),
                'reason' => 'Not completable'
            ]);
            return false;
        }

        try {
            $success = $this->update([
                'status' => self::STATUS_COMPLETED,
                'completed_by' => $userId,
                'completed_at' => now(),
                'updated_by' => $userId,
                'notes' => ($this->notes ?? '') . "\n\n" . 
                          now()->format('M j, Y g:i A') . " - Completed by staff"
            ]);

            if ($success) {
                Log::info('Appointment completed successfully', [
                    'appointment_id' => $this->id,
                    'completed_by' => $userId,
                    'date' => now()->format('Y-m-d H:i:s')
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Error completing appointment', [
                'appointment_id' => $this->id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Confirm appointment
     */
    public function confirm($nurseId, ?string $newTime = null): bool
    {
        if (!$this->isPending()) {
            Log::warning('Cannot confirm appointment - not pending', [
                'appointment_id' => $this->id,
                'status' => $this->status,
                'reason' => 'Not pending'
            ]);
            return false;
        }

        try {
            $data = [
                'status' => self::STATUS_CONFIRMED,
                'nurse_id' => $nurseId,
                'accepted_by' => $nurseId,
                'accepted_at' => now(),
                'updated_by' => $nurseId,
                'notes' => ($this->notes ?? '') . "\n\n" . 
                          now()->format('M j, Y g:i A') . " - Confirmed and assigned to nurse"
            ];

            // Update time if new time is provided and different from current
            if ($newTime && $newTime !== $this->appointment_time) {
                $data['appointment_time'] = $newTime;
                $data['notes'] .= "\nTime changed to " . Carbon::parse($newTime)->format('g:i A');
            }

            $success = $this->update($data);

            if ($success) {
                Log::info('Appointment confirmed successfully', [
                    'appointment_id' => $this->id,
                    'nurse_id' => $nurseId,
                    'new_time' => $newTime,
                    'date' => $this->appointment_date?->format('Y-m-d H:i:s')
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Error confirming appointment', [
                'appointment_id' => $this->id,
                'nurse_id' => $nurseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Reschedule appointment
     */
    public function reschedule($nurseId, string $newDate, string $newTime, string $reason, bool $requiresStudentConfirmation = false): bool
    {
        if (!$this->canBeRescheduledByNurse()) {
            Log::warning('Cannot reschedule appointment', [
                'appointment_id' => $this->id,
                'status' => $this->status,
                'reason' => 'Not reschedulable by nurse'
            ]);
            return false;
        }

        try {
            // Validate new time is within clinic hours
            if (!self::isWithinClinicHours($newTime)) {
                Log::warning('Reschedule time outside clinic hours', [
                    'appointment_id' => $this->id,
                    'new_time' => $newTime
                ]);
                return false;
            }

            $newDateCarbon = Carbon::parse($newDate);
            if ($newDateCarbon->isWeekend()) {
                Log::warning('Cannot reschedule to weekend', [
                    'appointment_id' => $this->id,
                    'new_date' => $newDate
                ]);
                return false;
            }

            $oldDate = $this->appointment_date->format('M j, Y');
            $oldTime = $this->appointment_time;
            $newDateFormatted = $newDateCarbon->format('M j, Y');

            $status = $requiresStudentConfirmation ? self::STATUS_RESCHEDULED : self::STATUS_CONFIRMED;

            $success = $this->update([
                'appointment_date' => $newDate,
                'appointment_time' => $newTime,
                'status' => $status,
                'rescheduled_by' => $nurseId,
                'rescheduled_at' => now(),
                'reschedule_reason' => $reason,
                'requires_student_confirmation' => $requiresStudentConfirmation,
                'updated_by' => $nurseId,
                'notes' => ($this->notes ?? '') . "\n\n" . 
                          now()->format('M j, Y g:i A') . " - Rescheduled by nurse:\n" .
                          "From: {$oldDate} at {$oldTime}\n" .
                          "To: {$newDateFormatted} at " . Carbon::parse($newTime)->format('g:i A') . "\n" .
                          "Reason: " . Str::limit($reason, 200) . "\n" .
                          ($requiresStudentConfirmation ? "Awaiting student confirmation\n" : "")
            ]);

            if ($success) {
                Log::info('Appointment rescheduled successfully', [
                    'appointment_id' => $this->id,
                    'nurse_id' => $nurseId,
                    'old_date' => $oldDate,
                    'old_time' => $oldTime,
                    'new_date' => $newDate,
                    'new_time' => $newTime,
                    'reason' => Str::limit($reason, 100),
                    'requires_confirmation' => $requiresStudentConfirmation
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Error rescheduling appointment', [
                'appointment_id' => $this->id,
                'nurse_id' => $nurseId,
                'new_date' => $newDate,
                'new_time' => $newTime,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Mark appointment as ready for consultation
     */
    public function markAsReady($nurseId): bool
    {
        if (!$this->isConfirmed()) {
            return false;
        }

        try {
            return $this->update([
                'status' => self::STATUS_FOLLOW_UP_PENDING,
                'updated_by' => $nurseId,
                'notes' => ($this->notes ?? '') . "\n\n" . 
                          now()->format('M j, Y g:i A') . " - Marked as ready for consultation"
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking appointment as ready', [
                'appointment_id' => $this->id,
                'nurse_id' => $nurseId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // ============= TIME & DATE HELPERS =============

    public function getTimeUntilAppointment(): ?string
    {
        if (!$this->appointment_date || !$this->appointment_time) {
            return null;
        }

        try {
            $appointmentDateTime = Carbon::parse($this->appointment_date->format('Y-m-d') . ' ' . $this->appointment_time);
            return $appointmentDateTime->diffForHumans();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function isToday(): bool
    {
        return $this->appointment_date && $this->appointment_date->isToday();
    }

    public function isTomorrow(): bool
    {
        return $this->appointment_date && $this->appointment_date->isTomorrow();
    }

    public function isThisWeek(): bool
    {
        if (!$this->appointment_date) return false;
        
        return $this->appointment_date->between(
            now()->startOfWeek(),
            now()->endOfWeek()
        );
    }

    /**
     * Check if appointment is within the next X days
     */
    public function isWithinDays(int $days): bool
    {
        if (!$this->appointment_date) return false;
        
        return $this->appointment_date->between(
            now(),
            now()->addDays($days)
        );
    }

    // ============= VALIDATION HELPER METHODS =============

    /**
     * Validate if appointment time slot is available
     */
    public function isTimeSlotAvailable(): bool
    {
        if (!$this->appointment_date || !$this->appointment_time) {
            return false;
        }

        return !self::where('appointment_date', $this->appointment_date)
            ->where('appointment_time', $this->appointment_time)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED])
            ->where('id', '!=', $this->id)
            ->exists();
    }

    // ============= SCOPES =============

    public function scopePending($query) 
    { 
        return $query->where('status', self::STATUS_PENDING); 
    }

    public function scopeConfirmed($query) 
    { 
        return $query->where('status', self::STATUS_CONFIRMED); 
    }

    public function scopeCompleted($query) 
    { 
        return $query->where('status', self::STATUS_COMPLETED); 
    }

    public function scopeCancelled($query) 
    { 
        return $query->where('status', self::STATUS_CANCELLED); 
    }

    public function scopeRejected($query) 
    { 
        return $query->where('status', self::STATUS_REJECTED); 
    }

    public function scopeRescheduleRequested($query) 
    { 
        return $query->where('status', self::STATUS_RESCHEDULE_REQUESTED); 
    }

    public function scopeToday($query) 
    { 
        return $query->whereDate('appointment_date', today()); 
    }

    public function scopeUpcoming($query) 
    { 
        return $query->where('appointment_date', '>=', today())
                     ->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_PENDING]); 
    }

    public function scopeOverdue($query)
    {
        return $query->where(function ($q) {
            $q->where('appointment_date', '<', today())
              ->orWhere(function ($q) {
                  $q->whereDate('appointment_date', today())
                    ->whereTime('appointment_time', '<', now()->format('H:i'));
              });
        })->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_PENDING]);
    }

    public function scopeUrgent($query)
    {
        return $query->where(function($q) {
            $q->where('is_urgent', true)
              ->orWhere('priority', '>=', self::PRIORITY_HIGH);
        });
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('user_id', $studentId);
    }

    public function scopeForNurse($query, $nurseId)
    {
        return $query->where('nurse_id', $nurseId);
    }

    public function scopeRequiringAction($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_RESCHEDULE_REQUESTED,
            self::STATUS_FOLLOW_UP_PENDING
        ]);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('appointment_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeNextWeek($query)
    {
        return $query->whereBetween('appointment_date', [
            now()->addWeek()->startOfWeek(),
            now()->addWeek()->endOfWeek()
        ]);
    }

    public function scopeWalkIn($query)
    {
        return $query->whereIn('appointment_type', [self::TYPE_WALK_IN, self::TYPE_EMERGENCY]);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>=', self::PRIORITY_HIGH);
    }

    public function scopeForConsultation($query)
    {
        return $query->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_RESCHEDULED])
                     ->whereDate('appointment_date', today());
    }

    // ============= STATIC HELPER METHODS =============

    /**
     * Get all available status options for dropdowns/filters
     */
    /**
 * Get status options for dropdowns
 */
public static function getStatusOptions(): array
{
    return [
        self::STATUS_PENDING => 'Pending Review',
        self::STATUS_CONFIRMED => 'Confirmed',
        self::STATUS_RESCHEDULED => 'Rescheduled',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_FOLLOW_UP_PENDING => 'Follow-up Pending',
        self::STATUS_RESCHEDULE_REQUESTED => 'Reschedule Requested',
        self::STATUS_REJECTED => 'Rejected',
    ];
}

    /**
     * Get priority options for dropdowns/filters
     */
    public static function getPriorityOptions(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
            self::PRIORITY_EMERGENCY => 'Emergency',
        ];
    }

    /**
     * Get appointment type options for dropdowns/filters
     */
    public static function getTypeOptions(): array
    {
        return [
            self::TYPE_SCHEDULED => 'Scheduled',
            self::TYPE_WALK_IN => 'Walk-in',
            self::TYPE_FOLLOW_UP => 'Follow-up',
            self::TYPE_EMERGENCY => 'Emergency',
        ];
    }

   /**
 * Get available dates for appointment creation
 */
public static function getAvailableDates(int $daysAhead = 30, string $preferredTime = 'any', ?int $nurseId = null): array
{
    $dates = [];
    $startDate = today();
    $endDate = today()->addDays($daysAhead);

    $date = $startDate->copy();
    while ($date->lte($endDate)) {
        if (!$date->isWeekend() && $date->gte(today())) {
            $allSlots = self::getAvailableTimeSlots($date->format('Y-m-d'), $nurseId);
            
            // Filter to only available slots
            $availableSlots = array_filter($allSlots, function($slot) {
                return $slot['is_available'];
            });
            
            // Further filter by preferred time if specified
            if ($preferredTime !== 'any') {
                $availableSlots = array_filter($availableSlots, function($slot) use ($preferredTime) {
                    return $slot['period'] === $preferredTime;
                });
            }

            if (count($availableSlots) > 0) {
                $dates[] = [
                    'date' => $date->format('Y-m-d'),
                    'formatted' => $date->format('M d, Y'),
                    'formatted_full' => $date->format('M d, Y (l)'),
                    'is_today' => $date->isToday(),
                    'day_name' => $date->format('l'),
                    'day_number' => $date->day,
                    'available_slots' => count($availableSlots),
                    'total_slots' => count($allSlots),
                    'slots' => array_values($availableSlots),
                    'has_morning_slots' => collect($availableSlots)->contains('period', 'morning'),
                    'has_afternoon_slots' => collect($availableSlots)->contains('period', 'afternoon'),
                ];
            }
        }
        $date->addDay();
    }

    return $dates;
}

    // ============= EVENT HOOKS =============

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            // Auto-set priority based on urgency and type
            if ($appointment->is_urgent && $appointment->priority === self::PRIORITY_NORMAL) {
                $appointment->priority = self::PRIORITY_HIGH;
            }

            if ($appointment->appointment_type === self::TYPE_EMERGENCY) {
                $appointment->priority = self::PRIORITY_EMERGENCY;
                $appointment->is_urgent = true;
            }

            // Set default nurse_id to null if not specified
            if (!$appointment->nurse_id) {
                $appointment->nurse_id = null;
            }
        });

        static::created(function ($appointment) {
            Log::info('Appointment created', [
                'appointment_id' => $appointment->id,
                'user_id' => $appointment->user_id,
                'nurse_id' => $appointment->nurse_id,
                'status' => $appointment->status,
                'type' => $appointment->appointment_type,
                'priority' => $appointment->priority,
                'is_urgent' => $appointment->is_urgent
            ]);
        });

        static::updating(function ($appointment) {
            // Prevent changing status to completed if not eligible
            if ($appointment->isDirty('status') && 
                $appointment->status === self::STATUS_COMPLETED && 
                !$appointment->canBeCompleted()) {
                throw new \Exception('Appointment cannot be marked as completed at this time');
            }

            // Auto-update priority if urgency changes
            if ($appointment->isDirty('is_urgent') || $appointment->isDirty('appointment_type')) {
                if ($appointment->is_urgent && $appointment->priority < self::PRIORITY_HIGH) {
                    $appointment->priority = self::PRIORITY_HIGH;
                }
                if ($appointment->appointment_type === self::TYPE_EMERGENCY) {
                    $appointment->priority = self::PRIORITY_EMERGENCY;
                    $appointment->is_urgent = true;
                }
            }
        });

        static::updated(function ($appointment) {
            if ($appointment->wasChanged('status')) {
                Log::info('Appointment status changed', [
                    'appointment_id' => $appointment->id,
                    'old_status' => $appointment->getOriginal('status'),
                    'new_status' => $appointment->status,
                    'changed_by' => $appointment->updated_by,
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ]);
            }

            if ($appointment->wasChanged(['appointment_date', 'appointment_time'])) {
                Log::info('Appointment time changed', [
                    'appointment_id' => $appointment->id,
                    'old_date' => $appointment->getOriginal('appointment_date'),
                    'old_time' => $appointment->getOriginal('appointment_time'),
                    'new_date' => $appointment->appointment_date,
                    'new_time' => $appointment->appointment_time,
                    'changed_by' => $appointment->updated_by
                ]);
            }
        });

        static::deleting(function ($appointment) {
            // Prevent deletion of completed appointments
            if ($appointment->isCompleted()) {
                throw new \Exception('Completed appointments cannot be deleted');
            }

            // Log deletion attempt
            Log::info('Appointment deletion attempted', [
                'appointment_id' => $appointment->id,
                'status' => $appointment->status,
                'user_id' => auth()->id()
            ]);
        });
    }
}