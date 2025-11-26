<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use App\Models\SymptomLog;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class StudentReportController extends Controller
{
    /**
     * Get dashboard data for index page
     */
    public function index()
    {
        try {
            $students = User::where('role', 'student')
                ->with(['medicalRecord', 'appointments', 'consultations', 'symptomLogs'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate(20);

            // Calculate dashboard statistics
            $totalStudents = User::where('role', 'student')->count();
            $activeToday = $this->getActiveTodayCount();
            $highRiskStudents = $this->getHighRiskStudentsCount();
            $pendingReviews = $this->getPendingReviewsCount();
            $recentReports = $this->getRecentReports();

            return view('nurse.student-reports.index', compact(
                'students',
                'totalStudents',
                'activeToday',
                'highRiskStudents',
                'pendingReviews',
                'recentReports'
            ));
        } catch (\Exception $e) {
            Log::error('Student reports index error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load student reports.');
        }
    }

    /**
     * Get count of students active today
     */
    private function getActiveTodayCount()
    {
        $today = Carbon::today();
        
        return User::where('role', 'student')
            ->whereHas('appointments', function($q) use ($today) {
                $q->whereDate('appointment_date', $today);
            })
            ->orWhereHas('consultations', function($q) use ($today) {
                $q->whereDate('consultation_date', $today);
            })
            ->orWhereHas('symptomLogs', function($q) use ($today) {
                $q->whereDate('logged_at', $today);
            })
            ->distinct()
            ->count();
    }

    /**
     * Get count of high risk students
     */
    private function getHighRiskStudentsCount()
    {
        $students = User::where('role', 'student')
            ->with(['medicalRecord', 'appointments', 'consultations', 'symptomLogs'])
            ->get();

        return $students->filter(function($student) {
            return $this->calculateStudentHealthRiskLevel($student) === 'High';
        })->count();
    }

    /**
     * Get count of pending reviews
     */
    private function getPendingReviewsCount()
    {
        // This could be consultations that need follow-up, pending appointments, etc.
        // For now, we'll count appointments with 'pending' status
        return Appointment::where('status', 'pending')->count();
    }

    /**
     * Get recent reports data for dashboard
     */
    private function getRecentReports()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        
        $students = User::where('role', 'student')
            ->with(['medicalRecord', 'appointments', 'consultations', 'symptomLogs'])
            ->whereHas('appointments', function($q) use ($thirtyDaysAgo) {
                $q->where('appointment_date', '>=', $thirtyDaysAgo);
            })
            ->orWhereHas('consultations', function($q) use ($thirtyDaysAgo) {
                $q->where('consultation_date', '>=', $thirtyDaysAgo);
            })
            ->orWhereHas('symptomLogs', function($q) use ($thirtyDaysAgo) {
                $q->where('logged_at', '>=', $thirtyDaysAgo);
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(10)
            ->get()
            ->map(function($student) {
                $lastActivity = $this->getLastActivityDate($student);
                $healthRisk = $this->calculateStudentHealthRiskLevel($student);
                
                return [
                    'id' => $student->id,
                    'name' => $student->full_name,
                    'student_id' => $student->student_id,
                    'email' => $student->email,
                    'course' => $student->course,
                    'year_level' => $student->year_level,
                    'health_risk' => $healthRisk,
                    'appointments_count' => $student->appointments->count(),
                    'consultations_count' => $student->consultations->count(),
                    'symptoms_count' => $student->symptomLogs->count(),
                    'last_activity' => $lastActivity ? $lastActivity->diffForHumans() : 'No activity',
                ];
            });

        return $students;
    }

    /**
     * Search for students
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('query', '');
            
            $students = User::where('role', 'student')
                ->when($query, function ($q) use ($query) {
                    $q->where(function ($subQ) use ($query) {
                        $subQ->where('student_id', 'like', "%{$query}%")
                            ->orWhere('first_name', 'like', "%{$query}%")
                            ->orWhere('last_name', 'like', "%{$query}%")
                            ->orWhere('course', 'like', "%{$query}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
                    });
                })
                ->with(['medicalRecord'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate(20);

            return view('nurse.student-reports.index', compact('students', 'query'));
        } catch (\Exception $e) {
            Log::error('Student reports search error: ' . $e->getMessage());
            return back()->with('error', 'Search failed.');
        }
    }

    /**
     * Search students for AJAX requests
     */
    public function searchAjax(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter at least 2 characters to search'
                ]);
            }

            $students = User::where('role', 'student')
                ->where(function($q) use ($query) {
                    $q->where('student_id', 'like', "%{$query}%")
                      ->orWhere('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%")
                      ->orWhere('course', 'like', "%{$query}%")
                      ->orWhere('year_level', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
                })
                ->with(['medicalRecord', 'appointments', 'consultations', 'symptomLogs'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->limit(20)
                ->get()
                ->map(function($student) {
                    $lastActivity = $this->getLastActivityDate($student);
                    $healthRisk = $this->calculateStudentHealthRiskLevel($student);
                    $completion = $this->calculateMedicalRecordCompletion($student);
                    
                    return [
                        'id' => $student->id,
                        'first_name' => $student->first_name,
                        'last_name' => $student->last_name,
                        'full_name' => $student->full_name,
                        'student_id' => $student->student_id,
                        'course' => $student->course,
                        'year_level' => $student->year_level,
                        'email' => $student->email,
                        'health_risk_level' => $healthRisk,
                        'medical_record_completion' => $completion,
                        'appointments_count' => $student->appointments->count(),
                        'consultations_count' => $student->consultations->count(),
                        'symptoms_count' => $student->symptomLogs->count(),
                        'last_activity' => $lastActivity ? $lastActivity->diffForHumans() : 'No activity',
                        'has_medical_record' => !is_null($student->medicalRecord),
                        'has_allergies' => $student->medicalRecord && $student->medicalRecord->allergies,
                        'has_chronic_conditions' => $student->medicalRecord && $student->medicalRecord->chronic_conditions,
                    ];
                });

            return response()->json([
                'success' => true,
                'students' => $students,
                'count' => $students->count(),
                'query' => $query
            ]);

        } catch (\Exception $e) {
            Log::error('Student search error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Search failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Calculate medical record completion percentage
     */
    private function calculateMedicalRecordCompletion($student)
    {
        if (!$student->medicalRecord) {
            return 0;
        }

        $fields = [
            'blood_type', 'height', 'weight', 'allergies', 'chronic_conditions',
            'current_medications', 'family_medical_history', 'immunization_history',
            'emergency_contact_name_1', 'emergency_contact_number_1'
        ];

        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($student->medicalRecord->$field)) {
                $completed++;
            }
        }

        return round(($completed / count($fields)) * 100);
    }

    /**
     * Get the last activity date for a student
     */
    private function getLastActivityDate($student)
    {
        $dates = [];
        
        // Get latest appointment
        if ($student->appointments->isNotEmpty()) {
            $latestAppointment = $student->appointments->max(function($appointment) {
                return $appointment->appointment_date ? Carbon::parse($appointment->appointment_date) : null;
            });
            if ($latestAppointment) {
                $dates[] = $latestAppointment;
            }
        }
        
        // Get latest consultation
        if ($student->consultations->isNotEmpty()) {
            $latestConsultation = $student->consultations->max(function($consultation) {
                return $consultation->consultation_date ? Carbon::parse($consultation->consultation_date) : null;
            });
            if ($latestConsultation) {
                $dates[] = $latestConsultation;
            }
        }
        
        // Get latest symptom log
        if ($student->symptomLogs->isNotEmpty()) {
            $latestSymptom = $student->symptomLogs->max('logged_at');
            if ($latestSymptom) {
                $dates[] = Carbon::parse($latestSymptom);
            }
        }
        
        return $dates ? collect($dates)->max() : null;
    }

    /**
     * Calculate health risk level for a student (for AJAX search)
     */
    private function calculateStudentHealthRiskLevel($student)
    {
        $now = Carbon::now();
        $sevenDaysAgo = $now->copy()->subDays(7);
        $thirtyDaysAgo = $now->copy()->subDays(30);
        
        $recentSymptomLogs = $student->symptomLogs->where('logged_at', '>=', $sevenDaysAgo)->count();
        $recentConsultations = $student->consultations->where('consultation_date', '>=', $sevenDaysAgo)->count();
        
        // Check medical record for risk factors
        $hasChronicConditions = $student->medicalRecord && $student->medicalRecord->chronic_conditions;
        $hasMaintenanceDrugs = $student->medicalRecord && $student->medicalRecord->is_taking_maintenance_drugs;
        $hasAllergies = $student->medicalRecord && $student->medicalRecord->allergies;
        
        // Emergency cases in last 30 days
        $emergencyCases = $student->consultations->where('type', 'emergency')
            ->where('consultation_date', '>=', $thirtyDaysAgo)
            ->count();

        if ($recentConsultations >= 3 || $recentSymptomLogs >= 10 || $hasChronicConditions || $emergencyCases >= 2) {
            return 'High';
        } elseif ($recentConsultations >= 2 || $recentSymptomLogs >= 5 || $hasMaintenanceDrugs || $emergencyCases >= 1) {
            return 'Medium';
        } elseif ($recentConsultations >= 1 || $recentSymptomLogs >= 1 || $hasAllergies) {
            return 'Low';
        } else {
            return 'None';
        }
    }

    /**
     * Display the specified student report.
     */
    public function show($studentId)
    {
        try {
            $user = auth()->user();
            
            // Only nurses can view student reports
            if ($user->role !== 'nurse') {
                abort(403, 'Unauthorized access');
            }

            // Get student details with medical record only
            $student = User::where('id', $studentId)
                ->where('role', 'student')
                ->with(['medicalRecord'])
                ->firstOrFail();

            // Get related data
            $appointments = $this->getAppointments($studentId);
            $symptomLogs = $this->getSymptomLogs($studentId);
            $consultations = $this->getConsultations($studentId);
            $vitalSigns = $this->getVitalSigns($studentId);
            $emergencyContacts = $this->getEmergencyContacts($studentId);

            // Calculate health statistics
            $healthStats = $this->getStudentHealthStats($student, $symptomLogs, $consultations, $appointments);

            return view('nurse.student-reports.show', compact(
                'student',
                'appointments',
                'symptomLogs',
                'consultations',
                'vitalSigns',
                'emergencyContacts',
                'healthStats'
            ));

        } catch (\Exception $e) {
            Log::error('Student report show error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load student report: ' . $e->getMessage());
        }
    }

    /**
     * Export student report as PDF
     */
    public function exportPdf($studentId)
    {
        try {
            // Get student details with medical record only
            $student = User::where('id', $studentId)
                ->where('role', 'student')
                ->with(['medicalRecord'])
                ->firstOrFail();

            // Get related data
            $appointments = $this->getAppointments($studentId);
            $symptomLogs = $this->getSymptomLogs($studentId);
            $consultations = $this->getConsultations($studentId);
            $vitalSigns = $this->getVitalSigns($studentId);
            $emergencyContacts = $this->getEmergencyContacts($studentId);

            // Calculate health statistics
            $healthStats = $this->getStudentHealthStats($student, $symptomLogs, $consultations, $appointments);

            $pdf = PDF::loadView('nurse.student-reports.pdf', compact(
                'student',
                'appointments',
                'symptomLogs',
                'consultations',
                'vitalSigns',
                'emergencyContacts',
                'healthStats'
            ));

            return $pdf->download("student-report-{$student->student_id}.pdf");

        } catch (\Exception $e) {
            Log::error('Student report PDF export error: ' . $e->getMessage());
            return back()->with('error', 'Failed to export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Print student report
     */
    public function printReport($studentId)
    {
        try {
            // Get student details with medical record only
            $student = User::where('id', $studentId)
                ->where('role', 'student')
                ->with(['medicalRecord'])
                ->firstOrFail();

            // Get related data
            $appointments = $this->getAppointments($studentId);
            $symptomLogs = $this->getSymptomLogs($studentId);
            $consultations = $this->getConsultations($studentId);
            $vitalSigns = $this->getVitalSigns($studentId);
            $emergencyContacts = $this->getEmergencyContacts($studentId);

            // Calculate health statistics
            $healthStats = $this->getStudentHealthStats($student, $symptomLogs, $consultations, $appointments);

            return view('nurse.student-reports.print', compact(
                'student',
                'appointments',
                'symptomLogs',
                'consultations',
                'vitalSigns',
                'emergencyContacts',
                'healthStats'
            ));

        } catch (\Exception $e) {
            Log::error('Student report print error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load print view: ' . $e->getMessage());
        }
    }

    /**
     * Get emergency contacts for student from medical record
     */
    private function getEmergencyContacts($studentId)
    {
        $medicalRecord = MedicalRecord::where('user_id', $studentId)->first();
        
        if (!$medicalRecord) {
            return collect();
        }

        $contacts = [];
        
        // Extract emergency contact 1 if exists
        if ($medicalRecord->emergency_contact_name_1) {
            $contacts[] = [
                'name' => $medicalRecord->emergency_contact_name_1,
                'relationship' => $medicalRecord->emergency_contact_relationship_1,
                'phone' => $medicalRecord->emergency_contact_number_1,
                'email' => $medicalRecord->emergency_contact_email_1,
                'address' => $medicalRecord->emergency_contact_address_1,
                'is_primary' => true,
            ];
        }
        
        // Extract emergency contact 2 if exists
        if ($medicalRecord->emergency_contact_name_2) {
            $contacts[] = [
                'name' => $medicalRecord->emergency_contact_name_2,
                'relationship' => $medicalRecord->emergency_contact_relationship_2,
                'phone' => $medicalRecord->emergency_contact_number_2,
                'email' => $medicalRecord->emergency_contact_email_2,
                'address' => $medicalRecord->emergency_contact_address_2,
                'is_primary' => false,
            ];
        }

        return collect($contacts);
    }

    /**
     * Get appointments for student
     */
    private function getAppointments($studentId)
    {
        return Appointment::where('user_id', $studentId)
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->limit(50)
            ->get();
    }

    /**
     * Get symptom logs for student
     */
    private function getSymptomLogs($studentId)
    {
        return SymptomLog::where('user_id', $studentId)
            ->orderBy('logged_at', 'desc')
            ->limit(50)
            ->get();
    }

    /**
     * Get consultations for student
     */
    private function getConsultations($studentId)
    {
        return Consultation::where('student_id', $studentId)
            ->orderBy('consultation_date', 'desc')
            ->limit(50)
            ->get();
    }

    /**
     * Get vital signs history for student
     */
    private function getVitalSigns($studentId)
    {
        return Consultation::where('student_id', $studentId)
            ->where(function($query) {
                $query->whereNotNull('temperature')
                      ->orWhereNotNull('blood_pressure_systolic')
                      ->orWhereNotNull('heart_rate')
                      ->orWhereNotNull('respiratory_rate')
                      ->orWhereNotNull('oxygen_saturation');
            })
            ->orderBy('consultation_date', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($consultation) {
                return [
                    'date' => $consultation->consultation_date,
                    'temperature' => $consultation->temperature,
                    'blood_pressure' => $consultation->blood_pressure_systolic && $consultation->blood_pressure_diastolic 
                        ? $consultation->blood_pressure_systolic . '/' . $consultation->blood_pressure_diastolic
                        : null,
                    'heart_rate' => $consultation->heart_rate,
                    'respiratory_rate' => $consultation->respiratory_rate,
                    'oxygen_saturation' => $consultation->oxygen_saturation,
                    'recorded_at' => $consultation->consultation_date,
                    'recorded_by' => $consultation->nurse ?? null,
                ];
            });
    }

   /**
 * Calculate student health statistics
 */
private function getStudentHealthStats($student, $symptomLogs, $consultations, $appointments)
{
    $now = Carbon::now();
    $thirtyDaysAgo = $now->copy()->subDays(30);

    // Calculate BMI if height and weight are available
    $bmi = null;
    $bmiCategory = null;
    if ($student->medicalRecord && $student->medicalRecord->height && $student->medicalRecord->weight) {
        $heightInMeters = $student->medicalRecord->height / 100;
        $bmi = $student->medicalRecord->weight / ($heightInMeters * $heightInMeters);
        $bmiCategory = $this->getBMICategory($bmi);
    }

    // Calculate additional stats
    $emergencyCases = $consultations->where('type', 'emergency')->count();
    $averagePainLevel = $consultations->avg('pain_level') ?? 0;
    $followUpNeeded = $consultations->where('status', 'follow_up')->count() + 
                     $symptomLogs->where('status', 'follow_up_needed')->count();

    return [
        'total_consultations' => $consultations->count(),
        'total_appointments' => $appointments->count(),
        'total_symptoms_logged' => $symptomLogs->count(), // Changed from 'total_symptom_logs'
        'recent_consultations' => $consultations->where('consultation_date', '>=', $thirtyDaysAgo)->count(),
        'recent_appointments' => $appointments->where('appointment_date', '>=', $thirtyDaysAgo)->count(),
        'recent_symptoms_logged' => $symptomLogs->where('logged_at', '>=', $thirtyDaysAgo)->count(), // Changed from 'recent_symptom_logs'
        'common_symptoms' => $this->getCommonSymptoms($symptomLogs),
        'health_risk_level' => $this->calculateHealthRiskLevelFromData($symptomLogs, $consultations),
        'emergency_cases' => $emergencyCases,
        'average_pain_level' => round($averagePainLevel, 1),
        'follow_up_needed' => $followUpNeeded,
        'bmi' => $bmi,
        'bmi_category' => $bmiCategory,
        'medical_record_completion' => $this->calculateMedicalRecordCompletion($student),
        'has_allergies' => $student->medicalRecord && $student->medicalRecord->allergies,
        'has_chronic_conditions' => $student->medicalRecord && $student->medicalRecord->chronic_conditions,
    ];
}

    /**
     * Get BMI category
     */
    private function getBMICategory($bmi)
    {
        if ($bmi < 18.5) {
            return ['category' => 'Underweight', 'color' => 'warning'];
        } elseif ($bmi < 25) {
            return ['category' => 'Normal', 'color' => 'success'];
        } elseif ($bmi < 30) {
            return ['category' => 'Overweight', 'color' => 'warning'];
        } else {
            return ['category' => 'Obese', 'color' => 'danger'];
        }
    }

    /**
     * Get common symptoms from symptom logs
     */
    private function getCommonSymptoms($symptomLogs)
    {
        $symptomCounts = [];
        
        foreach ($symptomLogs as $log) {
            // Handle symptoms stored as array in the symptoms field
            $symptoms = $log->symptoms;
            
            if (is_array($symptoms)) {
                foreach ($symptoms as $symptomName) {
                    if (!empty(trim($symptomName))) {
                        if (!isset($symptomCounts[$symptomName])) {
                            $symptomCounts[$symptomName] = 0;
                        }
                        $symptomCounts[$symptomName]++;
                    }
                }
            } elseif (!empty(trim($symptoms))) {
                // Handle single symptom string
                if (!isset($symptomCounts[$symptoms])) {
                    $symptomCounts[$symptoms] = 0;
                }
                $symptomCounts[$symptoms]++;
            }
        }
        
        arsort($symptomCounts);
        return array_slice($symptomCounts, 0, 5, true);
    }

    /**
     * Calculate health risk level from symptom and consultation data
     */
    private function calculateHealthRiskLevelFromData($symptomLogs, $consultations)
    {
        $now = Carbon::now();
        $sevenDaysAgo = $now->copy()->subDays(7);
        
        $recentSymptomLogs = $symptomLogs->where('logged_at', '>=', $sevenDaysAgo)->count();
        $recentConsultations = $consultations->where('consultation_date', '>=', $sevenDaysAgo)->count();
        
        if ($recentConsultations >= 3 || $recentSymptomLogs >= 10) {
            return 'High';
        } elseif ($recentConsultations >= 2 || $recentSymptomLogs >= 5) {
            return 'Medium';
        } else {
            return 'Low';
        }
    }

    /**
     * Export all student reports
     */
    public function exportAll()
    {
        try {
            $students = User::where('role', 'student')
                ->with(['medicalRecord'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            $pdf = PDF::loadView('nurse.student-reports.export-all', compact('students'));
            
            return $pdf->download('all-student-reports.pdf');
        } catch (\Exception $e) {
            Log::error('Export all student reports error: ' . $e->getMessage());
            return back()->with('error', 'Failed to export all reports: ' . $e->getMessage());
        }
    }

    /**
     * Show student health timeline
     */
    public function timeline($studentId)
    {
        try {
            $student = User::where('id', $studentId)
                ->where('role', 'student')
                ->with(['medicalRecord'])
                ->firstOrFail();

            $timelineData = $this->getTimelineData($studentId);

            return view('nurse.student-reports.timeline', compact('student', 'timelineData'));
        } catch (\Exception $e) {
            Log::error('Student timeline error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load timeline: ' . $e->getMessage());
        }
    }

    /**
     * Get timeline data for student
     */
    private function getTimelineData($studentId)
    {
        $consultations = Consultation::where('student_id', $studentId)
            ->orderBy('consultation_date', 'desc')
            ->get()
            ->map(function ($consultation) {
                return [
                    'type' => 'consultation',
                    'date' => $consultation->consultation_date,
                    'title' => 'Medical Consultation',
                    'description' => $consultation->chief_complaint,
                    'data' => $consultation
                ];
            });

        $appointments = Appointment::where('user_id', $studentId)
            ->orderBy('appointment_date', 'desc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'type' => 'appointment',
                    'date' => $appointment->appointment_date,
                    'title' => 'Appointment: ' . $appointment->reason,
                    'description' => 'Status: ' . ucfirst($appointment->status),
                    'data' => $appointment
                ];
            });

        $symptomLogs = SymptomLog::where('user_id', $studentId)
            ->orderBy('logged_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'type' => 'symptom',
                    'date' => $log->logged_at,
                    'title' => 'Symptom Log',
                    'description' => 'Severity: ' . ($log->severity_rating ?? 'N/A'),
                    'data' => $log
                ];
            });

        // Combine and sort all timeline events
        $timeline = $consultations->concat($appointments)->concat($symptomLogs);
        
        return $timeline->sortByDesc('date')->values();
    }

    /**
     * Quick overview of student health
     */
    public function quickOverview($studentId)
    {
        try {
            $student = User::where('id', $studentId)
                ->where('role', 'student')
                ->with(['medicalRecord'])
                ->firstOrFail();

            $recentConsultations = $this->getConsultations($studentId)->take(5);
            $recentSymptoms = $this->getSymptomLogs($studentId)->take(10);
            $healthStats = $this->getStudentHealthStats($student, $recentSymptoms, $recentConsultations, $this->getAppointments($studentId)->take(5));

            return response()->json([
                'student' => $student,
                'recent_consultations' => $recentConsultations,
                'recent_symptoms' => $recentSymptoms,
                'health_stats' => $healthStats
            ]);
        } catch (\Exception $e) {
            Log::error('Quick overview error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load quick overview'], 500);
        }
    }

    /**
     * Student medical history
     */
    public function medicalHistory($studentId)
    {
        try {
            $student = User::where('id', $studentId)
                ->where('role', 'student')
                ->with(['medicalRecord'])
                ->firstOrFail();

            $consultations = $this->getConsultations($studentId);
            $appointments = $this->getAppointments($studentId);
            $symptomLogs = $this->getSymptomLogs($studentId);

            return view('nurse.student-reports.medical-history', compact(
                'student',
                'consultations',
                'appointments',
                'symptomLogs'
            ));
        } catch (\Exception $e) {
            Log::error('Medical history error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load medical history: ' . $e->getMessage());
        }
    }
}