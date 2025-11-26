<?php
// app/Providers/AuthServiceProvider.php

namespace App\Providers;

use App\Models\User;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use App\Models\SymptomLog;
use App\Models\Symptom;
use App\Models\PossibleIllness;
use App\Models\ChatConversation;
use App\Policies\UserPolicy;
use App\Policies\MedicalRecordPolicy;
use App\Policies\AppointmentPolicy;
use App\Policies\SymptomLogPolicy;
use App\Policies\SymptomPolicy;
use App\Policies\PossibleIllnessPolicy;
use App\Policies\ChatConversationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        MedicalRecord::class => MedicalRecordPolicy::class,
        Appointment::class => AppointmentPolicy::class,
        SymptomLog::class => SymptomLogPolicy::class,
        Symptom::class => SymptomPolicy::class,
        PossibleIllness::class => PossibleIllnessPolicy::class,
        ChatConversation::class => ChatConversationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Super Admin gates (Dean only)
        Gate::define('manage-staff', fn (User $user) => $user->isDean());
        Gate::define('export-data', fn (User $user) => $user->isDean());
        Gate::define('manage-system-settings', fn (User $user) => $user->isDean());
        Gate::define('moderate-chats', fn (User $user) => $user->isDean());
        Gate::define('view-all-chats', fn (User $user) => $user->isDean());
        Gate::define('delete-any-conversation', fn (User $user) => $user->isDean());

        // Admin gates (Dean or Nurse)
        Gate::define('access-admin-panel', fn (User $user) => $user->isDean() || $user->isNurse());
        Gate::define('view-reports', fn (User $user) => $user->isDean() || $user->isNurse());
        Gate::define('create-medical-record', fn (User $user) => $user->isDean() || $user->isNurse());
        Gate::define('manage-appointments', fn (User $user) => $user->isDean() || $user->isNurse());
        Gate::define('view-all-students', fn (User $user) => $user->isDean() || $user->isNurse());
        Gate::define('manage-symptoms-database', fn (User $user) => $user->isDean() || $user->isNurse());

        // Chat gates
        Gate::define('access-chat', fn (User $user) => $user->isNurse() || $user->isStudent());
        Gate::define('initiate-chat', fn (User $user) => $user->isNurse());
        Gate::define('search-students', fn (User $user) => $user->isNurse());
        
        // Gate for viewing specific conversation
        Gate::define('view-conversation', function (User $user, ChatConversation $conversation) {
            return $conversation->nurse_id === $user->id 
                || $conversation->student_id === $user->id
                || $user->isDean();
        });
        
        // Gate for sending messages in conversation
        Gate::define('send-message', function (User $user, ChatConversation $conversation) {
            return $conversation->nurse_id === $user->id 
                || $conversation->student_id === $user->id;
        });

        // Student gates
        Gate::define('view-own-records', fn (User $user) => $user->isStudent());
        Gate::define('create-symptom-log', fn (User $user) => $user->isStudent());
        Gate::define('book-appointment', fn (User $user) => $user->isStudent());
        Gate::define('chat-with-nurse', fn (User $user) => $user->isStudent());
    }
}