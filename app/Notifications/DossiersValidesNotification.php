<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DossiersValidesNotification extends Notification
{
    use Queueable;

    protected $candidatures;
    protected $agent;

    public function __construct($candidatures, $agent)
    {
        $this->candidatures = $candidatures;
        $this->agent = $agent;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $nombre = $this->candidatures->count();
        $concours = $this->candidatures->first()->concours->nom;
        $centre = $this->candidatures->first()->centreDepot->nom;

        return (new MailMessage)
            ->subject("Dossiers validés - {$nombre} candidature(s)")
            ->greeting("Bonjour Admin,")
            ->line("L'agent du centre de dépôt a envoyé {$nombre} dossier(s) validé(s).")
            ->line("**Concours** : {$concours}")
            ->line("**Centre de dépôt** : {$centre}")
            ->line("**Agent** : {$this->agent->nom_complet}")
            ->line("**Candidatures** :")
            ->line($this->candidatures->map(function ($c) {
                return "- {$c->code_candidat} : {$c->user->nom_complet}";
            })->implode("\n"))
            ->action('Voir les candidatures', url('/admin/candidatures'))
            ->line('Merci !');
    }

    public function toArray($notifiable): array
    {
        return [
            'nombre_dossiers' => $this->candidatures->count(),
            'concours' => $this->candidatures->first()->concours->nom,
            'centre' => $this->candidatures->first()->centreDepot->nom,
            'agent' => $this->agent->nom_complet,
            'message' => 'Dossiers validés reçus du centre de dépôt',
        ];
    }
}
