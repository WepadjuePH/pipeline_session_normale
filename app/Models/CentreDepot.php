<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CentreDepot extends Model
{
    use HasFactory;

    protected $table = 'centres_depot';

    protected $fillable = [
        'nom',
        'code',
        'region_id',
        'departement_id',
        'ville',
        'adresse',
        'telephone',
        'email',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
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

    // Relations
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class);
    }

    // Accessors
    public function getAdresseCompleteAttribute(): string
    {
        $parts = array_filter([$this->adresse, $this->ville]);
        return implode(', ', $parts);
    }
}
