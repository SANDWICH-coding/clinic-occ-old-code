<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            
            // Core appointment fields
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Student who requested the appointment');
            $table->foreignId('nurse_id')->nullable()->constrained('users')->onDelete('set null')->comment('Assigned nurse for the appointment');
            $table->date('appointment_date')->comment('Date of the appointment');
            $table->time('appointment_time')->nullable()->comment('Time set when nurse accepts or immediately for walk-ins');
            $table->text('reason')->comment('Reason for the appointment (min:10, max:500)');
            $table->enum('status', [
                'pending',           // Student requested, waiting for nurse
                'confirmed',         // Nurse accepted and scheduled time
                'rescheduled',       // Nurse rescheduled, waiting for student confirmation
                'completed',         // Appointment completed (may or may not have consultation)
                'cancelled',         // Cancelled
                'follow_up_pending', // Nurse scheduled follow-up, waiting for student
                'reschedule_requested' // Student requested reschedule
            ])->default('pending')->comment('Current status of the appointment');
            
            // Appointment type field
            $table->enum('appointment_type', [
                'scheduled',         // Regular student-requested appointment
                'walk_in',          // Walk-in appointment created by nurse
                'follow_up',        // Follow-up appointment
                'emergency'         // Emergency appointment
            ])->default('scheduled')->comment('Type of appointment');
            
            // Basic appointment details
            $table->text('notes')->nullable()->comment('General notes (max:1000)');
            $table->string('preferred_time')->nullable()->comment('Preferred time slot (morning, afternoon, any)');
            $table->boolean('is_urgent')->default(false)->comment('Is the appointment urgent?');
            $table->text('symptoms')->nullable()->comment('Student\'s initial symptom description (max:500)');
            $table->integer('priority')->default(2)->comment('Priority level (1=Low, 2=Normal, 3=High, 4=Urgent, 5=Emergency)');
            
            // Nurse acceptance
            $table->foreignId('accepted_by')->nullable()->constrained('users')->onDelete('set null')->comment('Nurse who accepted the appointment');
            $table->timestamp('accepted_at')->nullable()->comment('When nurse accepted the appointment');
            
            // Rescheduling fields
            $table->foreignId('rescheduled_by')->nullable()->constrained('users')->onDelete('set null')->comment('Nurse who rescheduled');
            $table->timestamp('rescheduled_at')->nullable()->comment('When rescheduling occurred');
            $table->text('reschedule_reason')->nullable()->comment('Reason for rescheduling');
            $table->boolean('requires_student_confirmation')->default(false)->comment('Does student need to confirm reschedule?');
            $table->timestamp('student_confirmed_at')->nullable()->comment('When student confirmed reschedule');
            $table->text('reschedule_request_reason')->nullable()->comment('Student\'s reason for requesting reschedule');
            $table->timestamp('student_requested_reschedule_at')->nullable()->comment('When student requested reschedule');
            $table->date('student_preferred_new_date')->nullable()->comment('Student\'s preferred new date for reschedule');
            $table->time('student_preferred_new_time')->nullable()->comment('Student\'s preferred new time');
            
            // Completion fields
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null')->comment('Nurse who completed the appointment');
            $table->timestamp('completed_at')->nullable()->comment('When appointment was completed');
            
            // Feedback fields
            $table->text('feedback')->nullable()->comment('Student feedback after appointment');
            $table->integer('rating')->nullable()->comment('Student rating (1-5 stars)');
            $table->timestamp('feedback_submitted_at')->nullable()->comment('When feedback was submitted');
            $table->timestamp('rating_submitted_at')->nullable()->comment('When rating was submitted');
            
            // Follow-up system
            $table->boolean('is_follow_up')->default(false)->comment('Is this a follow-up appointment?');
            $table->foreignId('parent_appointment_id')->nullable()->constrained('appointments')->onDelete('cascade')->comment('Parent appointment for follow-ups');
            $table->foreignId('created_by_nurse')->nullable()->constrained('users')->onDelete('set null')->comment('Nurse who created walk-in or follow-up');
            $table->timestamp('student_accepted_followup_at')->nullable()->comment('When student accepted follow-up');
            $table->text('decline_reason')->nullable()->comment('Reason student declined follow-up');
            $table->timestamp('student_declined_followup_at')->nullable()->comment('When student declined follow-up');
            
            // Cancellation fields
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null')->comment('Who cancelled the appointment');
            $table->timestamp('cancelled_at')->nullable()->comment('When appointment was cancelled');
            $table->text('cancellation_reason')->nullable()->comment('Reason for cancellation');
            
            // Rejection fields
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null')->comment('Nurse who rejected the appointment');
            $table->timestamp('rejected_at')->nullable()->comment('When appointment was rejected');
            $table->text('rejection_reason')->nullable()->comment('Reason for rejection');
            
            // Update tracking
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('Who last updated the appointment');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['appointment_date', 'status'], 'appointments_date_status_index');
            $table->index(['user_id', 'appointment_date'], 'appointments_user_date_index');
            $table->index(['status', 'appointment_date'], 'appointments_status_date_index');
            $table->index(['is_follow_up', 'parent_appointment_id'], 'appointments_follow_up_index');
            $table->index(['appointment_date', 'appointment_time'], 'appointments_date_time_index');
            $table->index('is_urgent', 'appointments_is_urgent_index');
            $table->index('requires_student_confirmation', 'appointments_student_confirmation_index');
            $table->index('appointment_type', 'appointments_appointment_type_index');
            $table->index(['appointment_type', 'appointment_date'], 'appointments_type_date_index');
            $table->index(['created_by_nurse', 'appointment_date'], 'appointments_nurse_date_index');
            $table->index('priority', 'appointments_priority_index');
            $table->index('nurse_id', 'appointments_nurse_id_index');
            
            // Partial unique index to prevent overbooking for non-walk-in appointments
            $table->unique(['appointment_date', 'appointment_time'], 'appointments_date_time_unique')
                  ->where('appointment_type', '!=', 'walk_in');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};