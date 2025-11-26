<?php

use App\Http\Controllers\{
    AuthController,
    DashboardController,
    ChangePasswordController,
    StudentAcademicController,
    MedicalRecordController,
    AppointmentController,
    SymptomLogController,
    SymptomController,
    PossibleIllnessController,
    PrescriptionController,
    NotificationController,
    ReportController,
    UserController,
    SystemConfigController,
    SymptomCheckerController,
    ConsultationController,
    ChatController,
    MedicalDataManagementController,
    DeanDashboardController,
    
};
use App\Http\Controllers\Nurse\{
    NurseController,
    NurseAnalyticsController,
    StudentReportController
};
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Welcome Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication Routes
Route::controller(AuthController::class)->middleware('guest')->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
    Route::get('/verify-email', 'showEmailVerificationForm')->name('verification.notice');
    Route::post('/email/verification-notification', 'resendVerificationEmail')->name('verification.send');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Password Reset Routes
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Main Dashboard Route - Redirects to appropriate dashboard
Route::middleware('auth')->get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->role === 'dean') {
        $email = strtolower($user->email);
        if (str_contains($email, 'bsit')) {
            return redirect()->route('dean.dashboard.bsit.dashboard'); // ← FIXED
        } elseif (str_contains($email, 'bsba')) {
            return redirect()->route('dean.dashboard.bsba.dashboard'); // ← FIXED
        } elseif (str_contains($email, 'educ')) {
            return redirect()->route('dean.dashboard.educ.dashboard'); // ← FIXED
        }
        return redirect()->route('dean.dashboard');
    }
    return match ($user->role) {
        'student' => redirect()->route('student.dashboard'),
        'nurse' => redirect()->route('nurse.dashboard'),
        default => redirect()->route('home')->with('error', 'Invalid user role.')
    };
})->name('dashboard');

// ============================================================================
// STUDENT ROUTES
// ============================================================================
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'student'])->name('dashboard');

    // Profile & Account Management
    Route::get('/profile', [UserController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::get('/change-password', [ChangePasswordController::class, 'showChangePasswordForm'])->name('change-password');
    Route::post('/change-password', [ChangePasswordController::class, 'changePassword'])->name('update-password');

    // Academic Information
    Route::get('/academic-info', [StudentAcademicController::class, 'showUpdateForm'])->name('academic-info');
    Route::post('/academic-info/update', [StudentAcademicController::class, 'updateAcademicInfo'])->name('academic-info.update');

    // Student Medical Records - Full CRUD
    Route::prefix('medical-records')->name('medical-records.')->group(function () {
        Route::get('/', [MedicalRecordController::class, 'studentIndex'])->name('index');
        Route::get('/create', [MedicalRecordController::class, 'studentCreate'])->name('create');
        Route::post('/', [MedicalRecordController::class, 'studentStore'])->name('store');
        Route::get('/{medicalRecord}', [MedicalRecordController::class, 'studentShow'])->name('show');
        Route::get('/{medicalRecord}/edit', [MedicalRecordController::class, 'studentEdit'])->name('edit');
        Route::put('/{medicalRecord}', [MedicalRecordController::class, 'studentUpdate'])->name('update');
    });

    // Emergency Contacts
    Route::post('/emergency-contacts/update', [MedicalRecordController::class, 'updateEmergencyContacts'])->name('emergency-contacts.update');

 Route::prefix('appointments')->name('appointments.')->group(function () {
  Route::get('/available-slots', [AppointmentController::class, 'getAvailableSlots'])->name('available-slots');
    Route::get('/available-dates', [AppointmentController::class, 'availableDates'])->name('available-dates'); // ADD THIS LINE
    Route::get('/create', [AppointmentController::class, 'create'])->name('create');
     Route::post('/analyze-symptoms', [AppointmentController::class, 'analyzeSymptoms'])->name('analyze-symptoms');

    // 2️⃣ PARAMETERIZED ACTION ROUTES (specific actions on appointments)
    // POST routes
    Route::post('/{appointment}/confirm-reschedule', [AppointmentController::class, 'confirmReschedule'])->name('confirm-reschedule');
    Route::post('/{appointment}/request-reschedule', [AppointmentController::class, 'requestReschedule'])->name('request-reschedule');
    Route::post('/{appointment}/accept-followup', [AppointmentController::class, 'acceptFollowUp'])->name('accept-followup');
    Route::post('/{appointment}/decline-followup', [AppointmentController::class, 'declineFollowUp'])->name('decline-followup');
    Route::post('/{appointment}/request-followup-reschedule', [AppointmentController::class, 'requestFollowUpReschedule'])->name('request-followup-reschedule');
    Route::post('/{appointment}/feedback', [AppointmentController::class, 'submitFeedback'])->name('feedback');
    
    // GET routes with parameters
    Route::get('/{appointment}/edit', [AppointmentController::class, 'edit'])->name('edit');
    
    // PUT/PATCH/DELETE routes
    Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('update');
    Route::delete('/{appointment}', [AppointmentController::class, 'destroy'])->name('destroy');
    
    // 3️⃣ GENERIC RESTFUL ROUTES (MUST BE LAST)
    Route::get('/', [AppointmentController::class, 'index'])->name('index');
    Route::post('/', [AppointmentController::class, 'store'])->name('store');
    Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('show');

    
});
// Add this to student routes group
Route::prefix('conversations')->name('conversations.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
});
    // Prescriptions
    Route::prefix('prescriptions')->name('prescriptions.')->group(function () {
        Route::get('/', [PrescriptionController::class, 'studentIndex'])->name('index');
        Route::get('/{prescription}', [PrescriptionController::class, 'studentShow'])->name('show');
        Route::post('/{prescription}/mark-taken', [PrescriptionController::class, 'markAsTaken'])->name('mark-taken');
        Route::get('/{prescription}/download', [PrescriptionController::class, 'downloadPrescription'])->name('download');
    });

    // Symptom Checker
    Route::prefix('symptom-checker')->name('symptom-checker.')->group(function () {
        Route::get('/', [SymptomCheckerController::class, 'index'])->name('index');
        Route::post('/check', [SymptomCheckerController::class, 'check'])->name('check');
        Route::get('/history', [SymptomCheckerController::class, 'history'])->name('history');
        Route::get('/history/{id}', [SymptomCheckerController::class, 'showLog'])->name('show-log');
    });

    // Symptom Logs & Health Tracking
    Route::resource('symptom-logs', SymptomLogController::class);
    Route::get('/symptoms/checker', [SymptomController::class, 'checker'])->name('symptoms.checker');
    Route::post('/symptoms/check', [SymptomController::class, 'checkSymptoms'])->name('symptoms.check');
    Route::get('/health-tracker', [SymptomLogController::class, 'healthTracker'])->name('health-tracker');

    // Health Reports
    Route::get('/health-report', [ReportController::class, 'studentHealthReport'])->name('health-report');
    Route::get('/appointment-history', [AppointmentController::class, 'studentHistory'])->name('appointment-history');

    // Student Consultations (View Only)
    Route::prefix('consultations')->name('consultations.')->group(function () {
        Route::get('/', [ConsultationController::class, 'studentIndex'])->name('index');
        Route::get('/{consultation}/download', [ConsultationController::class, 'downloadConsultation'])->name('download');
        Route::get('/{consultation}', [ConsultationController::class, 'studentShow'])->name('show');
        
    });
});

