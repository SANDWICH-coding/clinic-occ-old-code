{{-- resources/views/nurse/medical-data/show.blade.php --}}
@extends('layouts.nurse-app')

@section('title', ucfirst($type) . ' Details')

@section('content')
<div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            {{-- Header --}}
            <div class="bg-{{ $type === 'symptom' ? 'blue' : 'purple' }}-600 text-white p-6 md:p-8">
                <div class="flex justify-between items-start">
                    <div class="flex items-center space-x-3 flex-1">
                        <i class="fas fa-{{ $type === 'symptom' ? 'heartbeat' : 'stethoscope' }} text-3xl"></i>
                        <div>
                            <h1 class="text-3xl font-semibold">{{ $item->name }}</h1>
                            <p class="text-{{ $type === 'symptom' ? 'blue' : 'purple' }}-100 mt-1">{{ ucfirst($type) }} Details</p>
                        </div>
                    </div>
                    <a href="{{ route('nurse.medical-data.index', ['tab' => $type === 'symptom' ? 'symptoms' : 'illnesses']) }}" 
                       class="bg-white text-{{ $type === 'symptom' ? 'blue' : 'purple' }}-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-100">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>

            {{-- Content --}}
            <div class="p-6 md:p-8">
                {{-- Basic Information --}}
                <div class="bg-gray-50 p-6 rounded-lg mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Name</p>
                            <p class="text-lg font-medium text-gray-900">{{ $item->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Created Date</p>
                            <p class="text-lg font-medium text-gray-900">{{ $item->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Last Updated</p>
                            <p class="text-lg font-medium text-gray-900">{{ $item->updated_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ $type === 'symptom' ? 'Associated Illnesses' : 'Associated Symptoms' }}</p>
                            <p class="text-lg font-medium text-gray-900">
                                {{ $type === 'symptom' ? $item->possibleIllnesses->count() : $item->symptoms->count() }} item(s)
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Associated Items --}}
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">
                        Associated {{ $type === 'symptom' ? 'Illnesses' : 'Symptoms' }}
                    </h2>
                    
                    @php
                        $relatedItems = $type === 'symptom' ? $item->possibleIllnesses : $item->symptoms;
                    @endphp

                    @if($relatedItems->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($relatedItems as $related)
                                <div class="flex items-center justify-between bg-{{ $type === 'symptom' ? 'purple' : 'blue' }}-50 p-3 rounded-lg border border-{{ $type === 'symptom' ? 'purple' : 'blue' }}-200">
                                    <span class="text-{{ $type === 'symptom' ? 'purple' : 'blue' }}-800 font-medium">{{ $related->name }}</span>
                                    <a href="{{ route('nurse.medical-data.show', ['id' => $related->id, 'type' => $type === 'symptom' ? 'illness' : 'symptom']) }}" 
                                       class="text-{{ $type === 'symptom' ? 'purple' : 'blue' }}-600 hover:text-{{ $type === 'symptom' ? 'purple' : 'blue' }}-800" 
                                       title="View Details">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <i class="fas fa-{{ $type === 'symptom' ? 'stethoscope' : 'heartbeat' }} text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No associated {{ $type === 'symptom' ? 'illnesses' : 'symptoms' }} yet</p>
                            <a href="{{ route('nurse.medical-data.edit', ['id' => $item->id, 'type' => $type]) }}" 
                               class="inline-block mt-3 text-{{ $type === 'symptom' ? 'blue' : 'purple' }}-600 hover:underline">
                                <i class="fas fa-link mr-1"></i> Add associations
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('nurse.medical-data.edit', ['id' => $item->id, 'type' => $type]) }}" 
                       class="bg-{{ $type === 'symptom' ? 'blue' : 'purple' }}-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-{{ $type === 'symptom' ? 'blue' : 'purple' }}-700 text-center">
                        <i class="fas fa-edit mr-2"></i> Edit {{ ucfirst($type) }}
                    </a>
                    <a href="{{ route('nurse.medical-data.index', ['tab' => $type === 'symptom' ? 'symptoms' : 'illnesses']) }}" 
                       class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 text-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to List
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
            </div>
        </div>
    </div>
</div>
@endsection