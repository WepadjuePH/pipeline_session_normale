<?php

namespace App\Notifications;

use App\Models\Candidature;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CandidatureSubmittedNotification extends Notification
{
    use Queueable;

    protected $candidature;

    public function __construct(Candidature $candidature)
    {
        $this->candidature = $candidature;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Candidature soumise avec succès')
            ->greeting("Bonjour {$notifiable->prenom} {$notifiable->nom},")
            ->line("Votre candidature pour le concours {$this->candidature->concours->nom} a été soumise avec succès.")
            ->line("Votre code candidat est : **{$this->candidature->code_candidat}**")
            ->line("Veuillez vous présenter au centre de dépôt **{$this->candidature->centreDepot->nom}** pour la validation de vos documents physiques.")
            ->line("Vous recevrez votre fiche de convocation avec QR code par email après la validation de vos documents au centre de dépôt.")
            ->action('Voir ma candidature', url("/candidat/candidatures/{$this->candidature->id}"))
            ->line('Merci d\'utiliser notre plateforme !');

        // Joindre la fiche provisoire
        $fichePath = storage_path("app/public/fiches/fiche_provisoire_{$this->candidature->code_candidat}.pdf");
        if (file_exists($fichePath)) {
            $message->attach($fichePath, [
                'as' => "fiche_provisoire_{$this->candidature->code_candidat}.pdf",
                'mime' => 'application/pdf',
            ]);
        }

        return $message;
    }

    public function toArray($notifiable): array
    {
        return [
            'candidature_id' => $this->candidature->id,
            'code_candidat' => $this->candidature->code_candidat,
            'concours' => $this->candidature->concours->nom,
            'message' => 'Candidature soumise avec succès',
        ];
    }
}
