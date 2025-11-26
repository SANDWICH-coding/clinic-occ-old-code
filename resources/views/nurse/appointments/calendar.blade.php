@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $requestedMonth = request()->get('month');
    if ($requestedMonth) {
        $currentMonth = Carbon::parse($requestedMonth . '-01');
    } else {
        $currentMonth = Carbon::now()->startOfMonth();
    }

    $startOfMonth = $currentMonth->copy()->startOfMonth();
    $endOfMonth = $currentMonth->copy()->endOfMonth();
    $prevMonth = $currentMonth->copy()->subMonth();
    $nextMonth = $currentMonth->copy()->addMonth();
@endphp

@extends('layouts.nurse-app')

@section('title', 'Clinic Calendar')

@push('styles')
<style>
    /* Hide scrollbars but maintain functionality */
    .overflow-y-auto::-webkit-scrollbar,
    .overflow-x-auto::-webkit-scrollbar {
        display: none;
    }
    
    .overflow-y-auto,
    .overflow-x-auto {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Calendar day scroll area */
    .calendar-day-content {
        -ms-overflow-style: none;
        scrollbar-width: none;
        overflow-y: auto;
        max-height: calc(100% - 1.5rem);
    }
    
    .calendar-day-content::-webkit-scrollbar {
        display: none;
    }

    /* Modal scroll areas */
    .modal-content::-webkit-scrollbar {
        display: none;
    }
    
    .modal-content {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Calendar grid */
    .calendar-grid {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .calendar-grid::-webkit-scrollbar {
        display: none;
    }

    /* Smooth scrolling */
    .calendar-day-content,
    .modal-content,
    .calendar-grid {
        scroll-behavior: smooth;
    }

    /* Touch scrolling for mobile */
    .calendar-day-content {
        -webkit-overflow-scrolling: touch;
    }

    /* Time slot styles */
    .time-slot-btn {
        display: inline-block;
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        text-align: center;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        min-width: 110px;
        position: relative;
        user-select: none;
    }

    .time-slot-btn:hover:not(.selected) {
        transform: translateY(-2px);
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        background: #eff6ff;
    }

    .time-slot-btn.selected {
        background: #2563eb !important;
        color: #fff !important;
        border-color: #1d4ed8 !important;
        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.4) !important;
        font-weight: 600 !important;
        transform: scale(1.03) !important;
    }

    .time-slot-btn.selected:hover {
        background: #1d4ed8 !important;
        border-color: #1e40af !important;
        transform: scale(1.05) !important;
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5) !important;
    }

    #rescheduleTimeSlots {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: flex-start;
        align-items: flex-start;
        min-height: 120px;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 0.5rem;
        border: 2px solid #e5e7eb;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    #rescheduleTimeSlots::-webkit-scrollbar {
        display: none;
    }

    .loading-spinner {
        display: inline-block;
        width: 1.5rem;
        height: 1.5rem;
        border: 3px solid #e5e7eb;
        border-top-color: #3b82f6;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0); }
        100% { transform: rotate(360deg); }
    }

    .calendar-day {
        transition: all 0.2s ease-in-out;
    }

    .calendar-day:hover {
        background-color: #f9fafb;
        transform: scale(1.02);
    }

    .appointment-item, .consultation-item {
        transition: all 0.2s ease-in-out;
    }

    .appointment-item:hover, .consultation-item:hover {
        transform: translateX(2px);
    }

    /* Enhanced Modal Styles */
    .modal-overlay {
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }
    
    .modal-content {
        background: white;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid #e2e8f0;
    }
</style>
@endpush

