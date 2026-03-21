<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ListeCandidatsNotification extends Notification
{
    use Queueable;

    protected $candidatures;
    protected $concours;
    protected $centreExamen;

    public function __construct($candidatures, $concours, $centreExamen)
    {
        $this->candidatures = $candidatures;
        $this->concours = $concours;
        $this->centreExamen = $centreExamen;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $nombre = $this->candidatures->count();

        return (new MailMessage)
            ->subject("Liste des candidats autorisés - {$nombre} candidat(s)")
            ->greeting("Bonjour Agent d'Examen,")
            ->line("Vous avez reçu la liste des candidats autorisés à composer.")
            ->line("**Concours** : {$this->concours->nom}")
            ->line("**Centre d'examen** : {$this->centreExamen->nom}")
            ->line("**Nombre de candidats** : {$nombre}")
            ->line("**Date d'examen** : {$this->concours->date_examen->format('d/m/Y')}")
            ->line("**Heure d'examen** : {$this->concours->heure_examen_formattee}")
            ->line("**Candidats autorisés** :")
            ->line($this->candidatures->map(function ($c) {
                return "- {$c->code_candidat} : {$c->user->nom_complet} (Salle {$c->salleExamen->nom}, Table {$c->numero_table})";
            })->implode("\n"))
            ->action('Voir la liste complète', url('/agent/examen/candidatures'))
            ->line('Merci !');
    }

    public function toArray($notifiable): array
    {
        return [
            'nombre_candidats' => $this->candidatures->count(),
            'concours' => $this->concours->nom,
            'centre_examen' => $this->centreExamen->nom,
            'date_examen' => $this->concours->date_examen->format('d/m/Y'),
            'heure_examen' => $this->concours->heure_examen_formattee,
            'message' => 'Liste des candidats autorisés reçue',
        ];
    }
}