// ============================================================================
// NURSE ROUTES
// ============================================================================
Route::middleware(['auth', 'role:nurse'])->prefix('nurse')->name('nurse.')->group(function () {
    // Profile & Account Management
    Route::get('/profile', [UserController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::get('/change-password', [ChangePasswordController::class, 'showChangePasswordForm'])->name('change-password');
    Route::post('/change-password', [ChangePasswordController::class, 'changePassword'])->name('update-password');


  // Dashboard & Analytics
Route::get('/dashboard', [DashboardController::class, 'nurse'])->name('dashboard');
Route::get('/dashboard-view', [AppointmentController::class, 'index'])->name('dashboard-view');

// Chart data routes - UPDATED FOR CONSISTENCY
Route::get('/dashboard/top-symptoms', [DashboardController::class, 'getTopSymptoms'])->name('dashboard.top-symptoms');
Route::get('/dashboard/symptom-trends', [DashboardController::class, 'getSymptomTrends'])->name('dashboard.symptom-trends');
Route::get('/dashboard/symptoms-by-demographic', [DashboardController::class, 'getSymptomsByDemographic'])->name('dashboard.symptoms-by-demographic');
Route::get('/dashboard/course-distribution', [DashboardController::class, 'courseDistribution'])->name('dashboard.course-distribution');
Route::get('/dashboard/illness-trends', [DashboardController::class, 'illnessTrends'])->name('dashboard.illness-trends');
Route::get('/dashboard/illness-by-section', [DashboardController::class, 'illnessBySection'])->name('dashboard.illness-by-section');
Route::get('/dashboard/illness-by-course', [DashboardController::class, 'illnessByCourse'])->name('dashboard.illness-by-course');
Route::get('/dashboard/illness-by-year-level', [DashboardController::class, 'illnessByYearLevel'])->name('dashboard.illness-by-year-level');
Route::get('/dashboard/illness-by-week', [DashboardController::class, 'illnessByWeek'])->name('dashboard.illness-by-week');
Route::get('/dashboard/illness-by-year-details', [DashboardController::class, 'getIllnessByYearDetails'])->name('dashboard.illness-by-year-details');

// ADD THESE NEW ROUTES FOR THE MOST COMMON ILLNESS FUNCTIONALITY
Route::get('/dashboard/most-common-illness-by-course', [DashboardController::class, 'mostCommonIllnessByCourse'])->name('dashboard.most-common-illness-by-course');
Route::get('/dashboard/symptom-trends-with-illness', [DashboardController::class, 'symptomTrendsWithTopIllness'])->name('dashboard.symptom-trends-with-illness');

// Department-specific analytics routes
Route::get('/dashboard/top-symptoms/{department}', [DashboardController::class, 'topSymptoms'])->name('dashboard.top-symptoms.department');
Route::get('/dashboard/symptom-trends/{department}', [DashboardController::class, 'symptomTrends'])->name('dashboard.symptom-trends.department');
Route::get('/dashboard/symptoms-by-program/{department}', [DashboardController::class, 'symptomsByProgram'])->name('dashboard.symptoms-by-program');
Route::get('/dashboard/symptoms-by-year/{department}', [DashboardController::class, 'symptomsByYear'])->name('dashboard.symptoms-by-year');
Route::get('/dashboard/health-alerts/{department}', [DashboardController::class, 'healthAlerts'])->name('dashboard.health-alerts');
    // ============================================================================
    // NURSE CONSULTATIONS - PROPERLY ORDERED ROUTES
    // ============================================================================
    Route::prefix('consultations')->name('consultations.')->group(function () {
        
        // ✅ 1. NON-PARAMETERIZED ROUTES (specific, non-wildcard)
        Route::get('/students/search', [ConsultationController::class, 'searchStudents'])->name('students.search');
        Route::get('/queue', [ConsultationController::class, 'queue'])->name('queue');
        Route::get('/queue-dashboard', [ConsultationController::class, 'queueDashboard'])->name('queue-dashboard');
        Route::get('/create', [ConsultationController::class, 'create'])->name('create');
        Route::get('/create-walk-in', [ConsultationController::class, 'createWalkIn'])->name('create-walk-in');
        Route::get('/stats', [ConsultationController::class, 'stats'])->name('stats');
        Route::get('/queue-status', [ConsultationController::class, 'getQueueStatus'])->name('queue-status');

         // 🔥 ADD THIS MISSING ROUTE - MUST BE BEFORE PARAMETERIZED ROUTES
    Route::get('/todays-appointments', [ConsultationController::class, 'todaysAppointments'])->name('todays-appointments');

        // ✅ 2. STUDENT DATA API ENDPOINTS (numeric ID parameter - comes BEFORE {consultation})
        Route::get('/check-student-status/{studentId}', [ConsultationController::class, 'checkStudentStatus'])->name('check-student-status');
        Route::get('/students/{studentId}/medical-data', [ConsultationController::class, 'getStudentMedicalData'])->name('students.medical-data');
        Route::get('/students/{studentId}/detailed-data', [ConsultationController::class, 'getDetailedStudentData'])->name('students.detailed-data');
        Route::get('/students/{studentId}/appointments', [ConsultationController::class, 'getStudentAppointments'])->name('students.appointments');

        // ✅ 3. CONSULTATION FORM DISPLAY ROUTES (GET with {consultation} - comes before POST/PUT/DELETE)
        Route::get('/{consultation}/edit', [ConsultationController::class, 'edit'])->name('edit');
        Route::get('/{consultation}/conduct', [ConsultationController::class, 'conduct'])->name('conduct');
        Route::get('/{consultation}/complete/form', [ConsultationController::class, 'completeForm'])->name('complete.form');

        // ✅ 4. ACTION ROUTES (POST/PUT/PATCH/DELETE - modifying operations)
        Route::post('/', [ConsultationController::class, 'store'])->name('store');
        Route::post('/store-walk-in', [ConsultationController::class, 'storeWalkIn'])->name('store-walk-in');
        Route::post('/{consultation}/create-from-appointment', [ConsultationController::class, 'createFromAppointment'])->name('create-from-appointment');
        Route::post('/{consultation}/start', [ConsultationController::class, 'start'])->name('start');
        Route::post('/{consultation}/update-progress', [ConsultationController::class, 'updateProgress'])->name('update-progress');
        Route::post('/{consultation}/save-progress', [ConsultationController::class, 'saveProgress'])->name('save-progress');
        Route::post('/{consultation}/complete', [ConsultationController::class, 'complete'])->name('complete');
        Route::post('/{consultation}/mark-ready', [ConsultationController::class, 'markReady'])->name('mark-ready');
        Route::post('/{consultation}/cancel', [ConsultationController::class, 'cancel'])->name('cancel');
        Route::post('/{consultation}/notify-parent', [ConsultationController::class, 'notifyParent'])->name('notify-parent');
        Route::post('/search-students', [ConsultationController::class, 'searchStudents'])->name('searchStudents');
        Route::post('/quick-register', [ConsultationController::class, 'quickRegister'])->name('quick-register');
        Route::post('/reorder-queue', [ConsultationController::class, 'reorderQueue'])->name('reorder-queue');
        Route::post('/{consultation}/vitals', [ConsultationController::class, 'updateVitals'])->name('vitals.update');
        Route::patch('/{consultation}/update-priority', [ConsultationController::class, 'updatePriority'])->name('update-priority');
        Route::put('/{consultation}', [ConsultationController::class, 'update'])->name('update');
        Route::delete('/{consultation}', [ConsultationController::class, 'destroy'])->name('destroy');

        // ✅ 5. INFO RETRIEVAL ROUTES (GET with {consultation})
        Route::get('/{consultation}/details', [ConsultationController::class, 'getDetails'])->name('details');
        Route::get('/{consultation}/timeline', [ConsultationController::class, 'getTimeline'])->name('timeline');
        Route::get('/{consultation}/vital-history', [ConsultationController::class, 'getVitalHistory'])->name('vital-history');
        Route::get('/{consultation}/follow-up', [ConsultationController::class, 'createFollowUp'])->name('follow-up');
        Route::get('/student/{student}/history', [ConsultationController::class, 'studentHistory'])->name('student-history');

        // ✅ 6. GENERIC RESTFUL ROUTES (MUST BE LAST - most generic)
        Route::get('/', [ConsultationController::class, 'index'])->name('index');
        Route::get('/{consultation}', [ConsultationController::class, 'show'])->name('show');
    });

    // Appointments Management
    Route::prefix('appointments')->name('appointments.')->group(function () {
        // Non-parameterized specific routes (MUST come first)
        Route::get('/calendar', [AppointmentController::class, 'calendar'])->name('calendar');
        Route::get('/create-walkin', [AppointmentController::class, 'createWalkIn'])->name('create-walkin');
        Route::post('/create-walkin', [AppointmentController::class, 'storeWalkIn'])->name('store-walkin');
   
      // ✅ FIXED: Available slots route
    Route::get('/available-slots', [AppointmentController::class, 'getAvailableSlots'])->name('available-slots');


        // Parameterized specific routes (before wildcard {appointment})
        Route::get('/{appointment}/reschedule', [AppointmentController::class, 'showRescheduleForm'])->name('reschedule.form');
        Route::match(['post', 'patch'], '/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('reschedule');
        Route::get('/{appointment}/details', [AppointmentController::class, 'getDetails'])->name('details');
        Route::get('/{appointment}/edit', [AppointmentController::class, 'edit'])->name('edit');
        Route::post('/{appointment}/start-consultation', [AppointmentController::class, 'startConsultation'])->name('start-consultation');
        
        // Action routes (POST/PATCH/DELETE)
        Route::post('/{appointment}/accept', [AppointmentController::class, 'accept'])->name('accept');
        Route::post('/{appointment}/reject', [AppointmentController::class, 'reject'])->name('reject');
        Route::patch('/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('cancel');
        Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('update');
         // Add this route for available slots
   
        // Basic CRUD routes (most generic - MUST be last)
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('show');
    });

    // Medical Data Management
    Route::get('/medical-data', [MedicalDataManagementController::class, 'index'])->name('medical-data.index');
    Route::get('/medical-data/create', [MedicalDataManagementController::class, 'create'])->name('medical-data.create');
    Route::post('/medical-data', [MedicalDataManagementController::class, 'store'])->name('medical-data.store');
    Route::get('/medical-data/{id}', [MedicalDataManagementController::class, 'show'])->name('medical-data.show');
    Route::get('/medical-data/{id}/edit', [MedicalDataManagementController::class, 'edit'])->name('medical-data.edit');
    Route::put('/medical-data/{id}', [MedicalDataManagementController::class, 'update'])->name('medical-data.update');
    Route::delete('/medical-data/{id}', [MedicalDataManagementController::class, 'destroy'])->name('medical-data.destroy');

    // Medical Records Management
    Route::prefix('medical-records')->name('medical-records.')->group(function () {
        Route::get('/', [MedicalRecordController::class, 'index'])->name('index');
        Route::get('/create', [MedicalRecordController::class, 'create'])->name('create');
        Route::post('/', [MedicalRecordController::class, 'nurseStore'])->name('store');
        Route::get('/{medicalRecord}', [MedicalRecordController::class, 'nurseShow'])->name('show');
        Route::get('/{medicalRecord}/edit', [MedicalRecordController::class, 'nurseEdit'])->name('edit');
        Route::put('/{medicalRecord}', [MedicalRecordController::class, 'update'])->name('update');
        Route::delete('/{medicalRecord}', [MedicalRecordController::class, 'nurseDestroy'])->name('destroy');
        Route::get('/{medicalRecord}/print', [MedicalRecordController::class, 'print'])->name('print');
        Route::get('/search', [MedicalRecordController::class, 'search'])->name('search');
        Route::get('/{medicalRecord}/download', [MedicalRecordController::class, 'download'])->name('download');
        Route::post('/export', [MedicalRecordController::class, 'export'])->name('export');
        Route::get('/create/for/{user}', [MedicalRecordController::class, 'createFor'])->name('create-for');
    });

    // Student Management
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [MedicalRecordController::class, 'nurseStudentSearch'])->name('index');
        Route::get('/search', [MedicalRecordController::class, 'nurseStudentSearch'])->name('search');
        Route::get('/advanced-search', [MedicalRecordController::class, 'advancedStudentSearch'])->name('advanced-search');
        Route::get('/{user}', [MedicalRecordController::class, 'studentRecordDetails'])->name('show');
        Route::get('/{user}/profile', [MedicalRecordController::class, 'studentRecordDetails'])->name('profile');
        Route::get('/{user}/record', [MedicalRecordController::class, 'manageStudentRecord'])->name('record');
        Route::get('/{user}/record-details', [MedicalRecordController::class, 'studentRecordDetails'])->name('record-details');
        Route::get('/{user}/info', [MedicalRecordController::class, 'studentRecordInfo'])->name('info');
        Route::get('/{user}/dashboard', [MedicalRecordController::class, 'studentRecordDashboard'])->name('dashboard');
        Route::put('/{user}/update', [MedicalRecordController::class, 'updateStudentRecord'])->name('update');
        Route::get('/{user}/export/{format}', [MedicalRecordController::class, 'exportStudentRecord'])->name('export')->where('format', 'pdf|csv|excel');
        Route::get('/{user}/health-timeline', [MedicalRecordController::class, 'studentHealthTimeline'])->name('health-timeline');
        Route::post('/{user}/add-note', [MedicalRecordController::class, 'addMedicalNote'])->name('add-note');
    });

    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [NurseAnalyticsController::class, 'index'])->name('index');
        Route::get('/appointment-stats', [NurseAnalyticsController::class, 'appointmentStats'])->name('appointment-stats');
        Route::get('/medical-record-stats', [NurseAnalyticsController::class, 'medicalRecordStats'])->name('medical-record-stats');
        Route::get('/health-trends', [NurseAnalyticsController::class, 'healthTrends'])->name('health-trends');
        Route::get('/walk-in-consultation-stats', [NurseAnalyticsController::class, 'walkInConsultationStats'])->name('walk-in-consultation-stats');
        Route::get('/export/{type}', [NurseAnalyticsController::class, 'export'])->name('export');

        // ADD THESE:
    Route::get('/illness-by-course', [NurseAnalyticsController::class, 'illnessByCourse'])->name('illness-by-course');
    Route::get('/illness-by-year-level', [NurseAnalyticsController::class, 'illnessByYearLevel'])->name('illness-by-year-level');
    Route::get('/export/{type}', [NurseAnalyticsController::class, 'export'])->name('export');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/daily', [ReportController::class, 'dailyReport'])->name('daily');
        Route::get('/weekly', [ReportController::class, 'weeklyReport'])->name('weekly');
        Route::get('/monthly', [ReportController::class, 'monthlyReport'])->name('monthly');
        Route::get('/custom', [ReportController::class, 'customReport'])->name('custom');
        Route::get('/health-trends', [ReportController::class, 'healthTrends'])->name('health-trends');
        Route::get('/appointment-statistics', [ReportController::class, 'appointmentStatistics'])->name('appointment-statistics');
        Route::post('/generate', [ReportController::class, 'generateCustomReport'])->name('generate');
        Route::get('/{report}/download', [ReportController::class, 'download'])->name('download');
    });

    // Symptom Management
    Route::resource('symptoms', SymptomController::class);
    Route::resource('possible-illnesses', PossibleIllnessController::class);

    // Symptom Logs
    Route::prefix('symptom-logs')->name('symptom-logs.')->group(function () {
        Route::get('/', [SymptomLogController::class, 'nurseIndex'])->name('index');
        Route::get('/export', [SymptomLogController::class, 'export'])->name('export');
        Route::get('/{symptomLog}', [SymptomLogController::class, 'show'])->name('show');
        Route::get('/student/{studentId}', [SymptomLogController::class, 'studentHistory'])->name('student-history');
        Route::post('/{symptomLog}/review', [SymptomLogController::class, 'markAsReviewed'])->name('mark-reviewed');
        Route::delete('/{symptomLog}', [SymptomLogController::class, 'destroy'])->name('destroy');
    });

    Route::get('/symptom-patterns', [SymptomLogController::class, 'analyzePatterns'])->name('symptom-patterns');

    // Prescription Management
    Route::resource('prescriptions', PrescriptionController::class)->except(['create', 'store']);
    Route::patch('/prescriptions/{prescription}/status', [PrescriptionController::class, 'updateStatus'])->name('prescriptions.update-status');
    Route::get('/prescriptions/{prescription}/refill-requests', [PrescriptionController::class, 'refillRequests'])->name('prescriptions.refill-requests');
    Route::post('/prescriptions/{prescription}/approve-refill', [PrescriptionController::class, 'approveRefill'])->name('prescriptions.approve-refill');

    // System Configuration
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/config', [SystemConfigController::class, 'index'])->name('config.index');
        Route::post('/config/update', [SystemConfigController::class, 'update'])->name('config.update');
    });
 
     // Activity Log
      // Activity Log
    Route::get('/activity-log', [NurseController::class, 'activityLog'])->name('activity-log');

    // ============================================================================
    // STUDENT REPORTS ROUTES (Nurse only) - FIXED VERSION
    // ============================================================================
    Route::prefix('student-reports')->name('student-reports.')->group(function () {
        Route::get('/', [StudentReportController::class, 'index'])->name('index');
        
        // Search routes - MUST come before parameterized routes
        Route::get('/search', [StudentReportController::class, 'search'])->name('search');
        Route::get('/search-ajax', [StudentReportController::class, 'searchAjax'])->name('search-ajax');
        
        // Specific routes BEFORE parameterized routes
        Route::get('/export-all', [StudentReportController::class, 'exportAll'])->name('export-all');
        
        // Parameterized routes
        Route::get('/{studentId}', [StudentReportController::class, 'show'])->name('show');
        Route::get('/{studentId}/export-pdf', [StudentReportController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{studentId}/print', [StudentReportController::class, 'printReport'])->name('print');
        Route::get('/{studentId}/timeline', [StudentReportController::class, 'timeline'])->name('timeline');
        Route::get('/{studentId}/quick-overview', [StudentReportController::class, 'quickOverview'])->name('quick-overview');
        Route::get('/{studentId}/medical-history', [StudentReportController::class, 'medicalHistory'])->name('medical-history');
    });
});


 // ============================================================================
