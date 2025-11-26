<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Consultation;
use App\Models\Symptom;
use App\Models\PossibleIllness;
use App\Models\SymptomLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Notifications\AppointmentStatusChanged;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        if ($request->get('view') === 'calendar') {
            return $this->calendar($request);
        }
        
        $user = Auth::user();
        $query = Appointment::with([
            'user',           // ✅ REQUIRED: Load patient/student info
            'nurse',
            'acceptedBy',
            'completedBy',
            'rescheduledBy',
            'cancelledBy',
            'rejectedBy'
        ]);

        // Apply user-specific filters
        if ($user->isStudent()) {
            $query->forStudent($user->id);
        } elseif ($user->isNurse()) {
            $query->where(function($q) use ($user) {
                $q->whereNull('nurse_id')
                  ->orWhere('nurse_id', $user->id);
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply TYPE filter
        if ($request->filled('type')) {
            $query->where('appointment_type', $request->type);
        }

        // Apply date filter
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        // Apply search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('symptoms', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('student_id', 'like', "%{$search}%");
                  });
            });
        }

        // Apply quick filters
        if ($request->filled('filter')) {
            $this->applyQuickFilters($query, $request->filter, $user);
        }

        // ✅ IMPORTANT: Clone the query BEFORE pagination to get all appointments for categories
        $allAppointmentsQuery = $query->clone();

        // Get PAGINATED appointments for regular list
        $appointments = $query->orderBy('appointment_date')
                             ->orderBy('appointment_time')
                             ->paginate(10);

        // ✅ Get ALL appointments (without pagination) for category organization
        $allAppointments = $allAppointmentsQuery->orderBy('appointment_date')
                                                ->orderBy('appointment_time')
                                                ->get();

        // ✅ ORGANIZE APPOINTMENTS INTO CATEGORIES (using all appointments with relationships loaded)
        $latestAppointments = $allAppointments->filter(function($apt) {
            return $apt->status === Appointment::STATUS_PENDING 
                || ($apt->status === Appointment::STATUS_CONFIRMED && $apt->appointment_date >= today());
        })->sortByDesc('created_at')->take(10);

        $pendingAppointments = $allAppointments->filter(function($apt) {
            return $apt->status === Appointment::STATUS_PENDING;
        });

        // ✅ NEW: Confirmed appointments section
        $confirmedAppointments = $allAppointments->filter(function($apt) {
            return $apt->status === Appointment::STATUS_CONFIRMED;
        });

        $rescheduledAppointments = $allAppointments->filter(function($apt) {
            return $apt->status === Appointment::STATUS_RESCHEDULED;
        });

        $cancelledAppointments = $allAppointments->filter(function($apt) {
            return $apt->status === Appointment::STATUS_CANCELLED;
        });

        $otherAppointments = $allAppointments->filter(function($apt) {
            return !in_array($apt->status, [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED, // ✅ Exclude confirmed from "other"
                Appointment::STATUS_RESCHEDULED,
                Appointment::STATUS_CANCELLED
            ]);
        });

        // Get statistics
        $stats = $this->getAppointmentStatistics($user);

        // Determine view path based on user role
        $viewPath = $user->isStudent() ? 'student.appointments.index' : 'nurse.appointments.index';

        return view($viewPath, compact(
            'appointments',
            'latestAppointments',
            'pendingAppointments',
            'confirmedAppointments', // ✅ NEW: Pass confirmed appointments to view
            'rescheduledAppointments',
            'cancelledAppointments',
            'otherAppointments',
            'stats'
        ));
    }

    /**
     * Display the appointments calendar view.
     *
     * @param Request $request
     * @return View
     */
    public function calendar(Request $request): View
    {
        $user = Auth::user();
        
        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month);
        $currentMonth->setTimezone(config('app.timezone'));
        
        $now = Carbon::now();
        if ($currentMonth->lt($now->copy()->subMonths(12)) || $currentMonth->gt($now->copy()->addMonths(12))) {
            $currentMonth = $now->copy();
            $month = $currentMonth->format('Y-m');
        }
        
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();
        $prevMonth = $startOfMonth->copy()->subMonth()->startOfMonth();
        $nextMonth = $endOfMonth->copy()->addMonth()->startOfMonth();
        
        // Get appointments - FIXED: Only order by appointment table columns
        $appointmentsQuery = Appointment::with([
            'user' => fn($q) => $q->select('id', 'first_name', 'last_name', 'student_id'),
            'acceptedBy' => fn($q) => $q->select('id', 'first_name', 'last_name'),
            'completedBy' => fn($q) => $q->select('id', 'first_name', 'last_name')
        ])
            ->whereMonth('appointment_date', $currentMonth->month)
            ->whereYear('appointment_date', $currentMonth->year)
            ->whereIn('status', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_RESCHEDULED,
                Appointment::STATUS_FOLLOW_UP_PENDING
            ])
            ->whereNotNull('appointment_time') // Only appointments with valid time
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');
        
        // Filter by nurse if specified
        if ($user->isNurse()) {
            $appointmentsQuery->where(function($q) use ($user) {
                $q->whereNull('nurse_id')
                  ->orWhere('nurse_id', $user->id);
            });
        } else {
            $appointmentsQuery->where('user_id', $user->id);
        }
        
        $appointments = $appointmentsQuery->get();
        
        // Get consultations - FIXED: Order by consultation table columns only
        $consultationsQuery = Consultation::with([
            'student' => fn($q) => $q->select('id', 'first_name', 'last_name', 'student_id'),
            'nurse' => fn($q) => $q->select('id', 'first_name', 'last_name')
        ])
            ->whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->whereIn('status', [
                Consultation::STATUS_REGISTERED,
                Consultation::STATUS_IN_PROGRESS,
                Consultation::STATUS_COMPLETED
            ])
            ->orderBy('created_at'); // FIXED: Only order by created_at, not appointment columns
        
        if ($user->isNurse()) {
            $consultationsQuery->where(function($q) use ($user) {
                $q->whereNull('nurse_id')
                  ->orWhere('nurse_id', $user->id);
            });
        } else {
            $consultationsQuery->where('student_id', $user->id);
        }
        
        $consultations = $consultationsQuery->get();
        
        // Group appointments by date - FIXED: Format the date properly
        $appointmentsByDate = $appointments->groupBy(function($appointment) {
            return $appointment->appointment_date->format('Y-m-d');
        });
        
        // Group consultations by date (using created_at date)
        $consultationsByDate = $consultations->groupBy(function($consultation) {
            return $consultation->created_at->format('Y-m-d');
        });
        
        // Calculate calendar statistics
        $nurseId = $user->isNurse() ? $user->id : null;

        $calendarStats = [
            'total_appointments' => Appointment::when($nurseId, function($query) use ($nurseId) {
                return $query->where(function ($q) use ($nurseId) {
                    $q->whereNull('nurse_id')->orWhere('nurse_id', $nurseId);
                });
            })
            ->whereBetween('appointment_date', [$startOfMonth, $endOfMonth])
            ->count(),
            
            'total_consultations' => Consultation::when($nurseId, function($query) use ($nurseId) {
                return $query->where(function ($q) use ($nurseId) {
                    $q->whereNull('nurse_id')->orWhere('nurse_id', $nurseId);
                });
            })
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count(),

            'urgent' => Appointment::when($nurseId, function($query) use ($nurseId) {
                return $query->where(function ($q) use ($nurseId) {
                    $q->whereNull('nurse_id')->orWhere('nurse_id', $nurseId);
                });
            })
            ->where('is_urgent', true)
            ->whereBetween('appointment_date', [$startOfMonth, $endOfMonth])
            ->count(),

            'requiring_action' => Appointment::when($nurseId, function($query) use ($nurseId) {
                return $query->where(function ($q) use ($nurseId) {
                    $q->whereNull('nurse_id')->orWhere('nurse_id', $nurseId);
                });
            })
            ->whereIn('status', [
                Appointment::STATUS_PENDING, 
                Appointment::STATUS_RESCHEDULE_REQUESTED, 
                Appointment::STATUS_FOLLOW_UP_PENDING
            ])
            ->whereBetween('appointment_date', [$startOfMonth, $endOfMonth])
            ->count(),
        ];
        
        $stats = $this->getAppointmentStatistics($user);
        
        return view('nurse.appointments.calendar', compact(
            'currentMonth',
            'startOfMonth',
            'endOfMonth',
            'prevMonth',
            'nextMonth',
            'appointmentsByDate',
            'consultationsByDate',
            'calendarStats',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new appointment (students only).
     * UPDATED: Includes symptom checker data from session
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        try {
            // Check authentication first
            if (!Auth::check()) {
                return redirect()->route('login')
                    ->with('error', 'Please log in to continue.');
            }

            $user = Auth::user();
            
            // Check if user is a student
            if (!$user || !$user->isStudent()) {
                return redirect()->route('home')
                    ->with('error', 'Only students can request appointments.');
            }

            // Check for pending appointments
            try {
                $hasPendingAppointments = Appointment::where('user_id', $user->id)
                    ->whereIn('status', [
                        Appointment::STATUS_PENDING,
                        Appointment::STATUS_RESCHEDULED,
                        Appointment::STATUS_FOLLOW_UP_PENDING,
                        Appointment::STATUS_RESCHEDULE_REQUESTED,
                        Appointment::STATUS_CONFIRMED
                    ])
                    ->where(function($query) {
                        $query->whereDate('appointment_date', '>=', today())
                              ->orWhereNull('appointment_date');
                    })
                    ->exists();

                if ($hasPendingAppointments) {
                    return redirect()->route('student.appointments.index')
                        ->with('error', 'You have pending or confirmed appointments. Please wait for them to be processed before requesting a new appointment.');
                }
            } catch (\Exception $e) {
                Log::warning('Could not check pending appointments', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                // Don't block the user - continue anyway
            }

            // Get symptom data from session
            $symptomData = Session::get('current_symptom_check');
            $prefilledSymptoms = [];
            $suggestedUrgency = false;

            if ($symptomData) {
                // Extract symptoms for prefilling
                if (isset($symptomData['symptoms']) && is_array($symptomData['symptoms'])) {
                    $prefilledSymptoms = $symptomData['symptoms'];
                }
                
                // Only set urgency for priority
                if (isset($symptomData['is_emergency']) && $symptomData['is_emergency']) {
                    $suggestedUrgency = true;
                }
            }

            // Load available dates for the next 30 days
            $availableDates = $this->getAvailableDates(30, 'any', null);

            Log::info('Student accessing appointment create page', [
                'user_id' => $user->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'has_symptom_data' => !empty($symptomData),
                'suggested_urgent' => $suggestedUrgency
            ]);

            return view('student.appointments.create', [
                'availableDates' => $availableDates,
                'symptomData' => $symptomData,
                'prefilledSymptoms' => $prefilledSymptoms,
                'suggestedUrgency' => $suggestedUrgency
            ]);

        } catch (\Exception $e) {
            Log::error('Critical error in create method', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('student.appointments.index')
                ->with('error', 'Unable to load appointment form. Please try again or contact support if the problem persists.');
        }
    }

    /**
     * Store a newly created appointment request (students only).
     * UPDATED: Handles symptom data from session
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isStudent()) {
            abort(403, 'Only students can request appointments.');
        }

        // Check for pending appointments
        $hasPending = Appointment::where('user_id', $user->id)
            ->whereIn('status', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_RESCHEDULED,
                Appointment::STATUS_FOLLOW_UP_PENDING,
                Appointment::STATUS_RESCHEDULE_REQUESTED,
                Appointment::STATUS_CONFIRMED
            ])
            ->where(function($query) {
                $query->whereDate('appointment_date', '>=', today())
                      ->orWhereNull('appointment_date');
            })
            ->exists();

        if ($hasPending) {
            return back()
                ->withInput()
                ->with('error', 'You have pending or confirmed appointments. Please wait for them to be processed before requesting a new appointment.');
        }

        // Get symptom data from session
        $symptomData = Session::get('current_symptom_check');
        $hasSymptomData = !empty($symptomData);

        // Enhanced validation with better error messages
        $validated = $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today|before_or_equal:' . now()->addDays(30)->toDateString(),
            'appointment_time' => 'required|string',
            'formatted_appointment_time' => 'nullable|string',
            'reason' => 'required|string|min:10|max:500',
            'symptoms' => 'nullable|array',
            'symptoms.*' => 'string|max:50',
            'notes' => 'nullable|string|max:1000',
            'preferred_time' => 'nullable|in:morning,afternoon,any',
            'is_urgent' => 'nullable|boolean',
            'appointment_type' => 'required|string',
        ], [
            'appointment_date.required' => 'Please select an appointment date.',
            'appointment_date.after_or_equal' => 'The appointment date must be today or later.',
            'appointment_date.before_or_equal' => 'The appointment date cannot be more than 30 days in the future.',
            'appointment_time.required' => 'Please select a time slot.',
            'reason.required' => 'Please provide a reason for your visit.',
            'reason.min' => 'The reason must be at least 10 characters.',
            'symptoms.*.max' => 'Each symptom must be 50 characters or less.',
            'appointment_type.required' => 'Appointment type is required.',
        ]);

        try {
            $appointmentDate = Carbon::parse($validated['appointment_date']);
            
            // Enhanced date validation
            if ($appointmentDate->isWeekend()) {
                return back()->withInput()->with('error', 'Appointments cannot be scheduled on weekends.');
            }

            if ($appointmentDate->lt(today())) {
                return back()->withInput()->with('error', 'Cannot schedule appointments in the past.');
            }

            // Use formatted time if available, otherwise format the original
            $time = $validated['formatted_appointment_time'] ?? $validated['appointment_time'];
            
            // Normalize time format
            $time = trim($time);
            if (strlen($time) === 5) {
                $time .= ':00';
            }

            Log::info('Time processing', [
                'original_time' => $validated['appointment_time'],
                'formatted_time' => $validated['formatted_appointment_time'] ?? 'not provided',
                'final_time' => $time
            ]);

            // Validate the normalized time format
            if (!preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $time)) {
                Log::error('Invalid time format', ['time' => $time]);
                return back()->withInput()->with('error', 'Invalid time format selected. Please try again.');
            }

            // Check if the selected time slot is valid for the clinic
            if (!Appointment::isWithinClinicHours($time)) {
                return back()->withInput()->with('error', 'Selected time is outside clinic hours. Please select a valid time slot.');
            }

            // Enhanced conflict checking
            $conflict = Appointment::where('appointment_date', $validated['appointment_date'])
                ->where('appointment_time', $time)
                ->whereIn('status', [
                    Appointment::STATUS_PENDING, 
                    Appointment::STATUS_CONFIRMED,
                    Appointment::STATUS_RESCHEDULED
                ])
                ->exists();

            if ($conflict) {
                Log::warning('Time slot conflict detected', [
                    'date' => $validated['appointment_date'],
                    'time' => $time
                ]);
                return back()->withInput()->with('error', 'This time slot was just booked by someone else. Please choose another time.');
            }

            DB::beginTransaction();

            // Combine user symptoms with symptom checker data
            $combinedSymptoms = [];
            
            // Add symptoms from form
            if (isset($validated['symptoms']) && is_array($validated['symptoms'])) {
                $combinedSymptoms = array_merge($combinedSymptoms, $validated['symptoms']);
            }
            
            // Add symptoms from symptom checker (avoid duplicates)
            if ($hasSymptomData && isset($symptomData['symptoms']) && is_array($symptomData['symptoms'])) {
                foreach ($symptomData['symptoms'] as $symptom) {
                    if (!in_array($symptom, $combinedSymptoms)) {
                        $combinedSymptoms[] = $symptom;
                    }
                }
            }

            // Combine notes with symptom checker results (symptoms only)
            $combinedNotes = $validated['notes'] ?? '';
            
            if ($hasSymptomData) {
                $symptomSummary = "\n\n--- Symptom Checker Results ---\n";
                $symptomSummary .= "Checked: " . ($symptomData['created_at'] ?? now()->format('M j, Y g:i A')) . "\n";
                
                if (isset($symptomData['symptoms']) && !empty($symptomData['symptoms'])) {
                    $symptomSummary .= "Reported Symptoms: " . (is_array($symptomData['symptoms']) 
                        ? implode(', ', $symptomData['symptoms']) 
                        : $symptomData['symptoms']) . "\n";
                }
                
                // Only set urgency for priority
                if (isset($symptomData['is_emergency']) && $symptomData['is_emergency']) {
                    $symptomSummary .= "⚠️ URGENT: Prompt attention recommended\n";
                }
                
                $combinedNotes = $symptomSummary . ($combinedNotes ? "\n\nAdditional Notes:\n" . $combinedNotes : '');
            }

            // Set priority based on urgency
            $priority = Appointment::PRIORITY_NORMAL;
            $isUrgent = $validated['is_urgent'] ?? false;
            
            // Use the appointment type from the hidden field (should be "scheduled")
            $appointmentType = $validated['appointment_type'] ?? 'scheduled';
            
            // Consider symptom checker emergency flag for priority only
            if ($hasSymptomData && ($symptomData['is_emergency'] ?? false)) {
                $priority = Appointment::PRIORITY_HIGH;
                $isUrgent = true;
            } elseif ($isUrgent) {
                $priority = Appointment::PRIORITY_HIGH;
            }

            // Provide default value for preferred_time if not provided
            $preferredTime = $validated['preferred_time'] ?? 'any';

            // Create the appointment
            $appointmentData = [
                'user_id' => $user->id,
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $time,
                'reason' => $validated['reason'],
                'symptoms' => !empty($combinedSymptoms) ? implode(', ', array_filter($combinedSymptoms)) : null,
                'notes' => $combinedNotes ?: null,
                'preferred_time' => $preferredTime === 'any' ? null : $preferredTime,
                'is_urgent' => $isUrgent,
                'appointment_type' => $appointmentType,
                'status' => Appointment::STATUS_PENDING,
                'is_follow_up' => false,
                'requires_student_confirmation' => false,
                'priority' => $priority,
                'nurse_id' => null,
            ];

            Log::info('Creating appointment with data', $appointmentData);

            $appointment = Appointment::create($appointmentData);

            // Store symptom log reference and clear session
            if ($hasSymptomData) {
                Session::put("appointment_{$appointment->id}_symptom_check", $symptomData);
                Session::forget('current_symptom_check');
                
                Log::info('Symptom data linked to appointment', [
                    'appointment_id' => $appointment->id,
                    'has_emergency_flag' => $symptomData['is_emergency'] ?? false
                ]);
            }

            DB::commit();

            Log::info('Appointment created successfully', [
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'date' => $validated['appointment_date'],
                'time' => $time,
                'type' => $appointmentType,
                'is_urgent' => $isUrgent,
                'priority' => $priority,
                'has_symptom_data' => $hasSymptomData
            ]);

            return redirect()->route('student.appointments.show', $appointment)
                ->with('success', 'Your appointment request has been submitted successfully! It will be reviewed by clinic Nurse.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating appointment', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'input' => $request->except(['_token']),
                'has_symptom_data' => $hasSymptomData,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Failed to create appointment: ' . ($e->getMessage() ?? 'Please try again.'));
        }
    }

    /**
     * Show form to create walk-in appointment (nurses only).
     *
     * @return View
     */
    public function createWalkIn(): View
    {
        $user = Auth::user();
        if (!$user->isNurse()) {
            abort(403, 'Only nurses can create walk-in appointments.');
        }

        $students = User::where('role', 'student')
                       ->orderBy('last_name')
                       ->orderBy('first_name')
                       ->get();

        return view('nurse.appointments.create-walkin', compact('students'));
    }

    /**
     * Store walk-in appointment (nurses only).
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function storeWalkIn(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isNurse()) {
            abort(403, 'Only nurses can create walk-in appointments.');
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'reason' => 'required|string|min:10|max:500',
            'symptoms' => 'nullable|array',
            'symptoms.*' => 'string|max:50',
            'is_urgent' => 'nullable|boolean',
            'appointment_type' => 'nullable|in:' . implode(',', [
                Appointment::TYPE_WALK_IN, 
                Appointment::TYPE_EMERGENCY
            ]),
            'notes' => 'nullable|string|max:1000',
        ], [
            'student_id.exists' => 'Selected student does not exist.',
            'reason.required' => 'Please provide a reason for the visit.',
            'reason.min' => 'Reason must be at least 10 characters.',
            'symptoms.*.max' => 'Each symptom must be 50 characters or less.',
            'appointment_type.in' => 'Invalid appointment type selected.',
        ]);

        $student = User::findOrFail($validated['student_id']);
        if (!$student->isStudent()) {
            return back()->withInput()->with('error', 'Selected user is not a student.');
        }

        // Check if student already has active walk-in
        $hasActiveWalkIn = Appointment::where('user_id', $student->id)
            ->whereIn('appointment_type', [Appointment::TYPE_WALK_IN, Appointment::TYPE_EMERGENCY])
            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
            ->whereDate('appointment_date', today())
            ->exists();

        if ($hasActiveWalkIn) {
            return back()->withInput()->with('error', 'This student already has an active walk-in appointment today.');
        }

        try {
            DB::beginTransaction();

            // Set priority based on urgency and type
            $priority = Appointment::PRIORITY_NORMAL;
            if (($validated['is_urgent'] ?? false)) {
                $priority = Appointment::PRIORITY_HIGH;
            }
            if ($validated['appointment_type'] === Appointment::TYPE_EMERGENCY) {
                $priority = Appointment::PRIORITY_EMERGENCY;
                $validated['is_urgent'] = true;
            }

            $now = Carbon::now();
            $appointment = Appointment::create([
                'user_id' => $student->id,
                'nurse_id' => $user->id,
                'appointment_date' => $now->toDateString(),
                'appointment_time' => $now->format('H:i:s'),
                'reason' => $validated['reason'],
                'notes' => ($validated['notes'] ?? '') . "\n\nWalk-in appointment created by {$user->full_name} on " . $now->format('M j, Y g:i A'),
                'is_urgent' => $validated['is_urgent'] ?? false,
                'appointment_type' => $validated['appointment_type'],
                'status' => Appointment::STATUS_CONFIRMED,
                'accepted_by' => $user->id,
                'accepted_at' => $now,
                'created_by_nurse' => $user->id,
                'is_follow_up' => false,
                'requires_student_confirmation' => false,
                'priority' => $priority,
            ]);

            // Create consultation immediately for walk-ins
            $consultation = Consultation::create([
                'appointment_id' => $appointment->id,
                'nurse_id' => $user->id,
                'student_id' => $student->id,
                'type' => $validated['appointment_type'],
                'priority' => $priority,
                'status' => 'in_progress',
                'started_at' => $now
            ]);

            DB::commit();

            Log::info('Walk-in appointment created', [
                'appointment_id' => $appointment->id,
                'consultation_id' => $consultation->id,
                'nurse_id' => $user->id,
                'student_id' => $student->id,
                'type' => $validated['appointment_type'],
                'is_urgent' => $validated['is_urgent'] ?? false,
                'priority' => $priority
            ]);

            return redirect()->route('nurse.consultations.show', $consultation)
                ->with('success', 'Walk-in appointment and consultation created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating walk-in appointment', [
                'error' => $e->getMessage(),
                'nurse_id' => $user->id,
                'student_id' => $validated['student_id'],
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'input' => $request->except(['_token'])
            ]);
            return back()->withInput()->with('error', 'Failed to create walk-in appointment. Please try again.');
        }
    }

    /**
     * Display the specified appointment.
     * UPDATED: Includes symptom checker data in response
     *
     * @param Appointment $appointment
     * @return View|JsonResponse
     */
    public function show(Appointment $appointment)
    {
        $user = Auth::user();
        if ($user->isStudent() && $appointment->user_id != $user->id) {
            abort(403, 'You can only view your own appointments.');
        }

        // Load ALL necessary relationships
        $appointment->load([
            'user.medicalRecord',
            'user.consultations' => function($query) use ($appointment) {
                $query->where('status', 'completed')
                      ->orderBy('created_at', 'desc')
                      ->take(5);
            },
            'nurse',
            'acceptedBy',
            'rescheduledBy',
            'completedBy',
            'cancelledBy',
            'rejectedBy',
            'followUps',
            'parent'
        ]);

        // Get symptom checker data from session
        $symptomData = Session::get("appointment_{$appointment->id}_symptom_check");

        // Check if this is an AJAX request for JSON response
        if (request()->wantsJson() || request()->ajax()) {
            try {
                $responseData = [
                    'success' => true,
                    'appointment' => [
                        'id' => $appointment->id,
                        'user' => [
                            'full_name' => $appointment->user->full_name ?? 'Unknown',
                            'student_id' => $appointment->user->student_id ?? 'N/A',
                            'email' => $appointment->user->email ?? 'Not provided',
                            'phone' => $appointment->user->phone ?? 'Not provided',
                            'date_of_birth' => $appointment->user->date_of_birth ? 
                                $appointment->user->date_of_birth->format('M d, Y') : null,
                        ],
                        'formatted_date_time' => $appointment->appointment_date && $appointment->appointment_time
                            ? $appointment->appointment_date->format('M d, Y') . ' at ' . 
                              Carbon::parse($appointment->appointment_time)->format('g:i A')
                            : 'Not scheduled',
                        'reason' => $appointment->reason ?? 'No reason provided',
                        'symptoms' => $appointment->symptoms ?? null,
                        'notes' => $appointment->notes ?? null,
                        'status' => $appointment->status,
                        'status_display' => $appointment->status_display ?? 'Unknown',
                        'status_badge_class' => $appointment->status_badge_class ?? 'bg-gray-100 text-gray-800',
                        'priority_display' => $appointment->priority_display ?? 'Normal',
                        'appointment_type' => $appointment->appointment_type ?? 'scheduled',
                        'appointment_type_display' => $appointment->appointment_type_display ?? 'Scheduled',
                        'is_urgent' => (bool)($appointment->is_urgent ?? false),
                        
                        // Symptom checker data
                        'symptom_check_data' => $symptomData,
                        'has_symptom_check' => !empty($symptomData),
                        
                        // Timestamps
                        'created_at' => $appointment->created_at ? 
                            $appointment->created_at->format('M d, Y g:i A') : null,
                        'accepted_at' => $appointment->accepted_at ? 
                            $appointment->accepted_at->format('M d, Y g:i A') : null,
                        'rescheduled_at' => $appointment->rescheduled_at ? 
                            $appointment->rescheduled_at->format('M d, Y g:i A') : null,
                        'completed_at' => $appointment->completed_at ? 
                            $appointment->completed_at->format('M d, Y g:i A') : null,
                        'cancelled_at' => $appointment->cancelled_at ? 
                            $appointment->cancelled_at->format('M d, Y g:i A') : null,
                        'rejected_at' => $appointment->rejected_at ? 
                            $appointment->rejected_at->format('M d, Y g:i A') : null,
                    ]
                ];

                Log::info('Appointment JSON retrieved', [
                    'appointment_id' => $appointment->id,
                    'user_id' => $user->id,
                    'user_role' => $user->role
                ]);

                return response()->json($responseData, 200, [], JSON_UNESCAPED_SLASHES);

            } catch (\Exception $e) {
                Log::error('Error building appointment JSON response', [
                    'error' => $e->getMessage(),
                    'appointment_id' => $appointment->id,
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load appointment details',
                    'error' => config('app.debug') ? $e->getMessage() : 'Server error'
                ], 500);
            }
        }

        // Regular view for non-AJAX requests
        $nurses = User::where('role', 'nurse')->orderBy('last_name')->get();
        
        $availableSlots = [];
        if ($appointment->appointment_date) {
            $availableSlots = Appointment::getAvailableTimeSlots(
                $appointment->appointment_date->format('Y-m-d'), 
                $user->isNurse() ? $user->id : null, 
                $appointment->id
            );
        }

        $viewPath = $user->isStudent() ? 'student.appointments.show' : 'nurse.appointments.show';

        return view($viewPath, compact('appointment', 'nurses', 'availableSlots', 'symptomData'));
    }

    /**
     * Show the form for editing the specified appointment.
     *
     * @param Appointment $appointment
     * @return View
     */
    public function edit(Appointment $appointment): View
    {
        $user = Auth::user();
        
        // Students can only edit their own pending appointments
        if ($user->isStudent()) {
            if ($appointment->user_id != $user->id || !$appointment->isPending()) {
                abort(403, 'Only pending appointments can be edited.');
            }
        } elseif (!$user->isNurse()) {
            // Non-nurses and non-students can't edit
            abort(403, 'Unauthorized action.');
        }

        $students = User::where('role', 'student')->orderBy('last_name')->get();
        $nurses = User::where('role', 'nurse')->orderBy('last_name')->get();
        
        $availableSlots = Appointment::getAvailableTimeSlots(
            $appointment->appointment_date->format('Y-m-d'), 
            $user->isNurse() ? $user->id : null, 
            $appointment->id
        );

        $viewPath = $user->isStudent() ? 'student.appointments.edit' : 'nurse.appointments.edit';

        return view($viewPath, compact('appointment', 'students', 'nurses', 'availableSlots'));
    }

    /**
     * Update the specified appointment.
     *
     * @param Request $request
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = Auth::user();
        
        // Authorization check
        if ($user->isStudent()) {
            if ($appointment->user_id != $user->id || !$appointment->isPending()) {
                abort(403, 'Only pending appointments can be updated.');
            }
        } elseif (!$user->isNurse()) {
            abort(403, 'Unauthorized action.');
        }

        $isStudentUpdate = $user->isStudent();
        
        $validated = $request->validate([
            'user_id' => $isStudentUpdate ? 'nullable' : 'required|exists:users,id',
            'nurse_id' => 'nullable|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today|before:' . now()->addDays(30)->toDateString(),
            'appointment_time' => 'required|date_format:H:i',
            'reason' => 'required|string|min:10|max:500',
            'symptoms' => 'nullable|array',
            'symptoms.*' => 'string|max:50',
            'notes' => 'nullable|string|max:1000',
            'preferred_time' => 'nullable|in:morning,afternoon,any',
            'is_urgent' => 'nullable|boolean',
            'appointment_type' => 'required|in:' . implode(',', array_keys(Appointment::getTypeOptions())),
            'priority' => $isStudentUpdate ? 'nullable|integer|min:1|max:5' : 'required|integer|min:1|max:5',
            'status' => $isStudentUpdate ? 'nullable' : 'sometimes|in:' . implode(',', array_keys(Appointment::getStatusOptions())),
        ], [
            'user_id.required' => 'Please select a patient.',
            'nurse_id.exists' => 'Selected nurse does not exist.',
            'appointment_date.after_or_equal' => 'The appointment date must be today or later.',
            'appointment_date.before' => 'The appointment date cannot be more than 30 days in the future.',
            'appointment_time.date_format' => 'The time must be in HH:MM format.',
            'reason.required' => 'Please provide a reason for the visit.',
            'reason.min' => 'The reason must be at least 10 characters.',
            'symptoms.*.max' => 'Each symptom must be 50 characters or less.',
            'appointment_type.in' => 'Invalid appointment type selected.',
            'priority.required' => 'Please select a priority level.',
            'priority.min' => 'Priority must be between 1 and 5.',
            'status.in' => 'Invalid status selected.',
        ]);

        // Validate nurse if provided
        if (!empty($validated['nurse_id'])) {
            $nurse = User::find($validated['nurse_id']);
            if (!$nurse || !$nurse->isNurse()) {
                return back()->withInput()->with('error', 'The selected nurse is invalid.');
            }
        }

        $appointmentDate = Carbon::parse($validated['appointment_date']);
        if ($appointmentDate->isWeekend()) {
            return back()->withInput()->with('error', 'Appointments cannot be scheduled on weekends.');
        }

        if (!Appointment::isWithinClinicHours($validated['appointment_time'])) {
            return back()->withInput()->with('error', 'Selected time is outside clinic hours.');
        }

        // Check for time slot conflicts (exclude current appointment)
        $conflict = Appointment::where('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
            ->where('id', '!=', $appointment->id)
            ->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'This time slot is already booked. Please choose another time.');
        }

        try {
            DB::beginTransaction();

            // Prepare data for update
            $updateData = [
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $validated['appointment_time'],
                'reason' => $validated['reason'],
                'symptoms' => $validated['symptoms'] ? implode(', ', $validated['symptoms']) : null,
                'notes' => $validated['notes'],
                'preferred_time' => $validated['preferred_time'] === 'any' ? null : $validated['preferred_time'],
                'appointment_type' => $validated['appointment_type'],
                'updated_by' => $user->id,
            ];

            // Only nurses can update user_id, nurse_id, priority, and status
            if ($user->isNurse()) {
                $updateData['user_id'] = $validated['user_id'];
                $updateData['nurse_id'] = $validated['nurse_id'] ?? null;
                $updateData['status'] = $validated['status'] ?? $appointment->status;
            }

            // Set is_urgent
            $updateData['is_urgent'] = $validated['is_urgent'] ?? $appointment->is_urgent;

            // Set priority based on urgency and type
            $priority = $validated['priority'] ?? $appointment->priority;
            if ($updateData['is_urgent'] && $priority < Appointment::PRIORITY_HIGH) {
                $priority = Appointment::PRIORITY_HIGH;
            }
            if ($updateData['appointment_type'] === Appointment::TYPE_EMERGENCY) {
                $priority = Appointment::PRIORITY_EMERGENCY;
                $updateData['is_urgent'] = true;
            }
            $updateData['priority'] = $priority;

            $oldStatus = $appointment->status;
            $appointment->update($updateData);

            // Log status change if nurse updated status
            if ($user->isNurse() && $appointment->wasChanged('status') && $oldStatus !== $appointment->status) {
                Log::info('Appointment status updated by nurse', [
                    'appointment_id' => $appointment->id,
                    'nurse_id' => $user->id,
                    'old_status' => $oldStatus,
                    'new_status' => $appointment->status,
                    'changes' => $appointment->getChanges()
                ]);
            }

            // Add update note for students
            if ($user->isStudent() && $appointment->wasChanged()) {
                $changes = array_diff_assoc($appointment->getAttributes(), $appointment->getOriginal());
                $changeLog = "Updated by student on " . now()->format('M j, Y g:i A') . ": " . 
                           implode(', ', array_map(function($key, $value) {
                               return ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
                           }, array_keys($changes), $changes));
                
                $appointment->update(['notes' => ($appointment->notes ?? '') . "\n\n" . $changeLog]);
            }

            DB::commit();

            $message = $user->isNurse() 
                ? 'Appointment updated successfully by staff.' 
                : 'Your appointment details have been updated successfully.';

            $redirectRoute = $user->isNurse() 
                ? route('nurse.appointments.show', $appointment)
                : route('student.appointments.show', $appointment);

            Log::info($user->isNurse() ? 'Appointment updated by nurse' : 'Appointment updated by student', [
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'user_type' => $user->role,
                'changes' => $appointment->getChanges()
            ]);

            return redirect($redirectRoute)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating appointment', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'user_type' => $user->role,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'input' => $request->except(['_token'])
            ]);
            return back()->withInput()->with('error', 'Failed to update appointment. Please try again.');
        }
    }

    /**
     * Remove the specified appointment (students only).
     *
     * @param Request $request
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isStudent() || $appointment->user_id != $user->id) {
            abort(403, 'Only students can cancel their own appointments.');
        }

        if (!$appointment->canBeCancelled()) {
            return back()->with('error', 'This appointment cannot be cancelled at this time.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:500'
        ], [
            'cancellation_reason.required' => 'Please provide a reason for cancellation.',
            'cancellation_reason.min' => 'Cancellation reason must be at least 10 characters.',
        ]);

        try {
            DB::beginTransaction();

            $success = $this->cancelAppointment($appointment, $user->id, $validated['cancellation_reason']);

            if (!$success) {
                throw new \Exception('Failed to cancel appointment.');
            }

            DB::commit();

            Log::info('Appointment cancelled by student', [
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'reason' => $validated['cancellation_reason']
            ]);

            return redirect()->route('student.appointments.index')
                ->with('success', 'Appointment cancelled successfully. You can request a new appointment if needed.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling appointment', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return back()->with('error', 'Failed to cancel appointment. Please try again.');
        }
    }

    /**
     * Get available time slots for a date (API endpoint) - FIXED VERSION
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAvailableSlots(Request $request): JsonResponse
    {
        $user = Auth::user();
        $date = $request->query('date');
        
        // Enhanced date validation
        if (!$date) {
            return response()->json([
                'success' => false,
                'message' => 'Date parameter is required.',
            ], 400);
        }

        try {
            // Validate date format more strictly
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format. Use YYYY-MM-DD.',
                ], 400);
            }

            $appointmentDate = Carbon::createFromFormat('Y-m-d', $date);
            
            // Check if date creation was successful
            if (!$appointmentDate || $appointmentDate->format('Y-m-d') !== $date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date provided.',
                ], 400);
            }
            
            // Check if date is in the past
            if ($appointmentDate->lt(today())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot schedule appointments in the past.',
                ], 400);
            }
            
            // Check if date is too far in the future
            if ($appointmentDate->gt(today()->addDays(90))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Date must be within the next 90 days.',
                ], 400);
            }

            // Check if date is weekend
            if ($appointmentDate->isWeekend()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Appointments are not available on weekends.',
                ], 400);
            }

            // Get available slots with better error handling
            $slots = Appointment::getAvailableTimeSlots(
                $date,
                $user->isNurse() ? $user->id : null
            );

            return response()->json([
                'success' => true,
                'slots' => array_values($slots),
                'date' => $date,
                'formatted_date' => $appointmentDate->format('M d, Y'),
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching available slots', [
                'date' => $date,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load time slots. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get available dates for appointment creation (API endpoint).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function availableDates(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $validated = $request->validate([
                'preferred_time' => 'nullable|in:morning,afternoon,any',
                'days_ahead' => 'nullable|integer|min:1|max:90',
                'nurse_id' => $user->isNurse() ? 'nullable|exists:users,id' : 'nullable'
            ]);

            $preferredTime = $validated['preferred_time'] ?? 'any';
            $daysAhead = $validated['days_ahead'] ?? 30;
            $nurseId = $user->isNurse() ? ($validated['nurse_id'] ?? $user->id) : null;

            $availableDates = $this->getAvailableDates($daysAhead, $preferredTime, $nurseId);

            return response()->json([
                'success' => true,
                'dates' => $availableDates,
                'count' => count($availableDates),
                'preferred_time' => $preferredTime,
                'message' => count($availableDates) > 0 ? 
                    "Found " . count($availableDates) . " available date(s)" : 
                    'No available dates found. Please try adjusting your preferred time.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading available dates', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'user_type' => $user->role,
                'request' => $request->all(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load available dates. Please try again.',
                'dates' => []
            ], 500);
        }
    }

    /**
     * Get appointment details for modal/day view (API endpoint)
     * FIXED VERSION - Works with User model and Appointment model
     * 
     * @param Appointment $appointment
     * @return JsonResponse
     */
    public function getDetails(Appointment $appointment): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Step 1: Verify authentication
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }
            
            // Step 2: Authorization check
            if ($user->isStudent() && $appointment->user_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'You can only view your own appointments.'
                ], 403);
            }

            // Step 3: Get the patient/student
            $patient = User::find($appointment->user_id);
            
            if (!$patient) {
                Log::warning('Patient not found for appointment', [
                    'appointment_id' => $appointment->id,
                    'user_id' => $appointment->user_id
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Patient information not available'
                ], 404);
            }

            // Step 4: Format the appointment time safely
            $formattedTime = 'Not scheduled';
            if ($appointment->appointment_time) {
                try {
                    $time = Carbon::parse($appointment->appointment_time);
                    $formattedTime = $time->format('g:i A');
                } catch (\Exception $e) {
                    Log::warning('Could not parse appointment time', [
                        'appointment_id' => $appointment->id,
                        'time_value' => $appointment->appointment_time,
                        'error' => $e->getMessage()
                    ]);
                    $formattedTime = 'Invalid time';
                }
            }

            // Step 5: Get status display safely
            $statusDisplay = match($appointment->status) {
                'pending' => 'Pending Review',
                'confirmed' => 'Confirmed',
                'rescheduled' => 'Rescheduled',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'rejected' => 'Rejected',
                'follow_up_pending' => 'Follow-up Pending',
                'reschedule_requested' => 'Reschedule Requested',
                default => ucfirst(str_replace('_', ' ', $appointment->status))
            };

            // Step 6: Get status badge class safely
            $statusBadgeClass = match($appointment->status) {
                'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                'confirmed' => 'bg-blue-100 text-blue-800 border-blue-200',
                'rescheduled' => 'bg-orange-100 text-orange-800 border-orange-200',
                'completed' => 'bg-green-100 text-green-800 border-green-200',
                'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                'rejected' => 'bg-gray-100 text-gray-800 border-gray-200',
                'follow_up_pending' => 'bg-purple-100 text-purple-800 border-purple-200',
                'reschedule_requested' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                default => 'bg-gray-100 text-gray-800 border-gray-200'
            };

            // Step 7: Get priority display safely
            $priorityDisplay = match($appointment->priority ?? 2) {
                1 => 'Low',
                2 => 'Normal',
                3 => 'High',
                4 => 'Urgent',
                5 => 'Emergency',
                default => 'Normal'
            };

            // Step 8: Check permissions for actions
            $canStartConsultation = false;
            if ($user->isNurse()) {
                try {
                    $canStartConsultation = $appointment->canStartConsultation();
                } catch (\Exception $e) {
                    Log::error('Error checking canStartConsultation', [
                        'error' => $e->getMessage()
                    ]);
                    $canStartConsultation = false;
                }
            }

            // Step 9: Build response
            $response = [
                'success' => true,
                'appointment' => [
                    'id' => $appointment->id,
                    'user' => [
                        'full_name' => trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')) ?: 'Unknown Patient',
                        'student_id' => $patient->student_id ?? 'N/A',
                        'email' => $patient->email ?? 'Not provided',
                        'phone' => $patient->phone ?? 'Not provided',
                        'date_of_birth' => $patient->date_of_birth ? 
                            $patient->date_of_birth->format('M d, Y') : null,
                    ],
                    'formatted_date' => $appointment->appointment_date ? 
                        $appointment->appointment_date->format('M d, Y') : 'Not set',
                    'formatted_time' => $formattedTime,
                    'formatted_date_time' => ($appointment->appointment_date ? 
                        $appointment->appointment_date->format('M d, Y') : 'Not set') . ' at ' . $formattedTime,
                    'reason' => $appointment->reason ?? 'No reason provided',
                    'symptoms' => $appointment->symptoms ?? null,
                    'notes' => $appointment->notes ?? null,
                    'status' => $appointment->status,
                    'status_display' => $statusDisplay,
                    'status_badge_class' => $statusBadgeClass,
                    'priority' => $appointment->priority ?? 2,
                    'priority_display' => $priorityDisplay,
                    'appointment_type' => $appointment->appointment_type ?? 'scheduled',
                    'appointment_type_display' => match($appointment->appointment_type ?? 'scheduled') {
                        'scheduled' => 'Scheduled Appointment',
                        'walk_in' => 'Walk-in Consultation',
                        'follow_up' => 'Follow-up Appointment',
                        'emergency' => 'Emergency',
                        default => ucfirst(str_replace('_', ' ', $appointment->appointment_type ?? 'scheduled'))
                    },
                    'is_urgent' => (bool)($appointment->is_urgent ?? false),
                    
                    // Timestamps
                    'created_at' => $appointment->created_at ? 
                        $appointment->created_at->format('M d, Y g:i A') : null,
                    'accepted_at' => $appointment->accepted_at ? 
                        $appointment->accepted_at->format('M d, Y g:i A') : null,
                    'rescheduled_at' => $appointment->rescheduled_at ? 
                        $appointment->rescheduled_at->format('M d, Y g:i A') : null,
                    'completed_at' => $appointment->completed_at ? 
                        $appointment->completed_at->format('M d, Y g:i A') : null,
                    'cancelled_at' => $appointment->cancelled_at ? 
                        $appointment->cancelled_at->format('M d, Y g:i A') : null,
                    'rejected_at' => $appointment->rejected_at ? 
                        $appointment->rejected_at->format('M d, Y g:i A') : null,
                    
                    // Action permissions
                    'can_accept' => $user->isNurse() && $appointment->isPending(),
                    'can_reschedule' => $user->isNurse() && $appointment->canBeRescheduledByNurse(),
                    'can_cancel' => $user->isNurse() && $appointment->canBeCancelled(),
                    'can_start_consultation' => $canStartConsultation,
                ]
            ];

            Log::info('Appointment details retrieved', [
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'user_role' => $user->role
            ]);

            return response()->json($response);

        } catch (\Throwable $e) {
            Log::error('Error in getDetails', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'appointment_id' => $appointment->id ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load appointment details. Please try again.',
                'debug' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    /**
     * Get appointments for a specific day (API endpoint).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function dayDetails(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $validated = $request->validate([
                'date' => 'required|date'
            ]);

            $date = Carbon::parse($validated['date']);
            
            $appointmentsQuery = Appointment::with(['user', 'nurse', 'acceptedBy'])
                ->whereDate('appointment_date', $date)
                ->orderBy('appointment_time')
                ->orderBy('priority', 'desc');

            // Filter by user role
            if ($user->isStudent()) {
                $appointmentsQuery->where('user_id', $user->id);
            } elseif ($user->isNurse()) {
                $appointmentsQuery->where(function($q) use ($user) {
                    $q->whereNull('nurse_id')
                      ->orWhere('nurse_id', $user->id);
                });
            }

            $appointments = $appointmentsQuery->get();

            return response()->json([
                'success' => true,
                'date' => $date->format('F j, Y (l)'),
                'appointments' => $appointments->map(function($appointment) {
                    return [
                        'id' => $appointment->id,
                        'patient' => $appointment->getPatientName(),
                        'student_id' => $appointment->getPatientStudentId(),
                        'time' => $appointment->formatted_time,
                        'type' => $appointment->appointment_type_display,
                        'status' => $appointment->status_display,
                        'status_class' => $appointment->status_badge_class,
                        'is_urgent' => $appointment->is_urgent,
                        'priority' => $appointment->priority_display,
                        'reason' => Str::limit($appointment->reason, 50),
                        'can_view' => true
                    ];
                }),
                'count' => $appointments->count(),
                'message' => $appointments->count() > 0 ? 
                    "Found {$appointments->count()} appointment(s) for {$date->format('F j, Y')}" : 
                    "No appointments scheduled for {$date->format('F j, Y')}"
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading day appointments', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'user_type' => $user->role,
                'date' => $request->get('date'),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load appointments for this day.',
                'appointments' => [],
                'count' => 0
            ], 500);
        }
    }

    /**
     * Request reschedule by student.
     *
     * @param Request $request
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function requestReschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isStudent() || $appointment->user_id != $user->id) {
            abort(403, 'Only students can request rescheduling of their own appointments.');
        }

        if (!$appointment->canBeRescheduledByStudent()) {
            return back()->with('error', 'This appointment cannot be rescheduled at this time.');
        }

        $validated = $request->validate([
            'reschedule_request_reason' => 'required|string|min:10|max:500',
            'student_preferred_new_date' => 'nullable|date|after:today|before:' . now()->addDays(30)->toDateString(),
            'student_preferred_new_time' => 'nullable|date_format:H:i',
        ], [
            'reschedule_request_reason.required' => 'Please provide a reason for requesting reschedule.',
            'reschedule_request_reason.min' => 'Reason must be at least 10 characters.',
            'student_preferred_new_date.after' => 'Preferred date must be in the future.',
            'student_preferred_new_date.before' => 'Preferred date cannot be more than 30 days in the future.',
            'student_preferred_new_time.date_format' => 'Preferred time must be in HH:MM format.',
        ]);

        // Validate preferred time if provided
        if ($validated['student_preferred_new_date'] && $validated['student_preferred_new_time']) {
            $newDateTime = Carbon::parse("{$validated['student_preferred_new_date']} {$validated['student_preferred_new_time']}");
            
            if ($newDateTime->isWeekend()) {
                return back()->withInput()->with('error', 'Preferred date cannot be on a weekend.');
            }

            if (!Appointment::isWithinClinicHours($validated['student_preferred_new_time'])) {
                return back()->withInput()->with('error', 'Preferred time is outside clinic hours.');
            }

            // Check for conflicts
            $conflict = Appointment::where('appointment_date', $validated['student_preferred_new_date'])
                ->where('appointment_time', $validated['student_preferred_new_time'])
                ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
                ->exists();

            if ($conflict) {
                return back()->withInput()->with('error', 'Preferred time slot is already booked.');
            }
        }

        try {
            DB::beginTransaction();

            $appointment->update([
                'status' => Appointment::STATUS_RESCHEDULE_REQUESTED,
                'reschedule_request_reason' => $validated['reschedule_request_reason'],
                'student_preferred_new_date' => $validated['student_preferred_new_date'] ?? null,
                'student_preferred_new_time' => $validated['student_preferred_new_time'] ?? null,
                'student_requested_reschedule_at' => now(),
                'notes' => ($appointment->notes ?? '') . "\n\nReschedule requested by student on " . now()->format('M j, Y g:i A') . ":\n" . $validated['reschedule_request_reason']
            ]);

            DB::commit();

            Log::info('Reschedule requested by student', [
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'reason' => $validated['reschedule_request_reason'],
                'preferred_date' => $validated['student_preferred_new_date'],
                'preferred_time' => $validated['student_preferred_new_time']
            ]);

            return redirect()->route('student.appointments.show', $appointment)
                ->with('success', 'Your reschedule request has been submitted. Clinic staff will review it soon.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error requesting reschedule', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return back()->withInput()->with('error', 'Failed to submit reschedule request. Please try again.');
        }
    }

    /**
     * Confirm rescheduled appointment (students only).
     *
     * @param Request $request
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function confirmReschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isStudent() || $appointment->user_id != $user->id) {
            abort(403, 'Only students can confirm their own rescheduled appointments.');
        }

        if ($appointment->status !== Appointment::STATUS_RESCHEDULED || !$appointment->requires_student_confirmation) {
            return back()->with('error', 'This appointment does not require confirmation.');
        }

        try {
            DB::beginTransaction();

            $success = $appointment->update([
                'status' => Appointment::STATUS_CONFIRMED,
                'requires_student_confirmation' => false,
                'student_confirmed_at' => now(),
                'notes' => ($appointment->notes ?? '') . "\n\nReschedule confirmed by student on " . now()->format('M j, Y g:i A')
            ]);

            if (!$success) {
                throw new \Exception('Failed to confirm reschedule.');
            }

            DB::commit();

            Log::info('Reschedule confirmed by student', [
                'appointment_id' => $appointment->id,
                'user_id' => $user->id
            ]);

            return redirect()->route('student.appointments.show', $appointment)
                ->with('success', 'Rescheduled appointment confirmed successfully. Your new appointment is now confirmed.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirming reschedule', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'line' => $e->getLine()
            ]);
            return back()->with('error', 'Failed to confirm reschedule. Please try again.');
        }
    }

    /**
     * Show reschedule form (nurses only).
     *
     * @param Appointment $appointment
     * @return View|RedirectResponse
     */
    public function showRescheduleForm(Appointment $appointment): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isNurse()) {
            abort(403, 'Only nurses can reschedule appointments.');
        }

        if (!$appointment->canBeRescheduledByNurse()) {
            return redirect()->route('nurse.appointments.show', $appointment)
                ->with('error', 'This appointment cannot be rescheduled at this time.');
        }

        $appointment->load(['user', 'nurse']);
        
        // Get available slots for the current date
        $availableSlots = Appointment::getAvailableTimeSlots(
            $appointment->appointment_date->format('Y-m-d'),
            $user->id,
            $appointment->id
        );

        return view('nurse.appointments.reschedule', compact('appointment', 'availableSlots'));
    }

    /**
     * Accept follow-up (students only).
     *
     * @param Request $request
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function acceptFollowUp(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isStudent() || $appointment->user_id != $user->id) {
            abort(403, 'Only students can accept their own follow-up appointments.');
        }

        if (!$appointment->isFollowUpPending()) {
            return back()->with('error', 'This follow-up appointment cannot be accepted at this time.');
        }

        try {
            DB::beginTransaction();

            $success = $appointment->update([
                'status' => Appointment::STATUS_CONFIRMED,
                'student_accepted_followup_at' => now(),
                'notes' => ($appointment->notes ?? '') . "\n\nFollow-up accepted by student on " . now()->format('M j, Y g:i A')
            ]);

            if (!$success) {
                throw new \Exception('Failed to accept follow-up.');
            }

            DB::commit();

            Log::info('Follow-up accepted by student', [
                'appointment_id' => $appointment->id,
                'user_id' => $user->id
            ]);

            return redirect()->route('student.appointments.show', $appointment)
                ->with('success', 'Follow-up appointment accepted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error accepting follow-up', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'line' => $e->getLine()
            ]);
            return back()->with('error', 'Failed to accept follow-up. Please try again.');
        }
    }

    /**
     * Decline follow-up appointment (students only).
     *
     * @param Request $request
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function declineFollowUp(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isStudent() || $appointment->user_id != $user->id) {
            abort(403, 'Only students can decline their own follow-up appointments.');
        }

        if (!$appointment->isFollowUpPending()) {
            return back()->with('error', 'This follow-up appointment cannot be declined at this time.');
        }

        $validated = $request->validate([
            'decline_reason' => 'required|string|min:10|max:500'
        ], [
            'decline_reason.required' => 'Please provide a reason for declining the follow-up.',
            'decline_reason.min' => 'Decline reason must be at least 10 characters.',
        ]);

        try {
            DB::beginTransaction();

            $success = $appointment->update([
                'status' => Appointment::STATUS_REJECTED,
                'decline_reason' => $validated['decline_reason'],
                'student_declined_followup_at' => now(),
                'notes' => ($appointment->notes ?? '') . "\n\nFollow-up declined by student on " . now()->format('M j, Y g:i A') . ":\n" . $validated['decline_reason']
            ]);

            if (!$success) {
                throw new \Exception('Failed to decline follow-up.');
            }

            DB::commit();

            Log::info('Follow-up declined by student', [
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'reason' => $validated['decline_reason']
            ]);

            return redirect()->route('student.appointments.show', $appointment)
                ->with('success', 'Follow-up appointment declined successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error declining follow-up', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'line' => $e->getLine()
            ]);
            return back()->with('error', 'Failed to decline follow-up. Please try again.');
        }
    }

    /**
     * Request follow-up reschedule (students only).
     *
     * @param Request $request
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function requestFollowUpReschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isStudent() || $appointment->user_id != $user->id) {
            abort(403, 'Only students can request follow-up rescheduling of their own appointments.');
        }

        if (!$appointment->isFollowUpPending()) {
            return back()->with('error', 'This follow-up appointment cannot be rescheduled at this time.');
        }

        $validated = $request->validate([
            'reschedule_reason' => 'required|string|min:10|max:500',
            'preferred_new_date' => 'nullable|date|after:today|before:' . now()->addDays(30)->toDateString(),
            'preferred_new_time' => 'nullable|date_format:H:i',
        ], [
            'reschedule_reason.required' => 'Please provide a reason for requesting reschedule.',
            'reschedule_reason.min' => 'Reason must be at least 10 characters.',
            'preferred_new_date.after' => 'Preferred date must be in the future.',
            'preferred_new_date.before' => 'Preferred date cannot be more than 30 days in the future.',
            'preferred_new_time.date_format' => 'Preferred time must be in HH:MM format.',
        ]);

        try {
            DB::beginTransaction();

            $appointment->update([
                'status' => Appointment::STATUS_RESCHEDULE_REQUESTED,
                'reschedule_request_reason' => $validated['reschedule_reason'],
                'student_preferred_new_date' => $validated['preferred_new_date'] ?? null,
                'student_preferred_new_time' => $validated['preferred_new_time'] ?? null,
                'student_requested_reschedule_at' => now(),
                'notes' => ($appointment->notes ?? '') . "\n\nFollow-up reschedule requested by student on " . now()->format('M j, Y g:i A') . ":\n" . $validated['reschedule_reason']
            ]);

            DB::commit();

            Log::info('Follow-up reschedule requested by student', [
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'reason' => $validated['reschedule_reason'],
                'preferred_date' => $validated['preferred_new_date'],
                'preferred_time' => $validated['preferred_new_time']
            ]);

            return redirect()->route('student.appointments.show', $appointment)
                ->with('success', 'Your follow-up reschedule request has been submitted. Clinic staff will review it soon.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error requesting follow-up reschedule', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return back()->withInput()->with('error', 'Failed to submit follow-up reschedule request. Please try again.');
        }
    }

    /**
     * Submit feedback and rating for completed appointment (students only).
     *
     * @param Request $request
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function submitFeedback(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isStudent() || $appointment->user_id != $user->id) {
            abort(403, 'Only students can submit feedback for their own appointments.');
        }

        if (!$appointment->isCompleted()) {
            return back()->with('error', 'Feedback can only be submitted for completed appointments.');
        }

        // Check if feedback already submitted
        if ($appointment->feedback_submitted_at) {
            return back()->with('error', 'Feedback has already been submitted for this appointment.');
        }

        $validated = $request->validate([
            'feedback' => 'required|string|min:10|max:1000',
            'rating' => 'required|integer|between:1,5'
        ], [
            'feedback.required' => 'Please provide feedback.',
            'feedback.min' => 'Feedback must be at least 10 characters.',
            'feedback.max' => 'Feedback must not exceed 1000 characters.',
            'rating.required' => 'Please provide a rating.',
            'rating.between' => 'Rating must be between 1 and 5 stars.',
        ]);

        try {
            DB::beginTransaction();

            $success = $appointment->update([
                'feedback' => $validated['feedback'],
                'rating' => $validated['rating'],
                'feedback_submitted_at' => now(),
                'rating_submitted_at' => now(),
                'notes' => ($appointment->notes ?? '') . "\n\nStudent feedback submitted on " . now()->format('M j, Y g:i A') . " (Rating: {$validated['rating']}/5):\n" . $validated['feedback']
            ]);

            if (!$success) {
                throw new \Exception('Failed to submit feedback.');
            }

            DB::commit();

            Log::info('Feedback submitted by student', [
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'rating' => $validated['rating'],
                'feedback_length' => Str::length($validated['feedback'])
            ]);

            return redirect()->route('student.appointments.show', $appointment)
                ->with('success', 'Thank you for your feedback! Your input helps us improve our services.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting feedback', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return back()->withInput()->with('error', 'Failed to submit feedback. Please try again.');
        }
    }

    /**
     * Reschedule an appointment (nurses only) - FIXED VERSION
     * NOW DIRECTLY CONFIRMS WITHOUT STUDENT CONFIRMATION
     *
     * @param Request $request
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isNurse()) {
            abort(403, 'Only nurses can reschedule appointments.');
        }

        if (!$appointment->canBeRescheduledByNurse()) {
            return back()->with('error', 'This appointment cannot be rescheduled.');
        }

        $validated = $request->validate([
            'new_appointment_date' => 'required|date|after_or_equal:today',
            'new_appointment_time' => 'required',
            'reschedule_reason' => 'required|string|min:10|max:500',
        ]);

        $normalizedTime = strlen($validated['new_appointment_time']) === 5 
            ? $validated['new_appointment_time'] . ':00' 
            : $validated['new_appointment_time'];

        $newDate = Carbon::parse($validated['new_appointment_date']);
        if ($newDate->isWeekend()) {
            return back()->withInput()->with('error', 'Appointments cannot be scheduled on weekends.');
        }

        try {
            DB::beginTransaction();

            $oldDate = $appointment->appointment_date->format('M j, Y');
            $oldTime = Carbon::parse($appointment->appointment_time)->format('g:i A');
            $newDateFormatted = $newDate->format('M j, Y');
            $newTimeFormatted = Carbon::parse($normalizedTime)->format('g:i A');

            // ✅ FIXED: Status directly to CONFIRMED, no student confirmation needed
            $appointment->update([
                'appointment_date' => $validated['new_appointment_date'],
                'appointment_time' => $normalizedTime,
                'status' => Appointment::STATUS_CONFIRMED,  // ✅ DIRECT CONFIRMATION
                'rescheduled_by' => $user->id,
                'rescheduled_at' => now(),
                'reschedule_reason' => $validated['reschedule_reason'],
                'updated_by' => $user->id,
                'notes' => ($appointment->notes ?? '') . "\n\nRescheduled by {$user->full_name} on " . now()->format('M j, Y g:i A')
            ]);

            DB::commit();

            // Send notification AFTER successful commit
            try {
                $appointment->refresh();
                $appointment->load('user');
                
                if ($appointment->user) {
                    $appointment->user->notify(new AppointmentStatusChanged(
                        $appointment,
                        'rescheduled',
                        [
                            'nurse_name' => $user->full_name,
                            'old_date' => $oldDate,
                            'old_time' => $oldTime,
                            'new_date' => $newDateFormatted,
                            'new_time' => $newTimeFormatted,
                            'reason' => $validated['reschedule_reason']
                        ]
                    ));
                    
                    Log::info('✅ Reschedule notification sent', [
                        'appointment_id' => $appointment->id,
                        'student_id' => $appointment->user->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('❌ Failed to send reschedule notification', [
                    'error' => $e->getMessage(),
                    'appointment_id' => $appointment->id,
                ]);
            }

            return redirect()->route('nurse.appointments.show', $appointment)
                ->with('success', "Appointment rescheduled to {$newDateFormatted} at {$newTimeFormatted}. Student has been notified automatically.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rescheduling appointment', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
            ]);
            return back()->withInput()->with('error', 'Failed to reschedule appointment.');
        }
    }

    /**
     * Accept appointment (nurses only) - FIXED VERSION
     *
     * @param Request $request
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function accept(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isNurse()) {
            abort(403, 'Only nurses can accept appointments.');
        }

        if (!$appointment->isPending()) {
            return back()->with('error', 'Only pending appointments can be accepted.');
        }

        try {
            DB::beginTransaction();

            $appointment->update([
                'status' => Appointment::STATUS_CONFIRMED,
                'nurse_id' => $user->id,
                'accepted_by' => $user->id,
                'accepted_at' => now(),
                'updated_by' => $user->id,
                'notes' => ($appointment->notes ?? '') . "\n\nAppointment confirmed by nurse on " . now()->format('M j, Y g:i A')
            ]);

            DB::commit();

            // Send notification AFTER successful commit
            try {
                $appointment->refresh();
                $appointment->load('user');
                
                if ($appointment->user) {
                    $appointment->user->notify(new AppointmentStatusChanged(
                        $appointment,
                        'accepted',
                        ['nurse_name' => $user->full_name]
                    ));
                    
                    Log::info('✅ Accept notification sent', [
                        'appointment_id' => $appointment->id,
                        'student_id' => $appointment->user->id,
                        'student_email' => $appointment->user->email,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('❌ Failed to send accept notification', [
                    'error' => $e->getMessage(),
                    'appointment_id' => $appointment->id,
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            return back()->with('success', 'Appointment accepted successfully and student has been notified!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error accepting appointment', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
                'nurse_id' => $user->id,
            ]);
            return back()->with('error', 'Failed to accept appointment. Please try again.');
        }
    }

    /**
     * Reject appointment (nurses only) - FIXED VERSION
     *
     * @param Request $request
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function reject(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isNurse()) {
            abort(403, 'Only nurses can reject appointments.');
        }

        if (!$appointment->isPending()) {
            return back()->with('error', 'Only pending appointments can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500'
        ]);

        try {
            DB::beginTransaction();

            $appointment->update([
                'status' => Appointment::STATUS_REJECTED,
                'rejected_by' => $user->id,
                'rejected_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
                'updated_by' => $user->id,
                'notes' => ($appointment->notes ?? '') . "\n\nRejected by nurse on " . now()->format('M j, Y g:i A')
            ]);

            DB::commit();

            // Send notification AFTER successful commit
            try {
                $appointment->refresh();
                $appointment->load('user');
                
                if ($appointment->user) {
                    $appointment->user->notify(new AppointmentStatusChanged(
                        $appointment,
                        'rejected',
                        [
                            'nurse_name' => $user->full_name,
                            'reason' => $validated['rejection_reason']
                        ]
                    ));
                    
                    Log::info('✅ Reject notification sent', [
                        'appointment_id' => $appointment->id,
                        'student_id' => $appointment->user->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('❌ Failed to send reject notification', [
                    'error' => $e->getMessage(),
                    'appointment_id' => $appointment->id,
                ]);
            }

            return back()->with('success', 'Appointment rejected successfully and student has been notified.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting appointment', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
            ]);
            return back()->with('error', 'Failed to reject appointment.');
        }
    }

    /**
     * Cancel appointment (nurses only) - FIXED VERSION
     *
     * @param Request $request
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isNurse()) {
            abort(403, 'Only nurses can cancel appointments.');
        }

        if (!$appointment->canBeCancelled()) {
            return back()->with('error', 'This appointment cannot be cancelled.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:500'
        ]);

        try {
            DB::beginTransaction();

            $appointment->update([
                'status' => Appointment::STATUS_CANCELLED,
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['cancellation_reason'],
                'updated_by' => $user->id,
                'notes' => ($appointment->notes ?? '') . "\n\nCancelled by nurse on " . now()->format('M j, Y g:i A')
            ]);

            DB::commit();

            // Send notification AFTER successful commit
            try {
                $appointment->refresh();
                $appointment->load('user');
                
                if ($appointment->user) {
                    $appointment->user->notify(new AppointmentStatusChanged(
                        $appointment,
                        'cancelled',
                        [
                            'nurse_name' => $user->full_name,
                            'reason' => $validated['cancellation_reason']
                        ]
                    ));
                    
                    Log::info('✅ Cancel notification sent', [
                        'appointment_id' => $appointment->id,
                        'student_id' => $appointment->user->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('❌ Failed to send cancel notification', [
                    'error' => $e->getMessage(),
                    'appointment_id' => $appointment->id,
                ]);
            }

            return back()->with('success', 'Appointment cancelled successfully and student has been notified.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling appointment', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
            ]);
            return back()->with('error', 'Failed to cancel appointment.');
        }
    }

    /**
     * Start consultation for confirmed appointment (nurses only).
     *
     * @param Appointment $appointment
     * @return RedirectResponse
     */
    public function startConsultation(Appointment $appointment): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isNurse()) {
            abort(403, 'Only nurses can start consultations.');
        }

        if (!$appointment->isConfirmed()) {
            return back()->with('error', 'Only confirmed appointments can be started.');
        }

        // Check if consultation already exists
        if ($appointment->consultation) {
            return redirect()->route('nurse.consultations.show', $appointment->consultation)
                ->with('info', 'Consultation already in progress.');
        }

        try {
            DB::beginTransaction();

            // Mark appointment as completed and assign nurse
            $success = $appointment->update([
                'nurse_id' => $user->id,
                'accepted_by' => $user->id,
                'accepted_at' => now(),
                'completed_by' => $user->id,
                'completed_at' => now(),
                'status' => Appointment::STATUS_COMPLETED,
                'notes' => ($appointment->notes ?? '') . "\n\nConsultation started by {$user->full_name} on " . now()->format('M j, Y g:i A')
            ]);

            if (!$success) {
                throw new \Exception('Failed to update appointment status.');
            }

            // Create consultation record
            $consultation = Consultation::create([
                'appointment_id' => $appointment->id,
                'nurse_id' => $user->id,
                'student_id' => $appointment->user_id,
                'type' => $appointment->appointment_type,
                'priority' => $appointment->is_urgent ? 'high' : 'normal',
                'status' => 'in_progress',
                'started_at' => now(),
                'notes' => "Consultation started for {$appointment->getPatientName()} - {$appointment->reason}"
            ]);

            DB::commit();

            Log::info('Consultation started successfully', [
                'appointment_id' => $appointment->id,
                'consultation_id' => $consultation->id,
                'nurse_id' => $user->id,
                'student_id' => $appointment->user_id,
                'type' => $appointment->appointment_type,
                'priority' => $appointment->priority
            ]);

            return redirect()->route('nurse.consultations.show', $consultation)
                ->with('success', 'Consultation started successfully. You can now record patient details.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting consultation', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
                'nurse_id' => $user->id,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return back()->with('error', 'Failed to start consultation. Please try again.');
        }
    }

    /**
     * Get confirmed appointments for API endpoint
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getConfirmedAppointments(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $query = Appointment::with(['user', 'nurse', 'acceptedBy'])
                ->where('status', Appointment::STATUS_CONFIRMED)
                ->whereDate('appointment_date', '>=', today())
                ->orderBy('appointment_date')
                ->orderBy('appointment_time');

            // Apply user-specific filters
            if ($user->isStudent()) {
                $query->where('user_id', $user->id);
            } elseif ($user->isNurse()) {
                $query->where(function($q) use ($user) {
                    $q->whereNull('nurse_id')
                      ->orWhere('nurse_id', $user->id);
                });
            }

            $appointments = $query->get();

            return response()->json([
                'success' => true,
                'appointments' => $appointments->map(function($appointment) {
                    return [
                        'id' => $appointment->id,
                        'patient_name' => $appointment->user->full_name ?? 'Unknown',
                        'student_id' => $appointment->user->student_id ?? 'N/A',
                        'appointment_date' => $appointment->appointment_date->format('M d, Y'),
                        'appointment_time' => $appointment->appointment_time ? Carbon::parse($appointment->appointment_time)->format('g:i A') : 'Not set',
                        'reason' => $appointment->reason,
                        'type' => $appointment->appointment_type_display,
                        'is_urgent' => $appointment->is_urgent,
                        'priority' => $appointment->priority_display,
                        'nurse_name' => $appointment->nurse->full_name ?? 'Not assigned',
                    ];
                }),
                'count' => $appointments->count(),
                'message' => $appointments->count() > 0 
                    ? "Found {$appointments->count()} confirmed appointment(s)" 
                    : "No confirmed appointments found"
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching confirmed appointments', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load confirmed appointments',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Apply quick filters to the appointment query.
     *
     * @param $query
     * @param string $filter
     * @param User $user
     * @return void
     */
    private function applyQuickFilters($query, string $filter, User $user): void
    {
        switch ($filter) {
            case 'today':
                $query->today();
                break;
            case 'upcoming':
                $query->upcoming();
                break;
            case 'this_week':
                $query->thisWeek();
                break;
            case 'next_week':
                $query->nextWeek();
                break;
            case 'overdue':
                $query->overdue();
                break;
            case 'urgent':
                $query->urgent();
                break;
            case 'confirmed': // ✅ NEW: Confirmed filter
                $this->applyConfirmedFilter($query);
                break;
            case 'requiring_action':
                if ($user->isNurse()) {
                    $query->requiringAction();
                }
                break;
            default:
                break;
        }
    }

    /**
     * Apply confirmed filter to the query
     * 
     * @param $query
     * @return mixed
     */
    private function applyConfirmedFilter($query)
    {
        return $query->where('status', Appointment::STATUS_CONFIRMED)
                    ->whereDate('appointment_date', '>=', today())
                    ->orderBy('appointment_date')
                    ->orderBy('appointment_time');
    }

    /**
     * Get available dates for appointments (up to specified days ahead).
     * FIXED VERSION - More robust and returns proper data
     *
     * @param int $daysAhead
     * @param string $preferredTime
     * @param int|null $nurseId
     * @return array
     */
    private function getAvailableDates(int $daysAhead = 30, string $preferredTime = 'any', ?int $nurseId = null): array
    {
        try {
            $startDate = today();
            $endDate = today()->addDays($daysAhead);

            // Get all booked appointments in a single query
            $bookedAppointments = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
                ->whereIn('status', [
                    Appointment::STATUS_PENDING, 
                    Appointment::STATUS_CONFIRMED,
                    Appointment::STATUS_RESCHEDULED
                ])
                ->whereNotNull('appointment_time')
                ->get(['appointment_date', 'appointment_time'])
                ->groupBy('appointment_date');

            $dates = [];
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Skip weekends
                if (!$currentDate->isWeekend()) {
                    $dateString = $currentDate->format('Y-m-d');
                    
                    // Get booked slots for this date
                    $bookedSlots = $bookedAppointments->get($dateString, collect())
                        ->pluck('appointment_time')
                        ->toArray();

                    // Generate available slots
                    $allSlots = $this->generateTimeSlots();
                    $availableSlots = array_filter($allSlots, function($slot) use ($bookedSlots) {
                        return !in_array($slot['value'], $bookedSlots);
                    });

                    // Filter by preferred time if specified
                    if ($preferredTime !== 'any') {
                        $availableSlots = array_filter($availableSlots, function($slot) use ($preferredTime) {
                            return $slot['period'] === $preferredTime;
                        });
                    }

                    if (count($availableSlots) > 0) {
                        $dates[] = [
                            'date' => $dateString,
                            'formatted' => $currentDate->format('M d, Y'),
                            'formatted_full' => $currentDate->format('M d, Y (l)'),
                            'is_today' => $currentDate->isToday(),
                            'day_name' => $currentDate->format('l'),
                            'day_number' => $currentDate->day,
                            'available_slots' => count($availableSlots),
                            'total_slots' => count($allSlots),
                            'has_morning_slots' => collect($availableSlots)->contains('period', 'morning'),
                            'has_afternoon_slots' => collect($availableSlots)->contains('period', 'afternoon'),
                        ];
                    }
                }
                $currentDate->addDay();
            }

            return $dates;
            
        } catch (\Exception $e) {
            Log::error('Error in getAvailableDates', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            
            return [];
        }
    }

    /**
     * Generate time slots without database queries
     */
    private function generateTimeSlots(): array
    {
        $morningSlots = [
            ['value' => '09:00:00', 'label' => '9:00 AM', 'period' => 'morning'],
            ['value' => '09:30:00', 'label' => '9:30 AM', 'period' => 'morning'],
            ['value' => '10:00:00', 'label' => '10:00 AM', 'period' => 'morning'],
            ['value' => '10:30:00', 'label' => '10:30 AM', 'period' => 'morning'],
            ['value' => '11:00:00', 'label' => '11:00 AM', 'period' => 'morning'],
            ['value' => '11:30:00', 'label' => '11:30 AM', 'period' => 'morning'],
        ];

        $afternoonSlots = [
            ['value' => '13:00:00', 'label' => '1:00 PM', 'period' => 'afternoon'],
            ['value' => '13:30:00', 'label' => '1:30 PM', 'period' => 'afternoon'],
            ['value' => '14:00:00', 'label' => '2:00 PM', 'period' => 'afternoon'],
            ['value' => '14:30:00', 'label' => '2:30 PM', 'period' => 'afternoon'],
            ['value' => '15:00:00', 'label' => '3:00 PM', 'period' => 'afternoon'],
            ['value' => '15:30:00', 'label' => '3:30 PM', 'period' => 'afternoon'],
            ['value' => '16:00:00', 'label' => '4:00 PM', 'period' => 'afternoon'],
            ['value' => '16:30:00', 'label' => '4:30 PM', 'period' => 'afternoon'],
        ];

        return array_merge($morningSlots, $afternoonSlots);
    }

    /**
     * Get appointment statistics.
     *
     * @param User $user
     * @return array
     */
    private function getAppointmentStatistics(User $user): array
    {
        $baseQuery = Appointment::query();
        
        if ($user->isStudent()) {
            $baseQuery->forStudent($user->id);
        } elseif ($user->isNurse()) {
            $baseQuery->where(function($q) use ($user) {
                $q->whereNull('nurse_id')
                  ->orWhere('nurse_id', $user->id);
            });
        }

        $today = today();
        $thisMonth = now()->startOfMonth();
        $thisMonthEnd = now()->endOfMonth();

        return [
            // Overall stats
            'total' => $baseQuery->count(),
            'pending' => $baseQuery->clone()->pending()->count(),
            'confirmed' => $baseQuery->clone()->confirmed()->count(), // ✅ Confirmed count
            'completed' => $baseQuery->clone()->completed()->count(),
            'cancelled' => $baseQuery->clone()->cancelled()->count(),
            'rejected' => $baseQuery->clone()->rejected()->count(),
            
            // Time-based stats
            'today' => $baseQuery->clone()->whereDate('appointment_date', $today)->count(),
            'today_confirmed' => $baseQuery->clone()->whereDate('appointment_date', $today)->confirmed()->count(),
            'upcoming' => $baseQuery->clone()->upcoming()->count(),
            
            // Priority stats
            'urgent' => $baseQuery->clone()->urgent()->count(),
            'high_priority' => $baseQuery->clone()->where('priority', '>=', Appointment::PRIORITY_HIGH)->count(),
            
            // Monthly stats
            'this_month_total' => $baseQuery->clone()->whereBetween('appointment_date', [$thisMonth, $thisMonthEnd])->count(),
            'this_month_completed' => $baseQuery->clone()->whereBetween('completed_at', [$thisMonth, $thisMonthEnd])->count(),
            'this_month_confirmed' => $baseQuery->clone()->whereBetween('appointment_date', [$thisMonth, $thisMonthEnd])->confirmed()->count(), // ✅ Monthly confirmed
            
            // Action items
            'requiring_action' => $user->isNurse() ? $baseQuery->clone()->requiringAction()->count() : 0,
            'overdue' => $baseQuery->clone()->overdue()->count(),
            
            // Follow-ups
            'follow_up_pending' => $baseQuery->clone()->where('is_follow_up', true)->where('status', Appointment::STATUS_FOLLOW_UP_PENDING)->count(),
        ];
    }

    /**
     * Fallback time slots when the main method fails
     */
    private function getFallbackTimeSlots(): array
    {
        return [
            ['value' => '09:00:00', 'label' => '9:00 AM', 'period' => 'morning', 'is_available' => true],
            ['value' => '10:00:00', 'label' => '10:00 AM', 'period' => 'morning', 'is_available' => true],
            ['value' => '13:30:00', 'label' => '1:30 PM', 'period' => 'afternoon', 'is_available' => true],
            ['value' => '14:30:00', 'label' => '2:30 PM', 'period' => 'afternoon', 'is_available' => true],
            ['value' => '15:30:00', 'label' => '3:30 PM', 'period' => 'afternoon', 'is_available' => true],
            ['value' => '16:30:00', 'label' => '4:30 PM', 'period' => 'afternoon', 'is_available' => true],
        ];
    }

    /**
     * Confirm appointment (helper method)
     *
     * @param Appointment $appointment
     * @param int $nurseId
     * @return bool
     */
    private function confirmAppointment(Appointment $appointment, int $nurseId): bool
    {
        try {
            return $appointment->update([
                'status' => Appointment::STATUS_CONFIRMED,
                'nurse_id' => $nurseId,
                'accepted_by' => $nurseId,
                'accepted_at' => now(),
                'updated_by' => $nurseId,
                'notes' => ($appointment->notes ?? '') . "\n\nAppointment confirmed by nurse on " . now()->format('M j, Y g:i A')
            ]);
        } catch (\Exception $e) {
            Log::error('Error confirming appointment', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
                'nurse_id' => $nurseId,
                'line' => $e->getLine()
            ]);
            return false;
        }
    }

    /**
     * Reject appointment (helper method)
     *
     * @param Appointment $appointment
     * @param int $nurseId
     * @param string $reason
     * @return bool
     */
    private function rejectAppointment(Appointment $appointment, int $nurseId, string $reason): bool
    {
        try {
            return $appointment->update([
                'status' => Appointment::STATUS_REJECTED,
                'rejected_by' => $nurseId,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
                'updated_by' => $nurseId,
                'notes' => ($appointment->notes ?? '') . "\n\nAppointment rejected by nurse on " . now()->format('M j, Y g:i A') . ":\nReason: " . Str::limit($reason, 200)
            ]);
        } catch (\Exception $e) {
            Log::error('Error rejecting appointment', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
                'nurse_id' => $nurseId,
                'line' => $e->getLine()
            ]);
            return false;
        }
    }

    /**
     * Cancel appointment (helper method)
     *
     * @param Appointment $appointment
     * @param int $userId
     * @param string $reason
     * @return bool
     */
    private function cancelAppointment(Appointment $appointment, int $userId, string $reason): bool
    {
        try {
            return $appointment->update([
                'status' => Appointment::STATUS_CANCELLED,
                'cancelled_by' => $userId,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'updated_by' => $userId,
                'notes' => ($appointment->notes ?? '') . "\n\nAppointment cancelled on " . now()->format('M j, Y g:i A') . ":\nReason: " . Str::limit($reason, 200)
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling appointment', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointment->id,
                'user_id' => $userId,
                'line' => $e->getLine()
            ]);
            return false;
        }
    }
}