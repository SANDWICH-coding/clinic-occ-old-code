@extends('layouts.nurse-app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Nurse Dashboard</h1>
        <p class="mt-1 text-sm text-gray-600">Welcome back, {{ auth()->user()->name ?? 'Nurse' }}</p>
    </div>

    @if(isset($error) && $error)
    <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Dashboard Error</h3>
                <p class="text-sm text-red-700 mt-1">{{ $error }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Updated Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Today's Appointments -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-blue-500">
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide">Today's Appointments</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $todaysAppointments->count() }}</p>
                        <p class="mt-1 text-sm text-gray-500">Scheduled for today</p>
                    </div>
                    <div class="flex-shrink-0">
                        <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Records -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-yellow-500">
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-yellow-600 uppercase tracking-wide">Pending Records</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $pendingRecords }}</p>
                        <p class="mt-1 text-sm text-gray-500">Awaiting review</p>
                    </div>
                    <div class="flex-shrink-0">
                        <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registered Students -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-green-500">
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-green-600 uppercase tracking-wide">Registered Students</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $registeredStudents }}</p>
                        <p class="mt-1 text-sm text-gray-500">In clinic system</p>
                    </div>
                    <div class="flex-shrink-0">
                        <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students Seen Today -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-purple-500">
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-purple-600 uppercase tracking-wide">Students Seen Today</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $studentsSeenToday }}</p>
                        <p class="mt-1 text-sm text-gray-500">Completed consultations</p>
                    </div>
                    <div class="flex-shrink-0">
                        <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Health Trends Charts - Single chart row -->
    <div class="grid grid-cols-1 gap-6 mb-8">
        <!-- Health Cases by Course (Most Common Illness) - UPDATED -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        Most Common Illness by Course
                    </h3>
                    <!-- <p id="mostCommonIllnessTitle" class="text-sm text-gray-600 mt-1 hidden">
                        Most common illness: <span class="font-semibold" id="mostCommonIllnessName"></span>
                    </p> -->
                </div>
                <div id="courseIllnessLoading" class="hidden">
                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            <div class="p-6">
                <div id="courseIllnessError" class="hidden mb-4 bg-yellow-50 border border-yellow-200 rounded-md p-3">
                    <p class="text-sm text-yellow-700">Unable to load health cases data.</p>
                </div>
                <div class="relative" style="height: 400px;">
                    <canvas id="mostCommonIllnessChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Top Symptoms Chart -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Top Symptoms (This Month)</h3>
                <div id="symptomsLoading" class="hidden">
                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            <div class="p-6">
                <div id="symptomsError" class="hidden mb-4 bg-yellow-50 border border-yellow-200 rounded-md p-3">
                    <p class="text-sm text-yellow-700">Unable to load symptoms data.</p>
                </div>
                <div class="relative" style="height: 300px;">
                    <canvas id="topSymptomsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Symptom Trends Chart -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Symptom Trends (Last 12 Months)</h3>
                <div id="trendsLoading" class="hidden">
                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            <div class="p-6">
                <div id="trendsError" class="hidden mb-4 bg-yellow-50 border border-yellow-200 rounded-md p-3">
                    <p class="text-sm text-yellow-700">Unable to load symptom trends.</p>
                </div>
                <div class="relative" style="height: 300px;">
                    <canvas id="symptomTrendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Improved Weekly Summary Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Enhanced Weekly Summary -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Weekly Summary</h3>
                <p class="text-sm text-gray-600 mt-1">This week's activity overview</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <p class="text-2xl font-bold text-blue-600">{{ $weeklyStats['appointments'] ?? 0 }}</p>
                        <p class="text-sm text-gray-600">Appointments</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <p class="text-2xl font-bold text-green-600">{{ $weeklyStats['newRecords'] ?? 0 }}</p>
                        <p class="text-sm text-gray-600">New Records</p>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <p class="text-2xl font-bold text-purple-600">{{ $studentsSeenToday }}</p>
                        <p class="text-sm text-gray-600">Students Treated</p>
                    </div>
                    <div class="text-center p-4 bg-orange-50 rounded-lg">
                        <p class="text-2xl font-bold text-orange-600">{{ $weeklyCases }}</p>
                        <p class="text-sm text-gray-600">Weekly Cases</p>
                    </div>
                </div>
                
                @if(isset($weeklyStats['commonIssues']) && $weeklyStats['commonIssues']->count() > 0)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Common Health Issues This Week</h4>
                    <div class="space-y-3">
                        @foreach($weeklyStats['commonIssues'] as $issue)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">{{ $issue->diagnosis ?? 'General Checkup' }}</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $issue->count ?? 1 }} cases
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-sm text-gray-500 text-center">No common issues recorded this week</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Student Count by Course -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Registered Students by Course</h3>
                    <p class="text-sm text-gray-600 mt-1">Total number of registered students per course</p>
                </div>
                <div id="distributionLoading" class="hidden">
                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            <div class="p-6">
                <div id="distributionError" class="hidden mb-4 bg-yellow-50 border border-yellow-200 rounded-md p-3">
                    <p class="text-sm text-yellow-700">Unable to load student enrollment data.</p>
                </div>
                <div class="relative" style="height: 300px;">
                    <canvas id="courseDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js default settings
    Chart.defaults.font.family = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    Chart.defaults.color = '#6B7280';

    // Helper function to fetch data
    async function fetchData(url, loadingElement, errorElement) {
        if (loadingElement) loadingElement.classList.remove('hidden');
        if (errorElement) errorElement.classList.add('hidden');

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Response is not JSON');
            }
            
            const data = await response.json();
            if (loadingElement) loadingElement.classList.add('hidden');
            
            if (data && data.success !== undefined && !data.success) {
                throw new Error('API returned unsuccessful response');
            }
            
            return data;
        } catch (error) {
            console.error(`Fetch error for ${url}:`, error);
            if (loadingElement) loadingElement.classList.add('hidden');
            if (errorElement) errorElement.classList.remove('hidden');
            return null;
        }
    }

    // Color palettes
    const courseColors = [
        'rgba(239, 68, 68, 0.8)',   // Red
        'rgba(234, 179, 8, 0.8)',   // Yellow
        'rgba(59, 130, 246, 0.8)',  // Blue
        'rgba(16, 185, 129, 0.8)',  // Green
        'rgba(139, 92, 246, 0.8)',  // Purple
        'rgba(236, 72, 153, 0.8)'   // Pink
    ];

    const generalColors = [
        'rgba(59, 130, 246, 0.8)',  // Blue
        'rgba(16, 185, 129, 0.8)',  // Green
        'rgba(245, 158, 11, 0.8)',  // Amber
        'rgba(236, 72, 153, 0.8)',  // Pink
        'rgba(139, 92, 246, 0.8)',  // Purple
        'rgba(239, 68, 68, 0.8)',   // Red
        'rgba(99, 102, 241, 0.8)'   // Indigo
    ];

    /**
     * MOST COMMON ILLNESS BY COURSE - UPDATED
     * Using new route that provides illness data
     */
    const mostCommonIllnessCtx = document.getElementById('mostCommonIllnessChart');
    if (mostCommonIllnessCtx) {
        const loadingEl = document.getElementById('courseIllnessLoading');
        const errorEl = document.getElementById('courseIllnessError');
        const illnessTitleWrapper = document.getElementById('mostCommonIllnessTitle');
        const illnessNameText = document.getElementById('mostCommonIllnessName');
        
        // Try the new route first, fall back to existing route
       // Try the new route first, fall back to existing route
fetchData('{{ route("nurse.dashboard.most-common-illness-by-course") }}', loadingEl, errorEl).then(data => {
    if (!data) {
        // Fallback to existing route
        return fetchData('{{ route("nurse.dashboard.illness-by-course") }}', loadingEl, errorEl).then(fallbackData => {
                    if (fallbackData && fallbackData.labels && fallbackData.counts) {
                        // Transform existing data to match expected format
                        return {
                            programs: fallbackData.labels,
                            counts: fallbackData.counts,
                            illnesses: Array(fallbackData.labels.length).fill('Data not available')
                        };
                    }
                    return null;
                });
            }
            return data;
        }).then(data => {
            if (data && data.programs && data.counts && data.programs.length > 0) {
                
                // Find the most common illness overall
                let maxCount = 0;
                let mostCommonIllness = '';
                let mostCommonCourse = '';
                
                data.counts.forEach((count, index) => {
                    if (count > maxCount && data.illnesses && data.illnesses[index]) {
                        maxCount = count;
                        mostCommonIllness = data.illnesses[index];
                        mostCommonCourse = data.programs[index];
                    }
                });

                // Show the most common illness above the chart
                if (mostCommonIllness && illnessTitleWrapper && illnessNameText) {
                    illnessNameText.textContent = `${mostCommonIllness} (${mostCommonCourse})`;
                    illnessTitleWrapper.classList.remove('hidden');
                }

                new Chart(mostCommonIllnessCtx, {
                    type: 'bar',
                    data: {
                        labels: data.programs,
                        datasets: [{
                            label: 'Health Cases',
                            data: data.counts,
                            backgroundColor: courseColors.slice(0, data.programs.length),
                            borderColor: courseColors.map(color => color.replace('0.8', '1')).slice(0, data.programs.length),
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    title: function(context) {
                                        return `Course: ${context[0].label}`;
                                    },
                                    label: function(context) {
                                        const illness = (data.illnesses && data.illnesses[context.dataIndex]) 
                                            ? data.illnesses[context.dataIndex] 
                                            : 'Data not available';
                                        return [
                                            `Health Cases: ${context.parsed.y}`,
                                            `Most Common: ${illness}`
                                        ];
                                    }
                                }
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true,
                                ticks: { precision: 0 },
                                grid: { color: 'rgba(0, 0, 0, 0.05)' },
                                title: { display: true, text: 'Number of Health Cases' }
                            },
                            x: {
                                grid: { display: false },
                                title: { display: true, text: 'Academic Program' }
                            }
                        }
                    }
                });
            } else {
                if (errorEl) errorEl.classList.remove('hidden');
            }
        });
    }

    /**
     * TOP SYMPTOMS CHART
     * Using existing route: /dashboard/top-symptoms
     */
    const topSymptomsCtx = document.getElementById('topSymptomsChart');
    if (topSymptomsCtx) {
        const loadingEl = document.getElementById('symptomsLoading');
        const errorEl = document.getElementById('symptomsError');
        
        fetchData('{{ route("nurse.dashboard.top-symptoms") }}', loadingEl, errorEl).then(data => {
            if (data && data.labels && data.counts && data.labels.length > 0) {
                new Chart(topSymptomsCtx, {
                    type: 'doughnut',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.counts,
                            backgroundColor: generalColors.slice(0, data.labels.length),
                            borderColor: generalColors.map(color => color.replace('0.8', '1')).slice(0, data.labels.length),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { boxWidth: 20, padding: 15 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${label}: ${value} cases (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '50%'
                    }
                });
            } else {
                if (errorEl) errorEl.classList.remove('hidden');
            }
        });
    }

    /**
     * SYMPTOM TRENDS CHART
     * Using existing route: /dashboard/symptom-trends
     */
    /**
 * SYMPTOM TRENDS CHART - UPDATED FOR MULTI-SYMPTOM DATA
 */
const trendsCtx = document.getElementById('symptomTrendsChart');
if (trendsCtx) {
    const loadingEl = document.getElementById('trendsLoading');
    const errorEl = document.getElementById('trendsError');
    
    fetchData('{{ route("nurse.dashboard.symptom-trends") }}', loadingEl, errorEl).then(data => {
        if (data && data.labels && data.datasets) {
            new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: data.datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                padding: 15
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                title: function(context) {
                                    return `Month: ${context[0].label}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                            title: { 
                                display: true, 
                                text: 'Number of Cases'
                            }
                        },
                        x: {
                            grid: { display: false },
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        } else {
            if (errorEl) errorEl.classList.remove('hidden');
        }
    });
}

    /**
     * REGISTERED STUDENTS BY COURSE
     * Using existing route: /dashboard/course-distribution
     */
    const courseDistributionCtx = document.getElementById('courseDistributionChart');
    if (courseDistributionCtx) {
        const loadingEl = document.getElementById('distributionLoading');
        const errorEl = document.getElementById('distributionError');
        
        fetchData('{{ route("nurse.dashboard.course-distribution") }}', loadingEl, errorEl).then(data => {
            if (data && data.labels && data.counts && data.labels.length > 0) {
                new Chart(courseDistributionCtx, {
                    type: 'pie',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Registered Students',
                            data: data.counts,
                            backgroundColor: courseColors.slice(0, data.labels.length),
                            borderColor: courseColors.map(color => color.replace('0.8', '1')).slice(0, data.labels.length),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { boxWidth: 20, padding: 15 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${label}: ${value} students (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                if (errorEl) errorEl.classList.remove('hidden');
            }
        });
    }

    // Auto-refresh dashboard data every 5 minutes
    setInterval(() => {
        console.log('Auto-refreshing dashboard data...');
        location.reload();
    }, 300000);
});
</script>
@endpush
@endsection