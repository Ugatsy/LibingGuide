<?php

namespace App\Notifications;

use App\Models\Contract;
use App\Models\SentClientNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Contract $contract, public string $stage) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if ($notifiable->email) $channels[] = 'mail';
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $stageLabel = $this->stage === 'treasurer' ? 'Treasurer' : 'Mayor';
        return (new MailMessage)
            ->subject('Contract — ' . $stageLabel . ' Signature Verified — HIMLAYAN')
            ->greeting('Dear ' . $notifiable->full_name . ',')
            ->line('Your Cemetery Lease Contract (#' . $this->contract->id . ') — the **' . $stageLabel . '** physical signature has been verified and recorded.')
            ->line('**Plot:** ' . ($this->contract->plot?->plot_number ?? 'N/A'))
            ->line('**Contract Date:** ' . $this->contract->contract_date->format('M d, Y'))
            ->action('View Contract', url('/contracts/' . $this->contract->id))
            ->line('Thank you for trusting HIMLAYAN.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'contract_id' => $this->contract->id,
            'stage' => $this->stage,
            'type' => 'contract_approved',
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        $stageLabel = $this->stage === 'treasurer' ? 'Treasurer' : 'Mayor';
        $data = $this->toArray($notifiable);
        SentClientNotification::create([
            'client_id' => $notifiable->id,
            'type' => 'contract_approved',
            'channel' => 'database',
            'subject' => $stageLabel . ' Signature Verified',
            'body' => 'Your contract (#' . $this->contract->id . ') for lot '
                . ($this->contract->plot?->plot_number ?? 'N/A')
                . ' — the ' . $stageLabel . ' physical signature has been verified.',
            'reference_type' => 'contract',
            'reference_id' => $this->contract->id,
            'status' => 'sent',
        ]);
        return $data;
    }
}
