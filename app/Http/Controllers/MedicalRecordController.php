<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MedicalRecordsExport;
use App\Exports\StudentRecordExport;
use App\Exports\MedicalHistoryExport;
use App\Imports\MedicalRecordsImport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;


class MedicalRecordController extends Controller
{
    use AuthorizesRequests;
    
    /**
 * Display a listing of medical records
 */
public function index(Request $request): View|RedirectResponse
{
    Log::info('MedicalRecordController@index called for user: ' . Auth::user()->role);
    
    $user = Auth::user();
    
    // STUDENT ACCESS - FIXED LOGIC
    if ($user->role === 'student') {
        $medicalRecord = $user->medicalRecord;
        
        // If no record exists OR record is auto-created, redirect to create form
        if (!$medicalRecord || $medicalRecord->is_auto_created) {
            return redirect()->route('student.medical-records.create')
                           ->with('info', 'Please create your medical record first.');
        }
        
        // Student has a real record - show their dashboard
        $stats = [
            'completion_percentage' => $medicalRecord->getCompletenessPercentage(),
            'last_updated' => $medicalRecord->updated_at,
            'health_risk_level' => $medicalRecord->hasHighRiskConditions() ? 'High' : 'Normal',
            'bmi' => $medicalRecord->calculateBMI(),
            'bmi_category' => $medicalRecord->getBMICategory(),
            'health_summary' => $medicalRecord->getHealthSummary(),
            'missing_fields' => $medicalRecord->getMissingFields()
        ];
        
        return view('student.medical-records.index', compact('medicalRecord', 'stats'));
    }

    // NURSE/DEAN ACCESS - Only show non-auto-created records
    $query = MedicalRecord::with(['user', 'createdBy'])
        ->where('is_auto_created', false)
        ->orderBy('created_at', 'desc');
    
    // Apply search and filters...
    if ($request->filled('search')) {
        $searchTerm = $request->search;
        $query->whereHas('user', function ($q) use ($searchTerm) {
            $q->where('first_name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('student_id', 'LIKE', "%{$searchTerm}%")
              ->orWhere('email', 'LIKE', "%{$searchTerm}%")
              ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchTerm}%"]);
        })->orWhere('allergies', 'LIKE', "%{$searchTerm}%")
          ->orWhere('past_illnesses', 'LIKE', "%{$searchTerm}%");
    }
    
    // Apply other filters...
    if ($request->filled('course')) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('course', $request->course);
        });
    }
    
    if ($request->filled('year_level')) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('year_level', $request->year_level);
        });
    }
    
    if ($request->filled('blood_type')) {
        $query->where('blood_type', $request->blood_type);
    }
    
    if ($request->filled('risk_level')) {
        $this->applyRiskLevelFilter($query, $request->risk_level);
    }
    
    $medicalRecords = $query->paginate(15)->withQueryString();
    $filterOptions = $this->getFilterOptions();
    $statistics = $this->getIndexStatistics($request);
    
    // Return appropriate view based on role
    if ($user->role === 'nurse') {
        return view('nurse.medical-records.index', compact('medicalRecords', 'filterOptions', 'statistics'));
    }
    
    if ($user->role === 'dean') {
        return view('dean.medical-records.index', compact('medicalRecords', 'filterOptions', 'statistics'));
    }
    
    abort(403, 'Unauthorized access.');
}
    /**
 * Show the form for creating a new medical record
 */
public function create(Request $request): View
{
    $this->authorize('create', MedicalRecord::class);
    
    // FIXED: Use correct relationship name (singular)
    $users = User::where('role', 'student')
        ->whereDoesntHave('medicalRecord') // ← SINGULAR 'medicalRecord'
        ->orderBy('first_name')
        ->orderBy('last_name')
        ->get();

    $selectedUserId = null;
    $selectedUser = null;
    
    if ($request->has('student_id')) {
        $selectedUserId = $request->student_id;
        $selectedUser = User::find($selectedUserId);
        
        // Additional safety check: ensure selected user doesn't already have a medical record
        if ($selectedUser && $selectedUser->medicalRecord) {
            $selectedUserId = null;
            $selectedUser = null;
        }
    }

    $viewPath = Auth::user()->role === 'dean' ? 'dean.medical-records.create' : 'nurse.medical-records.create';
    return view($viewPath, [
        'users' => $users,
        'selectedUserId' => $selectedUserId,
        'selectedUser' => $selectedUser
    ]);
}

    /**
     * Create medical record for specific user
     */
    public function createFor(User $user): View
    {
        $this->authorize('create', MedicalRecord::class);
        
        if ($user->role !== 'student') {
            abort(404, 'Student not found.');
        }
        
        if ($user->medicalRecord) {
            $routeName = Auth::user()->role === 'dean' ? 'dean.medical-records.index' : 'nurse.medical-records.index';
            return redirect()->route($routeName)
                           ->with('error', 'This student already has a medical record.');
        }

        $viewPath = Auth::user()->role === 'dean' ? 'dean.medical-records.create' : 'nurse.medical-records.create';
        return view($viewPath, [
            'selectedUser' => $user,
            'users' => collect([$user])
        ]);
    }

    /**
 * Store a newly created medical record
 */
public function store(Request $request): RedirectResponse
{
    $this->authorize('create', MedicalRecord::class);
    
    try {
        DB::beginTransaction();
        
        $validated = $this->validateMedicalRecord($request, true);
        $validated['created_by'] = Auth::id();
        
        // Sanitize input data
        $validated = $this->sanitizeInput($validated);

        // DOUBLE CHECK: Ensure user exists and is a student
        $user = User::where('id', $validated['user_id'])
                   ->where('role', 'student')
                   ->firstOrFail();

        // DOUBLE CHECK: Ensure user doesn't already have a medical record
        if ($user->medicalRecord) {
            throw new \Exception('This student already has a medical record. Please update the existing record instead.');
        }

        $medicalRecord = MedicalRecord::create($validated);
        
        // Log the activity
        $this->logActivity('created', $medicalRecord);
        
        DB::commit();
        
        $routeName = Auth::user()->role === 'dean' ? 'dean.medical-records.index' : 'nurse.medical-records.index';
        
        return redirect()
            ->route($routeName)
            ->with('success', 'Medical record created successfully for ' . $medicalRecord->user->full_name . '.');
            
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error creating medical record: ' . $e->getMessage());
        
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Error creating medical record: ' . $e->getMessage());
    }
}

    /**
 * Display the specified medical record
 */
public function show(MedicalRecord $medicalRecord): View
{
    $user = Auth::user();
    
    // Students can only view their own records
    if ($user->role === 'student' && $medicalRecord->user_id !== $user->id) {
        abort(403, 'Unauthorized access to this medical record.');
    }

    // Check if trying to view an auto-created (empty) record
    if ($medicalRecord->is_auto_created) {
        if ($user->role === 'student') {
            return redirect()->route('student.medical-records.create')
                           ->with('info', 'Please complete your medical record first.');
        } else {
            $routeName = $user->role === 'dean' ? 'dean.medical-records.index' : 'nurse.medical-records.index';
            return redirect()->route($routeName)
                           ->with('warning', 'This student has not completed their medical record yet.');
        }
    }

    // Load related data
    $medicalRecord->load(['user', 'createdBy']);
    
    // Log for debugging
    Log::info('Medical Record Display', [
        'record_id' => $medicalRecord->id,
        'student' => $medicalRecord->user->full_name,
        'blood_type' => $medicalRecord->blood_type,
        'has_emergency_contacts' => !empty($medicalRecord->emergency_contact_name_1),
        'is_complete' => $medicalRecord->isComplete()
    ]);

    $viewPath = $user->role === 'student' ? 'student.medical-records.show' : 
               ($user->role === 'dean' ? 'dean.medical-records.show' : 'nurse.medical-records.show');
    
    return view($viewPath, compact('medicalRecord'));
}

