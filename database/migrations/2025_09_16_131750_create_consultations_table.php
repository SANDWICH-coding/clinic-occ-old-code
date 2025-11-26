<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            
            // Student and nurse references
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('nurse_id')->constrained('users')->onDelete('cascade');
            
            // Consultation type and priority - UPDATED to match blade
           $table->enum('type', [
                'walk_in', 'appointment'
            ])->default('walk_in');
            
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            
            // Status
            $table->enum('status', [
                'registered', 'in_progress', 'completed', 'cancelled'
            ])->default('registered');
            
            // Main complaint and symptoms - UPDATED to match blade
            $table->text('chief_complaint'); // Changed from string to text
            $table->integer('pain_level')->default(0); // Changed from enum to integer (0-10)
            $table->text('symptoms_description')->nullable();
            
            // NEW: Initial notes from blade template
            $table->text('initial_notes')->nullable();
            
            // Vital signs - UPDATED to match blade template
            $table->decimal('temperature', 4, 1)->nullable()->comment('in °C');
            $table->integer('blood_pressure_systolic')->nullable()->comment('mmHg');
            $table->integer('blood_pressure_diastolic')->nullable()->comment('mmHg');
            $table->integer('heart_rate')->nullable()->comment('BPM');
            $table->integer('respiratory_rate')->nullable()->comment('per minute');
            $table->decimal('oxygen_saturation', 5, 2)->nullable()->comment('percentage');
            $table->decimal('weight', 5, 2)->nullable()->comment('kg');
            $table->decimal('height', 5, 2)->nullable()->comment('cm');
            
            // NEW: Treatment & Diagnosis section from blade template
            $table->text('diagnosis')->nullable();
            $table->text('treatment_provided')->nullable();
            $table->text('medications_given')->nullable();
            $table->text('procedures_performed')->nullable();
            $table->text('home_care_instructions')->nullable();
            
            // Timestamps
            $table->timestamp('consultation_date')->useCurrent();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['student_id', 'created_at']);
            $table->index(['status', 'consultation_date']);
            $table->index('type');
            $table->index('priority');
            $table->index('pain_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};