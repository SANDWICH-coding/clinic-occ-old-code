<?php
// database/migrations/2025_10_02_194800_create_chat_conversations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nurse_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            // Ensure one conversation per nurse-student pair
            $table->unique(['nurse_id', 'student_id'], 'nurse_student_unique');

            // Indexes for faster queries
            $table->index('nurse_id', 'chat_conversations_nurse_id_index');
            $table->index('student_id', 'chat_conversations_student_id_index');
            $table->index('last_message_at', 'chat_conversations_last_message_at_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_conversations');
    }
};