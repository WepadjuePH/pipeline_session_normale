<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RapportExamenNotification extends Notification
{
    use Queueable;

    protected $stats;
    protected $concours;
    protected $centreExamen;
    protected $agent;

    public function __construct($stats, $concours, $centreExamen, $agent)
    {
        $this->stats = $stats;
        $this->concours = $concours;
        $this->centreExamen = $centreExamen;
        $this->agent = $agent;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $tauxPresence = $this->stats['total'] > 0 
            ? round(($this->stats['present'] / $this->stats['total']) * 100, 2) 
            : 0;

        return (new MailMessage)
            ->subject("Rapport d'examen - {$this->concours->nom}")
            ->greeting("Bonjour Admin,")
            ->line("Le rapport d'examen a été envoyé par l'agent du centre d'examen.")
            ->line("**Concours** : {$this->concours->nom}")
            ->line("**Centre d'examen** : {$this->centreExamen->nom}")
            ->line("**Agent** : {$this->agent->nom_complet}")
            ->line("**Date d'examen** : {$this->concours->date_examen->format('d/m/Y')}")
            ->line("")
            ->line("**STATISTIQUES :**")
            ->line("- Total candidats : {$this->stats['total']}")
            ->line("- Présents : {$this->stats['present']}")
            ->line("- Absents : {$this->stats['absent']}")
            ->line("- Fraude : {$this->stats['fraude']}")
            ->line("- Taux de présence : {$tauxPresence}%")
            ->action('Voir le rapport complet', url('/admin/candidatures'))
            ->line('Merci !');
    }

    public function toArray($notifiable): array
    {
        return [
            'concours' => $this->concours->nom,
            'centre_examen' => $this->centreExamen->nom,
            'agent' => $this->agent->nom_complet,
            'statistiques' => $this->stats,
            'message' => 'Rapport d\'examen reçu',
        ];
    }
}
