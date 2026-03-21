<?php

namespace App\Notifications;

use App\Models\Candidature;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ValidationAnnuleeNotification extends Notification
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
            ->subject('Validation Annulée - Nouvelle Vérification Requise')
            ->greeting("Bonjour {$notifiable->prenom} {$notifiable->nom},")
            ->line("La validation de votre candidature a été annulée.")
            ->line("**Code Candidat:** {$this->candidature->code_candidat}")
            ->line("**Motif:** {$this->motif}")
            ->line("Votre candidature sera re-vérifiée prochainement.")
            ->line("Vous recevrez une nouvelle convocation une fois la vérification terminée.")
            ->action('Voir ma candidature', url("/candidat/candidatures/{$this->candidature->id}"))
            ->line('En cas de question, contactez le centre de dépôt.');
    }

    public function toArray($notifiable): array
    {
        return [
            'candidature_id' => $this->candidature->id,
            'code_candidat' => $this->candidature->code_candidat,
            'motif' => $this->motif,
            'message' => 'Validation annulée - Nouvelle vérification requise',
        ];
    }
}
