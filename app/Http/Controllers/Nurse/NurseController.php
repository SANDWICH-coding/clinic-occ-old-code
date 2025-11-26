<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use App\Models\SymptomLog;
use App\Models\User;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\EmergencyAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NurseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:nurse');
    }

    /**
     * Main nurse dashboard
     */
    public function dashboard()
    {
        $today = Carbon::today();

        // Get top symptoms by demographic
        $topSymptomsByDemo = $this->getTopSymptomsByDemographic();
        
        // Get dashboard statistics and analytics
        $analytics = [
           'monthly_symptom_checks' => SymptomLog::where('created_at', '>=', Carbon::now()->subMonths(3))->count(),
'emergency_cases' => SymptomLog::where('is_emergency', true)
    ->where('created_at', '>=', Carbon::now()->subMonths(3))->count(),
            'review_completion_rate' => $this->getReviewCompletionRate(),
            'top_symptoms' => [
                'all' => $this->getTopSymptoms()->toArray(),
                'by_course' => $topSymptomsByDemo['by_course'] ?? [],
                'by_year_level' => $topSymptomsByDemo['by_year_level'] ?? [],
            ],
            'symptom_trends' => $this->getSymptomTrendsForChart(),
            'resolved_today' => Appointment::whereDate('appointment_date', $today)
                ->where('status', 'completed')->count(),
            'pending_cases' => EmergencyAlert::where('status', 'active')->count(),
            'avg_response_time' => $this->getAverageResponseTime(),
            'weekly_submissions' => SymptomLog::where('created_at', '>=', Carbon::now()->startOfWeek())->count(),
            'weekly_emergencies' => SymptomLog::where('is_emergency', true)
                ->where('created_at', '>=', Carbon::now()->startOfWeek())->count(),
            'weekly_followups' => Appointment::where('reason', 'like', '%follow-up%')
                ->where('created_at', '>=', Carbon::now()->startOfWeek())->count(),
            'weekly_reviews' => $this->getWeeklyReviewCompletionRate(),
        ];

        // Identify high-risk students
        $highRiskStudents = User::where('role', 'student')
            ->whereHas('symptomLogs', function ($query) {
                $query->where('is_emergency', true)
                      ->orWhere('priority_level', 'high')
                      ->whereNull('reviewed_by');
            })
            ->with(['symptomLogs' => function ($query) {
                $query->latest()->take(1);
            }])
            ->limit(5)
            ->get()
            ->map(function ($student) {
                $lastLog = $student->symptomLogs->first();
                return (object) [
                    'id' => $student->id,
                    'full_name' => $student->first_name . ' ' . $student->last_name,
                    'risk_reason' => $lastLog ? ($lastLog->is_emergency ? 'Emergency symptoms' : 'High priority') : 'Unreviewed symptoms',
                ];
            });

        return view('nurse.dashboard.nurse', compact('analytics', 'highRiskStudents'));
    }

    /**
     * Get symptom trends formatted for Chart.js
     */
    private function getSymptomTrendsForChart()
    {
        $trends = ['labels' => [], 'datasets' => []];
        $symptomData = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthKey = $month->format('M Y');
            $trends['labels'][] = $monthKey;

            $logs = SymptomLog::where('created_at', '>=', Carbon::now()->subMonths(3))->get();

            foreach ($logs as $log) {
                $symptoms = $this->extractSymptoms($log->symptoms);
                foreach ($symptoms as $symptom) {
                    if (!isset($symptomData[$symptom])) {
                        $symptomData[$symptom] = array_fill(0, 12, 0);
                    }
                    $index = 11 - $i;
                    $symptomData[$symptom][$index]++;
                }
            }
        }

        // Get top 5 symptoms for the chart
        $topSymptoms = [];
        foreach ($symptomData as $symptom => $data) {
            $topSymptoms[$symptom] = array_sum($data);
        }
        arsort($topSymptoms);
        $topSymptoms = array_slice($topSymptoms, 0, 5, true);

        foreach ($topSymptoms as $symptom => $total) {
            $trends['datasets'][$symptom] = $symptomData[$symptom];
        }

        return $trends;
    }

    /**
     * Extract symptoms from log data
     */
    private function extractSymptoms($symptoms)
    {
        $symptomsList = [];
        
        if (is_array($symptoms)) {
            $symptomsList = array_filter($symptoms, 'is_string');
        } elseif (is_string($symptoms)) {
            $symptomsList = array_map('trim', explode(',', $symptoms));
        }

        return collect($symptomsList)
            ->map(function ($symptom) {
                return trim(strtolower($symptom));
            })
            ->filter()
            ->reject(function ($symptom) {
                return empty($symptom);
            })
            ->toArray();
    }

    /**
     * Symptom logs management
     */
    public function symptomLogs(Request $request)
    {
        $query = SymptomLog::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->filter === 'unreviewed') {
            $query->whereNull('reviewed_by');
        } elseif ($request->filter === 'emergency') {
            $query->where('is_emergency', true);
        } elseif ($request->filter === 'today') {
            $query->whereDate('created_at', Carbon::today());
        }

        $logs = $query->paginate(20);
        
        return view('nurse.symptom-logs.index', compact('logs'));
    }

    /**
     * Show detailed symptom log
     */
    public function showSymptomLog(SymptomLog $log)
    {
        $log->load('user');
        
        $studentHistory = MedicalRecord::where('patient_id', $log->user_id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $relatedLogs = SymptomLog::where('user_id', $log->user_id)
            ->where('id', '!=', $log->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('nurse.symptom-logs.show', compact('log', 'studentHistory', 'relatedLogs'));
    }

    /**
     * Review and add notes to symptom log
     */
    public function reviewSymptomLog(Request $request, SymptomLog $log)
    {
        $request->validate([
            'nurse_notes' => 'required|string|max:1000',
            'follow_up_required' => 'boolean',
            'priority_level' => 'required|in:low,medium,high,emergency'
        ]);

        $log->update([
            'reviewed_by' => Auth::id(),
            'nurse_notes' => $request->nurse_notes,
            'follow_up_required' => $request->boolean('follow_up_required'),
            'priority_level' => $request->priority_level,
            'reviewed_at' => now()
        ]);

        if ($request->boolean('follow_up_required')) {
            $this->createFollowUpAppointment($log);
        }

        if ($request->priority_level === 'emergency') {
            $this->createEmergencyAlert($log);
        }

        return redirect()->back()->with('success', 'Symptom log reviewed successfully');
    }

    /**
     * Emergency alerts management
     */
    public function emergencyAlerts()
    {
        $activeAlerts = EmergencyAlert::with('student', 'symptomLog')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $recentResolved = EmergencyAlert::with('student')
            ->where('status', 'resolved')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return view('nurse.emergency-alerts.index', compact('activeAlerts', 'recentResolved'));
    }

    /**
     * Resolve emergency alert
     */
    public function resolveEmergencyAlert(Request $request, EmergencyAlert $alert)
    {
        $request->validate([
            'resolution_notes' => 'required|string|max:1000',
            'action_taken' => 'required|string|max:500'
        ]);

        $alert->update([
            'status' => 'resolved',
            'resolved_by' => Auth::id(),
            'resolution_notes' => $request->resolution_notes,
            'action_taken' => $request->action_taken,
            'resolved_at' => now()
        ]);

        return redirect()->back()->with('success', 'Emergency alert resolved successfully');
    }

    /**
     * Student health analytics
     */
    public function analytics()
    {
        // Use last 3 months instead of just current month
        $threeMonthsAgo = Carbon::now()->subMonths(3);
        
        $analytics = [
            'monthly_symptom_checks' => SymptomLog::where('created_at', '>=', $threeMonthsAgo)->count(),
            'emergency_cases' => SymptomLog::where('is_emergency', true)
                ->where('created_at', '>=', $threeMonthsAgo)->count(),
            'top_symptoms' => $this->getTopSymptomsByDemographic(),
            'symptom_trends' => $this->getSymptomTrendsByDemographic(),
            'follow_up_compliance' => $this->getFollowUpCompliance(),
            'overall_top_symptoms' => $this->getTopSymptoms(),
        ];

        return view('nurse.analytics.index', compact('analytics'));
    }

    /**
     * Create medical record from symptom log
     */
    public function createMedicalRecord(Request $request, SymptomLog $log)
    {
        $request->validate([
            'diagnosis' => 'required|string|max:500',
            'treatment_plan' => 'required|string|max:1000',
            'medications' => 'nullable|string|max:500',
            'follow_up_date' => 'nullable|date|after:today'
        ]);

        $medicalRecord = MedicalRecord::create([
            'patient_id' => $log->user_id,
            'nurse_id' => Auth::id(),
            'symptom_log_id' => $log->id,
            'chief_complaint' => is_array($log->symptoms) ? implode(', ', $log->symptoms) : $log->symptoms,
            'diagnosis' => $request->diagnosis,
            'treatment_plan' => $request->treatment_plan,
            'medications' => $request->medications,
            'follow_up_date' => $request->follow_up_date,
            'status' => 'active',
            'created_at' => now()
        ]);

        $log->update([
            'medical_record_created' => true,
            'medical_record_id' => $medicalRecord->id
        ]);

        return redirect()->route('nurse.medical-records.show', $medicalRecord)
            ->with('success', 'Medical record created successfully');
    }

    /**
     * Student directory with health overview
     */
    public function studentDirectory(Request $request)
    {
        $query = User::where('role', 'student')
            ->with(['symptomLogs' => function($q) {
                $q->latest()->take(1);
            }]);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('student_id', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filter === 'at_risk') {
            $query->whereHas('symptomLogs', function($q) {
                $q->where('is_emergency', true)
                  ->orWhere('priority_level', 'high');
            });
        }

        $students = $query->paginate(20);

        return view('nurse.students.index', compact('students'));
    }

    // Helper methods
    private function createFollowUpAppointment(SymptomLog $log)
    {
        $symptoms = is_array($log->symptoms) ? implode(', ', $log->symptoms) : $log->symptoms;
        
        Appointment::create([
            'patient_id' => $log->user_id,
            'nurse_id' => Auth::id(),
            'appointment_date' => Carbon::now()->addDays(3),
            'reason' => 'Follow-up for symptom check: ' . $symptoms,
            'status' => 'scheduled',
            'priority' => $log->priority_level ?? 'medium'
        ]);
    }

    private function createEmergencyAlert(SymptomLog $log)
    {
        $symptoms = is_array($log->symptoms) ? implode(', ', $log->symptoms) : $log->symptoms;
        
        EmergencyAlert::create([
            'student_id' => $log->user_id,
            'symptom_log_id' => $log->id,
            'alert_type' => 'symptom_emergency',
            'severity' => 'high',
            'message' => 'Emergency symptoms detected: ' . $symptoms,
            'status' => 'active',
            'created_by' => Auth::id()
        ]);
    }

    /**
     * Get top symptoms for analytics
     */
    private function getTopSymptoms()
    {
        // Get data from last 3 months to ensure we have enough data
        $logs = SymptomLog::where('created_at', '>=', Carbon::now()->subMonths(3))->get();
        
        $allSymptoms = [];
        foreach ($logs as $log) {
            $symptoms = $this->extractSymptoms($log->symptoms);
            $allSymptoms = array_merge($allSymptoms, $symptoms);
        }
        
        $symptoms = collect($allSymptoms);
        return $symptoms->countBy()->sortDesc()->take(10);
    }

    /**
     * Get top symptoms grouped by course and year level for analytics
     */
    private function getTopSymptomsByDemographic()
    {
        $logs = SymptomLog::with('user')
    ->where('created_at', '>=', Carbon::now()->subMonths(3))
    ->get();

        $symptomsByCourse = [];
        $symptomsByYearLevel = [];

        foreach ($logs as $log) {
            $user = $log->user;
            if (!$user) continue;

            $course = $user->course ?? 'Undeclared';
            $yearLevel = $user->year_level ?? 'Unknown';

            $symptomsList = $this->extractSymptoms($log->symptoms);

            if (!isset($symptomsByCourse[$course])) $symptomsByCourse[$course] = collect();
            $symptomsByCourse[$course] = $symptomsByCourse[$course]->concat($symptomsList);

            if (!isset($symptomsByYearLevel[$yearLevel])) $symptomsByYearLevel[$yearLevel] = collect();
            $symptomsByYearLevel[$yearLevel] = $symptomsByYearLevel[$yearLevel]->concat($symptomsList);
        }

        $groupedSymptoms = [];
        foreach ($symptomsByCourse as $course => $symptomsCollection) {
            $groupedSymptoms['by_course'][$course] = $symptomsCollection->countBy()->sortDesc()->take(5)->all();
        }
        foreach ($symptomsByYearLevel as $yearLevel => $symptomsCollection) {
            $groupedSymptoms['by_year_level'][$yearLevel] = $symptomsCollection->countBy()->sortDesc()->take(5)->all();
        }

        if (isset($groupedSymptoms['by_course'])) ksort($groupedSymptoms['by_course']);
        if (isset($groupedSymptoms['by_year_level'])) ksort($groupedSymptoms['by_year_level']);

        return $groupedSymptoms;
    }

    /**
     * Get symptom trends grouped by a key demographic for analytics
     */
    private function getSymptomTrendsByDemographic()
    {
        $trends = [];
        $demographic = 'course';
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthKey = $month->format('M Y');
            $logs = SymptomLog::with('user')
                        ->whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->get();
            $monthlyData = [];
            foreach ($logs as $log) {
                $user = $log->user;
                if ($user) {
                    $groupValue = $user->{$demographic} ?? 'Unknown';
                    $monthlyData[$groupValue] = ($monthlyData[$groupValue] ?? 0) + 1;
                }
            }
            $trends[$monthKey] = $monthlyData;
        }
        return $trends;
    }

    /**
     * Get follow up compliance rate
     */
    private function getFollowUpCompliance()
    {
        try {
            $totalFollowUps = DB::table('appointments')
                ->where('reason', 'like', '%follow-up%')
                ->orWhere('reason', 'like', '%Follow-up%')
                ->count();
            $completedFollowUps = DB::table('appointments')
                ->where('status', 'completed')
                ->where(function($query) {
                    $query->where('reason', 'like', '%follow-up%')
                          ->orWhere('reason', 'like', '%Follow-up%');
                })
                ->count();
        } catch (\Exception $e) {
            $totalFollowUps = MedicalRecord::whereNotNull('follow_up_date')->count();
            $completedFollowUps = MedicalRecord::whereNotNull('follow_up_date')
                ->where('status', 'completed')
                ->count();
        }
        return $totalFollowUps > 0 ? round(($completedFollowUps / $totalFollowUps) * 100) : 0;
    }

    /**
     * Calculate average response time for emergency alerts
     */
    private function getAverageResponseTime()
    {
        $resolvedAlerts = EmergencyAlert::where('status', 'resolved')->get();
        if ($resolvedAlerts->isEmpty()) return 5;
        $totalTime = $resolvedAlerts->sum(function ($alert) {
            return $alert->resolved_at->diffInMinutes($alert->created_at);
        });
        return round($totalTime / $resolvedAlerts->count());
    }

    /**
     * Calculate review completion rate
     */
    private function getReviewCompletionRate()
    {
        $totalLogs = SymptomLog::count();
        $reviewedLogs = SymptomLog::whereNotNull('reviewed_by')->count();
        return $totalLogs > 0 ? round(($reviewedLogs / $totalLogs) * 100) : 0;
    }

    /**
     * Calculate weekly review completion rate
     */
    private function getWeeklyReviewCompletionRate()
    {
        $totalLogs = SymptomLog::where('created_at', '>=', Carbon::now()->startOfWeek())->count();
        $reviewedLogs = SymptomLog::where('created_at', '>=', Carbon::now()->startOfWeek())
            ->whereNotNull('reviewed_by')->count();
        return $totalLogs > 0 ? round(($reviewedLogs / $totalLogs) * 100) : 0;
    }

    /**
     * Activity log
     */
    public function activityLog()
    {
        return view('nurse.activity-log');
    }
}