@section('content')
    <div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8 max-w-7xl">
        <!-- Header -->
        <div class="mb-4 sm:mb-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800">
                        Clinic Calendar - {{ $currentMonth->format('F Y') }}
                    </h1>
                    <p class="text-sm sm:text-base text-gray-600 mt-1">View appointments and consultations</p>
                </div>
                <div class="flex flex-wrap gap-2 w-full lg:w-auto">
                    <div class="flex gap-2 flex-1 lg:flex-initial">
                        <a href="{{ route('nurse.appointments.index', ['view' => 'calendar', 'month' => $prevMonth->format('Y-m')]) }}"
                           class="flex-1 lg:flex-initial px-3 py-2 bg-gray-200 rounded hover:bg-gray-300 text-center text-sm sm:text-base transition-colors"
                           aria-label="Previous Month">
                            <i class="fas fa-chevron-left mr-1 hidden sm:inline"></i>
                            <span class="sm:hidden">Prev</span>
                            <span class="hidden sm:inline">Previous</span>
                        </a>
                        <a href="{{ route('nurse.appointments.index', ['view' => 'calendar']) }}"
                           class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-center text-sm sm:text-base transition-colors"
                           aria-label="Go to Current Month">
                            Today
                        </a>
                        <a href="{{ route('nurse.appointments.index', ['view' => 'calendar', 'month' => $nextMonth->format('Y-m')]) }}"
                           class="flex-1 lg:flex-initial px-3 py-2 bg-gray-200 rounded hover:bg-gray-300 text-center text-sm sm:text-base transition-colors"
                           aria-label="Next Month">
                            <span class="sm:hidden">Next</span>
                            <span class="hidden sm:inline">Next</span>
                            <i class="fas fa-chevron-right ml-1 hidden sm:inline"></i>
                        </a>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('nurse.appointments.index') }}"
                           class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm sm:text-base transition-colors"
                           aria-label="View List">
                            <i class="fas fa-list mr-1 hidden sm:inline"></i>List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 sm:px-4 sm:py-3 rounded mb-4 text-sm sm:text-base">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 sm:px-4 sm:py-3 rounded mb-4 text-sm sm:text-base">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <!-- Calendar Statistics -->
        <div class="mb-4 sm:mb-6 grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4">
            <div class="bg-white p-3 sm:p-4 rounded-lg shadow">
                <h3 class="text-xs sm:text-sm font-medium text-gray-500">Total Appointments</h3>
                <p class="text-lg sm:text-xl font-bold">{{ $calendarStats['total_appointments'] ?? 0 }}</p>
            </div>
            <div class="bg-white p-3 sm:p-4 rounded-lg shadow">
                <h3 class="text-xs sm:text-sm font-medium text-gray-500">Total Consultations</h3>
                <p class="text-lg sm:text-xl font-bold">{{ $calendarStats['total_consultations'] ?? 0 }}</p>
            </div>
            <div class="bg-white p-3 sm:p-4 rounded-lg shadow">
                <h3 class="text-xs sm:text-sm font-medium text-gray-500">Urgent Cases</h3>
                <p class="text-lg sm:text-xl font-bold text-red-600">{{ $calendarStats['urgent'] ?? 0 }}</p>
            </div>
            <div class="bg-white p-3 sm:p-4 rounded-lg shadow">
                <h3 class="text-xs sm:text-sm font-medium text-gray-500">Requiring Action</h3>
                <p class="text-lg sm:text-xl font-bold text-yellow-600">{{ $calendarStats['requiring_action'] ?? 0 }}</p>
            </div>
        </div>

        <!-- View Toggle -->
        <div class="mb-4 flex flex-wrap gap-2">
            <button id="viewAll" class="px-3 py-2 bg-purple-100 text-purple-800 rounded hover:bg-purple-200 text-sm sm:text-base transition-colors view-active">
                <i class="fas fa-calendar-alt mr-1"></i>View All
            </button>
            <button id="viewAppointments" class="px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-sm sm:text-base transition-colors">
                <i class="fas fa-calendar-check mr-1"></i>Appointments Only
            </button>
            <button id="viewConsultations" class="px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-sm sm:text-base transition-colors">
                <i class="fas fa-user-md mr-1"></i>Consultations Only
            </button>
        </div>

        <!-- Calendar Grid -->
        <div class="bg-white rounded-lg shadow overflow-x-auto calendar-grid">
            <div class="min-w-[320px]">
                <div class="grid grid-cols-7 gap-px bg-gray-200">
                    <!-- Weekday Headers -->
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                        <div class="bg-gray-50 p-1 sm:p-2 text-center text-xs sm:text-sm font-medium text-gray-700">
                            <span class="hidden sm:inline">{{ $day }}</span>
                            <span class="sm:hidden">{{ substr($day, 0, 1) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="grid grid-cols-7 gap-px bg-gray-200">
                    <!-- Fill empty days before the start of the month -->
                    @for($i = 0; $i < $startOfMonth->dayOfWeek; $i++)
                        <div class="bg-gray-100 p-2 sm:p-4 h-24 sm:h-32"></div>
                    @endfor
                    <!-- Render each day of the month -->
                    @for($day = 1; $day <= $endOfMonth->day; $day++)
                        @php
                            $date = Carbon::create($currentMonth->year, $currentMonth->month, $day);
                            $dateStr = $date->format('Y-m-d');
                            $isToday = $date->isToday();
                            $appointmentsForDay = $appointmentsByDate->get($dateStr, collect([]));
                            $consultationsForDay = $consultationsByDate->get($dateStr, collect([]));
                        @endphp
                        <div class="bg-white p-2 sm:p-4 h-24 sm:h-32 calendar-day relative" 
                             data-date="{{ $dateStr }}"
                             aria-label="Day {{ $day }} of {{ $currentMonth->format('F Y') }}"
                             role="gridcell">
                            <div class="text-xs sm:text-sm font-medium {{ $isToday ? 'bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center' : 'text-gray-700' }}">
                                {{ $day }}
                            </div>
                            <div class="mt-1 calendar-day-content">
                                @foreach($appointmentsForDay as $appointment)
                                    <div class="appointment-item text-xs p-1 mb-1 rounded bg-blue-100 text-blue-800 hover:bg-blue-200 cursor-pointer"
                                         data-appointment-id="{{ $appointment->id }}"
                                         role="button"
                                         aria-label="View appointment for {{ $appointment->user->full_name }} at {{ $appointment->formatted_time }}">
                                        <span class="font-medium">{{ $appointment->formatted_time }}</span>
                                        {{ Str::limit($appointment->user->full_name, 15) }}
                                        @if($appointment->is_urgent)
                                            <span class="text-red-600">⚠️</span>
                                        @endif
                                    </div>
                                @endforeach
                                @foreach($consultationsForDay as $consultation)
                                    <div class="consultation-item text-xs p-1 mb-1 rounded bg-green-100 text-green-800 hover:bg-green-200 cursor-pointer"
                                         data-consultation-id="{{ $consultation->id }}"
                                         role="button"
                                         aria-label="View consultation for {{ $consultation->student->full_name }} at {{ $consultation->formatted_created_at }}">
                                        <span class="font-medium">{{ $consultation->formatted_created_at }}</span>
                                        {{ Str::limit($consultation->student->full_name, 15) }}
                                        @if($consultation->priority === 'emergency')
                                            <span class="text-red-600">⚠️</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Enhanced Appointment Modal -->
        <div id="appointmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto modal-content">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Appointment Details</h3>
                    <button onclick="document.getElementById('appointmentModal').classList.add('hidden')" 
                            class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div id="appointmentDetails" class="space-y-4">
                    <!-- Details will be loaded here -->
                </div>
                
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                    <button id="rescheduleBtn" 
                            class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition hidden">
                        <i class="fas fa-calendar-alt mr-1"></i>Reschedule
                    </button>
                    <button id="startConsultationBtn" 
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition hidden">
                        <i class="fas fa-user-md mr-1"></i>Start Consultation
                    </button>
                    <button onclick="document.getElementById('appointmentModal').classList.add('hidden')" 
                            class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Enhanced Consultation Modal -->
        <div id="consultationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto modal-content">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-file-medical mr-3 text-green-500"></i>
                        Consultation Details
                    </h3>
                    <button onclick="document.getElementById('consultationModal').classList.add('hidden')" 
                            class="text-gray-400 hover:text-gray-600 transition-transform hover:scale-110">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                
                <div id="consultationDetails" class="space-y-6">
                    <!-- Details will be loaded here -->
                </div>
                
                <div class="flex justify-end space-x-4 mt-8 pt-6 border-t">
                    <a id="consultationDetailsLink" href="#" 
                       class="px-6 py-3 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        View Full Details
                    </a>
                    <button onclick="document.getElementById('consultationModal').classList.add('hidden')" 
                            class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 transform hover:scale-105">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Reschedule Modal -->
        <div id="rescheduleModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl mx-auto my-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="bg-yellow-100 p-2 rounded-full mr-3">
                            <i class="fas fa-calendar-alt text-yellow-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Reschedule Appointment</h3>
                    </div>
                    <button onclick="closeRescheduleModal()" 
                            class="text-gray-400 hover:text-gray-600 transition" type="button">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="rescheduleForm" method="POST">
                    @csrf @method('PATCH')
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Current Appointment
                        </label>
                        <div id="currentAppointmentInfo" class="bg-gray-50 p-3 rounded border">
                            <!-- Current appointment info will be loaded here -->
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="reschedule_new_date" class="block text-sm font-medium text-gray-700 mb-2">
                            New Appointment Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="reschedule_new_date" name="new_appointment_date"
                               min="{{ today()->format('Y-m-d') }}"
                               max="{{ today()->addDays(30)->format('Y-m-d') }}"
                               required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Select a date to view available time slots</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            New Appointment Time <span class="text-red-500">*</span>
                        </label>
                        <div id="rescheduleTimeSlots"
                             class="bg-gray-50 p-4 rounded-lg border-2 border-gray-200 min-h-[120px]">
                            <div class="w-full text-gray-500 text-center py-4">
                                <i class="fas fa-calendar-day mb-2"></i>
                                <p>Select a date to view available time slots</p>
                            </div>
                        </div>
                        <input type="hidden" id="reschedule_new_time" name="new_appointment_time" required>
                        <p class="mt-2 text-xs text-gray-500">
                            Click a time slot to select it – the chosen slot will be highlighted in blue.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label for="reschedule_reason_input" class="block text-sm font-medium text-gray-700 mb-2">
                            Reason for Rescheduling <span class="text-red-500">*</span>
                        </label>
                        <textarea id="reschedule_reason_input" name="reschedule_reason"
                                  rows="3" required minlength="10"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                                  placeholder="Please provide a reason for rescheduling this appointment..."></textarea>
                        <p class="mt-1 text-xs text-gray-500">Minimum 10 characters required</p>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" onclick="closeRescheduleModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition flex items-center">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Reschedule Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
            <div class="loading-spinner"></div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // View Toggle Handlers
    const viewAllBtn = document.getElementById('viewAll');
    const viewAppointmentsBtn = document.getElementById('viewAppointments');
    const viewConsultationsBtn = document.getElementById('viewConsultations');
    const appointmentItems = document.querySelectorAll('.appointment-item');
    const consultationItems = document.querySelectorAll('.consultation-item');

    function setActiveView(button) {
        [viewAllBtn, viewAppointmentsBtn, viewConsultationsBtn].forEach(btn => {
            btn.classList.remove('bg-purple-100', 'text-purple-800', 'view-active');
            btn.classList.add('bg-gray-100', 'text-gray-800');
        });
        button.classList.add('bg-purple-100', 'text-purple-800', 'view-active');
        button.classList.remove('bg-gray-100', 'text-gray-800');
    }

    viewAllBtn.addEventListener('click', function() {
        setActiveView(viewAllBtn);
        appointmentItems.forEach(item => item.style.display = 'block');
        consultationItems.forEach(item => item.style.display = 'block');
    });

    viewAppointmentsBtn.addEventListener('click', function() {
        setActiveView(viewAppointmentsBtn);
        appointmentItems.forEach(item => item.style.display = 'block');
        consultationItems.forEach(item => item.style.display = 'none');
    });

    viewConsultationsBtn.addEventListener('click', function() {
        setActiveView(viewConsultationsBtn);
        appointmentItems.forEach(item => item.style.display = 'none');
        consultationItems.forEach(item => item.style.display = 'block');
    });

    // Enhanced Fetch Appointment Details
    document.querySelectorAll('[data-appointment-id]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            const appointmentId = this.dataset.appointmentId;
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.classList.remove('hidden');
            
            fetch(`/nurse/appointments/${appointmentId}/details`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                loadingOverlay.classList.add('hidden');
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    alert(data.error || 'Failed to load appointment details');
                    return;
                }
                
                const apt = data.appointment;
                renderAppointmentDetails(apt, appointmentId);
                setupActionButtons(apt, appointmentId);
                
                document.getElementById('appointmentModal').classList.remove('hidden');
            })
            .catch(error => {
                loadingOverlay.classList.add('hidden');
                console.error('Error fetching appointment details:', error);
                alert('Failed to load appointment details');
            });
        });
    });

    // Enhanced Fetch Consultation Details
    document.querySelectorAll('[data-consultation-id]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            const consultationId = this.dataset.consultationId;
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.classList.remove('hidden');
            
            fetch(`/nurse/consultations/${consultationId}/details`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                loadingOverlay.classList.add('hidden');
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                
                const consultation = data.consultation;
                renderConsultationDetails(consultation, consultationId);
                document.getElementById('consultationDetailsLink').href = `/nurse/consultations/${consultationId}`;
                document.getElementById('consultationModal').classList.remove('hidden');
            })
            .catch(error => {
                loadingOverlay.classList.add('hidden');
                console.error('Error fetching consultation details:', error);
                alert('Failed to load consultation details');
            });
        });
    });

    // Keyboard Navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            const prevLink = document.querySelector('a[href*="month={{ $prevMonth->format('Y-m') }}"]');
            if (prevLink) prevLink.click();
        } else if (e.key === 'ArrowRight') {
            const nextLink = document.querySelector('a[href*="month={{ $nextMonth->format('Y-m') }}"]');
            if (nextLink) nextLink.click();
        } else if (e.key === 't' || e.key === 'T') {
            const todayLink = document.querySelector('a[href="{{ route('nurse.appointments.index', ['view' => 'calendar']) }}"]');
            if (todayLink) todayLink.click();
        }
    });
});

