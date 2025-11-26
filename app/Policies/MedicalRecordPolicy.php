<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MedicalRecordPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any medical records.
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff(); // Assuming you have this method in User model
    }

    /**
     * Determine whether the user can view the medical record.
     */
    public function view(User $user, MedicalRecord $medicalRecord): bool
    {
        // Students can only view their own records
        if ($user->isStudent()) {
            return $user->id === $medicalRecord->user_id;
        }
        
        // Staff can view all records
        return $user->isStaff();
    }

    /**
     * Determine whether the user can create medical records.
     */
    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can create a medical record for a specific user.
     */
    public function createFor(User $user, User $targetUser): bool
    {
        if (!$user->isStaff()) {
            return false;
        }

        // Prevent creating duplicate medical records
        if ($targetUser->medicalRecord) {
            return false;
        }

        // Nurses can only create records for students
        if ($user->isNurse() && !$targetUser->isStudent()) {
            return false;
        }

        // Dean can create records for anyone
        return true;
    }

    /**
     * Determine whether the user can update the medical record.
     */
    public function update(User $user, MedicalRecord $medicalRecord): bool
    {
        // Students cannot update medical records
        if ($user->isStudent()) {
            return false;
        }

        return $user->isStaff();
    }

    /**
     * Determine whether the user can delete the medical record.
     */
    public function delete(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->isDean();
    }

    /**
     * Determine whether the user can restore the medical record.
     */
    public function restore(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->isDean();
    }

    /**
     * Determine whether the user can permanently delete the medical record.
     */
    public function forceDelete(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->isDean();
    }

    /**
     * Determine whether the user can export medical records.
     */
    public function export(User $user): bool
    {
        return $user->isDean();
    }

    /**
     * Determine whether the user can view sensitive medical information.
     */
    public function viewSensitive(User $user, MedicalRecord $medicalRecord): bool
    {
        return $this->view($user, $medicalRecord);
    }

    /**
     * Determine whether the user can edit sensitive medical information.
     */
    public function editSensitive(User $user, MedicalRecord $medicalRecord): bool
    {
        return $this->update($user, $medicalRecord);
    }

    /**
     * Determine whether the user can download the medical record.
     */
    public function download(User $user, MedicalRecord $medicalRecord): bool
    {
        return $this->view($user, $medicalRecord);
    }

    /**
     * Determine whether the user can share the medical record with external parties.
     */
    public function share(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->isDean();
    }

    /**
     * Determine whether the user can add notes to the medical record.
     */
    public function addNotes(User $user, MedicalRecord $medicalRecord): bool
    {
        return $this->update($user, $medicalRecord);
    }

    /**
     * Determine whether the user can view emergency contact information.
     */
    public function viewEmergencyContacts(User $user, MedicalRecord $medicalRecord): bool
    {
        return $this->view($user, $medicalRecord);
    }

    /**
     * Determine whether the user can edit emergency contact information.
     */
    public function editEmergencyContacts(User $user, MedicalRecord $medicalRecord): bool
    {
        // Students can edit their own emergency contacts
        if ($user->isStudent() && $user->id === $medicalRecord->user_id) {
            return true;
        }
        
        return $user->isStaff();
    }

    /**
     * Determine whether the user can view vaccination records.
     */
    public function viewVaccinationRecords(User $user, MedicalRecord $medicalRecord): bool
    {
        return $this->view($user, $medicalRecord);
    }

    /**
     * Determine whether the user can update vaccination records.
     */
    public function updateVaccinationRecords(User $user, MedicalRecord $medicalRecord): bool
    {
        return $this->update($user, $medicalRecord);
    }

    /**
     * Before hook - runs before any other authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Super admin bypass (if you have one)
        // if ($user->isSuperAdmin()) {
        //     return true;
        // }
        
        return null;
    }
}