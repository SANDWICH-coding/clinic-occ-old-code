<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\User;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\SymptomLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ConsultationService
{
    /**
     * Create a new consultation
     */
    public function createConsultation(User $student, User $nurse, array $data, bool $isWalkIn = false): Consultation
    {
        if ($student->role !== 'student') {
            throw new \InvalidArgumentException('User must be a student to create a consultation.');
        }

        if (!$nurse->isNurse()) {
            throw new \InvalidArgumentException('Only nurses can create consultations.');
        }

        // Check if student already has an active consultation
        if ($student->hasActiveConsultation()) {
            throw new \Exception('Student already has an active consultation.');
        }

        return DB::transaction(function () use ($student, $nurse, $data, $isWalkIn) {
            // Determine consultation type
            $type = $isWalkIn ? Consultation::TYPE_WALK_IN : ($data['type'] ?? Consultation::TYPE_SCHEDULED);

            // Create the consultation
            $consultation = Consultation::create([
                'student_id' => $student->id,
                'nurse_id' => $nurse->id,
                'type' => $type,
                'status' => Consultation::STATUS_REGISTERED,
                'priority' => $data['priority'] ?? Consultation::PRIORITY_NORMAL,
                'chief_complaint' => $data['chief_complaint'],
                'symptoms_description' => $data['symptoms_description'] ?? null,
                'pain_level' => $data['pain_level'] ?? null,
                'consultation_notes' => $data['initial_notes'] ?? $data['notes'] ?? null,
                'registered_at' => now(),
                'appointment_id' => $data['appointment_id'] ?? null,
            ]);

            // Update vital signs if provided
            if (!empty($data['vital_signs'])) {
                $this->updateVitalSigns($consultation, $data['vital_signs']);
            }

            // Set queue position and reorder queue
            $consultation->reorderQueue();

            // Auto-trigger parent notification for emergency cases
            if ($consultation->isEmergency()) {
                $consultation->triggerParentNotification('Emergency priority consultation registered');
            }

            // Log the creation
            Log::info('Consultation created', [
                'consultation_id' => $consultation->id,
                'student_id' => $student->id,
                'nurse_id' => $nurse->id,
                'type' => $type,
                'priority' => $consultation->priority,
                'is_walk_in' => $isWalkIn,
            ]);

            return $consultation;
        });
    }

    /**
     * Update vital signs for a consultation
     */
    public function updateVitalSigns(Consultation $consultation, array $vitalSigns, ?string $notes = null): bool
    {
        // Validate vital signs
        $errors = $consultation->validateVitalSigns($vitalSigns);
        
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Invalid vital signs: ' . implode(', ', $errors));
        }

        // Prepare update data
        $updateData = array_filter([
            'temperature' => $vitalSigns['temperature'] ?? null,
            'blood_pressure_systolic' => $vitalSigns['blood_pressure_systolic'] ?? null,
            'blood_pressure_diastolic' => $vitalSigns['blood_pressure_diastolic'] ?? null,
            'heart_rate' => $vitalSigns['heart_rate'] ?? null,
            'respiratory_rate' => $vitalSigns['respiratory_rate'] ?? null,
            'oxygen_saturation' => $vitalSigns['oxygen_saturation'] ?? null,
            'weight' => $vitalSigns['weight'] ?? null,
            'height' => $vitalSigns['height'] ?? null,
            'vital_notes' => $notes,
        ], fn($value) => $value !== null && $value !== '');

        if (empty($updateData)) {
            return false;
        }

        // Update consultation
        $consultation->update($updateData);

        // Update student's medical record with current weight/height if provided
        if (isset($updateData['weight']) || isset($updateData['height'])) {
            $this->updateStudentMedicalRecord($consultation->student, $updateData);
        }

        // Log vital signs update
        Log::info('Vital signs updated', [
            'consultation_id' => $consultation->id,
            'updated_by' => Auth::id(),
            'vital_signs' => $updateData,
        ]);

        return true;
    }

    /**
     * Start a consultation
     */
    public function startConsultation(Consultation $consultation): bool
    {
        if (!$consultation->canStart()) {
            throw new \Exception('Consultation cannot be started. Current status: ' . $consultation->status);
        }

        return DB::transaction(function () use ($consultation) {
            $consultation->update([
                'status' => Consultation::STATUS_IN_PROGRESS,
                'consultation_started_at' => now(),
                'nurse_id' => Auth::id() ?? $consultation->nurse_id,
            ]);

            $consultation->calculateWaitTime();

            Log::info('Consultation started', [
                'consultation_id' => $consultation->id,
                'started_by' => Auth::id(),
                'wait_time' => $consultation->wait_time_minutes,
            ]);

            return true;
        });
    }

    /**
     * Complete a consultation
     */
    public function completeConsultation(Consultation $consultation, array $data): bool
    {
        if (!$consultation->canComplete()) {
            throw new \Exception('Consultation cannot be completed. Current status: ' . $consultation->status);
        }

        return DB::transaction(function () use ($consultation, $data) {
            // Update consultation with completion data
            $updateData = array_merge($data, [
                'status' => Consultation::STATUS_COMPLETED,
                'consultation_ended_at' => now(),
            ]);

            // Handle referral status
            if ($data['referral_issued'] ?? false) {
                $updateData['status'] = Consultation::STATUS_REFERRED;
            }

            $consultation->update($updateData);
            $consultation->calculateConsultationDuration();

            // Handle follow-up scheduling
            if ($data['follow_up_required'] ?? false) {
                $this->scheduleFollowUp($consultation, $data);
            }

            // Handle parent notifications
            if (($data['parent_notified'] ?? false) && !$consultation->parent_notified) {
                $consultation->triggerParentNotification(
                    'Consultation completed: ' . ($data['parent_communication_notes'] ?? '')
                );
            }

            // Handle referrals
            if ($data['referral_issued'] ?? false) {
                $this->processReferral($consultation, $data);
            }

            Log::info('Consultation completed', [
                'consultation_id' => $consultation->id,
                'completed_by' => Auth::id(),
                'outcome' => $data['outcome'] ?? null,
                'duration' => $consultation->consultation_duration_minutes,
                'referral_issued' => $data['referral_issued'] ?? false,
                'follow_up_required' => $data['follow_up_required'] ?? false,
            ]);

            return true;
        });
    }

    /**
     * Cancel a consultation
     */
    public function cancelConsultation(Consultation $consultation, string $reason): bool
    {
        if (!$consultation->canCancel()) {
            throw new \Exception('Consultation cannot be cancelled. Current status: ' . $consultation->status);
        }

        return DB::transaction(function () use ($consultation, $reason) {
            $consultation->update([
                'status' => Consultation::STATUS_CANCELLED,
                'consultation_notes' => ($consultation->consultation_notes ? $consultation->consultation_notes . "\n\n" : '') .
                    "CANCELLED: $reason",
            ]);

            $consultation->reorderQueue();

            Log::info('Consultation cancelled', [
                'consultation_id' => $consultation->id,
                'cancelled_by' => Auth::id(),
                'reason' => $reason,
            ]);

            return true;
        });
    }

    /**
     * Update consultation priority
     */
    public function updatePriority(Consultation $consultation, string $priority): bool
    {
        if (!array_key_exists($priority, Consultation::PRIORITY)) {
            throw new \InvalidArgumentException('Invalid priority level.');
        }

        $oldPriority = $consultation->priority;
        
        return DB::transaction(function () use ($consultation, $priority, $oldPriority) {
            $consultation->update(['priority' => $priority]);
            $consultation->reorderQueue();

            // Auto-notify parent for emergency cases
            if ($priority === Consultation::PRIORITY_EMERGENCY && !$consultation->parent_notified) {
                $consultation->triggerParentNotification('Consultation priority upgraded to emergency');
            }

            Log::info('Consultation priority updated', [
                'consultation_id' => $consultation->id,
                'updated_by' => Auth::id(),
                'old_priority' => $oldPriority,
                'new_priority' => $priority,
            ]);

            return true;
        });
    }

    /**
     * Mark consultation as ready
     */
    public function markReady(Consultation $consultation): bool
    {
        if (!$consultation->canMarkReady()) {
            throw new \Exception('Consultation cannot be marked as ready. Current status: ' . $consultation->status);
        }

        return DB::transaction(function () use ($consultation) {
            $consultation->update([
                'ready_for_consultation' => true,
                'marked_ready_at' => now(),
                'status' => Consultation::STATUS_WAITING,
            ]);

            $consultation->reorderQueue();

            Log::info('Consultation marked ready', [
                'consultation_id' => $consultation->id,
                'marked_by' => Auth::id(),
            ]);

            return true;
        });
    }

    /**
     * Process referral for a consultation
     */
    protected function processReferral(Consultation $consultation, array $data): void
    {
        // Trigger parent notification if not already done
        if (!$consultation->parent_notified) {
            $consultation->triggerParentNotification(
                'Referral issued to ' . ($data['referred_to'] ?? 'external healthcare provider')
            );
        }

        // Log referral details
        Log::info('Referral processed', [
            'consultation_id' => $consultation->id,
            'referred_to' => $data['referred_to'] ?? null,
            'referral_reason' => $data['referral_reason'] ?? null,
            'referral_urgency' => $data['referral_urgency'] ?? 'normal',
            'processed_by' => Auth::id(),
        ]);
    }

    /**
     * Schedule follow-up for a consultation
     */
    protected function scheduleFollowUp(Consultation $consultation, array $data): void
    {
        if (empty($data['recommended_follow_up_date'])) {
            return;
        }

        try {
            $followUpDate = Carbon::parse($data['recommended_follow_up_date']);
            
            // Create follow-up appointment if date is provided
            Appointment::create([
                'user_id' => $consultation->student_id,
                'appointment_date' => $followUpDate,
                'reason' => 'Follow-up for: ' . ($consultation->diagnosis ?? $consultation->chief_complaint),
                'notes' => $data['follow_up_instructions'] ?? 'Follow-up consultation recommended',
                'status' => Appointment::STATUS_FOLLOW_UP_PENDING,
                'is_follow_up' => true,
                'created_by_nurse' => $consultation->nurse_id,
                'related_consultation_id' => $consultation->id,
            ]);

            Log::info('Follow-up appointment scheduled', [
                'consultation_id' => $consultation->id,
                'follow_up_date' => $followUpDate->toDateString(),
                'scheduled_by' => Auth::id(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to schedule follow-up appointment', [
                'consultation_id' => $consultation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update student medical record with consultation data
     */
    protected function updateStudentMedicalRecord(User $student, array $data): void
    {
        $medicalRecord = $student->medicalRecord;

        try {
            if (!$medicalRecord) {
                // Create new medical record if none exists
                MedicalRecord::create([
                    'user_id' => $student->id,
                    'weight' => $data['weight'] ?? null,
                    'height' => $data['height'] ?? null,
                    'created_by' => Auth::id(),
                    'last_updated_by' => Auth::id(),
                ]);
            } else {
                // Update existing record with new vital measurements
                $updateData = array_filter([
                    'weight' => $data['weight'] ?? null,
                    'height' => $data['height'] ?? null,
                    'last_updated_by' => Auth::id(),
                ], fn($value) => $value !== null);

                if (!empty($updateData)) {
                    $medicalRecord->update($updateData);
                }
            }

            Log::info('Student medical record updated from consultation', [
                'student_id' => $student->id,
                'updated_fields' => array_keys($updateData ?? []),
                'updated_by' => Auth::id(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to update student medical record', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get consultation statistics for a given period
     */
    public function getConsultationStats(Carbon $startDate, Carbon $endDate): array
    {
        $query = Consultation::whereBetween('created_at', [$startDate, $endDate]);

        return [
            'total_consultations' => $query->count(),
            'completed_consultations' => $query->clone()->where('status', Consultation::STATUS_COMPLETED)->count(),
            'walk_in_consultations' => $query->clone()->where('type', Consultation::TYPE_WALK_IN)->count(),
            'emergency_consultations' => $query->clone()->where('priority', Consultation::PRIORITY_EMERGENCY)->count(),
            'referrals_issued' => $query->clone()->where('referral_issued', true)->count(),
            'follow_ups_required' => $query->clone()->where('follow_up_required', true)->count(),
            'average_wait_time' => $query->clone()->whereNotNull('wait_time_minutes')->avg('wait_time_minutes'),
            'average_consultation_time' => $query->clone()->whereNotNull('consultation_duration_minutes')->avg('consultation_duration_minutes'),
            'status_breakdown' => $query->clone()->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status')->toArray(),
            'priority_breakdown' => $query->clone()->selectRaw('priority, COUNT(*) as count')->groupBy('priority')->pluck('count', 'priority')->toArray(),
        ];
    }

    /**
     * Get queue statistics
     */
    public function getQueueStats(): array
    {
        $today = Carbon::today();

        return [
            'waiting_count' => Consultation::waiting()->whereDate('created_at', $today)->count(),
            'in_progress_count' => Consultation::inProgress()->whereDate('created_at', $today)->count(),
            'ready_count' => Consultation::readyForConsultation()->whereDate('created_at', $today)->count(),
            'emergency_waiting' => Consultation::waiting()->emergency()->whereDate('created_at', $today)->count(),
            'average_queue_wait_time' => $this->calculateAverageQueueWaitTime(),
            'longest_waiting_time' => $this->getLongestWaitingTime(),
            'queue_positions' => $this->getQueuePositions(),
        ];
    }

    /**
     * Calculate average queue wait time
     */
    protected function calculateAverageQueueWaitTime(): ?float
    {
        return Consultation::waiting()
            ->whereDate('created_at', Carbon::today())
            ->whereNotNull('registered_at')
            ->get()
            ->map(function ($consultation) {
                return $consultation->registered_at->diffInMinutes(now());
            })
            ->avg();
    }

    /**
     * Get longest waiting time in queue
     */
    protected function getLongestWaitingTime(): ?int
    {
        $longestWaiting = Consultation::waiting()
            ->whereDate('created_at', Carbon::today())
            ->whereNotNull('registered_at')
            ->orderBy('registered_at', 'asc')
            ->first();

        return $longestWaiting ? $longestWaiting->registered_at->diffInMinutes(now()) : null;
    }

    /**
     * Get queue positions for waiting consultations
     */
    protected function getQueuePositions(): array
    {
        return Consultation::waitingQueue()
            ->whereDate('created_at', Carbon::today())
            ->get()
            ->map(function ($consultation, $index) {
                return [
                    'consultation_id' => $consultation->id,
                    'student_name' => $consultation->getPatientName(),
                    'priority' => $consultation->priority,
                    'position' => $index + 1,
                    'wait_time' => $consultation->registered_at->diffInMinutes(now()),
                    'estimated_time' => $consultation->getEstimatedWaitTime(),
                ];
            })
            ->toArray();
    }

    /**
     * Bulk update consultation priorities
     */
    public function bulkUpdatePriorities(array $consultationPriorities): bool
    {
        return DB::transaction(function () use ($consultationPriorities) {
            foreach ($consultationPriorities as $consultationId => $priority) {
                $consultation = Consultation::find($consultationId);
                
                if ($consultation && array_key_exists($priority, Consultation::PRIORITY)) {
                    $consultation->updatePriority($priority);
                }
            }

            // Reorder queue after bulk updates
            $consultation = new Consultation();
            $consultation->reorderQueue();

            Log::info('Bulk priority update completed', [
                'updated_consultations' => array_keys($consultationPriorities),
                'updated_by' => Auth::id(),
            ]);

            return true;
        });
    }

    /**
     * Transfer consultation to another nurse
     */
    public function transferConsultation(Consultation $consultation, User $newNurse): bool
    {
        if (!$newNurse->isNurse()) {
            throw new \InvalidArgumentException('Transfer target must be a nurse.');
        }

        if ($consultation->isCompleted() || $consultation->isCancelled()) {
            throw new \Exception('Cannot transfer completed or cancelled consultations.');
        }

        return DB::transaction(function () use ($consultation, $newNurse) {
            $oldNurseId = $consultation->nurse_id;
            
            $consultation->update([
                'nurse_id' => $newNurse->id,
                'consultation_notes' => ($consultation->consultation_notes ? $consultation->consultation_notes . "\n\n" : '') .
                    "Transferred from nurse ID {$oldNurseId} to {$newNurse->id} on " . now()->format('Y-m-d H:i:s'),
            ]);

            Log::info('Consultation transferred', [
                'consultation_id' => $consultation->id,
                'from_nurse_id' => $oldNurseId,
                'to_nurse_id' => $newNurse->id,
                'transferred_by' => Auth::id(),
            ]);

            return true;
        });
    }

    /**
     * Generate consultation report
     */
    public function generateConsultationReport(Consultation $consultation): array
    {
        return [
            'consultation_summary' => $consultation->getConsultationSummary(),
            'medical_history' => $consultation->getMedicalHistory(),
            'symptom_analysis' => $consultation->getSymptomAnalysis(),
            'vital_signs_summary' => $consultation->getVitalSignsSummary(),
            'vital_signs_comparison' => $consultation->getFormattedVitalSignsComparison(),
            'treatment_summary' => [
                'diagnosis' => $consultation->diagnosis,
                'treatment_provided' => $consultation->treatment_provided,
                'medications_given' => $consultation->medications_given,
                'procedures_performed' => $consultation->procedures_performed,
                'home_care_instructions' => $consultation->home_care_instructions,
            ],
            'follow_up_plan' => [
                'required' => $consultation->follow_up_required,
                'instructions' => $consultation->follow_up_instructions,
                'recommended_date' => $consultation->recommended_follow_up_date?->format('Y-m-d'),
                'type' => $consultation->follow_up_type,
            ],
            'referral_details' => $consultation->referral_issued ? [
                'referred_to' => $consultation->referred_to,
                'reason' => $consultation->referral_reason,
                'notes' => $consultation->referral_notes,
                'urgency' => $consultation->referral_urgency,
            ] : null,
            'special_notes' => $consultation->requiresSpecialAttention(),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => Auth::user()?->full_name ?? 'System',
        ];
    }

    /**
     * Validate consultation data before operations
     */
    public function validateConsultationData(array $data, string $operation = 'create'): array
    {
        $errors = [];

        // Basic validation rules based on operation
        switch ($operation) {
            case 'create':
                if (empty($data['chief_complaint'])) {
                    $errors[] = 'Chief complaint is required';
                }
                if (empty($data['student_id'])) {
                    $errors[] = 'Student ID is required';
                }
                break;

            case 'complete':
                if (empty($data['diagnosis'])) {
                    $errors[] = 'Diagnosis is required to complete consultation';
                }
                if (empty($data['outcome'])) {
                    $errors[] = 'Outcome is required to complete consultation';
                }
                break;

            case 'vital_signs':
                if (!empty($data['vital_signs'])) {
                    $vitalErrors = $this->validateVitalSigns($data['vital_signs']);
                    $errors = array_merge($errors, $vitalErrors);
                }
                break;
        }

        // Validate referral data if referral is issued
        if (!empty($data['referral_issued']) && $data['referral_issued']) {
            if (empty($data['referred_to'])) {
                $errors[] = 'Referral destination is required when issuing referral';
            }
            if (empty($data['referral_reason'])) {
                $errors[] = 'Referral reason is required when issuing referral';
            }
        }

        // Validate follow-up data if follow-up is required
        if (!empty($data['follow_up_required']) && $data['follow_up_required']) {
            if (empty($data['follow_up_instructions'])) {
                $errors[] = 'Follow-up instructions are required when scheduling follow-up';
            }
        }

        return $errors;
    }

    /**
     * Validate vital signs data
     */
    protected function validateVitalSigns(array $vitalSigns): array
    {
        $errors = [];

        if (isset($vitalSigns['temperature'])) {
            $temp = floatval($vitalSigns['temperature']);
            if ($temp < 35.0 || $temp > 42.0) {
                $errors[] = 'Temperature must be between 35.0°C and 42.0°C';
            }
        }

        if (isset($vitalSigns['blood_pressure_systolic'])) {
            $systolic = intval($vitalSigns['blood_pressure_systolic']);
            if ($systolic < 70 || $systolic > 250) {
                $errors[] = 'Systolic blood pressure must be between 70 and 250 mmHg';
            }
        }

        if (isset($vitalSigns['blood_pressure_diastolic'])) {
            $diastolic = intval($vitalSigns['blood_pressure_diastolic']);
            if ($diastolic < 40 || $diastolic > 150) {
                $errors[] = 'Diastolic blood pressure must be between 40 and 150 mmHg';
            }
        }

        if (isset($vitalSigns['heart_rate'])) {
            $heartRate = intval($vitalSigns['heart_rate']);
            if ($heartRate < 30 || $heartRate > 200) {
                $errors[] = 'Heart rate must be between 30 and 200 BPM';
            }
        }

        if (isset($vitalSigns['respiratory_rate'])) {
            $respiratoryRate = intval($vitalSigns['respiratory_rate']);
            if ($respiratoryRate < 8 || $respiratoryRate > 40) {
                $errors[] = 'Respiratory rate must be between 8 and 40 per minute';
            }
        }

        if (isset($vitalSigns['oxygen_saturation'])) {
            $oxygenSat = floatval($vitalSigns['oxygen_saturation']);
            if ($oxygenSat < 70 || $oxygenSat > 100) {
                $errors[] = 'Oxygen saturation must be between 70% and 100%';
            }
        }

        if (isset($vitalSigns['weight'])) {
            $weight = floatval($vitalSigns['weight']);
            if ($weight < 20 || $weight > 300) {
                $errors[] = 'Weight must be between 20kg and 300kg';
            }
        }

        if (isset($vitalSigns['height'])) {
            $height = floatval($vitalSigns['height']);
            if ($height < 100 || $height > 250) {
                $errors[] = 'Height must be between 100cm and 250cm';
            }
        }

        return $errors;
    }

    /**
     * Get consultation metrics for reporting
     */
    public function getConsultationMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $consultations = Consultation::whereBetween('created_at', [$startDate, $endDate])->get();

        return [
            'total_consultations' => $consultations->count(),
            'completion_rate' => $consultations->whereIn('status', [
                Consultation::STATUS_COMPLETED, 
                Consultation::STATUS_REFERRED
            ])->count() / max($consultations->count(), 1) * 100,
            'average_wait_time' => $consultations->whereNotNull('wait_time_minutes')->avg('wait_time_minutes'),
            'average_consultation_duration' => $consultations->whereNotNull('consultation_duration_minutes')->avg('consultation_duration_minutes'),
            'emergency_response_time' => $consultations->where('priority', Consultation::PRIORITY_EMERGENCY)
                ->whereNotNull('wait_time_minutes')->avg('wait_time_minutes'),
            'referral_rate' => $consultations->where('referral_issued', true)->count() / max($consultations->count(), 1) * 100,
            'follow_up_rate' => $consultations->where('follow_up_required', true)->count() / max($consultations->count(), 1) * 100,
            'patient_satisfaction' => $consultations->whereNotNull('student_satisfaction')->avg('student_satisfaction'),
            'most_common_complaints' => $consultations->pluck('chief_complaint')
                ->filter()
                ->countBy()
                ->sortDesc()
                ->take(10)
                ->toArray(),
            'busiest_hours' => $consultations->pluck('created_at')
                ->groupBy(function ($date) {
                    return $date->format('H');
                })
                ->map->count()
                ->sortDesc()
                ->toArray(),
        ];
    }
}