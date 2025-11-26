<?php

// =============================================================================
// DATABASE MIGRATIONS
// =============================================================================

// database/migrations/2024_01_01_000001_create_medical_records_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_auto_created')->default(false); // ← ADDED THIS LINE
            // Basic health metrics
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            $table->decimal('height', 5, 2)->nullable()->comment('Height in cm');
            $table->decimal('weight', 5, 2)->nullable()->comment('Weight in kg');
            $table->string('allergies')->nullable();
            $table->text('past_illnesses')->nullable(); // ADDED: Referenced in form and controller
            
            // Medical history
            $table->boolean('has_been_pregnant')->default(false);
            $table->boolean('has_undergone_surgery')->default(false);
            $table->text('surgery_details')->nullable();
            $table->boolean('is_taking_maintenance_drugs')->default(false);
            $table->text('maintenance_drugs_specify')->nullable();
            $table->boolean('has_been_hospitalized_6_months')->default(false);
            $table->text('hospitalization_details_6_months')->nullable();
            
            // Family history
            $table->text('family_history_details')->nullable(); // ADDED: Referenced in model fillable
            
            // Disability information
            $table->boolean('is_pwd')->default(false);
            $table->string('pwd_id')->nullable(); // ADDED: PWD ID number
            $table->text('pwd_disability_details')->nullable();
            $table->text('pwd_reason')->nullable(); // ADDED: Reason for PWD status
            $table->text('notes_health_problems')->nullable();
            
            // Vaccination information
            $table->boolean('is_fully_vaccinated')->default(false);
            $table->enum('vaccine_type', [
                'Pfizer-BioNTech', 'Moderna', 'AstraZeneca', 'J&J', 
                'Sinopharm', 'Sinovac', 'COVAXIN', 'Covovax', 
                'Sputnik', 'N/A', 'Other'
            ])->nullable();
            $table->string('vaccine_name')->nullable(); // ADDED: Alternative vaccine name field
            $table->date('vaccine_date')->nullable(); // ADDED: Date of vaccination
            $table->string('other_vaccine_type')->nullable();
            $table->enum('number_of_doses', ['1 dose', '2 doses', 'N/A'])->nullable();
            $table->boolean('has_received_booster')->default(false);
            $table->enum('number_of_boosters', ['1 dose', '2 doses', 'None'])->nullable();
            $table->enum('booster_type', [
                'Pfizer-BioNTech', 'Moderna', 'AstraZeneca', 
                'Sinovac', 'None', 'Other'
            ])->nullable();
            
            // Emergency contacts
            $table->string('emergency_contact_name_1')->nullable();
            $table->string('emergency_contact_number_1')->nullable();
            $table->string('emergency_contact_relationship_1', 50)->nullable(); // ← FIXED: Proper field definition
            $table->string('emergency_contact_name_2')->nullable();
            $table->string('emergency_contact_number_2')->nullable();
            $table->string('emergency_contact_relationship_2', 50)->nullable(); // ← FIXED: Proper field definition
            
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};