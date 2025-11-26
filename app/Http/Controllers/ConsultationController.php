<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Appointment;
use App\Models\User;
use App\Models\MedicalRecord;
use App\Models\SymptomLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Notifications\ConsultationNotification;

class ConsultationController extends Controller
{
    /**
     * ============================================
     * STUDENT CONSULTATION METHODS
     * ============================================
     */

    /**
     * Display a listing of consultations for students
     */
    public function studentIndex(Request $request): View
    {
        $user = Auth::user();

        if (!$user->isStudent()) {
            abort(403, 'Only students can view their consultations.');
        }

        $query = Consultation::with(['nurse'])
            ->where('student_id', $user->id);

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply date filter
        if ($request->filled('date')) {
            $query->whereDate('consultation_date', $request->date);
        }

        // Apply search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('chief_complaint', 'like', "%{$search}%")
                  ->orWhere('diagnosis', 'like', "%{$search}%")
                  ->orWhere('symptoms_description', 'like', "%{$search}%");
            });
        }

        // Apply filter shortcuts
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'completed':
                    $query->where('status', Consultation::STATUS_COMPLETED);
                    break;
                case 'in_progress':
                    $query->where('status', Consultation::STATUS_IN_PROGRESS);
                    break;
                case 'this_week':
                    $query->whereBetween('consultation_date', [
                        now()->startOfWeek(), 
                        now()->endOfWeek()
                    ]);
                    break;
                case 'this_month':
                    $query->whereMonth('consultation_date', now()->month)
                          ->whereYear('consultation_date', now()->year);
                    break;
                case 'walk_in':
                    $query->where('type', Consultation::TYPE_WALK_IN);
                    break;
                case 'appointment':
                    $query->where('type', Consultation::TYPE_APPOINTMENT);
                    break;
            }
        }

        $consultations = $query->orderBy('consultation_date', 'desc')
                               ->paginate(15)
                               ->withQueryString();

        // Calculate statistics
        $stats = [
            'total' => Consultation::where('student_id', $user->id)->count(),
            'completed' => Consultation::where('student_id', $user->id)
                ->where('status', Consultation::STATUS_COMPLETED)
                ->count(),
            'in_progress' => Consultation::where('student_id', $user->id)
                ->where('status', Consultation::STATUS_IN_PROGRESS)
                ->count(),
            'this_month' => Consultation::where('student_id', $user->id)
                ->whereMonth('consultation_date', now()->month)
                ->whereYear('consultation_date', now()->year)
                ->count(),
            'walk_in' => Consultation::where('student_id', $user->id)
                ->where('type', Consultation::TYPE_WALK_IN)
                ->count(),
            'appointment' => Consultation::where('student_id', $user->id)
                ->where('type', Consultation::TYPE_APPOINTMENT)
                ->count(),
        ];

        return view('student.consultations.index', compact('consultations', 'stats'));
    }

    /**
     * Display the specified consultation for student
     */
    public function studentShow(Consultation $consultation): View
    {
        $user = Auth::user();

        // Authorization check
        if (!$user->isStudent() || $consultation->student_id != $user->id) {
            abort(403, 'You can only view your own consultations.');
        }

        // Load relationships
        $consultation->load(['nurse']);

        return view('student.consultations.show', compact('consultation'));
    }

    /**
     * Download consultation summary (PDF)
     */
    public function downloadConsultation(Consultation $consultation)
    {
        $user = Auth::user();

        // Authorization check
        if ($user->isStudent() && $consultation->student_id != $user->id) {
            abort(403, 'You can only download your own consultations.');
        } elseif ($user->isNurse() && $consultation->nurse_id && $consultation->nurse_id != $user->id) {
            abort(403, 'Unauthorized access.');
        }

        // Load relationships
        $consultation->load(['student', 'nurse']);

        // Generate PDF
        try {
            $pdf = \PDF::loadView('consultations.pdf', [
                'consultation' => $consultation,
                'generatedAt' => now(),
                'generatedBy' => $user
            ]);

            $filename = 'consultation_' . $consultation->id . '_' . now()->format('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generating consultation PDF: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to generate PDF. Please try again.');
        }
    }

    /**
     * ============================================
     * NURSE CONSULTATION METHODS
     * ============================================
     */

    /**
     * Display a listing of consultations (Nurses only)
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        if (!$user->isNurse()) {
            abort(403, 'Only nurses can view consultations.');
        }

        $query = Consultation::with(['student', 'nurse']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('date')) {
            $query->whereDate('consultation_date', $request->date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('chief_complaint', 'like', "%{$search}%")
                  ->orWhere('diagnosis', 'like', "%{$search}%")
                  ->orWhere('symptoms_description', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($studentQuery) use ($search) {
                      $studentQuery->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name', 'like', "%{$search}%")
                                  ->orWhere('student_id', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'today':
                    $query->whereDate('consultation_date', today());
                    break;
                case 'in_progress':
                    $query->where('status', Consultation::STATUS_IN_PROGRESS);
                    break;
                case 'completed_today':
                    $query->where('status', Consultation::STATUS_COMPLETED)
                          ->whereDate('consultation_date', today());
                    break;
                case 'critical':
                    $query->where('priority', Consultation::PRIORITY_CRITICAL);
                    break;
                case 'high_priority':
                    $query->where('priority', Consultation::PRIORITY_HIGH);
                    break;
                case 'walk_in':
                    $query->where('type', Consultation::TYPE_WALK_IN);
                    break;
                case 'appointment':
                    $query->where('type', Consultation::TYPE_APPOINTMENT);
                    break;
            }
        }

        // Order by priority and consultation date
        $consultations = $query->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
                              ->orderBy('consultation_date', 'desc')
                              ->paginate(15)
                              ->withQueryString();

        $stats = $this->getConsultationStatistics();

        return view('nurse.consultations.index', compact('consultations', 'stats'));
    }

    /**
     * Show the queue/dashboard view (Nurses only)
     */
    public function queue(): View
    {
        $user = Auth::user();

        if (!$user->isNurse()) {
            abort(403, 'Only nurses can view consultation queue.');
        }

        // Get today's active consultations organized by status
        $registeredConsultations = Consultation::where('status', Consultation::STATUS_REGISTERED)
                                             ->whereDate('consultation_date', Carbon::today())
                                             ->with(['student', 'nurse'])
                                             ->get();

        $inProgressConsultations = Consultation::where('status', Consultation::STATUS_IN_PROGRESS)
                                             ->whereDate('consultation_date', Carbon::today())
                                             ->with(['student', 'nurse'])
                                             ->get();

        $completedToday = Consultation::where('status', Consultation::STATUS_COMPLETED)
                                    ->whereDate('consultation_date', Carbon::today())
                                    ->count();

        $stats = $this->getConsultationStatistics();

        return view('nurse.consultations.queue', compact(
            'registeredConsultations',
            'inProgressConsultations',
            'completedToday',
            'stats'
        ));
    }

    /**
     * Display consultation creation form with student search
     */
    public function create(): View
    {
        $user = Auth::user();

        if (!$user->isNurse()) {
            abort(403, 'Only nurses can create consultations.');
        }

        // Get today's appointments for reference
        $todaysAppointments = Appointment::whereDate('appointment_date', today())
            ->whereIn('status', ['confirmed', 'rescheduled'])
            ->with('user')
            ->get()
            ->map(function($appointment) {
                return [
                    'id' => $appointment->id,
                    'student_id' => $appointment->user_id,
                    'student_name' => $appointment->user->full_name,
                    'student_number' => $appointment->user->student_id,
                    'appointment_time' => $appointment->appointment_time,
                    'reason' => $appointment->reason,
                ];
            });

        return view('nurse.consultations.create', [
            'consultationTypes' => [
                'walk_in' => 'Walk-in',
                'appointment' => 'Appointment'
            ],
            'consultationPriorities' => [
                'low' => 'Low',
                'medium' => 'Medium', 
                'high' => 'High',
                'critical' => 'Critical'
            ],
            'todaysAppointments' => $todaysAppointments,
        ]);
    }

    /**
     * Search students for consultation (GET version)
     */
    public function searchStudents(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isNurse()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $search = $request->get('search', '');

        if (strlen($search) < 2) {
            return response()->json(['students' => []]);
        }

        $students = User::where('role', 'student')
            ->where(function($query) use ($search) {
                $query->where('student_id', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            })
            ->with(['medicalRecord'])
            ->limit(10)
            ->get()
            ->map(function($student) {
                // Check for active consultation today
                $hasActiveConsultation = Consultation::where('student_id', $student->id)
                    ->whereIn('status', [Consultation::STATUS_REGISTERED, Consultation::STATUS_IN_PROGRESS])
                    ->whereDate('consultation_date', today())
                    ->exists();

                return [
                    'id' => $student->id,
                    'student_id' => $student->student_id,
                    'full_name' => $student->full_name,
                    'email' => $student->email,
                    'phone' => $student->phone,
                    'gender' => $student->gender,
                    'age' => $student->date_of_birth ? now()->diffInYears($student->date_of_birth) : null,
                    'medical_record' => $student->medicalRecord ? [
                        'blood_type' => $student->medicalRecord->blood_type,
                        'allergies' => $student->medicalRecord->allergies,
                        'chronic_conditions' => $student->medicalRecord->chronic_conditions,
                    ] : null,
                    'has_active_consultation' => $hasActiveConsultation,
                ];
            });

        return response()->json(['students' => $students]);
    }

    /**
     * Check student status for appointments and active consultations
     */
    public function checkStudentStatus($studentId): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isNurse()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Find student by ID
            $student = User::where('id', $studentId)->where('role', 'student')->first();

            if (!$student) {
                return response()->json([
                    'has_active_consultation' => false,
                    'has_appointment_today' => false,
                    'appointment_details' => null
                ], 200);
            }

            // Check for active consultation today
            $hasActiveConsultation = Consultation::where('student_id', $student->id)
                ->whereIn('status', [Consultation::STATUS_REGISTERED, Consultation::STATUS_IN_PROGRESS])
                ->whereDate('consultation_date', today())
                ->exists();

            // Check for scheduled appointment today
            $hasAppointmentToday = Appointment::where('user_id', $student->id)
                ->whereDate('appointment_date', today())
                ->whereIn('status', ['confirmed', 'rescheduled'])
                ->exists();

            $appointmentDetails = null;
            if ($hasAppointmentToday) {
                $appointmentDetails = Appointment::where('user_id', $student->id)
                    ->whereDate('appointment_date', today())
                    ->whereIn('status', ['confirmed', 'rescheduled'])
                    ->first();
            }

            return response()->json([
                'has_active_consultation' => $hasActiveConsultation,
                'has_appointment_today' => $hasAppointmentToday,
                'appointment_details' => $appointmentDetails
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error checking student status: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to check student status'], 500);
        }
    }

   // REPLACE ENTIRE getStudentMedicalData method in ConsultationController.php

/**
 * Get complete student medical data for consultation creation
 */
public function getStudentMedicalData($studentId): JsonResponse
{
    $user = Auth::user();

    if (!$user->isNurse()) {
        Log::warning('Unauthorized medical data access attempt', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'student_id' => $studentId
        ]);
        return response()->json(['error' => 'Unauthorized access'], 403);
    }

    try {
        // Validate student ID
        if (!is_numeric($studentId) || $studentId <= 0) {
            Log::warning('Invalid student ID format', ['student_id' => $studentId]);
            return response()->json(['error' => 'Invalid student ID'], 400);
        }

        // Find student with basic medical record
        $student = User::where('id', (int)$studentId)
                      ->where('role', 'student')
                      ->with(['medicalRecord'])
                      ->first();

        if (!$student) {
            Log::warning('Student not found for medical data', ['student_id' => $studentId]);
            return response()->json(['error' => 'Student not found'], 404);
        }

        Log::info('Student found, processing medical data', [
            'student_id' => $student->id,
            'student_name' => $student->full_name
        ]);

        $medicalRecord = $student->medicalRecord;
        
        // Get medical record summary
        $medicalSummary = $this->getMedicalRecordSummary($student);

        // Calculate BMI safely
        $bmiData = $this->calculateBMI($medicalRecord);

        // Get recent symptoms with limit
        $recentSymptoms = SymptomLog::where('user_id', $student->id)
            ->orderBy('logged_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent consultations
        $recentConsultations = Consultation::where('student_id', $student->id)
            ->where('status', Consultation::STATUS_COMPLETED)
            ->orderBy('consultation_date', 'desc')
            ->limit(3)
            ->get();

        // Get recent appointments with safe handling
        $recentAppointments = Appointment::where('user_id', $student->id)
            ->orderBy('appointment_date', 'desc')
            ->limit(5)
            ->get()
            ->map(function($appointment) {
                return $this->formatAppointmentData($appointment);
            });

        // Safe date formatting function
        $formatDateSafely = function ($date) {
            if (!$date) return null;
            try {
                if ($date instanceof \Carbon\Carbon) {
                    return $date->format('M j, Y');
                }
                return \Carbon\Carbon::parse($date)->format('M j, Y');
            } catch (\Exception $e) {
                Log::warning('Date parsing failed', ['date' => $date, 'error' => $e->getMessage()]);
                return null;
            }
        };

        // Calculate age safely
        $age = null;
        if ($student->date_of_birth) {
            try {
                $birthDate = $student->date_of_birth;
                if ($birthDate instanceof \Carbon\Carbon) {
                    $age = $birthDate->age;
                } else {
                    $age = \Carbon\Carbon::parse($birthDate)->age;
                }
            } catch (\Exception $e) {
                Log::warning('Age calculation failed', [
                    'student_id' => $student->id,
                    'date_of_birth' => $student->date_of_birth,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Prepare the response data with null safety - INLINE formatting, no method call
        $data = [
            'student' => [
                'id' => $student->id,
                'first_name' => $student->first_name ?? '',
                'last_name' => $student->last_name ?? '',
                'student_id' => $student->student_id ?? '',
                'full_name' => $student->full_name ?? 'Unknown Student',
                'email' => $student->email ?? 'No email',
                'phone' => $student->phone ?? 'No phone',
                'gender' => $student->gender ?? 'Not specified',
                'age' => $age,
                'birth_date' => $formatDateSafely($student->date_of_birth),
                'date_of_birth' => $formatDateSafely($student->date_of_birth),
                'course' => $student->course ?? 'Not specified',
                'year_level' => $student->year_level ?? 'Not specified',
                'section' => $student->section ?? 'Not specified',
                'address' => $student->address ?? 'Not provided',
            ],
            'medical_record' => $medicalRecord ? [
                'blood_type' => $medicalRecord->blood_type ?? 'Not recorded',
                'height' => $medicalRecord->height ?? 'Not recorded',
                'weight' => $medicalRecord->weight ?? 'Not recorded',
                'bmi' => $bmiData['bmi'] ?? null,
                'bmi_category' => $bmiData['category'] ?? null,
                'allergies' => $medicalRecord->allergies ?? 'None recorded',
                'chronic_conditions' => $medicalRecord->chronic_conditions ?? 'None recorded',
                'current_medications' => $medicalRecord->maintenance_drugs_specify ?? ($medicalRecord->current_medications ?? 'None recorded'),
                'past_illnesses' => $medicalRecord->past_illnesses ?? 'None recorded',
                'is_fully_vaccinated' => $medicalRecord->is_fully_vaccinated ?? false,
                'vaccine_type' => $medicalRecord->vaccine_type ?? ($medicalRecord->vaccine_name ?? null),
                'vaccine_date' => $medicalRecord->vaccine_date ? $formatDateSafely($medicalRecord->vaccine_date) : null,
                'emergency_contact_1' => [
                    'name' => $medicalRecord->emergency_contact_name_1 ?? null,
                    'phone' => $medicalRecord->emergency_contact_number_1 ?? null,
                    'relationship' => $medicalRecord->emergency_contact_relationship_1 ?? null,
                ],
                'emergency_contact_2' => [
                    'name' => $medicalRecord->emergency_contact_name_2 ?? null,
                    'phone' => $medicalRecord->emergency_contact_number_2 ?? null,
                    'relationship' => $medicalRecord->emergency_contact_relationship_2 ?? null,
                ],
            ] : null,
            'medical_summary' => $medicalSummary,
            'recent_symptoms' => $recentSymptoms->map(function($symptom) use ($formatDateSafely) {
                return [
                    'symptoms' => $symptom->symptoms ?? 'No symptoms recorded',
                    'severity' => $symptom->severity_rating ?? 'Not rated',
                    'logged_at' => $symptom->logged_at ? 
                        $formatDateSafely($symptom->logged_at) . ' ' . $symptom->logged_at->format('H:i') : 
                        'No date',
                    'notes' => $symptom->notes ?? 'No notes',
                ];
            }),
            'recent_consultations' => $recentConsultations->map(function($consultation) use ($formatDateSafely) {
                return [
                    'date' => $formatDateSafely($consultation->consultation_date),
                    'consultation_date' => $formatDateSafely($consultation->consultation_date),
                    'chief_complaint' => $consultation->chief_complaint ?? 'Not specified',
                    'diagnosis' => $consultation->diagnosis ?? 'Not specified',
                    'treatment' => $consultation->treatment_provided ?? 'Not specified',
                    'priority' => $consultation->priority ?? 'medium',
                ];
            }),
            'recent_appointments' => $recentAppointments,
        ];

        Log::info('Medical data prepared successfully', [
            'student_id' => $student->id,
            'has_medical_record' => !is_null($medicalRecord),
            'data_points' => [
                'symptoms' => $recentSymptoms->count(),
                'consultations' => $recentConsultations->count(),
                'appointments' => $recentAppointments->count()
            ]
        ]);

        return response()->json($data);

    } catch (\Exception $e) {
        Log::error('Medical data fetch exception - MAIN CATCH', [
            'student_id' => $studentId,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => 'Failed to fetch medical data',
            'message' => 'An error occurred while loading student medical information',
            'debug_info' => config('app.debug') ? [
                'error_message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ] : null
        ], 500);
    }
}

// ADD THIS NEW METHOD to format medical record data safely
private function formatMedicalRecordData($medicalRecord, $bmiData, $formatDateSafely): array
{
    if (!$medicalRecord) {
        return null;
    }

    return [
        'blood_type' => $medicalRecord->blood_type ?? 'Not recorded',
        'height' => $medicalRecord->height ?? 'Not recorded',
        'weight' => $medicalRecord->weight ?? 'Not recorded',
        'bmi' => $bmiData['bmi'] ?? null,
        'bmi_category' => $bmiData['category'] ?? null,
        'allergies' => $medicalRecord->allergies ?? 'None recorded',
        'chronic_conditions' => $medicalRecord->chronic_conditions ?? 'None recorded',
        'current_medications' => $medicalRecord->maintenance_drugs_specify ?? $medicalRecord->current_medications ?? 'None recorded',
        'past_illnesses' => $medicalRecord->past_illnesses ?? 'None recorded',
        'is_fully_vaccinated' => $medicalRecord->is_fully_vaccinated ?? false,
        'vaccine_type' => $medicalRecord->vaccine_type ?? $medicalRecord->vaccine_name ?? null,
        'vaccine_date' => $medicalRecord->vaccine_date ? $formatDateSafely($medicalRecord->vaccine_date) : null,
        'emergency_contact_1' => [
            'name' => $medicalRecord->emergency_contact_name_1 ?? null,
            'phone' => $medicalRecord->emergency_contact_number_1 ?? null,
            'relationship' => $medicalRecord->emergency_contact_relationship_1 ?? null,
        ],
        'emergency_contact_2' => [
            'name' => $medicalRecord->emergency_contact_name_2 ?? null,
            'phone' => $medicalRecord->emergency_contact_number_2 ?? null,
            'relationship' => $medicalRecord->emergency_contact_relationship_2 ?? null,
        ],
    ];
}

/**
 * Format appointment data safely
 */
private function formatAppointmentData($appointment): array
{
    try {
        $appointmentDate = $appointment->appointment_date;
        $appointmentTime = $appointment->appointment_time;
        
        $appointmentDateTime = null;
        if ($appointmentDate && $appointmentTime) {
            $appointmentDateTime = Carbon::parse(
                $appointmentDate->format('Y-m-d') . ' ' . $appointmentTime
            );
        } elseif ($appointmentDate) {
            $appointmentDateTime = Carbon::parse($appointmentDate);
        } else {
            $appointmentDateTime = Carbon::now();
        }
        
        $statusDisplay = match($appointment->status) {
            'scheduled' => 'Scheduled',
            'confirmed' => 'Confirmed',
            'rescheduled' => 'Rescheduled',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed',
            'no_show' => 'No Show',
            default => ucfirst($appointment->status ?? 'unknown')
        };

        return [
            'id' => $appointment->id,
            'date' => $appointmentDateTime->format('M d, Y'),
            'appointment_date' => $appointmentDateTime->format('M d, Y'),
            'appointment_time' => $appointmentTime ? $appointmentDateTime->format('g:i A') : 'Not set',
            'reason' => $appointment->reason ?? 'No reason specified',
            'status' => $appointment->status ?? 'unknown',
            'status_display' => $statusDisplay,
            'type' => ucfirst(str_replace('_', ' ', $appointment->type ?? 'standard')),
        ];
    } catch (\Exception $e) {
        Log::warning('Appointment formatting failed', [
            'appointment_id' => $appointment->id,
            'error' => $e->getMessage()
        ]);
        
        return [
            'id' => $appointment->id,
            'date' => 'Date error',
            'appointment_date' => 'Date error',
            'appointment_time' => 'Time error',
            'reason' => $appointment->reason ?? 'No reason specified',
            'status' => $appointment->status ?? 'unknown',
            'status_display' => 'Error',
            'type' => 'Unknown',
        ];
    }
}

    /**
     * Get student appointment history
     */
    public function getStudentAppointments(User $student): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isNurse()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Get appointments - both upcoming and past (limit to 10)
            $appointments = Appointment::where('user_id', $student->id)
                ->orderBy('appointment_date', 'desc')
                ->limit(10)
                ->get();

            if ($appointments->isEmpty()) {
                return response()->json([
                    'appointments' => [],
                ]);
            }

            $appointmentData = $appointments->map(function($appointment) {
                $appointmentDateTime = Carbon::parse(
                    $appointment->appointment_date->format('Y-m-d') . ' ' . 
                    $appointment->appointment_time
                );
                $isUpcoming = $appointmentDateTime->isFuture();
                
                // Status display mapping
                $statusDisplay = match($appointment->status) {
                    'scheduled' => 'Scheduled',
                    'confirmed' => 'Confirmed',
                    'rescheduled' => 'Rescheduled',
                    'cancelled' => 'Cancelled',
                    'completed' => 'Completed',
                    'no_show' => 'No Show',
                    default => ucfirst($appointment->status)
                };

                return [
                    'id' => $appointment->id,
                    'date' => $appointmentDateTime->format('M d, Y'),
                    'time' => $appointmentDateTime->format('g:i A'),
                    'reason' => $appointment->reason ?? 'No reason specified',
                    'status' => $appointment->status,
                    'status_display' => $statusDisplay,
                    'notes' => $appointment->notes,
                    'is_upcoming' => $isUpcoming,
                ];
            });

            return response()->json([
                'appointments' => $appointmentData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching student appointments: ' . $e->getMessage(), [
                'student_id' => $student->id,
            ]);
            return response()->json(['error' => 'Failed to fetch appointments'], 500);
        }
    }

    public function store(Request $request): RedirectResponse
{
    $user = Auth::user();

    if (!$user->isNurse()) {
        abort(403, 'Only nurses can create consultations.');
    }

    // LOG THE RAW INPUT FIRST
    Log::info('=== CONSULTATION STORE DEBUG ===');
    Log::info('All request data:', $request->all());
    Log::info('Vital signs from request:', $request->input('vital_signs'));
    
    $validated = $request->validate([
        'student_id' => ['required', 'integer', 'exists:users,id'],
        'chief_complaint' => 'required|string|max:500',
        'symptoms_description' => 'nullable|string|max:1000',
        'priority' => 'required|in:low,medium,high,critical',
        'pain_level' => 'nullable|integer|between:0,10',
        'type' => 'required|in:walk_in,appointment',
        'diagnosis' => 'required|string|max:1000',
        'treatment_provided' => 'required|string|max:1000',
        'medications_given' => 'nullable|string|max:1000',
        'procedures_performed' => 'nullable|string|max:1000',
        'home_care_instructions' => 'nullable|string|max:1000',
        'initial_notes' => 'nullable|string|max:1000',
        'vital_signs' => 'nullable|array',
        'vital_signs.temperature' => 'nullable|numeric|between:30,45',
        'vital_signs.blood_pressure_systolic' => 'nullable|integer|between:60,250',
        'vital_signs.blood_pressure_diastolic' => 'nullable|integer|between:40,150',
        'vital_signs.heart_rate' => 'nullable|integer|between:30,200',
        'vital_signs.oxygen_saturation' => 'nullable|numeric|between:70,100',
        'vital_signs.respiratory_rate' => 'nullable|integer|between:8,60',
        'vital_signs.weight' => 'nullable|numeric|between:20,200',
        'vital_signs.height' => 'nullable|numeric|between:100,250',
    ]);

    // LOG AFTER VALIDATION
    Log::info('Validated data:', $validated);
    Log::info('Vital signs after validation:', $validated['vital_signs'] ?? 'NO VITAL SIGNS');

    $student = User::findOrFail($validated['student_id']);

    if ($student->role !== 'student') {
        return redirect()->back()
            ->withInput()
            ->with('error', 'Selected user is not a student.');
    }

    // Check for existing active consultation today
    $existingConsultation = Consultation::where('student_id', $student->id)
        ->whereIn('status', [
            Consultation::STATUS_REGISTERED,
            Consultation::STATUS_IN_PROGRESS
        ])
        ->whereDate('consultation_date', today())
        ->first();

    if ($existingConsultation) {
        return redirect()->back()
            ->withInput()
            ->with('error', "Student already has an active consultation today.");
    }

    try {
        DB::beginTransaction();

        // Prepare consultation data with vital signs
        $consultationData = [
            'student_id' => $student->id,
            'nurse_id' => $user->id,
            'type' => $validated['type'],
            'status' => Consultation::STATUS_COMPLETED,
            'priority' => $validated['priority'],
            'chief_complaint' => $validated['chief_complaint'],
            'symptoms_description' => $validated['symptoms_description'],
            'pain_level' => $validated['pain_level'] ?? null,
            'initial_notes' => $validated['initial_notes'] ?? null,
            'diagnosis' => $validated['diagnosis'],
            'treatment_provided' => $validated['treatment_provided'],
            'medications_given' => $validated['medications_given'] ?? null,
            'procedures_performed' => $validated['procedures_performed'] ?? null,
            'home_care_instructions' => $validated['home_care_instructions'] ?? null,
            'consultation_date' => now(),
        ];

        // ADD VITAL SIGNS TO CONSULTATION DATA
        if (isset($validated['vital_signs']) && is_array($validated['vital_signs'])) {
            Log::info('Processing vital signs...');
            
            $vitalSigns = $validated['vital_signs'];
            
            $consultationData['temperature'] = $vitalSigns['temperature'] ?? null;
            $consultationData['blood_pressure_systolic'] = $vitalSigns['blood_pressure_systolic'] ?? null;
            $consultationData['blood_pressure_diastolic'] = $vitalSigns['blood_pressure_diastolic'] ?? null;
            $consultationData['heart_rate'] = $vitalSigns['heart_rate'] ?? null;
            $consultationData['oxygen_saturation'] = $vitalSigns['oxygen_saturation'] ?? null;
            $consultationData['respiratory_rate'] = $vitalSigns['respiratory_rate'] ?? null;
            $consultationData['weight'] = $vitalSigns['weight'] ?? null;
            $consultationData['height'] = $vitalSigns['height'] ?? null;
            
            Log::info('Vital signs to be saved:', [
                'temperature' => $consultationData['temperature'],
                'blood_pressure_systolic' => $consultationData['blood_pressure_systolic'],
                'blood_pressure_diastolic' => $consultationData['blood_pressure_diastolic'],
                'heart_rate' => $consultationData['heart_rate'],
                'oxygen_saturation' => $consultationData['oxygen_saturation'],
                'respiratory_rate' => $consultationData['respiratory_rate'],
                'weight' => $consultationData['weight'],
                'height' => $consultationData['height'],
            ]);
        } else {
            Log::warning('No vital signs data found in validated array');
        }

        Log::info('Final consultation data before save:', $consultationData);

        $consultation = Consultation::create($consultationData);

        Log::info('Consultation created with ID: ' . $consultation->id);
        Log::info('Saved consultation vital signs:', [
            'temperature' => $consultation->temperature,
            'blood_pressure_systolic' => $consultation->blood_pressure_systolic,
            'blood_pressure_diastolic' => $consultation->blood_pressure_diastolic,
            'heart_rate' => $consultation->heart_rate,
            'oxygen_saturation' => $consultation->oxygen_saturation,
            'respiratory_rate' => $consultation->respiratory_rate,
            'weight' => $consultation->weight,
            'height' => $consultation->height,
        ]);

        // ALSO update student medical record with new vital signs if provided
        if (isset($validated['vital_signs']) && is_array($validated['vital_signs'])) {
            $this->updateStudentMedicalRecord($student, $validated['vital_signs']);
        }

        DB::commit();

        // Send notification
        try {
            $student->notify(new ConsultationNotification($consultation, 'completed', [
                'consultation_type' => ucfirst(str_replace('_', ' ', $validated['type'])),
                'nurse_name' => $user->full_name,
            ]));
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
        }

        Log::info('Consultation created and completed successfully', [
            'consultation_id' => $consultation->id,
            'student_id' => $student->id,
            'nurse_id' => $user->id,
        ]);

        return redirect()->route('nurse.consultations.show', $consultation)
            ->with('success', 'Consultation registered and completed successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error creating consultation: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to create consultation: ' . $e->getMessage());
    }
}
    /**
     * Create walk-in consultation
     */
    public function createWalkIn(): View
    {
        $user = Auth::user();

        if (!$user->isNurse()) {
            abort(403, 'Only nurses can create walk-in consultations.');
        }

        return view('nurse.consultations.create-walk-in', [
            'consultationPriorities' => [
                'low' => 'Low',
                'medium' => 'Medium', 
                'high' => 'High',
                'critical' => 'Critical'
            ]
        ]);
    }

    /**
     * Store walk-in consultation
     */
    public function storeWalkIn(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isNurse()) {
            abort(403, 'Only nurses can create walk-in consultations.');
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'chief_complaint' => 'required|string|max:500',
            'symptoms_description' => 'nullable|string|max:1000',
            'priority' => 'required|in:low,medium,high,critical',
            'pain_level' => 'nullable|integer|between:0,10',
            'initial_notes' => 'nullable|string|max:1000',
        ]);

        $student = User::findOrFail($validated['student_id']);

        // Check for existing active consultation today
        $existingConsultation = Consultation::where('student_id', $student->id)
            ->whereIn('status', [
                Consultation::STATUS_REGISTERED,
                Consultation::STATUS_IN_PROGRESS
            ])
            ->whereDate('consultation_date', today())
            ->first();

        if ($existingConsultation) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Student already has an active consultation today.");
        }

        try {
            DB::beginTransaction();

            $consultation = Consultation::create([
                'student_id' => $student->id,
                'nurse_id' => $user->id,
                'type' => Consultation::TYPE_WALK_IN,
                'status' => Consultation::STATUS_REGISTERED,
                'priority' => $validated['priority'],
                'chief_complaint' => $validated['chief_complaint'],
                'symptoms_description' => $validated['symptoms_description'],
                'pain_level' => $validated['pain_level'] ?? null,
                'initial_notes' => $validated['initial_notes'] ?? null,
                'consultation_date' => now(),
            ]);

            DB::commit();

            // Send notification
            try {
                $student->notify(new ConsultationNotification($consultation, 'registered', [
                    'consultation_type' => 'Walk-in Consultation',
                    'nurse_name' => $user->full_name,
                ]));
            } catch (\Exception $e) {
                Log::error('Failed to send notification: ' . $e->getMessage());
            }

            Log::info('Walk-in consultation created', [
                'consultation_id' => $consultation->id,
                'student_id' => $student->id,
            ]);

            return redirect()->route('nurse.consultations.show', $consultation)
                ->with('success', 'Walk-in consultation registered successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating walk-in consultation: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating consultation: ' . $e->getMessage());
        }
    }

   /**
 * Show consultation details
 */
public function show(Consultation $consultation): View
{
    $user = Auth::user();

    if (!$user->isNurse()) {
        abort(403, 'Only nurses can view consultations.');
    }

    // Load all necessary relationships
    $consultation->load(['student.medicalRecord', 'nurse']);

    // Get student for easier access
    $student = $consultation->student;

    // Get recent consultations for this student
    $recentConsultations = Consultation::where('student_id', $consultation->student_id)
        ->where('id', '!=', $consultation->id)
        ->where('status', Consultation::STATUS_COMPLETED)
        ->with('nurse')
        ->orderBy('consultation_date', 'desc')
        ->limit(5)
        ->get();

    // Get recent symptom logs
    $recentSymptoms = SymptomLog::where('user_id', $student->id)
        ->orderBy('logged_at', 'desc')
        ->limit(10)
        ->get();

    // Get appointment history
    $appointments = Appointment::where('user_id', $student->id)
        ->orderBy('appointment_date', 'desc')
        ->limit(10)
        ->get();

    // Get medical record data
    $medicalRecord = $student->medicalRecord;
    
    // Calculate BMI if height and weight are available - USE MEDICAL RECORD DATA
    $bmiData = ['bmi' => null, 'category' => null, 'status_class' => 'secondary'];
    if ($medicalRecord && $medicalRecord->height && $medicalRecord->weight) {
        $heightInMeters = $medicalRecord->height / 100;
        $bmi = $medicalRecord->weight / ($heightInMeters * $heightInMeters);
        $bmiData['bmi'] = round($bmi, 1);
        
        // Determine BMI category and status class
        if ($bmi < 18.5) {
            $bmiData['category'] = 'Underweight';
            $bmiData['status_class'] = 'warning';
        } elseif ($bmi >= 18.5 && $bmi < 25) {
            $bmiData['category'] = 'Normal';
            $bmiData['status_class'] = 'success';
        } elseif ($bmi >= 25 && $bmi < 30) {
            $bmiData['category'] = 'Overweight';
            $bmiData['status_class'] = 'warning';
        } else {
            $bmiData['category'] = 'Obese';
            $bmiData['status_class'] = 'danger';
        }
    }

    // Prepare emergency contacts
    $emergencyContacts = [];
    if ($medicalRecord) {
        if ($medicalRecord->emergency_contact_name_1) {
            $emergencyContacts[] = [
                'name' => $medicalRecord->emergency_contact_name_1,
                'phone' => $medicalRecord->emergency_contact_number_1,
                'relationship' => $medicalRecord->emergency_contact_relationship_1,
            ];
        }
        if ($medicalRecord->emergency_contact_name_2) {
            $emergencyContacts[] = [
                'name' => $medicalRecord->emergency_contact_name_2,
                'phone' => $medicalRecord->emergency_contact_number_2,
                'relationship' => $medicalRecord->emergency_contact_relationship_2,
            ];
        }
    }

    // Prepare vital signs data - Use medical record as fallback
    $vitalSigns = [
        'temperature' => $consultation->temperature,
        'blood_pressure_systolic' => $consultation->blood_pressure_systolic,
        'blood_pressure_diastolic' => $consultation->blood_pressure_diastolic,
        'heart_rate' => $consultation->heart_rate,
        'oxygen_saturation' => $consultation->oxygen_saturation,
        'respiratory_rate' => $consultation->respiratory_rate,
        'height' => $consultation->height ?? $medicalRecord->height ?? null,
        'weight' => $consultation->weight ?? $medicalRecord->weight ?? null,
    ];

    return view('nurse.consultations.show', compact(
        'consultation', 
        'recentConsultations', 
        'recentSymptoms',
        'appointments',
        'medicalRecord',
        'bmiData',
        'emergencyContacts',
        'student',
        'vitalSigns'
    ));
}
    /**
     * Get detailed student data for consultation creation
     */
    public function getDetailedStudentData(User $student): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isNurse()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $medicalRecord = $student->medicalRecord;
            
            // Calculate BMI
            $bmiData = $this->calculateBMI($medicalRecord);

            // Get recent symptoms
            $recentSymptoms = SymptomLog::where('user_id', $student->id)
                ->orderBy('logged_at', 'desc')
                ->limit(10)
                ->get();

            // Get recent consultations
            $recentConsultations = Consultation::where('student_id', $student->id)
                ->where('status', Consultation::STATUS_COMPLETED)
                ->orderBy('consultation_date', 'desc')
                ->limit(10)
                ->get();

            // Get appointments
            $appointments = Appointment::where('user_id', $student->id)
                ->orderBy('appointment_date', 'desc')
                ->limit(10)
                ->get();

            // Calculate age properly
            $age = null;
            if ($student->date_of_birth) {
                $birthDate = $student->date_of_birth;
                if ($birthDate instanceof \Carbon\Carbon) {
                    $age = $birthDate->age;
                } else {
                    $age = \Carbon\Carbon::parse($birthDate)->age;
                }
            }

            $data = [
                'student' => [
                    'id' => $student->id,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'student_id' => $student->student_id,
                    'full_name' => $student->full_name,
                    'email' => $student->email,
                    'phone' => $student->phone,
                    'gender' => $student->gender,
                    'age' => $age,
                    'birth_date' => $student->date_of_birth ? $student->date_of_birth->format('M j, Y') : null,
                    'date_of_birth' => $student->date_of_birth ? $student->date_of_birth->format('M j, Y') : null,
                    'department' => $student->department ?? 'Not specified',
                    'course' => $student->course ?? 'Not specified',
                    'year_level' => $student->year_level ?? 'Not specified',
                    'section' => $student->section ?? 'Not specified',
                    'address' => $student->address ?? 'Not provided',
                ],
                'medical_record' => $medicalRecord ? [
                    // Basic Info
                    'blood_type' => $medicalRecord->blood_type,
                    'height' => $medicalRecord->height,
                    'weight' => $medicalRecord->weight,
                    'bmi' => $bmiData['bmi'],
                    'bmi_category' => $bmiData['category'],
                    
                    // Medical History
                    'allergies' => $medicalRecord->allergies,
                    'chronic_conditions' => $medicalRecord->chronic_conditions,
                    'current_medications' => $medicalRecord->maintenance_drugs_specify ?? $medicalRecord->current_medications,
                    'past_illnesses' => $medicalRecord->past_illnesses,
                    
                    // Surgical & Hospitalization
                    'has_undergone_surgery' => $medicalRecord->has_undergone_surgery ?? false,
                    'surgery_details' => $medicalRecord->surgery_details,
                    'has_been_hospitalized_6_months' => $medicalRecord->has_been_hospitalized_6_months ?? false,
                    'hospitalization_details_6_months' => $medicalRecord->hospitalization_details_6_months ?? $medicalRecord->hospitalization_details,
                    'has_been_pregnant' => $medicalRecord->has_been_pregnant ?? false,
                    
                    // PWD Information
                    'is_pwd' => $medicalRecord->is_pwd ?? false,
                    'pwd_id' => $medicalRecord->pwd_id,
                    'pwd_reason' => $medicalRecord->pwd_reason,
                    'pwd_disability_details' => $medicalRecord->pwd_disability_details ?? $medicalRecord->pwd_reason,
                    
                    // COVID-19 Vaccination
                    'is_fully_vaccinated' => $medicalRecord->is_fully_vaccinated ?? false,
                    'vaccine_type' => $medicalRecord->vaccine_type ?? $medicalRecord->vaccine_name,
                    'vaccine_name' => $medicalRecord->vaccine_name ?? $medicalRecord->vaccine_type,
                    'other_vaccine_type' => $medicalRecord->other_vaccine_type,
                    'vaccine_date' => $medicalRecord->vaccine_date ? $medicalRecord->vaccine_date->format('M j, Y') : null,
                    'number_of_doses' => $medicalRecord->number_of_doses,
                    'has_received_booster' => $medicalRecord->has_received_booster ?? false,
                    'number_of_boosters' => $medicalRecord->number_of_boosters,
                    'booster_type' => $medicalRecord->booster_type,
                    'booster_received' => $medicalRecord->booster_received ?? false,
                    
                    // Additional Notes
                    'notes_health_problems' => $medicalRecord->notes_health_problems ?? $medicalRecord->other_health_problems,
                    
                    // Emergency Contacts
                    'emergency_contact_1' => [
                        'name' => $medicalRecord->emergency_contact_name_1,
                        'phone' => $medicalRecord->emergency_contact_number_1,
                        'relationship' => $medicalRecord->emergency_contact_relationship_1,
                    ],
                    'emergency_contact_2' => [
                        'name' => $medicalRecord->emergency_contact_name_2,
                        'phone' => $medicalRecord->emergency_contact_number_2,
                        'relationship' => $medicalRecord->emergency_contact_relationship_2,
                    ],
                    
                    // Timestamps
                    'last_updated' => $medicalRecord->updated_at ? $medicalRecord->updated_at->format('M j, Y g:i A') : null,
                ] : null,
                'recent_symptoms' => $recentSymptoms->map(function($symptom) {
                    return [
                        'symptoms' => $symptom->symptoms,
                        'severity' => $symptom->severity_rating,
                        'logged_at' => $symptom->logged_at->format('M d, Y H:i'),
                        'notes' => $symptom->notes,
                    ];
                }),
                'recent_consultations' => $recentConsultations->map(function($consultation) {
                    return [
                        'id' => $consultation->id,
                        'consultation_date' => $consultation->consultation_date->format('M d, Y'),
                        'chief_complaint' => $consultation->chief_complaint,
                        'diagnosis' => $consultation->diagnosis,
                        'treatment' => $consultation->treatment_provided,
                        'priority' => $consultation->priority,
                    ];
                }),
                'recent_appointments' => $appointments->map(function($appointment) {
                    return [
                        'id' => $appointment->id,
                        'appointment_date' => $appointment->appointment_date->format('M d, Y'),
                        'appointment_time' => \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A'),
                        'reason' => $appointment->reason ?? 'No reason specified',
                        'status' => $appointment->status,
                        'status_display' => ucfirst(str_replace('_', ' ', $appointment->status)),
                        'type' => ucfirst(str_replace('_', ' ', $appointment->type ?? 'standard')),
                    ];
                }),
            ];

            Log::info('Detailed student data prepared', [
                'student_id' => $student->id,
                'has_medical_record' => $medicalRecord !== null,
                'age' => $age,
                'birth_date' => $student->date_of_birth ? $student->date_of_birth->format('M j, Y') : null,
            ]);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error fetching detailed student data: ' . $e->getMessage(), [
                'student_id' => $student->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to fetch student data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show edit form for consultation
     */
    public function edit(Consultation $consultation): View
    {
        $user = Auth::user();

        if (!$user->isNurse()) {
            abort(403, 'Only nurses can edit consultations.');
        }

        // Only allow editing own consultations
        if ($consultation->nurse_id != $user->id) {
            abort(403, 'You can only edit your own consultations.');
        }

        // Don't allow editing cancelled consultations
        if ($consultation->status === Consultation::STATUS_CANCELLED) {
            return redirect()->route('nurse.consultations.show', $consultation)
                ->with('error', 'Cancelled consultations cannot be edited.');
        }

        $consultation->load(['student']);

        return view('nurse.consultations.edit', [
            'consultation' => $consultation,
            'consultationPriorities' => [
                'low' => 'Low',
                'medium' => 'Medium', 
                'high' => 'High',
                'critical' => 'Critical'
            ]
        ]);
    }

    public function update(Request $request, Consultation $consultation): RedirectResponse
{
    $user = Auth::user();

    if (!$user->isNurse()) {
        abort(403, 'Only nurses can update consultations.');
    }

    if ($consultation->nurse_id != $user->id) {
        abort(403, 'You can only edit your own consultations.');
    }

    $validated = $request->validate([
        'chief_complaint' => 'required|string|max:500',
        'symptoms_description' => 'nullable|string|max:1000',
        'priority' => 'required|in:low,medium,high,critical',
        'pain_level' => 'nullable|integer|between:0,10',
        'diagnosis' => 'required|string|max:500',
        'treatment_provided' => 'nullable|string|max:1000',
        'medications_given' => 'nullable|string|max:1000',
        'procedures_performed' => 'nullable|string|max:1000',
        'home_care_instructions' => 'nullable|string|max:1000',
        'initial_notes' => 'nullable|string|max:1000',
        'vital_notes' => 'nullable|string|max:1000',
        // Vital signs validation
        'temperature' => 'nullable|numeric|between:30,45',
        'blood_pressure_systolic' => 'nullable|integer|between:60,250',
        'blood_pressure_diastolic' => 'nullable|integer|between:40,150',
        'heart_rate' => 'nullable|integer|between:30,200',
        'oxygen_saturation' => 'nullable|numeric|between:70,100',
        'respiratory_rate' => 'nullable|integer|between:8,60',
        'weight' => 'nullable|numeric|between:20,200',
        'height' => 'nullable|numeric|between:100,250',
    ]);

    try {
        DB::beginTransaction();

        $consultation->update($validated);

        DB::commit();

        // Send notification
        try {
            $consultation->student->notify(new ConsultationNotification($consultation, 'updated', [
                'nurse_name' => $user->full_name,
            ]));
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
        }

        Log::info('Consultation updated', [
            'consultation_id' => $consultation->id,
        ]);

        return redirect()->route('nurse.consultations.show', $consultation)
            ->with('success', 'Consultation updated successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error updating consultation: ' . $e->getMessage());
        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to update consultation: ' . $e->getMessage());
    }
}

    /**
     * Start a consultation
     */
    public function start(Consultation $consultation): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isNurse()) {
            abort(403, 'Only nurses can start consultations.');
        }

        try {
            DB::beginTransaction();

            $consultation->update([
                'status' => Consultation::STATUS_IN_PROGRESS,
                'started_at' => now()
            ]);

            // Send notification to student
            try {
                $consultation->student->notify(new ConsultationNotification($consultation, 'started'));
            } catch (\Exception $e) {
                Log::error('Failed to send notification: ' . $e->getMessage());
            }

            Log::info('Consultation started', [
                'consultation_id' => $consultation->id,
            ]);

            DB::commit();

            return redirect()->route('nurse.consultations.show', $consultation)
                ->with('success', 'Consultation started successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting consultation: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error starting consultation: ' . $e->getMessage());
        }
    }

    /**
     * Show complete consultation form
     */
    public function completeForm(Consultation $consultation): View
    {
        $user = Auth::user();

        if (!$user->isNurse()) {
            abort(403, 'Only nurses can complete consultations.');
        }

        $consultation->load(['student']);

        return view('nurse.consultations.complete', compact('consultation'));
    }

    /**
     * Complete a consultation
     */
    public function complete(Request $request, Consultation $consultation): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isNurse()) {
            abort(403, 'Only nurses can complete consultations.');
        }

        $validated = $request->validate([
            'diagnosis' => 'required|string|max:1000',
            'treatment_provided' => 'required|string|max:1000',
            'medications_given' => 'nullable|string|max:1000',
            'procedures_performed' => 'nullable|string|max:1000',
            'home_care_instructions' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $consultation->update([
                'status' => Consultation::STATUS_COMPLETED,
                'diagnosis' => $validated['diagnosis'],
                'treatment_provided' => $validated['treatment_provided'],
                'medications_given' => $validated['medications_given'],
                'procedures_performed' => $validated['procedures_performed'],
                'home_care_instructions' => $validated['home_care_instructions'],
                'completed_at' => now()
            ]);

            // Send notification to student
            try {
                $consultation->student->notify(new ConsultationNotification($consultation, 'completed'));
            } catch (\Exception $e) {
                Log::error('Failed to send notification: ' . $e->getMessage());
            }

            Log::info('Consultation completed', [
                'consultation_id' => $consultation->id,
            ]);

            DB::commit();

            return redirect()->route('nurse.consultations.show', $consultation)
                ->with('success', 'Consultation completed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completing consultation: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error completing consultation: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a consultation
     */
    public function cancel(Request $request, Consultation $consultation): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isNurse()) {
            abort(403, 'Only nurses can cancel consultations.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $consultation->update([
                'status' => Consultation::STATUS_CANCELLED,
                'cancellation_reason' => $validated['cancellation_reason'],
                'cancelled_at' => now()
            ]);

            // Send notification to student
            try {
                $consultation->student->notify(new ConsultationNotification($consultation, 'cancelled'));
            } catch (\Exception $e) {
                Log::error('Failed to send notification: ' . $e->getMessage());
            }

            Log::info('Consultation cancelled', [
                'consultation_id' => $consultation->id,
            ]);

            DB::commit();

            return redirect()->route('nurse.consultations.show', $consultation)
                ->with('success', 'Consultation cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling consultation: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error cancelling consultation: ' . $e->getMessage());
        }
    }

    /**
     * Get comprehensive medical record summary for display
     */
    private function getMedicalRecordSummary(User $student): array
    {
        $medicalRecord = $student->medicalRecord;
        
        if (!$medicalRecord) {
            return [
                'blood_type' => 'Not recorded',
                'height' => 'Not recorded',
                'weight' => 'Not recorded',
                'bmi' => 'Not calculated',
                'blood_pressure' => 'Not recorded',
                'chronic_conditions' => 'None recorded',
                'allergies' => 'None recorded',
                'medications' => 'None recorded',
                'past_hospitalizations' => 'None recorded',
                'vaccination_status' => 'Not recorded',
                'booster_status' => 'Not recorded',
                'surgery_history' => 'None recorded',
                'pwd_status' => 'Not recorded',
                'last_updated' => 'No medical record'
            ];
        }

        // Calculate BMI
        $bmiData = $this->calculateBMI($medicalRecord);
        
        // Format blood pressure
        $bloodPressure = 'Not recorded';
        if ($medicalRecord->blood_pressure_systolic && $medicalRecord->blood_pressure_diastolic) {
            $bloodPressure = $medicalRecord->blood_pressure_systolic . '/' . $medicalRecord->blood_pressure_diastolic . ' mmHg';
        }

        // Format chronic conditions
        $chronicConditions = $medicalRecord->chronic_conditions ?: 'None recorded';
        
        // Format allergies
        $allergies = $medicalRecord->allergies ?: 'None recorded';
        
        // Format medications
        $medications = $medicalRecord->maintenance_drugs_specify ?: 'None recorded';
        
        // Format past hospitalizations
        $pastHospitalizations = $medicalRecord->past_illnesses ?: 'None recorded';
        
        // Format surgery history
        $surgeryHistory = 'None recorded';
        if ($medicalRecord->has_undergone_surgery) {
            $surgeryHistory = $medicalRecord->surgery_details ?: 'Yes (details not specified)';
        }
        
        // Format PWD status
        $pwdStatus = $medicalRecord->is_pwd ? 'Yes' : 'No';
        if ($medicalRecord->is_pwd && $medicalRecord->pwd_id) {
            $pwdStatus .= ' (ID: ' . $medicalRecord->pwd_id . ')';
        }
        
        // Format vaccination status with booster info
        $vaccinationStatus = 'Not recorded';
        $boosterStatus = 'Not recorded';
        
        if ($medicalRecord->is_fully_vaccinated) {
            $vaccinationStatus = 'Fully Vaccinated';
            if ($medicalRecord->vaccine_type || $medicalRecord->vaccine_name) {
                $vaccineType = $medicalRecord->vaccine_type ?: $medicalRecord->vaccine_name;
                $vaccinationStatus .= ' (' . $vaccineType . ')';
            }
            if ($medicalRecord->number_of_doses) {
                $vaccinationStatus .= ' - ' . $medicalRecord->number_of_doses . ' doses';
            }
            
            // Booster status
            if ($medicalRecord->has_received_booster || $medicalRecord->booster_received) {
                $boosterStatus = 'Yes';
                if ($medicalRecord->booster_type) {
                    $boosterStatus .= ' (' . $medicalRecord->booster_type . ')';
                }
                if ($medicalRecord->number_of_boosters) {
                    $boosterStatus .= ' - ' . $medicalRecord->number_of_boosters . ' boosters';
                }
            } else {
                $boosterStatus = 'No';
            }
        } elseif ($medicalRecord->vaccine_type || $medicalRecord->vaccine_name) {
            $vaccinationStatus = 'Partially Vaccinated';
            $boosterStatus = 'Not applicable';
        }

        return [
            'blood_type' => $medicalRecord->blood_type ?: 'Not recorded',
            'height' => $medicalRecord->height ? $medicalRecord->height . ' cm' : 'Not recorded',
            'weight' => $medicalRecord->weight ? $medicalRecord->weight . ' kg' : 'Not recorded',
            'bmi' => $bmiData['bmi'] ? $bmiData['bmi'] . ' (' . $bmiData['category'] . ')' : 'Not calculated',
            'blood_pressure' => $bloodPressure,
            'chronic_conditions' => $chronicConditions,
            'allergies' => $allergies,
            'medications' => $medications,
            'past_hospitalizations' => $pastHospitalizations,
            'surgery_history' => $surgeryHistory,
            'pwd_status' => $pwdStatus,
            'vaccination_status' => $vaccinationStatus,
            'booster_status' => $boosterStatus,
            'last_updated' => $medicalRecord->updated_at ? $medicalRecord->updated_at->format('M j, Y g:i A') : 'Not available'
        ];
    }

    /**
     * ============================================
     * HELPER METHODS
     * ============================================
     */

    /**
     * Get consultation statistics
     */
    private function getConsultationStatistics(): array
    {
        $today = Carbon::today();

        return [
            'total_today' => Consultation::whereDate('consultation_date', $today)->count(),
            'registered_today' => Consultation::whereDate('consultation_date', $today)
                ->where('status', Consultation::STATUS_REGISTERED)
                ->count(),
            'in_progress_today' => Consultation::whereDate('consultation_date', $today)
                ->where('status', Consultation::STATUS_IN_PROGRESS)
                ->count(),
            'completed_today' => Consultation::whereDate('consultation_date', $today)
                ->where('status', Consultation::STATUS_COMPLETED)
                ->count(),
            'critical_today' => Consultation::whereDate('consultation_date', $today)
                ->where('priority', Consultation::PRIORITY_CRITICAL)
                ->count(),
            'high_priority_today' => Consultation::whereDate('consultation_date', $today)
                ->where('priority', Consultation::PRIORITY_HIGH)
                ->count(),
            'walk_in_today' => Consultation::whereDate('consultation_date', $today)
                ->where('type', Consultation::TYPE_WALK_IN)
                ->count(),
            'appointment_today' => Consultation::whereDate('consultation_date', $today)
                ->where('type', Consultation::TYPE_APPOINTMENT)
                ->count(),
        ];
    }

    /**
     * Calculate BMI from medical record
     */
    private function calculateBMI($medicalRecord): array
    {
        $bmiData = ['bmi' => null, 'category' => null];
        
        if (!$medicalRecord || !$medicalRecord->height || !$medicalRecord->weight) {
            return $bmiData;
        }

        $heightInMeters = $medicalRecord->height / 100;
        $bmi = $medicalRecord->weight / ($heightInMeters * $heightInMeters);
        $bmiData['bmi'] = round($bmi, 1);
        
        if ($bmi < 18.5) {
            $bmiData['category'] = 'Underweight';
        } elseif ($bmi >= 18.5 && $bmi < 25) {
            $bmiData['category'] = 'Normal';
        } elseif ($bmi >= 25 && $bmi < 30) {
            $bmiData['category'] = 'Overweight';
        } else {
            $bmiData['category'] = 'Obese';
        }
        
        return $bmiData;
    }

    /**
     * Update student medical record with new vital signs
     */
    private function updateStudentMedicalRecord(User $student, array $vitalSigns): void
    {
        $medicalRecord = $student->medicalRecord;

        if (!$medicalRecord) {
            return;
        }

        $updateData = [];

        if (isset($vitalSigns['weight']) && $vitalSigns['weight']) {
            $updateData['weight'] = $vitalSigns['weight'];
        }

        if (isset($vitalSigns['height']) && $vitalSigns['height']) {
            $updateData['height'] = $vitalSigns['height'];
        }

        if (isset($vitalSigns['blood_pressure_systolic']) && $vitalSigns['blood_pressure_systolic']) {
            $updateData['blood_pressure_systolic'] = $vitalSigns['blood_pressure_systolic'];
        }

        if (isset($vitalSigns['blood_pressure_diastolic']) && $vitalSigns['blood_pressure_diastolic']) {
            $updateData['blood_pressure_diastolic'] = $vitalSigns['blood_pressure_diastolic'];
        }

        if (!empty($updateData)) {
            $medicalRecord->update($updateData);
            Log::info('Medical record updated with new vital signs', [
                'student_id' => $student->id,
                'updated_fields' => array_keys($updateData)
            ]);
        }
    }

    /**
     * ============================================
     * DEAN CONSULTATION METHODS
     * ============================================
     */

    /**
     * Display consultations for dean
     */
    public function deanIndex(Request $request): View
    {
        $user = Auth::user();

        if (!$user->isDean()) {
            abort(403, 'Only deans can view this page.');
        }

        $query = Consultation::with(['student', 'nurse']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date')) {
            $query->whereDate('consultation_date', $request->date);
        }

        $consultations = $query->orderBy('consultation_date', 'desc')
                              ->paginate(15)
                              ->withQueryString();

        $stats = $this->getConsultationStatistics();

        return view('dean.consultations.index', compact('consultations', 'stats'));
    }

    /**
     * Show consultation details for dean
     */
    public function deanShow(Consultation $consultation): View
    {
        $user = Auth::user();

        if (!$user->isDean()) {
            abort(403, 'Only deans can view this page.');
        }

        $consultation->load(['student', 'nurse']);

        return view('dean.consultations.show', compact('consultation'));
    }

    /**
     * Get consultation details for API
     */
    public function getDetails(Consultation $consultation): JsonResponse
    {
        $user = Auth::user();
        
        // Authorization check
        if ($user->isNurse()) {
            if ($consultation->nurse_id && $consultation->nurse_id != $user->id) {
                return response()->json(['error' => 'Unauthorized access.'], 403);
            }
        } elseif ($user->isStudent()) {
            if ($consultation->student_id != $user->id) {
                return response()->json(['error' => 'Unauthorized access.'], 403);
            }
        }

        $consultation->load(['student', 'nurse']);

        return response()->json([
            'success' => true,
            'consultation' => [
                'id' => $consultation->id,
                'student' => [
                    'full_name' => $consultation->student->full_name ?? 'Unknown',
                    'student_id' => $consultation->student->student_id ?? 'N/A',
                ],
                'formatted_consultation_date' => $consultation->consultation_date->format('M d, Y g:i A'),
                'chief_complaint' => $consultation->chief_complaint,
                'diagnosis' => $consultation->diagnosis,
                'status' => $consultation->status,
                'priority' => $consultation->priority,
                'type' => $consultation->type,
                'nurse' => $consultation->nurse ? [
                    'full_name' => $consultation->nurse->full_name,
                ] : null,
            ]
        ]);
    }
}