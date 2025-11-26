// database/migrations/2024_01_01_000004_create_symptom_illness_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symptom_illness', function (Blueprint $table) {
            $table->id();
            $table->foreignId('symptom_id')->constrained()->onDelete('cascade');
            $table->foreignId('possible_illness_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['symptom_id', 'possible_illness_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symptom_illness');
    }
};