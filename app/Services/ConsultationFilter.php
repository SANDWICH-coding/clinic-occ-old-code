<?php

namespace App\Services;

use App\Models\Consultation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ConsultationFilter
{
    protected Builder $query;
    protected array $allowedFilters = [
        'status',
        'type',
        'priority',
        'date_from',
        'date_to',
        'student_search',
        'nurse_id',
        'outcome',
        'referral_issued',
        'follow_up_required',
        'parent_pickup_required',
    ];

    protected array $allowedSorts = [
        'created_at',
        'registered_at',
        'consultation_started_at',
        'consultation_ended_at',
        'priority',
        'status',
        'wait_time_minutes',
        'consultation_duration_minutes',
    ];

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function apply(Request $request): self
    {
        $this->applyFilters($request);
        $this->applySearch($request);
        $this->applySorting($request);
        $this->applyIncludes();

        return $this;
    }

    public function getPaginated(int $perPage = 15)
    {
        return $this->query->paginate($perPage)->withQueryString();
    }

    public function get()
    {
        return $this->query->get();
    }

    protected function applyFilters(Request $request): void
    {
        // Status filter
        if ($request->filled('status') && in_array($request->status, array_keys(Consultation::STATUS))) {
            $this->query->where('status', $request->status);
        }

        // Type filter
        if ($request->filled('type') && in_array($request->type, array_keys(Consultation::TYPE))) {
            $this->query->where('type', $request->type);
        }

        // Priority filter
        if ($request->filled('priority') && in_array($request->priority, array_keys(Consultation::PRIORITY))) {
            $this->query->where('priority', $request->priority);
        }

        // Date range filters
        if ($request->filled('date_from')) {
            try {
                $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                $this->query->where('created_at', '>=', $dateFrom);
            } catch (\Exception $e) {
                // Invalid date format, skip filter
            }
        }

        if ($request->filled('date_to')) {
            try {
                $dateTo = Carbon::parse($request->date_to)->endOfDay();
                $this->query->where('created_at', '<=', $dateTo);
            } catch (\Exception $e) {
                // Invalid date format, skip filter
            }
        }

        // Nurse filter
        if ($request->filled('nurse_id')) {
            $this->query->where('nurse_id', $request->nurse_id);
        }

        // Outcome filter
        if ($request->filled('outcome') && in_array($request->outcome, array_keys(Consultation::OUTCOME))) {
            $this->query->where('outcome', $request->outcome);
        }

        // Boolean filters
        if ($request->filled('referral_issued')) {
            $this->query->where('referral_issued', $request->boolean('referral_issued'));
        }

        if ($request->filled('follow_up_required')) {
            $this->query->where('follow_up_required', $request->boolean('follow_up_required'));
        }

        if ($request->filled('parent_pickup_required')) {
            $this->query->where('parent_pickup_required', $request->boolean('parent_pickup_required'));
        }

        // Today filter (quick filter)
        if ($request->filled('today') && $request->boolean('today')) {
            $this->query->whereDate('created_at', Carbon::today());
        }

        // Emergency filter (quick filter)
        if ($request->filled('emergency') && $request->boolean('emergency')) {
            $this->query->where('priority', Consultation::PRIORITY_EMERGENCY);
        }

        // Waiting filter (quick filter)
        if ($request->filled('waiting') && $request->boolean('waiting')) {
            $this->query->where('status', Consultation::STATUS_WAITING);
        }

        // In progress filter (quick filter)
        if ($request->filled('in_progress') && $request->boolean('in_progress')) {
            $this->query->where('status', Consultation::STATUS_IN_PROGRESS);
        }

        // Completed today filter (quick filter)
        if ($request->filled('completed_today') && $request->boolean('completed_today')) {
            $this->query->where('status', Consultation::STATUS_COMPLETED)
                        ->whereDate('consultation_ended_at', Carbon::today());
        }

        // With vital signs filter
        if ($request->filled('with_vitals') && $request->boolean('with_vitals')) {
            $this->query->where(function($query) {
                $query->whereNotNull('temperature')
                      ->orWhereNotNull('blood_pressure_systolic')
                      ->orWhereNotNull('heart_rate')
                      ->orWhereNotNull('respiratory_rate')
                      ->orWhereNotNull('oxygen_saturation')
                      ->orWhereNotNull('weight')
                      ->orWhereNotNull('height');
            });
        }

        // Pending parent pickup filter
        if ($request->filled('pending_pickup') && $request->boolean('pending_pickup')) {
            $this->query->where('parent_pickup_required', true)
                        ->where('parent_pickup_completed', false);
        }
    }

    protected function applySearch(Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            
            $this->query->where(function($query) use ($search) {
                // Search in consultation fields
                $query->where('chief_complaint', 'like', "%{$search}%")
                      ->orWhere('symptoms_description', 'like', "%{$search}%")
                      ->orWhere('diagnosis', 'like', "%{$search}%")
                      ->orWhere('consultation_notes', 'like', "%{$search}%")
                      ->orWhere('treatment_provided', 'like', "%{$search}%")
                      ->orWhere('medications_given', 'like', "%{$search}%");

                // Search in student data
                $query->orWhereHas('student', function($studentQuery) use ($search) {
                    $studentQuery->where('student_id', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                                ->orWhere('email', 'like', "%{$search}%");
                });

                // Search in nurse data
                $query->orWhereHas('nurse', function($nurseQuery) use ($search) {
                    $nurseQuery->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%")
                              ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            });
        }

        // Separate student search for student selection
        if ($request->filled('student_search')) {
            $studentSearch = $request->student_search;
            
            $this->query->whereHas('student', function($studentQuery) use ($studentSearch) {
                $studentQuery->where('student_id', 'like', "%{$studentSearch}%")
                            ->orWhere('first_name', 'like', "%{$studentSearch}%")
                            ->orWhere('last_name', 'like', "%{$studentSearch}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$studentSearch}%"]);
            });
        }
    }

    protected function applySorting(Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort parameters
        if (!in_array($sortBy, $this->allowedSorts)) {
            $sortBy = 'created_at';
        }

        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        // Apply priority-based sorting for waiting consultations
        if ($request->filled('priority_sort') && $request->boolean('priority_sort')) {
            $this->query->orderByRaw("FIELD(priority, ?, ?, ?, ?)", [
                Consultation::PRIORITY_EMERGENCY,
                Consultation::PRIORITY_HIGH,
                Consultation::PRIORITY_NORMAL,
                Consultation::PRIORITY_LOW
            ]);
        }

        // Apply queue position sorting for waiting consultations
        if ($request->filled('queue_sort') && $request->boolean('queue_sort')) {
            $this->query->orderBy('queue_position', 'asc');
        }

        // Apply main sorting
        $this->query->orderBy($sortBy, $sortOrder);

        // Add secondary sorting for consistent results
        if ($sortBy !== 'id') {
            $this->query->orderBy('id', 'desc');
        }
    }

    protected function applyIncludes(): void
    {
        $this->query->with([
            'student:id,student_id,first_name,last_name,email,phone,gender,date_of_birth',
            'nurse:id,first_name,last_name,email',
            'appointment:id,appointment_date,reason,status'
        ]);
    }

    // Quick filter methods for common use cases
    public function today(): self
    {
        $this->query->whereDate('created_at', Carbon::today());
        return $this;
    }

    public function thisWeek(): self
    {
        $this->query->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
        return $this;
    }

    public function thisMonth(): self
    {
        $this->query->whereBetween('created_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ]);
        return $this;
    }

    public function waiting(): self
    {
        $this->query->where('status', Consultation::STATUS_WAITING);
        return $this;
    }

    public function inProgress(): self
    {
        $this->query->where('status', Consultation::STATUS_IN_PROGRESS);
        return $this;
    }

    public function completed(): self
    {
        $this->query->where('status', Consultation::STATUS_COMPLETED);
        return $this;
    }

    public function emergency(): self
    {
        $this->query->where('priority', Consultation::PRIORITY_EMERGENCY);
        return $this;
    }

    public function withReferrals(): self
    {
        $this->query->where('referral_issued', true);
        return $this;
    }

    public function requireingFollowUp(): self
    {
        $this->query->where('follow_up_required', true);
        return $this;
    }

    public function pendingParentPickup(): self
    {
        $this->query->where('parent_pickup_required', true)
                    ->where('parent_pickup_completed', false);
        return $this;
    }

    public function byNurse(int $nurseId): self
    {
        $this->query->where('nurse_id', $nurseId);
        return $this;
    }

    public function byStudent(int $studentId): self
    {
        $this->query->where('student_id', $studentId);
        return $this;
    }

    // Statistics methods
    public function getStatusCounts(): array
    {
        $statuses = $this->query->selectRaw('status, COUNT(*) as count')
                               ->groupBy('status')
                               ->pluck('count', 'status')
                               ->toArray();

        $result = [];
        foreach (Consultation::STATUS as $key => $label) {
            $result[$key] = [
                'label' => $label,
                'count' => $statuses[$key] ?? 0
            ];
        }

        return $result;
    }

    public function getPriorityCounts(): array
    {
        $priorities = $this->query->selectRaw('priority, COUNT(*) as count')
                                 ->groupBy('priority')
                                 ->pluck('count', 'priority')
                                 ->toArray();

        $result = [];
        foreach (Consultation::PRIORITY as $key => $label) {
            $result[$key] = [
                'label' => $label,
                'count' => $priorities[$key] ?? 0
            ];
        }

        return $result;
    }

    public function getTypeCounts(): array
    {
        $types = $this->query->selectRaw('type, COUNT(*) as count')
                            ->groupBy('type')
                            ->pluck('count', 'type')
                            ->toArray();

        $result = [];
        foreach (Consultation::TYPE as $key => $label) {
            $result[$key] = [
                'label' => $label,
                'count' => $types[$key] ?? 0
            ];
        }

        return $result;
    }

    public function getAverageWaitTime(): ?float
    {
        return $this->query->whereNotNull('wait_time_minutes')
                          ->avg('wait_time_minutes');
    }

    public function getAverageConsultationTime(): ?float
    {
        return $this->query->whereNotNull('consultation_duration_minutes')
                          ->avg('consultation_duration_minutes');
    }

    // Helper method to get allowed filters for form generation
    public function getAllowedFilters(): array
    {
        return $this->allowedFilters;
    }

    // Helper method to get allowed sorts for form generation
    public function getAllowedSorts(): array
    {
        return $this->allowedSorts;
    }

    // Method to reset query for reuse
    public function reset(Builder $query): self
    {
        $this->query = $query;
        return $this;
    }
}