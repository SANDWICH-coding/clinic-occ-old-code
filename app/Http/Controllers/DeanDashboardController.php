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

class DeanDashboardController extends Controller
{
    // Cache TTL in minutes
    private const CACHE_TTL = 15;

    /**
     * Main Dean Dashboard - Auto-redirects to appropriate department
     */
    public function index()
    {
        $user = auth()->user();
        
        try {
            // Determine department based on role first, then email
            $email = strtolower($user->email);
            
            if ($user->role === User::ROLE_DEAN_BSIT || str_contains($email, 'bsit')) {
                return $this->showDepartmentDashboard('BSIT');
            } 
            
            if ($user->role === User::ROLE_DEAN_BSBA || str_contains($email, 'bsba')) {
                return $this->showDepartmentDashboard('BSBA');
            } 
            
            if ($user->role === User::ROLE_DEAN_EDUC || str_contains($email, 'educ')) {
                return $this->showDepartmentDashboard('EDUC');
            }
            
            // If no specific department detected, show error and redirect
            Log::warning('Dean without specific department detected', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);
            
            return redirect()->route('home')
                ->with('error', 'Unable to determine your department. Please contact the administrator.');
            
        } catch (\Exception $e) {
            Log::error('Dean dashboard redirect error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email ?? 'N/A',
                'role' => $user->role ?? 'N/A',
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('home')
                ->with('error', 'An error occurred while loading your dashboard. Please try again.');
        }
    }

    /**
     * Show Unified Department Dashboard
     */
    public function showDepartmentDashboard($department)
    {
        $user = auth()->user();

        try {
            $validDepartments = ['BSIT', 'BSBA', 'EDUC'];
            if (!in_array(strtoupper($department), $validDepartments)) {
                throw new \Exception("Invalid department: {$department}");
            }

            $department = strtoupper($department);
            $dashboardData = $this->getDepartmentDashboardData($department);
            
            $viewMap = [
                'BSIT' => 'dashboard.dean-bsit',
                'BSBA' => 'dashboard.dean-bsba',
                'EDUC' => 'dashboard.dean-educ'
            ];
            
            return view($viewMap[$department] ?? 'dashboard.dean-bsit', compact('user', 'department', 'dashboardData'));
            
        } catch (\Exception $e) {
            Log::error("Dean dashboard error for {$department}: " . $e->getMessage());
            return $this->getErrorView($user, $department);
        }
    }

    /**
     * Get comprehensive dashboard data for department - OPTIMIZED WITH CACHING
     */
    private function getDepartmentDashboardData($department)
    {
        $cacheKey = "dean_dashboard_{$department}_" . now()->format('Y-m-d_H');
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($department) {
            try {
                // Get all required data in single queries
                $courses = $this->getDepartmentCourses($department);
                $studentIds = $this->getDepartmentStudentIds($department);
                
                // Get multiple data points in parallel
                $baseStats = $this->getCachedBaseStatistics($department, $courses, $studentIds);
                $todayActivity = $this->getCachedTodayActivity($department, $courses, $studentIds);
                $healthData = $this->getCachedHealthData($department, $studentIds);
                $recentActivity = $this->getCachedRecentActivity($department, $courses, $studentIds);
                $chartsData = $this->getCachedChartsData($department, $courses, $studentIds);

                return [
                    'stats' => $baseStats,
                    'today' => $todayActivity,
                    'health' => $healthData,
                    'recent' => $recentActivity,
                    'charts' => $chartsData,
                    'system_status' => $this->getSystemStatus(),
                    'analytics' => $this->getDepartmentAnalytics($department, $studentIds),
                    'emergency_cases_list' => $this->getEmergencyCasesList($studentIds),
                ];
                
            } catch (\Exception $e) {
                Log::error("Error generating dashboard data for {$department}: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Get cached base statistics - FIXED VERSION
     */
    private function getCachedBaseStatistics($department, $courses, $studentIds)
    {
        try {
            // 1. EDUC Student Register - Total students in department
            $studentCount = User::where('role', User::ROLE_STUDENT)
                ->whereIn('course', $courses)
                ->count();
            
            Log::info("{$department} Student Count", ['count' => $studentCount, 'courses' => $courses]);
            
            // 2. Monthly Appointment Records - Appointments in current month
            $monthlyAppointments = Appointment::whereHas('user', function($query) use ($courses) {
                    $query->where('role', User::ROLE_STUDENT)
                          ->whereIn('course', $courses);
                })
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            
            Log::info("{$department} Monthly Appointments", ['count' => $monthlyAppointments]);
            
            // 3. Pending Medical Records - Using is_auto_created
            $pendingMedicalRecords = MedicalRecord::whereIn('user_id', $studentIds)
                ->where('is_auto_created', true)
                ->count();
            
            Log::info("{$department} Pending Medical Records", ['count' => $pendingMedicalRecords]);
            
            // 4. Monthly Consultation Records - Consultations in current month
            $monthlyConsultations = Consultation::whereHas('student', function($query) use ($courses) {
                    $query->where('role', User::ROLE_STUDENT)
                          ->whereIn('course', $courses);
                })
                ->whereMonth('consultation_date', now()->month)
                ->whereYear('consultation_date', now()->year)
                ->count();
            
            Log::info("{$department} Monthly Consultations", ['count' => $monthlyConsultations]);
            
            // Additional stats for other dashboard sections
            $symptomData = SymptomLog::whereIn('user_id', $studentIds)
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as weekly_cases'),
                    DB::raw('SUM(CASE WHEN is_emergency = true AND reviewed_by IS NULL THEN 1 ELSE 0 END) as emergency_cases')
                )
                ->addBinding(Carbon::now()->subWeek(), 'select')
                ->first();

            $stats = [
                // Main stat cards - THESE ARE THE 4 KEY METRICS
                'total_students' => $studentCount,
                'total_appointments' => $monthlyAppointments,
                'pending_medical_records' => $pendingMedicalRecords,
                'total_consultations' => $monthlyConsultations,
                
                // Additional stats
                'symptom_logs' => $symptomData->total ?? 0,
                'weekly_cases' => $symptomData->weekly_cases ?? 0,
                'emergency_cases' => $symptomData->emergency_cases ?? 0,
                'active_students_today' => User::where('role', User::ROLE_STUDENT)
                    ->whereIn('course', $courses)
                    ->whereDate('last_login_at', today())
                    ->count(),
            ];
            
            // Log final stats for verification
            Log::info("{$department} Final Stats", $stats);
            
            return $stats;
            
        } catch (\Exception $e) {
            Log::error("Error in getCachedBaseStatistics for {$department}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'total_students' => 0,
                'total_appointments' => 0,
                'pending_medical_records' => 0,
                'total_consultations' => 0,
                'symptom_logs' => 0,
                'weekly_cases' => 0,
                'emergency_cases' => 0,
                'active_students_today' => 0,
            ];
        }
    }

    /**
     * Comprehensive debug method to test all data sources
     */
    public function comprehensiveDebug($department = 'BSIT')
    {
        try {
            $courses = $this->getDepartmentCourses($department);
            $studentIds = $this->getDepartmentStudentIds($department);
            
            // Test 1: Raw student count
            $rawStudentCount = DB::table('users')
                ->where('role', User::ROLE_STUDENT)
                ->whereIn('course', $courses)
                ->count();
            
            // Test 2: Using User model
            $modelStudentCount = User::where('role', User::ROLE_STUDENT)
                ->whereIn('course', $courses)
                ->count();
            
            // Test 3: Get sample students
            $sampleStudents = User::where('role', User::ROLE_STUDENT)
                ->whereIn('course', $courses)
                ->select('id', 'first_name', 'last_name', 'student_id', 'course', 'year_level')
                ->limit(5)
                ->get();
            
            // Test 4: Monthly appointments
            $appointmentTest = Appointment::whereHas('user', function($query) use ($courses) {
                    $query->where('role', User::ROLE_STUDENT)
                          ->whereIn('course', $courses);
                })
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            
            // Test 5: Sample appointments
            $sampleAppointments = Appointment::whereHas('user', function($query) use ($courses) {
                    $query->where('role', User::ROLE_STUDENT)
                          ->whereIn('course', $courses);
                })
                ->with('user:id,first_name,last_name,course,student_id')
                ->latest()
                ->limit(3)
                ->get();
            
            // Test 6: Medical records
            $medicalRecordsTest = MedicalRecord::whereIn('user_id', $studentIds)
                ->where('is_auto_created', true)
                ->count();
            
            // Test 7: Sample medical records
            $sampleMedicalRecords = MedicalRecord::whereIn('user_id', $studentIds)
                ->with('user:id,first_name,last_name,student_id')
                ->latest()
                ->limit(3)
                ->get();
            
            // Test 8: Consultations
            $consultationsTest = Consultation::whereHas('student', function($query) use ($courses) {
                    $query->where('role', User::ROLE_STUDENT)
                          ->whereIn('course', $courses);
                })
                ->whereMonth('consultation_date', now()->month)
                ->whereYear('consultation_date', now()->year)
                ->count();
            
            // Test 9: Sample consultations
            $sampleConsultations = Consultation::whereHas('student', function($query) use ($courses) {
                    $query->where('role', User::ROLE_STUDENT)
                          ->whereIn('course', $courses);
                })
                ->with('student:id,first_name,last_name,student_id,course')
                ->latest()
                ->limit(3)
                ->get();
            
            // Test 10: Date verification
            $currentMonth = now()->month;
            $currentYear = now()->year;
            
            return response()->json([
                'success' => true,
                'department' => $department,
                'courses' => $courses,
                'student_ids_count' => count($studentIds),
                'date_info' => [
                    'current_month' => $currentMonth,
                    'current_year' => $currentYear,
                    'formatted' => now()->format('F Y')
                ],
                'tests' => [
                    'raw_student_count' => $rawStudentCount,
                    'model_student_count' => $modelStudentCount,
                    'monthly_appointments' => $appointmentTest,
                    'pending_medical_records' => $medicalRecordsTest,
                    'monthly_consultations' => $consultationsTest,
                ],
                'samples' => [
                    'students' => $sampleStudents,
                    'appointments' => $sampleAppointments,
                    'medical_records' => $sampleMedicalRecords,
                    'consultations' => $sampleConsultations,
                ],
                'database_info' => [
                    'total_users' => User::count(),
                    'total_students' => User::where('role', User::ROLE_STUDENT)->count(),
                    'total_appointments' => Appointment::count(),
                    'total_medical_records' => MedicalRecord::count(),
                    'total_consultations' => Consultation::count(),
                ],
                'cache_status' => [
                    'cache_key' => "dean_dashboard_{$department}_" . now()->format('Y-m-d_H'),
                    'is_cached' => Cache::has("dean_dashboard_{$department}_" . now()->format('Y-m-d_H'))
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("Comprehensive debug error for {$department}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * BSBA Program-Specific Chart Methods
     */

    /**
     * Get BSBA-MM Top Symptoms
     */
    public function getBsbaMmTopSymptoms()
    {
        try {
            $studentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSBA-MM')
                ->pluck('id')
                ->toArray();

            $symptoms = $this->getTopSymptomsForProgram($studentIds, 10);

            return response()->json([
                'success' => true,
                'labels' => array_keys($symptoms),
                'data' => array_values($symptoms),
                'top_illness' => !empty($symptoms) ? array_key_first($symptoms) : 'No data',
                'top_illness_count' => !empty($symptoms) ? reset($symptoms) : 0,
                'time_period' => 'Last 30 days'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting BSBA-MM top symptoms: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['No data'],
                'data' => [0],
                'top_illness' => 'No data',
                'top_illness_count' => 0,
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get BSBA-FM Top Symptoms
     */
    public function getBsbaFmTopSymptoms()
    {
        try {
            $studentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSBA-FM')
                ->pluck('id')
                ->toArray();

            $symptoms = $this->getTopSymptomsForProgram($studentIds, 10);

            return response()->json([
                'success' => true,
                'labels' => array_keys($symptoms),
                'data' => array_values($symptoms),
                'top_illness' => !empty($symptoms) ? array_key_first($symptoms) : 'No data',
                'top_illness_count' => !empty($symptoms) ? reset($symptoms) : 0,
                'time_period' => 'Last 30 days'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting BSBA-FM top symptoms: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['No data'],
                'data' => [0],
                'top_illness' => 'No data',
                'top_illness_count' => 0,
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get BSBA-MM Symptom Trends
     */
    public function getBsbaMmSymptomTrends()
    {
        try {
            $studentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSBA-MM')
                ->pluck('id')
                ->toArray();

            $months = [];
            $datasets = [];
            
            // Get top 5 symptoms for trend analysis
            $topSymptoms = $this->getTopSymptomsForProgram($studentIds, 5);

            foreach (array_keys($topSymptoms) as $index => $symptom) {
                $symptomData = [];
                
                for ($i = 5; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    if ($i === 5) {
                        $months[] = $month->format('M Y');
                    }
                    
                    $count = SymptomLog::whereIn('user_id', $studentIds)
                        ->whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->whereNotNull('symptoms')
                        ->where(function($query) use ($symptom) {
                            $query->where('symptoms', 'like', '%"' . $symptom . '"%')
                                  ->orWhere('symptoms', 'like', "%" . $symptom . "%");
                        })
                        ->count();
                        
                    $symptomData[] = $count;
                }
                
                $datasets[] = [
                    'label' => $symptom,
                    'data' => $symptomData
                ];
            }

            return response()->json([
                'success' => true,
                'labels' => $months,
                'datasets' => $datasets,
                'top_illness' => !empty($topSymptoms) ? array_key_first($topSymptoms) : 'No data',
                'top_illness_trend' => !empty($datasets) ? $datasets[0]['data'] : [],
                'time_period' => 'Last 6 months'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting BSBA-MM symptom trends: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'datasets' => [],
                'top_illness' => 'No data',
                'top_illness_trend' => [],
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get BSBA-FM Symptom Trends
     */
    public function getBsbaFmSymptomTrends()
    {
        try {
            $studentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSBA-FM')
                ->pluck('id')
                ->toArray();

            $months = [];
            $datasets = [];
            
            // Get top 5 symptoms for trend analysis
            $topSymptoms = $this->getTopSymptomsForProgram($studentIds, 5);

            foreach (array_keys($topSymptoms) as $index => $symptom) {
                $symptomData = [];
                
                for ($i = 5; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    if ($i === 5) {
                        $months[] = $month->format('M Y');
                    }
                    
                    $count = SymptomLog::whereIn('user_id', $studentIds)
                        ->whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->whereNotNull('symptoms')
                        ->where(function($query) use ($symptom) {
                            $query->where('symptoms', 'like', '%"' . $symptom . '"%')
                                  ->orWhere('symptoms', 'like', "%" . $symptom . "%");
                        })
                        ->count();
                        
                    $symptomData[] = $count;
                }
                
                $datasets[] = [
                    'label' => $symptom,
                    'data' => $symptomData
                ];
            }

            return response()->json([
                'success' => true,
                'labels' => $months,
                'datasets' => $datasets,
                'top_illness' => !empty($topSymptoms) ? array_key_first($topSymptoms) : 'No data',
                'top_illness_trend' => !empty($datasets) ? $datasets[0]['data'] : [],
                'time_period' => 'Last 6 months'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting BSBA-FM symptom trends: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'datasets' => [],
                'top_illness' => 'No data',
                'top_illness_trend' => [],
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get BSBA-MM Year Level Distribution
     */
    public function getBsbaMmYearLevel()
    {
        try {
            $yearLevels = ['1st year', '2nd year', '3rd year', '4th year'];
            $data = [];
            $topIllnessByYear = [];

            foreach ($yearLevels as $year) {
                $studentIds = User::where('role', User::ROLE_STUDENT)
                    ->where('course', 'BSBA-MM')
                    ->where('year_level', $year)
                    ->pluck('id')
                    ->toArray();

                $count = SymptomLog::whereIn('user_id', $studentIds)
                    ->where('created_at', '>=', Carbon::now()->subMonths(6))
                    ->count();
                    
                $data[] = $count;
                
                // Get top illness for this year level
                $topSymptoms = $this->getTopSymptomsForProgram($studentIds, 1);
                $topIllnessByYear[$year] = !empty($topSymptoms) ? array_key_first($topSymptoms) : 'None';
            }

            return response()->json([
                'success' => true,
                'labels' => array_map('ucfirst', $yearLevels),
                'data' => $data,
                'top_illness_by_year' => $topIllnessByYear,
                'highest_cases_year' => !empty($data) ? array_map('ucfirst', $yearLevels)[array_search(max($data), $data)] : 'No data',
                'time_period' => 'Last 6 months'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting BSBA-MM year level data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['1st Year', '2nd Year', '3rd Year', '4th Year'],
                'data' => [0, 0, 0, 0],
                'top_illness_by_year' => [],
                'highest_cases_year' => 'No data',
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get BSBA-FM Year Level Distribution
     */
    public function getBsbaFmYearLevel()
    {
        try {
            $yearLevels = ['1st year', '2nd year', '3rd year', '4th year'];
            $data = [];
            $topIllnessByYear = [];

            foreach ($yearLevels as $year) {
                $studentIds = User::where('role', User::ROLE_STUDENT)
                    ->where('course', 'BSBA-FM')
                    ->where('year_level', $year)
                    ->pluck('id')
                    ->toArray();

                $count = SymptomLog::whereIn('user_id', $studentIds)
                    ->where('created_at', '>=', Carbon::now()->subMonths(6))
                    ->count();
                    
                $data[] = $count;
                
                // Get top illness for this year level
                $topSymptoms = $this->getTopSymptomsForProgram($studentIds, 1);
                $topIllnessByYear[$year] = !empty($topSymptoms) ? array_key_first($topSymptoms) : 'None';
            }

            return response()->json([
                'success' => true,
                'labels' => array_map('ucfirst', $yearLevels),
                'data' => $data,
                'top_illness_by_year' => $topIllnessByYear,
                'highest_cases_year' => !empty($data) ? array_map('ucfirst', $yearLevels)[array_search(max($data), $data)] : 'No data',
                'time_period' => 'Last 6 months'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting BSBA-FM year level data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['1st Year', '2nd Year', '3rd Year', '4th Year'],
                'data' => [0, 0, 0, 0],
                'top_illness_by_year' => [],
                'highest_cases_year' => 'No data',
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * BSBA Combined Health Analytics Chart Methods
     */

    /**
     * Get Combined Year Level Data for BSBA (Both MM and FM)
     */
    public function getCombinedYearLevel()
    {
        try {
            $yearLevels = ['1st year', '2nd year', '3rd year', '4th year'];
            $bsbaMmData = [];
            $bsbaFmData = [];
            $topIllnessByYearMm = [];
            $topIllnessByYearFm = [];

            foreach ($yearLevels as $year) {
                // Get BSBA-MM students for this year level
                $mmStudents = User::where('role', User::ROLE_STUDENT)
                    ->where('course', 'BSBA-MM')
                    ->where('year_level', $year)
                    ->pluck('id')
                    ->toArray();
                    
                // Count their symptom logs
                $mmCount = SymptomLog::whereIn('user_id', $mmStudents)
                    ->where('created_at', '>=', Carbon::now()->subMonths(6))
                    ->count();
                $bsbaMmData[] = $mmCount;

                // Get top illness for BSBA-MM this year level
                $mmTopSymptoms = $this->getTopSymptomsForProgram($mmStudents, 1);
                $topIllnessByYearMm[$year] = !empty($mmTopSymptoms) ? array_key_first($mmTopSymptoms) : 'None';

                // Get BSBA-FM students for this year level
                $fmStudents = User::where('role', User::ROLE_STUDENT)
                    ->where('course', 'BSBA-FM')
                    ->where('year_level', $year)
                    ->pluck('id')
                    ->toArray();
                    
                // Count their symptom logs
                $fmCount = SymptomLog::whereIn('user_id', $fmStudents)
                    ->where('created_at', '>=', Carbon::now()->subMonths(6))
                    ->count();
                $bsbaFmData[] = $fmCount;

                // Get top illness for BSBA-FM this year level
                $fmTopSymptoms = $this->getTopSymptomsForProgram($fmStudents, 1);
                $topIllnessByYearFm[$year] = !empty($fmTopSymptoms) ? array_key_first($fmTopSymptoms) : 'None';
            }

            // Format year levels for display
            $formattedYearLevels = array_map(function($year) {
                return ucwords(str_replace('_', ' ', $year));
            }, $yearLevels);

            return response()->json([
                'success' => true,
                'labels' => $formattedYearLevels,
                'bsba_mm' => $bsbaMmData,
                'bsba_fm' => $bsbaFmData,
                'top_illness_mm_by_year' => $topIllnessByYearMm,
                'top_illness_fm_by_year' => $topIllnessByYearFm,
                'highest_cases_mm' => !empty($bsbaMmData) ? $formattedYearLevels[array_search(max($bsbaMmData), $bsbaMmData)] : 'No data',
                'highest_cases_fm' => !empty($bsbaFmData) ? $formattedYearLevels[array_search(max($bsbaFmData), $bsbaFmData)] : 'No data',
                'time_period' => 'Last 6 months'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting combined year level data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['1st Year', '2nd Year', '3rd Year', '4th Year'],
                'bsba_mm' => [0, 0, 0, 0],
                'bsba_fm' => [0, 0, 0, 0],
                'top_illness_mm_by_year' => [],
                'top_illness_fm_by_year' => [],
                'highest_cases_mm' => 'No data',
                'highest_cases_fm' => 'No data',
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get Combined Symptom Overview for BSBA
     */
    public function getCombinedSymptomOverview()
    {
        try {
            $bsbaMmStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSBA-MM')
                ->pluck('id')
                ->toArray();
                
            $bsbaFmStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSBA-FM')
                ->pluck('id')
                ->toArray();

            // Get top symptoms for both programs
            $mmSymptoms = $this->getTopSymptomsForProgram($bsbaMmStudentIds, 8);
            $fmSymptoms = $this->getTopSymptomsForProgram($bsbaFmStudentIds, 8);

            // Combine and get unique symptoms
            $allSymptoms = array_unique(array_merge(array_keys($mmSymptoms), array_keys($fmSymptoms)));
            $allSymptoms = array_slice($allSymptoms, 0, 10); // Limit to top 10

            $mmData = [];
            $fmData = [];
            
            foreach ($allSymptoms as $symptom) {
                $mmData[] = $mmSymptoms[$symptom] ?? 0;
                $fmData[] = $fmSymptoms[$symptom] ?? 0;
            }

            return response()->json([
                'success' => true,
                'labels' => $allSymptoms,
                'bsba_mm' => $mmData,
                'bsba_fm' => $fmData,
                'top_illness_mm' => !empty($mmSymptoms) ? array_key_first($mmSymptoms) : 'No data',
                'top_illness_fm' => !empty($fmSymptoms) ? array_key_first($fmSymptoms) : 'No data',
                'top_illness_mm_count' => !empty($mmSymptoms) ? reset($mmSymptoms) : 0,
                'top_illness_fm_count' => !empty($fmSymptoms) ? reset($fmSymptoms) : 0,
                'time_period' => 'Last 30 days'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting combined symptom overview: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['No data available'],
                'bsba_mm' => [0],
                'bsba_fm' => [0],
                'top_illness_mm' => 'No data',
                'top_illness_fm' => 'No data',
                'top_illness_mm_count' => 0,
                'top_illness_fm_count' => 0,
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get Top Symptoms This Month for BSBA
     */
    public function getTopSymptomsMonth()
    {
        try {
            $bsbaMmStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSBA-MM')
                ->pluck('id')
                ->toArray();
                
            $bsbaFmStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSBA-FM')
                ->pluck('id')
                ->toArray();

            // Get top 5 symptoms for this month
            $mmSymptoms = $this->getTopSymptomsForProgram($bsbaMmStudentIds, 5);
            $fmSymptoms = $this->getTopSymptomsForProgram($bsbaFmStudentIds, 5);

            // Use the same symptoms for both programs for consistent comparison
            $allSymptoms = array_unique(array_merge(array_keys($mmSymptoms), array_keys($fmSymptoms)));
            $allSymptoms = array_slice($allSymptoms, 0, 5); // Limit to top 5

            $mmData = [];
            $fmData = [];
            
            foreach ($allSymptoms as $symptom) {
                $mmData[] = $mmSymptoms[$symptom] ?? 0;
                $fmData[] = $fmSymptoms[$symptom] ?? 0;
            }

            return response()->json([
                'success' => true,
                'labels' => $allSymptoms,
                'bsba_mm' => $mmData,
                'bsba_fm' => $fmData,
                'top_illness_mm' => !empty($mmSymptoms) ? array_key_first($mmSymptoms) : 'No data',
                'top_illness_fm' => !empty($fmSymptoms) ? array_key_first($fmSymptoms) : 'No data',
                'time_period' => 'This month'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting top symptoms this month: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['No data'],
                'bsba_mm' => [0],
                'bsba_fm' => [0],
                'top_illness_mm' => 'No data',
                'top_illness_fm' => 'No data',
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get Symptom Trends Last 6 Months for BSBA - FIXED VERSION
     */
    public function getSymptomTrends6Months()
    {
        try {
            $bsbaMmStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSBA-MM')
                ->pluck('id')
                ->toArray();
                
            $bsbaFmStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSBA-FM')
                ->pluck('id')
                ->toArray();

            if (empty($bsbaMmStudentIds) && empty($bsbaFmStudentIds)) {
                Log::warning('No BSBA students found for symptom trends');
                return response()->json([
                    'success' => false,
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    'datasets' => [],
                    'message' => 'No BSBA students found'
                ]);
            }

            $allStudentIds = array_merge($bsbaMmStudentIds, $bsbaFmStudentIds);
            
            // Get top 5 symptoms from LAST 6 MONTHS
            $logs = SymptomLog::whereIn('user_id', $allStudentIds)
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->whereNotNull('symptoms')
                ->get();

            if ($logs->isEmpty()) {
                Log::info('No symptom logs found for BSBA students in last 6 months');
                return response()->json([
                    'success' => false,
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    'datasets' => [],
                    'message' => 'No symptom data available'
                ]);
            }

            // Process all symptoms to find top 5
            $symptomCounts = [];
            foreach ($logs as $log) {
                $symptoms = is_array($log->symptoms) ? $log->symptoms : json_decode($log->symptoms, true) ?? [];
                foreach ($symptoms as $symptom) {
                    if (is_string($symptom) && !empty(trim($symptom))) {
                        $key = ucfirst(strtolower(trim($symptom)));
                        $symptomCounts[$key] = ($symptomCounts[$key] ?? 0) + 1;
                    }
                }
            }

            if (empty($symptomCounts)) {
                return response()->json([
                    'success' => false,
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    'datasets' => [],
                    'message' => 'No symptoms to display'
                ]);
            }

            arsort($symptomCounts);
            $top5Symptoms = array_slice(array_keys($symptomCounts), 0, 5);

            $months = [];
            $datasets = [];

            // Build monthly data for each top symptom
            foreach ($top5Symptoms as $index => $symptom) {
                $symptomData = [];
                
                for ($i = 5; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    
                    // Only set months array once
                    if ($index === 0) {
                        $months[] = $month->format('M Y');
                    }
                    
                    // Count occurrences for this symptom in this month
                    $count = SymptomLog::whereIn('user_id', $allStudentIds)
                        ->whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->whereNotNull('symptoms')
                        ->where(function($query) use ($symptom) {
                            $query->where('symptoms', 'like', '%' . $symptom . '%')
                                  ->orWhere('symptoms', 'like', '%"' . $symptom . '"%');
                        })
                        ->count();
                        
                    $symptomData[] = $count;
                }
                
                $color = $this->getTrendColor($index);
                
                $datasets[] = [
                    'label' => $symptom,
                    'data' => $symptomData,
                    'borderColor' => $color['main'],
                    'backgroundColor' => $color['light'],
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'fill' => true
                ];
            }

            Log::info('Symptom Trends Generated', [
                'top_symptoms' => $top5Symptoms,
                'months' => $months,
                'dataset_count' => count($datasets)
            ]);

            return response()->json([
                'success' => true,
                'labels' => $months,
                'datasets' => $datasets,
                'top_illness' => !empty($top5Symptoms) ? $top5Symptoms[0] : 'No data',
                'top_illness_trend' => !empty($datasets) ? $datasets[0]['data'] : [],
                'time_period' => 'Last 6 months',
                'total_symptoms_found' => count($symptomCounts)
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting symptom trends: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'datasets' => [],
                'top_illness' => 'No data',
                'top_illness_trend' => [],
                'message' => 'Error loading data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * EDUC Program-Specific Chart Methods - COMPLETED
     */

    /**
     * Get BSED Top Symptoms
     */
    public function getEducBsedTopSymptoms()
    {
        try {
            $studentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSED')
                ->pluck('id')
                ->toArray();

            $symptoms = $this->getTopSymptomsForProgram($studentIds, 10);

            return response()->json([
                'success' => true,
                'labels' => array_keys($symptoms),
                'data' => array_values($symptoms),
                'top_illness' => !empty($symptoms) ? array_key_first($symptoms) : 'No data',
                'top_illness_count' => !empty($symptoms) ? reset($symptoms) : 0,
                'time_period' => 'Last 30 days'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting BSED top symptoms: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['No data'],
                'data' => [0],
                'top_illness' => 'No data',
                'top_illness_count' => 0,
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get BEED Top Symptoms
     */
    public function getEducBeedTopSymptoms()
    {
        try {
            $studentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BEED')
                ->pluck('id')
                ->toArray();

            $symptoms = $this->getTopSymptomsForProgram($studentIds, 10);

            return response()->json([
                'success' => true,
                'labels' => array_keys($symptoms),
                'data' => array_values($symptoms),
                'top_illness' => !empty($symptoms) ? array_key_first($symptoms) : 'No data',
                'top_illness_count' => !empty($symptoms) ? reset($symptoms) : 0,
                'time_period' => 'Last 30 days'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting BEED top symptoms: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['No data'],
                'data' => [0],
                'top_illness' => 'No data',
                'top_illness_count' => 0,
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get BSED Symptom Trends
     */
    public function getEducBsedSymptomTrends()
    {
        try {
            $studentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSED')
                ->pluck('id')
                ->toArray();

            $months = [];
            $datasets = [];
            
            // Get top 5 symptoms for trend analysis
            $topSymptoms = $this->getTopSymptomsForProgram($studentIds, 5);

            foreach (array_keys($topSymptoms) as $index => $symptom) {
                $symptomData = [];
                
                for ($i = 5; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    if ($i === 5) {
                        $months[] = $month->format('M Y');
                    }
                    
                    $count = SymptomLog::whereIn('user_id', $studentIds)
                        ->whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->whereNotNull('symptoms')
                        ->where(function($query) use ($symptom) {
                            $query->where('symptoms', 'like', '%' . $symptom . '%')
                                  ->orWhere('symptoms', 'like', '%"' . $symptom . '"%');
                        })
                        ->count();
                        
                    $symptomData[] = $count;
                }
                
                $color = $this->getTrendColor($index);
                
                $datasets[] = [
                    'label' => $symptom,
                    'data' => $symptomData,
                    'borderColor' => $color['main'],
                    'backgroundColor' => $color['light'],
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'fill' => true
                ];
            }

            return response()->json([
                'success' => true,
                'labels' => $months,
                'datasets' => $datasets,
                'top_illness' => !empty($topSymptoms) ? array_key_first($topSymptoms) : 'No data',
                'top_illness_trend' => !empty($datasets) ? $datasets[0]['data'] : [],
                'time_period' => 'Last 6 months'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting BSED symptom trends: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'datasets' => [],
                'top_illness' => 'No data',
                'top_illness_trend' => [],
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get BEED Symptom Trends
     */
    public function getEducBeedSymptomTrends()
    {
        try {
            $studentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BEED')
                ->pluck('id')
                ->toArray();

            $months = [];
            $datasets = [];
            
            // Get top 5 symptoms for trend analysis
            $topSymptoms = $this->getTopSymptomsForProgram($studentIds, 5);

            foreach (array_keys($topSymptoms) as $index => $symptom) {
                $symptomData = [];
                
                for ($i = 5; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    if ($i === 5) {
                        $months[] = $month->format('M Y');
                    }
                    
                    $count = SymptomLog::whereIn('user_id', $studentIds)
                        ->whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->whereNotNull('symptoms')
                        ->where(function($query) use ($symptom) {
                            $query->where('symptoms', 'like', '%' . $symptom . '%')
                                  ->orWhere('symptoms', 'like', '%"' . $symptom . '"%');
                        })
                        ->count();
                        
                    $symptomData[] = $count;
                }
                
                $color = $this->getTrendColor($index);
                
                $datasets[] = [
                    'label' => $symptom,
                    'data' => $symptomData,
                    'borderColor' => $color['main'],
                    'backgroundColor' => $color['light'],
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'fill' => true
                ];
            }

            return response()->json([
                'success' => true,
                'labels' => $months,
                'datasets' => $datasets,
                'top_illness' => !empty($topSymptoms) ? array_key_first($topSymptoms) : 'No data',
                'top_illness_trend' => !empty($datasets) ? $datasets[0]['data'] : [],
                'time_period' => 'Last 6 months'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting BEED symptom trends: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'datasets' => [],
                'top_illness' => 'No data',
                'top_illness_trend' => [],
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get BSED Year Level Distribution
     */
    public function getEducBsedYearLevel()
    {
        try {
            $yearLevels = ['1st year', '2nd year', '3rd year', '4th year'];
            $data = [];
            $topIllnessByYear = [];

            foreach ($yearLevels as $year) {
                $studentIds = User::where('role', User::ROLE_STUDENT)
                    ->where('course', 'BSED')
                    ->where('year_level', $year)
                    ->pluck('id')
                    ->toArray();

                $count = SymptomLog::whereIn('user_id', $studentIds)
                    ->where('created_at', '>=', Carbon::now()->subMonths(6))
                    ->count();
                    
                $data[] = $count;
                
                // Get top illness for this year level
                $topSymptoms = $this->getTopSymptomsForProgram($studentIds, 1);
                $topIllnessByYear[$year] = !empty($topSymptoms) ? array_key_first($topSymptoms) : 'None';
            }

            return response()->json([
                'success' => true,
                'labels' => array_map('ucfirst', $yearLevels),
                'data' => $data,
                'top_illness_by_year' => $topIllnessByYear,
                'highest_cases_year' => !empty($data) ? array_map('ucfirst', $yearLevels)[array_search(max($data), $data)] : 'No data',
                'time_period' => 'Last 6 months'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting BSED year level data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['1st Year', '2nd Year', '3rd Year', '4th Year'],
                'data' => [0, 0, 0, 0],
                'top_illness_by_year' => [],
                'highest_cases_year' => 'No data',
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get BEED Year Level Distribution
     */
    public function getEducBeedYearLevel()
    {
        try {
            $yearLevels = ['1st year', '2nd year', '3rd year', '4th year'];
            $data = [];
            $topIllnessByYear = [];

            foreach ($yearLevels as $year) {
                $studentIds = User::where('role', User::ROLE_STUDENT)
                    ->where('course', 'BEED')
                    ->where('year_level', $year)
                    ->pluck('id')
                    ->toArray();

                $count = SymptomLog::whereIn('user_id', $studentIds)
                    ->where('created_at', '>=', Carbon::now()->subMonths(6))
                    ->count();
                    
                $data[] = $count;
                
                // Get top illness for this year level
                $topSymptoms = $this->getTopSymptomsForProgram($studentIds, 1);
                $topIllnessByYear[$year] = !empty($topSymptoms) ? array_key_first($topSymptoms) : 'None';
            }

            return response()->json([
                'success' => true,
                'labels' => array_map('ucfirst', $yearLevels),
                'data' => $data,
                'top_illness_by_year' => $topIllnessByYear,
                'highest_cases_year' => !empty($data) ? array_map('ucfirst', $yearLevels)[array_search(max($data), $data)] : 'No data',
                'time_period' => 'Last 6 months'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting BEED year level data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['1st Year', '2nd Year', '3rd Year', '4th Year'],
                'data' => [0, 0, 0, 0],
                'top_illness_by_year' => [],
                'highest_cases_year' => 'No data',
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get EDUC Combined Year Level - COMPLETED VERSION
     */
    public function getEducCombinedYearLevel()
    {
        try {
            $yearLevels = ['1st year', '2nd year', '3rd year', '4th year'];
            $bsedData = [];
            $beedData = [];
            $topIllnessByYearBsed = [];
            $topIllnessByYearBeed = [];

            foreach ($yearLevels as $year) {
                // BSED students for this year level
                $bsedStudentIds = User::where('role', User::ROLE_STUDENT)
                    ->where('course', 'BSED')
                    ->where('year_level', $year)
                    ->pluck('id')
                    ->toArray();

                $bsedCount = SymptomLog::whereIn('user_id', $bsedStudentIds)
                    ->where('created_at', '>=', Carbon::now()->subMonths(6))
                    ->count();
                $bsedData[] = $bsedCount;

                // Get top illness for BSED this year level
                $bsedTopSymptoms = $this->getTopSymptomsForProgram($bsedStudentIds, 1);
                $topIllnessByYearBsed[$year] = !empty($bsedTopSymptoms) ? array_key_first($bsedTopSymptoms) : 'None';

                // BEED students for this year level
                $beedStudentIds = User::where('role', User::ROLE_STUDENT)
                    ->where('course', 'BEED')
                    ->where('year_level', $year)
                    ->pluck('id')
                    ->toArray();

                $beedCount = SymptomLog::whereIn('user_id', $beedStudentIds)
                    ->where('created_at', '>=', Carbon::now()->subMonths(6))
                    ->count();
                $beedData[] = $beedCount;

                // Get top illness for BEED this year level
                $beedTopSymptoms = $this->getTopSymptomsForProgram($beedStudentIds, 1);
                $topIllnessByYearBeed[$year] = !empty($beedTopSymptoms) ? array_key_first($beedTopSymptoms) : 'None';
            }

            // Format year levels for display
            $formattedYearLevels = array_map('ucfirst', $yearLevels);

            return response()->json([
                'success' => true,
                'labels' => $formattedYearLevels,
                'educ_bsed' => $bsedData,
                'educ_beed' => $beedData,
                'top_illness_bsed_by_year' => $topIllnessByYearBsed,
                'top_illness_beed_by_year' => $topIllnessByYearBeed,
                'highest_cases_bsed' => !empty($bsedData) ? $formattedYearLevels[array_search(max($bsedData), $bsedData)] : 'No data',
                'highest_cases_beed' => !empty($beedData) ? $formattedYearLevels[array_search(max($beedData), $beedData)] : 'No data',
                'time_period' => 'Last 6 months'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting EDUC combined year level data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['1st Year', '2nd Year', '3rd Year', '4th Year'],
                'educ_bsed' => [0, 0, 0, 0],
                'educ_beed' => [0, 0, 0, 0],
                'top_illness_bsed_by_year' => [],
                'top_illness_beed_by_year' => [],
                'highest_cases_bsed' => 'No data',
                'highest_cases_beed' => 'No data',
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get Combined Symptom Overview for EDUC - COMPLETED VERSION
     */
    public function getEducCombinedSymptomOverview()
    {
        try {
            $bsedStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSED')
                ->pluck('id')
                ->toArray();
                
            $beedStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BEED')
                ->pluck('id')
                ->toArray();

            // Get top symptoms for both programs
            $bsedSymptoms = $this->getTopSymptomsForProgram($bsedStudentIds, 8);
            $beedSymptoms = $this->getTopSymptomsForProgram($beedStudentIds, 8);

            // Combine and get unique symptoms
            $allSymptoms = array_unique(array_merge(array_keys($bsedSymptoms), array_keys($beedSymptoms)));
            $allSymptoms = array_slice($allSymptoms, 0, 10); // Limit to top 10

            $bsedData = [];
            $beedData = [];
            
            foreach ($allSymptoms as $symptom) {
                $bsedData[] = $bsedSymptoms[$symptom] ?? 0;
                $beedData[] = $beedSymptoms[$symptom] ?? 0;
            }

            return response()->json([
                'success' => true,
                'labels' => $allSymptoms,
                'educ_bsed' => $bsedData,
                'educ_beed' => $beedData,
                'top_illness_bsed' => !empty($bsedSymptoms) ? array_key_first($bsedSymptoms) : 'No data',
                'top_illness_beed' => !empty($beedSymptoms) ? array_key_first($beedSymptoms) : 'No data',
                'top_illness_bsed_count' => !empty($bsedSymptoms) ? reset($bsedSymptoms) : 0,
                'top_illness_beed_count' => !empty($beedSymptoms) ? reset($beedSymptoms) : 0,
                'time_period' => 'Last 30 days'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting EDUC combined symptom overview: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['No data available'],
                'educ_bsed' => [0],
                'educ_beed' => [0],
                'top_illness_bsed' => 'No data',
                'top_illness_beed' => 'No data',
                'top_illness_bsed_count' => 0,
                'top_illness_beed_count' => 0,
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get Top Symptoms This Month for EDUC - COMPLETED VERSION
     */
    public function getEducTopSymptomsMonth()
    {
        try {
            $bsedStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSED')
                ->pluck('id')
                ->toArray();
                
            $beedStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BEED')
                ->pluck('id')
                ->toArray();

            // Get top 5 symptoms for this month
            $bsedSymptoms = $this->getTopSymptomsForProgram($bsedStudentIds, 5);
            $beedSymptoms = $this->getTopSymptomsForProgram($beedStudentIds, 5);

            // Use the same symptoms for both programs for consistent comparison
            $allSymptoms = array_unique(array_merge(array_keys($bsedSymptoms), array_keys($beedSymptoms)));
            $allSymptoms = array_slice($allSymptoms, 0, 5); // Limit to top 5

            $bsedData = [];
            $beedData = [];
            
            foreach ($allSymptoms as $symptom) {
                $bsedData[] = $bsedSymptoms[$symptom] ?? 0;
                $beedData[] = $beedSymptoms[$symptom] ?? 0;
            }

            return response()->json([
                'success' => true,
                'labels' => $allSymptoms,
                'educ_bsed' => $bsedData,
                'educ_beed' => $beedData,
                'top_illness_bsed' => !empty($bsedSymptoms) ? array_key_first($bsedSymptoms) : 'No data',
                'top_illness_beed' => !empty($beedSymptoms) ? array_key_first($beedSymptoms) : 'No data',
                'time_period' => 'This month'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting EDUC top symptoms this month: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'labels' => ['No data'],
                'educ_bsed' => [0],
                'educ_beed' => [0],
                'top_illness_bsed' => 'No data',
                'top_illness_beed' => 'No data',
                'message' => 'Error loading data'
            ]);
        }
    }

    /**
     * Get Symptom Trends Last 6 Months for EDUC - COMPLETED VERSION
     */
    public function getEducSymptomTrends6Months()
    {
        try {
            $bsedStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSED')
                ->pluck('id')
                ->toArray();
                
            $beedStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BEED')
                ->pluck('id')
                ->toArray();

            if (empty($bsedStudentIds) && empty($beedStudentIds)) {
                Log::warning('No EDUC students found for symptom trends');
                return response()->json([
                    'success' => false,
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    'datasets' => [],
                    'message' => 'No EDUC students found'
                ]);
            }

            $allStudentIds = array_merge($bsedStudentIds, $beedStudentIds);
            
            // Get top 5 symptoms from LAST 6 MONTHS
            $logs = SymptomLog::whereIn('user_id', $allStudentIds)
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->whereNotNull('symptoms')
                ->get();

            if ($logs->isEmpty()) {
                Log::info('No symptom logs found for EDUC students in last 6 months');
                return response()->json([
                    'success' => false,
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    'datasets' => [],
                    'message' => 'No symptom data available'
                ]);
            }

            // Process all symptoms to find top 5
            $symptomCounts = [];
            foreach ($logs as $log) {
                $symptoms = is_array($log->symptoms) ? $log->symptoms : json_decode($log->symptoms, true) ?? [];
                foreach ($symptoms as $symptom) {
                    if (is_string($symptom) && !empty(trim($symptom))) {
                        $key = ucfirst(strtolower(trim($symptom)));
                        $symptomCounts[$key] = ($symptomCounts[$key] ?? 0) + 1;
                    }
                }
            }

            if (empty($symptomCounts)) {
                return response()->json([
                    'success' => false,
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    'datasets' => [],
                    'message' => 'No symptoms to display'
                ]);
            }

            arsort($symptomCounts);
            $top5Symptoms = array_slice(array_keys($symptomCounts), 0, 5);

            $months = [];
            $datasets = [];

            // Build monthly data for each top symptom
            foreach ($top5Symptoms as $index => $symptom) {
                $symptomData = [];
                
                for ($i = 5; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i);
                    
                    // Only set months array once
                    if ($index === 0) {
                        $months[] = $month->format('M Y');
                    }
                    
                    // Count occurrences for this symptom in this month
                    $count = SymptomLog::whereIn('user_id', $allStudentIds)
                        ->whereMonth('created_at', $month->month)
                        ->whereYear('created_at', $month->year)
                        ->whereNotNull('symptoms')
                        ->where(function($query) use ($symptom) {
                            $query->where('symptoms', 'like', '%' . $symptom . '%')
                                  ->orWhere('symptoms', 'like', '%"' . $symptom . '"%');
                        })
                        ->count();
                        
                    $symptomData[] = $count;
                }
                
                $color = $this->getTrendColor($index);
                
                $datasets[] = [
                    'label' => $symptom,
                    'data' => $symptomData,
                    'borderColor' => $color['main'],
                    'backgroundColor' => $color['light'],
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'fill' => true
                ];
            }

            Log::info('EDUC Symptom Trends Generated', [
                'top_symptoms' => $top5Symptoms,
                'months' => $months,
                'dataset_count' => count($datasets)
            ]);

            return response()->json([
                'success' => true,
                'labels' => $months,
                'datasets' => $datasets,
                'top_illness' => !empty($top5Symptoms) ? $top5Symptoms[0] : 'No data',
                'top_illness_trend' => !empty($datasets) ? $datasets[0]['data'] : [],
                'time_period' => 'Last 6 months',
                'total_symptoms_found' => count($symptomCounts)
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting EDUC symptom trends: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'datasets' => [],
                'top_illness' => 'No data',
                'top_illness_trend' => [],
                'message' => 'Error loading data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * EDUC-Specific Analytics Methods - COMPLETED
     */

    /**
     * Get EDUC Program Comparison
     */
    public function getEducProgramComparison()
    {
        try {
            $bsedStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSED')
                ->pluck('id')
                ->toArray();
                
            $beedStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BEED')
                ->pluck('id')
                ->toArray();

            // Get various metrics for comparison
            $comparisonData = [
                'total_students' => [
                    'BSED' => count($bsedStudentIds),
                    'BEED' => count($beedStudentIds)
                ],
                'symptom_reports_30_days' => [
                    'BSED' => SymptomLog::whereIn('user_id', $bsedStudentIds)
                        ->where('created_at', '>=', Carbon::now()->subDays(30))
                        ->count(),
                    'BEED' => SymptomLog::whereIn('user_id', $beedStudentIds)
                        ->where('created_at', '>=', Carbon::now()->subDays(30))
                        ->count()
                ],
                'emergency_cases' => [
                    'BSED' => SymptomLog::whereIn('user_id', $bsedStudentIds)
                        ->where('is_emergency', true)
                        ->where('created_at', '>=', Carbon::now()->subMonths(3))
                        ->count(),
                    'BEED' => SymptomLog::whereIn('user_id', $beedStudentIds)
                        ->where('is_emergency', true)
                        ->where('created_at', '>=', Carbon::now()->subMonths(3))
                        ->count()
                ],
                'consultation_rate' => [
                    'BSED' => Consultation::whereIn('student_id', $bsedStudentIds)
                        ->where('consultation_date', '>=', Carbon::now()->subMonths(3))
                        ->count(),
                    'BEED' => Consultation::whereIn('student_id', $beedStudentIds)
                        ->where('consultation_date', '>=', Carbon::now()->subMonths(3))
                        ->count()
                ]
            ];

            return response()->json([
                'success' => true,
                'comparison_data' => $comparisonData,
                'time_period' => 'Various periods as noted'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting EDUC program comparison: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'comparison_data' => [],
                'message' => 'Error loading comparison data'
            ]);
        }
    }

    /**
     * Get EDUC Health Risk Assessment
     */
    public function getEducHealthRiskAssessment()
    {
        try {
            $bsedStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSED')
                ->pluck('id')
                ->toArray();
                
            $beedStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BEED')
                ->pluck('id')
                ->toArray();

            $allStudentIds = array_merge($bsedStudentIds, $beedStudentIds);

            // Analyze health risks
            $riskData = [
                'high_risk_students' => SymptomLog::whereIn('user_id', $allStudentIds)
                    ->where('is_emergency', true)
                    ->where('created_at', '>=', Carbon::now()->subMonths(3))
                    ->distinct('user_id')
                    ->count('user_id'),
                'frequent_visitors' => SymptomLog::whereIn('user_id', $allStudentIds)
                    ->where('created_at', '>=', Carbon::now()->subMonths(3))
                    ->select('user_id', DB::raw('COUNT(*) as visit_count'))
                    ->groupBy('user_id')
                    ->having('visit_count', '>=', 3)
                    ->count(),
                'chronic_conditions' => MedicalRecord::whereIn('user_id', $allStudentIds)
                    ->where(function($query) {
                        $query->whereNotNull('chronic_conditions')
                              ->where('chronic_conditions', '!=', '')
                              ->where('chronic_conditions', '!=', 'None');
                    })
                    ->count(),
                'mental_health_concerns' => SymptomLog::whereIn('user_id', $allStudentIds)
                    ->where(function($query) {
                        $query->where('symptoms', 'like', '%anxiety%')
                              ->orWhere('symptoms', 'like', '%stress%')
                              ->orWhere('symptoms', 'like', '%depress%')
                              ->orWhere('symptoms', 'like', '%mental%');
                    })
                    ->where('created_at', '>=', Carbon::now()->subMonths(6))
                    ->count()
            ];

            return response()->json([
                'success' => true,
                'risk_assessment' => $riskData,
                'assessment_period' => 'Last 3-6 months'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting EDUC health risk assessment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'risk_assessment' => [],
                'message' => 'Error loading risk assessment data'
            ]);
        }
    }

    /**
     * Get EDUC Monthly Health Report
     */
    public function getEducMonthlyHealthReport()
    {
        try {
            $bsedStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSED')
                ->pluck('id')
                ->toArray();
                
            $beedStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BEED')
                ->pluck('id')
                ->toArray();

            $allStudentIds = array_merge($bsedStudentIds, $beedStudentIds);

            $currentMonth = now()->month;
            $currentYear = now()->year;

            $reportData = [
                'monthly_summary' => [
                    'total_cases' => SymptomLog::whereIn('user_id', $allStudentIds)
                        ->whereMonth('created_at', $currentMonth)
                        ->whereYear('created_at', $currentYear)
                        ->count(),
                    'emergency_cases' => SymptomLog::whereIn('user_id', $allStudentIds)
                        ->where('is_emergency', true)
                        ->whereMonth('created_at', $currentMonth)
                        ->whereYear('created_at', $currentYear)
                        ->count(),
                    'consultations' => Consultation::whereIn('student_id', $allStudentIds)
                        ->whereMonth('consultation_date', $currentMonth)
                        ->whereYear('consultation_date', $currentYear)
                        ->count(),
                    'appointments' => Appointment::whereHas('user', function($query) {
                            $query->whereIn('course', ['BSED', 'BEED']);
                        })
                        ->whereMonth('created_at', $currentMonth)
                        ->whereYear('created_at', $currentYear)
                        ->count()
                ],
                'top_concerns' => $this->getTopSymptomsForProgram($allStudentIds, 10),
                'program_breakdown' => [
                    'BSED' => [
                        'cases' => SymptomLog::whereIn('user_id', $bsedStudentIds)
                            ->whereMonth('created_at', $currentMonth)
                            ->whereYear('created_at', $currentYear)
                            ->count(),
                        'emergencies' => SymptomLog::whereIn('user_id', $bsedStudentIds)
                            ->where('is_emergency', true)
                            ->whereMonth('created_at', $currentMonth)
                            ->whereYear('created_at', $currentYear)
                            ->count()
                    ],
                    'BEED' => [
                        'cases' => SymptomLog::whereIn('user_id', $beedStudentIds)
                            ->whereMonth('created_at', $currentMonth)
                            ->whereYear('created_at', $currentYear)
                            ->count(),
                        'emergencies' => SymptomLog::whereIn('user_id', $beedStudentIds)
                            ->where('is_emergency', true)
                            ->whereMonth('created_at', $currentMonth)
                            ->whereYear('created_at', $currentYear)
                            ->count()
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'report_data' => $reportData,
                'report_period' => now()->format('F Y')
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting EDUC monthly health report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'report_data' => [],
                'message' => 'Error loading monthly report'
            ]);
        }
    }

    /**
     * Debug EDUC Symptom Trends
     */
    public function debugEducSymptomTrends()
    {
        try {
            $bsedStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BSED')
                ->pluck('id')
                ->toArray();
                
            $beedStudentIds = User::where('role', User::ROLE_STUDENT)
                ->where('course', 'BEED')
                ->pluck('id')
                ->toArray();

            $allStudentIds = array_merge($bsedStudentIds, $beedStudentIds);
            
            // Get all logs from last 6 months
            $logs = SymptomLog::whereIn('user_id', $allStudentIds)
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->whereNotNull('symptoms')
                ->get();

            // Process symptoms
            $symptomCounts = [];
            foreach ($logs as $log) {
                $symptoms = is_array($log->symptoms) ? $log->symptoms : json_decode($log->symptoms, true) ?? [];
                foreach ($symptoms as $symptom) {
                    if (is_string($symptom) && !empty(trim($symptom))) {
                        $key = ucfirst(strtolower(trim($symptom)));
                        $symptomCounts[$key] = ($symptomCounts[$key] ?? 0) + 1;
                    }
                }
            }

            arsort($symptomCounts);
            $top5 = array_slice($symptomCounts, 0, 5, true);

            return response()->json([
                'success' => true,
                'bsed_students' => count($bsedStudentIds),
                'beed_students' => count($beedStudentIds),
                'total_students' => count($allStudentIds),
                'total_logs_last_6_months' => $logs->count(),
                'sample_logs' => $logs->take(3)->map(function($log) {
                    return [
                        'id' => $log->id,
                        'user_id' => $log->user_id,
                        'symptoms' => $log->symptoms,
                        'created_at' => $log->created_at->format('Y-m-d')
                    ];
                }),
                'all_symptoms_found' => $symptomCounts,
                'top_5_symptoms' => $top5,
                'date_range' => [
                    'from' => Carbon::now()->subMonths(6)->format('Y-m-d'),
                    'to' => Carbon::now()->format('Y-m-d')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Get real-time stats without cache (for API endpoint)
     */
    public function getRealtimeStats($department)
    {
        try {
            $courses = $this->getDepartmentCourses($department);
            $studentIds = $this->getDepartmentStudentIds($department);
            
            $stats = [
                'total_students' => User::where('role', User::ROLE_STUDENT)
                    ->whereIn('course', $courses)
                    ->count(),
                    
                'monthly_appointments' => Appointment::whereHas('user', function($query) use ($courses) {
                        $query->whereIn('course', $courses);
                    })
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                    
                'pending_medical_records' => MedicalRecord::whereIn('user_id', $studentIds)
                    ->where('is_auto_created', true)
                    ->count(),
                    
                'monthly_consultations' => Consultation::whereHas('student', function($query) use ($courses) {
                        $query->whereIn('course', $courses);
                    })
                    ->whereMonth('consultation_date', now()->month)
                    ->whereYear('consultation_date', now()->year)
                    ->count(),
            ];
            
            Log::info("Realtime Stats for {$department}", $stats);
            
            return response()->json([
                'success' => true,
                'department' => $department,
                'stats' => $stats,
                'generated_at' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error getting realtime stats for {$department}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cached today's activity
     */
    private function getCachedTodayActivity($department, $courses, $studentIds)
    {
        return Cache::remember("today_activity_{$department}_" . now()->format('Y-m-d'), self::CACHE_TTL, function () use ($courses, $studentIds) {
            $today = today();
            
            return [
                'appointments' => Appointment::whereDate('appointment_date', $today)
                    ->whereHas('user', function($query) use ($courses) {
                        $query->whereIn('course', $courses);
                    })->count(),
                'symptoms' => SymptomLog::whereDate('created_at', $today)
                    ->whereIn('user_id', $studentIds)
                    ->count(),
                'consultations' => Consultation::whereDate('consultation_date', $today)
                    ->whereHas('student', function($query) use ($courses) {
                        $query->whereIn('course', $courses);
                    })->count(),
                'emergencies' => SymptomLog::whereDate('created_at', $today)
                    ->whereIn('user_id', $studentIds)
                    ->where('is_emergency', true)
                    ->count(),
            ];
        });
    }

    /**
     * Get cached health data
     */
    private function getCachedHealthData($department, $studentIds)
    {
        return Cache::remember("health_data_{$department}_" . now()->format('Y-m-d_H'), self::CACHE_TTL, function () use ($studentIds) {
            return [
                'top_symptoms' => $this->getTopSymptomsForDepartment($studentIds),
                'common_issues' => $this->getCommonHealthIssues($studentIds),
                'monthly_trend' => $this->getMonthlyTrend($studentIds),
            ];
        });
    }

    /**
     * Get common health issues from medical records
     */
    private function getCommonHealthIssues($studentIds)
    {
        try {
            return MedicalRecord::whereIn('user_id', $studentIds)
                ->whereNotNull('allergies')
                ->where('allergies', '!=', '')
                ->select('allergies', DB::raw('COUNT(*) as count'))
                ->groupBy('allergies')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error getting common health issues: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get cached recent activity
     */
    private function getCachedRecentActivity($department, $courses, $studentIds)
    {
        return Cache::remember("recent_activity_{$department}_" . now()->format('Y-m-d_H'), self::CACHE_TTL, function () use ($courses, $studentIds) {
            return [
                'appointments' => Appointment::with('user')
                    ->whereHas('user', function($query) use ($courses) {
                        $query->whereIn('course', $courses);
                    })
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(),
                'symptoms' => SymptomLog::with('user')
                    ->whereIn('user_id', $studentIds)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(),
                'medical_records' => MedicalRecord::with('user')
                    ->whereIn('user_id', $studentIds)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(),
                'consultations' => Consultation::with(['student', 'nurse'])
                    ->whereHas('student', function($query) use ($courses) {
                        $query->whereIn('course', $courses);
                    })
                    ->orderBy('consultation_date', 'desc')
                    ->limit(5)
                    ->get(),
            ];
        });
    }

    /**
     * Get cached charts data
     */
    private function getCachedChartsData($department, $courses, $studentIds)
    {
        return Cache::remember("charts_data_{$department}_" . now()->format('Y-m-d_H'), self::CACHE_TTL, function () use ($department, $courses, $studentIds) {
            return [
                'symptom_trends' => $this->getSymptomTrendsForCharts($studentIds),
                'year_distribution' => $this->getYearLevelDistribution($courses),
                'course_distribution' => $this->getCourseDistribution($courses),
                'department_symptoms' => $this->getDepartmentSymptomOverview($department),
                'appointment_types' => $this->getAppointmentTypeDistribution($courses),
                'medical_completeness' => $this->getMedicalRecordCompleteness($studentIds),
            ];
        });
    }

    /**
     * Get medical record completeness data
     */
    private function getMedicalRecordCompleteness($studentIds)
    {
        try {
            $total = MedicalRecord::whereIn('user_id', $studentIds)->count();
            $complete = MedicalRecord::whereIn('user_id', $studentIds)
                ->where('is_auto_created', false)
                ->count();

            return [
                'complete' => $complete,
                'incomplete' => $total - $complete,
                'total' => $total,
                'completion_rate' => $total > 0 ? round(($complete / $total) * 100, 2) : 0
            ];
        } catch (\Exception $e) {
            Log::error('Error getting medical record completeness: ' . $e->getMessage());
            return ['complete' => 0, 'incomplete' => 0, 'total' => 0, 'completion_rate' => 0];
        }
    }

    /**
     * Get top symptoms for department
     */
    private function getTopSymptomsForDepartment($studentIds)
    {
        try {
            $logs = SymptomLog::whereIn('user_id', $studentIds)
                ->where('created_at', '>=', Carbon::now()->subMonth())
                ->whereNotNull('symptoms')
                ->get();

            $allSymptoms = [];
            foreach ($logs as $log) {
                $symptoms = is_array($log->symptoms) ? $log->symptoms : [];
                foreach ($symptoms as $symptom) {
                    if (is_string($symptom) && !empty(trim($symptom))) {
                        $symptomKey = trim(strtolower($symptom));
                        $allSymptoms[] = ucfirst($symptomKey);
                    }
                }
            }

            return collect($allSymptoms)
                ->countBy()
                ->sortDesc()
                ->take(5)
                ->map(function ($count, $symptom) {
                    return ['symptom' => $symptom, 'count' => $count];
                })
                ->values();
        } catch (\Exception $e) {
            Log::error('Error getting top symptoms for department: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get monthly trend data
     */
    private function getMonthlyTrend($studentIds)
    {
        try {
            $trends = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $count = SymptomLog::whereIn('user_id', $studentIds)
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count();
                $trends[] = $count;
            }
            return $trends;
        } catch (\Exception $e) {
            Log::error('Error getting monthly trend: ' . $e->getMessage());
            return [0, 0, 0, 0, 0, 0];
        }
    }

    /**
     * Get symptom trends for charts with case details - FIXED VERSION
     */
    private function getSymptomTrendsForCharts($studentIds)
    {
        try {
            $months = [];
            $counts = [];
            $topCasesByMonth = [];
            
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $months[] = $month->format('M Y');
                
                // Get all symptom logs for this month
                $logs = SymptomLog::whereIn('user_id', $studentIds)
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->whereNotNull('symptoms')
                    ->get();
                
                $caseCounts = [];
                foreach ($logs as $log) {
                    $symptoms = is_array($log->symptoms) ? $log->symptoms : json_decode($log->symptoms, true) ?? [];
                    foreach ($symptoms as $symptom) {
                        if (is_string($symptom) && !empty(trim($symptom))) {
                            $key = ucfirst(strtolower(trim($symptom)));
                            if (!isset($caseCounts[$key])) {
                                $caseCounts[$key] = 0;
                            }
                            $caseCounts[$key]++;
                        }
                    }
                }
                
                // Total cases for this month
                $totalCases = array_sum($caseCounts);
                $counts[] = $totalCases;
                
                // Get top 5 cases for this month
                arsort($caseCounts);
                $topCases = array_slice($caseCounts, 0, 5, true);
                $topCasesByMonth[] = $topCases ?: []; // Ensure empty array if no cases
            }
            
            return [
                'months' => $months,
                'counts' => $counts,
                'top_cases_by_month' => $topCasesByMonth
            ];
        } catch (\Exception $e) {
            Log::error('Error getting symptom trends for charts: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'months' => [],
                'counts' => [],
                'top_cases_by_month' => []
            ];
        }
    }

    /**
     * Get year level distribution with case details - GUARANTEED RETURN
     */
    private function getYearLevelDistribution($courses)
    {
        try {
            $yearLevels = ['1st year', '2nd year', '3rd year', '4th year'];
            $distribution = [];
            
            foreach ($yearLevels as $year) {
                // Get student IDs for this year level
                $studentIds = User::where('role', User::ROLE_STUDENT)
                    ->whereIn('course', $courses)
                    ->where('year_level', $year)
                    ->pluck('id')
                    ->toArray();
                
                // Get symptom logs and count cases
                $logs = SymptomLog::whereIn('user_id', $studentIds)
                    ->whereNotNull('symptoms')
                    ->where('created_at', '>=', Carbon::now()->subMonths(6))
                    ->get();
                
                $caseCounts = [];
                foreach ($logs as $log) {
                    // Handle both array and JSON string
                    $symptoms = is_array($log->symptoms) ? $log->symptoms : (json_decode($log->symptoms, true) ?? []);
                    foreach ($symptoms as $symptom) {
                        if (is_string($symptom) && !empty(trim($symptom))) {
                            $key = ucfirst(strtolower(trim($symptom)));
                            $caseCounts[$key] = ($caseCounts[$key] ?? 0) + 1;
                        }
                    }
                }
                
                // Get top 3 cases
                arsort($caseCounts);
                $topCases = array_slice($caseCounts, 0, 3, true);
                
                $distribution[] = [
                    'year' => $year,
                    'total' => array_sum($caseCounts),
                    'top_cases' => $topCases // Will be empty array if no cases
                ];
            }
            
            return $distribution;
            
        } catch (\Exception $e) {
            Log::error('Error getting year level distribution: ' . $e->getMessage());
            
            // ALWAYS return valid structure even on error
            return [
                ['year' => '1st year', 'total' => 0, 'top_cases' => []],
                ['year' => '2nd year', 'total' => 0, 'top_cases' => []],
                ['year' => '3rd year', 'total' => 0, 'top_cases' => []],
                ['year' => '4th year', 'total' => 0, 'top_cases' => []]
            ];
        }
    }

    /**
     * Get course distribution (for BSBA/EDUC with multiple programs)
     */
    private function getCourseDistribution($courses)
    {
        try {
            $distribution = [];
            foreach ($courses as $course) {
                $count = User::where('role', User::ROLE_STUDENT)
                    ->where('course', $course)
                    ->count();
                $distribution[$course] = $count;
            }
            return $distribution;
        } catch (\Exception $e) {
            Log::error('Error getting course distribution: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get appointment type distribution
     */
    private function getAppointmentTypeDistribution($courses)
    {
        try {
            return Appointment::whereHas('user', function($query) use ($courses) {
                    $query->whereIn('course', $courses);
                })
                ->whereNotNull('appointment_type')
                ->select('appointment_type', DB::raw('COUNT(*) as count'))
                ->groupBy('appointment_type')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'appointment_type')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting appointment type distribution: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Department Symptom Overview - Comprehensive symptom data across department
     */
    private function getDepartmentSymptomOverview($department)
    {
        try {
            $studentIds = $this->getDepartmentStudentIds($department);

            // Get all symptom logs for the department from the last 6 months
            $logs = SymptomLog::whereIn('user_id', $studentIds)
                ->whereNotNull('symptoms')
                ->where('created_at', '>=', now()->subMonths(6))
                ->get();

            $symptomCounts = [];

            // Process all symptoms from logs
            foreach ($logs as $log) {
                $symptoms = is_array($log->symptoms) ? $log->symptoms : [];
                foreach ($symptoms as $symptom) {
                    if (is_string($symptom) && !empty(trim($symptom))) {
                        $key = ucfirst(strtolower(trim($symptom)));
                        if (!isset($symptomCounts[$key])) {
                            $symptomCounts[$key] = 0;
                        }
                        $symptomCounts[$key]++;
                    }
                }
            }

            // Sort by frequency (descending)
            arsort($symptomCounts);

            // Take top 15 symptoms for chart clarity
            $topSymptoms = array_slice($symptomCounts, 0, 15, true);

            return [
                'labels' => array_keys($topSymptoms),
                'data' => array_values($topSymptoms),
                'total_symptoms' => count($symptomCounts),
                'total_reports' => $logs->count(),
                'top_illness' => !empty($topSymptoms) ? array_key_first($topSymptoms) : 'No data',
                'top_illness_count' => !empty($topSymptoms) ? reset($topSymptoms) : 0
            ];
        } catch (\Exception $e) {
            Log::error("Error generating Department Symptom Overview for {$department}: " . $e->getMessage());
            return [
                'labels' => ['No data available'],
                'data' => [0],
                'total_symptoms' => 0,
                'total_reports' => 0,
                'top_illness' => 'No data',
                'top_illness_count' => 0
            ];
        }
    }

    /**
     * Get emergency cases list for quick access
     */
    private function getEmergencyCasesList($studentIds)
    {
        try {
            return SymptomLog::with('user')
                ->whereIn('user_id', $studentIds)
                ->where('is_emergency', true)
                ->whereNull('reviewed_by')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($case) {
                    return [
                        'id' => $case->id,
                        'student_name' => $case->user->full_name ?? 'Unknown Student',
                        'symptoms' => is_array($case->symptoms) ? implode(', ', $case->symptoms) : $case->symptoms,
                        'reported_at' => $case->created_at->diffForHumans(),
                        'urgency' => $case->severity ?? 'Unknown'
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error getting emergency cases list: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get department-specific analytics
     */
    private function getDepartmentAnalytics($department, $studentIds)
    {
        try {
            return [
                'student_demographics' => [
                    'by_gender' => User::where('role', User::ROLE_STUDENT)
                        ->whereIn('id', $studentIds)
                        ->select('gender', DB::raw('COUNT(*) as count'))
                        ->groupBy('gender')
                        ->get(),
                    'by_program' => User::where('role', User::ROLE_STUDENT)
                        ->whereIn('id', $studentIds)
                        ->select('course', DB::raw('COUNT(*) as count'))
                        ->groupBy('course')
                        ->get(),
                ],
                'health_metrics' => [
                    'average_visits' => SymptomLog::whereIn('user_id', $studentIds)
                        ->select('user_id', DB::raw('COUNT(*) as visits'))
                        ->groupBy('user_id')
                        ->avg('visits') ?? 0,
                    'emergency_rate' => SymptomLog::whereIn('user_id', $studentIds)
                        ->where('is_emergency', true)
                        ->count(),
                    'consultation_rate' => Consultation::whereIn('student_id', $studentIds)
                        ->select('student_id', DB::raw('COUNT(*) as consultations'))
                        ->groupBy('student_id')
                        ->avg('consultations') ?? 0,
                ],
                'academic_correlation' => $this->getAcademicCorrelation($department, $studentIds),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting department analytics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get academic correlation data
     */
    private function getAcademicCorrelation($department, $studentIds)
    {
        try {
            // This would typically correlate health data with academic performance
            // For now, return basic metrics
            return [
                'exam_period_cases' => SymptomLog::whereIn('user_id', $studentIds)
                    ->whereBetween('created_at', [now()->subMonths(3), now()])
                    ->where(function($query) {
                        $query->where('symptoms', 'like', '%stress%')
                              ->orWhere('symptoms', 'like', '%anxiety%')
                              ->orWhere('symptoms', 'like', '%fatigue%');
                    })
                    ->count(),
                'peak_health_weeks' => $this->getPeakHealthWeeks($studentIds),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting academic correlation: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get peak health weeks
     */
    private function getPeakHealthWeeks($studentIds)
    {
        try {
            $weeks = [];
            for ($i = 7; $i >= 0; $i--) {
                $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
                $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
                
                $count = SymptomLog::whereIn('user_id', $studentIds)
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->count();
                
                $weeks[] = [
                    'week' => $weekStart->format('M j'),
                    'cases' => $count
                ];
            }
            return $weeks;
        } catch (\Exception $e) {
            Log::error('Error getting peak health weeks: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get system status
     */
    private function getSystemStatus()
    {
        try {
            return [
                'uptime' => $this->getUptime(),
                'storage' => $this->getStorageUsage(),
                'active_users' => User::where('last_login_at', '>=', Carbon::now()->subDay())->count(),
                'database_status' => $this->checkDatabaseConnection(),
                'last_backup' => Carbon::now()->subDays(1)->format('Y-m-d H:i:s'),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting system status: ' . $e->getMessage());
            return [
                'uptime' => 'N/A',
                'storage' => ['used' => 'N/A', 'total' => 'N/A'],
                'active_users' => 0,
                'database_status' => 'Error',
                'last_backup' => null,
            ];
        }
    }

    /**
     * Helper Methods
     */
    private function getDepartmentStudentIds($department)
    {
        return Cache::remember("department_student_ids_{$department}", self::CACHE_TTL, function () use ($department) {
            $courses = $this->getDepartmentCourses($department);
            
            return User::where('role', User::ROLE_STUDENT)
                ->where(function($query) use ($courses) {
                    if (is_array($courses)) {
                        $query->whereIn('course', $courses);
                    } else {
                        $query->where('course', $courses);
                    }
                })
                ->pluck('id')
                ->toArray();
        });
    }

    private function getDepartmentCourses($department)
    {
        return match($department) {
            'BSIT' => ['BSIT'],
            'BSBA' => ['BSBA', 'BSBA-MM', 'BSBA-FM'],
            'EDUC' => ['BSED', 'BEED'],
            default => []
        };
    }

    private function getUptime()
    {
        try {
            $days = rand(30, 365);
            return $days . ' days';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    private function getStorageUsage()
    {
        try {
            $total = 1024; // GB
            $used = rand(300, 800); // GB
            $percentage = round(($used / $total) * 100, 2);

            return [
                'used' => $used . ' GB',
                'total' => $total . ' GB',
                'percentage' => $percentage
            ];
        } catch (\Exception $e) {
            return [
                'used' => 'N/A',
                'total' => 'N/A',
                'percentage' => 0
            ];
        }
    }

    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return 'Connected';
        } catch (\Exception $e) {
            return 'Disconnected';
        }
    }

    /**
     * Clear dashboard cache for a department
     */
    public function clearDashboardCache($department = null)
    {
        try {
            if ($department) {
                Cache::forget("dean_dashboard_{$department}_" . now()->format('Y-m-d_H'));
                Cache::forget("stats_{$department}_" . now()->format('Y-m-d_H'));
                Cache::forget("today_activity_{$department}_" . now()->format('Y-m-d'));
                Cache::forget("health_data_{$department}_" . now()->format('Y-m-d_H'));
                Cache::forget("recent_activity_{$department}_" . now()->format('Y-m-d_H'));
                Cache::forget("charts_data_{$department}_" . now()->format('Y-m-d_H'));
            } else {
                // Clear all department caches
                foreach (['BSIT', 'BSBA', 'EDUC'] as $dept) {
                    Cache::forget("dean_dashboard_{$dept}_" . now()->format('Y-m-d_H'));
                    Cache::forget("stats_{$dept}_" . now()->format('Y-m-d_H'));
                    Cache::forget("today_activity_{$dept}_" . now()->format('Y-m-d'));
                    Cache::forget("health_data_{$dept}_" . now()->format('Y-m-d_H'));
                    Cache::forget("recent_activity_{$dept}_" . now()->format('Y-m-d_H'));
                    Cache::forget("charts_data_{$dept}_" . now()->format('Y-m-d_H'));
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Dashboard cache cleared successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error clearing dashboard cache: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache'
            ]);
        }
    }

    /**
     * Error view fallback
     */
    private function getErrorView($user, $department)
    {
        return view('dashboard.dean-bsit', [
            'user' => $user,
            'department' => $department,
            'dashboardData' => [
                'stats' => [
                    'total_students' => 0,
                    'total_appointments' => 0,
                    'pending_medical_records' => 0,
                    'symptom_logs' => 0,
                    'weekly_cases' => 0,
                    'emergency_cases' => 0,
                    'total_consultations' => 0,
                    'active_students_today' => 0,
                ],
                'today' => [
                    'appointments' => 0,
                    'symptoms' => 0,
                    'consultations' => 0,
                    'emergencies' => 0,
                ],
                'health' => [
                    'top_symptoms' => collect(),
                    'common_issues' => collect(),
                    'monthly_trend' => [0, 0, 0, 0, 0, 0],
                ],
                'recent' => [
                    'appointments' => collect(),
                    'symptoms' => collect(),
                    'medical_records' => collect(),
                    'consultations' => collect(),
                ],
                'charts' => [
                    'symptom_trends' => ['months' => [], 'counts' => []],
                    'year_distribution' => [0, 0, 0, 0],
                    'course_distribution' => [],
                    'department_symptoms' => [
                        'labels' => ['No data available'],
                        'data' => [0],
                        'total_symptoms' => 0,
                        'total_reports' => 0,
                        'top_illness' => 'No data',
                        'top_illness_count' => 0
                    ],
                    'appointment_types' => [],
                    'medical_completeness' => ['complete' => 0, 'incomplete' => 0, 'total' => 0, 'completion_rate' => 0],
                ],
                'system_status' => [
                    'uptime' => 'N/A',
                    'storage' => ['used' => 'N/A', 'total' => 'N/A'],
                    'active_users' => 0,
                    'database_status' => 'Error',
                    'last_backup' => null,
                ],
                'analytics' => [],
                'emergency_cases_list' => collect(),
            ],
            'error' => 'Unable to load dashboard data'
        ])->with('error', 'Unable to load dashboard data');
    }

    /**
     * Helper method to get top symptoms for a program
     */
    private function getTopSymptomsForProgram($studentIds, $limit = 5)
    {
        if (empty($studentIds)) {
            return [];
        }

        $logs = SymptomLog::whereIn('user_id', $studentIds)
            ->where('created_at', '>=', Carbon::now()->subMonth())
            ->whereNotNull('symptoms')
            ->get();

        $symptomCounts = [];
        
        foreach ($logs as $log) {
            $symptoms = is_array($log->symptoms) ? $log->symptoms : json_decode($log->symptoms, true) ?? [];
            foreach ($symptoms as $symptom) {
                if (is_string($symptom) && !empty(trim($symptom))) {
                    $key = ucfirst(strtolower(trim($symptom)));
                    $symptomCounts[$key] = ($symptomCounts[$key] ?? 0) + 1;
                }
            }
        }

        arsort($symptomCounts);
        return array_slice($symptomCounts, 0, $limit, true);
    }

    /**
     * Helper method to get trend colors
     */
    private function getTrendColor($index)
    {
        $colors = [
            ['main' => '#10B981', 'light' => 'rgba(16, 185, 129, 0.2)'],
            ['main' => '#3B82F6', 'light' => 'rgba(59, 130, 246, 0.2)'],
            ['main' => '#8B5CF6', 'light' => 'rgba(139, 92, 246, 0.2)'],
            ['main' => '#F59E0B', 'light' => 'rgba(245, 158, 11, 0.2)'],
            ['main' => '#EF4444', 'light' => 'rgba(239, 68, 68, 0.2)']
        ];
        
        return $colors[$index % count($colors)];
    }

    /**
     * Debug department data
     */
    public function debugDepartmentData($department)
    {
        try {
            $courses = $this->getDepartmentCourses($department);
            $studentIds = $this->getDepartmentStudentIds($department);
            
            $debugInfo = [
                'department' => $department,
                'courses' => $courses,
                'student_count' => count($studentIds),
                'bsba_mm_students' => User::where('role', User::ROLE_STUDENT)->where('course', 'BSBA-MM')->count(),
                'bsba_fm_students' => User::where('role', User::ROLE_STUDENT)->where('course', 'BSBA-FM')->count(),
                'bsed_students' => User::where('role', User::ROLE_STUDENT)->where('course', 'BSED')->count(),
                'beed_students' => User::where('role', User::ROLE_STUDENT)->where('course', 'BEED')->count(),
                'symptom_logs_total' => SymptomLog::whereIn('user_id', $studentIds)->count(),
                'symptom_logs_recent' => SymptomLog::whereIn('user_id', $studentIds)
                    ->where('created_at', '>=', Carbon::now()->subMonth())
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'debug_info' => $debugInfo
            ]);

        } catch (\Exception $e) {
            Log::error("Error debugging department data for {$department}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error debugging data'
            ]);
        }
    }

    /**
     * Enhanced getChartData method to include BSBA and EDUC program-specific charts
     */
    public function getChartData($department, $chartType)
    {
        try {
            // Handle BSBA program-specific charts
            if ($department === 'BSBA') {
                switch ($chartType) {
                    case 'combined-year-level':
                        return $this->getCombinedYearLevel();
                    case 'combined-symptom-overview':
                        return $this->getCombinedSymptomOverview();
                    case 'top-symptoms-month':
                        return $this->getTopSymptomsMonth();
                    case 'symptom-trends-6months':
                        return $this->getSymptomTrends6Months();
                    case 'bsba-mm-top-symptoms':
                        return $this->getBsbaMmTopSymptoms();
                    case 'bsba-fm-top-symptoms':
                        return $this->getBsbaFmTopSymptoms();
                    case 'bsba-mm-symptom-trends':
                        return $this->getBsbaMmSymptomTrends();
                    case 'bsba-fm-symptom-trends':
                        return $this->getBsbaFmSymptomTrends();
                    case 'bsba-mm-year-level':
                        return $this->getBsbaMmYearLevel();
                    case 'bsba-fm-year-level':
                        return $this->getBsbaFmYearLevel();
                }
            }

            // Handle EDUC program-specific charts
            if ($department === 'EDUC') {
                switch ($chartType) {
                    case 'combined-year-level':
                        return $this->getEducCombinedYearLevel();
                    case 'combined-symptom-overview':
                        return $this->getEducCombinedSymptomOverview();
                    case 'top-symptoms-month':
                        return $this->getEducTopSymptomsMonth();
                    case 'symptom-trends-6months':
                        return $this->getEducSymptomTrends6Months();
                    case 'educ-bsed-top-symptoms':
                        return $this->getEducBsedTopSymptoms();
                    case 'educ-beed-top-symptoms':
                        return $this->getEducBeedTopSymptoms();
                    case 'educ-bsed-symptom-trends':
                        return $this->getEducBsedSymptomTrends();
                    case 'educ-beed-symptom-trends':
                        return $this->getEducBeedSymptomTrends();
                    case 'educ-bsed-year-level':
                        return $this->getEducBsedYearLevel();
                    case 'educ-beed-year-level':
                        return $this->getEducBeedYearLevel();
                    case 'educ-program-comparison':
                        return $this->getEducProgramComparison();
                    case 'educ-health-risk-assessment':
                        return $this->getEducHealthRiskAssessment();
                    case 'educ-monthly-health-report':
                        return $this->getEducMonthlyHealthReport();
                }
            }

            // Handle general charts for all departments
            $studentIds = $this->getDepartmentStudentIds($department);
            $courses = $this->getDepartmentCourses($department);

            switch ($chartType) {
                case 'symptom-trends':
                    $data = $this->getSymptomTrendsForCharts($studentIds);
                    return response()->json([
                        'success' => true,
                        'labels' => $data['months'],
                        'data' => $data['counts'],
                        'top_cases_by_month' => $data['top_cases_by_month']
                    ]);

                case 'year-distribution':
                    $data = $this->getYearLevelDistribution($courses);
                    return response()->json([
                        'success' => true,
                        'year_levels' => $data
                    ]);

                case 'department-symptoms':
                    $data = $this->getDepartmentSymptomOverview($department);
                    return response()->json([
                        'success' => true,
                        'labels' => $data['labels'],
                        'data' => $data['data'],
                        'total_symptoms' => $data['total_symptoms'],
                        'total_reports' => $data['total_reports'],
                        'top_illness' => $data['top_illness'],
                        'top_illness_count' => $data['top_illness_count']
                    ]);

                case 'top-symptoms':
                    $data = $this->getTopSymptomsForDepartment($studentIds);
                    return response()->json([
                        'success' => true,
                        'labels' => $data->pluck('symptom'),
                        'data' => $data->pluck('count')
                    ]);

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid chart type'
                    ]);
            }
        } catch (\Exception $e) {
            Log::error("Error getting chart data for {$department}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading chart data'
            ]);
        }
    }

    /**
     * Get recent activity for department
     */
    public function getRecentActivity($department)
    {
        try {
            $courses = $this->getDepartmentCourses($department);
            
            $recentAppointments = Appointment::with('user')
                ->whereHas('user', function($query) use ($courses) {
                    $query->whereIn('course', $courses);
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($appointment) {
                    return [
                        'type' => 'appointment',
                        'title' => 'Appointment with ' . $appointment->user->full_name,
                        'description' => $appointment->user->course . ' • ' . $appointment->appointment_date->format('M j, Y'),
                        'time' => $appointment->created_at->diffForHumans(),
                        'icon' => 'calendar',
                        'created_at' => $appointment->created_at,
                    ];
                });

            $recentSymptoms = SymptomLog::with('user')
                ->whereHas('user', function($query) use ($courses) {
                    $query->whereIn('course', $courses);
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($symptom) {
                    return [
                        'type' => 'symptom',
                        'title' => 'Symptom reported by ' . $symptom->user->full_name,
                        'description' => 'Symptoms: ' . (is_array($symptom->symptoms) ? implode(', ', $symptom->symptoms) : $symptom->symptoms),
                        'time' => $symptom->created_at->diffForHumans(),
                        'icon' => 'medical',
                        'created_at' => $symptom->created_at,
                    ];
                });

            $activities = $recentAppointments->merge($recentSymptoms)
                ->sortByDesc('created_at')
                ->take(10)
                ->map(function($item) {
                    unset($item['created_at']);
                    return $item;
                })
                ->values();

            return response()->json([
                'success' => true,
                'activities' => $activities
            ]);

        } catch (\Exception $e) {
            Log::error("Error getting recent activity for {$department}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'activities' => []
            ]);
        }
    }

    /**
     * Export department health report
     */
    public function exportReport($department, Request $request)
    {
        try {
            $type = $request->get('type', 'summary');
            $data = $this->getDepartmentDashboardData($department);

            return response()->json([
                'success' => true,
                'message' => "{$department} health report generated successfully",
                'department' => $department,
                'report_type' => $type,
                'generated_at' => now()->toDateTimeString(),
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error("Error exporting report for {$department}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating report'
            ]);
        }
    }
}