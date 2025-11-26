<?php


namespace App\Http\Controllers\Nurse;  // This is the correct namespace
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SymptomLog;
use App\Models\User;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NurseAnalyticsController extends Controller
{
    /**
     * Display the nurse analytics dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $analytics = [
            'monthly_symptom_checks' => $this->getMonthlySymptomLogs(),
            'emergency_cases' => $this->getEmergencyCases(),
            'review_completion_rate' => $this->getReviewCompletionRate(),
            'symptom_trends' => $this->getSymptomTrends(30),
            'top_symptoms' => [
                'all' => $this->getTopSymptoms(),
                'by_course' => $this->getTopSymptomsByCourse(),
                'by_year_level' => $this->getTopSymptomsByYearLevel(),
            ],
            'avg_response_time' => $this->getAverageResponseTime(),
            'resolved_today' => $this->getResolvedToday(),
            'pending_cases' => $this->getPendingCases(),
            'weekly_submissions' => $this->getWeeklySubmissions(),
            'weekly_emergencies' => $this->getWeeklyEmergencies(),
            'weekly_followups' => $this->getWeeklyFollowups(),
            'weekly_reviews' => $this->getWeeklyReviewRate(),
        ];

        $highRiskStudents = $this->getHighRiskStudents();

        return view('nurse.analytics.index', compact('analytics', 'highRiskStudents'));
    }

    /**
     * Get monthly symptom logs count
     */
    private function getMonthlySymptomLogs()
    {
        return SymptomLog::where(function($query) {
                $query->whereMonth('logged_at', Carbon::now()->month)
                      ->orWhereMonth('created_at', Carbon::now()->month);
            })
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
    }

    /**
     * Get emergency cases this month
     */
    private function getEmergencyCases()
    {
        return SymptomLog::where(function($query) {
                $query->whereMonth('logged_at', Carbon::now()->month)
                      ->orWhereMonth('created_at', Carbon::now()->month);
            })
            ->whereYear('created_at', Carbon::now()->year)
            ->where('is_emergency', true)
            ->count();
    }

    /**
     * Get review completion rate
     */
    private function getReviewCompletionRate()
    {
        $total = SymptomLog::count();
        if ($total == 0) return 0;

        $reviewed = SymptomLog::whereNotNull('reviewed_by')->count();
        return round(($reviewed / $total) * 100, 1);
    }

    /**
     * Get symptom trends for the last N months (formatted for chart)
     */
    private function getSymptomTrends($months = 12)
    {
        $startDate = Carbon::now()->subMonths($months)->startOfMonth();
        
        $trends = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthLabel = $month->format('M Y');
            
            $count = SymptomLog::where(function($query) use ($month) {
                $query->where(function($q) use ($month) {
                    $q->whereYear('logged_at', $month->year)
                      ->whereMonth('logged_at', $month->month);
                })->orWhere(function($q) use ($month) {
                    $q->whereYear('created_at', $month->year)
                      ->whereMonth('created_at', $month->month);
                });
            })->count();

            $trends[] = [
                'month' => $monthLabel,
                'count' => $count
            ];
        }

        return [
            'labels' => collect($trends)->pluck('month')->toArray(),
            'counts' => collect($trends)->pluck('count')->toArray()
        ];
    }

    /**
     * Get top symptoms overall
     */
    private function getTopSymptoms($limit = 10)
    {
        try {
            $logs = SymptomLog::where(function($query) {
                    $query->where('logged_at', '>=', Carbon::now()->subMonth())
                          ->orWhere('created_at', '>=', Carbon::now()->subMonth());
                })
                ->whereNotNull('symptoms')
                ->get();

            $allSymptoms = [];
            foreach ($logs as $log) {
                $symptoms = is_array($log->symptoms) ? $log->symptoms : [];
                foreach ($symptoms as $symptom) {
                    if (is_string($symptom)) {
                        $symptomKey = trim(strtolower($symptom));
                        if (!empty($symptomKey)) {
                            $allSymptoms[] = ucfirst($symptomKey);
                        }
                    }
                }
            }

            $symptomCounts = collect($allSymptoms)
                ->countBy()
                ->sortDesc()
                ->take($limit);

            return $symptomCounts->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting top symptoms: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top symptoms grouped by course
     */
    private function getTopSymptomsByCourse()
    {
        $symptomsByCourse = [];
        $courses = ['BSIT', 'BSBA', 'BSBA-MM', 'BSBA-FM', 'BSED', 'BEED'];

        foreach ($courses as $course) {
            $studentIds = User::where('role', 'student')
                ->where('course', $course)
                ->pluck('id');

            if ($studentIds->isEmpty()) {
                $symptomsByCourse[$course] = [];
                continue;
            }

            $logs = SymptomLog::whereIn('user_id', $studentIds)
                ->where(function($query) {
                    $query->where('logged_at', '>=', Carbon::now()->subMonth())
                          ->orWhere('created_at', '>=', Carbon::now()->subMonth());
                })
                ->whereNotNull('symptoms')
                ->get();

            $allSymptoms = [];
            foreach ($logs as $log) {
                $symptoms = is_array($log->symptoms) ? $log->symptoms : [];
                foreach ($symptoms as $symptom) {
                    if (is_string($symptom)) {
                        $symptomKey = trim(strtolower($symptom));
                        if (!empty($symptomKey)) {
                            $allSymptoms[] = ucfirst($symptomKey);
                        }
                    }
                }
            }

            $symptomCounts = collect($allSymptoms)
                ->countBy()
                ->sortDesc()
                ->take(5);

            $symptomsByCourse[$course] = $symptomCounts->toArray();
        }

        return $symptomsByCourse;
    }

    /**
     * Get top symptoms grouped by year level
     */
    private function getTopSymptomsByYearLevel()
    {
        $symptomsByYear = [];
        $yearLevels = ['1st_year', '2nd_year', '3rd_year', '4th_year'];

        foreach ($yearLevels as $year) {
            $studentIds = User::where('role', 'student')
                ->where('year_level', $year)
                ->pluck('id');

            if ($studentIds->isEmpty()) {
                $symptomsByYear["Year " . str_replace('_', ' ', $year)] = [];
                continue;
            }

            $logs = SymptomLog::whereIn('user_id', $studentIds)
                ->where(function($query) {
                    $query->where('logged_at', '>=', Carbon::now()->subMonth())
                          ->orWhere('created_at', '>=', Carbon::now()->subMonth());
                })
                ->whereNotNull('symptoms')
                ->get();

            $allSymptoms = [];
            foreach ($logs as $log) {
                $symptoms = is_array($log->symptoms) ? $log->symptoms : [];
                foreach ($symptoms as $symptom) {
                    if (is_string($symptom)) {
                        $symptomKey = trim(strtolower($symptom));
                        if (!empty($symptomKey)) {
                            $allSymptoms[] = ucfirst($symptomKey);
                        }
                    }
                }
            }

            $symptomCounts = collect($allSymptoms)
                ->countBy()
                ->sortDesc()
                ->take(5);

            $symptomsByYear["Year " . str_replace('_', ' ', $year)] = $symptomCounts->toArray();
        }

        return $symptomsByYear;
    }

    /**
     * Get average response time for emergency cases
     */
    private function getAverageResponseTime()
    {
        $avgTime = SymptomLog::where('is_emergency', true)
            ->whereNotNull('reviewed_by')
            ->whereNotNull('reviewed_at')
            ->whereNotNull('created_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, reviewed_at)) as avg_minutes')
            ->value('avg_minutes');

        return $avgTime ? round($avgTime, 0) : 0;
    }

    /**
     * Get cases resolved today
     */
    private function getResolvedToday()
    {
        return SymptomLog::whereDate('reviewed_at', Carbon::today())
            ->whereNotNull('reviewed_by')
            ->count();
    }

    /**
     * Get pending emergency cases
     */
    private function getPendingCases()
    {
        return SymptomLog::where('is_emergency', true)
            ->whereNull('reviewed_by')
            ->count();
    }

    /**
     * Get weekly submissions
     */
    private function getWeeklySubmissions()
    {
        return SymptomLog::where(function($query) {
            $query->whereBetween('logged_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->orWhereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]);
        })->count();
    }

    /**
     * Get weekly emergencies
     */
    private function getWeeklyEmergencies()
    {
        return SymptomLog::where(function($query) {
            $query->whereBetween('logged_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->orWhereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]);
        })
        ->where('is_emergency', true)
        ->count();
    }

    /**
     * Get weekly follow-ups
     */
    private function getWeeklyFollowups()
    {
        return Appointment::whereBetween('appointment_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])
        ->where('status', 'scheduled')
        ->count();
    }

    /**
     * Get weekly review completion rate
     */
    private function getWeeklyReviewRate()
    {
        $total = SymptomLog::where(function($query) {
            $query->whereBetween('logged_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->orWhereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]);
        })->count();

        if ($total == 0) return 0;

        $reviewed = SymptomLog::where(function($query) {
            $query->whereBetween('logged_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->orWhereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]);
        })
        ->whereNotNull('reviewed_by')
        ->count();

        return round(($reviewed / $total) * 100, 1);
    }

    /**
     * Get high-risk students (multiple symptoms or severe cases)
     */
    private function getHighRiskStudents($limit = 5)
    {
        // Students with multiple symptom logs in the last 7 days
        $recentLogs = SymptomLog::where(function($query) {
                $query->where('logged_at', '>=', Carbon::now()->subDays(7))
                      ->orWhere('created_at', '>=', Carbon::now()->subDays(7));
            })
            ->select('user_id', DB::raw('COUNT(*) as log_count'))
            ->groupBy('user_id')
            ->having('log_count', '>=', 3)
            ->pluck('log_count', 'user_id');

        // Students with recent emergency cases
        $emergencyCases = SymptomLog::where(function($query) {
                $query->where('logged_at', '>=', Carbon::now()->subDays(7))
                      ->orWhere('created_at', '>=', Carbon::now()->subDays(7));
            })
            ->where('is_emergency', true)
            ->pluck('user_id')
            ->unique();

        $highRiskIds = $recentLogs->keys()->merge($emergencyCases)->unique();

        return User::whereIn('id', $highRiskIds)
            ->with(['symptomLogs' => function($query) {
                $query->where(function($q) {
                    $q->where('logged_at', '>=', Carbon::now()->subDays(7))
                      ->orWhere('created_at', '>=', Carbon::now()->subDays(7));
                })->latest();
            }])
            ->take($limit)
            ->get()
            ->map(function ($student) use ($recentLogs, $emergencyCases) {
                if ($emergencyCases->contains($student->id)) {
                    $student->risk_reason = 'Recent emergency case';
                } else {
                    $count = $recentLogs->get($student->id, 0);
                    $student->risk_reason = "Multiple symptoms reported ($count times in 7 days)";
                }
                return $student;
            });
    }

    /**
     * Get analytics data for AJAX requests
     */
    public function getData(Request $request)
    {
        $period = $request->get('period', 'week');
        $type = $request->get('type', 'all');

        $data = [];

        switch ($type) {
            case 'trends':
                $days = $period === 'month' ? 30 : ($period === 'year' ? 365 : 7);
                $data = $this->getSymptomTrends(ceil($days / 30));
                break;

            case 'by_course':
                $data = $this->getTopSymptomsByCourse();
                break;

            case 'by_year':
                $data = $this->getTopSymptomsByYearLevel();
                break;

            default:
                $data = [
                    'monthly_checks' => $this->getMonthlySymptomLogs(),
                    'emergency_cases' => $this->getEmergencyCases(),
                    'top_symptoms' => $this->getTopSymptoms(),
                ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'timestamp' => Carbon::now()->toISOString()
        ]);
    }

    /**
     * Export analytics report
     */
    public function exportReport(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::now()->subMonth()));
        $endDate = Carbon::parse($request->get('end_date', Carbon::now()));

        $data = [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'summary' => [
                'total_submissions' => SymptomLog::where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('logged_at', [$startDate, $endDate])
                          ->orWhereBetween('created_at', [$startDate, $endDate]);
                })->count(),
                'emergency_cases' => SymptomLog::where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('logged_at', [$startDate, $endDate])
                          ->orWhereBetween('created_at', [$startDate, $endDate]);
                })->where('is_emergency', true)->count(),
                'unique_students' => SymptomLog::where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('logged_at', [$startDate, $endDate])
                          ->orWhereBetween('created_at', [$startDate, $endDate]);
                })->distinct('user_id')->count('user_id'),
            ],
            'top_symptoms' => $this->getTopSymptoms(20),
            'by_course' => $this->getTopSymptomsByCourse(),
            'by_year_level' => $this->getTopSymptomsByYearLevel(),
        ];

        return response()->json($data);
    }

    /**
     * Get illness data by course for charts
     */
    public function illnessByCourse()
    {
        try {
            $courses = ['BSIT', 'BSBA', 'BSBA-MM', 'BSBA-FM', 'BSED', 'BEED'];
            $illnessCounts = [];
            
            foreach ($courses as $course) {
                $studentIds = User::where('role', 'student')
                    ->where('course', $course)
                    ->pluck('id');
                
                $count = SymptomLog::whereIn('user_id', $studentIds)
                    ->where(function($query) {
                        $query->where('logged_at', '>=', Carbon::now()->subMonth())
                              ->orWhere('created_at', '>=', Carbon::now()->subMonth());
                    })
                    ->count();
                
                $illnessCounts[$course] = $count;
            }

            // Return the exact structure your JavaScript expects
            return response()->json([
                'success' => true,
                'labels' => array_keys($illnessCounts),
                'counts' => array_values($illnessCounts),
                'courses' => array_keys($illnessCounts), // For backward compatibility
                'illnesses' => array_fill(0, count($illnessCounts), 'Symptom Cases'), // Default illness name
                'data' => $illnessCounts // Additional data
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting illness by course: ' . $e->getMessage());
            
            // Return safe fallback data
            return response()->json([
                'success' => false,
                'labels' => ['BSIT', 'BSBA', 'BSBA-MM', 'BSBA-FM', 'BSED', 'BEED'],
                'counts' => [0, 0, 0, 0, 0, 0],
                'courses' => ['BSIT', 'BSBA', 'BSBA-MM', 'BSBA-FM', 'BSED', 'BEED'],
                'illnesses' => ['No Data', 'No Data', 'No Data', 'No Data', 'No Data', 'No Data'],
                'error' => 'Unable to load course data'
            ], 200);
        }
    }

    /**
     * Get illness data by year level for charts
     */
    public function illnessByYearLevel()
    {
        try {
            $yearLevels = ['1st_year', '2nd_year', '3rd_year', '4th_year'];
            $illnessCounts = [];
            
            foreach ($yearLevels as $yearLevel) {
                $studentIds = User::where('role', 'student')
                    ->where('year_level', $yearLevel)
                    ->pluck('id');
                
                $count = SymptomLog::whereIn('user_id', $studentIds)
                    ->where(function($query) {
                        $query->where('logged_at', '>=', Carbon::now()->subMonth())
                              ->orWhere('created_at', '>=', Carbon::now()->subMonth());
                    })
                    ->count();
                
                $displayYear = str_replace('_', ' ', $yearLevel);
                $illnessCounts[$displayYear] = $count;
            }

            // Return the exact structure your JavaScript expects
            return response()->json([
                'success' => true,
                'labels' => array_keys($illnessCounts),
                'counts' => array_values($illnessCounts),
                'yearLevels' => array_keys($illnessCounts), // For backward compatibility
                'illnesses' => array_fill(0, count($illnessCounts), 'Symptom Cases'), // Default illness name
                'data' => $illnessCounts // Additional data
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting illness by year level: ' . $e->getMessage());
            
            // Return safe fallback data
            return response()->json([
                'success' => false,
                'labels' => ['1st Year', '2nd Year', '3rd Year', '4th Year'],
                'counts' => [0, 0, 0, 0],
                'yearLevels' => ['1st Year', '2nd Year', '3rd Year', '4th Year'],
                'illnesses' => ['No Data', 'No Data', 'No Data', 'No Data'],
                'error' => 'Unable to load year level data'
            ], 200);
        }
    }

    /**
     * Get appointment statistics
     */
    public function appointmentStats()
    {
        try {
            $stats = [
                'today' => Appointment::whereDate('appointment_date', Carbon::today())->count(),
                'week' => Appointment::whereBetween('appointment_date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])->count(),
                'month' => Appointment::whereMonth('appointment_date', Carbon::now()->month)
                    ->whereYear('appointment_date', Carbon::now()->year)
                    ->count(),
                'completed_today' => Appointment::whereDate('appointment_date', Carbon::today())
                    ->where('status', 'completed')->count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error getting appointment stats: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load appointment statistics'], 500);
        }
    }

    /**
     * Get medical record statistics
     */
    public function medicalRecordStats()
    {
        try {
            $stats = [
                'total' => MedicalRecord::count(),
                'this_month' => MedicalRecord::whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count(),
                'last_month' => MedicalRecord::whereMonth('created_at', Carbon::now()->subMonth()->month)
                    ->whereYear('created_at', Carbon::now()->subMonth()->year)
                    ->count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error getting medical record stats: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load medical record statistics'], 500);
        }
    }

    /**
     * Get health trends data
     */
    public function healthTrends()
    {
        try {
            $trends = $this->getSymptomTrends(6); // Last 6 months
            return response()->json($trends);
        } catch (\Exception $e) {
            Log::error('Error getting health trends: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load health trends'], 500);
        }
    }

    /**
     * Get walk-in consultation statistics
     */
    public function walkInConsultationStats()
    {
        try {
            $stats = [
                'today' => Appointment::whereDate('appointment_date', Carbon::today())
                    ->where('is_walk_in', true)
                    ->count(),
                'this_week' => Appointment::whereBetween('appointment_date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])->where('is_walk_in', true)->count(),
                'completion_rate' => $this->calculateWalkInCompletionRate(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error getting walk-in stats: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to load walk-in statistics'], 500);
        }
    }

    /**
     * Calculate walk-in completion rate
     */
    private function calculateWalkInCompletionRate()
    {
        $total = Appointment::where('is_walk_in', true)->count();
        if ($total == 0) return 0;

        $completed = Appointment::where('is_walk_in', true)
            ->where('status', 'completed')
            ->count();

        return round(($completed / $total) * 100, 1);
    }
}