// DEAN ROUTES - COMPLETE VERSION WITH API ENDPOINTS
// ============================================================================
Route::middleware(['auth', 'role:dean'])->prefix('dean')->name('dean.')->group(function () {
    // Profile & Account Management
    Route::get('/profile', [UserController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::get('/change-password', [ChangePasswordController::class, 'showChangePasswordForm'])->name('change-password');
    Route::post('/change-password', [ChangePasswordController::class, 'changePassword'])->name('update-password');

    // Unified Dean Dashboard Routes
    Route::get('/dashboard', [DeanDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/{department}', [DeanDashboardController::class, 'showDepartmentDashboard'])->name('dashboard.department');
    
    // ✅ Dean Dashboard API Routes for Charts and Data
    Route::prefix('dashboard-api')->name('dashboard-api.')->group(function () {
        Route::get('/{department}/chart/{chartType}', [DeanDashboardController::class, 'getChartData'])->name('chart.data');
        
        // BSBA Program-Specific API Routes
        Route::get('/bsba/chart/bsba-mm-top-symptoms', [DeanDashboardController::class, 'getBsbaMmTopSymptoms'])->name('bsba.chart.bsba-mm-top-symptoms');
        Route::get('/bsba/chart/bsba-fm-top-symptoms', [DeanDashboardController::class, 'getBsbaFmTopSymptoms'])->name('bsba.chart.bsba-fm-top-symptoms');
        Route::get('/bsba/chart/bsba-mm-symptom-trends', [DeanDashboardController::class, 'getBsbaMmSymptomTrends'])->name('bsba.chart.bsba-mm-symptom-trends');
        Route::get('/bsba/chart/bsba-fm-symptom-trends', [DeanDashboardController::class, 'getBsbaFmSymptomTrends'])->name('bsba.chart.bsba-fm-symptom-trends');
        Route::get('/bsba/chart/bsba-mm-year-level', [DeanDashboardController::class, 'getBsbaMmYearLevel'])->name('bsba.chart.bsba-mm-year-level');
        Route::get('/bsba/chart/bsba-fm-year-level', [DeanDashboardController::class, 'getBsbaFmYearLevel'])->name('bsba.chart.bsba-fm-year-level');
        
        // ✅ BSBA Combined Analytics Routes
        Route::get('/bsba/chart/combined-year-level', [DeanDashboardController::class, 'getCombinedYearLevel'])->name('bsba.chart.combined-year-level');
        Route::get('/bsba/chart/combined-symptom-overview', [DeanDashboardController::class, 'getCombinedSymptomOverview'])->name('bsba.chart.combined-symptom-overview');
        Route::get('/bsba/chart/top-symptoms-month', [DeanDashboardController::class, 'getTopSymptomsMonth'])->name('bsba.chart.top-symptoms-month');
        Route::get('/bsba/chart/symptom-trends-6months', [DeanDashboardController::class, 'getSymptomTrends6Months'])->name('bsba.chart.symptom-trends-6months');
        
        // ✅ EDUC Program-Specific API Routes
        Route::get('/educ/chart/educ-bsed-top-symptoms', [DeanDashboardController::class, 'getEducBsedTopSymptoms'])->name('educ.chart.educ-bsed-top-symptoms');
        Route::get('/educ/chart/educ-beed-top-symptoms', [DeanDashboardController::class, 'getEducBeedTopSymptoms'])->name('educ.chart.educ-beed-top-symptoms');
        Route::get('/educ/chart/educ-bsed-symptom-trends', [DeanDashboardController::class, 'getEducBsedSymptomTrends'])->name('educ.chart.educ-bsed-symptom-trends');
        Route::get('/educ/chart/educ-beed-symptom-trends', [DeanDashboardController::class, 'getEducBeedSymptomTrends'])->name('educ.chart.educ-beed-symptom-trends');
        Route::get('/educ/chart/educ-bsed-year-level', [DeanDashboardController::class, 'getEducBsedYearLevel'])->name('educ.chart.educ-bsed-year-level');
        Route::get('/educ/chart/educ-beed-year-level', [DeanDashboardController::class, 'getEducBeedYearLevel'])->name('educ.chart.educ-beed-year-level');
        
        // ✅ EDUC Combined Analytics Routes
        Route::get('/educ/chart/combined-year-level', [DeanDashboardController::class, 'getEducCombinedYearLevel'])->name('educ.chart.combined-year-level');
        Route::get('/educ/chart/combined-symptom-overview', [DeanDashboardController::class, 'getEducCombinedSymptomOverview'])->name('educ.chart.combined-symptom-overview');
        Route::get('/educ/chart/top-symptoms-month', [DeanDashboardController::class, 'getEducTopSymptomsMonth'])->name('educ.chart.top-symptoms-month');
        Route::get('/educ/chart/symptom-trends-6months', [DeanDashboardController::class, 'getEducSymptomTrends6Months'])->name('educ.chart.symptom-trends-6months');
        
        Route::get('/{department}/recent-activity', [DeanDashboardController::class, 'getRecentActivity'])->name('recent-activity');
        Route::post('/{department}/export-report', [DeanDashboardController::class, 'exportReport'])->name('export-report');
        
        // ✅ Real-time stats and debug endpoints
        Route::get('/{department}/realtime-stats', [DeanDashboardController::class, 'getRealtimeStats'])->name('realtime-stats');
        Route::get('/{department}/debug-data', [DeanDashboardController::class, 'debugDepartmentData'])->name('debug-data');
        Route::post('/{department}/clear-cache', [DeanDashboardController::class, 'clearDashboardCache'])->name('clear-cache');
    });

    // Department-specific dashboard routes - BSIT
    Route::prefix('dashboard/bsit')->name('dashboard.bsit.')->group(function () {
        Route::get('/dashboard', function () {
            return app(DeanDashboardController::class)->showDepartmentDashboard('BSIT');
        })->name('dashboard');
        
        Route::get('/chart/{chartType}', function($chartType) {
            return app(DeanDashboardController::class)->getChartData('BSIT', $chartType);
        })->name('chart.data');
        
        Route::get('/recent-activity', function() {
            return app(DeanDashboardController::class)->getRecentActivity('BSIT');
        })->name('recent-activity');
        
        Route::post('/export-report', function(Request $request) {
            return app(DeanDashboardController::class)->exportReport('BSIT', $request);
        })->name('export-report');
        
        // ✅ Stats endpoints
        Route::get('/realtime-stats', function() {
            return app(DeanDashboardController::class)->getRealtimeStats('BSIT');
        })->name('realtime-stats');
        
        Route::get('/debug-data', function() {
            return app(DeanDashboardController::class)->debugDepartmentData('BSIT');
        })->name('debug-data');
        
        // Comprehensive Debug
        Route::get('/comprehensive-debug', function() {
            return app(DeanDashboardController::class)->comprehensiveDebug('BSIT');
        })->name('comprehensive-debug');
        
        // Clear Cache
        Route::post('/clear-cache', function() {
            return app(DeanDashboardController::class)->clearDashboardCache('BSIT');
        })->name('clear-cache');
    });

    // Department-specific dashboard routes - BSBA
    Route::prefix('dashboard/bsba')->name('dashboard.bsba.')->group(function () {
        // Main BSBA Dashboard
        Route::get('/dashboard', function () {
            return app(DeanDashboardController::class)->showDepartmentDashboard('BSBA');
        })->name('dashboard');
        
        // Chart Data Endpoints - General
        Route::get('/chart/{chartType}', function($chartType) {
            return app(DeanDashboardController::class)->getChartData('BSBA', $chartType);
        })->name('chart.data');
        
        // BSBA Program-Specific Chart Endpoints
        Route::get('/chart/bsba-mm-top-symptoms', function() {
            return app(DeanDashboardController::class)->getBsbaMmTopSymptoms();
        })->name('chart.bsba-mm-top-symptoms');
        
        Route::get('/chart/bsba-fm-top-symptoms', function() {
            return app(DeanDashboardController::class)->getBsbaFmTopSymptoms();
        })->name('chart.bsba-fm-top-symptoms');
        
        Route::get('/chart/bsba-mm-symptom-trends', function() {
            return app(DeanDashboardController::class)->getBsbaMmSymptomTrends();
        })->name('chart.bsba-mm-symptom-trends');
        
        Route::get('/chart/bsba-fm-symptom-trends', function() {
            return app(DeanDashboardController::class)->getBsbaFmSymptomTrends();
        })->name('chart.bsba-fm-symptom-trends');
        
        Route::get('/chart/bsba-mm-year-level', function() {
            return app(DeanDashboardController::class)->getBsbaMmYearLevel();
        })->name('chart.bsba-mm-year-level');
        
        Route::get('/chart/bsba-fm-year-level', function() {
            return app(DeanDashboardController::class)->getBsbaFmYearLevel();
        })->name('chart.bsba-fm-year-level');
        
        // ✅ BSBA Combined Analytics Endpoints
        Route::get('/chart/combined-year-level', function() {
            return app(DeanDashboardController::class)->getCombinedYearLevel();
        })->name('chart.combined-year-level');
        
        Route::get('/chart/combined-symptom-overview', function() {
            return app(DeanDashboardController::class)->getCombinedSymptomOverview();
        })->name('chart.combined-symptom-overview');
        
        Route::get('/chart/top-symptoms-month', function() {
            return app(DeanDashboardController::class)->getTopSymptomsMonth();
        })->name('chart.top-symptoms-month');
        
        Route::get('/chart/symptom-trends-6months', function() {
            return app(DeanDashboardController::class)->getSymptomTrends6Months();
        })->name('chart.symptom-trends-6months');
        
        // Recent Activity
        Route::get('/recent-activity', function() {
            return app(DeanDashboardController::class)->getRecentActivity('BSBA');
        })->name('recent-activity');
        
        // Export Report
        Route::post('/export-report', function(Request $request) {
            return app(DeanDashboardController::class)->exportReport('BSBA', $request);
        })->name('export-report');
        
        // Real-time Stats
        Route::get('/realtime-stats', function() {
            return app(DeanDashboardController::class)->getRealtimeStats('BSBA');
        })->name('realtime-stats');
        
        // Debug Data
        Route::get('/debug-data', function() {
            return app(DeanDashboardController::class)->debugDepartmentData('BSBA');
        })->name('debug-data');
        
        // Comprehensive Debug
        Route::get('/comprehensive-debug', function() {
            return app(DeanDashboardController::class)->comprehensiveDebug('BSBA');
        })->name('comprehensive-debug');
        
        // Clear Cache
        Route::post('/clear-cache', function() {
            return app(DeanDashboardController::class)->clearDashboardCache('BSBA');
        })->name('clear-cache');
        
        // Debug Symptom Trends
        Route::get('/chart/debug-symptom-trends', function() {
            return app(DeanDashboardController::class)->debugSymptomTrends();
        })->name('chart.debug-symptom-trends');
    });

    // Department-specific dashboard routes - EDUC - FIXED VERSION
    Route::prefix('dashboard/educ')->name('dashboard.educ.')->group(function () {
        // Main EDUC Dashboard
        Route::get('/dashboard', function () {
            return app(DeanDashboardController::class)->showDepartmentDashboard('EDUC');
        })->name('dashboard');
        
        // Chart Data Endpoints - General
        Route::get('/chart/{chartType}', function($chartType) {
            return app(DeanDashboardController::class)->getChartData('EDUC', $chartType);
        })->name('chart.data');
        
        // ✅ FIXED: EDUC Combined Analytics Endpoints - CORRECT ROUTE DEFINITIONS
        Route::get('/chart/combined-year-level', function() {
            return app(DeanDashboardController::class)->getEducCombinedYearLevel();
        })->name('chart.combined-year-level');
        
        Route::get('/chart/combined-symptom-overview', function() {
            return app(DeanDashboardController::class)->getEducCombinedSymptomOverview();
        })->name('chart.combined-symptom-overview');
        
        Route::get('/chart/top-symptoms-month', function() {
            return app(DeanDashboardController::class)->getEducTopSymptomsMonth();
        })->name('chart.top-symptoms-month');
        
        Route::get('/chart/symptom-trends-6months', function() {
            return app(DeanDashboardController::class)->getEducSymptomTrends6Months();
        })->name('chart.symptom-trends-6months');
        
        // EDUC Program-Specific Chart Endpoints
        Route::get('/chart/educ-bsed-top-symptoms', function() {
            return app(DeanDashboardController::class)->getEducBsedTopSymptoms();
        })->name('chart.educ-bsed-top-symptoms');
        
        Route::get('/chart/educ-beed-top-symptoms', function() {
            return app(DeanDashboardController::class)->getEducBeedTopSymptoms();
        })->name('chart.educ-beed-top-symptoms');
        
        Route::get('/chart/educ-bsed-symptom-trends', function() {
            return app(DeanDashboardController::class)->getEducBsedSymptomTrends();
        })->name('chart.educ-bsed-symptom-trends');
        
        Route::get('/chart/educ-beed-symptom-trends', function() {
            return app(DeanDashboardController::class)->getEducBeedSymptomTrends();
        })->name('chart.educ-beed-symptom-trends');
        
        Route::get('/chart/educ-bsed-year-level', function() {
            return app(DeanDashboardController::class)->getEducBsedYearLevel();
        })->name('chart.educ-bsed-year-level');
        
        Route::get('/chart/educ-beed-year-level', function() {
            return app(DeanDashboardController::class)->getEducBeedYearLevel();
        })->name('chart.educ-beed-year-level');
        
        // Recent Activity
        Route::get('/recent-activity', function() {
            return app(DeanDashboardController::class)->getRecentActivity('EDUC');
        })->name('recent-activity');
        
        // Export Report
        Route::post('/export-report', function(Request $request) {
            return app(DeanDashboardController::class)->exportReport('EDUC', $request);
        })->name('export-report');
        
        // Real-time Stats
        Route::get('/realtime-stats', function() {
            return app(DeanDashboardController::class)->getRealtimeStats('EDUC');
        })->name('realtime-stats');
        
        // Debug Data
        Route::get('/debug-data', function() {
            return app(DeanDashboardController::class)->debugDepartmentData('EDUC');
        })->name('debug-data');
        
        // Comprehensive Debug
        Route::get('/comprehensive-debug', function() {
            return app(DeanDashboardController::class)->comprehensiveDebug('EDUC');
        })->name('comprehensive-debug');
        
        // Clear Cache
        Route::post('/clear-cache', function() {
            return app(DeanDashboardController::class)->clearDashboardCache('EDUC');
        })->name('clear-cache');
        
        // Debug Symptom Trends
        Route::get('/chart/debug-symptom-trends', function() {
            return app(DeanDashboardController::class)->debugEducSymptomTrends();
        })->name('chart.debug-symptom-trends');
        
        // ✅ ADDED: Test data generation endpoint for development
        Route::post('/generate-test-data', function() {
            return app(DeanDashboardController::class)->generateTestEducData();
        })->name('generate-test-data');
    });

    // Comprehensive Debug Routes for all departments
    Route::get('/dashboard/bsit/comprehensive-debug', [DeanDashboardController::class, 'comprehensiveDebug'])
        ->name('dashboard.bsit.comprehensive-debug');
        
    Route::get('/dashboard/bsba/comprehensive-debug', [DeanDashboardController::class, 'comprehensiveDebug'])
        ->name('dashboard.bsba.comprehensive-debug');
        
    Route::get('/dashboard/educ/comprehensive-debug', [DeanDashboardController::class, 'comprehensiveDebug'])
        ->name('dashboard.educ.comprehensive-debug');
});

    // Academic Management
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [MedicalRecordController::class, 'nurseStudentSearch'])->name('index');
        Route::get('/search', [MedicalRecordController::class, 'nurseStudentSearch'])->name('search');
        Route::get('/analytics', [MedicalRecordController::class, 'studentAnalytics'])->name('analytics');
        Route::get('/academic-management', [StudentAcademicController::class, 'getStudentsForManagement'])->name('academic-management');
        Route::post('/bulk-update', [StudentAcademicController::class, 'bulkUpdateAcademicInfo'])->name('bulk-update');
        Route::get('/{student}/academic-details', [StudentAcademicController::class, 'showAcademicDetails'])->name('academic-details');
        Route::get('/{user}', [MedicalRecordController::class, 'studentRecordDetails'])->name('show');
        Route::get('/{user}/profile', [MedicalRecordController::class, 'studentRecordDetails'])->name('profile');
        Route::get('/{user}/record', [MedicalRecordController::class, 'manageStudentRecord'])->name('record');
        Route::get('/{user}/record-details', [MedicalRecordController::class, 'studentRecordDetails'])->name('record-details');
        Route::get('/{user}/dashboard', [MedicalRecordController::class, 'studentRecordDashboard'])->name('dashboard');
        Route::put('/{user}/update', [MedicalRecordController::class, 'updateStudentRecord'])->name('update');
        Route::get('/{user}/export/{format}', [MedicalRecordController::class, 'exportStudentRecord'])->name('export')->where('format', 'pdf|csv|excel');
        Route::delete('/{user}/archive', [MedicalRecordController::class, 'archiveStudentRecord'])->name('archive');
        Route::post('/{user}/restore', [MedicalRecordController::class, 'restoreStudentRecord'])->name('restore');
    });

    // Medical Records Management
    Route::prefix('medical-records')->name('medical-records.')->group(function () {
        Route::get('/', [MedicalRecordController::class, 'deanIndex'])->name('index');
        Route::get('/create', [MedicalRecordController::class, 'deanCreate'])->name('create');
        Route::post('/', [MedicalRecordController::class, 'deanStore'])->name('store');
        Route::get('/{medicalRecord}', [MedicalRecordController::class, 'deanShow'])->name('show');
        Route::get('/{medicalRecord}/edit', [MedicalRecordController::class, 'deanEdit'])->name('edit');
        Route::put('/{medicalRecord}', [MedicalRecordController::class, 'deanUpdate'])->name('update');
        Route::delete('/{medicalRecord}', [MedicalRecordController::class, 'deanDestroy'])->name('destroy');
        Route::get('/{medicalRecord}/download', [MedicalRecordController::class, 'download'])->name('download');
        Route::get('/{medicalRecord}/print', [MedicalRecordController::class, 'print'])->name('print');
        Route::post('/export', [MedicalRecordController::class, 'deanExport'])->name('export');
        Route::get('/search', [MedicalRecordController::class, 'deanSearch'])->name('search');
        Route::get('/create/for/{user}', [MedicalRecordController::class, 'createFor'])->name('create-for');
    });

    // Appointments Management
    Route::resource('appointments', AppointmentController::class);
    Route::prefix('appointments')->name('appointments.')->group(function () {
        // Specific routes first
        Route::get('/calendar', [AppointmentController::class, 'calendar'])->name('calendar');
        Route::get('/overview', [AppointmentController::class, 'systemOverview'])->name('overview');
        Route::get('/system-analytics', [AppointmentController::class, 'systemAnalytics'])->name('system-analytics');

        // Wildcard and action routes last
        Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('show');
        Route::get('/{appointment}/edit', [AppointmentController::class, 'edit'])->name('edit');
        Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('update');
        Route::post('/bulk-action', [AppointmentController::class, 'bulkAction'])->name('bulk-action');
        Route::post('/system-settings', [AppointmentController::class, 'updateSystemSettings'])->name('system-settings');
        Route::post('/{appointment}/accept', [AppointmentController::class, 'accept'])->name('accept');
        Route::post('/{appointment}/reject', [AppointmentController::class, 'reject'])->name('reject');
        Route::patch('/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('reschedule');
        Route::post('/{appointment}/create-consultation', [AppointmentController::class, 'createConsultation'])->name('create-consultation');
        Route::patch('/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('cancel');
        Route::patch('/{appointment}/approve', [AppointmentController::class, 'approve'])->name('approve');
        Route::patch('/{appointment}/confirm', [AppointmentController::class, 'confirm'])->name('confirm');
        Route::patch('/{appointment}/complete', [AppointmentController::class, 'complete'])->name('complete');
    });

    // Walk-in Appointments Management
    Route::prefix('walk-in-appointments')->name('walk-in-appointments.')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::get('/create', [AppointmentController::class, 'createWalkIn'])->name('create');
        Route::post('/store', [AppointmentController::class, 'storeWalkIn'])->name('store');
    });

    // Consultations Management (Dean access)
    Route::prefix('consultations')->name('consultations.')->group(function () {
        Route::get('/', [ConsultationController::class, 'deanIndex'])->name('index');
        Route::get('/{consultation}', [ConsultationController::class, 'deanShow'])->name('show');
        Route::get('/stats', [ConsultationController::class, 'deanStats'])->name('stats');
        Route::get('/reports', [ConsultationController::class, 'deanReports'])->name('reports');
        Route::post('/export', [ConsultationController::class, 'deanExport'])->name('export');
    });

    // Prescription Management
    Route::resource('prescriptions', PrescriptionController::class)->except(['create', 'store']);
    Route::patch('/prescriptions/{prescription}/status', [PrescriptionController::class, 'updateStatus'])->name('prescriptions.update-status');
    Route::get('/prescriptions/{prescription}/refill-requests', [PrescriptionController::class, 'refillRequests'])->name('prescriptions.refill-requests');
    Route::post('/prescriptions/{prescription}/approve-refill', [PrescriptionController::class, 'approveRefill'])->name('prescriptions.approve-refill');

    // Medical History Management
    Route::get('/{user}/medical-history', [MedicalRecordController::class, 'studentMedicalHistory'])->name('medical-history');
    Route::prefix('medical-history')->name('medical-history.')->group(function () {
        Route::get('/', [MedicalRecordController::class, 'medicalHistoryList'])->name('index');
        Route::get('/reports', [MedicalRecordController::class, 'medicalHistoryReports'])->name('reports');
        Route::get('/{user}/details', [MedicalRecordController::class, 'medicalHistoryDetails'])->name('details');
        Route::get('/{user}/export/{format}', [MedicalRecordController::class, 'exportMedicalHistory'])->name('export')->where('format', 'pdf|excel');
        Route::get('/system-trends', [ReportController::class, 'systemHealthTrends'])->name('system-trends');
    });

    // Staff Management
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [UserController::class, 'staffIndex'])->name('index');
        Route::get('/create', [UserController::class, 'createStaff'])->name('create');
        Route::post('/', [UserController::class, 'storeStaff'])->name('store');
        Route::get('/{user}', [UserController::class, 'showStaff'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'editStaff'])->name('edit');
        Route::put('/{user}', [UserController::class, 'updateStaff'])->name('update');
        Route::delete('/{user}', [UserController::class, 'deleteStaff'])->name('destroy');
        Route::post('/{user}/deactivate', [UserController::class, 'deactivateStaff'])->name('deactivate');
        Route::post('/{user}/activate', [UserController::class, 'activateStaff'])->name('activate');
    });

    // System Management
    Route::resource('symptoms', SymptomController::class);
    Route::resource('possible-illnesses', PossibleIllnessController::class);
    Route::get('/symptom-logs', [SymptomLogController::class, 'adminIndex'])->name('symptom-logs.index');

    // Advanced Reports and Analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'reportsIndex'])->name('index');
        Route::get('/generate', [ReportController::class, 'generateReport'])->name('generate');
        Route::get('/health-statistics', [ReportController::class, 'healthStatistics'])->name('health-statistics');
        Route::get('/appointment-analytics', [ReportController::class, 'appointmentAnalytics'])->name('appointment-analytics');
        Route::get('/system-performance', [ReportController::class, 'systemPerformance'])->name('system-performance');
        Route::get('/user-activity', [ReportController::class, 'userActivity'])->name('user-activity');
        Route::get('/financial-overview', [ReportController::class, 'financialOverview'])->name('financial-overview');
        Route::post('/custom-report', [ReportController::class, 'generateCustomReport'])->name('custom-report');
        Route::get('/export/{type}', [ReportController::class, 'exportReport'])->name('export')->where('type', 'pdf|excel|csv');
    });

    // System Settings & Configuration
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SystemConfigController::class, 'index'])->name('index');
        Route::post('/', [SystemConfigController::class, 'updateSettings'])->name('update');
        Route::get('/clinic-hours', [SystemConfigController::class, 'clinicHours'])->name('clinic-hours');
        Route::post('/clinic-hours', [SystemConfigController::class, 'updateClinicHours'])->name('clinic-hours.update');
        Route::get('/notifications', [SystemConfigController::class, 'notificationSettings'])->name('notifications');
        Route::post('/notifications', [SystemConfigController::class, 'updateNotificationSettings'])->name('notifications.update');
        Route::get('/backup', [SystemConfigController::class, 'backupSettings'])->name('backup');
        Route::post('/backup/create', [SystemConfigController::class, 'createBackup'])->name('backup.create');
        Route::get('/system-logs', [SystemConfigController::class, 'systemLogs'])->name('system-logs');
        Route::post('/maintenance-mode', [SystemConfigController::class, 'toggleMaintenanceMode'])->name('maintenance-mode');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
    ->name('notifications.unread-count');
    });

    // Audit & Compliance
    Route::prefix('audit')->name('audit.')->group(function () {
        Route::get('/', [DashboardController::class, 'auditIndex'])->name('index');
        Route::get('/user-activities', [DashboardController::class, 'userActivities'])->name('user-activities');
        Route::get('/data-changes', [DashboardController::class, 'dataChanges'])->name('data-changes');
        Route::get('/system-access', [DashboardController::class, 'systemAccess'])->name('system-access');
        Route::get('/export/{type}', [DashboardController::class, 'exportAudit'])->name('export')->where('type', 'pdf|excel|csv');
    });


