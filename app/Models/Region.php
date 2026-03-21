<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
    ];

    // Relations
    public function departements()
    {
        return $this->hasMany(Departement::class);
    }

    public function centresDepot()
    {
        return $this->hasMany(CentreDepot::class);
    }

    public function centresExamen()
    {
        return $this->hasMany(CentreExamen::class);
    }

    public function candidaturesOrigine()
    {
        return $this->hasMany(Candidature::class, 'region_origine_id');
    }
}
