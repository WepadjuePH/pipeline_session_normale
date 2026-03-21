<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CentreExamen extends Model
{
    use HasFactory;

    protected $table = 'centres_examen';

    protected $fillable = [
        'nom',
        'code',
        'region_id',
        'departement_id',
        'ville',
        'adresse',
        'capacite',
        'telephone',
        'email',
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

    public function scopeByRegion(Builder $query, int $regionId): Builder
    {
        return $query->where('region_id', $regionId);
    }

    public function scopeAvecCapacite(Builder $query): Builder
    {
        return $query->where('capacite', '>', 0);
    }

    // Relations
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function salles()
    {
        return $this->hasMany(SalleExamen::class);
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class);
    }

    public function agents()
    {
        return $this->hasMany(User::class, 'centre_examen_id');
    }

    // Methods
    public function getCapaciteRestante(): int
    {
        $occupees = $this->candidatures()
            ->whereIn('statut', ['valide_depot', 'convoque', 'present'])
            ->count();

        return max(0, $this->capacite - $occupees);
    }

    public function estComplet(): bool
    {
        return $this->getCapaciteRestante() === 0;
    }

    // Accessors
    public function getAdresseCompleteAttribute(): string
    {
        $parts = array_filter([$this->adresse, $this->ville]);
        return implode(', ', $parts);
    }
}
