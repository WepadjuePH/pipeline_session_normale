<?php

namespace App\Notifications;

use App\Models\Candidature;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CandidatureRejectedNotification extends Notification
{
    use Queueable;

    protected $candidature;
    protected $motif;

    public function __construct(Candidature $candidature, string $motif)
    {
        $this->candidature = $candidature;
        $this->motif = $motif;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Candidature - Documents à corriger')
            ->greeting("Bonjour {$notifiable->prenom} {$notifiable->nom},")
            ->line("Votre candidature nécessite des corrections.")
            ->line("**Motif:** {$this->motif}")
            ->line("Veuillez corriger les documents et soumettre à nouveau.")
            ->action('Modifier ma candidature', url("/candidat/candidatures/{$this->candidature->id}"))
            ->line('En cas de question, contactez le centre de dépôt.');
    }

    public function toArray($notifiable): array
    {
        return [
            'candidature_id' => $this->candidature->id,
            'code_candidat' => $this->candidature->code_candidat,
            'motif' => $this->motif,
            'message' => 'Documents à corriger',
        ];
    }
}