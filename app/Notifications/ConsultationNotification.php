<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Consultation;

class ConsultationNotification extends Notification
{
    use Queueable;

    protected $consultation;
    protected $action;
    protected $additionalData;

    /**
     * Create a new notification instance.
     */
    public function __construct(Consultation $consultation, string $action, array $additionalData = [])
    {
        $this->consultation = $consultation;
        $this->action = $action;
        $this->additionalData = $additionalData;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database']; // Only database notifications, no emails
    }

    /**
     * Get the array representation of the notification (for database storage).
     */
    public function toArray($notifiable): array
    {
        $data = [
            'consultation_id' => $this->consultation->id,
            'action' => $this->action,
            'url' => route('student.consultations.show', $this->consultation->id),
        ];

        // Set title, message, icon, and color based on action
        switch ($this->action) {
            case 'registered':
                $data['title'] = 'Consultation Registered';
                $data['message'] = 'Your ' . ($this->additionalData['consultation_type'] ?? 'consultation') . ' has been registered by ' . ($this->additionalData['nurse_name'] ?? 'the nurse') . '.';
                $data['icon'] = 'clipboard-check';
                $data['color'] = 'blue';
                break;

            case 'started':
                $data['title'] = 'Consultation Started';
                $data['message'] = 'Your ' . ($this->additionalData['consultation_type'] ?? 'consultation') . ' has been started by ' . ($this->additionalData['nurse_name'] ?? 'the nurse') . '.';
                $data['icon'] = 'play-circle';
                $data['color'] = 'green';
                break;

            case 'completed':
                $data['title'] = 'Consultation Completed';
                $data['message'] = 'Your ' . ($this->additionalData['consultation_type'] ?? 'consultation') . ' has been completed. Please review your consultation details.';
                $data['icon'] = 'check-circle';
                $data['color'] = 'green';
                break;

            case 'updated':
                $changes = $this->additionalData['changes'] ?? [];
                $changesText = !empty($changes) ? implode(', ', $changes) : 'consultation details';
                $data['title'] = 'Consultation Updated';
                $data['message'] = 'Your consultation has been updated: ' . $changesText . '.';
                $data['icon'] = 'edit';
                $data['color'] = 'yellow';
                break;

            case 'cancelled':
                $data['title'] = 'Consultation Cancelled';
                $data['message'] = 'Your consultation has been cancelled. Reason: ' . ($this->consultation->cancellation_reason ?? 'Not specified');
                $data['icon'] = 'x-circle';
                $data['color'] = 'red';
                break;

            default:
                $data['title'] = 'Consultation Update';
                $data['message'] = 'There has been an update to your consultation.';
                $data['icon'] = 'bell';
                $data['color'] = 'gray';
                break;
        }

        // Merge any additional data
        return array_merge($data, $this->additionalData);
    }
}