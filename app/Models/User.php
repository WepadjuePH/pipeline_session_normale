<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'email_verified_at',
        'password',
        'telephone',
        'role',
        'centre_examen_id',
        'centre_depot_id',
        'google_id',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // JWT Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'centre_examen_id' => $this->centre_examen_id,
            'centre_depot_id' => $this->centre_depot_id,
            'centre_examen_nom' => $this->centreExamen?->nom,
            'centre_depot_nom' => $this->centreDepot?->nom,
        ];
    }

    // Accessors
    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    // Role Checks
    public function isCandidat(): bool
    {
        return $this->role === 'candidat';
    }

    public function isAgentDepot(): bool
    {
        return $this->role === 'agent_depot';
    }

    public function isAgentExamen(): bool
    {
        return $this->role === 'agent_examen';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Relations
    public function centreExamen()
    {
        return $this->belongsTo(CentreExamen::class);
    }

    public function centreDepot()
    {
        return $this->belongsTo(CentreDepot::class);
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class);
    }

    public function concoursCreated()
    {
        return $this->hasMany(Concours::class, 'created_by');
    }

    public function candidaturesValidees()
    {
        return $this->hasMany(Candidature::class, 'valide_par_depot');
    }

    public function candidaturesExamen()
    {
        return $this->hasMany(Candidature::class, 'valide_par_examen');
    }
}
