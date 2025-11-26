@extends('layouts.nurse-app')

@section('title', 'Manage Appointments')

@push('styles')
<style>
    /* ========== ENHANCED ACTION BUTTON STYLES ========== */
    .action-button {
        display: flex;
        align-items: center;
        padding: 1rem 1.25rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: left;
        width: 100%;
        position: relative;
        gap: 0.875rem;
        font-family: inherit;
        margin-bottom: 0.75rem;
    }

    .action-button:hover {
        background: #f9fafb;
        border-color: #d1d5db;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    /* Icon Container */
    .action-button-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.625rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s ease;
        font-size: 1.125rem;
    }

    /* Content Container */
    .action-button-content {
        flex: 1;
        min-width: 0;
    }

    .action-button-title {
        display: block;
        font-weight: 600;
        font-size: 0.9375rem;
        margin-bottom: 0.125rem;
        color: #111827;
    }

    .action-button-description {
        display: block;
        font-size: 0.8125rem;
        color: #6b7280;
        line-height: 1.3;
    }

    /* Checkbox */
    .action-button-checkbox {
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid #d1d5db;
        border-radius: 0.25rem;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        background: white;
    }

    .action-button:hover .action-button-checkbox {
        border-color: #9ca3af;
    }

    /* ========== ACCEPT BUTTON VARIANT ========== */
    .action-button.accept-button .action-button-icon {
        background: #10b981;
        color: white;
    }

    .action-button.accept-button:hover .action-button-icon {
        background: #059669;
    }

    /* ========== RESCHEDULE BUTTON VARIANT ========== */
    .action-button.reschedule-button .action-button-icon {
        background: #f59e0b;
        color: white;
    }

    .action-button.reschedule-button:hover .action-button-icon {
        background: #d97706;
    }

    /* ========== CANCEL BUTTON VARIANT ========== */
    .action-button.cancel-button .action-button-icon {
        background: #ef4444;
        color: white;
    }

    .action-button.cancel-button:hover .action-button-icon {
        background: #dc2626;
    }

    /* ========== ACTIONS WRAPPER ========== */
    .actions-wrapper {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 1.25rem;
    }

    .wrapper-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9375rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 1rem;
    }

    .wrapper-header i {
        color: #6b7280;
        font-size: 1rem;
    }

    .actions-grid {
        display: grid;
        gap: 0.75rem;
    }

    /* ========== RESPONSIVE STYLES ========== */
    @media (max-width: 1024px) {
        .actions-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
    }

    @media (max-width: 768px) {
        .action-button {
            padding: 1rem 1.25rem;
        }

        .action-button-icon {
            width: 3rem;
            height: 3rem;
            font-size: 1.25rem;
        }

        .action-button-title {
            font-size: 0.95rem;
        }

        .action-button-description {
            font-size: 0.75rem;
        }

        .actions-grid {
            grid-template-columns: 1fr;
            gap: 0.875rem;
        }

        .actions-wrapper {
            padding: 1.25rem;
        }

        .wrapper-header {
            font-size: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
        }
    }

    @media (max-width: 480px) {
        .action-button {
            padding: 0.875rem 1rem;
            gap: 0.75rem;
        }

        .action-button-icon {
            width: 2.75rem;
            height: 2.75rem;
            font-size: 1.125rem;
            border-radius: 0.75rem;
        }

        .action-button-title {
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .action-button-description {
            font-size: 0.7rem;
        }

        .actions-wrapper {
            padding: 1rem;
            border-radius: 1rem;
        }

        .wrapper-header {
            font-size: 0.95rem;
            gap: 0.5rem;
        }

        .wrapper-header i {
            font-size: 1.125rem;
        }
    }

    /* ========== DISABLED STATE ========== */
    .action-button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
    }

    .action-button:disabled:hover {
        transform: none !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .action-button:disabled .action-button-icon {
        transform: none !important;
    }

    .action-button:disabled .action-button-arrow {
        transform: none !important;
        color: #d1d5db !important;
    }

    /* ========== FOCUS STATES FOR ACCESSIBILITY ========== */
    .action-button:focus {
        outline: 2px solid #3b82f6;
        outline-offset: 2px;
    }

    .action-button:focus:not(:focus-visible) {
        outline: none;
    }

    /* ========== LOADING STATE ========== */
    .action-button.loading {
        pointer-events: none;
        opacity: 0.7;
    }

    .action-button.loading .action-button-icon {
        animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
            transform: scale(1);
        }
        50% {
            opacity: 0.7;
            transform: scale(0.95);
        }
    }

    /* ========== SUCCESS STATE ========== */
    .action-button.success .action-button-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        transform: scale(1) !important;
        animation: successBounce 0.6s ease;
    }

    @keyframes successBounce {
        0%, 20%, 53%, 80%, 100% {
            transform: scale(1);
        }
        40%, 43% {
            transform: scale(1.1);
        }
        70% {
            transform: scale(1.05);
        }
    }

    /* ========== ENHANCED ACTION BUTTONS WITH CHECKBOX STYLE ========== */
    .action-button-checkbox {
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid #d1d5db;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        background: white;
        position: relative;
    }

    .action-button-checkbox::after {
        content: '';
        width: 0.75rem;
        height: 0.75rem;
        background: #3b82f6;
        border-radius: 0.125rem;
        transform: scale(0);
        transition: transform 0.2s ease;
    }

    .action-button:hover .action-button-checkbox {
        border-color: #9ca3af;
    }

    .action-button.selected .action-button-checkbox {
        border-color: #3b82f6;
        background: #3b82f6;
    }

    .action-button.selected .action-button-checkbox::after {
        transform: scale(1);
    }

    /* Arrow icon */
    .action-button-arrow {
        color: #9ca3af;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }

    .action-button:hover .action-button-arrow {
        color: #6b7280;
        transform: translateX(2px);
    }

    /* ========== TIME SLOT BUTTONS FOR RESCHEDULE MODAL ========== */
    .time-slot-btn {
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.75rem;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        font-weight: 500;
        color: #374151;
    }

    .time-slot-btn:hover {
        border-color: #3b82f6;
        background: #f0f9ff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }

    .time-slot-btn.selected {
        border-color: #3b82f6;
        background: #3b82f6;
        color: white;
        transform: scale(1.02);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.25);
    }

    .time-slot-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
        transform: none;
        border-color: #d1d5db;
        background: #f9fafb;
        color: #9ca3af;
    }

    /* ========== LOADING SPINNER ========== */
    .loading-spinner {
        width: 2rem;
        height: 2rem;
        border: 3px solid #f3f4f6;
        border-top: 3px solid #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* ========== MODAL OVERLAY ANIMATIONS ========== */
    .modal-overlay {
        animation: fadeIn 0.3s ease-out;
    }

    .modal-content {
        animation: slideUp 0.3s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { 
            opacity: 0;
            transform: translateY(20px) scale(0.95);
        }
        to { 
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* ========== STATUS BADGES ========== */
    .status-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-badge.pending {
        background-color: #fef3c7;
        color: #92400e;
    }

    .status-badge.confirmed {
        background-color: #d1fae5;
        color: #065f46;
    }

    .status-badge.cancelled {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .status-badge.completed {
        background-color: #e0e7ff;
        color: #3730a3;
    }

    /* ========== ACTION BUTTONS IN TABLE ========== */
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }

    .action-btn:hover {
        transform: scale(1.1);
    }

    .view-btn {
        background-color: #eff6ff;
        color: #1d4ed8;
    }

    .view-btn:hover {
        background-color: #dbeafe;
    }

    .reschedule-btn {
        background-color: #fffbeb;
        color: #d97706;
    }

    .reschedule-btn:hover {
        background-color: #fef3c7;
    }

    /* ========== FILTER TABS ========== */
    .filter-tab {
        transition: all 0.3s ease;
    }

    .filter-tab.active {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }

    /* ========== SELECTED SUMMARY ========== */
    .selected-summary {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* ========== SUCCESS ALERT ========== */
    .success-alert {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Manage Appointments</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Review and manage patient appointments</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 w-full sm:w-auto">
            <a href="{{ route('nurse.appointments.calendar') }}"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 text-center text-sm sm:text-base">
                <i class="fas fa-calendar-alt mr-2"></i>Calendar View
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 success-alert" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 success-alert" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Enhanced Statistics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-clock text-yellow-600 text-lg sm:text-xl"></i>
                </div>
                <div class="ml-2 sm:ml-3">
                    <p class="text-xs sm:text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-base sm:text-lg font-semibold text-gray-900">{{ $stats['pending'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-blue-600 text-lg sm:text-xl"></i>
                </div>
                <div class="ml-2 sm:ml-3">
                    <p class="text-xs sm:text-sm font-medium text-gray-500">Confirmed</p>
                    <p class="text-base sm:text-lg font-semibold text-gray-900">{{ $stats['confirmed'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-star text-green-600 text-lg sm:text-xl"></i>
                </div>
                <div class="ml-2 sm:ml-3">
                    <p class="text-xs sm:text-sm font-medium text-gray-500">Latest</p>
                    <p class="text-base sm:text-lg font-semibold text-gray-900">{{ $stats['latest'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 border-l-4 border-orange-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-calendar-alt text-orange-600 text-lg sm:text-xl"></i>
                </div>
                <div class="ml-2 sm:ml-3">
                    <p class="text-xs sm:text-sm font-medium text-gray-500">Rescheduled</p>
                    <p class="text-base sm:text-lg font-semibold text-gray-900">{{ $stats['rescheduled'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 border-l-4 border-red-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-ban text-red-600 text-lg sm:text-xl"></i>
                </div>
                <div class="ml-2 sm:ml-3">
                    <p class="text-xs sm:text-sm font-medium text-gray-500">Cancelled</p>
                    <p class="text-base sm:text-lg font-semibold text-gray-900">{{ $stats['cancelled'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-chart-line text-purple-600 text-lg sm:text-xl"></i>
                </div>
                <div class="ml-2 sm:ml-3">
                    <p class="text-xs sm:text-sm font-medium text-gray-500">Total</p>
                    <p class="text-base sm:text-lg font-semibold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Filter Tabs -->
    <div class="mb-6 bg-white rounded-lg shadow-sm p-4">
        <div class="flex flex-wrap gap-2">
            <button onclick="filterAppointments('all')" 
                    class="filter-tab active px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 bg-blue-100 text-blue-700 border-2 border-blue-200">
                <i class="fas fa-list mr-2"></i>All Appointments
            </button>
            <button onclick="filterAppointments('latest')" 
                    class="filter-tab px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 bg-green-100 text-green-700 border-2 border-green-200">
                <i class="fas fa-star mr-2"></i>Latest
            </button>
            <button onclick="filterAppointments('pending')" 
                    class="filter-tab px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 bg-yellow-100 text-yellow-700 border-2 border-yellow-200">
                <i class="fas fa-clock mr-2"></i>Pending
            </button>
            <button onclick="filterAppointments('confirmed')" 
                    class="filter-tab px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 bg-blue-100 text-blue-700 border-2 border-blue-200">
                <i class="fas fa-check-circle mr-2"></i>Confirmed
            </button>
            <button onclick="filterAppointments('rescheduled')" 
                    class="filter-tab px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 bg-orange-100 text-orange-700 border-2 border-orange-200">
                <i class="fas fa-calendar-alt mr-2"></i>Rescheduled
            </button>
            <button onclick="filterAppointments('cancelled')" 
                    class="filter-tab px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 bg-red-100 text-red-700 border-2 border-red-200">
                <i class="fas fa-ban mr-2"></i>Cancelled
            </button>
        </div>
    </div>

    <!-- Search & Filter Section -->
    <div class="mb-6 bg-white rounded-lg shadow-sm p-4 sm:p-6">
        <form method="GET" action="{{ route('nurse.appointments.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                           placeholder="Search by patient or reason...">
                </div>

                <div>
                    <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status" name="status"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        <option value="">All Statuses</option>
                        @foreach(\App\Models\Appointment::getStatusOptions() as $value => $label)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="type" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select id="type" name="type"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        <option value="">All Types</option>
                        @foreach(\App\Models\Appointment::getTypeOptions() as $value => $label)
                            @if($value !== 'emergency')
                                <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                    <i class="fas fa-filter mr-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Organized Appointments Sections -->
    <div class="space-y-6">
        <!-- Latest Appointments Section -->
        @if($latestAppointments->count() > 0)
        <div class="bg-white rounded-lg shadow-md overflow-hidden appointment-section" data-category="latest">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-star text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Latest Appointments</h3>
                        <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                            {{ $latestAppointments->count() }} New
                        </span>
                    </div>
                    <span class="text-sm text-green-600 font-medium">Most Recent</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($latestAppointments as $appointment)
                            <tr class="latest-appointment border-b hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm" data-label="Patient">
                                    <div class="font-medium text-gray-900">{{ $appointment->getPatientName() }}</div>
                                    <div class="text-xs text-gray-500">{{ $appointment->getPatientStudentId() }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Date & Time">
                                    <div class="text-gray-900">{{ $appointment->formatted_date_time }}</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mt-1">
                                        <i class="fas fa-star mr-1 text-xs"></i>Latest
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Type">
                                    <span class="{{ $appointment->appointment_type_badge_class }} text-xs px-2 py-1 rounded-full">
                                        {{ $appointment->appointment_type_display }}
                                        @if($appointment->is_urgent)
                                            <i class="fas fa-exclamation-circle text-red-500 ml-1"></i>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Status">
                                    <span class="{{ $appointment->status_badge_class }} status-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        {{ $appointment->status_display }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Actions">
                                    <div class="flex flex-wrap gap-1">
                                        <button onclick="openViewModal({{ $appointment->id }})"
                                           class="view-btn action-btn" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if($appointment->canBeRescheduledByNurse())
                                            <button onclick="openRescheduleModal({{ $appointment->id }})"
                                               class="reschedule-btn action-btn" title="Reschedule">
                                                <i class="fas fa-calendar-alt"></i>
                                            </button>
                                        @endif

                                        @if($appointment->isPending())
                                            <button onclick="openAcceptModal({{ $appointment->id }})" 
                                                    class="text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100 action-btn"
                                                    title="Accept Appointment">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif

                                        @if($appointment->canBeCancelled())
                                            <button onclick="openCancelModal({{ $appointment->id }})" 
                                                    class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 action-btn"
                                                    title="Cancel Appointment">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Pending Appointments Section -->
        @if($pendingAppointments->count() > 0)
        <div class="bg-white rounded-lg shadow-md overflow-hidden appointment-section" data-category="pending">
            <div class="bg-gradient-to-r from-yellow-50 to-amber-50 px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-clock text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Pending Approval</h3>
                        <span class="bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                            {{ $pendingAppointments->count() }} Waiting
                        </span>
                    </div>
                    <span class="text-sm text-yellow-600 font-medium">Requires Action</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingAppointments as $appointment)
                            <tr class="pending-appointment border-b hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm" data-label="Patient">
                                    <div class="font-medium text-gray-900">{{ $appointment->getPatientName() }}</div>
                                    <div class="text-xs text-gray-500">{{ $appointment->getPatientStudentId() }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Date & Time">
                                    <div class="text-gray-900">{{ $appointment->formatted_date_time }}</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">
                                        <i class="fas fa-clock mr-1 text-xs"></i>Pending
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Type">
                                    <span class="{{ $appointment->appointment_type_badge_class }} text-xs px-2 py-1 rounded-full">
                                        {{ $appointment->appointment_type_display }}
                                        @if($appointment->is_urgent)
                                            <i class="fas fa-exclamation-circle text-red-500 ml-1"></i>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Status">
                                    <span class="{{ $appointment->status_badge_class }} status-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        {{ $appointment->status_display }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Actions">
                                    <div class="flex flex-wrap gap-1">
                                        <button onclick="openViewModal({{ $appointment->id }})"
                                           class="view-btn action-btn" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if($appointment->canBeRescheduledByNurse())
                                            <button onclick="openRescheduleModal({{ $appointment->id }})"
                                               class="reschedule-btn action-btn" title="Reschedule">
                                                <i class="fas fa-calendar-alt"></i>
                                            </button>
                                        @endif

                                        @if($appointment->isPending())
                                            <button onclick="openAcceptModal({{ $appointment->id }})" 
                                                    class="text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100 action-btn"
                                                    title="Accept Appointment">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif

                                        @if($appointment->canBeCancelled())
                                            <button onclick="openCancelModal({{ $appointment->id }})" 
                                                    class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 action-btn"
                                                    title="Cancel Appointment">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Confirmed Appointments Section -->
        @if($confirmedAppointments->count() > 0)
        <div class="bg-white rounded-lg shadow-md overflow-hidden appointment-section" data-category="confirmed">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-check-circle text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Confirmed Appointments</h3>
                        <span class="bg-blue-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                            {{ $confirmedAppointments->count() }} Confirmed
                        </span>
                    </div>
                    <span class="text-sm text-blue-600 font-medium">Ready for Visit</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($confirmedAppointments as $appointment)
                            <tr class="confirmed-appointment border-b hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm" data-label="Patient">
                                    <div class="font-medium text-gray-900">{{ $appointment->getPatientName() }}</div>
                                    <div class="text-xs text-gray-500">{{ $appointment->getPatientStudentId() }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Date & Time">
                                    <div class="text-gray-900">{{ $appointment->formatted_date_time }}</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                        <i class="fas fa-check-circle mr-1 text-xs"></i>Confirmed
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Type">
                                    <span class="{{ $appointment->appointment_type_badge_class }} text-xs px-2 py-1 rounded-full">
                                        {{ $appointment->appointment_type_display }}
                                        @if($appointment->is_urgent)
                                            <i class="fas fa-exclamation-circle text-red-500 ml-1"></i>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Status">
                                    <span class="{{ $appointment->status_badge_class }} status-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        {{ $appointment->status_display }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Actions">
                                    <div class="flex flex-wrap gap-1">
                                        <button onclick="openViewModal({{ $appointment->id }})"
                                           class="view-btn action-btn" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if($appointment->canBeRescheduledByNurse())
                                            <button onclick="openRescheduleModal({{ $appointment->id }})"
                                               class="reschedule-btn action-btn" title="Reschedule">
                                                <i class="fas fa-calendar-alt"></i>
                                            </button>
                                        @endif

                                        @if($appointment->canBeCancelled())
                                            <button onclick="openCancelModal({{ $appointment->id }})" 
                                                    class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 action-btn"
                                                    title="Cancel Appointment">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Rescheduled Appointments Section -->
        @if($rescheduledAppointments->count() > 0)
        <div class="bg-white rounded-lg shadow-md overflow-hidden appointment-section" data-category="rescheduled">
            <div class="bg-gradient-to-r from-orange-50 to-amber-50 px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Rescheduled</h3>
                        <span class="bg-orange-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                            {{ $rescheduledAppointments->count() }} Changed
                        </span>
                    </div>
                    <span class="text-sm text-orange-600 font-medium">Date Modified</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rescheduledAppointments as $appointment)
                            <tr class="rescheduled-appointment border-b hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm" data-label="Patient">
                                    <div class="font-medium text-gray-900">{{ $appointment->getPatientName() }}</div>
                                    <div class="text-xs text-gray-500">{{ $appointment->getPatientStudentId() }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Date & Time">
                                    <div class="text-gray-900">{{ $appointment->formatted_date_time }}</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 mt-1">
                                        <i class="fas fa-calendar-alt mr-1 text-xs"></i>Rescheduled
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Type">
                                    <span class="{{ $appointment->appointment_type_badge_class }} text-xs px-2 py-1 rounded-full">
                                        {{ $appointment->appointment_type_display }}
                                        @if($appointment->is_urgent)
                                            <i class="fas fa-exclamation-circle text-red-500 ml-1"></i>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Status">
                                    <span class="{{ $appointment->status_badge_class }} status-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        {{ $appointment->status_display }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Actions">
                                    <div class="flex flex-wrap gap-1">
                                        <button onclick="openViewModal({{ $appointment->id }})"
                                           class="view-btn action-btn" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if($appointment->canBeRescheduledByNurse())
                                            <button onclick="openRescheduleModal({{ $appointment->id }})"
                                               class="reschedule-btn action-btn" title="Reschedule">
                                                <i class="fas fa-calendar-alt"></i>
                                            </button>
                                        @endif

                                        @if($appointment->isPending())
                                            <button onclick="openAcceptModal({{ $appointment->id }})" 
                                                    class="text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100 action-btn"
                                                    title="Accept Appointment">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif

                                        @if($appointment->canBeCancelled())
                                            <button onclick="openCancelModal({{ $appointment->id }})" 
                                                    class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 action-btn"
                                                    title="Cancel Appointment">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Cancelled Appointments Section -->
        @if($cancelledAppointments->count() > 0)
        <div class="bg-white rounded-lg shadow-md overflow-hidden appointment-section" data-category="cancelled">
            <div class="bg-gradient-to-r from-red-50 to-pink-50 px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-ban text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Cancelled Appointments</h3>
                        <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                            {{ $cancelledAppointments->count() }} Cancelled
                        </span>
                    </div>
                    <span class="text-sm text-red-600 font-medium">Not Active</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cancellation Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cancelledAppointments as $appointment)
                            <tr class="cancelled-appointment border-b hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm" data-label="Patient">
                                    <div class="font-medium text-gray-900">{{ $appointment->getPatientName() }}</div>
                                    <div class="text-xs text-gray-500">{{ $appointment->getPatientStudentId() }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Date & Time">
                                    <div class="text-gray-900">{{ $appointment->formatted_date_time }}</div>
                                    <div class="text-xs text-red-600 mt-1">
                                        <i class="fas fa-times-circle mr-1"></i>Cancelled
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Type">
                                    <span class="{{ $appointment->appointment_type_badge_class }} text-xs px-2 py-1 rounded-full">
                                        {{ $appointment->appointment_type_display }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Status">
                                    <span class="bg-red-100 text-red-800 px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        <i class="fas fa-ban mr-1"></i>{{ $appointment->status_display }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Cancellation Reason">
                                    <span class="text-gray-600 text-xs">
                                        {{ $appointment->cancellation_reason ?? 'No reason provided' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Other Appointments Section -->
        @if($otherAppointments->count() > 0)
        <div class="bg-white rounded-lg shadow-md overflow-hidden appointment-section" data-category="other">
            <div class="bg-gradient-to-r from-gray-50 to-blue-50 px-6 py-4 border-b">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar text-white text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Other Appointments</h3>
                    <span class="bg-gray-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                        {{ $otherAppointments->count() }} Total
                    </span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($otherAppointments as $appointment)
                            <tr class="border-b hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm" data-label="Patient">
                                    <div class="font-medium text-gray-900">{{ $appointment->getPatientName() }}</div>
                                    <div class="text-xs text-gray-500">{{ $appointment->getPatientStudentId() }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Date & Time">
                                    <div class="text-gray-900">{{ $appointment->formatted_date_time }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Type">
                                    <span class="{{ $appointment->appointment_type_badge_class }} text-xs px-2 py-1 rounded-full">
                                        {{ $appointment->appointment_type_display }}
                                        @if($appointment->is_urgent)
                                            <i class="fas fa-exclamation-circle text-red-500 ml-1"></i>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Status">
                                    <span class="{{ $appointment->status_badge_class }} status-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        {{ $appointment->status_display }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" data-label="Actions">
                                    <div class="flex flex-wrap gap-1">
                                        <button onclick="openViewModal({{ $appointment->id }})"
                                           class="view-btn action-btn" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if($appointment->canBeRescheduledByNurse())
                                            <button onclick="openRescheduleModal({{ $appointment->id }})"
                                               class="reschedule-btn action-btn" title="Reschedule">
                                                <i class="fas fa-calendar-alt"></i>
                                            </button>
                                        @endif

                                        @if($appointment->isPending())
                                            <button onclick="openAcceptModal({{ $appointment->id }})" 
                                                    class="text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100 action-btn"
                                                    title="Accept Appointment">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif

                                        @if($appointment->canBeCancelled())
                                            <button onclick="openCancelModal({{ $appointment->id }})" 
                                                    class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 action-btn"
                                                    title="Cancel Appointment">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Empty State -->
        @if($latestAppointments->count() == 0 && $pendingAppointments->count() == 0 && $confirmedAppointments->count() == 0 && $rescheduledAppointments->count() == 0 && $cancelledAppointments->count() == 0 && $otherAppointments->count() == 0)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-12 text-center">
                <div class="flex flex-col items-center">
                    <i class="fas fa-calendar-times text-3xl sm:text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">No appointments found</h3>
                    <p class="text-sm text-gray-500">Try adjusting your search or filter criteria</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ========== ENHANCED MODALS ========== --}}

{{-- View Appointment Modal --}}
<div id="viewModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50 p-3 sm:p-4 overflow-y-auto modal-overlay">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-auto my-4 sm:my-8 max-h-[95vh] overflow-y-auto modal-content transform transition-all duration-300 ease-out">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 sticky top-0 rounded-t-2xl z-10">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-xl flex items-center justify-center shadow-sm">
                    <i class="fas fa-calendar-alt text-blue-600 text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg sm:text-2xl font-bold text-gray-800">Appointment Details</h3>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1">Complete appointment information</p>
                </div>
            </div>
            <button onclick="closeViewModal()" 
                    class="text-gray-400 hover:text-gray-600 p-2 hover:bg-blue-100 rounded-lg transition duration-200 transform hover:scale-110"
                    aria-label="Close modal">
                <i class="fas fa-times text-xl sm:text-2xl"></i>
            </button>
        </div>

        <div id="viewModalContent" class="p-4 sm:p-6">
            <!-- Loading State with Enhanced Design -->
            <div class="flex justify-center items-center py-16">
                <div class="text-center">
                    <div class="loading-spinner mx-auto mb-4 w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                    <p class="text-gray-600 font-medium">Loading appointment details...</p>
                    <p class="text-sm text-gray-400 mt-1">Please wait a moment</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Accept Appointment Modal --}}
<div id="acceptModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto my-4 sm:my-8 transform transition-all duration-300 scale-95 hover:scale-100">
        <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50 rounded-t-2xl">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-xl flex items-center justify-center shadow-sm">
                    <i class="fas fa-check-circle text-green-600 text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800">Accept Appointment</h3>
                    <p class="text-xs text-gray-600 mt-1">Confirm appointment approval</p>
                </div>
            </div>
            <button onclick="closeAcceptModal()" 
                    class="text-gray-400 hover:text-gray-600 p-2 hover:bg-green-100 rounded-lg transition duration-200"
                    aria-label="Close modal">
                <i class="fas fa-times text-lg sm:text-xl"></i>
            </button>
        </div>

        <div class="p-4 sm:p-6">
            <div class="flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mx-auto mb-4">
                <i class="fas fa-check text-green-600 text-2xl"></i>
            </div>
            <h4 class="text-lg font-semibold text-gray-800 text-center mb-2">Confirm Acceptance</h4>
            <p class="text-gray-600 mb-6 text-sm sm:text-base text-center">Are you sure you want to accept this appointment? The student will be notified via email.</p>

            <form id="acceptForm" method="POST" class="space-y-4">
                @csrf
                <div class="flex flex-col-reverse sm:flex-row gap-3">
                    <button type="button" onclick="closeAcceptModal()" 
                            class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition font-medium text-sm sm:text-base transform hover:scale-105 active:scale-95">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-xl transition font-medium text-sm sm:text-base transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl">
                        <i class="fas fa-check mr-2"></i>Accept Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reschedule Appointment Modal --}}
<div id="rescheduleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-auto my-4 sm:my-8 transform transition-all duration-300 max-h-[95vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 sticky top-0 z-10">
            <div class="flex items-center space-x-2 sm:space-x-3 flex-1 min-w-0">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i class="fas fa-calendar-alt text-blue-600 text-base sm:text-lg"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-base sm:text-xl font-bold text-gray-900 truncate">Reschedule Appointment</h3>
                    <p class="text-xs sm:text-sm text-gray-600 truncate">Select a new date and time</p>
                </div>
            </div>
            <button onclick="closeRescheduleModal()" 
                    class="text-gray-400 hover:text-gray-600 transition p-2 hover:bg-blue-100 rounded-lg flex-shrink-0 ml-2 transform hover:scale-110"
                    aria-label="Close modal">
                <i class="fas fa-times text-lg sm:text-xl"></i>
            </button>
        </div>

        <!-- Modal Content -->
        <form id="rescheduleForm" method="POST" class="p-4 sm:p-6">
            @csrf
            @method('PATCH')

            <!-- Two Column Layout -->
            <div class="grid lg:grid-cols-2 gap-6 sm:gap-8">
                <!-- Left Column: Date and Reason -->
                <div class="space-y-6 sm:space-y-8">
                    <!-- Step 1: Date Selection -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 text-white rounded-full text-xs font-bold flex-shrink-0 shadow-sm">1</div>
                            <label for="reschedule_new_date" class="text-sm sm:text-base font-semibold text-gray-900">
                                Select Date <span class="text-red-500">*</span>
                            </label>
                        </div>
                        <div class="relative">
                            <input type="date" 
                                   id="reschedule_new_date" 
                                   name="new_appointment_date"
                                   min="{{ today()->format('Y-m-d') }}"
                                   max="{{ today()->addDays(30)->format('Y-m-d') }}"
                                   required
                                   aria-label="Select new appointment date"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-white text-sm sm:text-base hover:border-gray-400 shadow-sm">
                            <i class="fas fa-calendar absolute right-4 top-3.5 text-blue-400 pointer-events-none text-sm sm:text-base"></i>
                        </div>
                        <p class="text-xs text-gray-500">Valid for next 30 days from today</p>
                    </div>

                    <!-- Step 3: Reason -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 text-white rounded-full text-xs font-bold flex-shrink-0 shadow-sm">3</div>
                            <label for="reschedule_reason_input" class="text-sm sm:text-base font-semibold text-gray-900">
                                Reason for Rescheduling <span class="text-red-500">*</span>
                            </label>
                        </div>
                        <div class="relative">
                            <textarea id="reschedule_reason_input" 
                                      name="reschedule_reason" 
                                      rows="4"
                                      required
                                      minlength="10"
                                      maxlength="500"
                                      aria-label="Reason for rescheduling"
                                      placeholder="Please explain why you need to reschedule this appointment..."
                                      class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none text-sm sm:text-base hover:border-gray-400 shadow-sm"></textarea>
                            <div class="absolute bottom-3 right-4 text-xs text-gray-400 pointer-events-none bg-white px-2 py-1 rounded-lg">
                                <span id="charCount">0</span>/500
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">Minimum 10 characters required</p>
                    </div>
                </div>

                <!-- Right Column: Time Selection -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 text-white rounded-full text-xs font-bold flex-shrink-0 shadow-sm">2</div>
                            <label class="text-sm sm:text-base font-semibold text-gray-900">
                                Select Time <span class="text-red-500">*</span>
                            </label>
                        </div>
                        <button type="button" onclick="refreshTimeSlots()" 
                                class="text-blue-600 hover:text-blue-700 p-2 hover:bg-blue-50 rounded-lg transition duration-200 transform hover:rotate-180" 
                                title="Refresh time slots" 
                                aria-label="Refresh available time slots">
                            <i class="fas fa-sync-alt text-sm"></i>
                        </button>
                    </div>
                    
                    <!-- Enhanced Time Slots Container -->
                    <div id="rescheduleTimeSlots" class="p-4 bg-gradient-to-br from-gray-50 to-blue-50 rounded-xl border-2 border-gray-200 min-h-[200px] flex flex-col shadow-inner">
                        <div class="flex flex-col items-center justify-center flex-1 text-gray-400 space-y-3">
                            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm">
                                <i class="fas fa-clock text-2xl text-gray-300"></i>
                            </div>
                            <p class="text-sm font-medium text-center text-gray-500">Select a date to see available times</p>
                            <p class="text-xs text-center text-gray-400">Times will appear here after date selection</p>
                        </div>
                    </div>
                    
                    <input type="hidden" id="reschedule_new_time" name="new_appointment_time" required aria-label="Selected appointment time">
                    
                    <!-- Enhanced Info Box -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 p-4 rounded-r-xl">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-info-circle text-blue-600 mt-0.5 flex-shrink-0 text-lg"></i>
                            <div>
                                <p class="text-sm font-medium text-blue-900">How to select a time</p>
                                <p class="text-xs text-blue-700 mt-1">Click on any available time slot. Your selection will be highlighted in blue.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Selected Summary Box -->
            <div id="selectedSummary" class="hidden bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-4 rounded-xl selected-summary mt-6 shadow-sm">
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 shadow-sm">
                        <i class="fas fa-check text-white text-sm"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-900">Appointment Scheduled Successfully!</p>
                        <div class="mt-2 space-y-2">
                            <div class="flex items-center space-x-2 text-sm text-gray-700">
                                <i class="fas fa-calendar-check text-green-500 w-4"></i>
                                <span id="summaryDate" class="font-medium"></span>
                            </div>
                            <div class="flex items-center space-x-2 text-sm text-gray-700">
                                <i class="fas fa-clock text-green-500 w-4"></i>
                                <span id="summaryTime" class="font-semibold"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Form Actions -->
            <div class="flex flex-col-reverse sm:flex-row gap-3 pt-6 sm:pt-8 border-t border-gray-200 mt-6 sm:mt-8">
                <button type="button" 
                        onclick="closeRescheduleModal()" 
                        class="w-full sm:w-auto px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition duration-200 text-sm sm:text-base flex items-center justify-center space-x-2 transform hover:scale-105 active:scale-95">
                    <i class="fas fa-times"></i>
                    <span>Cancel</span>
                </button>
                <button type="submit" 
                        id="submitBtn"
                        class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold transition duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95">
                    <i class="fas fa-calendar-check"></i>
                    <span class="hidden sm:inline">Confirm Reschedule</span>
                    <span class="sm:hidden">Confirm</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Cancel Appointment Modal --}}
<div id="cancelModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto my-4 sm:my-8 transform transition-all duration-300 scale-95 hover:scale-100">
        <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 bg-gradient-to-r from-red-50 to-pink-50 rounded-t-2xl">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-red-100 rounded-xl flex items-center justify-center shadow-sm">
                    <i class="fas fa-ban text-red-600 text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800">Cancel Appointment</h3>
                    <p class="text-xs text-gray-600 mt-1">Provide cancellation reason</p>
                </div>
            </div>
            <button onclick="closeCancelModal()" 
                    class="text-gray-400 hover:text-gray-600 p-2 hover:bg-red-100 rounded-lg transition duration-200"
                    aria-label="Close modal">
                <i class="fas fa-times text-lg sm:text-xl"></i>
            </button>
        </div>

        <div class="p-4 sm:p-6">
            <div class="flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <h4 class="text-lg font-semibold text-gray-800 text-center mb-2">Cancel Appointment</h4>
            <p class="text-gray-600 mb-6 text-sm sm:text-base text-center">Please provide a reason for cancelling this appointment. This helps us improve our service.</p>

            <form id="cancelForm" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label for="cancellation_reason" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                        Cancellation Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea id="cancellation_reason" 
                              name="cancellation_reason" 
                              rows="4"
                              required 
                              minlength="10"
                              maxlength="500"
                              class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition text-sm sm:text-base resize-none shadow-sm"
                              placeholder="Please explain why this appointment needs to be cancelled..."></textarea>
                    <div class="flex justify-between items-center mt-2">
                        <p class="text-xs text-gray-500">Minimum 10 characters required</p>
                        <p class="text-xs text-gray-400"><span id="cancelCharCount">0</span>/500</p>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row gap-3">
                    <button type="button" onclick="closeCancelModal()" 
                            class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition font-medium text-sm sm:text-base transform hover:scale-105 active:scale-95">
                        <i class="fas fa-arrow-left mr-2"></i>Keep Appointment
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white rounded-xl transition font-medium text-sm sm:text-base transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl">
                        <i class="fas fa-ban mr-2"></i>Cancel Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let selectedTimeSlot = null;
let currentAppointmentId = null;

// ========== AUTO-REFRESH FUNCTIONALITY ==========
let refreshInterval;
let isModalOpen = false;

function startAutoRefresh() {
    // Refresh every 3 seconds (3000 milliseconds)
    refreshInterval = setInterval(() => {
        if (!isModalOpen) {
            refreshAppointments();
        }
    }, 3000);
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

function refreshAppointments() {
    // Get current URL parameters to maintain filters
    const currentUrl = new URL(window.location.href);
    const searchParams = new URLSearchParams(currentUrl.search);
    
    // Add a timestamp to prevent caching
    searchParams.set('_', Date.now());
    
    fetch(`${currentUrl.pathname}?${searchParams.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Cache-Control': 'no-cache'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Create a temporary container to parse the new HTML
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        
        // Update statistics cards
        const newStatsContainer = tempDiv.querySelector('.grid.grid-cols-2.lg\\:grid-cols-6');
        if (newStatsContainer) {
            const currentStatsContainer = document.querySelector('.grid.grid-cols-2.lg\\:grid-cols-6');
            if (currentStatsContainer) {
                currentStatsContainer.innerHTML = newStatsContainer.innerHTML;
            }
        }
        
        // Update appointment sections
        const appointmentSections = tempDiv.querySelectorAll('.appointment-section');
        appointmentSections.forEach(newSection => {
            const category = newSection.dataset.category;
            const currentSection = document.querySelector(`.appointment-section[data-category="${category}"]`);
            
            if (currentSection) {
                currentSection.innerHTML = newSection.innerHTML;
            }
        });
        
        // Update empty state if needed
        const newEmptyState = tempDiv.querySelector('.bg-white.rounded-lg.shadow-md.overflow-hidden:last-child');
        const currentEmptyState = document.querySelector('.bg-white.rounded-lg.shadow-md.overflow-hidden:last-child');
        
        if (newEmptyState && newEmptyState.querySelector('.fa-calendar-times')) {
            if (currentEmptyState) {
                currentEmptyState.innerHTML = newEmptyState.innerHTML;
            }
        } else if (currentEmptyState && currentEmptyState.querySelector('.fa-calendar-times')) {
            currentEmptyState.remove();
        }
        
        console.log('Appointments refreshed at', new Date().toLocaleTimeString());
    })
    .catch(error => {
        console.error('Error refreshing appointments:', error);
    });
}

// Modal state tracking
function trackModalState() {
    const modals = ['viewModal', 'acceptModal', 'rescheduleModal', 'cancelModal'];
    
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            // Use MutationObserver to detect modal visibility changes
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const isHidden = modal.classList.contains('hidden');
                        isModalOpen = !isHidden;
                        
                        if (isModalOpen) {
                            stopAutoRefresh();
                        } else {
                            startAutoRefresh();
                        }
                    }
                });
            });
            
            observer.observe(modal, {
                attributes: true,
                attributeFilter: ['class']
            });
        }
    });
}

// Initialize auto-refresh when page loads
document.addEventListener('DOMContentLoaded', function() {
    startAutoRefresh();
    trackModalState();
    
    // Also track modal open/close via escape key and backdrop clicks
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            isModalOpen = false;
            startAutoRefresh();
        }
    });
});

// Stop auto-refresh when page is hidden (tab switch)
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
    }
});

// Filter appointments by category
function filterAppointments(category) {
    // Update active tab
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.remove('active', 'bg-blue-100', 'text-blue-700', 'border-blue-200');
        tab.classList.add('bg-white', 'text-gray-700', 'border-gray-300');
    });
    
    event.target.classList.add('active', 'bg-blue-100', 'text-blue-700', 'border-blue-200');
    event.target.classList.remove('bg-white', 'text-gray-700', 'border-gray-300');
    
    // Show/hide sections based on category
    const sections = document.querySelectorAll('.appointment-section');
    sections.forEach(section => {
        if (category === 'all') {
            section.style.display = 'block';
        } else {
            if (section.dataset.category === category) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        }
    });

    // Scroll to the first visible section
    const firstVisibleSection = document.querySelector('.appointment-section[style="display: block"]');
    if (firstVisibleSection) {
        firstVisibleSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Auto-scroll to latest appointments on page load
document.addEventListener('DOMContentLoaded', function() {
    const latestSection = document.querySelector('[data-category="latest"]');
    if (latestSection) {
        latestSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});

// ========== VIEW MODAL FUNCTIONS ==========
function openViewModal(appointmentId) {
    const modal = document.getElementById('viewModal');
    const content = document.getElementById('viewModalContent');
    
    modal.classList.remove('hidden');
    currentAppointmentId = appointmentId;
    
    // Show loading state
    content.innerHTML = '<div class="flex justify-center items-center py-12"><div class="text-center"><div class="loading-spinner mx-auto mb-4"></div><p class="text-gray-600">Loading appointment details...</p></div></div>';
    
    fetch(`/nurse/appointments/${appointmentId}`, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderAppointmentDetails(data.appointment);
        } else {
            content.innerHTML = '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-circle text-2xl mb-2"></i><p>Failed to load appointment details</p></div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-circle text-2xl mb-2"></i><p>Error loading appointment details</p></div>';
    });
}

function renderAppointmentDetails(appointment) {
    const content = document.getElementById('viewModalContent');
    
    // Filter out symptom checker from notes
    let displayNotes = appointment.notes;
    if (displayNotes && displayNotes.includes('Symptom Checker Results')) {
        displayNotes = null;
    }
    
    // Determine which action buttons to show
    const isPending = appointment.status === 'pending';
    const canBeRescheduled = ['pending', 'confirmed', 'accepted'].includes(appointment.status);
    const canBeCancelled = ['pending', 'confirmed', 'accepted'].includes(appointment.status);
    
    // Build action buttons HTML with enhanced design matching the image
    let actionButtonsHTML = '';
    
    if (isPending || canBeRescheduled || canBeCancelled) {
        actionButtonsHTML = `
            <div class="actions-wrapper">
                <div class="wrapper-header">
                    <i class="fas fa-cogs"></i>
                    <span>Appointment Actions</span>
                </div>
                <div class="actions-grid">
                    ${isPending ? `
                    <!-- ✅ ACCEPT BUTTON (Green) -->
                    <button onclick="handleQuickAction('accept', ${appointment.id})" 
                            class="action-button accept-button" 
                            title="Accept Appointment"
                            aria-label="Accept this appointment">
                        <div class="action-button-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="action-button-content">
                            <span class="action-button-title">Accept</span>
                            <span class="action-button-description">Approve this appointment</span>
                        </div>
                        <div class="action-button-checkbox"></div>
                    </button>
                    ` : ''}

                    ${canBeRescheduled ? `
                    <!-- 📅 RESCHEDULE BUTTON (Amber) -->
                    <button onclick="handleQuickAction('reschedule', ${appointment.id})" 
                            class="action-button reschedule-button" 
                            title="Reschedule Appointment"
                            aria-label="Reschedule this appointment">
                        <div class="action-button-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="action-button-content">
                            <span class="action-button-title">Reschedule</span>
                            <span class="action-button-description">Change date & time</span>
                        </div>
                        <div class="action-button-checkbox"></div>
                    </button>
                    ` : ''}

                    ${canBeCancelled ? `
                    <!-- ⛔ CANCEL BUTTON (Red) -->
                    <button onclick="handleQuickAction('cancel', ${appointment.id})" 
                            class="action-button cancel-button" 
                            title="Cancel Appointment"
                            aria-label="Cancel this appointment">
                        <div class="action-button-icon">
                            <i class="fas fa-ban"></i>
                        </div>
                        <div class="action-button-content">
                            <span class="action-button-title">Cancel</span>
                            <span class="action-button-description">Cancel this appointment</span>
                        </div>
                        <div class="action-button-checkbox"></div>
                    </button>
                    ` : ''}
                </div>
            </div>
        `;
    } else {
        actionButtonsHTML = `
            <div class="bg-gradient-to-r from-gray-50 to-blue-50 border-2 border-gray-200 rounded-xl p-6 mt-6">
                <div class="text-center py-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-200 rounded-full mb-3">
                        <i class="fas fa-info-circle text-gray-500 text-lg"></i>
                    </div>
                    <p class="text-gray-600 font-medium">No actions available</p>
                    <p class="text-sm text-gray-500 mt-1">This appointment cannot be modified in its current status.</p>
                </div>
            </div>
        `;
    }
    
    content.innerHTML = `
        <div class="space-y-6">
            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Left Column: Student Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Student Information -->
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 bg-blue-50 border-b border-gray-200">
                            <h4 class="text-md font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-user-graduate mr-2 text-blue-500"></i>
                                Student Information
                            </h4>
                        </div>
                        <div class="p-4">
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Full Name</p>
                                        <p class="font-medium text-gray-900">${appointment.user.full_name || 'N/A'}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Student ID</p>
                                        <p class="font-medium text-gray-900">${appointment.user.student_id || 'N/A'}</p>
                                    </div>
                                    ${appointment.user.date_of_birth ? `
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Date of Birth</p>
                                        <p class="font-medium text-gray-900">${appointment.user.date_of_birth}</p>
                                    </div>
                                    ` : ''}
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Email</p>
                                        <p class="font-medium text-gray-900 break-all">${appointment.user.email || 'Not provided'}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 font-medium">Phone</p>
                                        <p class="font-medium text-gray-900">${appointment.user.phone || 'Not provided'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Appointment Information -->
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 bg-green-50 border-b border-gray-200">
                            <h4 class="text-md font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-calendar-alt mr-2 text-green-500"></i>
                                Appointment Information
                            </h4>
                        </div>
                        <div class="p-4">
                            <dl class="grid md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <dt class="text-xs text-gray-500 font-medium">Date & Time</dt>
                                    <dd class="font-medium text-gray-900 mt-1">${appointment.formatted_date_time}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500 font-medium">Status</dt>
                                    <dd class="mt-1">
                                        <span class="${appointment.status_badge_class} px-3 py-1 rounded-full text-xs font-medium inline-block">
                                            ${appointment.status_display}
                                        </span>
                                    </dd>
                                </div>
                                <div class="md:col-span-2">
                                    <dt class="text-xs text-gray-500 font-medium">Reason for Visit</dt>
                                    <dd class="font-medium text-gray-900 mt-1">${appointment.reason}</dd>
                                </div>
                                ${appointment.symptoms ? `
                                <div class="md:col-span-2">
                                    <dt class="text-xs text-gray-500 font-medium">Symptoms</dt>
                                    <dd class="font-medium text-gray-900 mt-1">${appointment.symptoms}</dd>
                                </div>
                                ` : ''}
                                ${displayNotes ? `
                                <div class="md:col-span-2">
                                    <dt class="text-xs text-gray-500 font-medium">Additional Notes</dt>
                                    <dd class="font-medium text-gray-900 mt-1 whitespace-pre-wrap bg-gray-50 p-3 rounded-lg text-sm">${displayNotes}</dd>
                                </div>
                                ` : ''}
                            </dl>
                        </div>
                    </div>

                    <!-- Action Buttons Section -->
                    ${actionButtonsHTML}
                </div>

                <!-- Right Column: Timeline & Details -->
                <div class="space-y-6">
                    <!-- Timeline -->
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 bg-purple-50 border-b border-gray-200">
                            <h4 class="text-md font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-history mr-2 text-purple-500"></i>
                                Timeline
                            </h4>
                        </div>
                        <div class="p-4">
                            <div class="status-timeline text-sm">
                                ${appointment.created_at ? `
                                <div class="timeline-item">
                                    <p class="font-medium text-gray-800">Appointment Created</p>
                                    <p class="text-gray-600 text-xs">${appointment.created_at}</p>
                                </div>
                                ` : ''}
                                ${appointment.accepted_at ? `
                                <div class="timeline-item">
                                    <p class="font-medium text-gray-800">Appointment Accepted</p>
                                    <p class="text-gray-600 text-xs">${appointment.accepted_at}</p>
                                </div>
                                ` : ''}
                                ${appointment.rescheduled_at ? `
                                <div class="timeline-item">
                                    <p class="font-medium text-gray-800">Appointment Rescheduled</p>
                                    <p class="text-gray-600 text-xs">${appointment.rescheduled_at}</p>
                                </div>
                                ` : ''}
                                ${appointment.completed_at ? `
                                <div class="timeline-item">
                                    <p class="font-medium text-gray-800">Appointment Completed</p>
                                    <p class="text-gray-600 text-xs">${appointment.completed_at}</p>
                                </div>
                                ` : ''}
                                ${appointment.cancelled_at ? `
                                <div class="timeline-item">
                                    <p class="font-medium text-gray-800">Appointment Cancelled</p>
                                    <p class="text-gray-600 text-xs">${appointment.cancelled_at}</p>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>

                    <!-- Appointment Type & Urgency -->
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 bg-yellow-50 border-b border-gray-200">
                            <h4 class="text-md font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-yellow-500"></i>
                                Details
                            </h4>
                        </div>
                        <div class="p-4 space-y-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Type</p>
                                <p class="font-medium text-gray-900">${appointment.appointment_type_display}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Urgency</p>
                                <p class="font-medium ${appointment.is_urgent ? 'text-red-600' : 'text-gray-900'}">
                                    ${appointment.is_urgent ? '<i class="fas fa-exclamation-circle mr-1"></i>Urgent' : 'Regular'}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function handleQuickAction(action, appointmentId) {
    closeViewModal();
    
    // Small delay to allow view modal to close
    setTimeout(() => {
        switch(action) {
            case 'accept':
                openAcceptModal(appointmentId);
                break;
            case 'reschedule':
                openRescheduleModal(appointmentId);
                break;
            case 'cancel':
                openCancelModal(appointmentId);
                break;
        }
    }, 300);
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
    currentAppointmentId = null;
}

// ========== ACCEPT MODAL FUNCTIONS ==========
function openAcceptModal(appointmentId) {
    const modal = document.getElementById('acceptModal');
    const form = document.getElementById('acceptForm');
    form.action = `/nurse/appointments/${appointmentId}/accept`;
    modal.classList.remove('hidden');
}

function closeAcceptModal() {
    document.getElementById('acceptModal').classList.add('hidden');
}

// ========== RESCHEDULE MODAL FUNCTIONS ==========
function openRescheduleModal(appointmentId) {
    const modal = document.getElementById('rescheduleModal');
    const form = document.getElementById('rescheduleForm');
    form.action = `/nurse/appointments/${appointmentId}/reschedule`;
    form.reset();
    selectedTimeSlot = null;
    document.getElementById('reschedule_new_time').value = '';
    document.getElementById('selectedSummary').classList.add('hidden');
    document.getElementById('charCount').textContent = '0';
    resetTimeSlots();
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeRescheduleModal() {
    document.getElementById('rescheduleModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    selectedTimeSlot = null;
}

function resetTimeSlots() {
    document.getElementById('rescheduleTimeSlots').innerHTML = `
        <div class="flex flex-col items-center justify-center flex-1 text-gray-400 space-y-2">
            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm">
                <i class="fas fa-calendar-alt text-xl text-gray-300"></i>
            </div>
            <p class="text-sm font-medium text-center text-gray-500">Select a date to see available times</p>
        </div>
    `;
}

function refreshTimeSlots() {
    const date = document.getElementById('reschedule_new_date').value;
    if (date) {
        loadRescheduleTimeSlots(date);
    }
}

document.getElementById('reschedule_new_date')?.addEventListener('change', function() {
    if (this.value) {
        loadRescheduleTimeSlots(this.value);
    }
});

// Character counter
document.getElementById('reschedule_reason_input')?.addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
});

function loadRescheduleTimeSlots(date) {
    const container = document.getElementById('rescheduleTimeSlots');
    container.innerHTML = `
        <div class="flex flex-col items-center justify-center flex-1 text-gray-400 space-y-2">
            <div class="loading-spinner"></div>
            <p class="text-sm font-medium">Loading available times...</p>
        </div>
    `;

    fetch(`/nurse/appointments/available-slots?date=${encodeURIComponent(date)}`, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        container.innerHTML = '';
        
        if (data.success && data.slots && data.slots.length > 0) {
            let hasAvailableSlots = false;
            
            // Create a grid wrapper
            const grid = document.createElement('div');
            grid.className = 'grid grid-cols-2 sm:grid-cols-3 gap-2 w-full';
            
            data.slots.forEach(slot => {
                if (slot.is_available) {
                    hasAvailableSlots = true;
                    
                    const slotButton = document.createElement('button');
                    slotButton.type = 'button';
                    slotButton.className = 'time-slot-btn';
                    slotButton.dataset.time = slot.value;
                    slotButton.setAttribute('aria-label', `Select ${slot.label}`);
                    
                    slotButton.innerHTML = `
                        <div style="font-weight: 600; margin-bottom: 0.25rem; line-height: 1.2;">
                            ${slot.label}
                        </div>
                        <div style="font-size: 0.75rem; opacity: 0.7; line-height: 1.2;">
                            ${slot.period.charAt(0).toUpperCase() + slot.period.slice(1)}
                        </div>
                    `;
                    
                    slotButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        document.querySelectorAll('#rescheduleTimeSlots .time-slot-btn').forEach(btn => {
                            btn.classList.remove('selected');
                        });
                        
                        this.classList.add('selected');
                        selectedTimeSlot = { value: slot.value, label: slot.label };
                        document.getElementById('reschedule_new_time').value = slot.value;
                        
                        updateSummary();
                    });
                    
                    grid.appendChild(slotButton);
                }
            });
            
            if (hasAvailableSlots) {
                container.appendChild(grid);
            } else {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center flex-1 text-gray-400 space-y-2">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm">
                            <i class="fas fa-calendar-times text-xl text-gray-300"></i>
                        </div>
                        <p class="text-sm font-medium text-center text-gray-500">No available times</p>
                        <p class="text-xs text-center text-gray-400">Try selecting a different date</p>
                    </div>
                `;
            }
        } else {
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center flex-1 text-gray-400 space-y-2">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm">
                        <i class="fas fa-calendar-times text-xl text-gray-300"></i>
                    </div>
                    <p class="text-sm font-medium text-center text-gray-500">No available times</p>
                    <p class="text-xs text-center text-gray-400">Try selecting a different date</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading time slots:', error);
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center flex-1 text-gray-400 space-y-2">
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm">
                    <i class="fas fa-exclamation-triangle text-xl text-red-500"></i>
                </div>
                <p class="text-sm font-medium text-center text-red-600">Error loading times</p>
                <p class="text-xs text-center text-gray-400">Please try again</p>
            </div>
        `;
    });
}

function updateSummary() {
    const dateInput = document.getElementById('reschedule_new_date');
    const date = dateInput.value;
    
    if (date && selectedTimeSlot) {
        const dateObj = new Date(date + 'T00:00:00');
        const formattedDate = dateObj.toLocaleDateString('en-US', { 
            weekday: 'short', 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
        
        document.getElementById('summaryDate').textContent = formattedDate;
        document.getElementById('summaryTime').textContent = selectedTimeSlot.label;
        document.getElementById('selectedSummary').classList.remove('hidden');
    }
}

document.getElementById('rescheduleForm')?.addEventListener('submit', function(e) {
    const time = document.getElementById('reschedule_new_time').value;
    const date = document.getElementById('reschedule_new_date').value;
    const reason = document.getElementById('reschedule_reason_input').value.trim();

    if (!date) {
        e.preventDefault();
        alert('Please select a date.');
        return false;
    }

    if (!time) {
        e.preventDefault();
        alert('Please select a time slot.');
        return false;
    }

    if (!reason || reason.length < 10) {
        e.preventDefault();
        alert('Reason must be at least 10 characters.');
        return false;
    }

    const form = this;
    const formData = new FormData(form);
    
    e.preventDefault();
    
    // Disable submit button
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Processing...</span>';
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || data.message) {
            showSuccessStateInModal(data.message || 'Appointment rescheduled successfully!');
            
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i><span class="hidden sm:inline">Confirm Reschedule</span><span class="sm:hidden">Confirm</span>';
            alert(data.message || 'An error occurred while rescheduling the appointment.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i><span class="hidden sm:inline">Confirm Reschedule</span><span class="sm:hidden">Confirm</span>';
        alert('An error occurred. Please try again.');
    });
});

function showSuccessStateInModal(message) {
    const form = document.getElementById('rescheduleForm');
    const modal = document.getElementById('rescheduleModal');
    
    form.style.display = 'none';
    
    const successContainer = document.createElement('div');
    successContainer.className = 'flex flex-col items-center justify-center py-12 px-6 text-center';
    successContainer.id = 'successState';
    
    successContainer.innerHTML = `
        <div class="mb-6">
            <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center mx-auto shadow-lg mb-4">
                <i class="fas fa-check text-4xl text-white"></i>
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Success!</h2>
            <p class="text-base sm:text-lg text-gray-600 mb-6">${message}</p>
            
            <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4 mb-6 max-w-sm">
                <p class="text-sm text-blue-900">
                    <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                    <span id="redirectCountdown">Redirecting in 3 seconds...</span>
                </p>
            </div>
        </div>
    `;
    
    const modalHeader = modal.querySelector('div.flex.items-center.justify-between.p-4');
    modalHeader.insertAdjacentElement('afterend', successContainer);
    
    let countdown = 3;
    const countdownInterval = setInterval(() => {
        countdown--;
        document.getElementById('redirectCountdown').textContent = `Redirecting in ${countdown} second${countdown !== 1 ? 's' : ''}...`;
        if (countdown <= 0) {
            clearInterval(countdownInterval);
        }
    }, 1000);
}

// ========== CANCEL MODAL FUNCTIONS ==========
function openCancelModal(appointmentId) {
    const modal = document.getElementById('cancelModal');
    const form = document.getElementById('cancelForm');
    form.action = `/nurse/appointments/${appointmentId}/cancel`;
    modal.classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
    document.getElementById('cancellation_reason').value = '';
}

// ========== SUCCESS MESSAGE ==========
function showSuccessMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative fixed top-4 left-4 right-4 sm:left-auto sm:right-4 sm:w-96 z-[9999] success-alert';
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.insertBefore(alertDiv, document.body.firstChild);
    
    setTimeout(() => {
        alertDiv.style.transition = 'opacity 0.5s ease-out';
        alertDiv.style.opacity = '0';
        setTimeout(() => alertDiv.remove(), 500);
    }, 3000);
}

// ========== MODAL BACKDROP HANDLERS ==========
document.getElementById('viewModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeViewModal();
});

document.getElementById('acceptModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAcceptModal();
});

document.getElementById('rescheduleModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeRescheduleModal();
});

document.getElementById('cancelModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeCancelModal();
});

// ========== ESCAPE KEY HANDLERS ==========
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeViewModal();
        closeAcceptModal();
        closeRescheduleModal();
        closeCancelModal();
    }
});

// ========== AUTO-HIDE ALERTS ==========
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.success-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});
</script>
@endpush