<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SalleExamen extends Model
{
    use HasFactory;

    protected $table = 'salles_examen';

    protected $fillable = [
        'centre_examen_id',
        'nom',
        'capacite',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacite' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // Relations
    public function centreExamen()
    {
        return $this->belongsTo(CentreExamen::class);
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class);
    }

    // Methods
    public function getCapaciteRestante(): int
    {
        $occupees = $this->candidatures()
            ->whereIn('statut', ['valide_depot', 'convoque', 'present'])
            ->count();

        return max(0, $this->capacite - $occupees);
    }

    public function estComplete(): bool
    {
        return $this->getCapaciteRestante() === 0;
    }

    public function getProchainNumeroTable(): string
    {
        // 🔒 Verrouiller pour éviter les doublons (race condition)
        $dernierNumero = $this->candidatures()
            ->whereNotNull('numero_table')
            ->lockForUpdate()
            ->max('numero_table');

        if (!$dernierNumero) {
            return '001'; // Premier candidat dans cette salle
        }

        $prochain = (int)$dernierNumero + 1;
        
        // Vérifier qu'on ne dépasse pas la capacité
        if ($prochain > $this->capacite) {
            throw new \Exception("La salle {$this->nom} est complète (capacité: {$this->capacite})");
        }

        return str_pad((string)$prochain, 3, '0', STR_PAD_LEFT);
    }
}
