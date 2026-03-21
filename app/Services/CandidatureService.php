<?php

namespace App\Services;

use App\Models\Candidature;
use App\Models\Concours;
use App\Models\User;
use App\Models\SalleExamen;
use App\Models\AuditLog;
use App\Notifications\CandidatureSubmittedNotification;
use App\Notifications\CandidatureValidatedNotification;
use App\Notifications\CandidatureRejectedNotification;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CandidatureService
{
    /**
     * Soumettre une nouvelle candidature
     */
    public function soumettre(array $data, User $user): Candidature
    {
        return DB::transaction(function () use ($data, $user) {
            $concours = Concours::findOrFail($data['concours_id']);

            // Vérifier que le concours est ouvert
            if (!$concours->estOuvert()) {
                throw new \Exception('Les inscriptions pour ce concours sont fermées');
            }

            // Vérifier que le concours n'est pas complet
            if ($concours->estComplet()) {
                throw new \Exception('Ce concours a atteint sa capacité maximale');
            }

            // Vérifier que l'utilisateur n'a pas déjà postulé
            $existing = Candidature::where('user_id', $user->id)
                ->where('concours_id', $concours->id)
                ->first();

            if ($existing) {
                throw new \Exception('Vous avez déjà postulé à ce concours');
            }

            // Générer le code candidat unique
            $codeCandidat = $concours->genererCodeCandidat();

            // Uploader les documents
            $documents = $this->uploadDocuments($data);

            // Créer la candidature
            $candidature = Candidature::create([
                'user_id' => $user->id,
                'concours_id' => $concours->id,
                'code_candidat' => $codeCandidat,
                'centre_depot_id' => $data['centre_depot_id'],
                'date_naissance' => $data['date_naissance'],
                'lieu_naissance' => $data['lieu_naissance'],
                'sexe' => $data['sexe'],
                'nationalite' => $data['nationalite'] ?? 'Camerounaise',
                'region_origine' => $data['region_origine'],
                'departement_origine' => $data['departement_origine'],
                'telephone' => $data['telephone'],
                'adresse' => $data['adresse'],
                'premiere_langue' => $data['premiere_langue'],
                'cni' => $data['cni'],
                'filiere' => $data['filiere'],
                'diplome_admission' => $data['diplome_admission'],
                'mention_diplome' => $data['mention_diplome'] ?? null,
                'annee_diplome' => $data['annee_diplome'],
                'centre_examen_id' => $data['centre_examen_id'] ?? null,
                'nom_pere' => $data['nom_pere'] ?? null,
                'telephone_pere' => $data['telephone_pere'] ?? null,
                'nom_mere' => $data['nom_mere'] ?? null,
                'telephone_mere' => $data['telephone_mere'] ?? null,
                ...$documents,
                'statut' => 'en_attente',
            ]);

            // Générer la fiche provisoire (sans salle/table)
            $this->genererFicheProvisoire($candidature);

            // Envoyer notification (sans fiche - juste email de vérification)
            $user->notify(new CandidatureSubmittedNotification($candidature));

            // Log
            AuditLog::log('create', $candidature, $user);

            return $candidature->load(['concours', 'centreDepot', 'user']);
        });
    }

    /**
     * Uploader les documents
     */
    private function uploadDocuments(array $data): array
    {
        $documents = [];
        $types = ['document_cni', 'document_diplome', 'document_acte_naissance', 'document_recu_paiement', 'photo_candidat'];

        foreach ($types as $type) {
            if (isset($data[$type])) {
                $file = $data[$type];
                $path = $file->store('documents/' . $type, 'public');
                $documents[$type] = $path;
            }
        }

        return $documents;
    }

    /**
     * Générer la fiche provisoire (après soumission)
     */
    public function genererFicheProvisoire(Candidature $candidature): string
    {
        // Générer le QR Code avec données anti-fraude
        $qrData = $this->genererDonneesQRCode($candidature);
        $qrCodePath = $this->genererQRCodeImage($qrData, $candidature->code_candidat);

        $data = [
            'candidature' => $candidature,
            'qr_code_path' => $qrCodePath,
            'type' => 'provisoire',
        ];

        $pdf = PDF::loadView('fiches.provisoire', $data);
        $filename = "fiche_provisoire_{$candidature->code_candidat}.pdf";
        $path = "fiches/{$filename}";

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Générer les données du QR Code avec anti-fraude
     */
    private function genererDonneesQRCode(Candidature $candidature): string
    {
        $data = [
            'code_candidat' => $candidature->code_candidat,
            'candidature_id' => $candidature->id,
            'user_id' => $candidature->user_id,
            'concours_id' => $candidature->concours_id,
            'timestamp' => now()->timestamp,
            'hash' => hash('sha256', $candidature->code_candidat . $candidature->id . config('app.key')),
            'version' => '1.0',
        ];

        return json_encode($data);
    }

    /**
     * Générer l'image du QR Code
     */
    private function genererQRCodeImage(string $data, string $code): string
    {
        $qrCode = new QrCode($data);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $qrCodePath = "qrcodes/{$code}.png";
        Storage::disk('public')->put($qrCodePath, $result->getString());

        return Storage::disk('public')->path($qrCodePath);
    }

    /**
     * Valider une candidature (par agent centre de dépôt)
     */
    public function valider(Candidature $candidature, array $data, User $agent): Candidature
    {
        return DB::transaction(function () use ($candidature, $data, $agent) {
            if (!$candidature->peutEtreValidee()) {
                // Vérifier pourquoi la validation échoue
                if (!in_array($candidature->statut, ['en_attente', 'documents_a_corriger'])) {
                    throw new \Exception("Cette candidature ne peut pas être validée. Statut actuel: {$candidature->statut}");
                }
                
                if (!$candidature->documentsComplets()) {
                    $documentsManquants = [];
                    if (empty($candidature->document_cni)) $documentsManquants[] = 'CNI';
                    if (empty($candidature->document_diplome)) $documentsManquants[] = 'Diplôme';
                    if (empty($candidature->document_acte_naissance)) $documentsManquants[] = 'Acte de naissance';
                    if (empty($candidature->document_recu_paiement)) $documentsManquants[] = 'Reçu de paiement';
                    if (empty($candidature->photo_candidat)) $documentsManquants[] = 'Photo';
                    
                    throw new \Exception('Documents manquants: ' . implode(', ', $documentsManquants));
                }
                
                throw new \Exception('Cette candidature ne peut pas être validée');
            }

            // Assigner une salle et un numéro de table
            $salle = SalleExamen::findOrFail($data['salle_examen_id']);

            if ($salle->estComplete()) {
                throw new \Exception('Cette salle est complète');
            }

            $numeroTable = $salle->getProchainNumeroTable();

            // Générer QR Code
            $qrCodeData = $candidature->genererQRCode();

            $candidature->update([
                'statut' => 'valide_depot',
                'salle_examen_id' => $salle->id,
                'numero_table' => $numeroTable,
                'centre_examen_id' => $salle->centreExamen->id,
                'qr_code_data' => $qrCodeData,
                'valide_par_depot' => $agent->id,
                'valide_depot_at' => Carbon::now(),
            ]);

            // Générer la convocation officielle (Fiche 2 avec QR Code)
            $this->genererConvocation($candidature);

            // Verrouiller la candidature
            $candidature->verrouiller();

            // Envoyer notification avec fiche en pièce jointe
            $candidature->user->notify(new CandidatureValidatedNotification($candidature));

            // Log
            AuditLog::log('validate', $candidature, $agent);

            return $candidature->fresh();
        });
    }

    /**
     * Rejeter une candidature
     */
    public function rejeter(Candidature $candidature, string $motif, User $agent): Candidature
    {
        return DB::transaction(function () use ($candidature, $motif, $agent) {
            if (!$candidature->peutEtreRejetee()) {
                throw new \Exception('Cette candidature ne peut pas être rejetée');
            }

            $candidature->update([
                'statut' => 'documents_a_corriger',
                'motif_rejet' => $motif,
            ]);

            // Déverrouiller la candidature pour permettre les corrections
            $candidature->deverrouiller();

            // Envoyer notification
            $candidature->user->notify(new CandidatureRejectedNotification($candidature, $motif));

            // Log
            AuditLog::log('reject', $candidature, $agent);

            return $candidature->fresh();
        });
    }

    /**
     * Générer la convocation officielle avec QR Code
     */
    public function genererConvocation(Candidature $candidature): string
    {
        // Générer le QR Code avec données anti-fraude
        $qrData = $this->genererDonneesQRCode($candidature);
        $qrCodePath = $this->genererQRCodeImage($qrData, $candidature->code_candidat);

        $data = [
            'candidature' => $candidature,
            'qr_code_path' => $qrCodePath,
            'type' => 'convocation',
        ];

        $pdf = PDF::loadView('fiches.convocation', $data);
        $filename = "convocation_{$candidature->code_candidat}.pdf";
        $path = "fiches/{$filename}";

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Générer la fiche validée (après validation centre dépôt)
     * Utilise la même vue que la convocation
     */
    public function genererFicheValidee(Candidature $candidature): string
    {
        // Générer le QR Code avec données anti-fraude
        $qrData = $this->genererDonneesQRCode($candidature);
        $qrCodePath = $this->genererQRCodeImage($qrData, $candidature->code_candidat);

        $data = [
            'candidature' => $candidature,
            'qr_code_path' => $qrCodePath,
            'type' => 'validee',
        ];

        $pdf = PDF::loadView('fiches.convocation', $data);
        $filename = "fiche_validee_{$candidature->code_candidat}.pdf";
        $path = "fiches/{$filename}";

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Marquer présent à l'examen
     */
    public function marquerPresent(Candidature $candidature, User $agent): Candidature
    {
        return DB::transaction(function () use ($candidature, $agent) {
            $candidature->marquerCommePresent($agent);

            // Log
            AuditLog::log('mark_present', $candidature, $agent);

            return $candidature->fresh();
        });
    }

    /**
     * Marquer absent à l'examen
     */
    public function marquerAbsent(Candidature $candidature, ?string $motif = null, string $statut = 'absent'): Candidature
    {
        return DB::transaction(function () use ($candidature, $motif, $statut) {
            $candidature->update([
                'statut' => $statut,
                'motif_rejet' => $motif ?? ($statut === 'fraude' ? 'Faux documents détectés' : 'Non présenté à l\'examen'),
            ]);

            // Log
            AuditLog::log('mark_' . $statut, $candidature);

            return $candidature->fresh();
        });
    }

    /**
     * Vérifier un QR Code
     */
    public function verifierQRCode(string $qrData): ?Candidature
    {
        return Candidature::verifierQRCode($qrData);
    }
}
