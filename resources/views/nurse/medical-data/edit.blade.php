{{-- resources/views/nurse/medical-data/edit.blade.php --}}
@extends('layouts.nurse-app')

@section('title', 'Edit ' . ucfirst($type))

@section('content')
<div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            {{-- Header --}}
            <div class="bg-{{ $type === 'symptom' ? 'blue' : 'purple' }}-600 text-white p-6 md:p-8 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-edit text-3xl"></i>
                    <h1 class="text-3xl font-semibold">Edit {{ ucfirst($type) }}</h1>
                </div>
                <a href="{{ route('nurse.medical-data.index', ['tab' => $type === 'symptom' ? 'symptoms' : 'illnesses']) }}" 
                   class="bg-white text-{{ $type === 'symptom' ? 'blue' : 'purple' }}-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-100">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
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

                <form action="{{ route('nurse.medical-data.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="type" value="{{ $type }}">

                    {{-- Name Field --}}
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ ucfirst($type) }} Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $item->name) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-{{ $type === 'symptom' ? 'blue' : 'purple' }}-500"
                               placeholder="e.g., {{ $type === 'symptom' ? 'Headache, Fever, Cough' : 'Common Cold, Flu, Migraine' }}"
                               required>
                    </div>

                    {{-- Related Items --}}
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Associated {{ $type === 'symptom' ? 'Illnesses' : 'Symptoms' }} (Optional)
                        </label>
                        <p class="text-sm text-gray-500 mb-3">
                            Select {{ $type === 'symptom' ? 'illnesses' : 'symptoms' }} that are commonly associated
                        </p>
                        
                        @if($relatedItems->count() > 0)
                            <div class="max-h-64 overflow-y-auto border border-gray-300 rounded-lg p-4 bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($relatedItems as $relItem)
                                        <label class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded cursor-pointer">
                                            <input type="checkbox" 
                                                   name="related_items[]" 
                                                   value="{{ $relItem->id }}"
                                                   {{ in_array($relItem->id, old('related_items', $selectedItems)) ? 'checked' : '' }}
                                                   class="form-checkbox h-5 w-5 text-{{ $type === 'symptom' ? 'blue' : 'purple' }}-600 rounded">
                                            <span class="text-gray-700">{{ $relItem->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-yellow-800">
                                    No {{ $type === 'symptom' ? 'illnesses' : 'symptoms' }} available. 
                                    <a href="{{ route('nurse.medical-data.create', ['type' => $type === 'symptom' ? 'illness' : 'symptom']) }}" 
                                       class="text-{{ $type === 'symptom' ? 'blue' : 'purple' }}-600 hover:underline">
                                        Create {{ $type === 'symptom' ? 'an illness' : 'a symptom' }} first
                                    </a>.
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
                        <button type="submit" class="bg-{{ $type === 'symptom' ? 'blue' : 'purple' }}-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-{{ $type === 'symptom' ? 'blue' : 'purple' }}-700">
                            <i class="fas fa-save mr-2"></i> Update {{ ucfirst($type) }}
                        </button>
                        <a href="{{ route('nurse.medical-data.index', ['tab' => $type === 'symptom' ? 'symptoms' : 'illnesses']) }}" 
                           class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 text-center">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                        <form action="{{ route('nurse.medical-data.destroy', $item->id) }}" method="POST" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="type" value="{{ $type }}">
                            <button type="submit" 
                                    class="w-full bg-red-100 text-red-600 px-6 py-3 rounded-lg font-semibold hover:bg-red-200"
                                    onclick="return confirm('Are you sure you want to delete this {{ $type }}?')">
                                <i class="fas fa-trash mr-2"></i> Delete {{ ucfirst($type) }}
                            </button>
                        </form>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection