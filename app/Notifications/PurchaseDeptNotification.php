<?php

namespace App\Notifications;

use App\Models\BomHeader;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseDeptNotification extends Notification
{
    use Queueable;

    private BomHeader $bomHeader;
    private string $batchId;

    /**
     * Create a new notification instance.
     */
    public function __construct(BomHeader $bomHeader, string $batchId)
    {
        $this->bomHeader = $bomHeader;
        $this->batchId = $batchId;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $shortfallCount = $this->bomHeader->purchaseIntents()->count();
        $projectName = $this->bomHeader->project->name;

        return (new MailMessage)
            ->subject("Procurement Pipeline Alert: Shortfalls Identified for {$projectName} (v{$this->bomHeader->version})")
            ->greeting("Hello Procurement Department,")
            ->line("An automated inventory evaluation has run for a new BOM upload of {$projectName} (Version {$this->bomHeader->version}).")
            ->line("A total of {$shortfallCount} items had insufficient stock levels and require purchase orders.")
            ->line("Purchase batch tracker UUID: {$this->batchId}")
            ->action('View Purchase Pipeline', url('/'))
            ->line('Please review and acknowledge these purchase intents in the control dashboard.');
    }
}
