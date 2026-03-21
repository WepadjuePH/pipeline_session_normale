<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'nom',
        'code',
    ];

    // Relations
    public function region()
    {
        return $this->belongsTo(Region::class);
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
        return $this->hasMany(Candidature::class, 'departement_origine_id');
    }
}
