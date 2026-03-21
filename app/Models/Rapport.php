<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'agent_id',
        'concours_id',
        'centre_depot_id',
        'centre_examen_id',
        'titre',
        'description',
        'periode_debut',
        'periode_fin',
        'statistiques',
        'fichier_path',
        'envoye_admin',
        'envoye_admin_at',
    ];

    protected $casts = [
        'statistiques' => 'array',
        'periode_debut' => 'date',
        'periode_fin' => 'date',
        'envoye_admin' => 'boolean',
        'envoye_admin_at' => 'datetime',
    ];

    // Relations
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function concours()
    {
        return $this->belongsTo(Concours::class);
    }

    public function centreDepot()
    {
        return $this->belongsTo(CentreDepot::class);
    }

    public function centreExamen()
    {
        return $this->belongsTo(CentreExamen::class);
    }

    // Scopes
    public function scopeDepot($query)
    {
        return $query->where('type', 'depot');
    }

    public function scopeExamen($query)
    {
        return $query->where('type', 'examen');
    }

    public function scopeEnvoye($query)
    {
        return $query->where('envoye_admin', true);
    }

    public function scopeNonEnvoye($query)
    {
        return $query->where('envoye_admin', false);
    }
}
