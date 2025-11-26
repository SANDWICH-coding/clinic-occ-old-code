<?php
// app/Notifications/MedicalRecordUpdate.php

namespace App\Notifications;

use App\Models\MedicalRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class MedicalRecordUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    protected $medicalRecord;
    protected $action;

    public function __construct(MedicalRecord $medicalRecord, string $action)
    {
        $this->medicalRecord = $medicalRecord;
        $this->action = $action;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'medical_record_id' => $this->medicalRecord->id,
            'action' => $this->action,
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'url' => $this->getUrl($notifiable),
            'icon' => 'file-medical',
            'color' => 'blue',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    private function getTitle()
    {
        return match($this->action) {
            'created' => 'Medical Record Created',
            'updated' => 'Medical Record Updated',
            default => 'Medical Record Update',
        };
    }

    private function getMessage()
    {
        return match($this->action) {
            'created' => "Your medical record has been created",
            'updated' => "Your medical record has been updated",
            default => "Medical record has been modified",
        };
    }

    private function getUrl($notifiable)
    {
        $role = $notifiable->role;
        return route("{$role}.medical-records.show", $this->medicalRecord);
    }
}