// ============================================================================
// SHARED ROUTES (All authenticated users)
// ============================================================================
Route::middleware(['auth'])->group(function () {
    // Profile Routes
    Route::get('/profile', [UserController::class, 'showProfile'])->name('profile.show');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/avatar', [UserController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [UserController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::post('/profile/update-password', [ChangePasswordController::class, 'updatePassword'])->name('profile.update-password');

  // Notification Routes - FIXED VERSION
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/stats', [NotificationController::class, 'stats'])->name('stats');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count'); // FIXED: This was missing
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('/mark-multiple-read', [NotificationController::class, 'markMultipleAsRead'])->name('mark-multiple-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::get('/{id}', [NotificationController::class, 'show'])->name('show');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/multiple', [NotificationController::class, 'destroyMultiple'])->name('destroy-multiple');
    Route::delete('/clear/read', [NotificationController::class, 'clearRead'])->name('clear-read');
    Route::delete('/clear/all', [NotificationController::class, 'clearAll'])->name('clear-all');
});

    // Debug notifications route (remove in production)
    Route::get('/debug-notifications', function() {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Not authenticated']);
        }
        
        $notifications = $user->notifications()->latest()->take(10)->get();
        $unreadCount = $user->unreadNotifications()->count();
        
        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'total_notifications' => $notifications->count(),
            'unread_count' => $unreadCount,
            'notifications' => $notifications->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->toDateTimeString(),
                    'is_unread' => is_null($notification->read_at)
                ];
            }),
            'routes' => [
                'index' => route('notifications.index'),
                'unread_count' => route('notifications.unread-count')
            ]
        ]);
    });

    // Help & Support
    Route::prefix('help')->name('help.')->group(function () {
        Route::get('/', [DashboardController::class, 'helpIndex'])->name('index');
        Route::get('/faq', [DashboardController::class, 'faq'])->name('faq');
        Route::get('/contact', [DashboardController::class, 'contactSupport'])->name('contact');
        Route::post('/contact', [DashboardController::class, 'submitSupportRequest'])->name('contact.submit');
        Route::get('/user-guide', [DashboardController::class, 'userGuide'])->name('user-guide');
    });

    // Activity Logs (Personal)
    Route::get('/activity-log', [UserController::class, 'activityLog'])->name('activity-log');

    // In your Chat Routes section (around line 600+)
