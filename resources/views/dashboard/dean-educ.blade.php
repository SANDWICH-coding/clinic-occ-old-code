@extends('layouts.dean')

@section('title', 'EDUC Health Analytics Dashboard')

@push('styles')
<style>
    /* Enhanced EDUC Dashboard Styles - Darker Theme */
    :root {
        --educ-bsed: #3B82F6; /* Blue for BSED */
        --educ-beed: #8B5CF6; /* Purple for BEED */
        --educ-bsed-light: rgba(59, 130, 246, 0.15);
        --educ-beed-light: rgba(139, 92, 246, 0.15);
        --primary-bg: #0f172a; /* Darker background */
        --card-bg: #1e293b; /* Darker card background */
        --card-hover: #334155;
        --text-primary: #ffffff;
        --text-secondary: #e5e7eb;
        --text-muted: #9ca3af;
    }

    /* Dashboard Container */
    .dashboard-container {
        background: linear-gradient(135deg, var(--primary-bg) 0%, #020617 100%); /* Darker gradient */
        min-height: 100vh;
        padding: 2rem 1rem;
        position: relative;
        overflow-x: hidden;
    }

    .dashboard-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 300px;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.03) 0%, transparent 100%); /* More subtle */
        pointer-events: none;
    }

    /* Enhanced Stat Cards - Darker Version */
    .stat-card {
        background: linear-gradient(135deg, var(--card-bg) 0%, #334155 100%); /* Darker gradient */
        padding: 1.75rem;
        border-radius: 1.25rem;
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.4), /* Darker shadow */
            inset 0 1px 0 rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.08); /* Slightly more visible border */
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
        background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.2) 50%, transparent 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 
            0 20px 40px rgba(0, 0, 0, 0.5),
            inset 0 1px 0 rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.15);
    }

    .stat-card:hover::before {
        opacity: 1;
    }

    /* Program-specific card styles - Darker gradients */
    .stat-card.educ-student-register {
        border-left: 4px solid #3B82F6;
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); /* Darker blue */
    }

    .stat-card.educ-appointment-records {
        border-left: 4px solid var(--educ-bsed);
        background: linear-gradient(135deg, #1e3a8a 0%, #3B82F6 100%); /* Darker blue */
    }

    .stat-card.educ-medical-records {
        border-left: 4px solid var(--educ-beed);
        background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%); /* Darker purple */
    }

    .stat-card.educ-consultation-records {
        border-left: 4px solid #8B5CF6;
        background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 100%); /* Darker purple */
    }

    .stat-card-icon {
        position: absolute;
        top: 1.25rem;
        right: 1.25rem;
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.08); /* Darker background */
        backdrop-filter: blur(10px);
        opacity: 0.8;
        transition: all 0.3s ease;
    }

    .stat-card:hover .stat-card-icon {
        opacity: 1;
        transform: scale(1.1) rotate(5deg);
    }

    /* Enhanced Stat Values */
    .stat-value {
        font-size: 2.75rem;
        font-weight: 800;
        line-height: 1;
        margin: 1.25rem 0;
        background: linear-gradient(135deg, #f8fafc, #e2e8f0); /* Slightly darker gradient */
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
        position: relative;
    }

    .stat-label {
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Enhanced Grid Layouts */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    /* NEW: 2x2 Charts Grid Layout */
    .charts-grid-2x2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    .chart-card {
        grid-column: span 1;
    }

    /* Enhanced Dashboard Cards */
    .dashboard-card {
        background: linear-gradient(135deg, var(--card-bg) 0%, #334155 100%); /* Darker */
        border-radius: 1.25rem;
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.4),
            inset 0 1px 0 rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.08);
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .dashboard-card:hover {
        box-shadow: 
            0 15px 35px rgba(0, 0, 0, 0.5),
            inset 0 1px 0 rgba(255, 255, 255, 0.05);
        transform: translateY(-3px);
    }

    .card-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 1.5rem;
    }

    /* Enhanced Chart Containers */
    .chart-container {
        position: relative;
        height: 320px;
        width: 100%;
        min-height: 300px;
        background: transparent;
    }

    .large-chart-container {
        height: 350px;
        min-height: 320px;
    }

    /* Enhanced Section Headers */
    .section-header {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        position: relative;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .section-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, #3B82F6, transparent);
        border-radius: 2px;
    }

    .section-header-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #3B82F6, #1D4ED8);
    }

    /* Enhanced Chart Headers */
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        flex-wrap: wrap;
        gap: 1rem;
    }

    .chart-header h3 {
        color: var(--text-primary);
        font-weight: 700;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    /* Enhanced Program Badges */
    .program-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
    }

    .program-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    }

    .badge-educ-bsed { 
        background: linear-gradient(135deg, var(--educ-bsed), #1d4ed8); /* Darker blue */
        color: white;
    }

    .badge-educ-beed { 
        background: linear-gradient(135deg, var(--educ-beed), #6d28d9); /* Darker purple */
        color: white;
    }

    /* Enhanced Program Distribution - Darker */
    .program-distribution-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .program-distribution-card {
        background: linear-gradient(135deg, var(--card-bg), #334155); /* Darker */
        border-radius: 1rem;
        padding: 1.25rem;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .program-distribution-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
    }

    .program-distribution-card.bsed {
        border-top: 3px solid var(--educ-bsed);
    }

    .program-distribution-card.beed {
        border-top: 3px solid var(--educ-beed);
    }

    /* Enhanced Dashboard Header - Darker */
    .dashboard-header {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.95)); /* Darker */
        backdrop-filter: blur(20px);
        border-radius: 1.25rem;
        padding: 2rem;
        margin-bottom: 3rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.4), transparent);
    }

    /* Enhanced Loading States */
    .loading-spinner {
        border: 3px solid rgba(255, 255, 255, 0.1);
        border-left: 3px solid #3B82F6;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 20px auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Enhanced Button Styles */
    .refresh-btn {
        background: linear-gradient(135deg, #374151, #1f2937); /* Darker */
        border: 1px solid rgba(255, 255, 255, 0.1);
        cursor: pointer;
        color: var(--text-secondary);
        font-size: 0.875rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .refresh-btn:hover {
        background: linear-gradient(135deg, #3B82F6, #1D4ED8);
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
    }

    /* Enhanced Toast Notification */
    .toast {
        position: fixed;
        top: 2rem;
        right: 2rem;
        background: linear-gradient(135deg, var(--card-bg), #334155); /* Darker */
        color: white;
        padding: 1.25rem 1.75rem;
        border-radius: 1rem;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        z-index: 1000;
        transform: translateX(400px);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(20px);
        max-width: 350px;
    }

    .toast.show {
        transform: translateX(0);
    }

    /* Additional utility classes for text colors */
    .text-blue-200 { color: #bfdbfe; }
    .text-green-200 { color: #bbf7d0; }
    .text-yellow-200 { color: #fef3c7; }
    .text-purple-200 { color: #e9d5ff; }

    /* NEW: Responsive Design for 2x2 Layout */
    @media (max-width: 1200px) {
        .charts-grid-2x2 {
            gap: 1.25rem;
        }
        
        .chart-container {
            height: 300px;
        }
        
        .large-chart-container {
            height: 320px;
        }
    }

    @media (max-width: 1024px) {
        .charts-grid-2x2 {
            grid-template-columns: 1fr;
        }
        
        .chart-card {
            grid-column: span 1;
        }
        
        .chart-container {
            height: 350px;
        }
        
        .large-chart-container {
            height: 380px;
        }
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem 0.75rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        .charts-grid-2x2 {
            gap: 1.25rem;
        }

        .chart-container {
            height: 300px;
            min-height: 280px;
        }

        .large-chart-container {
            height: 320px;
            min-height: 300px;
        }

        .section-header {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-value {
            font-size: 2.25rem;
        }

        .stat-card {
            padding: 1.5rem;
        }

        .card-content {
            padding: 1.25rem;
        }
        
        .chart-header h3 {
            font-size: 1rem;
        }
        
        .dashboard-header {
            padding: 1.5rem;
        }

        .program-distribution-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.75rem;
        }
        
        .program-distribution-card {
            padding: 1rem;
        }
    }

    @media (max-width: 640px) {
        .charts-grid-2x2 {
            gap: 1rem;
        }
        
        .chart-container {
            height: 280px;
        }
        
        .large-chart-container {
            height: 300px;
        }
        
        .card-content {
            padding: 1rem;
        }
    }

    @media (max-width: 480px) {
        .stat-card {
            padding: 1.25rem;
        }

        .chart-container {
            height: 250px;
        }

        .large-chart-container {
            height: 280px;
        }

        .stat-value {
            font-size: 2rem;
        }

        .section-header {
            font-size: 1.375rem;
        }

        .dashboard-header {
            padding: 1.25rem;
        }
        
        .chart-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .chart-header h3 {
            font-size: 0.95rem;
        }

        .program-distribution-grid {
            grid-template-columns: 1fr;
            max-width: 200px;
            margin-left: auto;
            margin-right: auto;
        }
    }

    @media (max-width: 380px) {
        .chart-container {
            height: 220px;
        }
        
        .large-chart-container {
            height: 250px;
        }
        
        .stats-grid {
            gap: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Enhanced Header Section -->
    <section class="dashboard-header">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <div class="section-header-icon">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v6l9-5M12 20l-9-5"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl lg:text-4xl font-bold text-white mb-2">EDUC Health Analytics</h1>
                        <p class="text-gray-300">Comprehensive health monitoring for Education programs</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-4">
                    <span class="program-badge badge-educ-bsed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        BSED - Secondary Education
                    </span>
                    <span class="program-badge badge-educ-beed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v6l9-5M12 20l-9-5"/>
                        </svg>
                        BEED - Elementary Education
                    </span>
                </div>
            </div>
            <div class="flex gap-3 flex-wrap">
                <button onclick="refreshAllCharts()" class="refresh-btn no-print">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh All
                </button>
                <button onclick="exportDashboard()" class="refresh-btn no-print">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Report
                </button>
            </div>
        </div>
    </section>

    <!-- Enhanced Stats Section -->
    <section class="mb-8">
        <h2 class="section-header">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Department Overview
        </h2>
        
        <div class="stats-grid">
            <!-- EDUC Student Register -->
            <div class="stat-card educ-student-register">
                <div class="stat-card-icon">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="flex flex-col h-full justify-center">
                    <p class="stat-label text-blue-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        EDUC Student Register
                    </p>
                    <p class="stat-value" id="stat-students">
                        {{ number_format($dashboardData['stats']['total_students'] ?? 0) }}
                    </p>
                    <p class="text-sm text-gray-300 mt-3">Total enrolled students across all EDUC programs</p>
                </div>
            </div>

            <!-- Monthly Appointment Records -->
            <div class="stat-card educ-appointment-records">
                <div class="stat-card-icon">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="flex flex-col h-full justify-center">
                    <p class="stat-label text-blue-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Monthly Appointments
                    </p>
                    <p class="stat-value" id="stat-appointments">
                        {{ number_format($dashboardData['stats']['total_appointments'] ?? 0) }}
                    </p>
                    <p class="text-sm text-gray-300 mt-3">Appointments scheduled this month</p>
                </div>
            </div>

            <!-- Pending Medical Records -->
            <div class="stat-card educ-medical-records">
                <div class="stat-card-icon">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="flex flex-col h-full justify-center">
                    <p class="stat-label text-purple-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Pending Medical Records
                    </p>
                    <p class="stat-value" id="stat-medical">
                        {{ number_format($dashboardData['stats']['pending_medical_records'] ?? 0) }}
                    </p>
                    <p class="text-sm text-gray-300 mt-3">Records awaiting medical review</p>
                </div>
            </div>

            <!-- Monthly Consultation Records -->
            <div class="stat-card educ-consultation-records">
                <div class="stat-card-icon">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex flex-col h-full justify-center">
                    <p class="stat-label text-purple-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        Monthly Consultations
                    </p>
                    <p class="stat-value" id="stat-consultations">
                        {{ number_format($dashboardData['stats']['total_consultations'] ?? 0) }}
                    </p>
                    <p class="text-sm text-gray-300 mt-3">Consultations completed this month</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Enhanced Program Distribution Section -->
    <section class="mb-8">
        <h2 class="section-header">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
            </svg>
            Program Distribution
        </h2>
        
        <div class="program-distribution-grid">
            @php
                $programs = [
                    'BSED' => ['count' => 0, 'color' => 'bsed', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    'BEED' => ['count' => 0, 'color' => 'beed', 'icon' => 'M12 14l9-5-9-5-9 5 9 5z M12 14l9-5-9-5-9 5 9 5z M12 14v6l9-5M12 20l-9-5']
                ];
                
                if(isset($dashboardData['charts']['course_distribution'])) {
                    foreach($dashboardData['charts']['course_distribution'] as $course => $count) {
                        if(isset($programs[$course])) {
                            $programs[$course]['count'] = $count;
                        }
                    }
                }
            @endphp

            @foreach($programs as $programCode => $data)
            <div class="program-distribution-card {{ $data['color'] }}">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-white/10 to-white/5 rounded-xl mb-3 mx-auto">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $data['icon'] }}"/>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-white mb-1">{{ $data['count'] }}</div>
                <div class="program-badge badge-educ-{{ $data['color'] }} text-xs mb-2">
                    {{ $programCode }}
                </div>
                <p class="text-gray-400 text-xs">Enrolled Students</p>
                <div class="mt-2 pt-2 border-t border-gray-600">
                    <div class="flex justify-between text-xs text-gray-400">
                        <span>Active</span>
                        <span class="text-white font-semibold">{{ round(($data['count'] / max(array_sum(array_column($programs, 'count')), 1) * 100), 1) }}%</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- NEW: 2x2 Health Analytics Section -->
    <section class="mb-8">
        <h2 class="section-header">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Health Analytics
        </h2>
        
        <div class="charts-grid-2x2">
            <!-- Top Row - Chart 1 -->
            <div class="chart-card">
                <div class="dashboard-card">
                    <div class="card-content">
                        <div class="chart-header">
                            <h3>
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                                Year Level Cases
                            </h3>
                            <div class="flex gap-2">
                                <span class="program-badge badge-educ-bsed text-xs">BSED</span>
                                <span class="program-badge badge-educ-beed text-xs">BEED</span>
                            </div>
                        </div>
                        <div id="combinedYearLevelContainer" class="chart-container">
                            <div class="flex justify-center items-center h-full">
                                <div class="loading-spinner"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Row - Chart 2 -->
            <div class="chart-card">
                <div class="dashboard-card">
                    <div class="card-content">
                        <div class="chart-header">
                            <h3>
                                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Symptom Overview
                            </h3>
                            <button onclick="loadChart('combinedSymptomOverview')" class="refresh-btn no-print text-xs">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                        </div>
                        <div id="combinedSymptomOverviewContainer" class="chart-container">
                            <div class="flex justify-center items-center h-full">
                                <div class="loading-spinner"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row - Chart 3 -->
            <div class="chart-card">
                <div class="dashboard-card">
                    <div class="card-content">
                        <div class="chart-header">
                            <h3>
                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                                Top Symptoms This Month
                            </h3>
                            <button onclick="loadChart('topSymptomsMonth')" class="refresh-btn no-print text-xs">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                        </div>
                        <div id="topSymptomsMonthContainer" class="chart-container">
                            <div class="flex justify-center items-center h-full">
                                <div class="loading-spinner"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row - Chart 4 -->
            <div class="chart-card">
                <div class="dashboard-card">
                    <div class="card-content">
                        <div class="chart-header">
                            <h3>
                                <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                                </svg>
                                Symptom Trends (6 Months)
                            </h3>
                            <button onclick="loadChart('symptomTrends6Months')" class="refresh-btn no-print text-xs">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                        </div>
                        <div id="symptomTrends6MonthsContainer" class="large-chart-container">
                            <div class="flex justify-center items-center h-full">
                                <div class="loading-spinner"></div>
                            </div>
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
// Enhanced JavaScript for EDUC Health Analytics Dashboard with Real Data
const config = {
    routes: {
        combinedYearLevel: '{{ route("dean.dashboard.educ.chart.combined-year-level") }}',
        combinedSymptomOverview: '{{ route("dean.dashboard.educ.chart.combined-symptom-overview") }}',
        topSymptomsMonth: '{{ route("dean.dashboard.educ.chart.top-symptoms-month") }}',
        symptomTrends6Months: '{{ route("dean.dashboard.educ.chart.symptom-trends-6months") }}',
        realtimeStats: '{{ route("dean.dashboard-api.realtime-stats", ["department" => "EDUC"]) }}',
        exportReport: '{{ route("dean.dashboard.educ.export-report") }}',
        debugData: '{{ route("dean.dashboard.educ.debug-data") }}',
        clearCache: '{{ route("dean.dashboard.educ.clear-cache") }}'
    },
    colors: {
        educ_bsed: '#3B82F6',
        educ_beed: '#8B5CF6',
        educ_bsed_light: 'rgba(59, 130, 246, 0.2)',
        educ_beed_light: 'rgba(139, 92, 246, 0.2)',
        trend_colors: [
            { main: '#3B82F6', light: 'rgba(59, 130, 246, 0.2)' },
            { main: '#8B5CF6', light: 'rgba(139, 92, 246, 0.2)' },
            { main: '#10B981', light: 'rgba(16, 185, 129, 0.2)' },
            { main: '#F59E0B', light: 'rgba(245, 158, 11, 0.2)' },
            { main: '#EF4444', light: 'rgba(239, 68, 68, 0.2)' }
        ]
    }
};

const charts = {};

// Enhanced initialization with real data detection
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Enhanced EDUC Health Analytics Dashboard initializing...');
    
    // Add loading animation to stats
    animateStats();
    
    // Load all charts with real data priority
    setTimeout(() => loadChartWithFallback('combinedYearLevel'), 100);
    setTimeout(() => loadChartWithFallback('combinedSymptomOverview'), 300);
    setTimeout(() => loadChartWithFallback('topSymptomsMonth'), 500);
    setTimeout(() => loadChartWithFallback('symptomTrends6Months'), 700);
    
    console.log('✅ Enhanced EDUC Health Analytics Dashboard initialized successfully');
});

// Main chart loading function with real database data
async function loadChartWithFallback(chartType) {
    const container = document.getElementById(`${chartType}Container`);
    const url = config.routes[chartType];

    if (!url) {
        console.error(`Route not configured for ${chartType}`);
        loadFallbackData(chartType, container);
        return;
    }

    try {
        showEnhancedLoading(container, chartType);
        
        console.log(`📊 Loading real data for: ${chartType} from ${url}`);
        
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status} - ${response.statusText}`);
        }

        const data = await response.json();
        
        console.log(`📈 ${chartType} API Response:`, data);
        
        if (!data.success) {
            throw new Error(data.message || 'API returned unsuccessful response');
        }

        // Check if we have real database data
        if (hasRealDatabaseData(data, chartType)) {
            console.log(`✅ Using REAL database data for ${chartType}`);
            renderEnhancedChart(chartType, data, container);
            showToast(`✅ ${getChartDisplayName(chartType)} loaded with real data`, 'success');
        } else {
            console.warn(`⚠️ No real data in database for ${chartType}, using educational sample`);
            loadEducationalSampleData(chartType, container);
        }

    } catch (error) {
        console.error(`❌ Chart error for ${chartType}:`, error);
        console.log('Loading educational sample data due to error...');
        loadEducationalSampleData(chartType, container);
    }
}

// Enhanced real data detection for database content
function hasRealDatabaseData(data, chartType) {
    // If API explicitly says no data
    if (data.message && data.message.includes('No data') || data.message && data.message.includes('No students')) {
        return false;
    }
    
    // Check for actual database content in different chart structures
    switch (chartType) {
        case 'combinedYearLevel':
            return data.educ_bsed && data.educ_beed && 
                   (data.educ_bsed.some(val => val > 0) || data.educ_beed.some(val => val > 0));
        
        case 'combinedSymptomOverview':
        case 'topSymptomsMonth':
            return data.educ_bsed && data.educ_beed && 
                   data.labels && data.labels.length > 0 && 
                   data.labels[0] !== 'No data available';
        
        case 'symptomTrends6Months':
            return data.datasets && data.datasets.length > 0 && 
                   data.datasets.some(dataset => dataset.data && dataset.data.some(val => val > 0));
        
        default:
            return data.labels && data.labels.length > 0 && data.labels[0] !== 'No data available';
    }
}

// Load educational sample data (more realistic than random data)
function loadEducationalSampleData(chartType, container) {
    const sampleData = generateEducationalSampleData(chartType);
    renderEnhancedChart(chartType, sampleData, container);
    showToast(`📚 Showing educational sample data for ${getChartDisplayName(chartType)}`, 'info');
}

// Generate realistic educational sample data
function generateEducationalSampleData(chartType) {
    const currentMonth = new Date().toLocaleString('default', { month: 'short' });
    const currentYear = new Date().getFullYear().toString().slice(-2);
    
    // Realistic educational health patterns
    switch (chartType) {
        case 'combinedYearLevel':
            return {
                success: true,
                labels: ['1st Year', '2nd Year', '3rd Year', '4th Year'],
                educ_bsed: [18, 25, 32, 28], // Higher in upper years due to stress
                educ_beed: [22, 28, 35, 30], // BEED typically has more cases
                top_illness_bsed_by_year: {
                    '1st year': 'Common Cold',
                    '2nd year': 'Headache & Fatigue', 
                    '3rd year': 'Stress & Anxiety',
                    '4th year': 'Academic Pressure'
                },
                top_illness_beed_by_year: {
                    '1st year': 'Seasonal Allergies',
                    '2nd year': 'Respiratory Issues',
                    '3rd year': 'Stress & Sleep Issues',
                    '4th year': 'Anxiety & Fatigue'
                },
                highest_cases_bsed: '3rd Year',
                highest_cases_beed: '3rd Year',
                time_period: 'Last 6 months'
            };

        case 'combinedSymptomOverview':
            return {
                success: true,
                labels: ['Headache', 'Fatigue', 'Common Cold', 'Stress', 'Anxiety', 'Fever', 'Cough', 'Sore Throat', 'Muscle Pain', 'Allergies'],
                educ_bsed: [45, 38, 32, 28, 25, 22, 20, 18, 15, 12],
                educ_beed: [42, 45, 38, 32, 28, 25, 22, 20, 18, 15],
                top_illness_bsed: 'Headache',
                top_illness_beed: 'Fatigue',
                top_illness_bsed_count: 45,
                top_illness_beed_count: 45,
                time_period: 'Last 30 days'
            };

        case 'topSymptomsMonth':
            return {
                success: true,
                labels: ['Headache', 'Fatigue', 'Common Cold', 'Stress', 'Anxiety'],
                educ_bsed: [28, 25, 22, 18, 15],
                educ_beed: [25, 28, 24, 20, 16],
                top_illness_bsed: 'Headache',
                top_illness_beed: 'Fatigue',
                time_period: 'This month'
            };

        case 'symptomTrends6Months':
            const months = [];
            const baseTrends = {
                'Headache': [35, 38, 42, 45, 40, 38],
                'Fatigue': [32, 35, 38, 42, 45, 42],
                'Common Cold': [28, 32, 25, 22, 28, 32],
                'Stress': [25, 28, 32, 35, 38, 35],
                'Anxiety': [22, 25, 28, 32, 35, 32]
            };
            
            for (let i = 5; i >= 0; i--) {
                const date = new Date();
                date.setMonth(date.getMonth() - i);
                months.push(date.toLocaleString('default', { month: 'short' }) + ' ' + date.getFullYear().toString().slice(-2));
            }
            
            const datasets = Object.keys(baseTrends).map((symptom, index) => {
                const color = config.colors.trend_colors[index % config.colors.trend_colors.length];
                return {
                    label: symptom,
                    data: baseTrends[symptom],
                    borderColor: color.main,
                    backgroundColor: color.light,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: color.main,
                    pointBorderColor: '#1a1f2e',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                };
            });

            return {
                success: true,
                labels: months,
                datasets: datasets,
                top_illness: 'Fatigue',
                top_illness_trend: [32, 35, 38, 42, 45, 42],
                time_period: 'Last 6 months',
                total_symptoms_found: 5
            };

        default:
            return { success: true, labels: ['Sample Data'], data: [10] };
    }
}

// Enhanced chart rendering with real data
function renderEnhancedChart(type, data, container) {
    container.innerHTML = '<canvas></canvas>';
    const ctx = container.querySelector('canvas').getContext('2d');

    if (charts[type]) {
        charts[type].destroy();
    }

    const chartConfig = getEnhancedChartConfig(type, data);
    if (chartConfig) {
        try {
            charts[type] = new Chart(ctx, chartConfig);
            
            // Add analytics info to chart
            addChartAnalytics(type, data, container);
            
            // Add subtle entrance animation
            container.style.opacity = '0';
            container.style.transform = 'translateY(20px)';
            setTimeout(() => {
                container.style.transition = 'all 0.5s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
            
        } catch (error) {
            console.error(`Chart rendering error for ${type}:`, error);
            showEnhancedError(container, type, 'Chart rendering failed');
        }
    }
}

// Enhanced chart configuration
function getEnhancedChartConfig(type, data) {
    const baseConfig = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { 
                position: 'top',
                labels: { 
                    color: '#e5e7eb',
                    boxWidth: 12,
                    padding: 15,
                    font: { size: 11, weight: '600' },
                    usePointStyle: true
                }
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: 'rgba(45, 55, 72, 0.95)',
                titleColor: '#e5e7eb',
                bodyColor: '#e5e7eb',
                borderColor: '#4a5568',
                borderWidth: 1,
                titleFont: { size: 13, weight: '600' },
                bodyFont: { size: 12 },
                padding: 12,
                cornerRadius: 8,
                displayColors: true,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += context.parsed.y + ' cases';
                        return label;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { 
                    display: false, 
                    color: 'rgba(255, 255, 255, 0.1)',
                    drawBorder: false
                },
                ticks: { 
                    color: '#e5e7eb', 
                    font: { size: 11, weight: '500' }
                }
            },
            y: {
                beginAtZero: true,
                grid: { 
                    color: 'rgba(255, 255, 255, 0.05)',
                    drawBorder: false
                },
                ticks: { 
                    color: '#e5e7eb', 
                    font: { size: 11, weight: '500' },
                    stepSize: 1
                }
            }
        },
        animation: {
            duration: 1000,
            easing: 'easeOutQuart'
        }
    };

    switch (type) {
        case 'combinedYearLevel':
            return {
                type: 'bar',
                data: {
                    labels: data.labels || ['1st Year', '2nd Year', '3rd Year', '4th Year'],
                    datasets: [
                        {
                            label: 'BSED',
                            data: data.educ_bsed || [0, 0, 0, 0],
                            backgroundColor: config.colors.educ_bsed,
                            borderRadius: 8,
                            borderWidth: 0,
                            barPercentage: 0.7
                        },
                        {
                            label: 'BEED',
                            data: data.educ_beed || [0, 0, 0, 0],
                            backgroundColor: config.colors.educ_beed,
                            borderRadius: 8,
                            borderWidth: 0,
                            barPercentage: 0.7
                        }
                    ]
                },
                options: baseConfig
            };

        case 'combinedSymptomOverview':
            return {
                type: 'bar',
                data: {
                    labels: data.labels || ['No Data Available'],
                    datasets: [
                        {
                            label: 'BSED Cases',
                            data: data.educ_bsed || [0],
                            backgroundColor: config.colors.educ_bsed,
                            borderRadius: 8,
                            borderWidth: 0
                        },
                        {
                            label: 'BEED Cases',
                            data: data.educ_beed || [0],
                            backgroundColor: config.colors.educ_beed,
                            borderRadius: 8,
                            borderWidth: 0
                        }
                    ]
                },
                options: {
                    ...baseConfig,
                    indexAxis: 'y'
                }
            };

        case 'topSymptomsMonth':
            return {
                type: 'bar',
                data: {
                    labels: data.labels || ['No Data Available'],
                    datasets: [
                        {
                            label: 'BSED',
                            data: data.educ_bsed || [0],
                            backgroundColor: config.colors.educ_bsed,
                            borderRadius: 8,
                            borderWidth: 0
                        },
                        {
                            label: 'BEED',
                            data: data.educ_beed || [0],
                            backgroundColor: config.colors.educ_beed,
                            borderRadius: 8,
                            borderWidth: 0
                        }
                    ]
                },
                options: {
                    ...baseConfig,
                    indexAxis: 'y'
                }
            };

        case 'symptomTrends6Months':
            const datasets = (data.datasets || []).map((dataset, index) => {
                const color = config.colors.trend_colors[index % config.colors.trend_colors.length];
                return {
                    label: dataset.label || `Symptom ${index + 1}`,
                    data: dataset.data || [0, 0, 0, 0, 0, 0],
                    borderColor: color.main,
                    backgroundColor: color.light,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: color.main,
                    pointBorderColor: '#1a1f2e',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                };
            });

            return {
                type: 'line',
                data: {
                    labels: data.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: datasets
                },
                options: baseConfig
            };

        default:
            return null;
    }
}

// Add analytics information below charts
function addChartAnalytics(type, data, container) {
    const analyticsDiv = document.createElement('div');
    analyticsDiv.className = 'chart-analytics mt-3 text-xs text-gray-400';
    
    let analyticsHTML = '';
    
    switch (type) {
        case 'combinedYearLevel':
            if (data.highest_cases_bsed && data.highest_cases_beed) {
                analyticsHTML = `
                    <div class="flex justify-between items-center">
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            BSED Peak: ${data.highest_cases_bsed}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            BEED Peak: ${data.highest_cases_beed}
                        </span>
                    </div>
                `;
            }
            break;
            
        case 'combinedSymptomOverview':
            if (data.top_illness_bsed && data.top_illness_beed) {
                analyticsHTML = `
                    <div class="flex justify-between items-center">
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            BSED: ${data.top_illness_bsed} (${data.top_illness_bsed_count} cases)
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            BEED: ${data.top_illness_beed} (${data.top_illness_beed_count} cases)
                        </span>
                    </div>
                `;
            }
            break;
            
        case 'symptomTrends6Months':
            if (data.top_illness) {
                analyticsHTML = `
                    <div class="text-center">
                        <span>📊 Dominant Symptom: ${data.top_illness}</span>
                        ${data.total_symptoms_found ? `<span class="ml-3">• Tracking: ${data.total_symptoms_found} symptoms</span>` : ''}
                    </div>
                `;
            }
            break;
    }
    
    if (data.time_period) {
        analyticsHTML += `<div class="text-center mt-1">⏰ ${data.time_period}</div>`;
    }
    
    analyticsDiv.innerHTML = analyticsHTML;
    container.appendChild(analyticsDiv);
}

// Utility functions
function animateStats() {
    const statElements = document.querySelectorAll('.stat-value');
    statElements.forEach((element, index) => {
        const finalValue = element.textContent;
        element.textContent = '0';
        
        setTimeout(() => {
            let current = 0;
            const increment = parseInt(finalValue.replace(/,/g, '')) / 30;
            const timer = setInterval(() => {
                current += increment;
                if (current >= parseInt(finalValue.replace(/,/g, ''))) {
                    element.textContent = finalValue;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current).toLocaleString();
                }
            }, 50);
        }, index * 200);
    });
}

function showEnhancedLoading(container, chartType) {
    const displayName = getChartDisplayName(chartType);
    container.innerHTML = `
        <div class="flex flex-col items-center justify-center h-full p-6">
            <div class="loading-spinner mb-4"></div>
            <span class="text-sm text-gray-400 font-medium mb-2">Loading ${displayName}</span>
            <div class="w-32 h-1 bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 rounded-full animate-pulse"></div>
            </div>
            <span class="text-xs text-gray-500 mt-2">Checking database...</span>
        </div>
    `;
}

function showEnhancedError(container, chartType, message) {
    const displayName = getChartDisplayName(chartType);
    container.innerHTML = `
        <div class="flex flex-col items-center justify-center h-full p-6 text-center">
            <div class="w-16 h-16 bg-red-500/10 rounded-2xl flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h4 class="text-red-400 font-semibold mb-2">Failed to load ${displayName}</h4>
            <p class="text-xs text-gray-400 mb-4 max-w-xs">${message}</p>
            <button onclick="loadChartWithFallback('${chartType}')" 
                    class="text-blue-400 text-sm hover:text-blue-300 transition-colors font-medium px-4 py-2 border border-blue-400 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Try Again
            </button>
        </div>
    `;
}

function getChartDisplayName(chartType) {
    const names = {
        'combinedYearLevel': 'Year Level Distribution',
        'combinedSymptomOverview': 'Symptom Overview',
        'topSymptomsMonth': 'Top Symptoms',
        'symptomTrends6Months': 'Symptom Trends'
    };
    return names[chartType] || chartType;
}

// Dashboard controls
function refreshAllCharts() {
    showToast('🔄 Refreshing all health analytics...', 'info');
    
    // Clear all chart caches first
    Object.keys(charts).forEach(type => {
        if (charts[type]) {
            charts[type].destroy();
            delete charts[type];
        }
    });
    
    // Reload all charts
    const chartLoadOrder = [
        'combinedYearLevel',
        'combinedSymptomOverview', 
        'topSymptomsMonth',
        'symptomTrends6Months'
    ];
    
    chartLoadOrder.forEach((chartType, index) => {
        setTimeout(() => {
            loadChartWithFallback(chartType);
        }, index * 800);
    });
}

function exportDashboard() {
    showToast('📊 Preparing dashboard export...', 'info');
    
    // Create export data
    const exportData = {
        department: 'EDUC',
        generated_at: new Date().toISOString(),
        charts: {}
    };
    
    // Collect data from all charts
    Object.keys(charts).forEach(chartType => {
        if (charts[chartType]) {
            const chart = charts[chartType];
            exportData.charts[chartType] = {
                labels: chart.data.labels,
                datasets: chart.data.datasets.map(dataset => ({
                    label: dataset.label,
                    data: dataset.data
                }))
            };
        }
    });
    
    // Add stats data
    const stats = {
        total_students: document.getElementById('stat-students')?.textContent || '0',
        monthly_appointments: document.getElementById('stat-appointments')?.textContent || '0',
        pending_medical_records: document.getElementById('stat-medical')?.textContent || '0',
        monthly_consultations: document.getElementById('stat-consultations')?.textContent || '0'
    };
    
    exportData.stats = stats;
    
    // Create and download JSON file
    const dataStr = JSON.stringify(exportData, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(dataBlob);
    
    const link = document.createElement('a');
    link.href = url;
    link.download = `EDUC-Health-Analytics-${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    setTimeout(() => {
        showToast('✅ Dashboard export ready for download', 'success');
    }, 1000);
}

function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    const bgColors = {
        'error': 'bg-red-600',
        'success': 'bg-green-600',
        'info': 'bg-blue-600',
        'warning': 'bg-yellow-600'
    };
    
    const icons = {
        'error': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
        'success': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
        'info': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'warning': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>'
    };
    
    const bgColor = bgColors[type] || bgColors['info'];
    const icon = icons[type] || icons['info'];
    
    toast.className = `toast no-print ${bgColor} text-white`;
    toast.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${icon}
            </svg>
            <div class="flex-1">${message}</div>
        </div>
    `;
    
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4000);
}

// Debug and testing functions
async function testDataConnection() {
    showToast('🔍 Testing data connections...', 'info');
    
    const testResults = [];
    
    for (const chartType of ['combinedYearLevel', 'combinedSymptomOverview']) {
        try {
            const url = config.routes[chartType];
            const response = await fetch(url);
            const data = await response.json();
            
            testResults.push({
                chart: chartType,
                status: data.success ? '✅' : '❌',
                hasRealData: hasRealDatabaseData(data, chartType),
                dataPoints: data.labels ? data.labels.length : 0,
                route: url
            });
            
        } catch (error) {
            testResults.push({
                chart: chartType,
                status: '❌',
                error: error.message,
                route: config.routes[chartType]
            });
        }
    }
    
    console.table(testResults);
    
    // Show summary
    const successful = testResults.filter(r => r.status === '✅' && r.hasRealData).length;
    const total = testResults.length;
    
    if (successful === total) {
        showToast(`🎉 All ${total} charts connected to real data!`, 'success');
    } else if (successful > 0) {
        showToast(`📊 ${successful}/${total} charts have real data`, 'info');
    } else {
        showToast(`📚 Using educational sample data (no real data yet)`, 'warning');
    }
}

// Make functions available globally for debugging
window.debugEDUCData = testDataConnection;
window.refreshEDUCCharts = refreshAllCharts;
window.exportEDUCReport = exportDashboard;

// Auto-refresh every 5 minutes
setInterval(refreshAllCharts, 300000);

// Add keyboard shortcuts
document.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        refreshAllCharts();
    }
    if (e.ctrlKey && e.key === 'e') {
        e.preventDefault();
        exportDashboard();
    }
    if (e.ctrlKey && e.key === 'd') {
        e.preventDefault();
        testDataConnection();
    }
});

console.log('EDUC Dashboard JavaScript loaded successfully');
</script>
@endpush