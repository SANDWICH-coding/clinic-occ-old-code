@extends('layouts.nurse-app')

@section('content')
<div class="container mx-auto px-3 sm:px-4 py-4 sm:py-6">
    <!-- Remove the grid constraint and use full width -->
    <div class="bg-white shadow-lg rounded-xl p-4 sm:p-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-gray-200 pb-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-medical-alt text-indigo-600 text-lg"></i>
                </div>
                <div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900">Student Reports</h3>
                    <p class="text-sm text-gray-500 mt-1">Monitor student health activities and generate reports</p>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
            @foreach([
                ['icon' => 'users', 'label' => 'Total Students', 'value' => $totalStudents ?? 0, 'color' => 'indigo', 'bg' => 'bg-indigo-50', 'border' => 'border-indigo-100'],
                ['icon' => 'user-clock', 'label' => 'Active Today', 'value' => $activeToday ?? 0, 'color' => 'green', 'bg' => 'bg-green-50', 'border' => 'border-green-100'],
                ['icon' => 'exclamation-triangle', 'label' => 'High Risk', 'value' => $highRiskStudents ?? 0, 'color' => 'yellow', 'bg' => 'bg-yellow-50', 'border' => 'border-yellow-100'],
                ['icon' => 'clipboard-list', 'label' => 'Pending Reviews', 'value' => $pendingReviews ?? 0, 'color' => 'red', 'bg' => 'bg-red-50', 'border' => 'border-red-100']
            ] as $stat)
                <div class="{{ $stat['bg'] }} {{ $stat['border'] }} p-4 rounded-xl transition-all hover:shadow-md">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-lg bg-white flex items-center justify-center mr-3 shadow-sm">
                            <i class="fas fa-{{ $stat['icon'] }} text-{{ $stat['color'] }}-600 text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-gray-600 truncate">{{ $stat['label'] }}</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ $stat['value'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Search Section -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-5 rounded-xl shadow border border-blue-100">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-search mr-3 text-blue-600 text-base"></i> Search Students
                </h4>
                <div class="relative">
                    <div class="relative">
                        <input type="text" 
                               id="studentSearch" 
                               placeholder="Search by name, student ID, course, or year level..." 
                               class="w-full p-4 text-base border-2 border-blue-200 rounded-xl focus:outline-none focus:ring-3 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder-gray-400">
                        <button id="clearSearch" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors" style="display: none;">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                    <p class="text-sm text-gray-500 mt-3 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-400"></i>
                        Start typing (minimum 2 characters required)
                    </p>
                    <div id="searchResults" class="mt-3 hidden absolute z-50 w-full bg-white border-2 border-blue-200 rounded-xl shadow-xl max-h-96 overflow-y-auto"></div>
                </div>
            </div>
        </div>

        <!-- Recent Reports -->
        <div>
            <div class="bg-white p-5 rounded-xl shadow border border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-gray-200 pb-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-history text-blue-600 text-base"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-800">Recent Student Activity</h4>
                            <p class="text-sm text-gray-500">Last 30 days of student health interactions</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-medium">Last 30 days</span>
                </div>
                
                @if(!empty($recentReports) && count($recentReports) > 0)
                    <!-- Mobile Cards View -->
                    <div class="block sm:hidden space-y-4">
                        @foreach($recentReports as $student)
                            @php
                                $studentData = is_array($student) ? $student : $student->toArray();
                                $riskColors = [
                                    'High' => 'red', 
                                    'Medium' => 'yellow', 
                                    'Low' => 'blue', 
                                    'None' => 'green', 
                                    'Unknown' => 'gray'
                                ];
                                $riskLevel = $studentData['health_risk'] ?? 'Unknown';
                                $color = $riskColors[$riskLevel] ?? 'gray';
                            @endphp
                            <div class="bg-white border-2 border-gray-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-all">
                                <!-- Student Info -->
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center mr-3 shadow-sm">
                                        <span class="text-indigo-600 font-bold text-base">
                                            @if(isset($studentData['name']))
                                                {{ substr($studentData['name'], 0, 1) }}
                                            @elseif(isset($studentData['first_name']))
                                                {{ substr($studentData['first_name'], 0, 1) }}{{ substr($studentData['last_name'] ?? '', 0, 1) }}
                                            @else
                                                ?
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-900 text-base truncate">
                                            @if(isset($studentData['name']))
                                                {{ $studentData['name'] }}
                                            @elseif(isset($studentData['first_name']))
                                                {{ $studentData['first_name'] }} {{ $studentData['last_name'] ?? '' }}
                                            @else
                                                Unknown Student
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-500 truncate">{{ $studentData['student_id'] ?? 'N/A' }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-800 border border-{{ $color }}-200">
                                        {{ $riskLevel }}
                                    </span>
                                </div>

                                <!-- Course & Year -->
                                <div class="mb-4">
                                    <p class="text-sm font-semibold text-gray-900">{{ $studentData['course'] ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-500">Year {{ $studentData['year_level'] ?? 'N/A' }}</p>
                                </div>

                                <!-- Activity Counts -->
                                <div class="grid grid-cols-3 gap-3 mb-4">
                                    <div class="text-center bg-blue-50 rounded-lg p-3 border border-blue-100">
                                        <p class="text-blue-600 font-bold text-lg">{{ $studentData['appointments_count'] ?? 0 }}</p>
                                        <p class="text-xs text-gray-600 font-medium">Appts</p>
                                    </div>
                                    <div class="text-center bg-green-50 rounded-lg p-3 border border-green-100">
                                        <p class="text-green-600 font-bold text-lg">{{ $studentData['consultations_count'] ?? 0 }}</p>
                                        <p class="text-xs text-gray-600 font-medium">Consults</p>
                                    </div>
                                    <div class="text-center bg-yellow-50 rounded-lg p-3 border border-yellow-100">
                                        <p class="text-yellow-600 font-bold text-lg">{{ $studentData['symptoms_count'] ?? 0 }}</p>
                                        <p class="text-xs text-gray-600 font-medium">Symptoms</p>
                                    </div>
                                </div>

                                <!-- Last Activity & Actions -->
                                <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="fas fa-clock mr-2 text-gray-400"></i>
                                        {{ $studentData['last_activity'] ?? 'No activity' }}
                                    </div>
                                    <div class="flex space-x-2">
                                        @if(isset($studentData['id']))
                                            <a href="{{ route('nurse.student-reports.show', $studentData['id']) }}" 
                                               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-all hover:shadow-md" 
                                               title="View Report">
                                                <i class="fas fa-file-medical mr-2 text-xs"></i> View
                                            </a>
                                        @else
                                            <span class="inline-flex items-center px-4 py-2 bg-gray-400 text-white text-sm font-medium rounded-lg">
                                                <i class="fas fa-file-medical mr-2 text-xs"></i> View
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden sm:block overflow-x-auto rounded-lg border border-gray-200">
                        <table class="w-full text-left min-w-full">
                            <thead class="bg-gradient-to-r from-gray-50 to-blue-50">
                                <tr>
                                    <th class="px-6 py-4 text-gray-700 text-sm font-semibold uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-4 text-gray-700 text-sm font-semibold uppercase tracking-wider">Student ID</th>
                                    <th class="px-6 py-4 text-gray-700 text-sm font-semibold uppercase tracking-wider">Course & Year</th>
                                    <th class="px-6 py-4 text-gray-700 text-sm font-semibold uppercase tracking-wider">Health Risk</th>
                                    <th class="px-6 py-4 text-gray-700 text-sm font-semibold uppercase tracking-wider">Activities</th>
                                    <th class="px-6 py-4 text-gray-700 text-sm font-semibold uppercase tracking-wider">Last Activity</th>
                                    <th class="px-6 py-4 text-gray-700 text-sm font-semibold uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($recentReports as $student)
                                    @php
                                        $studentData = is_array($student) ? $student : $student->toArray();
                                        $riskColors = [
                                            'High' => 'red', 
                                            'Medium' => 'yellow', 
                                            'Low' => 'blue', 
                                            'None' => 'green', 
                                            'Unknown' => 'gray'
                                        ];
                                        $riskLevel = $studentData['health_risk'] ?? 'Unknown';
                                        $color = $riskColors[$riskLevel] ?? 'gray';
                                    @endphp
                                    <tr class="hover:bg-blue-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center mr-4 shadow-sm">
                                                    <span class="text-indigo-600 font-bold text-sm">
                                                        @if(isset($studentData['name']))
                                                            {{ substr($studentData['name'], 0, 1) }}
                                                        @elseif(isset($studentData['first_name']))
                                                            {{ substr($studentData['first_name'], 0, 1) }}{{ substr($studentData['last_name'] ?? '', 0, 1) }}
                                                        @else
                                                            ?
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="font-semibold text-gray-900 text-base truncate">
                                                        @if(isset($studentData['name']))
                                                            {{ $studentData['name'] }}
                                                        @elseif(isset($studentData['first_name']))
                                                            {{ $studentData['first_name'] }} {{ $studentData['last_name'] ?? '' }}
                                                        @else
                                                            Unknown Student
                                                        @endif
                                                    </p>
                                                    <p class="text-sm text-gray-500 truncate">{{ $studentData['email'] ?? 'No email' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 font-mono text-sm font-semibold text-gray-700">{{ $studentData['student_id'] ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-gray-900 text-sm">{{ $studentData['course'] ?? 'N/A' }}</p>
                                            <p class="text-sm text-gray-500">Year {{ $studentData['year_level'] ?? 'N/A' }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-800 border border-{{ $color }}-200">
                                                {{ $riskLevel }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-4">
                                                <div class="text-center">
                                                    <p class="text-blue-600 font-bold text-lg">{{ $studentData['appointments_count'] ?? 0 }}</p>
                                                    <p class="text-xs text-gray-600 font-medium">Appts</p>
                                                </div>
                                                <div class="text-center">
                                                    <p class="text-green-600 font-bold text-lg">{{ $studentData['consultations_count'] ?? 0 }}</p>
                                                    <p class="text-xs text-gray-600 font-medium">Consults</p>
                                                </div>
                                                <div class="text-center">
                                                    <p class="text-yellow-600 font-bold text-lg">{{ $studentData['symptoms_count'] ?? 0 }}</p>
                                                    <p class="text-xs text-gray-600 font-medium">Symptoms</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center text-sm text-gray-600">
                                                <i class="fas fa-clock mr-2 text-gray-400"></i>
                                                {{ $studentData['last_activity'] ?? 'No activity' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-2">
                                                @if(isset($studentData['id']))
                                                    <a href="{{ route('nurse.student-reports.show', $studentData['id']) }}" 
                                                       class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-all hover:shadow-md" 
                                                       title="View Report">
                                                        <i class="fas fa-file-medical mr-2 text-xs"></i> View
                                                    </a>
                                                @else
                                                    <span class="inline-flex items-center px-4 py-2 bg-gray-400 text-white text-sm font-medium rounded-lg">
                                                        <i class="fas fa-file-medical mr-2 text-xs"></i> View
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-6 text-sm text-gray-500 bg-gray-50 py-3 rounded-lg">
                        <i class="fas fa-info-circle mr-2 text-blue-400"></i>
                        Showing {{ count($recentReports) }} most recent student activities
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users text-3xl text-gray-300"></i>
                        </div>
                        <h5 class="text-xl font-semibold text-gray-400 mb-2">No Recent Student Activity</h5>
                        <p class="text-gray-400 mb-4">No student activity found in the last 30 days.</p>
                        <p class="text-sm text-gray-300">Student reports will appear here as they interact with the clinic.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.search-result-item {
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
    border-radius: 12px;
}
.search-result-item:hover {
    border-left-color: #4f46e5;
    transform: translateX(4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.search-result-item.active {
    background: linear-gradient(135deg, #eff6ff, #f0f9ff);
    border-left-color: #4f46e5;
}

@media (max-width: 640px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    #searchResults {
        position: fixed;
        top: 50%;
        left: 1rem;
        right: 1rem;
        transform: translateY(-50%);
        max-height: 70vh;
        z-index: 1000;
        border-radius: 16px;
    }
    
    .search-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 999;
    }
}

#searchResults::-webkit-scrollbar {
    width: 6px;
}
#searchResults::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}
#searchResults::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}
#searchResults::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.btn-mobile {
    min-height: 48px;
    min-width: 48px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}
.loading-pulse {
    animation: pulse 1.5s ease-in-out infinite;
}

.gradient-border {
    border: 2px solid;
    border-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%) 1;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('studentSearch');
    const clearButton = document.getElementById('clearSearch');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;
    let currentSearchQuery = '';
    let abortController = null;
    let searchOverlay = null;

    function createOverlay() {
        if (window.innerWidth <= 640) {
            searchOverlay = document.createElement('div');
            searchOverlay.className = 'search-overlay';
            searchOverlay.style.display = 'none';
            document.body.appendChild(searchOverlay);
            
            searchOverlay.addEventListener('click', function() {
                hideSearchResults();
            });
        }
    }

    function showSearchResults() {
        searchResults.classList.remove('hidden');
        if (searchOverlay) {
            searchOverlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    function hideSearchResults() {
        searchResults.classList.add('hidden');
        if (searchOverlay) {
            searchOverlay.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        
        if (abortController) {
            abortController.abort();
        }

        const query = this.value.trim();
        currentSearchQuery = query;

        if (query.length > 0) {
            clearButton.style.display = 'block';
        } else {
            clearButton.style.display = 'none';
            hideSearchResults();
            searchResults.innerHTML = '';
            return;
        }

        if (query.length < 2) {
            searchResults.innerHTML = `
                <div class="text-center p-6 text-gray-500">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-keyboard text-blue-400 text-xl"></i>
                    </div>
                    <p class="text-sm font-medium">Please enter at least 2 characters to search</p>
                </div>
            `;
            showSearchResults();
            return;
        }

        searchResults.innerHTML = `
            <div class="text-center p-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>
                <p class="text-gray-600 text-base font-medium mb-2">Searching students...</p>
                <p class="text-sm text-gray-400">Searching for: "${query}"</p>
            </div>
        `;
        showSearchResults();

        searchTimeout = setTimeout(() => performSearch(query), 400);
    });

    clearButton.addEventListener('click', function() {
        searchInput.value = '';
        this.style.display = 'none';
        hideSearchResults();
        searchResults.innerHTML = '';
        currentSearchQuery = '';
        searchInput.focus();
    });

    function performSearch(query) {
        abortController = new AbortController();
        
        fetch(`/nurse/student-reports/search-ajax?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            signal: abortController.signal
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (query !== currentSearchQuery) return;

            if (data.success && data.students && data.students.length > 0) {
                let html = `
                    <div class="sticky top-0 bg-white border-b border-gray-200 p-4 rounded-t-xl">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-check text-green-600"></i>
                                </div>
                                <div>
                                    <h6 class="text-base font-semibold text-gray-800">
                                        Found ${data.count} student${data.count !== 1 ? 's' : ''}
                                    </h6>
                                    <p class="text-sm text-gray-500">Results for your search</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">"${data.query}"</span>
                        </div>
                    </div>
                    <div class="p-4 space-y-3">
                `;

                data.students.forEach(student => {
                    const riskColor = getRiskColor(student.health_risk_level);
                    const completion = student.medical_record_completion || 0;
                    const completionColorClass = getCompletionColorClass(completion);
                    let riskIndicators = '';

                    if (student.has_allergies) riskIndicators += '<span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-medium mr-2" title="Has Allergies"><i class="fas fa-allergies mr-1"></i>Allergies</span>';
                    if (student.has_chronic_conditions) riskIndicators += '<span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-medium mr-2" title="Chronic Conditions"><i class="fas fa-heartbeat mr-1"></i>Chronic</span>';
                    if (!student.has_medical_record) riskIndicators += '<span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs font-medium mr-2" title="No Medical Record"><i class="fas fa-file-medical mr-1"></i>No Record</span>';

                    html += `
                        <div class="p-4 bg-white rounded-xl border-2 border-gray-100 hover:border-blue-300 hover:shadow-lg transition-all search-result-item btn-mobile">
                            <div class="flex justify-between items-start">
                                <div class="flex-1 mr-3 min-w-0">
                                    <div class="flex items-start mb-3">
                                        <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0 shadow-sm">
                                            <span class="text-indigo-600 font-bold text-base">${(student.first_name?.charAt(0) || '') + (student.last_name?.charAt(0) || '')}</span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center mb-2">
                                                <h6 class="font-bold text-gray-900 text-lg truncate">${student.full_name || 'Unknown Student'}</h6>
                                            </div>
                                            <div class="mb-3">
                                                ${riskIndicators}
                                            </div>
                                            <p class="text-sm text-gray-600 mb-2">
                                                <strong class="font-mono">${student.student_id || 'N/A'}</strong> | ${student.course || 'N/A'} - Year ${student.year_level || 'N/A'}
                                            </p>
                                            <div class="flex flex-wrap gap-3 mb-3 text-sm text-gray-600">
                                                <span class="flex items-center font-medium"><i class="fas fa-calendar-check mr-2 text-blue-500"></i>${student.appointments_count || 0} appointments</span>
                                                <span class="flex items-center font-medium"><i class="fas fa-stethoscope mr-2 text-green-500"></i>${student.consultations_count || 0} consultations</span>
                                                <span class="flex items-center font-medium"><i class="fas fa-thermometer-half mr-2 text-yellow-500"></i>${student.symptoms_count || 0} symptoms</span>
                                            </div>
                                            <div class="flex items-center mb-2">
                                                <div class="w-full bg-gray-200 rounded-full h-3 mr-3 shadow-inner">
                                                    <div class="h-3 rounded-full ${completionColorClass} transition-all duration-500" style="width: ${completion}%" title="Medical Record: ${completion}% Complete"></div>
                                                </div>
                                                <span class="text-sm font-semibold text-gray-700 whitespace-nowrap">${completion}% Complete</span>
                                            </div>
                                            <div class="text-sm text-gray-500 flex items-center">
                                                <i class="fas fa-clock mr-2 text-gray-400"></i>${student.last_activity || 'No recent activity'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0 ml-3">
                                    <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-bold bg-${getRiskColorClass(riskColor)} text-white mb-3 whitespace-nowrap shadow-sm">
                                        <i class="fas fa-shield-alt mr-2"></i>${student.health_risk_level || 'Unknown'}
                                    </span>
                                    <div class="flex flex-col space-y-2">
                                        <a href="/nurse/student-reports/${student.id}" 
                                           class="px-5 py-3 text-white bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 rounded-xl text-sm font-semibold flex items-center justify-center whitespace-nowrap btn-mobile transition-all hover:shadow-lg" 
                                           title="View Full Report">
                                            <i class="fas fa-file-medical mr-2"></i> View Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
                searchResults.innerHTML = html;
                showSearchResults();
            } else {
                searchResults.innerHTML = `
                    <div class="text-center p-8 text-gray-500">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-search text-3xl text-gray-300"></i>
                        </div>
                        <h5 class="text-lg font-semibold text-gray-400 mb-2">No Students Found</h5>
                        <p class="text-gray-400 mb-3">No students match your search criteria: "${data.query || query}"</p>
                        <p class="text-sm text-gray-300">Try searching by name, student ID, course, or year level</p>
                    </div>
                `;
                showSearchResults();
            }
        })
        .catch(error => {
            if (error.name === 'AbortError') return;
            console.error('Search error:', error);
            searchResults.innerHTML = `
                <div class="text-center p-6 text-red-500">
                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                    </div>
                    <strong class="text-base font-semibold">Search failed</strong>
                    <p class="text-sm mt-2 text-gray-600">Please try again in a moment</p>
                </div>
            `;
            showSearchResults();
        })
        .finally(() => {
            abortController = null;
        });
    }

    function getRiskColor(risk) {
        const colors = { 
            'High': 'red', 
            'Medium': 'yellow', 
            'Low': 'blue', 
            'None': 'green', 
            'Unknown': 'gray' 
        };
        return colors[risk] || 'gray';
    }

    function getRiskColorClass(riskColor) {
        const classes = { 
            'red': 'bg-red-500', 
            'yellow': 'bg-yellow-500', 
            'blue': 'bg-blue-500', 
            'green': 'bg-green-500', 
            'gray': 'bg-gray-500' 
        };
        return classes[riskColor] || 'bg-gray-500';
    }

    function getCompletionColorClass(completion) {
        if (completion >= 80) return 'bg-green-500';
        if (completion >= 50) return 'bg-yellow-500';
        return 'bg-red-500';
    }

    createOverlay();

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target) && !clearButton.contains(e.target)) {
            hideSearchResults();
        }
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideSearchResults();
            this.blur();
        }
        if (e.key === 'Enter' && this.value.trim().length >= 2) {
            clearTimeout(searchTimeout);
            performSearch(this.value.trim());
        }
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth > 640 && searchOverlay) {
            searchOverlay.remove();
            searchOverlay = null;
        } else if (window.innerWidth <= 640 && !searchOverlay) {
            createOverlay();
        }
    });

    setTimeout(() => {
        searchInput.focus();
    }, 100);
});
</script>
@endpush