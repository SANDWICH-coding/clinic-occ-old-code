<?php

namespace App\Http\Controllers;

use App\Models\Symptom;
use App\Models\PossibleIllness;
use Illuminate\Http\Request;

class MedicalDataManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:nurse');
    }

    /**
     * Display unified listing page
     */
    public function index()
    {
        $symptomsQuery = Symptom::withCount('possibleIllnesses');
        if (request()->filled('search_symptom')) {
            $symptomsQuery->where('name', 'LIKE', '%' . request('search_symptom') . '%');
        }
        $symptoms = $symptomsQuery->orderBy('name')->paginate(15, ['*'], 'symptoms_page');

        $illnessesQuery = PossibleIllness::withCount('symptoms');
        if (request()->filled('search_illness')) {
            $illnessesQuery->where('name', 'LIKE', '%' . request('search_illness') . '%');
        }
        $illnesses = $illnessesQuery->orderBy('name')->paginate(15, ['*'], 'illnesses_page');

        return view('nurse.medical-data.index', compact('symptoms', 'illnesses'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        // Load both symptoms and illnesses for the dynamic form
        $symptoms = Symptom::orderBy('name')->get();
        $illnesses = PossibleIllness::orderBy('name')->get();
        
        // For backward compatibility, set relatedItems based on type parameter if present
        $type = request('type', null);
        $relatedItems = $type === 'illness' ? $symptoms : $illnesses;
        
        return view('nurse.medical-data.create', compact('symptoms', 'illnesses', 'relatedItems', 'type'));
    }

    /**
     * Store new item
     */
    public function store(Request $request)
    {
        $type = $request->input('type');
        
        // Validate type is provided
        $request->validate([
            'type' => 'required|in:symptom,illness'
        ]);
        
        if ($type === 'symptom') {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:symptoms,name',
                'related_items' => 'nullable|array',
                'related_items.*' => 'exists:possible_illnesses,id'
            ]);

            $item = Symptom::create(['name' => $validated['name']]);
            
            if (!empty($validated['related_items'])) {
                $item->possibleIllnesses()->attach($validated['related_items']);
            }
            
            $message = 'Symptom created successfully.';
            $tab = 'symptoms';
        } else {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:possible_illnesses,name',
                'related_items' => 'nullable|array',
                'related_items.*' => 'exists:symptoms,id'
            ]);

            $item = PossibleIllness::create(['name' => $validated['name']]);
            
            if (!empty($validated['related_items'])) {
                $item->symptoms()->attach($validated['related_items']);
            }
            
            $message = 'Illness created successfully.';
            $tab = 'illnesses';
        }

        return redirect()->route('nurse.medical-data.index', ['tab' => $tab])
            ->with('success', $message);
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $type = request('type', 'symptom');
        
        if ($type === 'symptom') {
            $item = Symptom::findOrFail($id);
            $relatedItems = PossibleIllness::orderBy('name')->get();
            $selectedItems = $item->possibleIllnesses->pluck('id')->toArray();
        } else {
            $item = PossibleIllness::findOrFail($id);
            $relatedItems = Symptom::orderBy('name')->get();
            $selectedItems = $item->symptoms->pluck('id')->toArray();
        }
        
        return view('nurse.medical-data.edit', compact('type', 'item', 'relatedItems', 'selectedItems'));
    }

    /**
     * Update item
     */
    public function update(Request $request, $id)
    {
        $type = $request->input('type');
        
        // Validate type is provided
        $request->validate([
            'type' => 'required|in:symptom,illness'
        ]);
        
        if ($type === 'symptom') {
            $item = Symptom::findOrFail($id);
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:symptoms,name,' . $id,
                'related_items' => 'nullable|array',
                'related_items.*' => 'exists:possible_illnesses,id'
            ]);

            $item->update(['name' => $validated['name']]);
            $item->possibleIllnesses()->sync($validated['related_items'] ?? []);
            
            $message = 'Symptom updated successfully.';
            $tab = 'symptoms';
        } else {
            $item = PossibleIllness::findOrFail($id);
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:possible_illnesses,name,' . $id,
                'related_items' => 'nullable|array',
                'related_items.*' => 'exists:symptoms,id'
            ]);

            $item->update(['name' => $validated['name']]);
            $item->symptoms()->sync($validated['related_items'] ?? []);
            
            $message = 'Illness updated successfully.';
            $tab = 'illnesses';
        }

        return redirect()->route('nurse.medical-data.index', ['tab' => $tab])
            ->with('success', $message);
    }

    /**
     * Show item details
     */
    public function show($id)
    {
        $type = request('type', 'symptom');
        
        if ($type === 'symptom') {
            $item = Symptom::with('possibleIllnesses')->findOrFail($id);
        } else {
            $item = PossibleIllness::with('symptoms')->findOrFail($id);
        }
        
        return view('nurse.medical-data.show', compact('type', 'item'));
    }

    /**
     * Delete item
     */
    public function destroy($id)
    {
        $type = request('type', 'symptom');
        
        if ($type === 'symptom') {
            $item = Symptom::findOrFail($id);
            $message = 'Symptom deleted successfully.';
            $tab = 'symptoms';
        } else {
            $item = PossibleIllness::findOrFail($id);
            $message = 'Illness deleted successfully.';
            $tab = 'illnesses';
        }
        
        $item->delete();

        return redirect()->route('nurse.medical-data.index', ['tab' => $tab])
            ->with('success', $message);
    }
}