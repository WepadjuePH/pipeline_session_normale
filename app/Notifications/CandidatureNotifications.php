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
        return (new MailMessage)
            ->subject('Candidature soumise avec succès')
            ->greeting("Bonjour {$notifiable->prenom} {$notifiable->nom},")
            ->line("Votre candidature pour le concours {$this->candidature->concours->nom} a été soumise avec succès.")
            ->line("Votre code candidat est : **{$this->candidature->code_candidat}**")
            ->line("Veuillez vous présenter au centre de dépôt pour la validation de vos documents physiques.")
            ->action('Voir ma candidature', url("/candidat/candidatures/{$this->candidature->id}"))
            ->line('Merci d\'utiliser notre plateforme !');
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

class CandidatureValidatedNotification extends Notification
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
        return (new MailMessage)
            ->subject('Candidature validée - Convocation')
            ->greeting("Félicitations {$notifiable->prenom} {$notifiable->nom} !")
            ->line("Votre candidature a été validée.")
            ->line("**Centre d'examen:** {$this->candidature->centreExamen->nom}")
            ->line("**Salle:** {$this->candidature->salleExamen->nom}")
            ->line("**Numéro de table:** {$this->candidature->numero_table}")
            ->line("**Date:** {$this->candidature->concours->date_examen_formattee}")
            ->line("**Heure:** {$this->candidature->concours->heure_examen_formattee}")
            ->action('Télécharger ma convocation', url("/candidat/candidatures/{$this->candidature->id}/fiche"))
            ->line('Présentez-vous 30 minutes avant le début de l\'épreuve.');
    }

    public function toArray($notifiable): array
    {
        return [
            'candidature_id' => $this->candidature->id,
            'code_candidat' => $this->candidature->code_candidat,
            'salle' => $this->candidature->salleExamen->nom,
            'numero_table' => $this->candidature->numero_table,
            'message' => 'Candidature validée - Convocation disponible',
        ];
    }
}

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
