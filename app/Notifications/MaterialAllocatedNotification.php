<?php

namespace App\Notifications;

use App\Models\BomHeader;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaterialAllocatedNotification extends Notification
{
    use Queueable;

    private BomHeader $bomHeader;

    /**
     * Create a new notification instance.
     */
    public function __construct(BomHeader $bomHeader)
    {
        $this->bomHeader = $bomHeader;
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
        $allocatedCount = $this->bomHeader->materialAllocations()->count();
        $projectName = $this->bomHeader->project->name;

        return (new MailMessage)
            ->subject("Material Allocation Provisioned: {$projectName} (v{$this->bomHeader->version})")
            ->greeting("Hello Operations team,")
            ->line("Good news! System inventory calculations have completed for {$projectName} (Version {$this->bomHeader->version}).")
            ->line("A total of {$allocatedCount} line items have been successfully reserved and allocated directly to their respective roles.")
            ->action('View Material Allocations', url('/'))
            ->line('The reserved stock has been locked and is available for collection.');
    }
}
