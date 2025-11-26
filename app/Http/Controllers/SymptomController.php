<?php

namespace App\Http\Controllers;

use App\Models\Symptom;
use App\Models\SymptomLog;
use App\Models\PossibleIllness;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SymptomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $symptoms = Symptom::orderBy('name')->get();
            
            // For admin/nurse views, include additional data
            if (Auth::user()->role === 'nurse' || Auth::user()->role === 'dean') {
                $symptoms = Symptom::withCount(['symptomLogs' => function($query) {
                    $query->where('created_at', '>=', now()->subMonth());
                }])->orderBy('name')->get();
                
                return view('symptoms.index', compact('symptoms'));
            }
            
            return view('symptoms.index', compact('symptoms'));
        } catch (\Exception $e) {
            Log::error('Error loading symptoms index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load symptoms list.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only nurses and deans can create symptoms
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }
        
        $categories = $this->getSymptomCategories();
        return view('symptoms.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only nurses and deans can create symptoms
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:symptoms',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:255',
            'severity_level' => 'nullable|in:low,medium,high',
            'is_emergency' => 'boolean',
            'related_illnesses' => 'nullable|array',
            'related_illnesses.*' => 'exists:possible_illnesses,id'
        ]);

        try {
            DB::beginTransaction();
            
            $symptom = Symptom::create([
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category,
                'severity_level' => $request->severity_level ?? 'medium',
                'is_emergency' => $request->is_emergency ?? false,
                'created_by' => Auth::id(),
            ]);

            // Sync related illnesses if provided
            if ($request->has('related_illnesses')) {
                $symptom->possibleIllnesses()->sync($request->related_illnesses);
            }

            DB::commit();

            return redirect()->route('symptoms.index')
                ->with('success', 'Symptom created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating symptom: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to create symptom. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Symptom $symptom)
    {
        try {
            $symptom->load(['possibleIllnesses', 'symptomLogs' => function($query) {
                $query->orderBy('created_at', 'desc')->take(10);
            }]);
            
            $usageStats = $this->getSymptomUsageStats($symptom);
            
            return view('symptoms.show', compact('symptom', 'usageStats'));
        } catch (\Exception $e) {
            Log::error('Error loading symptom: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load symptom details.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Symptom $symptom)
    {
        // Only nurses and deans can edit symptoms
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }
        
        $categories = $this->getSymptomCategories();
        $possibleIllnesses = PossibleIllness::orderBy('name')->get();
        
        return view('symptoms.edit', compact('symptom', 'categories', 'possibleIllnesses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Symptom $symptom)
    {
        // Only nurses and deans can update symptoms
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:symptoms,name,' . $symptom->id,
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:255',
            'severity_level' => 'nullable|in:low,medium,high',
            'is_emergency' => 'boolean',
            'related_illnesses' => 'nullable|array',
            'related_illnesses.*' => 'exists:possible_illnesses,id'
        ]);

        try {
            DB::beginTransaction();
            
            $symptom->update([
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category,
                'severity_level' => $request->severity_level ?? 'medium',
                'is_emergency' => $request->is_emergency ?? false,
                'updated_by' => Auth::id(),
            ]);

            // Sync related illnesses
            $symptom->possibleIllnesses()->sync($request->related_illnesses ?? []);

            DB::commit();

            return redirect()->route('symptoms.index')
                ->with('success', 'Symptom updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating symptom: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to update symptom. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Symptom $symptom)
    {
        // Only nurses and deans can delete symptoms
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Check if symptom is being used in logs
            $usageCount = SymptomLog::whereJsonContains('symptom_ids', $symptom->id)
                          ->orWhereJsonContains('symptoms', $symptom->name)
                          ->count();

            if ($usageCount > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete symptom. It is being used in ' . $usageCount . ' symptom logs.');
            }

            $symptom->delete();

            return redirect()->route('symptoms.index')
                ->with('success', 'Symptom deleted successfully.');
                
        } catch (\Exception $e) {
            Log::error('Error deleting symptom: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to delete symptom. Please try again.');
        }
    }

    /**
     * Search symptoms for autocomplete
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q');
            
            $symptoms = Symptom::where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->orderBy('name')
                ->limit(10)
                ->get(['id', 'name', 'category', 'severity_level']);

            return response()->json($symptoms);
        } catch (\Exception $e) {
            Log::error('Error searching symptoms: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * API index for symptoms
     */
    public function apiIndex()
    {
        try {
            $symptoms = Symptom::orderBy('name')->get(['id', 'name', 'category', 'severity_level', 'is_emergency']);
            return response()->json($symptoms);
        } catch (\Exception $e) {
            Log::error('Error in symptoms API: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * Symptom checker page - Redirect to SymptomCheckerController
     */
    public function checker()
    {
        try {
            // Redirect to the actual symptom checker
            return app(SymptomCheckerController::class)->index();
        } catch (\Exception $e) {
            Log::error('Error redirecting to symptom checker: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Unable to load symptom checker.');
        }
    }

    /**
     * Check symptoms - Redirect to SymptomCheckerController
     */
    public function checkSymptoms(Request $request)
    {
        try {
            // Redirect to the actual symptom check functionality
            return app(SymptomCheckerController::class)->check($request);
        } catch (\Exception $e) {
            Log::error('Error in symptom check: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Unable to process symptom check.');
        }
    }

    /**
     * Get symptom usage statistics
     */
    private function getSymptomUsageStats(Symptom $symptom)
    {
        try {
            $totalUsage = SymptomLog::whereJsonContains('symptom_ids', $symptom->id)
                           ->orWhereJsonContains('symptoms', $symptom->name)
                           ->count();

            $monthlyUsage = SymptomLog::where(function($query) use ($symptom) {
                                $query->whereJsonContains('symptom_ids', $symptom->id)
                                      ->orWhereJsonContains('symptoms', $symptom->name);
                            })
                            ->where('created_at', '>=', now()->subMonth())
                            ->count();

            $emergencyUsage = SymptomLog::where(function($query) use ($symptom) {
                                 $query->whereJsonContains('symptom_ids', $symptom->id)
                                       ->orWhereJsonContains('symptoms', $symptom->name);
                             })
                             ->where('is_emergency', true)
                             ->count();

            return [
                'total_usage' => $totalUsage,
                'monthly_usage' => $monthlyUsage,
                'emergency_usage' => $emergencyUsage,
                'usage_percentage' => $totalUsage > 0 ? round(($monthlyUsage / $totalUsage) * 100, 2) : 0
            ];
        } catch (\Exception $e) {
            Log::error('Error getting symptom usage stats: ' . $e->getMessage());
            return [
                'total_usage' => 0,
                'monthly_usage' => 0,
                'emergency_usage' => 0,
                'usage_percentage' => 0
            ];
        }
    }

    /**
     * Get symptom categories
     */
    private function getSymptomCategories()
    {
        return [
            'General' => 'General',
            'Respiratory' => 'Respiratory',
            'Cardiovascular' => 'Cardiovascular',
            'Neurological' => 'Neurological',
            'Gastrointestinal' => 'Gastrointestinal',
            'Musculoskeletal' => 'Musculoskeletal',
            'Dermatological' => 'Dermatological',
            'Psychological' => 'Psychological',
            'Ophthalmic' => 'Ophthalmic',
            'ENT' => 'ENT (Ear, Nose, Throat)',
            'Urological' => 'Urological',
            'Gynecological' => 'Gynecological',
            'Other' => 'Other'
        ];
    }

    /**
     * Bulk delete symptoms
     */
    public function bulkDestroy(Request $request)
    {
        // Only nurses and deans can bulk delete
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'symptom_ids' => 'required|array',
            'symptom_ids.*' => 'exists:symptoms,id'
        ]);

        try {
            DB::beginTransaction();
            
            $deletedCount = 0;
            $failedCount = 0;

            foreach ($request->symptom_ids as $symptomId) {
                $symptom = Symptom::findOrFail($symptomId);
                
                // Check if symptom is being used
                $usageCount = SymptomLog::whereJsonContains('symptom_ids', $symptom->id)
                              ->orWhereJsonContains('symptoms', $symptom->name)
                              ->count();

                if ($usageCount === 0) {
                    $symptom->delete();
                    $deletedCount++;
                } else {
                    $failedCount++;
                }
            }

            DB::commit();

            $message = "Deleted {$deletedCount} symptoms successfully.";
            if ($failedCount > 0) {
                $message .= " {$failedCount} symptoms could not be deleted as they are in use.";
            }

            return redirect()->route('symptoms.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk delete symptoms: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to delete symptoms. Please try again.');
        }
    }

    /**
     * Export symptoms
     */
    public function export(Request $request)
    {
        // Only nurses and deans can export
        if (!in_array(Auth::user()->role, ['nurse', 'dean'])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $format = $request->get('format', 'csv');
            $symptoms = Symptom::withCount(['symptomLogs as recent_usage' => function($query) {
                $query->where('created_at', '>=', now()->subMonth());
            }])->orderBy('name')->get();

            if ($format === 'csv') {
                return $this->exportToCsv($symptoms);
            } elseif ($format === 'excel') {
                return $this->exportToExcel($symptoms);
            }

            return redirect()->back()->with('error', 'Invalid export format.');
        } catch (\Exception $e) {
            Log::error('Error exporting symptoms: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to export symptoms.');
        }
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($symptoms)
    {
        $filename = 'symptoms-export-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function() use ($symptoms) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            fputcsv($file, [
                'ID', 'Name', 'Description', 'Category', 'Severity Level',
                'Emergency', 'Recent Usage', 'Created At', 'Updated At'
            ]);

            foreach ($symptoms as $symptom) {
                fputcsv($file, [
                    $symptom->id,
                    $symptom->name,
                    $symptom->description ?? '',
                    $symptom->category,
                    $symptom->severity_level,
                    $symptom->is_emergency ? 'Yes' : 'No',
                    $symptom->recent_usage,
                    $symptom->created_at->format('Y-m-d H:i:s'),
                    $symptom->updated_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel (using CSV as fallback)
     */
    private function exportToExcel($symptoms)
    {
        return $this->exportToCsv($symptoms);
    }
}