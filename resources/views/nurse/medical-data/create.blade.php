{{-- resources/views/nurse/medical-data/create.blade.php --}}
@extends('layouts.nurse-app')

@section('title', 'Create Medical Data')

@section('content')
<div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 md:p-8">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-plus-circle text-3xl"></i>
                        <h1 class="text-3xl font-semibold">Create New Entry</h1>
                    </div>
                    <a href="{{ route('nurse.medical-data.index') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-100">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>

            {{-- Form --}}
            <div class="p-6 md:p-8">
                @if($errors->any())
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-exclamation-triangle mr-3 text-xl"></i>
                            <p class="font-bold">Please fix the following errors:</p>
                        </div>
                        <ul class="list-disc list-inside ml-8">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('nurse.medical-data.store') }}" method="POST" id="createForm">
                    @csrf

                    {{-- Type Selection --}}
                    <div class="mb-6 bg-gray-50 p-6 rounded-lg border-2 border-gray-300">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            What would you like to create? <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-4">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="type" value="symptom" class="sr-only peer" 
                                       {{ old('type') === 'symptom' ? 'checked' : '' }} 
                                       onchange="updateFormType('symptom')" required>
                                <div class="p-4 border-2 border-gray-300 rounded-lg peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:bg-gray-100 transition">
                                    <div class="flex items-center justify-center mb-2">
                                        <i class="fas fa-heartbeat text-3xl text-blue-600"></i>
                                    </div>
                                    <p class="text-center font-semibold text-gray-800">Symptom</p>
                                    <p class="text-center text-sm text-gray-500">e.g., Headache, Fever</p>
                                </div>
                            </label>
                            
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="type" value="illness" class="sr-only peer" 
                                       {{ old('type') === 'illness' ? 'checked' : '' }} 
                                       onchange="updateFormType('illness')" required>
                                <div class="p-4 border-2 border-gray-300 rounded-lg peer-checked:border-purple-600 peer-checked:bg-purple-50 hover:bg-gray-100 transition">
                                    <div class="flex items-center justify-center mb-2">
                                        <i class="fas fa-stethoscope text-3xl text-purple-600"></i>
                                    </div>
                                    <p class="text-center font-semibold text-gray-800">Illness</p>
                                    <p class="text-center text-sm text-gray-500">e.g., Common Cold, Flu</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Name Field --}}
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span id="nameLabel">Name</span> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter name"
                               required>
                    </div>

                    {{-- Related Items - When SYMPTOM is selected, show ILLNESSES --}}
                    <div class="mb-6 hidden" id="illnessesSection">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Associated Illnesses (Optional)
                        </label>
                        <p class="text-sm text-gray-500 mb-3">Select illnesses commonly associated with this symptom</p>
                        
                        @if(isset($illnesses) && $illnesses->count() > 0)
                            <div class="max-h-64 overflow-y-auto border border-gray-300 rounded-lg p-4 bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($illnesses as $illness)
                                        <label class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded cursor-pointer">
                                            <input type="checkbox" 
                                                   name="related_items[]" 
                                                   value="{{ $illness->id }}"
                                                   {{ in_array($illness->id, old('related_items', [])) ? 'checked' : '' }}
                                                   class="form-checkbox h-5 w-5 text-blue-600 rounded illness-checkbox">
                                            <span class="text-gray-700">{{ $illness->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-yellow-800">No illnesses available. Create an illness first.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Related Items - When ILLNESS is selected, show SYMPTOMS --}}
                    <div class="mb-6 hidden" id="symptomsSection">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Associated Symptoms (Optional)
                        </label>
                        <p class="text-sm text-gray-500 mb-3">Select symptoms commonly associated with this illness</p>
                        
                        @if(isset($symptoms) && $symptoms->count() > 0)
                            <div class="max-h-64 overflow-y-auto border border-gray-300 rounded-lg p-4 bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($symptoms as $symptom)
                                        <label class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded cursor-pointer">
                                            <input type="checkbox" 
                                                   name="related_items[]" 
                                                   value="{{ $symptom->id }}"
                                                   {{ in_array($symptom->id, old('related_items', [])) ? 'checked' : '' }}
                                                   class="form-checkbox h-5 w-5 text-purple-600 rounded symptom-checkbox">
                                            <span class="text-gray-700">{{ $symptom->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-yellow-800">No symptoms available. Create a symptom first.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
                        <button type="submit" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700">
                            <i class="fas fa-save mr-2"></i> <span id="submitText">Create Entry</span>
                        </button>
                        <a href="{{ route('nurse.medical-data.index') }}" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 text-center">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateFormType(type) {
    const nameLabel = document.getElementById('nameLabel');
    const submitText = document.getElementById('submitText');
    const illnessesSection = document.getElementById('illnessesSection');
    const symptomsSection = document.getElementById('symptomsSection');
    const nameInput = document.getElementById('name');
    
    // Clear all checkboxes when switching types
    document.querySelectorAll('input[name="related_items[]"]').forEach(cb => {
        cb.checked = false;
    });
    
    if (type === 'symptom') {
        nameLabel.textContent = 'Symptom Name';
        submitText.textContent = 'Create Symptom';
        nameInput.placeholder = 'e.g., Headache, Fever, Cough';
        illnessesSection.classList.remove('hidden');
        symptomsSection.classList.add('hidden');
        
        // Enable illness checkboxes, disable symptom checkboxes
        document.querySelectorAll('.illness-checkbox').forEach(cb => cb.disabled = false);
        document.querySelectorAll('.symptom-checkbox').forEach(cb => cb.disabled = true);
    } else {
        nameLabel.textContent = 'Illness Name';
        submitText.textContent = 'Create Illness';
        nameInput.placeholder = 'e.g., Common Cold, Flu, Migraine';
        symptomsSection.classList.remove('hidden');
        illnessesSection.classList.add('hidden');
        
        // Enable symptom checkboxes, disable illness checkboxes
        document.querySelectorAll('.symptom-checkbox').forEach(cb => cb.disabled = false);
        document.querySelectorAll('.illness-checkbox').forEach(cb => cb.disabled = true);
    }
}

// Initialize on page load if type is already selected (for validation errors)
document.addEventListener('DOMContentLoaded', function() {
    const selectedType = document.querySelector('input[name="type"]:checked');
    if (selectedType) {
        updateFormType(selectedType.value);
    }
});
</script>
@endpush
@endsection