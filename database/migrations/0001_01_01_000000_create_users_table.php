<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['student', 'nurse', 'dean'])->default('student');
            $table->enum('department', ['BSIT', 'BSBA', 'EDUC'])->nullable(); // Added department column
            $table->string('student_id')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            // Updated enum with all course options used in your application
            $table->enum('course', ['BSIT', 'BSBA-MM', 'BSBA-FM', 'BSED', 'BEED'])->nullable();
            $table->enum('year_level', ['1st year', '2nd year', '3rd year', '4th year'])->nullable();
            $table->string('section', 10)->nullable();
            $table->year('academic_year')->nullable()->comment('Current academic year (e.g., 2024)');
            $table->timestamp('year_level_updated_at')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->timestamp('password_changed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};