Route::prefix('chat')->name('chat.')->group(function () {
    
    Route::get('/', [ChatController::class, 'index'])->name('index');
    
    // FIXED: Chat-specific student search
    Route::get('/search-students', [ChatController::class, 'searchStudents'])->name('search-students');
    Route::get('/search-nurses', [ChatController::class, 'searchNurses'])->name('search-nurses');
    
    Route::post('/get-or-create-conversation', [ChatController::class, 'getOrCreateConversation'])->name('get-or-create-conversation');
    
    Route::get('/conversation/{conversationId}', [ChatController::class, 'show'])->name('conversation');
    Route::get('/unread-count', [ChatController::class, 'getUnreadCount'])->name('unread-count');
    
      // 🔥 ADD THIS MISSING ROUTE
    Route::post('/{conversationId}/message', [ChatController::class, 'sendMessage'])->name('send-message');
    
    // Message routes
    Route::post('/{conversationId}/message', [ChatController::class, 'sendMessage'])->name('message.send');
    Route::get('/{conversationId}/messages', [ChatController::class, 'getMessages'])->name('messages');
    Route::post('/{conversationId}/read', [ChatController::class, 'markAsRead'])->name('read');
});

});


// ============================================================================
// API ROUTES
// ============================================================================
Route::prefix('api/chat')->middleware('auth')->name('api.chat.')->group(function () {
    Route::get('/search-students', [ChatController::class, 'searchStudents'])->name('search-students');
    Route::get('/search-nurses', [ChatController::class, 'searchNurses'])->name('search-nurses');
    Route::post('/{conversationId}/message', [ChatController::class, 'sendMessage'])->name('message.send');
    Route::get('/{conversationId}/messages', [ChatController::class, 'getMessages'])->name('messages');
    Route::post('/{conversationId}/read', [ChatController::class, 'markAsRead'])->name('read');
    Route::get('/unread-count', [ChatController::class, 'getUnreadCount'])->name('unread.count');
});

