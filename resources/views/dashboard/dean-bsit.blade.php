@extends('layouts.dean')

@section('title', 'BSIT Health Analytics Dashboard')

@push('styles')
<style>
    /* Dashboard Specific Styles */
    .dashboard-container {
        background-color: #1a1f2e;
        min-height: 100vh;
        padding: 2rem 1rem;
    }

    /* Override body and main content backgrounds */
    body {
        background-color: #1a1f2e !important;
    }

    .page-container {
        background-color: #1a1f2e !important;
    }

    .content-area {
        background-color: #1a1f2e !important;
    }

    .stat-card {
        background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
        padding: 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        border-left: 6px solid;
        transition: all 0.4s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.3) 50%, transparent 100%);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
    }

    /* Individual card colors */
    .stat-card.student-register {
        border-left-color: #10B981;
        background: linear-gradient(135deg, #065f46 0%, #047857 100%);
    }

    .stat-card.appointment-records {
        border-left-color: #3B82F6;
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    }

    .stat-card.medical-records {
        border-left-color: #F59E0B;
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
    }

    .stat-card.consultation-records {
        border-left-color: #8B5CF6;
        background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
    }

    .stat-card-icon {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.2;
        transition: all 0.3s ease;
    }

    .stat-card:hover .stat-card-icon {
        opacity: 0.4;
        transform: scale(1.1);
    }

    .chart-container {
        position: relative;
        height: 280px;
        width: 100%;
        min-height: 250px;
        background-color: transparent;
    }

    .dashboard-card {
        background: linear-gradient(135deg, #2d3748 0%, #374151 100%);
        border-radius: 1rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        border: 1px solid #4a5568;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }

    .dashboard-card:hover {
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.4);
        transform: translateY(-2px);
    }

    .card-content {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    /* Loading States */
    .loading-spinner {
        border: 3px solid #4a5568;
        border-left: 3px solid #800000;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 20px auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Grid Layouts */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    /* Header Styles */
    .section-header {
        font-size: 1.5rem;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 3px solid;
        border-image: linear-gradient(90deg, #800000, transparent) 1;
        position: relative;
    }

    .section-header::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 100px;
        height: 3px;
        background: linear-gradient(90deg, #800000, transparent);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #4a5568;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .chart-header h3 {
        color: #ffffff;
        font-weight: 600;
    }

    /* Text color fixes for dark cards */
    .stat-card p,
    .stat-card .text-gray-600 {
        color: #e5e7eb !important;
    }

    .stat-card .text-gray-900 {
        color: #ffffff !important;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .stat-card .text-gray-500 {
        color: #d1d5db !important;
    }

    .dashboard-card .text-gray-800 {
        color: #ffffff !important;
    }

    /* Enhanced Stat Values */
    .stat-value {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1;
        margin: 1rem 0;
        background: linear-gradient(135deg, #ffffff, #e5e7eb);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .stat-label {
        font-size: 0.9rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    /* Alert & Badge Styles */
    .alert-badge {
        background: #742a2a;
        border: 1px solid #9b2c2c;
        color: #feb2b2;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }

    .badge-success {
        background: #22543d;
        border: 1px solid #276749;
        color: #9ae6b4;
    }

    /* Button Styles */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1rem;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #800000, #660000);
        color: white;
        box-shadow: 0 4px 15px rgba(128, 0, 0, 0.3);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #660000, #550000);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(128, 0, 0, 0.4);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #4a5568, #2d3748);
        color: #e2e8f0;
        border: 1px solid #718096;
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #2d3748, #4a5568);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }

    .refresh-btn {
        background: linear-gradient(135deg, #4a5568, #2d3748);
        border: 1px solid #718096;
        cursor: pointer;
        color: #e2e8f0;
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 1rem;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
    }

    .refresh-btn:hover {
        background: linear-gradient(135deg, #2d3748, #4a5568);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .refresh-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Debug Panel */
    #debug-panel {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        background: rgba(45, 55, 72, 0.95);
        border: 1px solid #4a5568;
        border-radius: 0.75rem;
        padding: 1rem;
        backdrop-filter: blur(10px);
        z-index: 1000;
    }

    #debug-panel.hidden {
        display: none;
    }

    .debug-toggle {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 50px;
        height: 50px;
        background: #800000;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        z-index: 1001;
        box-shadow: 0 4px 15px rgba(128, 0, 0, 0.3);
        transition: all 0.3s ease;
    }

    .debug-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(128, 0, 0, 0.4);
    }

    /* Case Details Styles */
    .case-details {
        background: #374151;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-top: 1rem;
        border: 1px solid #4a5568;
    }

    .case-details h4 {
        color: #e5e7eb;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .case-item {
        display: flex;
        justify-content: between;
        align-items: center;
        padding: 0.25rem 0;
        border-bottom: 1px solid #4b5563;
    }

    .case-item:last-child {
        border-bottom: none;
    }

    /* Mobile Navigation */
    .mobile-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #2d3748;
        border-top: 1px solid #4a5568;
        padding: 0.75rem;
        display: none;
        z-index: 50;
    }

    .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0.5rem;
        border-radius: 0.5rem;
        color: #cbd5e0;
        text-decoration: none;
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }

    .nav-item.active {
        background: #800000;
        color: white;
    }

    .nav-item:hover {
        background: #4a5568;
        color: white;
    }

    .nav-item.active:hover {
        background: #660000;
    }

    /* Toast Notification */
    .toast {
        position: fixed;
        top: 1rem;
        right: 1rem;
        background: linear-gradient(135deg, #2d3748, #4a5568);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        border: 1px solid #4a5568;
        z-index: 100;
        transform: translateX(400px);
        transition: transform 0.3s ease;
    }

    .toast.show {
        transform: translateX(0);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem 0.5rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .charts-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .chart-container {
            height: 250px;
        }

        .section-header {
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .mobile-nav {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
        }

        .main-content {
            padding-bottom: 5rem;
        }

        .chart-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .chart-header h3 {
            font-size: 1rem;
        }

        .header-actions {
            flex-direction: column;
            gap: 0.75rem;
            width: 100%;
        }

        .alert-badge {
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
        }

        .stat-value {
            font-size: 2rem;
        }

        .stat-card-icon {
            width: 40px;
            height: 40px;
        }

        #debug-panel {
            bottom: 1rem;
            right: 1rem;
            left: 1rem;
        }

        .debug-toggle {
            bottom: 1rem;
            right: 1rem;
        }
    }

    @media (max-width: 480px) {
        .stat-card {
            padding: 1.25rem;
        }

        .chart-container {
            height: 220px;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .stat-value {
            font-size: 1.75rem;
        }

        .stat-card-icon {
            width: 35px;
            height: 35px;
        }
    }

    /* Dark mode - Force dark theme */
    @media (prefers-color-scheme: dark) {
        body {
            background-color: #1a1f2e !important;
        }

        .dashboard-container {
            background-color: #1a1f2e !important;
        }

        .stat-card,
        .dashboard-card {
            background: #2d3748;
            border-color: #4a5568;
        }

        .section-header {
            color: #ffffff;
            border-bottom-color: #4a5568;
        }
    }

    /* Print Styles */
    @media print {
        .no-print {
            display: none !important;
        }

        .stat-card,
        .dashboard-card {
            break-inside: avoid;
            box-shadow: none;
            border: 1px solid #000;
            background: white !important;
        }

        body,
        .dashboard-container,
        .page-container,
        .content-area {
            background-color: white !important;
        }

        .stat-card p,
        .stat-card .text-gray-600,
        .stat-card .text-gray-900,
        .dashboard-card .text-gray-800,
        .section-header,
        .chart-header h3 {
            color: #000000 !important;
        }
    }

    /* Container wrapper fix */
    .container,
    .mx-auto {
        max-width: 1400px;
    }

    /* Ensure full height coverage */
    html {
        background-color: #1a1f2e;
    }

    /* Fix any potential white gaps */
    main,
    .main-content {
        background-color: #1a1f2e !important;
    }

    /* Chart.js canvas styling */
    canvas {
        background-color: transparent !important;
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Stats Section - FIXED VERSION -->
    <section class="mb-8">
        <h2 class="section-header">BSIT Department Overview</h2>
        
        <div class="stats-grid">
            <!-- Card 1: BSIT Student Register -->
            <div class="stat-card student-register">
                <div class="stat-card-icon">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="flex flex-col h-full justify-center items-center text-center">
                    <p class="stat-label text-green-200">BSIT Student Register</p>
                    <p class="stat-value" id="stat-students">
                        {{ number_format($dashboardData['stats']['total_students'] ?? 0) }}
                    </p>
                    <p class="text-xs text-gray-300 mt-2">Total enrolled students</p>
                </div>
            </div>

            <!-- Card 2: Monthly Appointment Records -->
            <div class="stat-card appointment-records">
                <div class="stat-card-icon">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="flex flex-col h-full justify-center items-center text-center">
                    <p class="stat-label text-blue-200">Monthly Appointment Records</p>
                    <p class="stat-value" id="stat-appointments">
                        {{ number_format($dashboardData['stats']['total_appointments'] ?? 0) }}
                    </p>
                    <p class="text-xs text-gray-300 mt-2">Appointments this month</p>
                </div>
            </div>

            <!-- Card 3: Pending Medical Records - FIXED KEY -->
            <div class="stat-card medical-records">
                <div class="stat-card-icon">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="flex flex-col h-full justify-center items-center text-center">
                    <p class="stat-label text-yellow-200">Pending Medical Records</p>
                    <p class="stat-value" id="stat-medical">
                        {{ number_format($dashboardData['stats']['pending_medical_records'] ?? 0) }}
                    </p>
                    <p class="text-xs text-gray-300 mt-2">Awaiting review</p>
                </div>
            </div>

            <!-- Card 4: Monthly Consultation Records -->
            <div class="stat-card consultation-records">
                <div class="stat-card-icon">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex flex-col h-full justify-center items-center text-center">
                    <p class="stat-label text-purple-200">Monthly Consultation Records</p>
                    <p class="stat-value" id="stat-consultations">
                        {{ number_format($dashboardData['stats']['total_consultations'] ?? 0) }}
                    </p>
                    <p class="text-xs text-gray-300 mt-2">Consultations this month</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Charts Section -->
    <section class="mb-8">
        <h2 class="section-header">Health Analytics</h2>
        
        <!-- Top Row: Top Symptoms & Symptom Trends -->
        <div class="charts-grid mb-6">
            <!-- Top Symptoms -->
            <div class="dashboard-card p-4 lg:p-6">
                <div class="card-content">
                    <div class="chart-header">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Top Symptoms This Month</h3>
                        <button data-chart-type="topSymptoms" class="refresh-btn no-print">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                    </div>
                    <div id="topSymptomsContainer" class="chart-container">
                        <div class="flex justify-center items-center h-full">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Symptom Trends -->
            <div class="dashboard-card p-4 lg:p-6">
                <div class="card-content">
                    <div class="chart-header">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Symptom Trends (Last 6 Months)</h3>
                        <button data-chart-type="symptomTrends" class="refresh-btn no-print">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                    </div>
                    <div id="symptomTrendsContainer" class="chart-container">
                        <div class="flex justify-center items-center h-full">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row: BSIT Symptom Overview & Year Level -->
        <div class="charts-grid">
            <!-- BSIT Symptom Overview -->
            <div class="dashboard-card p-4 lg:p-6">
                <div class="card-content">
                    <div class="chart-header">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">BSIT Symptom Overview</h3>
                        <button data-chart-type="departmentSymptoms" class="refresh-btn no-print">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                    </div>
                    <div id="departmentSymptomsContainer" class="chart-container">
                        <div class="flex justify-center items-center h-full">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leading Case Counts by BSIT Year Level -->
            <div class="dashboard-card p-4 lg:p-6">
                <div class="card-content">
                    <div class="chart-header">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Leading Case Counts by BSIT Year Level</h3>
                        <button data-chart-type="yearLevel" class="refresh-btn no-print">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                    </div>
                    <div id="yearLevelContainer" class="chart-container">
                        <div class="flex justify-center items-center h-full">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


<!-- Toast Notification -->
<div id="toast" class="toast no-print"></div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// ✅ GLOBAL SCOPE - Make functions accessible everywhere
const config = {
    routes: {
        departmentSymptoms: '{{ route("dean.dashboard-api.chart.data", ["department" => "BSIT", "chartType" => "department-symptoms"]) }}',
        topSymptoms: '{{ route("dean.dashboard-api.chart.data", ["department" => "BSIT", "chartType" => "top-symptoms"]) }}',
        symptomTrends: '{{ route("dean.dashboard-api.chart.data", ["department" => "BSIT", "chartType" => "symptom-trends"]) }}',
        yearLevel: '{{ route("dean.dashboard-api.chart.data", ["department" => "BSIT", "chartType" => "year-distribution"]) }}',
        realtimeStats: '{{ route("dean.dashboard-api.realtime-stats", ["department" => "BSIT"]) }}',
        debugData: '{{ route("dean.dashboard-api.debug-data", ["department" => "BSIT"]) }}',
        clearCache: '{{ route("dean.dashboard-api.clear-cache", ["department" => "BSIT"]) }}'
    },
    colors: ['#800000', '#3B82F6', '#F59E0B', '#8B5CF6', '#10B981', '#EC4899', '#F97316', '#6366F1', '#14B8A6', '#F43F5E']
};

const charts = {};

// ✅ GLOBAL FUNCTIONS - Accessible from anywhere
window.toggleDebugPanel = function() {
    const debugPanel = document.getElementById('debug-panel');
    debugPanel.classList.toggle('hidden');
}

window.debugStats = async function() {
    try {
        showToast('Fetching debug data...', 'info');
        const response = await fetch(config.routes.debugData);
        const data = await response.json();
        console.log('Debug Data:', data);
        
        if (data.success) {
            showToast('Debug data loaded - check console', 'info');
            
            const debugInfo = `
Total Students: ${data.debug_info.students_in_department}
Monthly Appointments: ${data.debug_info.appointments.this_month}
Pending Medical Records: ${data.debug_info.medical_records.pending}
Monthly Consultations: ${data.debug_info.consultations.this_month}
            `;
            alert('Debug Information:\n' + debugInfo);
        } else {
            showToast('Debug failed: ' + (data.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Debug error:', error);
        showToast('Debug error - check console', 'error');
    }
}

window.refreshStats = async function() {
    try {
        showToast('Refreshing statistics...', 'info');
        const response = await fetch(config.routes.realtimeStats);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('stat-students').textContent = data.stats.total_students.toLocaleString();
            document.getElementById('stat-appointments').textContent = data.stats.monthly_appointments.toLocaleString();
            document.getElementById('stat-medical').textContent = data.stats.pending_medical_records.toLocaleString();
            document.getElementById('stat-consultations').textContent = data.stats.monthly_consultations.toLocaleString();
            
            showToast('Statistics refreshed successfully', 'success');
            refreshAllCharts();
        } else {
            showToast('Failed to refresh: ' + (data.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Refresh error:', error);
        showToast('Error refreshing statistics', 'error');
    }
}

window.clearCache = async function() {
    try {
        showToast('Clearing cache...', 'info');
        
        const response = await fetch(config.routes.clearCache, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Cache cleared successfully', 'success');
            setTimeout(() => {
                window.refreshStats();
            }, 1000);
        } else {
            showToast('Cache clear failed: ' + (data.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Cache clear error:', error);
        showToast('Error clearing cache', 'error');
    }
}

window.loadChart = async function(chartType) {
    const container = document.getElementById(`${chartType}Container`);
    const url = config.routes[chartType];

    if (!url) {
        showError(container, 'Route not configured');
        return;
    }

    try {
        showLoading(container);
        
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        console.log(`Chart data for ${chartType}:`, data);
        
        if (!data.success) {
            throw new Error(data.message || 'API error');
        }

        // Check for empty data
        if (chartType === 'yearLevel') {
            if (!data.year_levels || data.year_levels.length === 0) {
                showEnhancedEmptyState(container, chartType);
                return;
            }
        } else {
            if (!data.labels || data.labels.length === 0 || 
                !data.data || data.data.length === 0 ||
                (data.data && data.data.every(item => item === 0))) {
                showEnhancedEmptyState(container, chartType);
                return;
            }
        }

        renderChart(chartType, data, container);

    } catch (error) {
        console.error(`Chart error for ${chartType}:`, error);
        showError(container, 'Failed to load chart data: ' + error.message);
    }
}

function refreshAllCharts() {
    Object.keys(config.routes).forEach(type => {
        if (type !== 'realtimeStats' && type !== 'debugData' && type !== 'clearCache') {
            window.loadChart(type);
        }
    });
}

function renderSymptomTrendsChart(type, data, container) {
    console.log('renderSymptomTrendsChart called with:', data);
    
    if (!data || !data.labels || !data.data) {
        console.error('Invalid data structure for symptom trends chart:', data);
        showEmpty(container, 'No trend data available');
        return;
    }
    
    container.innerHTML = '';
    
    const chartWrapper = document.createElement('div');
    chartWrapper.style.height = '280px';
    const ctx = document.createElement('canvas');
    chartWrapper.appendChild(ctx);
    container.appendChild(chartWrapper);
    
    // const detailsSection = document.createElement('div');
    // detailsSection.className = 'mt-4 p-4 bg-gray-700 rounded-lg';
    // detailsSection.innerHTML = `
    //     <h4 class="text-sm font-semibold text-gray-300 mb-3">Monthly Top Cases</h4>
    //     <div id="monthCaseDetails" class="space-y-2 max-h-48 overflow-y-auto">
    //         <p class="text-gray-400 text-xs">Click on chart points to see details</p>
    //     </div>
    // `;
    // container.appendChild(detailsSection);

    if (charts[type]) {
        charts[type].destroy();
    }

    charts[type] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Monthly Cases',
                data: data.data,
                borderColor: '#800000',
                backgroundColor: 'rgba(128, 0, 0, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#800000',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(45, 55, 72, 0.95)',
                    titleColor: '#e5e7eb',
                    bodyColor: '#e5e7eb',
                    borderColor: '#4a5568',
                    callbacks: {
                        afterBody: function(context) {
                            const monthIndex = context[0].dataIndex;
                            if (data.top_cases_by_month && data.top_cases_by_month[monthIndex]) {
                                const topCases = data.top_cases_by_month[monthIndex];
                                let details = '\n\nTop Cases:';
                                Object.entries(topCases).forEach(([caseName, count]) => {
                                    details += `\n• ${caseName}: ${count}`;
                                });
                                return details;
                            }
                            return '\nNo cases recorded';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false, color: '#4a5568' },
                    ticks: { color: '#e5e7eb' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#4a5568' },
                    ticks: { color: '#e5e7eb', stepSize: 1 }
                }
            },
            onClick: (event, elements) => {
                if (elements.length > 0) {
                    const monthIndex = elements[0].index;
                    const monthName = data.labels[monthIndex];
                    const topCases = data.top_cases_by_month && data.top_cases_by_month[monthIndex];
                    
                    let html = `<div class="mb-2"><strong class="text-white">${monthName}</strong></div>`;
                    if (topCases && Object.keys(topCases).length > 0) {
                        html += '<div class="space-y-1">';
                        Object.entries(topCases).forEach(([caseName, count]) => {
                            html += `
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-gray-300">${caseName}</span>
                                    <span class="bg-red-900 text-red-200 px-2 py-0.5 rounded font-semibold">${count}</span>
                                </div>
                            `;
                        });
                        html += '</div>';
                    } else {
                        html += '<p class="text-gray-400 text-xs">No cases recorded this month</p>';
                    }
                    
                    document.getElementById('monthCaseDetails').innerHTML = html;
                }
            }
        }
    });
}

function renderYearLevelChart(type, data, container) {
    console.log('renderYearLevelChart called with:', data);
    
    if (!data || !data.year_levels || !Array.isArray(data.year_levels)) {
        console.error('Invalid data structure for year level chart:', data);
        showEmpty(container, 'No data available for year levels');
        return;
    }
    
    container.innerHTML = '';
    
    const chartWrapper = document.createElement('div');
    chartWrapper.style.height = '280px';
    const ctx = document.createElement('canvas');
    chartWrapper.appendChild(ctx);
    container.appendChild(chartWrapper);
    
    // REMOVED THE DETAILS SECTION HERE

    if (charts[type]) {
        charts[type].destroy();
    }

    const hasData = data.year_levels.some(y => (y.total || 0) > 0);
    
    charts[type] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.year_levels.map(y => y.year.replace(' year', 'Y')),
            datasets: [{
                label: 'Total Cases',
                data: data.year_levels.map(y => y.total || 0),
                backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6'],
                borderRadius: 8,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(45, 55, 72, 0.95)',
                    titleColor: '#e5e7eb',
                    bodyColor: '#e5e7eb',
                    borderColor: '#4a5568',
                    callbacks: {
                        afterBody: function(context) {
                            const yearIndex = context[0].dataIndex;
                            const yearData = data.year_levels[yearIndex];
                            
                            if (yearData.top_cases && Object.keys(yearData.top_cases).length > 0) {
                                let details = '\n\nTop Cases:';
                                Object.entries(yearData.top_cases).forEach(([caseName, count]) => {
                                    details += `\n• ${caseName}: ${count}`;
                                });
                                return details;
                            }
                            return '\nNo cases recorded';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#e5e7eb' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#4a5568' },
                    ticks: { color: '#e5e7eb', stepSize: 1 }
                }
            }
        }
    });
    
    if (!hasData) {
        const noDataMsg = document.createElement('div');
        noDataMsg.className = 'absolute inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75 rounded';
        noDataMsg.innerHTML = '<p class="text-gray-400 text-sm">No symptom cases recorded in the last 6 months</p>';
        chartWrapper.style.position = 'relative';
        chartWrapper.appendChild(noDataMsg);
    }
}

function renderChart(type, data, container) {
    if (type === 'symptomTrends' && data.top_cases_by_month) {
        renderSymptomTrendsChart(type, data, container);
        return;
    }
    
    if (type === 'yearLevel' && data.year_levels) {
        renderYearLevelChart(type, data, container);
        return;
    }
    
    const ctx = document.createElement('canvas');
    container.innerHTML = '';
    container.appendChild(ctx);

    if (charts[type]) {
        charts[type].destroy();
    }

    const chartConfig = getChartConfig(type, data);
    if (chartConfig) {
        charts[type] = new Chart(ctx, chartConfig);
    }
}

function getChartConfig(type, data) {
    const baseConfig = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            legend: { 
                display: type === 'topSymptoms',
                position: 'right',
                labels: { 
                    boxWidth: 12,
                    padding: 15,
                    color: '#e5e7eb',
                    font: { weight: '600' }
                }
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: 'rgba(45, 55, 72, 0.9)',
                titleColor: '#e5e7eb',
                bodyColor: '#e5e7eb',
                borderColor: '#4a5568'
            }
        },
        scales: {
            x: {
                grid: { display: false, color: '#4a5568' },
                ticks: { color: '#e5e7eb' }
            },
            y: {
                beginAtZero: true,
                grid: { color: '#4a5568' },
                ticks: { color: '#e5e7eb', stepSize: 1 }
            }
        }
    };

    switch (type) {
        case 'departmentSymptoms':
            return {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Symptom Count',
                        data: data.data,
                        backgroundColor: config.colors,
                        borderRadius: 8,
                        borderWidth: 0
                    }]
                },
                options: {
                    ...baseConfig,
                    plugins: { ...baseConfig.plugins, legend: { display: false } }
                }
            };

        case 'topSymptoms':
            return {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.data,
                        backgroundColor: config.colors,
                        borderWidth: 2,
                        borderColor: '#1a1f2e'
                    }]
                },
                options: {
                    ...baseConfig,
                    cutout: '60%',
                    plugins: {
                        ...baseConfig.plugins,
                        legend: {
                            position: window.innerWidth < 768 ? 'bottom' : 'right',
                            labels: { boxWidth: 12, padding: 15, color: '#e5e7eb' }
                        }
                    }
                }
            };
    }
}

function showLoading(container) {
    container.innerHTML = `
        <div class="flex flex-col items-center justify-center h-full">
            <div class="loading-spinner"></div>
            <span class="mt-2 text-sm text-gray-400">Loading...</span>
        </div>
    `;
}

function showError(container, message) {
    container.innerHTML = `
        <div class="flex flex-col items-center justify-center h-full text-red-400 p-4 text-center">
            <svg class="w-12 h-12 mb-3 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm font-medium mb-2">${message}</p>
            <button onclick="window.loadChart('${container.id.replace('Container', '')}')" 
                    class="text-blue-400 text-sm hover:text-blue-300 transition-colors font-medium">
                Try Again
            </button>
        </div>
    `;
}

function showEmpty(container, message) {
    container.innerHTML = `
        <div class="flex flex-col items-center justify-center h-full text-gray-400 p-4 text-center">
            <div class="text-4xl mb-3 opacity-50">📊</div>
            <p class="text-sm font-medium">${message}</p>
            <p class="text-xs mt-1 opacity-75">Check back later for updates</p>
        </div>
    `;
}

function showEnhancedEmptyState(container, chartType) {
    const suggestions = {
        'yearLevel': 'No year-level data found. Add some symptom logs for BSIT students to see data here.',
        'symptomTrends': 'No symptom trend data available. Data will appear once consultations are recorded.',
        'topSymptoms': 'No symptom data recorded this month.',
        'departmentSymptoms': 'No department symptom data available.'
    };

    container.innerHTML = `
        <div class="flex flex-col items-center justify-center h-full text-gray-400 p-6 text-center">
            <div class="text-4xl mb-4 opacity-50">📊</div>
            <p class="text-sm font-medium mb-2">No data available</p>
            <p class="text-xs mb-4 opacity-75">${suggestions[chartType] || 'Check back later for updates'}</p>
            <div class="flex gap-2 mt-2">
                <button onclick="window.loadChart('${chartType}')" 
                        class="text-blue-400 text-sm hover:text-blue-300 transition-colors font-medium px-3 py-1 border border-blue-400 rounded">
                    Try Again
                </button>
                <button onclick="window.debugStats()" 
                        class="text-gray-400 text-sm hover:text-gray-300 transition-colors font-medium px-3 py-1 border border-gray-400 rounded">
                    Debug Data
                </button>
            </div>
        </div>
    `;
}

function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    const bgColor = type === 'error' ? 'bg-red-600' : 
                   type === 'success' ? 'bg-green-600' : 'bg-gray-800';
    
    toast.className = `toast no-print ${bgColor} text-white`;
    toast.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            ${message}
        </div>
    `;
    
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard initializing...');
    
    // Event handlers for refresh buttons
    document.querySelectorAll('[data-chart-type]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const type = btn.dataset.chartType;
            window.loadChart(type);
            showToast('Chart refreshed');
        });
    });

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            Object.keys(charts).forEach(type => {
                if (charts[type]) {
                    charts[type].resize();
                }
            });
        }, 250);
    });

    // Initial load with staggered timing
    Object.keys(config.routes).forEach((type, index) => {
        if (type !== 'realtimeStats' && type !== 'debugData' && type !== 'clearCache') {
            setTimeout(() => window.loadChart(type), index * 400);
        }
    });

    // Auto-refresh every 2 minutes
    setInterval(window.refreshStats, 120000);
    
    console.log('Dashboard initialized successfully');
});
</script>
@endpush