<?php

namespace App\Http\Controllers;

use App\Models\SymptomLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SymptomLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of symptom logs for students
     */
    public function index()
    {
        $this->middleware('role:student');
        
        $logs = SymptomLog::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('student.symptom-logs.index', compact('logs'));
    }

   public function nurseIndex()
{
    $this->middleware('role:nurse');
    
    $query = SymptomLog::with(['user'])
        ->orderBy('created_at', 'desc');

    // Apply search filter
    if (request()->has('search') && request('search')) {
        $search = request('search');
        $query->where(function($q) use ($search) {
            $q->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('student_id', 'LIKE', "%{$search}%");
            })->orWhere('student_id', 'LIKE', "%{$search}%");
        });
    }

    // Apply emergency filter
    if (request()->has('emergency') && request('emergency') !== '') {
        $query->where('is_emergency', request('emergency') == '1');
    }

    // Apply date filters
    if (request()->has('date_from') && request('date_from')) {
        $query->whereDate('created_at', '>=', request('date_from'));
    }

    if (request()->has('date_to') && request('date_to')) {
        $query->whereDate('created_at', '<=', request('date_to'));
    }

    if (request()->has('date') && request('date')) {
        $query->whereDate('created_at', request('date'));
    }

    if (request()->has('student_id') && request('student_id')) {
        $query->where('student_id', request('student_id'));
    }

    // Apply reviewed status filter - FIXED: Use reviewed_by instead of nurse_reviewed
    if (request()->has('reviewed_status') && request('reviewed_status')) {
        if (request('reviewed_status') === 'reviewed') {
            $query->whereNotNull('reviewed_by'); // Use reviewed_by to check if reviewed
        } elseif (request('reviewed_status') === 'pending') {
            $query->whereNull('reviewed_by'); // Use reviewed_by to check if pending
        }
    }

    $logs = $query->paginate(20);
    $totalLogs = SymptomLog::count();
    $emergencyLogs = SymptomLog::where('is_emergency', true)->count();
    $recentLogs = SymptomLog::where('created_at', '>=', Carbon::now()->subDays(7))->count();
    $todayLogs = SymptomLog::whereDate('created_at', Carbon::today())->count();

    return view('nurse.symptom-logs.index', compact(
        'logs', 
        'totalLogs', 
        'emergencyLogs', 
        'recentLogs',
        'todayLogs'
    ));
}
    /**
     * Show individual student's symptom history
     */
    public function studentHistory($studentId)
    {
        $this->middleware('role:nurse');
        
       // In the studentHistory method, replace:
$student = User::where('student_id', $studentId)
              ->orWhere('id', $studentId)
              ->firstOrFail();

// With:
$student = User::where('student_id', $studentId)
              ->orWhere('id', $studentId)
              ->firstOrFail();

        // Get all symptom logs for this student
        $logs = SymptomLog::where(function($query) use ($student, $studentId) {
                $query->where('user_id', $student->id)
                      ->orWhere('student_id', $studentId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Statistics for this student
        $stats = [
            'total_logs' => SymptomLog::where(function($query) use ($student, $studentId) {
                $query->where('user_id', $student->id)
                      ->orWhere('student_id', $studentId);
            })->count(),
            'emergency_logs' => SymptomLog::where(function($query) use ($student, $studentId) {
                $query->where('user_id', $student->id)
                      ->orWhere('student_id', $studentId);
            })->where('is_emergency', true)->count(),
            'recent_logs' => SymptomLog::where(function($query) use ($student, $studentId) {
                $query->where('user_id', $student->id)
                      ->orWhere('student_id', $studentId);
            })->where('created_at', '>=', Carbon::now()->subDays(30))->count(),
        ];

        return view('nurse.symptom-logs.student-history', compact('logs', 'student', 'stats'));
    }

    /**
 * Mark symptom log as reviewed
 */
public function markAsReviewed(SymptomLog $symptomLog)
{
    $this->middleware('role:nurse');
    
    $symptomLog->update([
        'reviewed_by' => Auth::id(),
        'reviewed_at' => now()
        // Remove 'nurse_reviewed' => true as it doesn't exist in your database
    ]);

    if (request()->expectsJson()) {
        return response()->json(['success' => true, 'message' => 'Symptom log marked as reviewed.']);
    }

    return redirect()->back()->with('success', 'Symptom log marked as reviewed.');
}

    /**
     * Display a listing of symptom logs for dean/admin
     */
    public function adminIndex()
    {
        $this->middleware('role:dean');
        
        $query = SymptomLog::with('user')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (request()->has('emergency') && request('emergency') == '1') {
            $query->where('is_emergency', true);
        }

        if (request()->has('date_from') && request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }

        if (request()->has('date_to') && request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        $logs = $query->paginate(25);
        
        // Admin statistics
        $stats = [
            'total' => SymptomLog::count(),
            'emergency' => SymptomLog::where('is_emergency', true)->count(),
            'this_month' => SymptomLog::whereMonth('created_at', Carbon::now()->month)->count(),
            'this_week' => SymptomLog::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
        ];

        return view('dean.symptom-logs.index', compact('logs', 'stats'));
    }

    /**
     * Show the form for creating a new symptom log
     */
    public function create()
    {
        $this->middleware('role:student');
        
        return view('student.symptom-logs.create');
    }

    /**
     * Store a newly created symptom log
     */
    public function store(Request $request)
    {
        $this->middleware('role:student');
        
        $request->validate([
            'symptoms' => 'required|array|min:1',
            'symptoms.*' => 'required|string',
            'severity' => 'required|in:mild,moderate,severe',
            'duration' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $studentId = $user->student_id ?? $user->id;

        $symptomLog = SymptomLog::create([
            'user_id' => $user->id,
            'student_id' => $studentId,
            'symptoms' => $request->symptoms,
            'severity' => $request->severity,
            'duration' => $request->duration,
            'notes' => $request->notes,
            'is_emergency' => $this->determineEmergency($request->symptoms, $request->severity),
            'logged_at' => now(),
        ]);

        return redirect()->route('student.symptom-logs.index')
            ->with('success', 'Symptom log created successfully.');
    }

    /**
 * Display the specified symptom log
 */
public function show(SymptomLog $symptomLog)
{
    // Check authorization
    if (Auth::user()->role === 'student' && $symptomLog->user_id !== Auth::id()) {
        abort(403);
    }

    // Use the student-specific view
    if (Auth::user()->role === 'student') {
        return view('student.symptom-logs.show', compact('symptomLog'));
    }
    
    // For nurses/admins - FIXED: changed 'log' to 'symptomLog'
    return view('nurse.symptom-logs.show', compact('symptomLog'));
}
    /**
     * Show the form for editing the specified symptom log
     */
    public function edit(SymptomLog $symptomLog)
    {
        $this->middleware('role:student');
        
        if ($symptomLog->user_id !== Auth::id()) {
            abort(403);
        }

        return view('student.symptom-logs.edit', compact('symptomLog'));
    }

    /**
     * Update the specified symptom log
     */
    public function update(Request $request, SymptomLog $symptomLog)
    {
        $this->middleware('role:student');
        
        if ($symptomLog->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'symptoms' => 'required|array|min:1',
            'symptoms.*' => 'required|string',
            'severity' => 'required|in:mild,moderate,severe',
            'duration' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        $symptomLog->update([
            'symptoms' => $request->symptoms,
            'severity' => $request->severity,
            'duration' => $request->duration,
            'notes' => $request->notes,
            'is_emergency' => $this->determineEmergency($request->symptoms, $request->severity),
        ]);

        return redirect()->route('student.symptom-logs.index')
            ->with('success', 'Symptom log updated successfully.');
    }

    /**
     * Remove the specified symptom log
     */
    public function destroy(SymptomLog $symptomLog)
    {
        if (Auth::user()->role === 'student' && $symptomLog->user_id !== Auth::id()) {
            abort(403);
        }

        $symptomLog->delete();

        $route = match(Auth::user()->role) {
            'student' => 'student.symptom-logs.index',
            'nurse' => 'nurse.symptom-logs.index',
            'dean' => 'dean.symptom-logs.index',
            default => 'dashboard'
        };

        return redirect()->route($route)
            ->with('success', 'Symptom log deleted successfully.');
    }

    /**
     * Display health tracker for students
     */
    public function healthTracker()
    {
        $this->middleware('role:student');
        
        $logs = SymptomLog::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();

        $recentLogs = $logs->take(10);
        $symptoms = $logs->flatMap(function($log) {
            return $log->symptoms;
        })->unique()->values();

        return view('student.health-tracker', compact('recentLogs', 'symptoms'));
    }

    /**
     * Analyze symptom patterns for nurses
     */
    public function analyzePatterns()
    {
        $this->middleware('role:nurse');
        
        $patterns = SymptomLog::with('user')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->get()
            ->groupBy(function($log) {
                return collect($log->symptoms)->sort()->implode(', ');
            })
            ->map(function($group) {
                return [
                    'symptoms' => $group->first()->symptoms,
                    'count' => $group->count(),
                    'users' => $group->pluck('user.name')->unique()->values(),
                    'emergency_count' => $group->where('is_emergency', true)->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(10);

        return view('nurse.symptom-patterns', compact('patterns'));
    }

    // ... (rest of the methods remain the same: export, exportToPdf, exportToCsv, exportToExcel, determineEmergency)

    /**
     * Export symptom logs for nurses
     */
    public function export(Request $request)
    {
        $this->middleware('role:nurse');
        
        $format = $request->get('format', 'excel');
        
        $query = SymptomLog::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->has('emergency') && $request->get('emergency') == '1') {
            $query->where('is_emergency', true);
        }

        if ($request->has('date_from') && $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->has('date_to') && $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        if ($request->has('student_id') && $request->get('student_id')) {
            $query->where('student_id', $request->get('student_id'));
        }

        $logs = $query->get();
        
        switch ($format) {
            case 'pdf':
                return $this->exportToPdf($logs);
            case 'csv':
                return $this->exportToCsv($logs);
            case 'excel':
            default:
                return $this->exportToExcel($logs);
        }
    }

    private function exportToPdf($logs)
    {
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="symptom-logs-' . date('Y-m-d') . '.pdf"'
        ];
        
        return response()->json(['message' => 'PDF export functionality needs to be implemented'], 501);
    }

    private function exportToCsv($logs)
    {
        $filename = 'symptom-logs-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID', 
                'Student Name', 
                'Student ID', 
                'Symptoms', 
                'Emergency', 
                'Logged At',
                'Created At'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user ? $log->user->name : 'N/A',
                    $log->student_id,
                    is_array($log->symptoms) ? implode(', ', $log->symptoms) : $log->symptoms,
                    $log->is_emergency ? 'Yes' : 'No',
                    $log->logged_at ? $log->logged_at->format('Y-m-d H:i:s') : 'N/A',
                    $log->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToExcel($logs)
    {
        return $this->exportToCsv($logs);
    }

    /**
     * Determine if symptoms constitute an emergency
     */
    private function determineEmergency(array $symptoms, string $severity): bool
    {
        $emergencyKeywords = [
            'chest pain', 'difficulty breathing', 'shortness of breath',
            'severe bleeding', 'unconscious', 'seizure', 'stroke',
            'heart attack', 'severe headache', 'high fever'
        ];

        if ($severity === 'severe') {
            return true;
        }

        foreach ($symptoms as $symptom) {
            foreach ($emergencyKeywords as $keyword) {
                if (stripos($symptom, $keyword) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}