public function update(Request $request, MedicalRecord $medicalRecord): RedirectResponse
{
    $this->authorize('update', $medicalRecord);
    
    try {
        DB::beginTransaction();
        
        $validated = $this->validateMedicalRecord($request, false);
        
        // Handle boolean fields correctly using request->boolean()
        $booleanFields = [
            'has_been_pregnant',
            'has_undergone_surgery', 
            'is_taking_maintenance_drugs',
            'has_been_hospitalized_6_months',
            'is_pwd',
            'is_fully_vaccinated',
            'has_received_booster'
        ];
        
        foreach ($booleanFields as $field) {
            $validated[$field] = $request->boolean($field);
        }
        
        $validated = $this->sanitizeInput($validated);
        
        // Update the record
        $medicalRecord->update($validated);
        
        // Get changes from the model (safer than manual diff)
        $changes = $medicalRecord->getChanges();
        
        // Log the update
        $this->logActivity('updated', $medicalRecord, [
            'changes' => $changes
        ]);
        
        DB::commit();

        $routeName = Auth::user()->role === 'student' ? 'student.medical-records.index' : 
                    (Auth::user()->role === 'dean' ? 'dean.medical-records.index' : 'nurse.medical-records.index');
        
        return redirect()
            ->route($routeName)
            ->with('success', 'Medical record updated successfully!');
            
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error updating medical record: ' . $e->getMessage() . ' | Stack: ' . $e->getTraceAsString());
        
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Error updating medical record: ' . $e->getMessage());
    }
}

    /**
     * Remove the specified medical record
     */
    public function destroy(MedicalRecord $medicalRecord): RedirectResponse
    {
        $this->authorize('delete', $medicalRecord);
        
        try {
            DB::beginTransaction();
            
            $studentName = $medicalRecord->user->full_name;
            $studentId = $medicalRecord->user->student_id;
            
            // Log the deletion
            $this->logActivity('deleted', $medicalRecord);
            
            $medicalRecord->delete();
            
            DB::commit();

            $routeName = Auth::user()->role === 'dean' ? 'dean.medical-records.index' : 'nurse.medical-records.index';
            
            return redirect()
                ->route($routeName)
                ->with('success', "Medical record for {$studentName} ({$studentId}) has been deleted successfully.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting medical record: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Error deleting medical record: ' . $e->getMessage());
        }
    }

    /**
     * Download medical record as PDF
     */
    public function download(MedicalRecord $medicalRecord)
    {
        $this->authorize('view', $medicalRecord);
        
        try {
            // Load related data
            $medicalRecord->load(['user', 'createdBy']);
            
            $pdf = Pdf::loadView('pdf.medical-record', compact('medicalRecord'));
            $filename = "medical-record-{$medicalRecord->user->student_id}-" . now()->format('Y-m-d') . ".pdf";
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('Error downloading medical record: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating download: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export medical records (bulk export)
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', MedicalRecord::class);
        
        try {
            $validated = $request->validate([
                'format' => 'required|in:csv,excel,pdf',
                'filters' => 'nullable|array',
                'include_fields' => 'nullable|array',
                'date_range' => 'nullable|string',
                'students' => 'nullable|array'
            ]);
            
            // Build query based on filters
            $query = MedicalRecord::with(['user', 'createdBy']);
            
            // Apply filters if provided
            if (!empty($validated['filters'])) {
                $this->applyExportFilters($query, $validated['filters']);
            }
            
            // Apply date range filter
            if (!empty($validated['date_range'])) {
                $this->applyDateRangeFilter($query, $validated['date_range']);
            }
            
            // Apply specific students filter
            if (!empty($validated['students'])) {
                $query->whereIn('user_id', $validated['students']);
            }
            
            $records = $query->get();
            
            $filename = 'medical-records-export-' . now()->format('Y-m-d-H-i-s');
            
            switch ($validated['format']) {
                case 'csv':
                    return Excel::download(
                        new MedicalRecordsExport($records), 
                        $filename . '.csv'
                    );
                case 'excel':
                    return Excel::download(
                        new MedicalRecordsExport($records), 
                        $filename . '.xlsx'
                    );
                case 'pdf':
                    $pdf = Pdf::loadView('pdf.medical-records-export', [
                        'records' => $records,
                        'exportedBy' => Auth::user()->full_name,
                        'exportedAt' => now()
                    ]);
                    return $pdf->download($filename . '.pdf');
                default:
                    throw new \Exception('Invalid export format');
            }
            
        } catch (\Exception $e) {
            Log::error('Error exporting medical records: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating export: ' . $e->getMessage()
            ], 500);
        }
    }
   /**
 * Show detailed medical history for a specific student
 */
public function studentRecordDetails($id): View|RedirectResponse
{
    $this->authorize('viewAny', MedicalRecord::class);
    
    try {
        // Find the student user
        $user = User::where('role', 'student')->findOrFail($id);
        
        // Get the student's medical record with relationships
        $medicalRecord = $user->medicalRecord;
        
        if (!$medicalRecord) {
            $routeName = Auth::user()->role === 'dean' ? 'dean.students.search' : 'nurse.students.search';
            return redirect()->route($routeName)
                           ->with('error', 'This student does not have a medical record yet.');
        }
        
        // Load additional relationships
        $medicalRecord->load(['createdBy']);
        
        // Calculate comprehensive stats
        $stats = [
            'completion_rate' => $this->calculateRecordCompletion($medicalRecord),
            'risk_level' => $this->calculateHealthRiskLevel($medicalRecord),
            'last_updated' => $medicalRecord->updated_at,
            'bmi' => $medicalRecord->calculateBMI(),
            'bmi_category' => $medicalRecord->getBMICategory(),
        ];
        
        // Determine the appropriate view based on user role
        $viewPath = Auth::user()->role === 'dean' ? 'dean.students.record-details' : 'nurse.students.record-details';
        
        // Check if view exists, fallback to a working view
        if (!view()->exists($viewPath)) {
            // Use the medical record show view as fallback
            $viewPath = Auth::user()->role === 'dean' ? 'dean.medical-records.show' : 'nurse.medical-records.show';
        }
        
        return view($viewPath, compact('user', 'medicalRecord', 'stats'));
        
    } catch (\Exception $e) {
        Log::error('Error loading student record details: ' . $e->getMessage());
        
        $routeName = Auth::user()->role === 'dean' ? 'dean.students.search' : 'nurse.students.search';
        return redirect()->route($routeName)
                       ->with('error', 'Error loading student record: ' . $e->getMessage());
    }
}
/**
 * Display student's own medical record details
 */
public function studentShow(MedicalRecord $medicalRecord): View
{
    $user = Auth::user();
    
    // Students can only view their own records
    if ($user->role === 'student' && $medicalRecord->user_id !== $user->id) {
        abort(403, 'Unauthorized access to this medical record.');
    }

    // Load related data
    $medicalRecord->load(['user', 'createdBy']);

    return view('student.medical-records.show', compact('medicalRecord'));
}

    // ============= NURSE STUDENT SEARCH & MANAGEMENT =============
    
    /**
     * Main student search page for nurses
     */
    public function nurseStudentSearch(Request $request): View
    {
        $this->authorize('viewAny', MedicalRecord::class);
        
        $query = User::where('role', 'student')->with(['medicalRecord']);
        
        // Apply search filters
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('student_id', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchTerm}%"]);
            });
        }
        
        if ($request->filled('course')) {
            $query->where('course', $request->course);
        }
        
        if ($request->filled('year_level')) {
            $query->where('year_level', $request->year_level);
        }
        
        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }
        
        // Filter by medical record status
        if ($request->filled('record_status')) {
            if ($request->record_status === 'with_record') {
                $query->has('medicalRecord');
            } elseif ($request->record_status === 'without_record') {
                $query->doesntHave('medicalRecord');
            }
        }
        
        $students = $query->orderBy('first_name')
                         ->orderBy('last_name')
                         ->paginate(20)
                         ->withQueryString();
        
        // Get filter options
        $filterOptions = $this->getStudentFilterOptions();
        
        $viewPath = Auth::user()->role === 'dean' ? 'dean.students.search' : 'nurse.students.search';
        
        return view($viewPath, array_merge($filterOptions, ['students' => $students]));
    }
    
    /**
     * AJAX search for student auto-suggestions
     */
    public function searchStudentsAjax(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MedicalRecord::class);
        
        try {
            $searchTerm = $request->get('q', '');
            
            if (strlen($searchTerm) < 2) {
                return response()->json([]);
            }
            
            $students = User::where('role', 'student')
                ->where(function ($query) use ($searchTerm) {
                    $query->where('first_name', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('student_id', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                          ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchTerm}%"]);
                })
                ->with('medicalRecord')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->limit(10)
                ->get()
                ->map(function ($student) {
                    $profileRoute = Auth::user()->role === 'dean' ? 'dean.students.show' : 'nurse.students.show';
                    
                    return [
                        'id' => $student->id,
                        'student_id' => $student->student_id,
                        'full_name' => $student->full_name ?? $student->first_name . ' ' . $student->last_name,
                        'course' => $student->course,
                        'year_level' => $student->year_level,
                        'section' => $student->section,
                        'has_medical_record' => $student->medicalRecord ? true : false,
                        'url' => route($profileRoute, $student->id)
                    ];
                });
            
            return response()->json($students);
            
        } catch (\Exception $e) {
            Log::error('Error in AJAX student search: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Search failed'
            ], 500);
        }
    }

    /**
     * Get student summary for AJAX calls
     */
  public function getStudentSummary(MedicalRecord $medicalRecord): JsonResponse
{
    // Students can only view their own records
    if (Auth::user()->role === 'student' && $medicalRecord->user_id !== Auth::user()->id) {
        abort(403, 'Unauthorized access.');
    }
    
    try {
        $summary = [
            'basic_info' => [
                'blood_type' => $medicalRecord->blood_type,
                'height' => $medicalRecord->height,
                'weight' => $medicalRecord->weight,
                'bmi' => $medicalRecord->calculateBMI(),
                'bmi_category' => $medicalRecord->getBMICategory(),
            ],
            'health_status' => [
                'has_allergies' => !empty($medicalRecord->allergies),
                'allergies' => $medicalRecord->allergies,
                'vaccination_status' => $medicalRecord->is_fully_vaccinated,
                'health_risks' => $medicalRecord->getHealthRisks(),
            ],
            'emergency_contacts' => [
                'primary' => [
                    'name' => $medicalRecord->emergency_contact_name_1,
                    'number' => $medicalRecord->emergency_contact_number_1,
                    'relationship' => $medicalRecord->emergency_contact_relationship_1
                ],
                'secondary' => [
                    'name' => $medicalRecord->emergency_contact_name_2,
                    'number' => $medicalRecord->emergency_contact_number_2,
                    'relationship' => $medicalRecord->emergency_contact_relationship_2
                ]
            ],
            'completion_rate' => $this->calculateRecordCompletion($medicalRecord),
            'last_updated' => $medicalRecord->updated_at->format('M j, Y h:i A')
        ];
        
        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error retrieving summary'
        ], 500);
    }
}

