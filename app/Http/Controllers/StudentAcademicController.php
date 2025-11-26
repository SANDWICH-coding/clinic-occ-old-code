<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class StudentAcademicController extends Controller
{
    /**
     * Show academic info update form
     */
    public function showUpdateForm()
    {
        $user = Auth::user();
        
        if (!$user->isStudent()) {
            abort(403, 'Unauthorized action.');
        }

        // FIXED: Added 'student.' prefix to match the file location
        return view('student.Academic-Info', compact('user'));
    }

    /**
     * Update student academic information
     */
    public function updateAcademicInfo(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isStudent()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'course' => 'required|string|in:BSIT,BSBA,BSBA-MM,BSBA-FM,BSED,BEED',
            'year_level' => 'required|string|in:1st year,2nd year,3rd year,4th year',
            'section' => 'nullable|string|max:10',
        ]);

        try {
            // Use the updateAcademicInfo method from User model
            $user->updateAcademicInfo(
                $validated['year_level'],
                $validated['section'],
                $validated['course']
            );
            
            // Check if it's an AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Academic information updated successfully!',
                    'user' => [
                        'course' => $user->course,
                        'year_level' => $user->year_level,
                        'section' => $user->section,
                        'academic_year' => $user->academic_year,
                        'year_level_updated_at' => $user->year_level_updated_at?->format('M j, Y'),
                    ]
                ]);
            }
            
            // Redirect back to the academic info page with success message
            return redirect()->route('student.academic-info')
                ->with('success', 'Academic information updated successfully!');
                
        } catch (\Exception $e) {
            Log::error('Academic info update error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update academic information. Please try again.'
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to update academic information. Please try again.')
                ->withInput();
        }
    }

    /**
     * Bulk update students for new academic year (Admin/Dean function)
     */
    public function bulkUpdateAcademicInfo(Request $request)
    {
        // Authorization check - for admin/dean only
        if (!Gate::allows('manage-academic-info')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id,role,student',
            'new_sections' => 'sometimes|array',
            'new_sections.*' => 'nullable|string|max:10',
            'year_levels' => 'sometimes|array',
            'year_levels.*' => 'nullable|in:1st year,2nd year,3rd year,4th year',
            'courses' => 'sometimes|array',
            'courses.*' => 'nullable|in:BSIT,BSBA,BSBA-MM,BSBA-FM,BSED,BEED',
        ]);

        $updatedCount = 0;
        $errors = [];
        
        foreach ($validated['student_ids'] as $index => $studentId) {
            try {
                $student = User::findOrFail($studentId);
                
                $yearLevel = $validated['year_levels'][$index] ?? null;
                $newSection = $validated['new_sections'][$index] ?? null;
                $course = $validated['courses'][$index] ?? null;
                
                if ($student->updateAcademicInfo($yearLevel, $newSection, $course)) {
                    $updatedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "Failed to update student ID {$studentId}: " . $e->getMessage();
                Log::error("Bulk update error for student {$studentId}: " . $e->getMessage());
            }
        }

        $response = redirect()->back();
        
        if ($updatedCount > 0) {
            $response->with('success', "Successfully updated {$updatedCount} student(s).");
        }
        
        if (!empty($errors)) {
            $response->with('errors', $errors);
        }

        return $response;
    }

    /**
     * Get all students for academic management (Admin/Dean view)
     */
    public function getStudentsForManagement(Request $request)
    {
        // Authorization check - for admin/dean only
        if (!Gate::allows('manage-academic-info')) {
            abort(403, 'Unauthorized action.');
        }

        $query = User::where('role', 'student')
            ->with(['medicalRecord']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by course
        if ($request->has('course') && $request->course) {
            $query->where('course', $request->course);
        }

        // Filter by year level
        if ($request->has('year_level') && $request->year_level) {
            $query->where('year_level', $request->year_level);
        }

        // Filter by section
        if ($request->has('section') && $request->section) {
            $query->where('section', $request->section);
        }

        $students = $query->orderBy('course')
            ->orderBy('year_level')
            ->orderBy('section')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20);

        $courseOptions = User::getCourseOptions();
        $yearLevelOptions = User::getYearLevelOptions();

        return view('admin.students-academic-management', compact('students', 'courseOptions', 'yearLevelOptions'));
    }

    /**
     * Show academic details for a specific student
     */
    public function showAcademicDetails(User $student)
    {
        if (!Gate::allows('manage-academic-info')) {
            abort(403, 'Unauthorized action.');
        }

        if (!$student->isStudent()) {
            abort(404, 'Student not found.');
        }

        return view('admin.student-academic-details', compact('student'));
    }

    /**
     * Update individual student academic info (Admin/Dean function)
     */
    public function updateStudentAcademicInfo(Request $request, User $student)
    {
        if (!Gate::allows('manage-academic-info')) {
            abort(403, 'Unauthorized action.');
        }

        if (!$student->isStudent()) {
            abort(404, 'Student not found.');
        }

        $validated = $request->validate([
            'course' => 'required|string|in:BSIT,BSBA,BSBA-MM,BSBA-FM,BSED,BEED',
            'year_level' => 'required|string|in:1st year,2nd year,3rd year,4th year',
            'section' => 'nullable|string|max:10',
            'academic_year' => 'nullable|integer|min:2020|max:2030',
        ]);

        try {
            $student->update([
                'course' => $validated['course'],
                'year_level' => $validated['year_level'],
                'section' => $validated['section'],
                'academic_year' => $validated['academic_year'] ?? $student->getCurrentAcademicYear(),
                'year_level_updated_at' => now(),
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student academic information updated successfully!'
                ]);
            }
            
            return redirect()->back()
                ->with('success', 'Student academic information updated successfully!');
                
        } catch (\Exception $e) {
            Log::error('Student academic info update error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update student academic information. Please try again.'
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to update student academic information. Please try again.')
                ->withInput();
        }
    }

    /**
     * Get students needing year level updates
     */
    public function getStudentsNeedingUpdates(Request $request)
    {
        if (!Gate::allows('manage-academic-info')) {
            abort(403, 'Unauthorized action.');
        }

        $currentAcademicYear = (new User())->getCurrentAcademicYear();
        
        $query = User::where('role', 'student')
            ->where(function($query) use ($currentAcademicYear) {
                $query->where('academic_year', '<', $currentAcademicYear)
                      ->orWhereNull('academic_year')
                      ->orWhere('year_level', '')
                      ->orWhereNull('year_level');
            });

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('course')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20);

        return view('admin.students-needing-updates', compact('students', 'currentAcademicYear'));
    }

    /**
     * Export students academic data
     */
    public function exportAcademicData(Request $request)
    {
        if (!Gate::allows('manage-academic-info')) {
            abort(403, 'Unauthorized action.');
        }

        $students = User::where('role', 'student')
            ->when($request->course, function($query, $course) {
                return $query->where('course', $course);
            })
            ->when($request->year_level, function($query, $yearLevel) {
                return $query->where('year_level', $yearLevel);
            })
            ->orderBy('course')
            ->orderBy('year_level')
            ->orderBy('section')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'student_id', 'first_name', 'last_name', 'email', 'course', 'year_level', 'section', 'academic_year', 'year_level_updated_at']);

        // For now, return JSON response - you can implement CSV or Excel export later
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'students' => $students,
                'total' => $students->count(),
                'filters' => $request->all(),
                'exported_at' => now()->toDateTimeString()
            ]);
        }

        // You can implement CSV download here
        return response()->streamDownload(function () use ($students) {
            $handle = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($handle, ['Student ID', 'Name', 'Email', 'Course', 'Year Level', 'Section', 'Academic Year', 'Last Updated']);
            
            // Add data rows
            foreach ($students as $student) {
                fputcsv($handle, [
                    $student->student_id,
                    $student->first_name . ' ' . $student->last_name,
                    $student->email,
                    $student->course,
                    $student->year_level,
                    $student->section,
                    $student->academic_year ? $student->academic_year . '-' . ($student->academic_year + 1) : 'Not set',
                    $student->year_level_updated_at?->format('M j, Y') ?? 'Never'
                ]);
            }
            
            fclose($handle);
        }, 'students_academic_data_' . now()->format('Y-m-d') . '.csv');
    }

    /**
     * Get academic statistics
     */
    public function getAcademicStatistics()
    {
        if (!Gate::allows('manage-academic-info')) {
            abort(403, 'Unauthorized action.');
        }

        $stats = [
            'total_students' => User::where('role', 'student')->count(),
            'by_course' => User::where('role', 'student')
                ->selectRaw('course, COUNT(*) as count')
                ->groupBy('course')
                ->pluck('count', 'course')
                ->toArray(),
            'by_year_level' => User::where('role', 'student')
                ->selectRaw('year_level, COUNT(*) as count')
                ->groupBy('year_level')
                ->pluck('count', 'year_level')
                ->toArray(),
            'needs_update' => User::where('role', 'student')
                ->where(function($query) {
                    $currentYear = (new User())->getCurrentAcademicYear();
                    $query->where('academic_year', '<', $currentYear)
                          ->orWhereNull('academic_year');
                })
                ->count(),
            'recently_updated' => User::where('role', 'student')
                ->where('year_level_updated_at', '>=', now()->subDays(30))
                ->count(),
        ];

        if (request()->ajax()) {
            return response()->json($stats);
        }

        return view('admin.academic-statistics', compact('stats'));
    }

    /**
     * Auto-update academic year for all students
     */
    public function autoUpdateAcademicYear()
    {
        if (!Gate::allows('manage-academic-info')) {
            abort(403, 'Unauthorized action.');
        }

        $currentAcademicYear = (new User())->getCurrentAcademicYear();
        $updatedCount = 0;

        try {
            $students = User::where('role', 'student')
                ->where('academic_year', '<', $currentAcademicYear)
                ->get();

            foreach ($students as $student) {
                $student->update([
                    'academic_year' => $currentAcademicYear,
                    'year_level_updated_at' => now(),
                ]);
                $updatedCount++;
            }

            return redirect()->back()
                ->with('success', "Automatically updated academic year for {$updatedCount} student(s) to {$currentAcademicYear}.");

        } catch (\Exception $e) {
            Log::error('Auto update academic year error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to auto-update academic years. Please try again.');
        }
    }
}