<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symptom_logs', function (Blueprint $table) {
            $table->id();
            
            // User relationship
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('student_id')->nullable()->comment('Student ID for easy reference and reporting');
            
            // Symptom data
            $table->json('symptoms')->comment('Array of selected symptom names');
            $table->json('possible_illnesses')->nullable()->comment('Array of suggested illness names');
            
            // Emergency and severity tracking
            $table->boolean('is_emergency')->default(false)->comment('True if emergency symptoms detected');
            $table->integer('severity_rating')->nullable()->comment('Severity rating 1-5 scale (1=very mild, 5=very severe)');
            
            // Additional information
            $table->text('notes')->nullable()->comment('Additional notes or observations from patient');
            $table->text('follow_up_notes')->nullable()->comment('Notes for follow-up care or recommendations');
            
            // Location and context
            $table->string('location')->nullable()->comment('Where symptoms were experienced (home, campus, etc)');
            $table->json('vital_signs')->nullable()->comment('Basic vital signs if recorded (temp, BP, pulse, etc)');
            
            // Timing information
            $table->timestamp('logged_at')->useCurrent()->comment('When symptoms were logged by patient');
            $table->timestamp('symptoms_started_at')->nullable()->comment('When symptoms first appeared');
            $table->integer('duration_hours')->nullable()->comment('How long symptoms have been present in hours');
            
            // Status tracking
            $table->enum('status', ['active', 'resolved', 'follow_up_needed', 'referred'])
                  ->default('active')
                  ->comment('Current status of the symptom log');
            
            // Staff interaction
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->comment('When reviewed by medical staff');
            $table->text('staff_notes')->nullable()->comment('Notes added by medical staff');
            $table->text('recommendations')->nullable()->comment('Recommendations given by staff');
            
            // Follow-up tracking
            $table->boolean('requires_follow_up')->default(false)->comment('Whether this log requires follow-up');
            $table->timestamp('follow_up_scheduled_at')->nullable()->comment('When follow-up is scheduled');
            $table->foreignId('related_appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            
            // System timestamps
            $table->timestamps();
            $table->softDeletes(); // For data retention compliance
            
            // Performance indexes
            $table->index(['user_id', 'created_at'], 'symptom_logs_user_created_idx');
            $table->index(['student_id', 'logged_at'], 'symptom_logs_student_logged_idx');
            $table->index(['is_emergency', 'created_at'], 'symptom_logs_emergency_idx');
            $table->index('logged_at');
            $table->index('status');
            $table->index(['severity_rating', 'created_at'], 'symptom_logs_severity_idx');
            $table->index('requires_follow_up');
            $table->index(['reviewed_by', 'reviewed_at'], 'symptom_logs_reviewed_idx');
            
            // Compound indexes for common queries
            $table->index(['user_id', 'status', 'created_at'], 'symptom_logs_user_status_idx');
            $table->index(['is_emergency', 'status', 'logged_at'], 'symptom_logs_emergency_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symptom_logs');
    }
};