/**
 * Show comprehensive student record management page
 */
public function manageStudentRecord(User $user): View
{
        $this->authorize('viewAny', MedicalRecord::class);
        
        if ($user->role !== 'student') {
            abort(404, 'Student not found.');
        }
        
        // Load relationships
        $user->load([
            'medicalRecord',
            'appointments' => function ($query) {
                $query->orderBy('appointment_date', 'desc')->limit(10);
            },
            'symptomLogs' => function ($query) {
                $query->orderBy('logged_at', 'desc')->limit(10);
            }
        ]);
        
        $viewPath = Auth::user()->role === 'dean' ? 'dean.students.manage-record' : 'nurse.students.manage-record';
        
        return view($viewPath, compact('user'));
    }

    // ============= BULK OPERATIONS =============
    
    /**
     * Bulk import medical records
     */
    public function bulkImport(Request $request): RedirectResponse
    {
        $this->authorize('create', MedicalRecord::class);
        
        try {
            $validated = $request->validate([
                'import_file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
                'import_type' => 'required|in:create,update,upsert'
            ]);
            
            $file = $request->file('import_file');
            
            DB::beginTransaction();
            
            $import = new MedicalRecordsImport($validated['import_type']);
            Excel::import($import, $file);
            
            DB::commit();
            
            Log::info('Bulk import completed', [
                'file_name' => $file->getClientOriginalName(),
                'import_type' => $validated['import_type'],
                'user' => Auth::user()->id,
                'records_processed' => $import->getProcessedCount()
            ]);
            
            return redirect()
                ->back()
                ->with('success', "Successfully imported {$import->getProcessedCount()} medical records.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk import: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Bulk import failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update medical records
     */
    public function bulkUpdate(Request $request): JsonResponse
{
    $this->authorize('update', MedicalRecord::class);
    
    try {
        $validated = $request->validate([
            'record_ids' => 'required|array|min:1',
            'record_ids.*' => 'exists:medical_records,id',
            'updates' => 'required|array|min:1',
            'updates.is_fully_vaccinated' => 'sometimes|boolean',
            'updates.vaccine_type' => 'sometimes|string|max:100',
            'updates.emergency_contact_name_1' => 'sometimes|string|max:100',
            'updates.emergency_contact_number_1' => 'sometimes|string|max:20',
            'updates.emergency_contact_relationship_1' => 'sometimes|string|max:100',
            'updates.emergency_contact_name_2' => 'sometimes|string|max:100',
            'updates.emergency_contact_number_2' => 'sometimes|string|max:20',
            'updates.emergency_contact_relationship_2' => 'sometimes|string|max:100',
        ]);
        
        DB::beginTransaction();
        
        $updateData = $this->sanitizeInput($validated['updates']);
        
        $updated = MedicalRecord::whereIn('id', $validated['record_ids'])
            ->update($updateData);
        
        DB::commit();
        
        Log::info('Bulk update completed', [
            'records_updated' => $updated,
            'update_data' => $updateData,
            'updated_by' => Auth::user()->full_name
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "Successfully updated {$updated} medical records.",
            'updated_count' => $updated
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error in bulk update: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Bulk update failed: ' . $e->getMessage()
        ], 500);
    }
}

   /**
 * Display student's own medical record
 */
public function studentIndex(): View|RedirectResponse
{
    $user = Auth::user();
    $medicalRecord = $user->medicalRecord;
    
    // ✅ FIX: First check if no record exists at all
    if (!$medicalRecord) {
        return redirect()->route('student.medical-records.create')
                       ->with('info', 'Please create your medical record first.');
    }
    
    // ✅ Then check if record is auto-created (empty)
    if ($medicalRecord->is_auto_created) {
        return redirect()->route('student.medical-records.create')
                       ->with('info', 'Please complete your medical record first.');
    }
    
    // Student has a real record - show their dashboard
    $stats = [
        'completion_percentage' => $this->calculateRecordCompletion($medicalRecord),
        'last_updated' => $medicalRecord->updated_at,
        'health_risk_level' => $this->calculateHealthRiskLevel($medicalRecord),
        'bmi' => $medicalRecord->calculateBMI(),
        'bmi_category' => $medicalRecord->getBMICategory(),
        'health_summary' => $medicalRecord->getHealthSummary(),
        'missing_fields' => $medicalRecord->getMissingFields()
    ];
    
    return view('student.medical-records.index', compact('medicalRecord', 'stats'));
}

   /**
 * Show the form for students to create their own medical record
 */
public function studentCreate(): View|RedirectResponse
{
    $user = Auth::user();
    
    // Check if user already has a REAL medical record (not auto-created)
    if ($user->medicalRecord && !$user->medicalRecord->is_auto_created) {
        return redirect()->route('student.medical-records.index')
                       ->with('info', 'You already have a medical record.');
    }

    return view('student.medical-records.create');
}

    public function studentStore(Request $request): RedirectResponse
{
    $user = Auth::user();
    
    // Check if user already has a completed medical record (not auto-created)
    if ($user->medicalRecord && !$user->medicalRecord->is_auto_created) {
        return redirect()->route('student.medical-records.index')
                       ->with('error', 'You already have a medical record.');
    }

    try {
        DB::beginTransaction();
        
        $validated = $this->validateStudentMedicalRecord($request);
        $validated = $this->sanitizeInput($validated);
        
        // Set the user_id and created_by to the authenticated student
        $validated['user_id'] = $user->id;
        $validated['created_by'] = $user->id;
        $validated['is_auto_created'] = false; // Remove the auto-created flag

        // If an auto-created record exists, update it instead of creating new
        if ($user->medicalRecord && $user->medicalRecord->is_auto_created) {
            $medicalRecord = $user->medicalRecord;
            $medicalRecord->update($validated);
        } else {
            $medicalRecord = MedicalRecord::create($validated);
        }
        
        // Log student self-creation
        $this->logActivity('self-created', $medicalRecord);
        
        DB::commit();
        
        return redirect()->route('student.medical-records.index')
                        ->with('success', 'Your medical record has been created successfully!');
                        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error creating student medical record: ' . $e->getMessage());
        
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Error creating medical record: ' . $e->getMessage());
    }
}

    public function studentEdit(MedicalRecord $medicalRecord): View
{
    // Check if the student owns this medical record
    if (Auth::user()->id !== $medicalRecord->user_id) {
        abort(403, 'Unauthorized action.');
    }

    // Check if record is auto-created (empty placeholder)
    if ($medicalRecord->is_auto_created) {
        return redirect()->route('student.medical-records.create')
                       ->with('info', 'Please complete your medical record first.');
    }

    // Load relationships to prevent undefined errors
    $medicalRecord->load(['user']);

    return view('student.medical-records.edit', compact('medicalRecord'));
}

    /**
     * Update student's own medical record (limited fields)
     */
    public function studentUpdate(Request $request, MedicalRecord $medicalRecord): RedirectResponse
    {
        // Check if the student owns this medical record
        if (Auth::user()->id !== $medicalRecord->user_id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            
            $validated = $this->validateStudentUpdate($request);
            $validated = $this->sanitizeInput($validated);
            
            $medicalRecord->update($validated);
            
            $this->logActivity('self-updated', $medicalRecord);
            
            DB::commit();

            return redirect()->route('student.medical-records.index')
                            ->with('success', 'Your medical record has been updated successfully!');
                            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating student medical record: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error updating medical record: ' . $e->getMessage());
        }
    }

    // ============= HELPER METHODS =============
    
    /**
     * Apply risk level filter to query
     */
    private function applyRiskLevelFilter($query, string $riskLevel): void
    {
        switch ($riskLevel) {
            case 'high':
                $query->where(function ($q) {
                    $q->where('is_pwd', true)
                      ->orWhereNotNull('allergies')
                      ->orWhere('is_taking_maintenance_drugs', true)
                      ->orWhere('has_been_hospitalized_6_months', true);
                });
                break;
            case 'medium':
                $query->where(function ($q) {
                    $q->where(function ($subQ) {
                        $subQ->whereNotNull('past_illnesses')
                             ->orWhere('has_undergone_surgery', true);
                    })->whereNotIn('id', function ($subQ) {
                        $subQ->select('id')->from('medical_records')
                             ->where('is_pwd', true)
                             ->orWhereNotNull('allergies')
                             ->orWhere('is_taking_maintenance_drugs', true)
                             ->orWhere('has_been_hospitalized_6_months', true);
                    });
                });
                break;
            case 'low':
                $query->where(function ($q) {
                    $q->where('is_pwd', false)
                      ->whereNull('allergies')
                      ->where('is_taking_maintenance_drugs', false)
                      ->where('has_been_hospitalized_6_months', false)
                      ->where('has_undergone_surgery', false);
                });
                break;
        }
    }
    
    /**
     * Get filter options for medical records
     */
    private function getFilterOptions(): array
    {
        return [
            'courses' => User::where('role', 'student')
                          ->whereNotNull('course')
                          ->distinct()
                          ->pluck('course')
                          ->sort()
                          ->values(),
            'yearLevels' => User::where('role', 'student')
                             ->whereNotNull('year_level')
                             ->distinct()
                             ->pluck('year_level')
                             ->sort()
                             ->values(),
            'bloodTypes' => MedicalRecord::whereNotNull('blood_type')
                                       ->distinct()
                                       ->pluck('blood_type')
                                       ->sort()
                                       ->values()
        ];
    }
    
    /**
     * Get filter options for student search
     */
    private function getStudentFilterOptions(): array
    {
        return [
            'courses' => User::where('role', 'student')
                          ->whereNotNull('course')
                          ->distinct()
                          ->pluck('course')
                          ->sort()
                          ->values(),
            'yearLevels' => User::where('role', 'student')
                             ->whereNotNull('year_level')
                             ->distinct()
                             ->pluck('year_level')
                             ->sort()
                             ->values(),
            'sections' => User::where('role', 'student')
                           ->whereNotNull('section')
                           ->distinct()
                           ->pluck('section')
                           ->sort()
                           ->values()
        ];
    }
    
    /**
     * Apply export filters to query
     */
    private function applyExportFilters($query, array $filters): void
    {
        if (!empty($filters['course'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('course', $filters['course']);
            });
        }
        
        if (!empty($filters['year_level'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('year_level', $filters['year_level']);
            });
        }
        
        if (!empty($filters['blood_type'])) {
            $query->where('blood_type', $filters['blood_type']);
        }
        
        if (!empty($filters['vaccination_status'])) {
            $query->where('is_fully_vaccinated', $filters['vaccination_status'] === 'vaccinated');
        }
        
        if (!empty($filters['risk_level']) && $filters['risk_level'] === 'high') {
            $query->where(function ($q) {
                $q->where('is_pwd', true)
                  ->orWhereNotNull('allergies')
                  ->orWhere('is_taking_maintenance_drugs', true)
                  ->orWhere('has_been_hospitalized_6_months', true);
            });
        }
    }
    
    /**
     * Apply date range filter to query
     */
    private function applyDateRangeFilter($query, string $dateRange): void
    {
        switch ($dateRange) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                break;
            case 'year':
                $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
                break;
            case 'last_30_days':
                $query->where('created_at', '>=', now()->subDays(30));
                break;
            case 'last_6_months':
                $query->where('created_at', '>=', now()->subMonths(6));
                break;
        }
    }
    
    /**
     * Calculate record completion percentage
     */
  private function calculateRecordCompletion($medicalRecord): int
{
    if (!$medicalRecord) return 0;
    
    $fields = [
        'blood_type', 'height', 'weight', 'allergies', 'past_illnesses',
        'emergency_contact_name_1', 'emergency_contact_number_1', 'emergency_contact_relationship_1',
        'is_fully_vaccinated', 'vaccine_type'
    ];
    
    $completedFields = 0;
    foreach ($fields as $field) {
        if (!empty($medicalRecord->$field) || 
            ($medicalRecord->$field === false && in_array($field, ['is_fully_vaccinated']))) {
            $completedFields++;
        }
    }
    
    return round(($completedFields / count($fields)) * 100);
}

    /**
     * Calculate health risk level
     */
    private function calculateHealthRiskLevel($medicalRecord): string
    {
        if (!$medicalRecord) return 'Unknown';
        
        $riskFactors = 0;
        
        if ($medicalRecord->is_pwd) $riskFactors++;
        if (!empty($medicalRecord->allergies)) $riskFactors++;
        if ($medicalRecord->is_taking_maintenance_drugs) $riskFactors++;
        if ($medicalRecord->has_been_hospitalized_6_months) $riskFactors++;
        if (!empty($medicalRecord->past_illnesses)) $riskFactors++;
        if ($medicalRecord->has_undergone_surgery) $riskFactors++;
        
        if ($riskFactors >= 3) return 'High';
        if ($riskFactors >= 1) return 'Medium';
        return 'Low';
    }

 private function getIndexStatistics(Request $request): array
{
    $baseQuery = MedicalRecord::query();
    
    // Apply same filters as main query for consistent stats
    if ($request->filled('search')) {
        $searchTerm = $request->search;
        $baseQuery->whereHas('user', function ($q) use ($searchTerm) {
            $q->where('first_name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('student_id', 'LIKE', "%{$searchTerm}%")
              ->orWhere('email', 'LIKE', "%{$searchTerm}%");
        });
    }
    
    return [
        'total_records' => $baseQuery->count(),
        'complete_records' => $baseQuery->whereNotNull('blood_type')
                                       ->whereNotNull('height')
                                       ->whereNotNull('weight')
                                       ->whereNotNull('emergency_contact_name_1')
                                       ->count(),
        'high_risk_records' => $baseQuery->where(function ($q) {
            $q->where('is_pwd', true)
              ->orWhereNotNull('allergies')
              ->orWhere('is_taking_maintenance_drugs', true)
              ->orWhere('has_been_hospitalized_6_months', true); // FIXED: Changed orWere to orWhere
        })->count(),
        'recent_updates' => $baseQuery->where('updated_at', '>=', now()->subDays(7))->count(),
        'vaccination_rate' => $this->getVaccinationRate($baseQuery),
        'needs_review' => $baseQuery->where('updated_at', '<', now()->subMonths(6))->count()
    ];
}
    
    /**
     * Get vaccination rate from a query
     */
    private function getVaccinationRate($query): float
    {
        $total = $query->count();
        if ($total === 0) return 0;
        
        $vaccinated = (clone $query)->where('is_fully_vaccinated', true)->count();
        return round(($vaccinated / $total) * 100, 1);
    }

    /**
     * Get vaccination statistics
     */
    private function getVaccinationStats(): array
    {
        $total = MedicalRecord::count();
        $vaccinated = MedicalRecord::where('is_fully_vaccinated', true)->count();
        
        return [
            'total_records' => $total,
            'fully_vaccinated' => $vaccinated,
            'not_vaccinated' => $total - $vaccinated,
            'vaccination_rate' => $total > 0 ? round(($vaccinated / $total) * 100, 1) : 0,
            'with_boosters' => MedicalRecord::where('number_of_boosters', '>', 0)->count(),
            'vaccine_types' => MedicalRecord::whereNotNull('vaccine_type')
                                          ->groupBy('vaccine_type')
                                          ->selectRaw('vaccine_type, count(*) as count')
                                          ->pluck('count', 'vaccine_type')
                                          ->toArray()
        ];
    }

    /**
     * Get blood type distribution
     */
    private function getBloodTypeDistribution(): array
    {
        return MedicalRecord::whereNotNull('blood_type')
                           ->groupBy('blood_type')
                           ->selectRaw('blood_type, count(*) as count')
                           ->pluck('count', 'blood_type')
                           ->toArray();
    }

    /**
     * Get demographic statistics
     */
    private function getDemographicStats(): array
    {
        return [
            'total_students' => User::where('role', 'student')->count(),
            'with_records' => User::where('role', 'student')->has('medicalRecord')->count(),
            'without_records' => User::where('role', 'student')->doesntHave('medicalRecord')->count(),
            'by_year_level' => User::where('role', 'student')
                                  ->whereNotNull('year_level')
                                  ->groupBy('year_level')
                                  ->selectRaw('year_level, count(*) as count')
                                  ->pluck('count', 'year_level')
                                  ->toArray(),
            'by_course' => User::where('role', 'student')
                              ->whereNotNull('course')
                              ->groupBy('course')
                              ->selectRaw('course, count(*) as count')
                              ->pluck('count', 'course')
                              ->toArray(),
            'by_gender' => User::where('role', 'student')
                              ->whereNotNull('gender')
                              ->groupBy('gender')
                              ->selectRaw('gender, count(*) as count')
                              ->pluck('count', 'gender')
                              ->toArray()
        ];
    }
    
    /**
     * Get BMI distribution statistics
     */
    private function getBMIDistribution(): array
    {
        $records = MedicalRecord::whereNotNull('height')
                              ->whereNotNull('weight')
                              ->where('height', '>', 0)
                              ->where('weight', '>', 0)
                              ->get();
        
        $distribution = [
            'underweight' => 0,
            'normal' => 0,
            'overweight' => 0,
            'obese' => 0,
            'total_calculated' => $records->count()
        ];
        
        foreach ($records as $record) {
            $category = $record->getBMICategory();
            switch ($category) {
                case 'Underweight':
                    $distribution['underweight']++;
                    break;
                case 'Normal weight':
                    $distribution['normal']++;
                    break;
                case 'Overweight':
                    $distribution['overweight']++;
                    break;
                case 'Obese':
                    $distribution['obese']++;
                    break;
            }
        }
        
        return $distribution;
    }

    // ============= VALIDATION METHODS =============
    
    /**
     * Validate medical record data for nurses/deans
     */
    /**
 * Validate medical record data for nurses/deans
 */
private function validateMedicalRecord(Request $request, bool $requireUserId = true): array
{
    $rules = [
        'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        'height' => 'nullable|numeric|between:50,250',
        'weight' => 'nullable|numeric|between:20,300',
        'allergies' => 'nullable|string|max:500',
        'past_illnesses' => 'nullable|string|max:1000',
        'has_been_pregnant' => 'sometimes|boolean',
        'has_undergone_surgery' => 'sometimes|boolean',
        'surgery_details' => 'nullable|string|max:1000',
        'is_taking_maintenance_drugs' => 'sometimes|boolean',
        'maintenance_drugs_specify' => 'nullable|string|max:1000',
        'has_been_hospitalized_6_months' => 'sometimes|boolean',
        'hospitalization_details_6_months' => 'nullable|string|max:1000',
        'is_pwd' => 'sometimes|boolean',
        'pwd_disability_details' => 'nullable|string|max:1000',
        'pwd_id' => 'nullable|string|max:50',
        'pwd_reason' => 'nullable|string|max:1000',
        'notes_health_problems' => 'nullable|string|max:1000',
        'family_history_details' => 'nullable|string|max:1000',
        'is_fully_vaccinated' => 'sometimes|boolean',
        'vaccine_name' => 'nullable|string|max:255', // Changed from vaccine_type
        'vaccine_date' => 'nullable|date|before_or_equal:today',
        'number_of_doses' => 'nullable|integer|min:0|max:10',
        'number_of_boosters' => 'nullable|integer|min:0|max:10',
        'booster_type' => 'nullable|string|max:255', // Increased length
        'emergency_contact_name_1' => 'required|string|max:100',
        'emergency_contact_number_1' => 'required|string|max:20',
        'emergency_contact_relationship_1' => 'required|string|max:100',
        'emergency_contact_name_2' => 'nullable|string|max:100',
        'emergency_contact_number_2' => 'nullable|string|max:20',
        'emergency_contact_relationship_2' => 'nullable|string|max:100',
    ];

    if ($requireUserId) {
        $rules['user_id'] = 'required|exists:users,id';
    }

    $validated = $request->validate($rules);
    
    // Handle boolean conversions for checkboxes
    $booleanFields = [
        'has_been_pregnant',
        'has_undergone_surgery',
        'is_taking_maintenance_drugs',
        'has_been_hospitalized_6_months',
        'is_pwd',
        'is_fully_vaccinated'
    ];
    
    foreach ($booleanFields as $field) {
        // Convert checkbox values: if present and truthy, set to true; otherwise false
        $validated[$field] = $request->has($field) && $request->input($field);
    }

    return $validated;
}
    /**
     * Validate medical record data for students (creation)
     */
   /**
 * Validate medical record data for students (creation)
 */
private function validateStudentMedicalRecord(Request $request): array
{
    $validated = $request->validate([
        'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        'height' => 'nullable|numeric|min:50|max:300',
        'weight' => 'nullable|numeric|min:20|max:500',
        'allergies' => 'nullable|string|max:500',
        'past_illnesses' => 'nullable|string|max:1000',
        'surgery_details' => 'nullable|string|max:500',
        'maintenance_drugs_specify' => 'nullable|string|max:500',
        'hospitalization_details_6_months' => 'nullable|string|max:500',
        'pwd_disability_details' => 'nullable|string|max:500',
        'pwd_id' => 'nullable|string|max:50',
        'pwd_reason' => 'nullable|string|max:500',
        'notes_health_problems' => 'nullable|string|max:1000',
        'family_history_details' => 'nullable|string|max:1000',
        'vaccine_type' => 'nullable|string|in:Pfizer-BioNTech,Moderna,AstraZeneca,Johnson & Johnson,Sinovac,Sinopharm,Other',
        'vaccine_name' => 'nullable|string|max:100',
        'other_vaccine_type' => 'nullable|string|max:100',
        'vaccine_date' => 'nullable|date|before_or_equal:today',
        'number_of_doses' => 'nullable|integer|min:0|max:10',
        'number_of_boosters' => 'nullable|integer|min:0|max:10',
        'booster_type' => 'nullable|string|max:100',
         'emergency_contact_name_1' => 'required|string|max:100',
        'emergency_contact_number_1' => 'required|string|max:20',
        'emergency_contact_relationship_1' => 'required|string|max:100',
        'emergency_contact_name_2' => 'nullable|string|max:100',
        'emergency_contact_number_2' => 'nullable|string|max:20',
        'emergency_contact_relationship_2' => 'nullable|string|max:100',
    ]);
    
    // Convert checkbox values from the form to boolean
    $booleanFields = [
        'has_been_pregnant',
        'has_undergone_surgery',
        'is_taking_maintenance_drugs',
        'has_been_hospitalized_6_months',
        'is_pwd',
        'is_fully_vaccinated'
    ];
    
    foreach ($booleanFields as $field) {
        $validated[$field] = $request->has($field) ? true : false;
    }

    return $validated;
}
    /**
     * Validate medical record data for student updates (limited fields)
     */
    private function validateStudentUpdate(Request $request): array
    {
        $validated = $request->validate([
            'height' => 'nullable|numeric|min:50|max:300',
            'weight' => 'nullable|numeric|min:20|max:500',
            'allergies' => 'nullable|string|max:500',
            'surgery_details' => 'nullable|string|max:500',
            'maintenance_drugs_specify' => 'nullable|string|max:500',
            'hospitalization_details_6_months' => 'nullable|string|max:500',
            'pwd_disability_details' => 'nullable|string|max:500',
            'notes_health_problems' => 'nullable|string|max:1000',
            'family_history_details' => 'nullable|string|max:1000',
             'emergency_contact_name_1' => 'required|string|max:100',
        'emergency_contact_number_1' => 'required|string|max:20',
        'emergency_contact_relationship_1' => 'required|string|max:100',
        'emergency_contact_name_2' => 'nullable|string|max:100',
        'emergency_contact_number_2' => 'nullable|string|max:20',
        'emergency_contact_relationship_2' => 'nullable|string|max:100',
        ]);
        
        // Convert checkbox values from the form to boolean
        $booleanFields = [
            'has_been_pregnant',
            'has_undergone_surgery',
            'is_taking_maintenance_drugs',
            'has_been_hospitalized_6_months',
            'is_pwd'
        ];
        
        foreach ($booleanFields as $field) {
            $validated[$field] = $request->has($field) ? true : false;
        }

        return $validated;
    }

    // ============= UTILITY METHODS =============
    
    /**
     * Validate and sanitize medical record input
     */
    private function sanitizeInput(array $data): array
{
    // Sanitize text fields
    $textFields = [
        'allergies', 'past_illnesses', 'surgery_details', 
        'maintenance_drugs_specify', 'hospitalization_details_6_months',
        'pwd_disability_details', 'pwd_reason', 'notes_health_problems',
        'family_history_details', 'vaccine_name', 'other_vaccine_type',
        'booster_type', 'emergency_contact_name_1', 'emergency_contact_name_2',
        'emergency_contact_relationship_1', 'emergency_contact_relationship_2'
    ];
    
    foreach ($textFields as $field) {
        if (isset($data[$field])) {
            $data[$field] = strip_tags($data[$field]);
            $data[$field] = trim($data[$field]);
            // Convert empty strings to null
            if ($data[$field] === '') {
                $data[$field] = null;
            }
        }
    }
    
    // Sanitize phone numbers
    $phoneFields = ['emergency_contact_number_1', 'emergency_contact_number_2'];
    foreach ($phoneFields as $field) {
        if (isset($data[$field])) {
            $data[$field] = preg_replace('/[^0-9+\-\s\(\)]/', '', $data[$field]);
            $data[$field] = trim($data[$field]);
        }
    }
    
    // Validate numeric fields
    $numericFields = ['height', 'weight', 'number_of_doses', 'number_of_boosters'];
    foreach ($numericFields as $field) {
        if (isset($data[$field]) && !is_numeric($data[$field])) {
            $data[$field] = null;
        }
    }
    
    return $data;
}

/**
 * Log medical record activity
 */
private function logActivity(string $action, MedicalRecord $record, array $details = []): void
{
        Log::info("Medical Record {$action}", array_merge([
            'record_id' => $record->id,
            'student_id' => $record->user->student_id,
            'student_name' => $record->user->full_name,
            'performed_by' => Auth::user()->full_name,
            'performed_by_role' => Auth::user()->role,
            'timestamp' => now()->toDateTimeString()
        ], $details));
    }

    /**
     * Get dashboard statistics for role-specific dashboards
     */
    public function getDashboardStats(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!in_array($user->role, ['nurse', 'dean'])) {
                abort(403, 'Unauthorized access.');
            }
            
            $stats = [
                'overview' => [
                    'total_students' => User::where('role', 'student')->count(),
                    'total_records' => MedicalRecord::count(),
                    'completion_rate' => $this->getCompletionRate(),
                    'high_risk_count' => MedicalRecord::where(function ($q) {
                        $q->where('is_pwd', true)
                          ->orWhereNotNull('allergies')
                          ->orWhere('is_taking_maintenance_drugs', true)
                          ->orWhere('has_been_hospitalized_6_months', true);
                    })->count()
                ],
                'recent_activity' => [
                    'records_created_today' => MedicalRecord::whereDate('created_at', today())->count(),
                    'records_updated_today' => MedicalRecord::whereDate('updated_at', today())
                                                          ->where('created_at', '!=', DB::raw('updated_at'))
                                                          ->count(),
                    'records_created_this_week' => MedicalRecord::whereBetween('created_at', 
                        [now()->startOfWeek(), now()->endOfWeek()])->count(),
                    'needs_review' => MedicalRecord::where('updated_at', '<', now()->subMonths(6))->count()
                ],
                'health_metrics' => [
                    'vaccination_stats' => $this->getVaccinationStats(),
                    'bmi_distribution' => $this->getBMIDistribution(),
                    'blood_type_distribution' => $this->getBloodTypeDistribution(),
                    'demographic_stats' => $this->getDemographicStats()
                ],
                'alerts' => [
                    'incomplete_records' => MedicalRecord::where(function ($q) {
                        $q->whereNull('blood_type')
                          ->orWhereNull('height')
                          ->orWhereNull('weight')
                          ->orWhereNull('emergency_contact_name_1');
                    })->count(),
                    'missing_emergency_contacts' => MedicalRecord::whereNull('emergency_contact_name_1')->count(),
                    'outdated_records' => MedicalRecord::where('updated_at', '<', now()->subYear())->count()
                ]
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'generated_at' => now()->toDateTimeString(),
                'generated_by' => $user->full_name
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating dashboard stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating statistics'
            ], 500);
        }
    }

    /**
     * Get completion rate percentage
     */
    private function getCompletionRate(): float
    {
        $total = MedicalRecord::count();
        if ($total === 0) return 0;
        
        $complete = MedicalRecord::whereNotNull('blood_type')
                                ->whereNotNull('height')
                                ->whereNotNull('weight')
                                ->whereNotNull('emergency_contact_name_1')
                                ->count();
        
        return round(($complete / $total) * 100, 1);
    }

    /**
     * Advanced search functionality
     */
    public function advancedSearch(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MedicalRecord::class);
        
        try {
            $validated = $request->validate([
                'search_term' => 'nullable|string|max:255',
                'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
                'risk_level' => 'nullable|string|in:low,medium,high',
                'vaccination_status' => 'nullable|boolean',
                'has_allergies' => 'nullable|boolean',
                'is_pwd' => 'nullable|boolean',
                'course' => 'nullable|string|max:100',
                'year_level' => 'nullable|string|max:50',
                'created_from' => 'nullable|date',
                'created_to' => 'nullable|date|after_or_equal:created_from',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);
            
            $query = MedicalRecord::with(['user'])->orderBy('updated_at', 'desc');
            
            // Apply search filters
            if (!empty($validated['search_term'])) {
                $searchTerm = $validated['search_term'];
                $query->whereHas('user', function ($q) use ($searchTerm) {
                    $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('student_id', 'LIKE', "%{$searchTerm}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchTerm}%"]);
                });
            }
            
            // Apply additional filters
            $this->applyAdvancedSearchFilters($query, $validated);
            
            $limit = $validated['limit'] ?? 20;
            $records = $query->limit($limit)->get();
            
            $results = $records->map(function ($record) {
                return [
                    'id' => $record->id,
                    'student' => [
                        'id' => $record->user->id,
                        'name' => $record->user->full_name,
                        'student_id' => $record->user->student_id,
                        'course' => $record->user->course,
                        'year_level' => $record->user->year_level
                    ],
                    'medical_info' => [
                        'blood_type' => $record->blood_type,
                        'bmi' => $record->calculateBMI(),
                        'risk_level' => $this->calculateHealthRiskLevel($record),
                        'has_allergies' => !empty($record->allergies),
                        'is_vaccinated' => $record->is_fully_vaccinated,
                        'is_pwd' => $record->is_pwd
                    ],
                    'last_updated' => $record->updated_at->format('M j, Y'),
                    'completion_rate' => $this->calculateRecordCompletion($record)
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $results,
                'total_found' => $records->count(),
                'search_criteria' => $validated
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in advanced search: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply advanced search filters to query
     */
    private function applyAdvancedSearchFilters($query, array $filters): void
    {
        if (isset($filters['blood_type'])) {
            $query->where('blood_type', $filters['blood_type']);
        }
        
        if (isset($filters['vaccination_status'])) {
            $query->where('is_fully_vaccinated', $filters['vaccination_status']);
        }
        
        if (isset($filters['has_allergies'])) {
            if ($filters['has_allergies']) {
                $query->whereNotNull('allergies');
            } else {
                $query->whereNull('allergies');
            }
        }
        
        if (isset($filters['is_pwd'])) {
            $query->where('is_pwd', $filters['is_pwd']);
        }
        
        if (!empty($filters['course'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('course', $filters['course']);
            });
        }
        
        if (!empty($filters['year_level'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('year_level', $filters['year_level']);
            });
        }
        
        // Date range filters
        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }
        
        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }
        
        // Risk level filter
        if (!empty($filters['risk_level'])) {
            $this->applyRiskLevelFilter($query, $filters['risk_level']);
        }
    }

    /**
     * Get medical record summary for quick overview
     */
  public function getRecordSummary(MedicalRecord $medicalRecord): JsonResponse
{
    $this->authorize('view', $medicalRecord);
    
    try {
        $summary = [
            'basic_info' => [
                'student_name' => $medicalRecord->user->full_name,
                'student_id' => $medicalRecord->user->student_id,
                'course' => $medicalRecord->user->course,
                'year_level' => $medicalRecord->user->year_level,
                'blood_type' => $medicalRecord->blood_type,
                'bmi' => $medicalRecord->calculateBMI(),
                'bmi_category' => $medicalRecord->getBMICategory()
            ],
            'health_status' => [
                'risk_level' => $this->calculateHealthRiskLevel($medicalRecord),
                'has_allergies' => !empty($medicalRecord->allergies),
                'is_pwd' => $medicalRecord->is_pwd,
                'on_maintenance_drugs' => $medicalRecord->is_taking_maintenance_drugs,
                'vaccination_status' => $medicalRecord->is_fully_vaccinated
            ],
            'emergency_contacts' => [
                'primary' => [
                    'name' => $medicalRecord->emergency_contact_name_1,
                    'number' => $medicalRecord->emergency_contact_number_1,
                    'relationship' => $medicalRecord->emergency_contact_relationship_1
                ],
                'secondary' => [
                    'name' => $medicalRecord->emergency_contact_name_2,
                    'number' => $medicalRecord->emergency_contact_number_2,
                    'relationship' => $medicalRecord->emergency_contact_relationship_2
                ]
            ],
            'last_updated' => $medicalRecord->updated_at->format('M j, Y h:i A'),
            'completion_rate' => $this->calculateRecordCompletion($medicalRecord)
        ];
        
        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error getting record summary: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error retrieving record summary'
        ], 500);
    }
}

    /**
     * Update emergency contacts only
     */
    public function updateEmergencyContacts(Request $request): RedirectResponse
{
    $user = Auth::user();
    $medicalRecord = $user->medicalRecord;

    if (!$medicalRecord) {
        return redirect()->back()->with('error', 'No medical record found.');
    }

    if ($medicalRecord->user_id !== $user->id) {
        abort(403, 'Unauthorized action.');
    }

    try {
        DB::beginTransaction();
        
        $validated = $request->validate([
            'emergency_contact_name_1' => 'required|string|max:100',
            'emergency_contact_number_1' => 'required|string|max:20',
            'emergency_contact_relationship_1' => 'required|string|max:100',
            'emergency_contact_name_2' => 'nullable|string|max:100',
            'emergency_contact_number_2' => 'nullable|string|max:20',
            'emergency_contact_relationship_2' => 'nullable|string|max:100',
        ]);

        $validated = $this->sanitizeInput($validated);
        $medicalRecord->update($validated);
        
        $this->logActivity('emergency-contacts-updated', $medicalRecord);
        
        DB::commit();

        return redirect()->route('student.medical-records.index')
                        ->with('success', 'Emergency contacts updated successfully!');
                        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error updating emergency contacts: ' . $e->getMessage());
        
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Error updating emergency contacts: ' . $e->getMessage());
    }
}

    /**
     * Generate health summary report
     */
    public function generateHealthSummary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MedicalRecord::class);
        
        try {
            $validated = $request->validate([
                'period' => 'nullable|string|in:week,month,quarter,year',
                'include_demographics' => 'nullable|boolean',
                'include_trends' => 'nullable|boolean'
            ]);
            
            $period = $validated['period'] ?? 'month';
            $includeDemographics = $validated['include_demographics'] ?? true;
            $includeTrends = $validated['include_trends'] ?? true;
            
            $summary = [
                'period' => $period,
                'generated_at' => now()->toDateTimeString(),
                'generated_by' => Auth::user()->full_name,
                'overview' => $this->getHealthOverview($period),
                'vaccination_summary' => $this->getVaccinationStats(),
                'risk_assessment' => $this->getRiskAssessment(),
                'alerts' => $this->getHealthAlerts()
            ];
            
            if ($includeDemographics) {
                $summary['demographics'] = $this->getDemographicStats();
            }
            
            if ($includeTrends) {
                $summary['trends'] = $this->getHealthTrends($period);
            }
            
            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating health summary: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating health summary'
            ], 500);
        }
    }

    /**
     * Get health overview for specified period
     */
    private function getHealthOverview(string $period): array
    {
        $startDate = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subMonths(3),
            'year' => now()->subYear(),
            default => now()->subMonth()
        };
        
        return [
            'total_records' => MedicalRecord::count(),
            'new_records' => MedicalRecord::where('created_at', '>=', $startDate)->count(),
            'updated_records' => MedicalRecord::where('updated_at', '>=', $startDate)
                                              ->where('created_at', '!=', DB::raw('updated_at'))
                                              ->count(),
            'completion_rate' => $this->getCompletionRate(),
            'high_risk_students' => MedicalRecord::where(function ($q) {
                $q->where('is_pwd', true)
                  ->orWhereNotNull('allergies')
                  ->orWhere('is_taking_maintenance_drugs', true)
                  ->orWhere('has_been_hospitalized_6_months', true);
            })->count()
        ];
    }

    /**
     * Get risk assessment data
     */
    private function getRiskAssessment(): array
    {
        $total = MedicalRecord::count();
        
        $highRisk = MedicalRecord::where(function ($q) {
            $q->where('is_pwd', true)
              ->orWhereNotNull('allergies')
              ->orWhere('is_taking_maintenance_drugs', true)
              ->orWhere('has_been_hospitalized_6_months', true);
        })->count();
        
        $mediumRisk = MedicalRecord::where(function ($q) {
            $q->where(function ($subQ) {
                $subQ->whereNotNull('past_illnesses')
                     ->orWhere('has_undergone_surgery', true);
            })->whereNotIn('id', function ($subQ) {
                $subQ->select('id')->from('medical_records')
                     ->where('is_pwd', true)
                     ->orWhereNotNull('allergies')
                     ->orWhere('is_taking_maintenance_drugs', true)
                     ->orWhere('has_been_hospitalized_6_months', true);
            });
        })->count();
        
        $lowRisk = $total - $highRisk - $mediumRisk;
        
        return [
            'total_assessed' => $total,
            'high_risk' => [
                'count' => $highRisk,
                'percentage' => $total > 0 ? round(($highRisk / $total) * 100, 1) : 0
            ],
            'medium_risk' => [
                'count' => $mediumRisk,
                'percentage' => $total > 0 ? round(($mediumRisk / $total) * 100, 1) : 0
            ],
            'low_risk' => [
                'count' => $lowRisk,
                'percentage' => $total > 0 ? round(($lowRisk / $total) * 100, 1) : 0
            ]
        ];
    }

    /**
     * Get health alerts
     */
    private function getHealthAlerts(): array
    {
        return [
            'critical' => [
                'missing_emergency_contacts' => MedicalRecord::whereNull('emergency_contact_name_1')->count(),
                'incomplete_vital_info' => MedicalRecord::where(function ($q) {
                    $q->whereNull('blood_type')
                      ->orWhereNull('height')
                      ->orWhereNull('weight');
                })->count()
            ],
            'warning' => [
                'outdated_records' => MedicalRecord::where('updated_at', '<', now()->subYear())->count(),
                'needs_review' => MedicalRecord::where('updated_at', '<', now()->subMonths(6))->count(),
                'unvaccinated_students' => MedicalRecord::where('is_fully_vaccinated', false)->count()
            ],
            'info' => [
                'recent_updates' => MedicalRecord::where('updated_at', '>=', now()->subDays(7))->count(),
                'pwd_students' => MedicalRecord::where('is_pwd', true)->count()
            ]
        ];
    }

    /**
     * Get health trends for specified period
     */
    private function getHealthTrends(string $period): array
    {
        $periods = match($period) {
            'week' => 7,
            'month' => 30,
            'quarter' => 90,
            'year' => 365,
            default => 30
        };
        
        $trends = [];
        for ($i = $periods - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'new_records' => MedicalRecord::whereDate('created_at', $date)->count(),
                'updates' => MedicalRecord::whereDate('updated_at', $date)
                                         ->whereDate('created_at', '!=', $date)
                                         ->count()
            ];
        }
        
        return $trends;
    }

    /**
     * Batch operations for medical records
     */
    public function batchOperations(Request $request): JsonResponse
    {
        $this->authorize('update', MedicalRecord::class);
        
        try {
            $validated = $request->validate([
                'operation' => 'required|string|in:update,delete,export,notify',
                'record_ids' => 'required|array|min:1|max:100',
                'record_ids.*' => 'exists:medical_records,id',
                'data' => 'nullable|array'
            ]);
            
            DB::beginTransaction();
            
            $records = MedicalRecord::whereIn('id', $validated['record_ids'])->get();
            $results = [];
            
            switch ($validated['operation']) {
                case 'update':
                    $results = $this->batchUpdate($records, $validated['data'] ?? []);
                    break;
                case 'delete':
                    $results = $this->batchDelete($records);
                    break;
                case 'export':
                    $results = $this->batchExport($records);
                    break;
                case 'notify':
                    $results = $this->batchNotify($records);
                    break;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'operation' => $validated['operation'],
                'processed' => count($records),
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in batch operations: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Batch operation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch update records
     */
    private function batchUpdate($records, array $updateData): array
    {
        $results = [];
        $sanitizedData = $this->sanitizeInput($updateData);
        
        foreach ($records as $record) {
            try {
                $record->update($sanitizedData);
                $this->logActivity('batch-updated', $record);
                $results[] = ['id' => $record->id, 'status' => 'updated'];
            } catch (\Exception $e) {
                $results[] = ['id' => $record->id, 'status' => 'failed', 'error' => $e->getMessage()];
            }
        }
        
        return $results;
    }

    /**
     * Batch delete records
     */
    private function batchDelete($records): array
    {
        $results = [];
        
        foreach ($records as $record) {
            try {
                $this->logActivity('batch-deleted', $record);
                $record->delete();
                $results[] = ['id' => $record->id, 'status' => 'deleted'];
            } catch (\Exception $e) {
                $results[] = ['id' => $record->id, 'status' => 'failed', 'error' => $e->getMessage()];
            }
        }
        
        return $results;
    }

    /**
     * Batch export records
     */
    private function batchExport($records): array
    {
        $filename = 'batch-export-' . now()->format('Y-m-d-H-i-s') . '.xlsx';
        $filepath = storage_path('app/exports/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        Excel::store(new MedicalRecordsExport($records), 'exports/' . $filename);
        
        return [
            'filename' => $filename,
            'filepath' => $filepath,
            'record_count' => $records->count(),
            'download_url' => route('medical-records.download-export', ['filename' => $filename])
        ];
    }

    /**
     * Batch notify (placeholder for notification system)
     */
    private function batchNotify($records): array
    {
        $results = [];
        
        foreach ($records as $record) {
            // TODO: Implement notification system
            $results[] = [
                'id' => $record->id,
                'status' => 'notification_queued',
                'student' => $record->user->full_name
            ];
        }
        
        return $results;
    }

    /**
     * Download exported file
     */
    public function downloadExport(string $filename): Response
    {
        $this->authorize('viewAny', MedicalRecord::class);
        
        $filepath = storage_path('app/exports/' . $filename);
        
        if (!file_exists($filepath)) {
            abort(404, 'Export file not found.');
        }
        
        return response()->download($filepath)->deleteFileAfterSend();
    }

    /**
     * Get medical record statistics by filters
     */
    public function getFilteredStatistics(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MedicalRecord::class);
        
        try {
            $query = MedicalRecord::with(['user']);
            
            // Apply filters from request
            if ($request->filled('course')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('course', $request->course);
                });
            }
            
            if ($request->filled('year_level')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('year_level', $request->year_level);
                });
            }
            
            if ($request->filled('risk_level')) {
                $this->applyRiskLevelFilter($query, $request->risk_level);
            }
            
            $records = $query->get();
            
            $statistics = [
                'total_records' => $records->count(),
                'completion_stats' => $this->calculateFilteredCompletionStats($records),
                'health_stats' => $this->calculateFilteredHealthStats($records),
                'demographic_breakdown' => $this->calculateFilteredDemographics($records),
                'vaccination_stats' => $this->calculateFilteredVaccinationStats($records)
            ];
            
            return response()->json([
                'success' => true,
                'data' => $statistics,
                'filters_applied' => $request->only(['course', 'year_level', 'risk_level'])
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting filtered statistics: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error calculating statistics'
            ], 500);
        }
    }

    /**
     * Calculate completion stats for filtered records
     */
    private function calculateFilteredCompletionStats($records): array
    {
        $total = $records->count();
        if ($total === 0) return ['completion_rate' => 0, 'complete_records' => 0];
        
        $complete = $records->filter(function ($record) {
            return !empty($record->blood_type) && 
                   !empty($record->height) && 
                   !empty($record->weight) && 
                   !empty($record->emergency_contact_name_1);
        })->count();
        
        return [
            'completion_rate' => round(($complete / $total) * 100, 1),
            'complete_records' => $complete,
            'incomplete_records' => $total - $complete
        ];
    }

    /**
     * Calculate health stats for filtered records
     */
    private function calculateFilteredHealthStats($records): array
    {
        return [
            'with_allergies' => $records->whereNotNull('allergies')->count(),
            'pwd_students' => $records->where('is_pwd', true)->count(),
            'on_maintenance_drugs' => $records->where('is_taking_maintenance_drugs', true)->count(),
            'recent_hospitalization' => $records->where('has_been_hospitalized_6_months', true)->count(),
            'high_risk' => $records->filter(function ($record) {
                return $this->calculateHealthRiskLevel($record) === 'High';
            })->count()
        ];
    }

    /**
     * Calculate demographics for filtered records
     */
    private function calculateFilteredDemographics($records): array
    {
        $users = $records->pluck('user');
        
        return [
            'by_course' => $users->whereNotNull('course')->groupBy('course')->map->count()->toArray(),
            'by_year_level' => $users->whereNotNull('year_level')->groupBy('year_level')->map->count()->toArray(),
            'by_gender' => $users->whereNotNull('gender')->groupBy('gender')->map->count()->toArray()
        ];
    }

    /**
     * Calculate vaccination stats for filtered records
     */
    private function calculateFilteredVaccinationStats($records): array
    {
        $total = $records->count();
        $vaccinated = $records->where('is_fully_vaccinated', true)->count();
        
        return [
            'total_records' => $total,
            'fully_vaccinated' => $vaccinated,
            'vaccination_rate' => $total > 0 ? round(($vaccinated / $total) * 100, 1) : 0,
            'vaccine_types' => $records->whereNotNull('vaccine_type')
                                     ->groupBy('vaccine_type')
                                     ->map->count()
                                     ->toArray()
        ];
    }

