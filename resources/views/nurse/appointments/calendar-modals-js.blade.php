<script>
// Modal rendering functions (same as previous implementation)
function renderAppointmentDetails(appointment, appointmentId) {
    const detailsContainer = document.getElementById('appointmentDetails');
    
    detailsContainer.innerHTML = `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Patient Information -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border-2 border-blue-100">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user-circle mr-3 text-blue-500"></i>
                    Patient Information
                </h4>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Full Name</p>
                            <p class="text-lg font-semibold text-gray-900">${appointment.user.full_name}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Student ID</p>
                            <p class="text-lg font-semibold text-gray-900">${appointment.user.student_id}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Email</p>
                            <p class="text-lg font-semibold text-gray-900">${appointment.user.email}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Phone</p>
                            <p class="text-lg font-semibold text-gray-900">${appointment.user.phone || 'Not provided'}</p>
                        </div>
                    </div>
                    ${appointment.user.date_of_birth ? `
                    <div>
                        <p class="text-sm font-medium text-gray-600">Date of Birth</p>
                        <p class="text-lg font-semibold text-gray-900">${appointment.user.date_of_birth}</p>
                    </div>
                    ` : ''}
                </div>
            </div>

            <!-- Appointment Details -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-6 border-2 border-green-100">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt mr-3 text-green-500"></i>
                    Appointment Details
                </h4>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Date & Time</p>
                        <p class="text-lg font-semibold text-gray-900">${appointment.formatted_date_time}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Type</p>
                            <p class="text-lg font-semibold text-gray-900">${appointment.appointment_type_display}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Status</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${appointment.status_badge_class}">
                                ${appointment.status_display}
                            </span>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Priority</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold ${appointment.is_urgent ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'}">
                            ${appointment.priority_display}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical Information -->
        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-6 border-2 border-purple-100">
            <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-stethoscope mr-3 text-purple-500"></i>
                Medical Information
            </h4>
            <div class="space-y-4">
                <div>
                    <p class="text-sm font-medium text-gray-600">Reason for Visit</p>
                    <p class="text-lg text-gray-900 leading-relaxed">${appointment.reason}</p>
                </div>
                ${appointment.symptoms ? `
                <div>
                    <p class="text-sm font-medium text-gray-600">Symptoms</p>
                    <p class="text-lg text-gray-900 leading-relaxed">${appointment.symptoms}</p>
                </div>
                ` : ''}
                ${appointment.notes ? `
                <div>
                    <p class="text-sm font-medium text-gray-600">Additional Notes</p>
                    <p class="text-lg text-gray-900 leading-relaxed whitespace-pre-line">${appointment.notes}</p>
                </div>
                ` : ''}
            </div>
        </div>

        <!-- Timeline -->
        <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-2xl p-6 border-2 border-orange-100">
            <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-history mr-3 text-orange-500"></i>
                Timeline
            </h4>
            <div class="space-y-3">
                ${appointment.created_at ? `
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Created</p>
                        <p class="text-lg font-semibold text-gray-900">${appointment.created_at}</p>
                    </div>
                </div>
                ` : ''}
                ${appointment.accepted_at ? `
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Accepted</p>
                        <p class="text-lg font-semibold text-gray-900">${appointment.accepted_at}</p>
                    </div>
                </div>
                ` : ''}
                ${appointment.rescheduled_at ? `
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Rescheduled</p>
                        <p class="text-lg font-semibold text-gray-900">${appointment.rescheduled_at}</p>
                    </div>
                </div>
                ` : ''}
                ${appointment.completed_at ? `
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-purple-500 rounded-full mr-3"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Completed</p>
                        <p class="text-lg font-semibold text-gray-900">${appointment.completed_at}</p>
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
    `;
}

// Include all other JavaScript functions from previous implementation
// (setupActionButtons, renderConsultationDetails, reschedule modal functions, etc.)
</script>