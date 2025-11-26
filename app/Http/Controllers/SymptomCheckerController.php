<?php

namespace App\Http\Controllers;

use App\Models\Symptom;
use App\Models\PossibleIllness;
use App\Models\SymptomLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class SymptomCheckerController extends Controller
{
    /**
     * Constructor with middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:student'); // Ensure only students can access
    }

    /**
     * Display the symptom checker form
     */
    public function index()
    {
        // Cache symptoms for better performance
        $symptoms = Cache::remember('symptoms_list', 3600, function () {
            return Symptom::orderBy('name')->get();
        });
        
        return view('symptom-checker.index', compact('symptoms'));
    }
    
    /**
     * Process the symptom check and redirect to appointment creation
     */
    public function check(Request $request)
    {
        $request->validate([
            'symptoms' => 'required|array|min:1|max:10',
            'symptoms.*' => 'exists:symptoms,id'
        ]);
        
        $selectedSymptomIds = $request->symptoms;
        
        // Validate that symptoms actually exist
        $existingSymptomsCount = Symptom::whereIn('id', $selectedSymptomIds)->count();
        if ($existingSymptomsCount !== count($selectedSymptomIds)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['symptoms' => 'One or more selected symptoms are invalid.']);
        }
        
        $selectedSymptoms = Symptom::whereIn('id', $selectedSymptomIds)->get();
        
        // Find possible illnesses
        $possibleIllnesses = $this->findPossibleIllnesses($selectedSymptomIds);
        
        // Check for emergency symptoms
        $isEmergency = $this->checkForEmergencySymptoms($selectedSymptomIds);
        
        // Log the symptom check
        try {
            $symptomLog = SymptomLog::create([
                'user_id' => Auth::id(),
                'student_id' => Auth::user()->student_id,
                'symptoms' => $selectedSymptoms->pluck('name')->toArray(),
                'possible_illnesses' => $possibleIllnesses->pluck('name')->toArray(),
                'is_emergency' => $isEmergency,
                'logged_at' => now(),
                'symptom_ids' => $selectedSymptomIds,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create symptom log: ' . $e->getMessage());
        }
        
        // 🔄 NEW: Store symptom data in session for appointment creation
        $symptomData = [
            'id' => $symptomLog->id ?? null,
            'symptoms' => $selectedSymptoms->pluck('name')->toArray(),
            'possible_illnesses' => $possibleIllnesses->pluck('name')->toArray(),
            'is_emergency' => $isEmergency,
            'created_at' => now()->format('M j, Y g:i A'),
            'symptom_ids' => $selectedSymptomIds
        ];
        
        Session::put('current_symptom_check', $symptomData);
        
        // 🔄 NEW: Redirect to appointment creation instead of showing results
        return redirect()->route('student.appointments.create')
            ->with('success', 'Symptom analysis completed! Your symptoms have been pre-filled in the appointment form.')
            ->with('symptom_data', $symptomData);
    }
    
    /**
     * Improved possible illnesses finder with better matching algorithm
     */
    private function findPossibleIllnesses(array $symptomIds)
    {
        $selectedSymptomCount = count($symptomIds);
        
        // Create cache key based on symptoms for performance
        $cacheKey = 'illnesses_match_' . md5(implode(',', $symptomIds));
        
        return Cache::remember($cacheKey, 1800, function () use ($symptomIds, $selectedSymptomCount) {
            // Get all possible illnesses with their symptoms
            $possibleIllnesses = PossibleIllness::with(['symptoms' => function($query) use ($symptomIds) {
                $query->select('symptoms.id', 'name');
            }])->get();

            $matchedIllnesses = $possibleIllnesses->map(function($illness) use ($symptomIds, $selectedSymptomCount) {
                $illnessSymptomIds = $illness->symptoms->pluck('id')->toArray();
                $matchingSymptoms = array_intersect($symptomIds, $illnessSymptomIds);
                $matchCount = count($matchingSymptoms);
                $totalSymptoms = count($illnessSymptomIds);
                
                return [
                    'illness' => $illness,
                    'match_count' => $matchCount,
                    'total_symptoms' => $totalSymptoms,
                    'match_percentage' => $totalSymptoms > 0 ? ($matchCount / $totalSymptoms) * 100 : 0,
                    'symptom_coverage' => $selectedSymptomCount > 0 ? ($matchCount / $selectedSymptomCount) * 100 : 0
                ];
            })->filter(function($result) use ($selectedSymptomCount) {
                // Filter conditions based on match quality
                if ($result['match_count'] === 0) {
                    return false;
                }
                
                // Dynamic threshold based on number of selected symptoms
                if ($selectedSymptomCount === 1) {
                    return $result['match_count'] >= 1;
                } elseif ($selectedSymptomCount <= 3) {
                    return $result['match_percentage'] >= 25 || $result['symptom_coverage'] >= 50;
                } else {
                    return $result['match_percentage'] >= 30 || $result['symptom_coverage'] >= 40;
                }
            })->sortByDesc(function($result) {
                // Weighted scoring: 60% match percentage + 40% symptom coverage
                $score = ($result['match_percentage'] * 0.6) + ($result['symptom_coverage'] * 0.4);
                return $score;
            });

            // If we have good matches, return top 5
            if ($matchedIllnesses->count() >= 3) {
                return $matchedIllnesses->take(5)->pluck('illness');
            }

            // If few matches, get all illnesses with any matching symptoms
            $anyMatches = $matchedIllnesses->count() > 0 ? 
                $matchedIllnesses->pluck('illness') : 
                $possibleIllnesses->filter(function($illness) use ($symptomIds) {
                    $illnessSymptomIds = $illness->symptoms->pluck('id')->toArray();
                    return count(array_intersect($symptomIds, $illnessSymptomIds)) > 0;
                })->sortByDesc(function($illness) use ($symptomIds) {
                    $illnessSymptomIds = $illness->symptoms->pluck('id')->toArray();
                    return count(array_intersect($symptomIds, $illnessSymptomIds));
                });

            // If still no matches, return most common general conditions
            if ($anyMatches->count() == 0) {
                return PossibleIllness::whereIn('name', [
                    'Stress', 'Minor Viral Infection', 'General Malaise', 'Fatigue Syndrome', 'Sleep Deprivation'
                ])->take(3)->get();
            }

            return $anyMatches->take(5);
        });
    }
    
    /**
     * Enhanced emergency symptoms check with severity levels
     */
    private function checkForEmergencySymptoms(array $symptomIds): bool
    {
        $emergencySymptoms = [
            // High severity symptoms
            'Chest Pain' => 'high',
            'Shortness of Breath' => 'high', 
            'Difficulty Breathing' => 'high',
            'Severe Bleeding' => 'high',
            'Loss of Consciousness' => 'high',
            
            // Medium severity symptoms
            'Fainting' => 'medium',
            'Severe Headache' => 'medium',
            'Blurred Vision' => 'medium',
            'Rapid Heartbeat' => 'medium',
            'Difficulty Swallowing' => 'medium',
            
            // Additional emergency symptoms
            'Sudden Numbness' => 'high',
            'Slurred Speech' => 'high',
            'Severe Abdominal Pain' => 'medium',
            'High Fever' => 'medium'
        ];
        
        $emergencySymptomNames = array_keys($emergencySymptoms);
        
        return Symptom::whereIn('id', $symptomIds)
            ->whereIn('name', $emergencySymptomNames)
            ->exists();
    }
    
    /**
     * Get emergency severity level for a symptom
     */
    private function getEmergencySeverity(string $symptomName): string
    {
        $emergencySymptoms = [
            'Chest Pain' => 'high',
            'Shortness of Breath' => 'high',
            'Difficulty Breathing' => 'high',
            'Severe Bleeding' => 'high',
            'Loss of Consciousness' => 'high',
            'Fainting' => 'medium',
            'Severe Headache' => 'medium',
            'Blurred Vision' => 'medium',
            'Rapid Heartbeat' => 'medium',
            'Difficulty Swallowing' => 'medium',
            'Sudden Numbness' => 'high',
            'Slurred Speech' => 'high',
            'Severe Abdominal Pain' => 'medium',
            'High Fever' => 'medium'
        ];
        
        return $emergencySymptoms[$symptomName] ?? 'low';
    }
    
    /**
     * Display symptom check history with search
     */
    public function history(Request $request)
    {
        $query = SymptomLog::where('user_id', Auth::id());
        
        // Add search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('symptoms', 'like', "%{$searchTerm}%")
                  ->orWhere('possible_illnesses', 'like', "%{$searchTerm}%");
            });
        }
        
        // Filter by emergency only
        if ($request->has('emergency_only')) {
            $query->where('is_emergency', true);
        }
        
        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
            
        return view('symptom-checker.history', compact('logs'));
    }
    
    /**
     * Show individual symptom log
     */
    public function showLog($id)
    {
        $log = SymptomLog::where('user_id', Auth::id())->findOrFail($id);
        
        // Convert JSON strings to arrays for better display
        $log->symptoms = is_array($log->symptoms) ? $log->symptoms : json_decode($log->symptoms, true);
        $log->possible_illnesses = is_array($log->possible_illnesses) ? $log->possible_illnesses : json_decode($log->possible_illnesses, true);
        
        return view('symptom-checker.show-log', compact('log'));
    }
    
    /**
     * Delete a symptom log
     */
    public function destroyLog($id)
    {
        $log = SymptomLog::where('user_id', Auth::id())->findOrFail($id);
        $log->delete();
        
        return redirect()->route('symptom-checker.history')
            ->with('success', 'Symptom log deleted successfully.');
    }
}