// Render simplified appointment information (only requested fields)
function renderAppointmentDetails(appointment, appointmentId) {
    const detailsContainer = document.getElementById('appointmentDetails');
    
    detailsContainer.innerHTML = `
        <div class="space-y-6">
            <!-- Student Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-700 mb-3 flex items-center text-blue-800">
                    <i class="fas fa-user-graduate mr-2"></i>
                    Student Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div>
                        <strong class="text-gray-600">Name:</strong>
                        <p class="text-gray-800">${appointment.user.full_name}</p>
                    </div>
                    <div>
                        <strong class="text-gray-600">Student ID:</strong>
                        <p class="text-gray-800">${appointment.user.student_id}</p>
                    </div>
                    <div>
                        <strong class="text-gray-600">Email:</strong>
                        <p class="text-gray-800">${appointment.user.email}</p>
                    </div>
                    <div>
                        <strong class="text-gray-600">Phone:</strong>
                        <p class="text-gray-800">${appointment.user.phone || 'Not provided'}</p>
                    </div>
                </div>
            </div>

            <!-- Appointment Information -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-700 mb-3 flex items-center text-green-800">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Appointment Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div>
                        <strong class="text-gray-600">Date & Time:</strong>
                        <p class="text-gray-800">${appointment.formatted_date_time}</p>
                    </div>
                    <div>
                        <strong class="text-gray-600">Status:</strong>
                        <span class="${appointment.status_badge_class} px-2 py-1 rounded text-xs">${appointment.status_display}</span>
                    </div>
                </div>
            </div>

            <!-- Reason for Visit -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-700 mb-3 flex items-center text-yellow-800">
                    <i class="fas fa-comment-medical mr-2"></i>
                    Reason for Visit
                </h4>
                <div class="text-sm">
                    <p class="text-gray-800 leading-relaxed">${appointment.reason}</p>
                </div>
            </div>

            <!-- Symptoms -->
            ${appointment.symptoms ? `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-700 mb-3 flex items-center text-red-800">
                    <i class="fas fa-stethoscope mr-2"></i>
                    Symptoms
                </h4>
                <div class="text-sm">
                    <p class="text-gray-800 leading-relaxed">${appointment.symptoms}</p>
                </div>
            </div>
            ` : ''}

            <!-- Additional Notes -->
            ${appointment.notes ? `
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-700 mb-3 flex items-center text-purple-800">
                    <i class="fas fa-notes-medical mr-2"></i>
                    Additional Notes
                </h4>
                <div class="text-sm">
                    <p class="text-gray-800 leading-relaxed whitespace-pre-line">${cleanNotes(appointment.notes)}</p>
                </div>
            </div>
            ` : ''}
        </div>
    `;
}

