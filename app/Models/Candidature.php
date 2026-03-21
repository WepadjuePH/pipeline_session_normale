<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Candidature extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'concours_id',
        'code_candidat',
        'centre_depot_id',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'nationalite',
        'region_origine',
        'departement_origine',
        'telephone',
        'adresse',
        'premiere_langue',
        'cni',
        'filiere',
        'cursus',
        'diplome_admission',
        'mention_diplome',
        'annee_diplome',
        'centre_examen_id',
        'nom_pere',
        'telephone_pere',
        'nom_mere',
        'telephone_mere',
        'document_cni',
        'document_diplome',
        'document_acte_naissance',
        'document_recu_paiement',
        'photo_candidat',
        'statut',
        'salle_examen_id',
        'numero_table',
        'qr_code_data',
        'valide_par_depot',
        'valide_depot_at',
        'valide_par_examen',
        'valide_examen_at',
        'motif_rejet',
        'motif_annulation',
        'locked',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'annee_diplome' => 'integer',
            'valide_depot_at' => 'datetime',
            'valide_examen_at' => 'datetime',
            'locked' => 'boolean',
        ];
    }

    // Scopes
    public function scopeEnAttente(Builder $query): Builder
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeValideDepot(Builder $query): Builder
    {
        return $query->where('statut', 'valide_depot');
    }

    public function scopeConvoque(Builder $query): Builder
    {
        return $query->where('statut', 'convoque');
    }

    public function scopePresent(Builder $query): Builder
    {
        return $query->where('statut', 'present');
    }

    public function scopeAbsent(Builder $query): Builder
    {
        return $query->where('statut', 'absent');
    }

    public function scopeRejete(Builder $query): Builder
    {
        return $query->where('statut', 'rejete');
    }

    public function scopeByConcours(Builder $query, int $concoursId): Builder
    {
        return $query->where('concours_id', $concoursId);
    }

    public function scopeByCentreDepot(Builder $query, int $centreId): Builder
    {
        return $query->where('centre_depot_id', $centreId);
    }

    public function scopeByCentreExamen(Builder $query, int $centreId): Builder
    {
        return $query->where('centre_examen_id', $centreId);
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
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



    public function salleExamen()
    {
        return $this->belongsTo(SalleExamen::class);
    }

    public function validateurDepot()
    {
        return $this->belongsTo(User::class, 'valide_par_depot');
    }

    public function validateurExamen()
    {
        return $this->belongsTo(User::class, 'valide_par_examen');
    }

    // Methods - Workflow
    public function peutEtreModifiee(): bool
    {
        return !$this->locked && in_array($this->statut, ['en_attente', 'documents_a_corriger']);
    }

    public function peutEtreValidee(): bool
    {
        return in_array($this->statut, ['en_attente', 'documents_a_corriger']) && $this->documentsComplets();
    }

    public function peutEtreRejetee(): bool
    {
        return in_array($this->statut, ['en_attente', 'valide_depot']);
    }

    public function documentsComplets(): bool
    {
        return !empty($this->document_cni)
            && !empty($this->document_diplome)
            && !empty($this->document_acte_naissance)
            && !empty($this->document_recu_paiement)
            && !empty($this->photo_candidat);
    }

    public function marquerCommePresent(User $agent): void
    {
        $this->update([
            'statut' => 'present',
            'valide_par_examen' => $agent->id,
            'valide_examen_at' => Carbon::now(),
        ]);
    }

    public function marquerCommeAbsent(string $motif = null): void
    {
        $this->update([
            'statut' => 'absent',
            'motif_rejet' => $motif,
        ]);
    }

    public function verrouiller(): void
    {
        $this->update(['locked' => true]);
    }

    public function deverrouiller(): void
    {
        $this->update(['locked' => false]);
    }

    // Génération QR Code
    public function genererQRCode(): string
    {
        $data = [
            'code' => $this->code_candidat,
            'concours_id' => $this->concours_id,
            'user_id' => $this->user_id,
            'timestamp' => Carbon::now()->timestamp,
        ];

        return encrypt(json_encode($data));
    }

    public static function verifierQRCode(string $qrData): ?self
    {
        try {
            // Try to decrypt first (for real QR codes)
            $decrypted = decrypt($qrData);
            $data = json_decode($decrypted, true);

            return self::where('code_candidat', $data['code'])
                ->where('concours_id', $data['concours_id'])
                ->where('user_id', $data['user_id'])
                ->first();
        } catch (\Exception $e) {
            // If decryption fails, try direct code_candidat lookup (for testing)
            return self::where('code_candidat', $qrData)->first();
        }
    }

    // Accessors
    public function getNomCompletCandidatAttribute(): string
    {
        return "{$this->user->prenom} {$this->user->nom}";
    }

    public function getAgeAttribute(): int
    {
        return Carbon::parse($this->date_naissance)->age;
    }

    public function getStatutLibelleAttribute(): string
    {
        return match($this->statut) {
            'en_attente' => 'En attente de validation',
            'documents_a_corriger' => 'Documents à corriger',
            'valide_depot' => 'Validé - Centre de dépôt',
            'convoque' => 'Convoqué',
            'present' => 'Présent à l\'examen',
            'absent' => 'Absent à l\'examen',
            'rejete' => 'Rejeté',
            default => 'Statut inconnu',
        };
    }

    public function getStatutCouleurAttribute(): string
    {
        return match($this->statut) {
            'en_attente' => 'warning',
            'documents_a_corriger' => 'danger',
            'valide_depot' => 'info',
            'convoque' => 'primary',
            'present' => 'success',
            'absent' => 'secondary',
            'rejete' => 'danger',
            default => 'dark',
        };
    }
}