// Debug Routes (Remove in production)
Route::middleware('auth')->group(function() {
    Route::get('/debug-students', function() {
        $students = \App\Models\User::where('role', 'student')
            ->where(function($q) {
                $q->where('first_name', 'like', '%ma%')
                  ->orWhere('last_name', 'like', '%ma%')
                  ->orWhere('student_id', 'like', '%ma%');
            })
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'student_id', 'course', 'year_level']);
        
        return response()->json([
            'count' => $students->count(),
            'students' => $students
        ]);
    });

    Route::get('/debug-routes', function() {
        $routes = [
            'chat.search-students' => route('chat.search-students'),
            'chat.search-nurses' => route('chat.search-nurses'),
            'api.chat.search-students' => route('api.chat.search-students'),
            'api.chat.search-nurses' => route('api.chat.search-nurses'),
        ];
        
        return response()->json($routes);
    })->name('debug.routes');
});

Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    // Core API Routes
    Route::get('/symptoms/search', [SymptomController::class, 'search'])->name('symptoms.search');
    Route::get('/users/search', [UserController::class, 'searchUsers'])->name('users.search');
    Route::get('/dashboard/stats', [DashboardController::class, 'getDashboardStats'])->name('dashboard.stats');

    // Student Search API
    Route::get('/students/search', function (\Illuminate\Http\Request $request) {
        $query = $request->get('q', '');
        $students = \App\Models\User::where('role', 'student')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQ) use ($query) {
                    $subQ->where('student_id', 'like', "%{$query}%")
                        ->orWhere('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit($query ? 15 : 100)
            ->get(['id', 'first_name', 'last_name', 'student_id']);
        return response()->json(['students' => $students]);
    })->name('students.search');

    Route::get('/students/quick-info/{user}', [MedicalRecordController::class, 'getStudentQuickInfo'])->name('students.quick-info');

    // Appointment API Routes
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/available-dates', [AppointmentController::class, 'availableDates'])->name('available-dates');
        Route::get('/available-slots', [AppointmentController::class, 'availableSlots'])->name('available-slots');
        Route::get('/stats', [AppointmentController::class, 'appointmentStats'])->name('stats');
        Route::get('/calendar-data', [AppointmentController::class, 'getCalendarData'])->name('calendar-data');
        Route::get('/queue-status', [AppointmentController::class, 'getQueueStatus'])->name('queue-status');
        Route::post('/quick-schedule', [AppointmentController::class, 'quickSchedule'])->name('quick-schedule');
        Route::get('/conflicts-check', [AppointmentController::class, 'checkConflicts'])->name('conflicts-check');
        Route::get('/{appointment}/pending-count', [AppointmentController::class, 'getPendingCount'])->name('pending-count');
        Route::get('/{appointment}/timeline', [AppointmentController::class, 'getAppointmentTimeline'])->name('timeline');
        Route::get('/{appointment}/day-details', [AppointmentController::class, 'dayDetails'])->name('day-details');

        // Appointment Details for Calendar Modals
        Route::get('/{appointment}/details', function (\App\Models\Appointment $appointment) {
            try {
                $appointment->load('user');
                if (!$appointment->user) {
                    return response()->json(['error' => 'User not found for this appointment'], 404);
                }
                return response()->json([
                    'id' => $appointment->id,
                    'student_name' => $appointment->user->first_name . ' ' . $appointment->user->last_name,
                    'student_id' => $appointment->user->student_id,
                    'formatted_time' => $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') : ($appointment->is_walk_in ? 'Walk-in Consultation' : 'No time set'),
                    'reason' => $appointment->reason,
                    'status_display' => ucfirst(str_replace('_', ' ', $appointment->status)),
                    'status_class' => match ($appointment->status) {
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'confirmed' => 'bg-blue-100 text-blue-800',
                        'completed' => 'bg-green-100 text-green-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800'
                    },
                    'is_walk_in' => $appointment->is_walk_in ?? false,
                    'walk_in_priority' => $appointment->walk_in_priority ?? null,
                    'can_start_consultation' => method_exists($appointment, 'canStartConsultation') ? $appointment->canStartConsultation() : false,
                    'can_mark_ready' => method_exists($appointment, 'canBeCompleted') ? $appointment->canBeCompleted() : false,
                    'is_urgent' => $appointment->is_urgent ?? false,
                    'priority' => $appointment->priority ?? \App\Models\Appointment::PRIORITY_NORMAL
                ]);
            } catch (\Exception $e) {
                \Log::error('Appointment details error: ' . $e->getMessage());
                return response()->json(['error' => 'Error loading appointment details'], 500);
            }
        })->name('details');
    });

    // Walk-in Consultation API
    Route::prefix('walk-in-appointments')->name('walk-in-appointments.')->group(function () {
        Route::get('/queue-status', [AppointmentController::class, 'getWalkInConsultationQueueStatus'])->name('queue-status');
        Route::get('/estimated-wait/{appointment}', [AppointmentController::class, 'getEstimatedWaitTime'])->name('estimated-wait');
        Route::post('/update-queue-position', [AppointmentController::class, 'updateQueuePosition'])->name('update-queue-position');
        Route::get('/next-patient', [AppointmentController::class, 'getNextPatient'])->name('next-patient');
        Route::get('/stats', [AppointmentController::class, 'walkInConsultationStats'])->name('stats');
    });

    // Consultations API
    Route::prefix('consultations')->name('consultations.')->group(function () {
        Route::get('/stats', [ConsultationController::class, 'stats'])->name('stats');
        Route::post('/quick-register', [ConsultationController::class, 'quickRegister'])->name('quick-register');
        Route::get('/queue-status', [ConsultationController::class, 'getQueueStatus'])->name('queue-status');
        Route::get('/{consultation}/details', [ConsultationController::class, 'getDetails'])->name('details');
    });

    // Chat API Routes
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::post('/{conversationId}/message', [ChatController::class, 'sendMessage'])->name('message.send');
        Route::get('/{conversationId}/messages', [ChatController::class, 'getMessages'])->name('messages');
        Route::post('/{conversationId}/read', [ChatController::class, 'markAsRead'])->name('read');
        Route::get('/unread-count', [ChatController::class, 'getUnreadCount'])->name('unread.count');
    });

    // Analytics API
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/appointment-stats', [NurseAnalyticsController::class, 'getAppointmentStats'])->name('appointment-stats');
        Route::get('/medical-record-stats', [NurseAnalyticsController::class, 'getMedicalRecordStats'])->name('medical-record-stats');
        Route::get('/health-trends-data', [NurseAnalyticsController::class, 'getHealthTrendsData'])->name('health-trends-data');
        Route::get('/walk-in-consultation-stats', [NurseAnalyticsController::class, 'getWalkInConsultationStats'])->name('walk-in-consultation-stats');
        Route::get('/system-stats', [DashboardController::class, 'getSystemStats'])->name('system-stats');
    });

    // Symptom Checker API
    Route::prefix('symptom-checker')->name('symptom-checker.')->group(function () {
        Route::get('/symptoms', [SymptomController::class, 'apiIndex'])->name('symptoms');
        Route::get('/possible-illnesses', [PossibleIllnessController::class, 'apiIndex'])->name('possible-illnesses');
        Route::post('/analyze', [SymptomCheckerController::class, 'analyze'])->name('analyze');
        Route::get('/history', [SymptomCheckerController::class, 'apiHistory'])->name('history');
    });

    // User Management API
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/search', [UserController::class, 'searchUsers'])->name('search');
        Route::get('/{user}/quick-info', [UserController::class, 'getQuickInfo'])->name('quick-info');
        Route::get('/{user}/timeline', [UserController::class, 'getTimeline'])->name('timeline');
    });

    // File Upload API
    Route::prefix('files')->name('files.')->group(function () {
        Route::post('/upload', function (\Illuminate\Http\Request $request) {
            $request->validate([
                'file' => 'required|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
                'type' => 'required|in:profile,medical,document'
            ]);

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/' . $request->type, $filename, 'public');

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => Storage::url($path),
                'filename' => $filename
            ]);
        })->name('upload');
        
        Route::delete('/{path}', function ($path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                return response()->json(['success' => true]);
            }
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        })->name('delete');
    });
});