// Clean notes by removing "Possible Conditions" and "EMERGENCY FLAGGED" text
function cleanNotes(notes) {
    if (!notes) return '';
    
    // Remove Possible Conditions lines
    let cleaned = notes.replace(/Possible Conditions:.*?(\n|$)/gi, '');
    
    // Remove EMERGENCY FLAGGED lines
    cleaned = cleaned.replace(/⚠️ EMERGENCY FLAGGED:.*?(\n|$)/gi, '');
    cleaned = cleaned.replace(/EMERGENCY FLAGGED:.*?(\n|$)/gi, '');
    cleaned = cleaned.replace(/EMERGENCY:.*?(\n|$)/gi, '');
    
    // Clean up extra whitespace and line breaks
    cleaned = cleaned.replace(/\n\s*\n\s*\n/g, '\n\n');
    cleaned = cleaned.trim();
    
    return cleaned;
}

// Setup action buttons based on permissions
function setupActionButtons(appointment, appointmentId) {
    const rescheduleBtn = document.getElementById('rescheduleBtn');
    const startConsultationBtn = document.getElementById('startConsultationBtn');
    
    // Reset buttons
    rescheduleBtn.classList.add('hidden');
    startConsultationBtn.classList.add('hidden');
    
    // Show reschedule button if allowed
    if (appointment.can_reschedule) {
        rescheduleBtn.classList.remove('hidden');
        rescheduleBtn.onclick = function() {
            openRescheduleModal(appointment, appointmentId);
        };
    }
    
    // Show start consultation button if allowed
    if (appointment.can_start_consultation) {
        startConsultationBtn.classList.remove('hidden');
        startConsultationBtn.onclick = function() {
            window.location.href = `/nurse/consultations/create?appointment_id=${appointmentId}`;
        };
    }
}

