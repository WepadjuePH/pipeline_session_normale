<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Concours extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'concours';

    protected $fillable = [
        'nom',
        'code',
        'description',
        'type',
        'filiere',
        'cursus',
        'date_ouverture',
        'date_cloture',
        'date_examen',
        'heure_examen',
        'diplomes_requis',
        'age_minimum',
        'age_maximum',
        'frais_inscription',
        'monnaie',
        'nombre_places',
        'inscription_ouverte',
        'is_active',
        'documents_requis',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_ouverture' => 'date',
            'date_cloture' => 'date',
            'date_examen' => 'date',
            'diplomes_requis' => 'array',
            'documents_requis' => 'array',
            'age_minimum' => 'integer',
            'age_maximum' => 'integer',
            'frais_inscription' => 'decimal:2',
            'nombre_places' => 'integer',
            'inscription_ouverte' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Scopes
    public function scopeOuvert(Builder $query): Builder
    {
        return $query->where('inscription_ouverte', true)
            ->where('is_active', true)
            ->where('date_ouverture', '<=', Carbon::now())
            ->where('date_cloture', '>=', Carbon::now());
    }

    public function scopeActif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeProchain(Builder $query): Builder
    {
        return $query->where('date_examen', '>', Carbon::now())
            ->orderBy('date_examen');
    }

    // Relations
    public function candidatures()
    {
        return $this->hasMany(Candidature::class);
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Methods
    public function estOuvert(): bool
    {
        return $this->inscription_ouverte 
            && $this->is_active
            && Carbon::now()->between($this->date_ouverture, $this->date_cloture);
    }

    public function estCloture(): bool
    {
        return !$this->inscription_ouverte || Carbon::now()->greaterThan($this->date_cloture);
    }

    public function getNombreCandidatures(): int
    {
        return $this->candidatures()->count();
    }

    public function getNombrePlacesRestantes(): ?int
    {
        if (!$this->nombre_places) {
            return null;
        }

        return max(0, $this->nombre_places - $this->getNombreCandidatures());
    }

    public function estComplet(): bool
    {
        if (!$this->nombre_places) {
            return false;
        }

        return $this->getNombrePlacesRestantes() === 0;
    }

    public function genererCodeCandidat(): string
    {
        $annee = Carbon::now()->year;
        $dernierNumero = $this->candidatures()
            ->whereYear('created_at', $annee)
            ->count() + 1;

        return sprintf(
            '%s-%s-%d',
            $this->code,
            $annee,
            str_pad((string)$dernierNumero, 6, '0', STR_PAD_LEFT)
        );
    }

    // Accessors
    public function getDateExamenFormatteeAttribute(): string
    {
        return $this->date_examen->format('d/m/Y');
    }

    public function getHeureExamenFormatteeAttribute(): string
    {
        return Carbon::parse($this->heure_examen)->format('H:i');
    }
}
