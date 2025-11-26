<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use App\Models\SymptomLog;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Cache TTL in minutes
    private const CACHE_TTL = 15;

    // Student Dashboard
    public function student()
    {
        $user = auth()->user();

        try {
            // Get student's medical records
            $medicalRecords = Cache::remember(
                "student_medical_records_{$user->id}",
                self::CACHE_TTL,
                fn() => MedicalRecord::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
            );

            // Get upcoming appointments
            $appointments = Cache::remember(
                "student_appointments_{$user->id}",
                self::CACHE_TTL,
                fn() => Appointment::where('user_id', $user->id)
                    ->where('appointment_date', '>=', now())
                    ->orderBy('appointment_date', 'asc')
                    ->take(5)
                    ->get()
            );

            // Get health statistics
            $healthStats = [
                'last_checkup' => optional($medicalRecords->first())->created_at?->format('M j, Y'),
                'blood_type' => optional($user->medicalRecord)->blood_type ?? 'Not set',
                'bmi' => $user->medicalRecord ? $user->medicalRecord->calculateBMI() : null,
            ];

            return view('dashboard.student', compact(
                'user',
                'medicalRecords',
                'appointments',
                'healthStats'
            ));
        } catch (\Exception $e) {
            Log::error('Student dashboard error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('dashboard.student', [
                'user' => $user,
                'medicalRecords' => collect(),
                'appointments' => collect(),
                'healthStats' => [
                    'last_checkup' => null,
                    'blood_type' => 'Not set',
                    'bmi' => null,
                ],
                'error' => 'Unable to load dashboard data'
            ])->with('error', 'Unable to load dashboard data');
        }
    }

    // Nurse Dashboard
    public function nurse()
    {
        $user = auth()->user();

        try {
            // Today's appointments collection
            $todaysAppointments = Cache::remember(
                'nurse_todays_appointments_' . today()->toDateString(),
                self::CACHE_TTL,
                fn() => Appointment::whereDate('appointment_date', today())
                    ->orderBy('appointment_time', 'asc')
                    ->with('user')
                    ->get()
            );

            // Recent medical records
            $recentRecords = Cache::remember(
                'nurse_recent_records',
                self::CACHE_TTL,
                fn() => MedicalRecord::with('user')
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get()
            );

            // Weekly statistics
            $weeklyStats = $this->getWeeklyStats();

            // Dashboard metrics
            $todayAppointments = $todaysAppointments->count();
            $pendingRecords = Cache::remember(
                'nurse_pending_records',
                self::CACHE_TTL,
                fn() => MedicalRecord::where('created_at', '>=', now()->subDays(7))
                    ->whereNull('reviewed_at')
                    ->count()
            );

            $urgentCases = Cache::remember(
                'nurse_urgent_cases',
                self::CACHE_TTL,
                fn() => Appointment::where('appointment_date', '>=', now())
                    ->where(function ($query) {
                        return $this->getEmergencyConditions()($query, 'reason');
                    })
                    ->count()
            );

            $studentsSeenToday = Cache::remember(
                'nurse_students_seen_today',
                self::CACHE_TTL,
                fn() => Appointment::whereDate('appointment_date', today())
                    ->where('status', 'completed')
                    ->count()
            );

            // Get recent patients
            $recentPatients = $this->getRecentPatientsSafe();

            // Get emergency symptom logs
            $emergencyLogs = Cache::remember(
                'nurse_emergency_logs',
                self::CACHE_TTL,
                fn() => SymptomLog::where('is_emergency', true)
                    ->whereNull('reviewed_by')
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
            );

            // Calendar data
            $appointments = Cache::remember(
                'nurse_calendar_appointments_' . now()->month,
                self::CACHE_TTL,
                fn() => Appointment::with('user')
                    ->where('appointment_date', '>=', now()->startOfMonth())
                    ->where('appointment_date', '<=', now()->endOfMonth())
                    ->orderBy('appointment_date')
                    ->orderBy('appointment_time')
                    ->get()
            );

            $currentMonth = now();
            $analytics = $this->getNurseAnalytics();

            return view('dashboard.nurse', compact(
                'user',
                'todaysAppointments',
                'recentRecords',
                'pendingRecords',
                'weeklyStats',
                'todayAppointments',
                'urgentCases',
                'studentsSeenToday',
                'recentPatients',
                'emergencyLogs',
                'appointments',
                'currentMonth',
                'analytics'
            ));
        } catch (\Exception $e) {
            Log::error('Nurse dashboard error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('dashboard.nurse', [
                'user' => $user,
                'todaysAppointments' => collect(),
                'recentRecords' => collect(),
                'pendingRecords' => 0,
                'weeklyStats' => [
                    'appointments' => 0,
                    'newRecords' => 0,
                    'commonIssues' => collect(),
                    'emergencyLogs' => 0
                ],
                'todayAppointments' => 0,
                'urgentCases' => 0,
                'studentsSeenToday' => 0,
                'recentPatients' => collect(),
                'emergencyLogs' => collect(),
                'appointments' => collect(),
                'currentMonth' => now(),
                'analytics' => $this->getDefaultAnalytics(),
                'error' => 'Unable to load dashboard data'
            ])->with('error', 'Unable to load dashboard data');
        }
    }

    // Dean Dashboard - Main Dashboard
    public function dean()
    {
        $user = auth()->user();

        try {
            // Route to appropriate department dashboard based on user's department
            switch ($user->department) {
                case User::DEPARTMENT_BSBA:
                    return $this->bsbaDashboard();
                case User::DEPARTMENT_BSIT:
                    return $this->bsitDashboard();
                case User::DEPARTMENT_EDUC:
                    return $this->educDashboard();
                default:
                    // Fallback to general dean dashboard
                    return $this->generalDeanDashboard();
            }
        } catch (\Exception $e) {
            Log::error('Dean dashboard routing error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'department' => $user->department,
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->generalDeanDashboard();
        }
    }

    // BSBA Department Dashboard
    public function bsbaDashboard()
    {
        $user = auth()->user();
        
        // BSBA courses
        $bsbaCourses = ['BSBA-MM', 'BSBA-FM'];
        
        try {
            $data = [
                'stats' => $this->getDepartmentStats($bsbaCourses),
                'healthTrends' => $this->getDepartmentHealthTrends($bsbaCourses),
                'recentActivity' => $this->getRecentActivity($bsbaCourses),
                'systemStatus' => $this->getSystemStatus(),
                'user' => $user
            ];

            return view('dashboard.dean-bsba', $data);
        } catch (\Exception $e) {
            Log::error('BSBA dashboard error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('dashboard.dean-bsba', $this->getDefaultDeanData($user))
                ->with('error', 'Unable to load BSBA dashboard data');
        }
    }

    // BSIT Department Dashboard
    public function bsitDashboard()
    {
        $user = auth()->user();
        
        // BSIT courses
        $bsitCourses = ['BSIT'];
        
        try {
            $data = [
                'stats' => $this->getDepartmentStats($bsitCourses),
                'healthTrends' => $this->getDepartmentHealthTrends($bsitCourses),
                'recentActivity' => $this->getRecentActivity($bsitCourses),
                'systemStatus' => $this->getSystemStatus(),
                'user' => $user
            ];

            return view('dashboard.dean-bsit', $data);
        } catch (\Exception $e) {
            Log::error('BSIT dashboard error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('dashboard.dean-bsit', $this->getDefaultDeanData($user))
                ->with('error', 'Unable to load BSIT dashboard data');
        }
    }

    // Education Department Dashboard
    public function educDashboard()
    {
        $user = auth()->user();
        
        // Education courses
        $educCourses = ['BSED', 'BEED'];
        
        try {
            $data = [
                'stats' => $this->getDepartmentStats($educCourses),
                'healthTrends' => $this->getDepartmentHealthTrends($educCourses),
                'recentActivity' => $this->getRecentActivity($educCourses),
                'systemStatus' => $this->getSystemStatus(),
                'user' => $user
            ];

            return view('dashboard.dean-educ', $data);
        } catch (\Exception $e) {
            Log::error('EDUC dashboard error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('dashboard.dean-educ', $this->getDefaultDeanData($user))
                ->with('error', 'Unable to load Education dashboard data');
        }
    }

    // General Dean Dashboard (fallback)
    private function generalDeanDashboard()
    {
        $user = auth()->user();

        try {
            // Overall statistics
            $stats = [
                'totalStudents' => Cache::remember(
                    'dean_total_students',
                    self::CACHE_TTL,
                    fn() => User::where('role', User::ROLE_STUDENT)->count()
                ),
                'totalStaff' => Cache::remember(
                    'dean_total_staff',
                    self::CACHE_TTL,
                    fn() => User::whereIn('role', [User::ROLE_NURSE, User::ROLE_DEAN])->count()
                ),
                'totalAppointments' => Cache::remember(
                    'dean_total_appointments',
                    self::CACHE_TTL,
                    fn() => Appointment::count()
                ),
                'totalRecords' => Cache::remember(
                    'dean_total_records',
                    self::CACHE_TTL,
                    fn() => MedicalRecord::count()
                ),
            ];

            // Recent activity
            $recentActivity = Cache::remember(
                'dean_recent_activity',
                self::CACHE_TTL,
                fn() => MedicalRecord::with('user')
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get()
            );

            // Health trends and system status
            $healthTrends = $this->getHealthTrends();
            $systemStatus = $this->getSystemStatus();

            return view('dashboard.dean', compact(
                'user',
                'stats',
                'recentActivity',
                'healthTrends',
                'systemStatus'
            ));
        } catch (\Exception $e) {
            Log::error('General dean dashboard error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('dashboard.dean', $this->getDefaultDeanData($user))
                ->with('error', 'Unable to load dashboard data');
        }
    }

    /**
     * Get department-specific statistics
     */
    private function getDepartmentStats(array $courses)
    {
        return Cache::remember(
            'dept_stats_' . implode('_', $courses) . '_' . today()->toDateString(),
            self::CACHE_TTL,
            function () use ($courses) {
                // Total students in department
                $totalStudents = User::where('role', User::ROLE_STUDENT)
                    ->whereIn('course', $courses)
                    ->count();

                // Total appointments for department students
                $totalAppointments = Appointment::whereHas('user', function($query) use ($courses) {
                    $query->where('role', User::ROLE_STUDENT)
                          ->whereIn('course', $courses);
                })->count();

                // Total medical records for department students
                $totalRecords = MedicalRecord::whereHas('user', function($query) use ($courses) {
                    $query->where('role', User::ROLE_STUDENT)
                          ->whereIn('course', $courses);
                })->count();

                // Department staff (nurses + deans for this department)
                $currentUser = auth()->user();
                $totalStaff = User::whereIn('role', [User::ROLE_NURSE, User::ROLE_DEAN])
                    ->where('department', $currentUser->department)
                    ->count();

                return [
                    'totalStudents' => $totalStudents,
                    'totalAppointments' => $totalAppointments,
                    'totalRecords' => $totalRecords,
                    'totalStaff' => $totalStaff,
                ];
            }
        );
    }

    /**
     * Get department health trends with course and year level breakdown
     */
    private function getDepartmentHealthTrends(array $courses)
    {
        return Cache::remember(
            'dept_health_trends_' . implode('_', $courses) . '_' . today()->toDateString(),
            self::CACHE_TTL,
            function () use ($courses) {
                // Course breakdown with student counts
                $courseBreakdown = User::where('role', User::ROLE_STUDENT)
                    ->whereIn('course', $courses)
                    ->select('course', DB::raw('COUNT(*) as student_count'))
                    ->groupBy('course')
                    ->get()
                    ->map(function($item) use ($courses) {
                        // Add recent appointments and records for each course
                        $item->recent_appointments = Appointment::whereHas('user', function($query) use ($item) {
                            $query->where('role', User::ROLE_STUDENT)
                                  ->where('course', $item->course);
                        })->where('created_at', '>=', Carbon::now()->subDays(30))
                          ->count();

                        $item->recent_records = MedicalRecord::whereHas('user', function($query) use ($item) {
                            $query->where('role', User::ROLE_STUDENT)
                                  ->where('course', $item->course);
                        })->where('updated_at', '>=', Carbon::now()->subDays(30))
                          ->count();

                        return $item;
                    });

                // Year level health trends
                $yearLevelTrends = User::where('role', User::ROLE_STUDENT)
                    ->whereIn('course', $courses)
                    ->whereNotNull('year_level')
                    ->select('year_level', DB::raw('COUNT(*) as student_count'))
                    ->groupBy('year_level')
                    ->orderBy('year_level')
                    ->get()
                    ->map(function($item) use ($courses) {
                        // Get health statistics by year level
                        $item->health_stats = [
                            'with_medical_records' => MedicalRecord::whereHas('user', function($query) use ($item, $courses) {
                                $query->where('role', User::ROLE_STUDENT)
                                      ->where('year_level', $item->year_level)
                                      ->whereIn('course', $courses);
                            })->count(),
                            'recent_consultations' => Consultation::whereHas('student', function($query) use ($item, $courses) {
                                $query->where('year_level', $item->year_level)
                                      ->whereIn('course', $courses);
                            })->where('created_at', '>=', Carbon::now()->subDays(30))->count(),
                            'emergency_cases' => SymptomLog::whereHas('user', function($query) use ($item, $courses) {
                                $query->where('role', User::ROLE_STUDENT)
                                      ->where('year_level', $item->year_level)
                                      ->whereIn('course', $courses);
                            })->where('is_emergency', true)
                              ->where('created_at', '>=', Carbon::now()->subDays(30))
                              ->count(),
                        ];

                        return $item;
                    });

                // Top health conditions in department
                $topConditions = MedicalRecord::whereHas('user', function($query) use ($courses) {
                    $query->where('role', User::ROLE_STUDENT)
                          ->whereIn('course', $courses);
                })
                ->whereNotNull('past_illnesses')
                ->where('past_illnesses', '!=', '')
                ->select('past_illnesses', DB::raw('COUNT(*) as count'))
                ->groupBy('past_illnesses')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

                // Monthly visit trends
                $monthlyVisits = Consultation::whereHas('student', function($query) use ($courses) {
                    $query->whereIn('course', $courses);
                })
                ->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

                return [
                    'courseBreakdown' => $courseBreakdown,
                    'yearLevelTrends' => $yearLevelTrends,
                    'topConditions' => $topConditions,
                    'monthlyVisits' => $monthlyVisits,
                ];
            }
        );
    }

    /**
     * Get recent student activity in department
     */
    private function getRecentActivity(array $courses)
    {
        return Cache::remember(
            'dept_recent_activity_' . implode('_', $courses) . '_' . today()->toDateString(),
            self::CACHE_TTL,
            function () use ($courses) {
                // Get recent medical records, consultations, and symptom logs
                $recentMedicalRecords = MedicalRecord::whereHas('user', function($query) use ($courses) {
                    $query->where('role', User::ROLE_STUDENT)
                          ->whereIn('course', $courses);
                })
                ->with('user')
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();

                $recentConsultations = Consultation::whereHas('student', function($query) use ($courses) {
                    $query->whereIn('course', $courses);
                })
                ->with('student')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

                $recentSymptomLogs = SymptomLog::whereHas('user', function($query) use ($courses) {
                    $query->where('role', User::ROLE_STUDENT)
                          ->whereIn('course', $courses);
                })
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

                // Combine and sort all activities
                $allActivities = collect()
                    ->merge($recentMedicalRecords->map(function($item) {
                        $item->activity_type = 'medical_record';
                        return $item;
                    }))
                    ->merge($recentConsultations->map(function($item) {
                        $item->activity_type = 'consultation';
                        return $item;
                    }))
                    ->merge($recentSymptomLogs->map(function($item) {
                        $item->activity_type = 'symptom_log';
                        return $item;
                    }))
                    ->sortByDesc('created_at')
                    ->take(10);

                return $allActivities;
            }
        );
    }

    /**
     * API endpoint for course-specific health trends
     */
    public function getCourseHealthTrends(Request $request, $course)
    {
        $user = auth()->user();
        
        // Verify dean has access to this course
        if (!in_array($course, $user->allowed_courses ?? [])) {
            return response()->json(['error' => 'Unauthorized access to course data'], 403);
        }

        $yearLevel = $request->get('year_level');

        $query = User::where('role', User::ROLE_STUDENT)
            ->where('course', $course);

        if ($yearLevel) {
            $query->where('year_level', $yearLevel);
        }

        $students = $query->with(['medicalRecord', 'symptomLogs' => function($q) {
            $q->where('created_at', '>=', Carbon::now()->subDays(30));
        }])->get();

        // Analyze health trends
        $healthData = $this->analyzeHealthTrends($students);

        return response()->json([
            'course' => $course,
            'year_level' => $yearLevel,
            'total_students' => $students->count(),
            'health_trends' => $healthData,
            'common_conditions' => $this->getCommonConditions($students),
            'symptom_patterns' => $this->getSymptomPatterns($students),
        ]);
    }

    /**
     * Analyze health trends for a group of students
     */
    private function analyzeHealthTrends($students)
    {
        $analysis = [
            'risk_levels' => [
                'low' => 0,
                'medium' => 0,
                'high' => 0
            ],
            'completion_rates' => [
                'complete' => 0,
                'incomplete' => 0
            ],
            'recent_activity' => [
                'consultations' => 0,
                'symptoms' => 0,
                'appointments' => 0
            ]
        ];

        foreach ($students as $student) {
            // Risk level analysis
            $riskLevel = $student->getHealthRiskLevel();
            $analysis['risk_levels'][strtolower($riskLevel)]++;

            // Medical record completion
            if ($student->hasCompleteMedicalRecord()) {
                $analysis['completion_rates']['complete']++;
            } else {
                $analysis['completion_rates']['incomplete']++;
            }

            // Recent activity
            $analysis['recent_activity']['consultations'] += $student->consultations()
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->count();

            $analysis['recent_activity']['symptoms'] += $student->symptomLogs()
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->count();

            $analysis['recent_activity']['appointments'] += $student->appointments()
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->count();
        }

        return $analysis;
    }

    /**
     * Get common conditions for students
     */
    private function getCommonConditions($students)
    {
        $conditions = [];

        foreach ($students as $student) {
            if ($student->medicalRecord && $student->medicalRecord->past_illnesses) {
                $illnesses = explode(',', $student->medicalRecord->past_illnesses);
                foreach ($illnesses as $illness) {
                    $illness = trim($illness);
                    if ($illness) {
                        $conditions[$illness] = ($conditions[$illness] ?? 0) + 1;
                    }
                }
            }
        }

        arsort($conditions);
        return array_slice($conditions, 0, 10, true);
    }

    /**
     * Get symptom patterns for students
     */
    private function getSymptomPatterns($students)
    {
        $symptoms = [];

        foreach ($students as $student) {
            foreach ($student->symptomLogs as $log) {
                if (is_array($log->symptoms)) {
                    foreach ($log->symptoms as $symptom) {
                        $symptoms[$symptom] = ($symptoms[$symptom] ?? 0) + 1;
                    }
                }
            }
        }

        arsort($symptoms);
        return array_slice($symptoms, 0, 15, true);
    }

    /**
     * Get default data for dean dashboard error fallback
     */
    private function getDefaultDeanData($user)
    {
        return [
            'user' => $user,
            'stats' => [
                'totalStudents' => 0,
                'totalStaff' => 0,
                'totalAppointments' => 0,
                'totalRecords' => 0,
            ],
            'recentActivity' => collect(),
            'healthTrends' => [
                'courseBreakdown' => collect(),
                'yearLevelTrends' => collect(),
                'topConditions' => collect(),
                'monthlyVisits' => collect(),
            ],
            'systemStatus' => [
                'uptime' => 'N/A',
                'storage' => ['used' => 'N/A', 'total' => 'N/A', 'percentage' => 0],
                'activeUsers' => 0,
                'database_status' => 'Error',
                'last_backup' => null,
                'emergency_cases' => 0,
            ],
        ];
    }

    // Get top symptoms for chart
    public function getTopSymptoms()
    {
        try {
            $topSymptoms = Cache::remember('dashboard_top_symptoms_' . now()->month, self::CACHE_TTL, function () {
                return SymptomLog::whereNotNull('symptoms')
                    ->whereMonth('logged_at', now()->month)
                    ->select('symptoms', DB::raw('COUNT(*) as count'))
                    ->groupBy('symptoms')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($log) {
                        return [
                            'reason' => implode(', ', (array) $log->symptoms), // Handle symptoms as array
                            'count' => $log->count
                        ];
                    });
            });

            return response()->json([
                'labels' => $topSymptoms->pluck('reason'),
                'counts' => $topSymptoms->pluck('count')
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getTopSymptoms: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'labels' => [],
                'counts' => []
            ], 500);
        }
    }

    // Get symptom trends for chart
    public function getSymptomTrends()
    {
        try {
            $trends = Cache::remember('dashboard_symptom_trends_12months', self::CACHE_TTL, function () {
                $logs = SymptomLog::select(
                    DB::raw('YEAR(logged_at) as year'),
                    DB::raw('MONTH(logged_at) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('logged_at', '>=', now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

                $labels = [];
                $counts = array_fill(0, 12, 0); // 12 months array

                foreach ($logs as $log) {
                    $monthIndex = (int)$log->month - 1; // 0-based index
                    $labels[$monthIndex] = Carbon::create($log->year, $log->month)->format('M Y');
                    $counts[$monthIndex] = $log->count;
                }

                return [
                    'labels' => array_values($labels),
                    'counts' => array_values($counts)
                ];
            });

            return response()->json($trends);
        } catch (\Exception $e) {
            Log::error('Error in getSymptomTrends: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'labels' => [],
                'counts' => []
            ], 500);
        }
    }

    // Get symptoms by demographic data
    public function getSymptomsByDemographic(Request $request)
    {
        try {
            $type = $request->get('type', 'course');

            $data = $type === 'course' 
                ? $this->getSymptomsByCourse() 
                : $this->getSymptomsByYearLevel();

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error in getSymptomsByDemographic: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'labels' => [],
                'datasets' => []
            ], 500);
        }
    }

    // Private Helper Methods

    private function getNurseAnalytics()
    {
        return Cache::remember('nurse_analytics_' . now()->month, self::CACHE_TTL, function () {
            try {
                return [
                    'monthly_symptom_checks' => MedicalRecord::whereMonth('created_at', now()->month)->count(),
                    'emergency_cases' => Appointment::where(function ($query) {
                        return $this->getEmergencyConditions()($query, 'reason');
                    })
                        ->whereMonth('appointment_date', now()->month)
                        ->count(),
                    'review_completion_rate' => $this->calculateReviewCompletionRate(),
                    'weekly_submissions' => MedicalRecord::whereBetween('created_at', [
                        now()->startOfWeek(), now()->endOfWeek()
                    ])->count(),
                    'weekly_emergencies' => Appointment::where(function ($query) {
                        return $this->getEmergencyConditions()($query, 'reason');
                    })
                        ->whereBetween('appointment_date', [now()->startOfWeek(), now()->endOfWeek()])
                        ->count(),
                    'weekly_followups' => Appointment::where('status', 'scheduled')
                        ->whereBetween('appointment_date', [now()->startOfWeek(), now()->endOfWeek()])
                        ->count(),
                    'weekly_reviews' => $this->getWeeklyReviewsPercentage(),
                    'top_symptoms' => $this->getTopSymptomsData(),
                    'symptom_trends' => $this->getSymptomTrends()->original['counts'] ?? collect(),
                    'emergency_logs_count' => SymptomLog::where('is_emergency', true)
                        ->where('created_at', '>=', now()->subWeek())
                        ->count(),
                ];
            } catch (\Exception $e) {
                Log::error('Error getting nurse analytics: ' . $e->getMessage(), [
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return $this->getDefaultAnalytics();
            }
        });
    }

    private function getDefaultAnalytics()
    {
        return [
            'monthly_symptom_checks' => 0,
            'emergency_cases' => 0,
            'review_completion_rate' => 0,
            'weekly_submissions' => 0,
            'weekly_emergencies' => 0,
            'weekly_followups' => 0,
            'weekly_reviews' => 0,
            'top_symptoms' => collect(),
            'symptom_trends' => collect(),
            'emergency_logs_count' => 0,
        ];
    }

    private function calculateReviewCompletionRate()
    {
        try {
            $totalRecords = MedicalRecord::count();
            if ($totalRecords === 0) return 0;

            $reviewedRecords = MedicalRecord::whereNotNull('reviewed_at')->count();
            return round(($reviewedRecords / $totalRecords) * 100, 2);
        } catch (\Exception $e) {
            Log::error('Error calculating review completion rate: ' . $e->getMessage(), [
                'line' => $e->getLine()
            ]);
            return 0;
        }
    }

    private function getWeeklyReviewsPercentage()
    {
        try {
            $weeklyRecords = MedicalRecord::whereBetween('created_at', [
                now()->startOfWeek(), now()->endOfWeek()
            ])->count();

            if ($weeklyRecords === 0) return 0;

            $weeklyReviewed = MedicalRecord::whereBetween('created_at', [
                now()->startOfWeek(), now()->endOfWeek()
            ])->whereNotNull('reviewed_at')->count();

            return round(($weeklyReviewed / $weeklyRecords) * 100, 2);
        } catch (\Exception $e) {
            Log::error('Error calculating weekly reviews percentage: ' . $e->getMessage(), [
                'line' => $e->getLine()
            ]);
            return 0;
        }
    }

    private function getTopSymptomsData()
    {
        return Cache::remember('dashboard_top_symptoms_data_' . now()->month, self::CACHE_TTL, function () {
            try {
                $totalAppointments = Appointment::whereMonth('appointment_date', now()->month)->count();

                return SymptomLog::whereNotNull('symptoms')
                    ->whereMonth('logged_at', now()->month)
                    ->select('symptoms', DB::raw('COUNT(*) as count'))
                    ->groupBy('symptoms')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($item) use ($totalAppointments) {
                        return [
                            'symptom' => implode(', ', (array) $item->symptoms),
                            'count' => $item->count,
                            'percentage' => round(($item->count / max(1, $totalAppointments)) * 100, 1)
                        ];
                    });
            } catch (\Exception $e) {
                Log::error('Error getting top symptoms data: ' . $e->getMessage(), [
                    'line' => $e->getLine()
                ]);
                return collect();
            }
        });
    }

    private function getSymptomsByCourse()
    {
        return Cache::remember('symptoms_by_course', self::CACHE_TTL, function () {
            try {
                $courses = User::where('role', User::ROLE_STUDENT)
                    ->whereNotNull('course')
                    ->select('course', DB::raw('COUNT(*) as student_count'))
                    ->groupBy('course')
                    ->get();

                $commonSymptoms = SymptomLog::whereNotNull('symptoms')
                    ->select('symptoms', DB::raw('COUNT(*) as count'))
                    ->groupBy('symptoms')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($log) {
                        return implode(', ', (array) $log->symptoms);
                    });

                $datasets = [];
                $colors = [
                    'rgba(78, 115, 223, 0.8)',
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(246, 194, 62, 0.8)',
                    'rgba(231, 74, 59, 0.8)',
                    'rgba(126, 87, 194, 0.8)'
                ];

                foreach ($commonSymptoms as $index => $symptom) {
                    $symptomCounts = [];

                    foreach ($courses as $course) {
                        $count = SymptomLog::whereHas('user', function ($query) use ($course) {
                            $query->where('course', $course->course);
                        })
                        ->where('symptoms', 'like', "%$symptom%")
                        ->count();

                        $symptomCounts[] = $count;
                    }

                    $datasets[] = [
                        'label' => $symptom,
                        'data' => $symptomCounts,
                        'backgroundColor' => $colors[$index % count($colors)]
                    ];
                }

                return [
                    'labels' => $courses->pluck('course'),
                    'datasets' => $datasets
                ];
            } catch (\Exception $e) {
                Log::error('Error getting symptoms by course: ' . $e->getMessage(), [
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return [
                    'labels' => [],
                    'datasets' => []
                ];
            }
        });
    }

    private function getSymptomsByYearLevel()
    {
        return Cache::remember('symptoms_by_year_level', self::CACHE_TTL, function () {
            try {
                $yearLevels = ['1st year', '2nd year', '3rd year', '4th year', '5th year'];

                $commonSymptoms = SymptomLog::whereNotNull('symptoms')
                    ->select('symptoms', DB::raw('COUNT(*) as count'))
                    ->groupBy('symptoms')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($log) {
                        return implode(', ', (array) $log->symptoms);
                    });

                $datasets = [];
                $colors = [
                    'rgba(78, 115, 223, 0.8)',
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(246, 194, 62, 0.8)',
                    'rgba(231, 74, 59, 0.8)',
                    'rgba(126, 87, 194, 0.8)'
                ];

                foreach ($commonSymptoms as $index => $symptom) {
                    $symptomCounts = [];

                    foreach ($yearLevels as $yearLevel) {
                        $count = SymptomLog::whereHas('user', function ($query) use ($yearLevel) {
                            $query->where('year_level', $yearLevel);
                        })
                        ->where('symptoms', 'like', "%$symptom%")
                        ->count();

                        $symptomCounts[] = $count;
                    }

                    $datasets[] = [
                        'label' => $symptom,
                        'data' => $symptomCounts,
                        'backgroundColor' => $colors[$index % count($colors)]
                    ];
                }

                return [
                    'labels' => $yearLevels,
                    'datasets' => $datasets
                ];
            } catch (\Exception $e) {
                Log::error('Error getting symptoms by year level: ' . $e->getMessage(), [
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return [
                    'labels' => [],
                    'datasets' => []
                ];
            }
        });
    }

    private function getRecentPatientsSafe()
    {
        return Cache::remember('recent_patients', self::CACHE_TTL, function () {
            try {
                return User::where('role', User::ROLE_STUDENT)
                    ->with(['appointments' => fn($query) => $query->latest()->limit(1)])
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(function ($patient) {
                        $patient->full_name = $patient->first_name . ' ' . $patient->last_name;

                        $lastAppointment = $patient->appointments->first();
                        $patient->last_visit_date = $lastAppointment?->appointment_date?->format('M j, Y') ?? 'N/A';
                        $patient->last_visit_reason = $lastAppointment?->reason ?? 'No visits';
                        $patient->last_visit_status = $lastAppointment?->status ?? 'N/A';

                        return $patient;
                    });
            } catch (\Exception $e) {
                Log::error('Error getting recent patients: ' . $e->getMessage(), [
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return collect();
            }
        });
    }

    private function getWeeklyStats()
    {
        return Cache::remember('weekly_stats', self::CACHE_TTL, function () {
            try {
                $startOfWeek = now()->startOfWeek();
                $endOfWeek = now()->endOfWeek();

                return [
                    'appointments' => Appointment::whereBetween('appointment_date', [$startOfWeek, $endOfWeek])->count(),
                    'newRecords' => MedicalRecord::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),
                    'commonIssues' => $this->getCommonComplaintsThisWeek(),
                    'emergencyLogs' => SymptomLog::where('is_emergency', true)
                        ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                        ->count(),
                ];
            } catch (\Exception $e) {
                Log::error('Error getting weekly stats: ' . $e->getMessage(), [
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return [
                    'appointments' => 0,
                    'newRecords' => 0,
                    'commonIssues' => collect(),
                    'emergencyLogs' => 0,
                ];
            }
        });
    }

    private function getHealthTrends()
    {
        return Cache::remember('health_trends', self::CACHE_TTL, function () {
            try {
                return [
                    'topConditions' => MedicalRecord::whereNotNull('past_illnesses')
                        ->where('past_illnesses', '!=', '')
                        ->select('past_illnesses', DB::raw('COUNT(*) as count'))
                        ->groupBy('past_illnesses')
                        ->orderBy('count', 'desc')
                        ->limit(5)
                        ->get(),
                    'monthlyVisits' => $this->getMonthlyVisitTrends(),
                    'commonSymptoms' => $this->getTopSymptomsData()->take(5),
                ];
            } catch (\Exception $e) {
                Log::error('Error getting health trends: ' . $e->getMessage(), [
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return [
                    'topConditions' => collect(),
                    'monthlyVisits' => collect(),
                    'commonSymptoms' => collect(),
                ];
            }
        });
    }

    private function getSystemStatus()
    {
        try {
            return [
                'uptime' => '99.8%',
                'storage' => [
                    'used' => '2.8 GB',
                    'total' => '10 GB',
                    'percentage' => 28,
                ],
                'activeUsers' => Cache::remember(
                    'active_users',
                    self::CACHE_TTL,
                    fn() => User::whereNotNull('updated_at')
                        ->where('updated_at', '>=', now()->subHours(24))
                        ->count()
                ),
                'database_status' => $this->getDatabaseStatus(),
                'last_backup' => now()->subDays(1)->format('M j, Y g:i A'),
                'emergency_cases' => SymptomLog::where('is_emergency', true)
                    ->whereNull('reviewed_by')
                    ->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting system status: ' . $e->getMessage(), [
                'line' => $e->getLine()
            ]);
            return [
                'uptime' => 'N/A',
                'storage' => ['used' => 'N/A', 'total' => 'N/A', 'percentage' => 0],
                'activeUsers' => 0,
                'database_status' => 'Error',
                'last_backup' => null,
                'emergency_cases' => 0,
            ];
        }
    }

    private function getCommonComplaints($date)
    {
        try {
            return Appointment::whereDate('appointment_date', $date)
                ->whereNotNull('reason')
                ->where('reason', '!=', '')
                ->select('reason', DB::raw('COUNT(*) as count'))
                ->groupBy('reason')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error getting common complaints: ' . $e->getMessage(), [
                'line' => $e->getLine()
            ]);
            return collect();
        }
    }

    private function getCommonComplaintsThisWeek()
    {
        try {
            return Appointment::whereBetween('appointment_date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
                ->whereNotNull('reason')
                ->where('reason', '!=', '')
                ->select('reason', DB::raw('COUNT(*) as count'))
                ->groupBy('reason')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error getting weekly complaints: ' . $e->getMessage(), [
                'line' => $e->getLine()
            ]);
            return collect();
        }
    }

    private function getWeeklyHealthTrends($start, $end)
    {
        try {
            return [
                'appointments_by_day' => Appointment::whereBetween('appointment_date', [$start, $end])
                    ->select(DB::raw('DATE(appointment_date) as date'), DB::raw('COUNT(*) as count'))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
                'common_reasons' => Appointment::whereBetween('appointment_date', [$start, $end])
                    ->whereNotNull('reason')
                    ->select('reason', DB::raw('COUNT(*) as count'))
                    ->groupBy('reason')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get(),
                'emergency_logs' => SymptomLog::where('is_emergency', true)
                    ->whereBetween('created_at', [$start, $end])
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting weekly health trends: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'appointments_by_day' => collect(),
                'common_reasons' => collect(),
                'emergency_logs' => collect(),
            ];
        }
    }

    private function getMonthlyVisitTrends()
    {
        return Cache::remember('monthly_visit_trends', self::CACHE_TTL, function () {
            try {
                return Appointment::select(
                    DB::raw('MONTH(appointment_date) as month'),
                    DB::raw('YEAR(appointment_date) as year'),
                    DB::raw('COUNT(*) as count')
                )
                    ->where('appointment_date', '>=', now()->subMonths(6))
                    ->groupBy('year', 'month')
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'month' => Carbon::createFromDate($item->year, $item->month, 1)->format('M Y'),
                            'count' => $item->count
                        ];
                    });
            } catch (\Exception $e) {
                Log::error('Error getting monthly visit trends: ' . $e->getMessage(), [
                    'line' => $e->getLine()
                ]);
                return collect();
            }
        });
    }

    private function getDatabaseStatus()
    {
        try {
            DB::connection()->getPdo();
            return 'Connected';
        } catch (\Exception $e) {
            Log::error('Database connection error: ' . $e->getMessage(), [
                'line' => $e->getLine()
            ]);
            return 'Error';
        }
    }

    public function searchUsers(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100'
        ]);

        try {
            $query = $request->input('query');

            $users = User::where('role', User::ROLE_STUDENT)
                ->where(function ($q) use ($query) {
                    $q->where('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%")
                        ->orWhere('student_id', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                })
                ->select('id', 'first_name', 'last_name', 'student_id', 'email')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching users: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'query' => $request->input('query')
            ]);
            return response()->json([
                'success' => false,
                'users' => [],
                'message' => 'Error searching users'
            ], 500);
        }
    }

    public function dailyReport()
    {
        try {
            $today = now()->format('Y-m-d');

            $report = [
                'date' => now()->format('M j, Y'),
                'appointments' => Appointment::whereDate('appointment_date', $today)->count(),
                'completedAppointments' => Appointment::whereDate('appointment_date', $today)
                    ->where('status', 'completed')
                    ->count(),
                'newRecords' => MedicalRecord::whereDate('created_at', $today)->count(),
                'commonComplaints' => $this->getCommonComplaints($today),
                'emergencyLogs' => SymptomLog::whereDate('created_at', $today)
                    ->where('is_emergency', true)
                    ->count(),
            ];

            return view('reports.daily', compact('report'));
        } catch (\Exception $e) {
            Log::error('Error generating daily report: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error generating daily report.');
        }
    }

    public function weeklyReport()
    {
        try {
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();

            $report = [
                'date_range' => $startOfWeek->format('M j') . ' - ' . $endOfWeek->format('M j, Y'),
                'appointments' => Appointment::whereBetween('appointment_date', [$startOfWeek, $endOfWeek])->count(),
                'completedAppointments' => Appointment::whereBetween('appointment_date', [$startOfWeek, $endOfWeek])
                    ->where('status', 'completed')
                    ->count(),
                'newRecords' => MedicalRecord::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),
                'healthTrends' => $this->getWeeklyHealthTrends($startOfWeek, $endOfWeek),
                'emergencyLogs' => SymptomLog::where('is_emergency', true)
                    ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->count(),
            ];

            return view('reports.weekly', compact('report'));
        } catch (\Exception $e) {
            Log::error('Error generating weekly report: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error generating weekly report.');
        }
    }

    public function getDashboardStats()
    {
        try {
            $user = auth()->user();

            $stats = match ($user->role) {
                User::ROLE_STUDENT => $this->getStudentStats($user),
                User::ROLE_NURSE => $this->getNurseStats(),
                User::ROLE_DEAN => $this->getDeanStats(),
                default => [],
            };

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting dashboard stats: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'stats' => [],
                'message' => 'Error retrieving dashboard stats'
            ], 500);
        }
    }

    private function getStudentStats($user)
    {
        return Cache::remember("student_stats_{$user->id}", self::CACHE_TTL, function () use ($user) {
            try {
                return [
                    'medicalRecords' => MedicalRecord::where('user_id', $user->id)->count(),
                    'upcomingAppointments' => Appointment::where('user_id', $user->id)
                        ->where('appointment_date', '>=', now())
                        ->count(),
                    'lastCheckup' => MedicalRecord::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->value('created_at')?->format('M j, Y') ?? 'N/A',
                    'symptomLogs' => SymptomLog::where('user_id', $user->id)
                        ->where('logged_at', '>=', now()->subMonth())
                        ->count(),
                ];
            } catch (\Exception $e) {
                Log::error('Error getting student stats: ' . $e->getMessage(), [
                    'line' => $e->getLine(),
                    'user_id' => $user->id
                ]);
                return [
                    'medicalRecords' => 0,
                    'upcomingAppointments' => 0,
                    'lastCheckup' => 'N/A',
                    'symptomLogs' => 0,
                ];
            }
        });
    }

    private function getNurseStats()
    {
        return Cache::remember('nurse_stats', self::CACHE_TTL, function () {
            try {
                return [
                    'todaysAppointments' => Appointment::whereDate('appointment_date', today())->count(),
                    'pendingRecords' => MedicalRecord::where('created_at', '>=', now()->subDays(7))
                        ->whereNull('reviewed_at')
                        ->count(),
                    'weeklyAverage' => Appointment::whereBetween('appointment_date', [
                        now()->startOfWeek(), now()->endOfWeek()
                    ])->count() / max(1, now()->dayOfWeek),
                    'emergencyLogs' => SymptomLog::where('is_emergency', true)
                        ->whereNull('reviewed_by')
                        ->count(),
                ];
            } catch (\Exception $e) {
                Log::error('Error getting nurse stats: ' . $e->getMessage(), [
                    'line' => $e->getLine()
                ]);
                return [
                    'todaysAppointments' => 0,
                    'pendingRecords' => 0,
                    'weeklyAverage' => 0,
                    'emergencyLogs' => 0,
                ];
            }
        });
    }

    private function getDeanStats()
    {
        return Cache::remember('dean_stats', self::CACHE_TTL, function () {
            try {
                return [
                    'totalUsers' => User::count(),
                    'totalAppointments' => Appointment::count(),
                    'totalMedicalRecords' => MedicalRecord::count(),
                    'totalSymptomLogs' => SymptomLog::count(),
                    'systemHealth' => $this->getDatabaseStatus(),
                    'pendingEmergencies' => SymptomLog::where('is_emergency', true)
                        ->whereNull('reviewed_by')
                        ->count(),
                ];
            } catch (\Exception $e) {
                Log::error('Error getting dean stats: ' . $e->getMessage(), [
                    'line' => $e->getLine()
                ]);
                return [
                    'totalUsers' => 0,
                    'totalAppointments' => 0,
                    'totalMedicalRecords' => 0,
                    'totalSymptomLogs' => 0,
                    'systemHealth' => 'Error',
                    'pendingEmergencies' => 0,
                ];
            }
        });
    }

    public function checkEmergencies()
    {
        try {
            $emergencyCount = 0;
            $emergencyAlerts = collect();

            // Check for urgent appointments
            $urgentAppointments = Appointment::where('appointment_date', '>=', now())
                ->where(function ($query) {
                    return $this->getEmergencyConditions()($query, 'reason');
                })
                ->where('status', '!=', 'completed')
                ->with(['user' => fn($query) => $query->select('id', 'first_name', 'last_name')])
                ->orderBy('appointment_date', 'asc')
                ->take(5)
                ->get();

            $emergencyCount += $urgentAppointments->count();

            // Check for recent emergency medical records
            $urgentRecords = MedicalRecord::where('created_at', '>=', now()->subHours(24))
                ->where(function ($query) {
                    return $this->getEmergencyConditions()($query, 'chief_complaint');
                })
                ->with(['user' => fn($query) => $query->select('id', 'first_name', 'last_name')])
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();

            $emergencyCount += $urgentRecords->count();

            // Check for emergency symptom logs
            $emergencySymptomLogs = SymptomLog::where('is_emergency', true)
                ->whereNull('reviewed_by')
                ->with(['user' => fn($query) => $query->select('id', 'first_name', 'last_name')])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $emergencyCount += $emergencySymptomLogs->count();

            $emergencyAlerts = $urgentAppointments->merge($urgentRecords)->merge($emergencySymptomLogs);

            Cache::put('last_emergency_check', now()->toISOString(), self::CACHE_TTL);

            return response()->json([
                'success' => true,
                'count' => $emergencyCount,
                'emergencies' => $emergencyAlerts->take(8)->map(function ($item) {
                    return [
                        'type' => class_basename($item),
                        'id' => $item->id,
                        'user' => $item->user ? [
                            'id' => $item->user->id,
                            'name' => $item->user->first_name . ' ' . $item->user->last_name
                        ] : null,
                        'date' => $item->appointment_date ?? $item->created_at,
                        'reason' => $item->reason ?? $item->chief_complaint ?? $item->symptoms[0] ?? 'Emergency case',
                        'priority' => $item->priority ?? ($item->is_emergency ? 'High' : 'Normal')
                    ];
                }),
                'message' => $emergencyCount > 0
                    ? "Found {$emergencyCount} urgent case(s) requiring attention"
                    : 'No emergency cases found',
                'timestamp' => now()->toISOString(),
                'last_check' => Cache::get('last_emergency_check', now()->toISOString()),
            ]);
        } catch (\Exception $e) {
            Log::error('Emergency check failed: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'count' => 0,
                'emergencies' => [],
                'message' => 'Unable to check emergency status',
                'error' => config('app.debug') ? $e->getMessage() : 'System error',
            ], 500);
        }
    }

    public function markSymptomReviewed($id)
    {
        try {
            $symptomLog = SymptomLog::findOrFail($id);
            $symptomLog->update([
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Clear relevant cache
            Cache::forget('nurse_emergency_logs');
            Cache::forget('nurse_stats');
            Cache::forget('dean_stats');

            return response()->json([
                'success' => true,
                'message' => 'Symptom log marked as reviewed',
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking symptom as reviewed: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'symptom_log_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error updating symptom log',
                'error' => config('app.debug') ? $e->getMessage() : 'System error',
            ], 500);
        }
    }

    private function getEmergencyConditions($column = 'reason')
    {
        return function ($query) use ($column) {
            $query->where($column, 'like', '%urgent%')
                ->orWhere($column, 'like', '%emergency%')
                ->orWhere($column, 'like', '%chest pain%')
                ->orWhere($column, 'like', '%breathing%')
                ->orWhere('priority', '>=', 3);
        };
    }
}