// Add these methods to your MedicalRecordController class

/**
 * Display medical records for nurses
 */
public function nurseIndex(Request $request): View
{
    // Use the existing index method with nurse context
    return $this->index($request);
}

/**
 * Show the form for editing a medical record (nurse view)
 */
public function nurseEdit(MedicalRecord $medicalRecord): View
{
    $this->authorize('update', $medicalRecord);
    
    // Load related data
    $medicalRecord->load(['user']);
    
    return view('nurse.medical-records.edit', compact('medicalRecord'));
}

/**
 * Store a newly created medical record (nurse version)
 */
public function nurseStore(Request $request): RedirectResponse
{
    // Use the existing store method
    return $this->store($request);
}

/**
 * Show for nurse specifically (with debug logging)
 */
public function nurseShow(MedicalRecord $medicalRecord): View
{
    $this->authorize('view', $medicalRecord);
    
    // Check if auto-created
    if ($medicalRecord->is_auto_created) {
        return redirect()->route('nurse.medical-records.index')
                       ->with('warning', 'This student has not completed their medical record yet.');
    }
    
    // Load related data
    $medicalRecord->load(['user', 'createdBy']);
    
    // DEBUG LOGGING
    Log::info('Nurse Medical Record Show', [
        'record_id' => $medicalRecord->id,
        'student_name' => $medicalRecord->user->full_name,
        'is_auto_created' => $medicalRecord->is_auto_created,
        'has_data' => !empty($medicalRecord->blood_type),
        'nurse' => Auth::user()->full_name
    ]);
    
    return view('nurse.medical-records.show', compact('medicalRecord'));
}

