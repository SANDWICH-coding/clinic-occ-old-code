<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\SymptomLog;
use App\Models\User;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display reports index page
     */
    public function index()
    {
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }

        return view('reports.index');
    }

    /**
     * Daily report
     */
    public function dailyReport(Request $request)
    {
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }

        $date = $request->get('date', now()->format('Y-m-d'));
        
        try {
            $stats = [
                'appointments' => [
                    'total' => Appointment::whereDate('appointment_date', $date)->count(),
                    'completed' => Appointment::whereDate('appointment_date', $date)->where('status', 'completed')->count(),
                    'scheduled' => Appointment::whereDate('appointment_date', $date)->where('status', 'scheduled')->count(),
                ],
                'consultations' => Consultation::whereDate('created_at', $date)->count(),
                'symptom_logs' => SymptomLog::whereDate('created_at', $date)->count(),
                'emergency_cases' => SymptomLog::whereDate('created_at', $date)->where('is_emergency', true)->count(),
            ];

            return view('reports.daily', compact('stats', 'date'));
        } catch (\Exception $e) {
            Log::error('Error generating daily report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate daily report.');
        }
    }

    /**
     * Weekly report
     */
    public function weeklyReport(Request $request)
    {
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }

        $weekStart = $request->get('week_start', now()->startOfWeek()->format('Y-m-d'));
        
        try {
            $startDate = Carbon::parse($weekStart);
            $endDate = $startDate->copy()->endOfWeek();

            $stats = [
                'appointments' => [
                    'total' => Appointment::whereBetween('appointment_date', [$startDate, $endDate])->count(),
                    'completed' => Appointment::whereBetween('appointment_date', [$startDate, $endDate])->where('status', 'completed')->count(),
                ],
                'consultations' => Consultation::whereBetween('created_at', [$startDate, $endDate])->count(),
                'symptom_logs' => SymptomLog::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_students' => User::where('role', 'student')->whereBetween('created_at', [$startDate, $endDate])->count(),
            ];

            return view('reports.weekly', compact('stats', 'weekStart'));
        } catch (\Exception $e) {
            Log::error('Error generating weekly report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate weekly report.');
        }
    }

    /**
     * Monthly report
     */
    public function monthlyReport(Request $request)
    {
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }

        $month = $request->get('month', now()->format('Y-m'));
        
        try {
            $startDate = Carbon::parse($month)->startOfMonth();
            $endDate = Carbon::parse($month)->endOfMonth();

            $stats = [
                'appointments' => Appointment::whereBetween('appointment_date', [$startDate, $endDate])->count(),
                'consultations' => Consultation::whereBetween('created_at', [$startDate, $endDate])->count(),
                'symptom_logs' => SymptomLog::whereBetween('created_at', [$startDate, $endDate])->count(),
                'medical_records' => MedicalRecord::whereBetween('created_at', [$startDate, $endDate])->count(),
                'emergency_cases' => SymptomLog::whereBetween('created_at', [$startDate, $endDate])->where('is_emergency', true)->count(),
            ];

            return view('reports.monthly', compact('stats', 'month'));
        } catch (\Exception $e) {
            Log::error('Error generating monthly report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate monthly report.');
        }
    }

    /**
     * Custom report
     */
    public function customReport(Request $request)
    {
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }

        return view('reports.custom');
    }

    /**
     * Generate custom report
     */
    public function generateCustomReport(Request $request)
    {
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'report_type' => 'required|in:appointments,consultations,symptoms,medical_records,all',
        ]);

        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $reportType = $request->report_type;

            $data = $this->generateReportData($startDate, $endDate, $reportType);

            return view('reports.custom-result', compact('data', 'startDate', 'endDate', 'reportType'));
        } catch (\Exception $e) {
            Log::error('Error generating custom report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate custom report.');
        }
    }

    /**
     * Health trends report
     */
    public function healthTrends(Request $request)
    {
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $period = $request->get('period', 'monthly');
            $data = $this->getHealthTrendsData($period);

            return view('reports.health-trends', compact('data', 'period'));
        } catch (\Exception $e) {
            Log::error('Error generating health trends report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate health trends report.');
        }
    }

    /**
     * Appointment statistics
     */
    public function appointmentStatistics(Request $request)
    {
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $period = $request->get('period', 'monthly');
            $data = $this->getAppointmentStatistics($period);

            return view('reports.appointment-statistics', compact('data', 'period'));
        } catch (\Exception $e) {
            Log::error('Error generating appointment statistics: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate appointment statistics.');
        }
    }

    /**
     * Student health report
     */
    public function studentHealthReport(Request $request)
    {
        if (Auth::user()->role !== 'student') {
            abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();
            $startDate = $request->get('start_date', now()->subMonths(3)->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->format('Y-m-d'));

            $data = [
                'appointments' => Appointment::where('user_id', $user->id)
                    ->whereBetween('appointment_date', [$startDate, $endDate])
                    ->orderBy('appointment_date', 'desc')
                    ->get(),
                'symptom_logs' => SymptomLog::where('user_id', $user->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'desc')
                    ->get(),
                'medical_records' => MedicalRecord::where('user_id', $user->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'desc')
                    ->get(),
            ];

            return view('reports.student-health', compact('data', 'startDate', 'endDate'));
        } catch (\Exception $e) {
            Log::error('Error generating student health report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate health report.');
        }
    }

    /**
     * System health trends (for Dean)
     */
    public function systemHealthTrends()
    {
        if (Auth::user()->role !== 'dean') {
            abort(403, 'Unauthorized action.');
        }

        try {
            $data = $this->getSystemHealthTrends();
            return view('reports.system-health-trends', compact('data'));
        } catch (\Exception $e) {
            Log::error('Error generating system health trends: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate system health trends.');
        }
    }

    /**
     * Appointment analytics (for Dean)
     */
    public function appointmentAnalytics()
    {
        if (Auth::user()->role !== 'dean') {
            abort(403, 'Unauthorized action.');
        }

        try {
            $data = $this->getAppointmentAnalytics();
            return view('reports.appointment-analytics', compact('data'));
        } catch (\Exception $e) {
            Log::error('Error generating appointment analytics: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate appointment analytics.');
        }
    }

    /**
     * System performance (for Dean)
     */
    public function systemPerformance()
    {
        if (Auth::user()->role !== 'dean') {
            abort(403, 'Unauthorized action.');
        }

        try {
            $data = $this->getSystemPerformance();
            return view('reports.system-performance', compact('data'));
        } catch (\Exception $e) {
            Log::error('Error generating system performance report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate system performance report.');
        }
    }

    /**
     * User activity (for Dean)
     */
    public function userActivity()
    {
        if (Auth::user()->role !== 'dean') {
            abort(403, 'Unauthorized action.');
        }

        try {
            $data = $this->getUserActivity();
            return view('reports.user-activity', compact('data'));
        } catch (\Exception $e) {
            Log::error('Error generating user activity report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate user activity report.');
        }
    }

    /**
     * Financial overview (for Dean)
     */
    public function financialOverview()
    {
        if (Auth::user()->role !== 'dean') {
            abort(403, 'Unauthorized action.');
        }

        try {
            $data = $this->getFinancialOverview();
            return view('reports.financial-overview', compact('data'));
        } catch (\Exception $e) {
            Log::error('Error generating financial overview: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate financial overview.');
        }
    }

    /**
     * Generate report data
     */
    private function generateReportData($startDate, $endDate, $reportType)
    {
        $data = [];

        if (in_array($reportType, ['appointments', 'all'])) {
            $data['appointments'] = [
                'total' => Appointment::whereBetween('appointment_date', [$startDate, $endDate])->count(),
                'by_status' => Appointment::whereBetween('appointment_date', [$startDate, $endDate])
                    ->select('status', DB::raw('COUNT(*) as count'))
                    ->groupBy('status')
                    ->get(),
                'by_type' => Appointment::whereBetween('appointment_date', [$startDate, $endDate])
                    ->select('type', DB::raw('COUNT(*) as count'))
                    ->groupBy('type')
                    ->get(),
            ];
        }

        if (in_array($reportType, ['consultations', 'all'])) {
            $data['consultations'] = Consultation::whereBetween('created_at', [$startDate, $endDate])->count();
        }

        if (in_array($reportType, ['symptoms', 'all'])) {
            $data['symptom_logs'] = SymptomLog::whereBetween('created_at', [$startDate, $endDate])->count();
            $data['emergency_cases'] = SymptomLog::whereBetween('created_at', [$startDate, $endDate])
                ->where('is_emergency', true)
                ->count();
        }

        if (in_array($reportType, ['medical_records', 'all'])) {
            $data['medical_records'] = MedicalRecord::whereBetween('created_at', [$startDate, $endDate])->count();
        }

        return $data;
    }

    /**
     * Get health trends data
     */
    private function getHealthTrendsData($period)
    {
        $data = [];

        if ($period === 'monthly') {
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();

                $data['labels'][] = $month->format('M Y');
                $data['symptom_logs'][] = SymptomLog::whereBetween('created_at', [$start, $end])->count();
                $data['appointments'][] = Appointment::whereBetween('appointment_date', [$start, $end])->count();
                $data['emergency_cases'][] = SymptomLog::whereBetween('created_at', [$start, $end])
                    ->where('is_emergency', true)
                    ->count();
            }
        }

        return $data;
    }

    /**
     * Get appointment statistics
     */
    private function getAppointmentStatistics($period)
    {
        $data = [];

        if ($period === 'monthly') {
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();

                $data['labels'][] = $month->format('M Y');
                $data['total'][] = Appointment::whereBetween('appointment_date', [$start, $end])->count();
                $data['completed'][] = Appointment::whereBetween('appointment_date', [$start, $end])
                    ->where('status', 'completed')
                    ->count();
                $data['cancelled'][] = Appointment::whereBetween('appointment_date', [$start, $end])
                    ->where('status', 'cancelled')
                    ->count();
            }
        }

        return $data;
    }

    /**
     * Get system health trends
     */
    private function getSystemHealthTrends()
    {
        $data = [];

        // Monthly trends for last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $data['labels'][] = $month->format('M Y');
            $data['students_registered'][] = User::where('role', 'student')
                ->whereBetween('created_at', [$start, $end])
                ->count();
            $data['active_cases'][] = SymptomLog::whereBetween('created_at', [$start, $end])->count();
            $data['consultations'][] = Consultation::whereBetween('created_at', [$start, $end])->count();
        }

        return $data;
    }

    /**
     * Get appointment analytics
     */
    private function getAppointmentAnalytics()
    {
        return [
            'total_appointments' => Appointment::count(),
            'completion_rate' => Appointment::count() > 0 ? 
                round((Appointment::where('status', 'completed')->count() / Appointment::count()) * 100, 2) : 0,
            'average_wait_time' => Appointment::where('status', 'completed')->avg('wait_time') ?? 0,
            'popular_times' => $this->getPopularAppointmentTimes(),
        ];
    }

    /**
     * Get popular appointment times
     */
    private function getPopularAppointmentTimes()
    {
        return Appointment::select(DB::raw('HOUR(appointment_time) as hour'), DB::raw('COUNT(*) as count'))
            ->whereNotNull('appointment_time')
            ->groupBy(DB::raw('HOUR(appointment_time)'))
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get system performance
     */
    private function getSystemPerformance()
    {
        return [
            'uptime' => '99.9%',
            'response_time' => '125ms',
            'active_users' => User::where('last_login_at', '>=', now()->subDay())->count(),
            'storage_usage' => '2.3GB / 10GB',
        ];
    }

    /**
     * Get user activity
     */
    private function getUserActivity()
    {
        return [
            'total_users' => User::count(),
            'active_today' => User::where('last_login_at', '>=', now()->startOfDay())->count(),
            'active_this_week' => User::where('last_login_at', '>=', now()->startOfWeek())->count(),
            'active_this_month' => User::where('last_login_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * Get financial overview
     */
    private function getFinancialOverview()
    {
        return [
            'revenue' => 0, // Placeholder - integrate with your financial system
            'expenses' => 0, // Placeholder
            'budget_utilization' => '75%',
        ];
    }

    /**
     * Download report
     */
    public function download(Request $request)
    {
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'report_type' => 'required|in:daily,weekly,monthly,custom',
            'format' => 'required|in:pdf,csv,excel',
        ]);

        try {
            // This would generate and download the report in the specified format
            // For now, return a placeholder response
            return response()->json(['message' => 'Report download functionality to be implemented']);
        } catch (\Exception $e) {
            Log::error('Error downloading report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to download report.');
        }
    }

    /**
     * Generate report (for Dean reports index)
     */
    public function generateReport(Request $request)
    {
        if (Auth::user()->role !== 'dean') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'report_type' => 'required|in:health-statistics,appointment-analytics,system-performance,user-activity,financial',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $reportType = $request->report_type;
            $startDate = $request->start_date ? Carbon::parse($request->start_date) : now()->subMonth();
            $endDate = $request->end_date ? Carbon::parse($request->end_date) : now();

            $data = match($reportType) {
                'health-statistics' => $this->getSystemHealthTrends(),
                'appointment-analytics' => $this->getAppointmentAnalytics(),
                'system-performance' => $this->getSystemPerformance(),
                'user-activity' => $this->getUserActivity(),
                'financial' => $this->getFinancialOverview(),
                default => []
            };

            return view('reports.generated', compact('data', 'reportType', 'startDate', 'endDate'));
        } catch (\Exception $e) {
            Log::error('Error generating dean report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate report.');
        }
    }

    /**
     * Export report
     */
    public function exportReport(Request $request)
    {
        if (Auth::user()->role !== 'dean') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'type' => 'required|in:pdf,excel,csv',
        ]);

        try {
            // Placeholder for export functionality
            return response()->json(['message' => 'Export functionality to be implemented']);
        } catch (\Exception $e) {
            Log::error('Error exporting report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to export report.');
        }
    }

    /**
     * Reports index for Dean
     */
    public function reportsIndex()
    {
        if (Auth::user()->role !== 'dean') {
            abort(403, 'Unauthorized action.');
        }

        return view('reports.dean-index');
    }

    /**
     * Health statistics for Dean
     */
    public function healthStatistics()
    {
        if (Auth::user()->role !== 'dean') {
            abort(403, 'Unauthorized action.');
        }

        try {
            $data = $this->getSystemHealthTrends();
            return view('reports.health-statistics', compact('data'));
        } catch (\Exception $e) {
            Log::error('Error loading health statistics: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load health statistics.');
        }
    }
}