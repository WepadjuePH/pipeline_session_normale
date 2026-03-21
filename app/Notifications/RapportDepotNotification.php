<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RapportDepotNotification extends Notification
{
    use Queueable;

    protected $rapport;
    protected $agent;

    public function __construct($rapport, $agent)
    {
        $this->rapport = $rapport;
        $this->agent = $agent;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $stats = $this->rapport->statistiques;
        
        return (new MailMessage)
            ->subject('📊 Nouveau Rapport d\'Activité - Agent Dépôt')
            ->greeting('Bonjour ' . $notifiable->prenom . ',')
            ->line('Un nouveau rapport d\'activité a été envoyé par un agent de dépôt.')
            ->line('**Agent:** ' . $this->agent->nom_complet)
            ->line('**Type:** ' . ucfirst($this->rapport->titre))
            ->line('**Période:** ' . $this->rapport->periode_debut->format('d/m/Y') . ' - ' . $this->rapport->periode_fin->format('d/m/Y'))
            ->line('')
            ->line('**Statistiques:**')
            ->line('- Total traitées: ' . ($stats['total_traitees'] ?? 0))
            ->line('- Validées: ' . ($stats['validees'] ?? 0))
            ->line('- Rejetées: ' . ($stats['rejetees'] ?? 0))
            ->line('- En attente: ' . ($stats['en_attente'] ?? 0))
            ->line('- Temps moyen: ' . ($stats['temps_moyen_traitement'] ?? 'N/A'))
            ->action('Consulter le rapport', url('/admin/rapports'))
            ->line('Merci d\'utiliser le SGECN!');
    }

    public function toArray($notifiable)
    {
        $stats = $this->rapport->statistiques;
        
        return [
            'type' => 'rapport_depot',
            'titre' => '📊 Nouveau Rapport d\'Activité',
            'message' => "Rapport envoyé par {$this->agent->nom_complet}",
            'rapport_id' => $this->rapport->id,
            'agent_nom' => $this->agent->nom_complet,
            'agent_email' => $this->agent->email,
            'periode' => $this->rapport->periode_debut->format('d/m/Y') . ' - ' . $this->rapport->periode_fin->format('d/m/Y'),
            'statistiques' => [
                'total_traitees' => $stats['total_traitees'] ?? 0,
                'validees' => $stats['validees'] ?? 0,
                'rejetees' => $stats['rejetees'] ?? 0,
                'en_attente' => $stats['en_attente'] ?? 0,
            ],
            'action_url' => '/admin/rapports',
            'action_text' => 'Consulter le rapport',
            'created_at' => now()->toISOString(),
        ];
    }
}
