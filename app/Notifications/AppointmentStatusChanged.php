<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class AppointmentStatusChanged extends Notification
{
    use Queueable;

    protected $appointment;
    protected $action;
    protected $nurseName;
    protected $additionalData;

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment, string $action, array $additionalData = [])
    {
        $this->appointment = $appointment;
        $this->action = $action;
        $this->nurseName = $additionalData['nurse_name'] ?? 'Clinic Staff';
        $this->additionalData = $additionalData;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage);
        
        switch ($this->action) {
            case 'accepted':
                $mail->subject('Appointment Confirmed - OCC Clinic')
                    ->greeting("Hello {$notifiable->first_name}!")
                    ->line("Your appointment request has been accepted by {$this->nurseName}.")
                    ->line("**Appointment Details:**")
                    ->line("Date: " . $this->appointment->appointment_date->format('F j, Y'))
                    ->line("Time: " . Carbon::parse($this->appointment->appointment_time)->format('g:i A'))
                    ->line("Reason: " . $this->appointment->reason)
                    ->action('View Appointment', route('student.appointments.show', $this->appointment))
                    ->line('Please arrive 10 minutes before your scheduled time.')
                    ->line('If you need to reschedule, please do so at least 24 hours in advance.');
                break;

            case 'rescheduled':
                $oldDate = $this->additionalData['old_date'] ?? 'Previous date';
                $oldTime = $this->additionalData['old_time'] ?? 'Previous time';
                $reason = $this->additionalData['reason'] ?? 'Schedule conflict';
                
                $mail->subject('Appointment Rescheduled - OCC Clinic')
                    ->greeting("Hello {$notifiable->first_name}!")
                    ->line("Your appointment has been rescheduled by {$this->nurseName}.")
                    ->line("**Previous Schedule:**")
                    ->line("Date: {$oldDate}")
                    ->line("Time: {$oldTime}")
                    ->line("**New Schedule:**")
                    ->line("Date: " . $this->appointment->appointment_date->format('F j, Y'))
                    ->line("Time: " . Carbon::parse($this->appointment->appointment_time)->format('g:i A'))
                    ->line("**Reason:** {$reason}")
                    ->action('Confirm New Schedule', route('student.appointments.show', $this->appointment))
                    ->line('Please confirm your availability for the new schedule.')
                    ->line('If this time doesn\'t work for you, you can request another reschedule.');
                break;

            case 'rejected':
                $reason = $this->additionalData['reason'] ?? 'No reason provided';
                
                $mail->subject('Appointment Request Declined - OCC Clinic')
                    ->greeting("Hello {$notifiable->first_name},")
                    ->line("We regret to inform you that your appointment request has been declined.")
                    ->line("**Original Request:**")
                    ->line("Date: " . $this->appointment->appointment_date->format('F j, Y'))
                    ->line("Time: " . Carbon::parse($this->appointment->appointment_time)->format('g:i A'))
                    ->line("**Reason:** {$reason}")
                    ->action('Request New Appointment', route('student.appointments.create'))
                    ->line('You can submit a new appointment request at any time.')
                    ->line('For urgent concerns, please visit the clinic directly or call our hotline.');
                break;

            case 'cancelled':
                $reason = $this->additionalData['reason'] ?? 'No reason provided';
                
                $mail->subject('Appointment Cancelled - OCC Clinic')
                    ->greeting("Hello {$notifiable->first_name},")
                    ->line("Your appointment has been cancelled by {$this->nurseName}.")
                    ->line("**Cancelled Appointment:**")
                    ->line("Date: " . $this->appointment->appointment_date->format('F j, Y'))
                    ->line("Time: " . Carbon::parse($this->appointment->appointment_time)->format('g:i A'))
                    ->line("**Reason:** {$reason}")
                    ->action('Book New Appointment', route('student.appointments.create'))
                    ->line('We apologize for any inconvenience caused.')
                    ->line('You can schedule a new appointment at your convenience.');
                break;
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $data = [
            'appointment_id' => $this->appointment->id,
            'action' => $this->action,
            'nurse_name' => $this->nurseName,
            'appointment_date' => $this->appointment->appointment_date->format('M j, Y'),
            'appointment_time' => Carbon::parse($this->appointment->appointment_time)->format('g:i A'),
            'url' => route('student.appointments.show', $this->appointment),
        ];

        switch ($this->action) {
            case 'accepted':
                $data['title'] = 'Appointment Confirmed';
                $data['message'] = "Your appointment on {$data['appointment_date']} at {$data['appointment_time']} has been confirmed.";
                $data['icon'] = 'check-circle';
                $data['color'] = 'green';
                break;

            case 'rescheduled':
                $data['title'] = 'Appointment Rescheduled';
                $data['message'] = "Your appointment has been moved to {$data['appointment_date']} at {$data['appointment_time']}. Please confirm.";
                $data['icon'] = 'calendar';
                $data['color'] = 'blue';
                $data['old_date'] = $this->additionalData['old_date'] ?? null;
                $data['old_time'] = $this->additionalData['old_time'] ?? null;
                $data['reason'] = $this->additionalData['reason'] ?? null;
                break;

            case 'rejected':
                $data['title'] = 'Appointment Declined';
                $data['message'] = "Your appointment request for {$data['appointment_date']} has been declined.";
                $data['icon'] = 'times-circle';
                $data['color'] = 'red';
                $data['reason'] = $this->additionalData['reason'] ?? null;
                break;

            case 'cancelled':
                $data['title'] = 'Appointment Cancelled';
                $data['message'] = "Your appointment on {$data['appointment_date']} at {$data['appointment_time']} has been cancelled.";
                $data['icon'] = 'ban';
                $data['color'] = 'yellow';
                $data['reason'] = $this->additionalData['reason'] ?? null;
                break;
        }

        return $data;
    }
}