// Enhanced Consultation Details Renderer
function renderConsultationDetails(consultation, consultationId) {
    const detailsContainer = document.getElementById('consultationDetails');
    
    detailsContainer.innerHTML = `
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Patient Info -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Patient Information -->
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 bg-blue-50 border-b border-gray-200">
                        <h4 class="text-md font-medium text-gray-900 flex items-center">
                            <i class="fas fa-user mr-2 text-blue-500"></i>
                            Patient Information
                        </h4>
                    </div>
                    <div class="p-4 space-y-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-500">Full Name</p>
                            <p class="font-medium">${consultation.student.full_name}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Student ID</p>
                                <p class="font-medium">${consultation.student.student_id}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Age</p>
                                <p class="font-medium">${consultation.student.age || 'N/A'} years</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Gender</p>
                                <p class="font-medium">${consultation.student.gender ? consultation.student.gender.charAt(0).toUpperCase() + consultation.student.gender.slice(1) : 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Blood Type</p>
                                <p class="font-medium">${consultation.student.medical_record?.blood_type || 'Unknown'}</p>
                            </div>
                        </div>
                        ${consultation.student.medical_record?.allergies ? `
                        <div>
                            <p class="text-xs text-gray-500">Allergies</p>
                            <p class="font-medium text-red-600">${consultation.student.medical_record.allergies}</p>
                        </div>
                        ` : ''}
                        ${consultation.student.medical_record?.chronic_conditions ? `
                        <div>
                            <p class="text-xs text-gray-500">Chronic Conditions</p>
                            <p class="font-medium">${consultation.student.medical_record.chronic_conditions}</p>
                        </div>
                        ` : ''}
                    </div>
                </div>

                <!-- Status Card -->
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <h4 class="text-md font-medium text-gray-900 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-gray-500"></i>
                            Consultation Status
                        </h4>
                    </div>
                    <div class="p-4 space-y-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-500">Status</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${consultation.status === 'completed' ? 'bg-green-100 text-green-800' : (consultation.status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')}">
                                ${consultation.status_display}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Priority</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${consultation.priority === 'emergency' ? 'bg-red-600 text-white' : (consultation.priority === 'high' ? 'bg-orange-600 text-white' : 'bg-blue-600 text-white')}">
                                ${consultation.priority_display}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Type</p>
                            <p class="font-medium">${consultation.type_display}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Registered At</p>
                            <p class="font-medium">${consultation.formatted_created_at}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Nurse</p>
                            <p class="font-medium">${consultation.nurse?.full_name || 'Not assigned'}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Consultation Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Chief Complaint & Symptoms -->
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 bg-red-50 border-b border-gray-200">
                        <h4 class="text-md font-medium text-gray-900 flex items-center">
                            <i class="fas fa-stethoscope mr-2 text-red-500"></i>
                            Chief Complaint & Symptoms
                        </h4>
                    </div>
                    <div class="p-4 space-y-4 text-sm">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Chief Complaint</p>
                            <p class="text-gray-900">${consultation.chief_complaint || 'Not specified'}</p>
                        </div>
                        ${consultation.pain_level ? `
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Pain Level</p>
                            <div class="flex items-center">
                                <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-xs">
                                    <div class="bg-${consultation.pain_level > 7 ? 'red' : (consultation.pain_level > 4 ? 'yellow' : 'green')}-500 h-2 rounded-full" style="width: ${consultation.pain_level * 10}%"></div>
                                </div>
                                <span class="ml-3 text-sm font-medium">${consultation.pain_level}/10</span>
                            </div>
                        </div>
                        ` : ''}
                        ${consultation.symptoms_description ? `
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Symptoms Description</p>
                            <p class="text-gray-900 whitespace-pre-line">${consultation.symptoms_description}</p>
                        </div>
                        ` : ''}
                    </div>
                </div>

                <!-- Vital Signs -->
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 bg-green-50 border-b border-gray-200">
                        <h4 class="text-md font-medium text-gray-900 flex items-center">
                            <i class="fas fa-heartbeat mr-2 text-green-500"></i>
                            Vital Signs
                        </h4>
                    </div>
                    <div class="p-4">
                        ${consultation.temperature || consultation.blood_pressure_systolic || consultation.heart_rate || consultation.oxygen_saturation || consultation.respiratory_rate || consultation.weight || consultation.height ? `
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            ${consultation.temperature ? `
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 mb-1">Temperature</p>
                                <p class="text-md font-semibold text-gray-900">${consultation.temperature}°C</p>
                            </div>
                            ` : ''}
                            ${consultation.blood_pressure_systolic && consultation.blood_pressure_diastolic ? `
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 mb-1">Blood Pressure</p>
                                <p class="text-md font-semibold text-gray-900">${consultation.blood_pressure_systolic}/${consultation.blood_pressure_diastolic}</p>
                                <p class="text-xs text-gray-500">mmHg</p>
                            </div>
                            ` : ''}
                            ${consultation.heart_rate ? `
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 mb-1">Heart Rate</p>
                                <p class="text-md font-semibold text-gray-900">${consultation.heart_rate}</p>
                                <p class="text-xs text-gray-500">BPM</p>
                            </div>
                            ` : ''}
                            ${consultation.oxygen_saturation ? `
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 mb-1">O₂ Saturation</p>
                                <p class="text-md font-semibold text-gray-900">${consultation.oxygen_saturation}%</p>
                            </div>
                            ` : ''}
                            ${consultation.respiratory_rate ? `
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 mb-1">Respiratory Rate</p>
                                <p class="text-md font-semibold text-gray-900">${consultation.respiratory_rate}</p>
                                <p class="text-xs text-gray-500">per min</p>
                            </div>
                            ` : ''}
                            ${consultation.weight ? `
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 mb-1">Weight</p>
                                <p class="text-md font-semibold text-gray-900">${consultation.weight}</p>
                                <p class="text-xs text-gray-500">kg</p>
                            </div>
                            ` : ''}
                            ${consultation.height ? `
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 mb-1">Height</p>
                                <p class="text-md font-semibold text-gray-900">${consultation.height}</p>
                                <p class="text-xs text-gray-500">cm</p>
                            </div>
                            ` : ''}
                        </div>
                        ` : `
                        <p class="text-gray-500 text-center py-4 text-sm">No vital signs recorded</p>
                        `}
                    </div>
                </div>

                <!-- Treatment & Diagnosis -->
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 bg-blue-50 border-b border-gray-200">
                        <h4 class="text-md font-medium text-gray-900 flex items-center">
                            <i class="fas fa-file-medical mr-2 text-blue-500"></i>
                            Treatment & Diagnosis
                        </h4>
                    </div>
                    <div class="p-4 space-y-4 text-sm">
                        ${consultation.diagnosis ? `
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Diagnosis</p>
                            <p class="text-gray-900 whitespace-pre-line">${consultation.diagnosis}</p>
                        </div>
                        ` : ''}
                        ${consultation.treatment_provided ? `
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Treatment Provided</p>
                            <p class="text-gray-900 whitespace-pre-line">${consultation.treatment_provided}</p>
                        </div>
                        ` : ''}
                        ${consultation.medications_given ? `
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Medications Given</p>
                            <p class="text-gray-900 whitespace-pre-line">${consultation.medications_given}</p>
                        </div>
                        ` : ''}
                        ${consultation.procedures_performed ? `
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Procedures Performed</p>
                            <p class="text-gray-900 whitespace-pre-line">${consultation.procedures_performed}</p>
                        </div>
                        ` : ''}
                        ${consultation.home_care_instructions ? `
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Home Care Instructions</p>
                            <p class="text-gray-900 whitespace-pre-line">${consultation.home_care_instructions}</p>
                        </div>
                        ` : ''}
                        ${!consultation.diagnosis && !consultation.treatment_provided && !consultation.medications_given && !consultation.procedures_performed && !consultation.home_care_instructions ? `
                        <p class="text-gray-500 text-center py-4 text-sm">No treatment or diagnosis information recorded</p>
                        ` : ''}
                    </div>
                </div>

                <!-- Additional Notes -->
                ${consultation.consultation_notes ? `
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <h4 class="text-md font-medium text-gray-900 flex items-center">
                            <i class="fas fa-notes-medical mr-2 text-gray-500"></i>
                            Additional Notes
                        </h4>
                    </div>
                    <div class="p-4">
                        <p class="text-gray-900 whitespace-pre-line text-sm">${consultation.consultation_notes}</p>
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
    `;
}

// Reschedule Modal Functions
function openRescheduleModal(appointment, appointmentId) {
    const modal = document.getElementById('rescheduleModal');
    const form = document.getElementById('rescheduleForm');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    // Set form action
    form.action = `/nurse/appointments/${appointmentId}/reschedule`;
    form.reset();
    
    // Set current appointment info
    document.getElementById('currentAppointmentInfo').innerHTML = `
        <p><strong>Patient:</strong> ${appointment.user.full_name}</p>
        <p><strong>Current Date:</strong> ${appointment.formatted_date}</p>
        <p><strong>Current Time:</strong> ${appointment.formatted_time}</p>
        <p><strong>Reason:</strong> ${appointment.reason}</p>
    `;
    
    // Reset time slots
    document.getElementById('reschedule_new_time').value = '';
    document.getElementById('rescheduleTimeSlots').innerHTML = `
        <div class="w-full text-gray-500 text-center py-4">
            <i class="fas fa-calendar-day mb-2"></i>
            <p>Select a date to view available time slots</p>
        </div>`;
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeRescheduleModal() {
    document.getElementById('rescheduleModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Load time slots for rescheduling
document.getElementById('reschedule_new_date').addEventListener('change', function(e) {
    loadRescheduleTimeSlots(e.target.value);
});

function loadRescheduleTimeSlots(date) {
    if (!date) return;
    
    const container = document.getElementById('rescheduleTimeSlots');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    container.innerHTML = `
        <div class="col-span-full text-center py-4">
            <div class="loading-spinner inline-block"></div>
            <p class="mt-2 text-gray-600">Loading available time slots...</p>
        </div>`;

    fetch(`/nurse/appointments/available-slots?date=${encodeURIComponent(date)}`, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error(response.status);
        return response.json();
    })
    .then(data => {
        container.innerHTML = '';
        
        if (data.success && data.slots && data.slots.length) {
            let availableCount = 0;
            
            data.slots.forEach(slot => {
                if (slot.is_available) {
                    availableCount++;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'time-slot-btn';
                    btn.dataset.time = slot.value;
                    btn.innerHTML = `
                        <div style="font-weight:600;margin-bottom:.25rem;">${slot.label}</div>
                        <div style="font-size:.75rem;opacity:.7;">${slot.period.charAt(0).toUpperCase() + slot.period.slice(1)}</div>`;
                    
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('#rescheduleTimeSlots .time-slot-btn')
                            .forEach(b => b.classList.remove('selected'));
                        this.classList.add('selected');
                        document.getElementById('reschedule_new_time').value = slot.value;
                    });
                    
                    container.appendChild(btn);
                }
            });
            
            if (!availableCount) {
                container.innerHTML = `<div class="col-span-full text-center py-4 text-yellow-600">
                    <i class="fas fa-exclamation-triangle mb-2"></i>
                    <p>No available time slots for this date</p>
                </div>`;
            }
        } else {
            container.innerHTML = `<div class="col-span-full text-center py-4 text-gray-500">
                <i class="fas fa-calendar-times mb-2"></i>
                <p>No available time slots for this date</p>
            </div>`;
        }
    })
    .catch(err => {
        console.error(err);
        container.innerHTML = `<div class="col-span-full text-center py-4 text-red-600">
            <i class="fas fa-exclamation-circle mb-2"></i>
            <p>Error loading time slots. Please try again.</p>
        </div>`;
    });
}

// Form validation for reschedule
document.getElementById('rescheduleForm').addEventListener('submit', function(e) {
    const time = document.getElementById('reschedule_new_time').value;
    const date = document.getElementById('reschedule_new_date').value;
    const reason = document.getElementById('reschedule_reason_input').value.trim();
    
    if (!time) {
        e.preventDefault();
        alert('Please select a time slot.');
        return;
    }
    
    if (!date) {
        e.preventDefault();
        alert('Please select a date.');
        return;
    }
    
    if (!reason || reason.length < 10) {
        e.preventDefault();
        alert('Reason must be at least 10 characters.');
        return;
    }
});

// Close modals on outside click and escape key
document.getElementById('rescheduleModal').addEventListener('click', function(e) {
    if (e.target === this) closeRescheduleModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('rescheduleModal').classList.contains('hidden')) {
            closeRescheduleModal();
        }
        if (!document.getElementById('appointmentModal').classList.contains('hidden')) {
            document.getElementById('appointmentModal').classList.add('hidden');
        }
        if (!document.getElementById('consultationModal').classList.contains('hidden')) {
            document.getElementById('consultationModal').classList.add('hidden');
        }
    }
});
</script>
@endpush