/**
 * Update the specified medical record (nurse version) - Update this too if used
 */
public function nurseUpdate(Request $request, MedicalRecord $medicalRecord): RedirectResponse
{
    $this->authorize('update', $medicalRecord);
    
    try {
        DB::beginTransaction();
        
        $validated = $this->validateMedicalRecord($request, false);
        
        // Handle boolean fields correctly using request->boolean()
        $booleanFields = [
            'has_been_pregnant',
            'has_undergone_surgery', 
            'is_taking_maintenance_drugs',
            'has_been_hospitalized_6_months',
            'is_pwd',
            'is_fully_vaccinated',
            'has_received_booster'
        ];
        
        foreach ($booleanFields as $field) {
            $validated[$field] = $request->boolean($field);
        }
        
        $validated = $this->sanitizeInput($validated);
        
        // Update the record
        $medicalRecord->update($validated);
        
        // Get changes from the model
        $changes = $medicalRecord->getChanges();
        
        // Log the update
        $this->logActivity('updated', $medicalRecord, [
            'changes' => $changes
        ]);
        
        DB::commit();
        
        return redirect()
            ->route('nurse.medical-records.index')
            ->with('success', 'Medical record updated successfully for ' . $medicalRecord->user->full_name . '.');
            
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error updating medical record: ' . $e->getMessage() . ' | Stack: ' . $e->getTraceAsString());
        
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Error updating medical record: ' . $e->getMessage());
    }
}

/**
 * Remove the specified medical record (nurse version)
 */
public function nurseDestroy(MedicalRecord $medicalRecord): RedirectResponse
{
    // Use the existing destroy method
    return $this->destroy($medicalRecord);
}
}