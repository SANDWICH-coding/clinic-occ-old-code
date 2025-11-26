<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Appointment Modal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 p-8">
    <!-- Demo Button -->
    <div class="max-w-2xl mx-auto mb-8">
        <button onclick="openRescheduleModal(123)" class="px-6 py-3 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition flex items-center">
            <i class="fas fa-calendar-alt mr-2"></i>
            Open Reschedule Modal
        </button>
    </div>

    <!-- Reschedule Modal -->
    <div id="rescheduleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl mx-auto my-8 transform transition-all">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-amber-50">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-yellow-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Reschedule Appointment</h3>
                        <p class="text-sm text-gray-600">Select a new date and time</p>
                    </div>
                </div>
                <button onclick="closeRescheduleModal()" class="text-gray-400 hover:text-gray-600 transition p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <form id="rescheduleForm" method="POST" class="p-6 space-y-6">
                <input type="hidden" name="_token" value="">
                <input type="hidden" name="_method" value="PATCH">

                <!-- Step 1: Date Selection -->
                <div class="space-y-3">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="flex items-center justify-center w-6 h-6 bg-yellow-600 text-white rounded-full text-xs font-bold">1</div>
                        <label for="reschedule_new_date" class="text-sm font-semibold text-gray-700">
                            New Appointment Date <span class="text-red-500">*</span>
                        </label>
                    </div>
                    <div class="relative">
                        <input type="date" 
                               id="reschedule_new_date" 
                               name="new_appointment_date"
                               min=""
                               max=""
                               required
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition bg-white">
                        <i class="fas fa-calendar absolute right-4 top-3.5 text-gray-400 pointer-events-none"></i>
                    </div>
                    <p class="text-xs text-gray-600">Select a date to view available time slots</p>
                </div>

                <!-- Step 2: Time Selection -->
                <div class="space-y-3">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="flex items-center justify-center w-6 h-6 bg-yellow-600 text-white rounded-full text-xs font-bold">2</div>
                        <label class="text-sm font-semibold text-gray-700">
                            New Appointment Time <span class="text-red-500">*</span>
                        </label>
                    </div>
                    
                    <!-- Time Slots Container -->
                    <div id="rescheduleTimeSlots" class="grid grid-cols-3 gap-3 p-4 bg-gray-50 rounded-lg border-2 border-gray-200 min-h-[140px]">
                        <div class="col-span-3 flex flex-col items-center justify-center py-6 text-gray-400">
                            <i class="fas fa-calendar-alt text-3xl mb-2"></i>
                            <p class="text-sm font-medium">Select a date above to view available time slots</p>
                        </div>
                    </div>
                    
                    <input type="hidden" id="reschedule_new_time" name="new_appointment_time" required>
                    <p class="text-xs text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Click a time slot to select it — the chosen slot will be highlighted in blue
                    </p>
                </div>

                <!-- Step 3: Reason -->
                <div class="space-y-3">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="flex items-center justify-center w-6 h-6 bg-yellow-600 text-white rounded-full text-xs font-bold">3</div>
                        <label for="reschedule_reason_input" class="text-sm font-semibold text-gray-700">
                            Reason for Rescheduling <span class="text-red-500">*</span>
                        </label>
                    </div>
                    <textarea id="reschedule_reason_input" 
                              name="reschedule_reason" 
                              rows="4" 
                              required
                              minlength="10"
                              placeholder="Please provide a reason for rescheduling this appointment..."
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition resize-none"></textarea>
                    <p class="text-xs text-gray-600">Minimum 10 characters required</p>
                </div>

                <!-- Selected Summary Box -->
                <div id="selectedSummary" class="hidden bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-blue-600 mt-1"></i>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Selected Appointment</p>
                            <p class="text-sm text-gray-700 mt-1">
                                <span id="summaryDate"></span> at <span id="summaryTime" class="font-semibold text-blue-600"></span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <button type="button" 
                            onclick="closeRescheduleModal()" 
                            class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" 
                            id="submitBtn"
                            class="px-6 py-2.5 bg-gradient-to-r from-yellow-600 to-amber-600 text-white rounded-lg font-medium hover:from-yellow-700 hover:to-amber-700 transition duration-200 flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-check mr-2"></i>Reschedule Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* Time Slot Button Styling */
        .time-slot-btn {
            display: block;
            padding: 0.875rem 0.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            background-color: #ffffff;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            width: 100%;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            outline: none;
            position: relative;
            overflow: hidden;
        }

        .time-slot-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(234, 179, 8, 0.1);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .time-slot-btn:hover:not(.selected) {
            transform: translateY(-2px);
            border-color: #eab308;
            box-shadow: 0 4px 12px rgba(234, 179, 8, 0.25);
            background-color: #fffbeb;
        }

        .time-slot-btn:hover:not(.selected)::before {
            width: 300px;
            height: 300px;
        }

        .time-slot-btn.selected {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            color: #ffffff !important;
            border-color: #1e40af !important;
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4) !important;
            font-weight: 700 !important;
            transform: scale(1.05) !important;
        }

        .time-slot-btn.selected::after {
            content: '✓';
            position: absolute;
            top: 4px;
            right: 6px;
            font-size: 1rem;
            font-weight: bold;
            color: #ffffff;
            animation: checkmark 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes checkmark {
            0% {
                opacity: 0;
                transform: scale(0) rotate(-45deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }

        .time-slot-btn.selected:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%) !important;
            transform: scale(1.08) !important;
            box-shadow: 0 12px 32px rgba(37, 99, 235, 0.5) !important;
        }

        .loading-spinner {
            display: inline-block;
            width: 1.5rem;
            height: 1.5rem;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #eab308;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Modal Animation */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #rescheduleModal:not(.hidden) .bg-white {
            animation: slideIn 0.3s ease-out;
        }
    </style>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        let selectedTimeSlot = null;

        // Set min and max dates
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const maxDate = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000);
            
            const minString = today.toISOString().split('T')[0];
            const maxString = maxDate.toISOString().split('T')[0];
            
            document.getElementById('reschedule_new_date').min = minString;
            document.getElementById('reschedule_new_date').max = maxString;
        });

        function openRescheduleModal(appointmentId) {
            const modal = document.getElementById('rescheduleModal');
            const form = document.getElementById('rescheduleForm');
            form.action = `/nurse/appointments/${appointmentId}/reschedule`;
            form.reset();
            selectedTimeSlot = null;
            document.getElementById('reschedule_new_time').value = '';
            document.getElementById('selectedSummary').classList.add('hidden');
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
                <div class="col-span-3 flex flex-col items-center justify-center py-6 text-gray-400">
                    <i class="fas fa-calendar-alt text-3xl mb-2"></i>
                    <p class="text-sm font-medium">Select a date above to view available time slots</p>
                </div>
            `;
        }

        // Date change handler
        document.getElementById('reschedule_new_date').addEventListener('change', function() {
            if (this.value) {
                loadRescheduleTimeSlots(this.value);
            }
        });

        function loadRescheduleTimeSlots(date) {
            const container = document.getElementById('rescheduleTimeSlots');
            container.innerHTML = `
                <div class="col-span-3 flex flex-col items-center justify-center py-6">
                    <div class="loading-spinner mb-3"></div>
                    <p class="text-sm text-gray-600 font-medium">Loading available time slots...</p>
                </div>
            `;

            // Simulate API call - replace with your actual endpoint
            setTimeout(() => {
                const mockSlots = [
                    { value: '09:00', label: '9:00', period: 'morning', is_available: true },
                    { value: '09:30', label: '9:30', period: 'morning', is_available: true },
                    { value: '10:00', label: '10:00', period: 'morning', is_available: false },
                    { value: '14:00', label: '2:00', period: 'afternoon', is_available: true },
                    { value: '14:30', label: '2:30', period: 'afternoon', is_available: true },
                    { value: '15:00', label: '3:00', period: 'afternoon', is_available: true },
                ];

                container.innerHTML = '';
                let availableCount = 0;

                mockSlots.forEach(slot => {
                    if (slot.is_available) {
                        availableCount++;
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'time-slot-btn';
                        btn.dataset.time = slot.value;
                        btn.innerHTML = `
                            <div style="font-weight: 600; line-height: 1.2;">${slot.label}</div>
                            <div style="font-size: 0.7rem; opacity: 0.7; line-height: 1;">${slot.period.charAt(0).toUpperCase() + slot.period.slice(1)}</div>
                        `;

                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            
                            // Remove selection from all buttons
                            document.querySelectorAll('.time-slot-btn').forEach(b => b.classList.remove('selected'));
                            
                            // Add selection to clicked button
                            this.classList.add('selected');
                            selectedTimeSlot = { value: slot.value, label: slot.label };
                            document.getElementById('reschedule_new_time').value = slot.value;
                            
                            // Update summary
                            updateSummary();
                        });

                        container.appendChild(btn);
                    }
                });

                if (availableCount === 0) {
                    container.innerHTML = `
                        <div class="col-span-3 flex flex-col items-center justify-center py-8">
                            <i class="fas fa-calendar-times text-gray-300 text-3xl mb-2"></i>
                            <p class="text-gray-500 font-medium">No available slots</p>
                            <p class="text-xs text-gray-400 mt-1">Please select a different date</p>
                        </div>
                    `;
                }
            }, 500);
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

        // Form validation
        document.getElementById('rescheduleForm').addEventListener('submit', function(e) {
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
        });

        // Close on outside click
        document.getElementById('rescheduleModal').addEventListener('click', function(e) {
            if (e.target === this) closeRescheduleModal();
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('rescheduleModal').classList.contains('hidden')) {
                closeRescheduleModal();
            }
        });
    </script>
</body>
</html>