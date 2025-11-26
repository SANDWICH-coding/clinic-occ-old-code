{{-- resources/views/nurse/symptom-logs/show.blade.php --}}
@extends('layouts.nurse-app')

@section('title', 'Symptom Log Details')

@section('content')
<div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            {{-- Card Header --}}
            <div class="bg-blue-600 text-white p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-clipboard-list text-2xl md:text-3xl"></i>
                    <h1 class="text-2xl md:text-3xl font-semibold">Symptom Log Details</h1>
                </div>
                <div>
                    <a href="{{ route('nurse.symptom-logs.index') }}" class="bg-white text-blue-600 px-4 py-2 rounded-full font-medium shadow-sm hover:bg-gray-200 transition">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Logs
                    </a>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="p-6 md:p-8">
                {{-- Emergency Alert --}}
                @if($symptomLog->is_emergency)
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-3 text-xl"></i>
                        <p class="font-bold">Emergency Case</p>
                    </div>
                    <p class="mt-1">This symptom log contains emergency symptoms that require immediate attention.</p>
                </div>
                @endif

                {{-- Student Information --}}
                <div class="bg-gray-50 p-6 rounded-lg mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Student Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Student Name</p>
                            <p class="font-medium">{{ $symptomLog->user->name ?? 'Unknown User' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Student ID</p>
                            <p class="font-medium">{{ $symptomLog->student_id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Logged At</p>
                            <p class="font-medium">{{ $symptomLog->logged_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <<div>
    <p class="text-sm text-gray-600">Status</p>
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $symptomLog->reviewed_by ? 'bg-green-500 text-white' : 'bg-yellow-500 text-white' }}">
        {{ $symptomLog->reviewed_by ? 'Reviewed' : 'Pending Review' }}
    </span>
</div>
                    </div>
                </div>

                {{-- Symptoms --}}
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Symptoms Reported</h2>
                    <div class="flex flex-wrap gap-2">
                        @if(is_array($symptomLog->symptoms) && count($symptomLog->symptoms) > 0)
                            @foreach($symptomLog->symptoms as $symptom)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ $symptom }}
                                </span>
                            @endforeach
                        @else
                            <p class="text-gray-500">No symptoms recorded</p>
                        @endif
                    </div>
                </div>

                {{-- Possible Illnesses --}}
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Possible Illnesses</h2>
                    <div class="flex flex-wrap gap-2">
                        @if(is_array($symptomLog->possible_illnesses) && count($symptomLog->possible_illnesses) > 0)
                            @foreach($symptomLog->possible_illnesses as $illness)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    {{ $illness }}
                                </span>
                            @endforeach
                        @else
                            <p class="text-gray-500">No illnesses identified</p>
                        @endif
                    </div>
                </div>

                {{-- Additional Information --}}
                @if($symptomLog->severity || $symptomLog->duration || $symptomLog->notes)
                <div class="bg-gray-50 p-6 rounded-lg mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Additional Information</h2>
                    <div class="space-y-4">
                        @if($symptomLog->severity)
                        <div>
                            <p class="text-sm text-gray-600">Severity</p>
                            <p class="font-medium capitalize">{{ $symptomLog->severity }}</p>
                        </div>
                        @endif
                        
                        @if($symptomLog->duration)
                        <div>
                            <p class="text-sm text-gray-600">Duration</p>
                            <p class="font-medium">{{ $symptomLog->duration }}</p>
                        </div>
                        @endif
                        
                        @if($symptomLog->notes)
                        <div>
                            <p class="text-sm text-gray-600">Notes</p>
                            <p class="font-medium whitespace-pre-wrap">{{ $symptomLog->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Actions --}}
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('nurse.symptom-logs.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Back to List
                    </a>
                    @unless($symptomLog->reviewed_by)

                    <form action="{{ route('nurse.symptom-logs.mark-reviewed', $symptomLog->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Mark as Reviewed
                        </button>
                    </form>
                    @endunless
                </div>
            </div>
        </div>
    </div>
</div>
@endsection