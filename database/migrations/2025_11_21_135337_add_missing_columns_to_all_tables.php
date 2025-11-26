<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing columns to users table
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'last_login_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_login_at')->nullable()->after('last_seen_at');
            });
        }

        // Add missing columns to medical_records table
        if (Schema::hasTable('medical_records')) {
            Schema::table('medical_records', function (Blueprint $table) {
                if (!Schema::hasColumn('medical_records', 'diagnosis')) {
                    $table->text('diagnosis')->nullable()->after('family_history_details');
                }
                if (!Schema::hasColumn('medical_records', 'chronic_conditions')) {
                    $table->text('chronic_conditions')->nullable()->after('diagnosis');
                }
            });
        }

        // Add missing columns to appointments table
        if (Schema::hasTable('appointments') && !Schema::hasColumn('appointments', 'is_walk_in')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->boolean('is_walk_in')->default(false)->after('is_follow_up');
                $table->integer('walk_in_priority')->nullable()->after('is_walk_in');
            });
        }

        // Add missing columns to symptom_logs table
        if (Schema::hasTable('symptom_logs')) {
            Schema::table('symptom_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('symptom_logs', 'severity')) {
                    $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium')->after('symptoms');
                }
                if (!Schema::hasColumn('symptom_logs', 'logged_at')) {
                    $table->timestamp('logged_at')->nullable()->after('severity');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'last_login_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('last_login_at');
            });
        }

        if (Schema::hasTable('medical_records')) {
            Schema::table('medical_records', function (Blueprint $table) {
                if (Schema::hasColumn('medical_records', 'diagnosis')) {
                    $table->dropColumn('diagnosis');
                }
                if (Schema::hasColumn('medical_records', 'chronic_conditions')) {
                    $table->dropColumn('chronic_conditions');
                }
            });
        }

        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                if (Schema::hasColumn('appointments', 'is_walk_in')) {
                    $table->dropColumn(['is_walk_in', 'walk_in_priority']);
                }
            });
        }

        if (Schema::hasTable('symptom_logs')) {
            Schema::table('symptom_logs', function (Blueprint $table) {
                if (Schema::hasColumn('symptom_logs', 'severity')) {
                    $table->dropColumn(['severity', 'logged_at']);
                }
            });
        }
    }
};