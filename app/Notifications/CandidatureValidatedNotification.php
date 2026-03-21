<?php

namespace App\Notifications;

use App\Models\Candidature;
use App\Http\Controllers\PublicFicheController;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Storage;

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
        $centreExamen = $this->candidature->centreExamen?->nom ?? 'À déterminer';
        $salle = $this->candidature->salleExamen?->nom ?? 'À déterminer';
        $date = $this->candidature->concours?->date_examen_formattee ?? 'À déterminer';
        $heure = $this->candidature->concours?->heure_examen_formattee ?? 'À déterminer';

        // Générer le token public pour télécharger la fiche
        $token = PublicFicheController::genererToken($this->candidature);
        $downloadUrl = url("/fiche/{$this->candidature->code_candidat}/{$token}");

        $message = (new MailMessage)
            ->subject('Candidature validée - Convocation')
            ->greeting("Félicitations {$notifiable->prenom} {$notifiable->nom} !")
            ->line("Votre candidature a été validée.")
            ->line("**Centre d'examen:** {$centreExamen}")
            ->line("**Salle:** {$salle}")
            ->line("**Numéro de table:** {$this->candidature->numero_table}")
            ->line("**Date:** {$date}")
            ->line("**Heure:** {$heure}")
            ->action('Télécharger ma convocation', $downloadUrl)
            ->line('Présentez-vous 30 minutes avant le début de l\'épreuve.')
            ->line('Votre convocation est également jointe à cet email.');

        // Joindre la convocation PDF
        $convocationPath = storage_path("app/public/fiches/convocation_{$this->candidature->code_candidat}.pdf");
        if (file_exists($convocationPath)) {
            $message->attach($convocationPath, [
                'as' => "convocation_{$this->candidature->code_candidat}.pdf",
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
            'salle' => $this->candidature->salleExamen->nom ?? 'À déterminer',
            'numero_table' => $this->candidature->numero_table,
            'message' => 'Candidature validée - Convocation disponible',
        ];
    }
}
