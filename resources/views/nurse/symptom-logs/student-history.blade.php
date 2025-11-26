{{-- resources/views/nurse/symptom-logs/student-history.blade.php --}}
@extends('layouts.nurse-app')

@section('title', 'Student Symptom History')

@section('content')
<div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            {{-- Card Header --}}
            <div class="bg-purple-600 text-white p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-user-clock text-2xl md:text-3xl"></i>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-semibold">Student Symptom History</h1>
                        <p class="text-purple-100 mt-1">{{ $student->name }} (ID: {{ $student->student_id ?? $student->id }})</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 md:gap-4">
                    <a href="{{ route('nurse.symptom-logs.index') }}" class="bg-white text-purple-600 px-4 py-2 rounded-full font-medium shadow-sm hover:bg-gray-200 transition">
                        <i class="fas fa-arrow-left mr-1"></i> Back to All Logs
                    </a>
                    <a href="{{ route('nurse.symptom-logs.export', ['student_id' => $student->student_id ?? $student->id]) }}" class="bg-white text-purple-600 px-4 py-2 rounded-full font-medium shadow-sm hover:bg-gray-200 transition">
                        <i class="fas fa-download mr-1"></i> Export History
                    </a>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="p-6 md:p-8">
                {{-- Student Statistics Summary --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                    <div class="bg-blue-100 text-blue-800 p-6 rounded-lg shadow-sm text-center transform transition duration-300 hover:scale-105">
                        <p class="text-4xl font-bold">{{ $stats['total_logs'] }}</p>
                        <span class="text-lg mt-2 block">Total Logs</span>
                    </div>
                    <div class="bg-red-100 text-red-800 p-6 rounded-lg shadow-sm text-center transform transition duration-300 hover:scale-105">
                        <p class="text-4xl font-bold">{{ $stats['emergency_logs'] }}</p>
                        <span class="text-lg mt-2 block">Emergency Cases</span>
                    </div>
                    <div class="bg-green-100 text-green-800 p-6 rounded-lg shadow-sm text-center transform transition duration-300 hover:scale-105">
                        <p class="text-4xl font-bold">{{ $stats['recent_logs'] }}</p>
                        <span class="text-lg mt-2 block">Last 30 Days</span>
                    </div>
                </div>

                {{-- Student Information Card --}}
                <div class="bg-gray-50 p-6 rounded-lg mb-8">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Student Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Full Name</p>
                            <p class="font-medium">{{ $student->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Student ID</p>
                            <p class="font-medium">{{ $student->student_id ?? $student->id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-medium">{{ $student->email }}</p>
                        </div>
                    </div>
                </div>

                {{-- Symptom History Timeline --}}
                @if($logs->count() > 0)
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Symptom History Timeline</h2>
                    <div class="overflow-x-auto bg-gray-50 rounded-lg shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Date/Time</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Symptoms</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Possible Illnesses</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Severity</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($logs as $log)
                                    <tr class="hover:bg-gray-50 {{ $log->is_emergency ? 'bg-red-50 hover:bg-red-100' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 font-medium">
                                                {{ $log->logged_at ? $log->logged_at->format('M d, Y') : $log->created_at->format('M d, Y') }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $log->logged_at ? $log->logged_at->format('h:i A') : $log->created_at->format('h:i A') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <div class="flex flex-wrap gap-1">
                                                @if(is_array($log->symptoms) && count($log->symptoms) > 0)
                                                    @foreach($log->symptoms as $symptom)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">{{ $symptom }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-800">No symptoms recorded</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <div class="flex flex-wrap gap-1">
                                                @if(is_array($log->possible_illnesses) && count($log->possible_illnesses) > 0)
                                                    @foreach($log->possible_illnesses as $illness)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">{{ $illness }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">No illnesses identified</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($log->severity)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    {{ $log->severity === 'severe' ? 'bg-red-100 text-red-800' : ($log->severity === 'moderate' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                                    {{ ucfirst($log->severity) }}
                                                </span>
                                            @endif
                                            @if($log->is_emergency)
                                                <div class="mt-1">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-600 text-white">
                                                        EMERGENCY
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($log->nurse_reviewed)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500 text-white">
                                                    <i class="fas fa-check mr-1"></i> Reviewed
                                                </span>
                                                @if($log->reviewed_at)
                                                    <div class="text-xs text-gray-400 mt-1">{{ $log->reviewed_at->diffForHumans() }}</div>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500 text-white">
                                                    <i class="fas fa-clock mr-1"></i> Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('nurse.symptom-logs.show', $log->id) }}" class="text-blue-600 hover:text-blue-800 transition" title="View Details">
                                                    <i class="fas fa-eye text-lg"></i>
                                                </a>
                                                @unless($log->nurse_reviewed)
                                                    <form action="{{ route('nurse.symptom-logs.mark-reviewed', $log->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:text-green-800 transition" title="Mark as Reviewed">
                                                            <i class="fas fa-check text-lg"></i>
                                                        </button>
                                                    </form>
                                                @endunless
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-8">
                        {{ $logs->links() }}
                    </div>

                    {{-- Summary Notes Section --}}
                    @if($logs->where('notes')->count() > 0)
                        <div class="mt-8 bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Student Notes History</h3>
                            <div class="space-y-4">
                                @foreach($logs->where('notes')->take(5) as $log)
                                    <div class="border-l-4 border-blue-500 pl-4">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-grow">
                                                <p class="text-sm text-gray-600">{{ $log->logged_at ? $log->logged_at->format('M d, Y h:i A') : $log->created_at->format('M d, Y h:i A') }}</p>
                                                <p class="text-gray-800 whitespace-pre-wrap">{{ $log->notes }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-16 px-4">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-user-clock text-6xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">No symptom history found</h3>
                        <p class="mt-2 text-gray-500">This student has not logged any symptoms yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function markAsReviewed(logId) {
        if (confirm('Are you sure you want to mark this symptom log as reviewed?')) {
            fetch(`/nurse/symptom-logs/${logId}/mark-reviewed`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to mark as reviewed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }
    }
</script>
@endpush
@endsection