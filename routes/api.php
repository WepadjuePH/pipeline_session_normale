<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\ConcoursController;
use App\Http\Controllers\Api\CandidatureController;
use App\Http\Controllers\Api\AgentDepotController;
use App\Http\Controllers\Api\AgentExamenController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes publiques (sans authentification)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-code', [AuthController::class, 'resendVerificationCode']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Google OAuth
    Route::get('/google', [AuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

// Concours publics (sans auth)
Route::get('/concours/ouverts', [ConcoursController::class, 'ouverts']);
Route::get('/concours/{id}', [ConcoursController::class, 'show']);

// Routes publiques temporaires pour tester les fiches (À SUPPRIMER EN PRODUCTION)
Route::get('/candidatures/{id}/fiche-provisoire', [CandidatureController::class, 'telechargerFichePublic']);
Route::get('/candidatures/{id}/convocation', [CandidatureController::class, 'telechargerFichePublic']);

// Référentiels publics
Route::get('/regions', [AdminController::class, 'getRegions']);
Route::get('/regions/{id}/departements', [AdminController::class, 'getDepartements']);
Route::get('/centres-depot', [AdminController::class, 'getCentresDepot']);
Route::get('/centres-examen', [AdminController::class, 'getCentresExamen']);

// Routes protégées (authentification requise)
Route::middleware(['auth:api'])->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Rapports (generic endpoint for all agents)
    Route::get('/rapports', [\App\Http\Controllers\Api\RapportController::class, 'index']);
    Route::post('/rapports', [\App\Http\Controllers\Api\RapportController::class, 'store']);
    Route::put('/rapports/{id}', [\App\Http\Controllers\Api\RapportController::class, 'update']);
    Route::delete('/rapports/{id}', [\App\Http\Controllers\Api\RapportController::class, 'destroy']);

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    });

    // Routes CONCOURS (generic endpoint)
    Route::get('/concours', [ConcoursController::class, 'index']);
    Route::get('/concours/{id}', [ConcoursController::class, 'show']);

    // Routes CANDIDAT
    Route::middleware(['role:candidat'])->prefix('candidat')->group(function () {
        Route::get('/concours', [ConcoursController::class, 'index']);

        // Candidatures
        Route::prefix('candidatures')->group(function () {
            Route::get('/', [CandidatureController::class, 'mesCandidatures']);
            Route::post('/', [CandidatureController::class, 'soumettre']);
            Route::get('/{id}', [CandidatureController::class, 'show']);
            Route::put('/{id}', [CandidatureController::class, 'update']);
            Route::get('/{id}/fiche', [CandidatureController::class, 'telechargerFiche']);
            Route::get('/{id}/fiche-provisoire', [CandidatureController::class, 'telechargerFiche']);
            Route::get('/{id}/convocation', [CandidatureController::class, 'telechargerFiche']);
            Route::get('/{id}/qrcode', [CandidatureController::class, 'telechargerQRCode']);
        });
    });

    // Routes AGENT CENTRE DE DÉPÔT
    Route::middleware(['role:agent_depot'])->prefix('agent/depot')->group(function () {
        Route::get('/candidatures', [AgentDepotController::class, 'index']);
        Route::get('/candidatures/{id}', [AgentDepotController::class, 'show']);
        Route::post('/candidatures/{id}/valider', [AgentDepotController::class, 'valider']);
        Route::post('/candidatures/{id}/rejeter', [AgentDepotController::class, 'rejeter']);
        Route::post('/candidatures/{id}/annuler-validation', [AgentDepotController::class, 'annulerValidation']);
        Route::post('/envoyer-dossiers-admin', [AgentDepotController::class, 'envoyerDossiersAdmin']);
        Route::get('/candidatures/{id}/documents/{type}', [AgentDepotController::class, 'voirDocument']);
        Route::get('/statistiques', [AgentDepotController::class, 'statistiques']);
        Route::get('/export-liste', [AgentDepotController::class, 'exporterListe']);

        // Rapports
        Route::get('/rapports', [\App\Http\Controllers\Api\RapportController::class, 'index']);
        Route::post('/rapports/generer', [\App\Http\Controllers\Api\RapportController::class, 'genererRapportDepot']);
        Route::post('/rapports/{id}/envoyer', [\App\Http\Controllers\Api\RapportController::class, 'envoyerRapport']);
        Route::get('/rapports/{id}/telecharger', [\App\Http\Controllers\Api\RapportController::class, 'telechargerRapport']);
        Route::delete('/rapports/{id}', [\App\Http\Controllers\Api\RapportController::class, 'supprimerRapport']);
    });

    // Routes AGENT CENTRE D'EXAMEN
    Route::middleware(['role:agent_examen'])->prefix('agent/examen')->group(function () {
        Route::get('/candidatures', [AgentExamenController::class, 'index']);
        Route::get('/candidatures/{id}', [AgentExamenController::class, 'show']);
        Route::post('/scan-qr', [AgentExamenController::class, 'scanQRCode']);
        Route::post('/candidatures/{id}/marquer-present', [AgentExamenController::class, 'marquerPresent']);
        Route::post('/candidatures/{id}/marquer-absent', [AgentExamenController::class, 'marquerAbsent']);
        Route::post('/envoyer-rapport-admin', [AgentExamenController::class, 'envoyerRapportAdmin']);
        Route::get('/statistiques', [AgentExamenController::class, 'statistiques']);
        Route::get('/export-feuille-presence', [AgentExamenController::class, 'exporterFeuillePresence']);
    });

    // Routes ADMIN
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {

        // Gestion des concours
        Route::get('/concours', [AdminController::class, 'getConcours']);
        Route::post('/concours', [AdminController::class, 'storeConcours']);
        Route::put('/concours/{id}', [AdminController::class, 'updateConcours']);
        Route::delete('/concours/{id}', [AdminController::class, 'deleteConcours']);
        Route::post('/concours/{id}/ouvrir', [AdminController::class, 'ouvrirConcours']);
        Route::post('/concours/{id}/fermer', [AdminController::class, 'fermerConcours']);

        // Gestion des utilisateurs
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::post('/users', [AdminController::class, 'storeUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::post('/users/{id}/toggle-active', [AdminController::class, 'toggleUserActive']);

        // Centres
        Route::post('/centres-depot', [AdminController::class, 'storeCentreDepot']);
        Route::post('/centres-examen', [AdminController::class, 'storeCentreExamen']);

        // Export
        Route::get('/concours/{id}/export', [AdminController::class, 'exporterCandidatures']);

        // Gestion des candidatures
        Route::get('/candidatures', [AdminController::class, 'getCandidatures']);
        Route::post('/candidatures/{id}/valider', [AdminController::class, 'validerCandidature']);
        Route::post('/candidatures/{id}/rejeter', [AdminController::class, 'rejeterCandidature']);
        Route::post('/envoyer-liste-agent-examen', [AdminController::class, 'envoyerListeAgentExamen']);

        // Statistiques et rapports
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/statistiques/globales', [AdminController::class, 'statistiquesGlobales']);
        Route::get('/statistiques/par-concours/{id}', [AdminController::class, 'statistiquesParConcours']);
        Route::get('/audit-logs', [AdminController::class, 'auditLogs']);

        // Rapports reçus
        Route::get('/rapports', [\App\Http\Controllers\Api\RapportController::class, 'rapportsRecus']);
        Route::get('/rapports/{id}', [\App\Http\Controllers\Api\RapportController::class, 'show']);
        Route::get('/rapports/{id}/telecharger', [\App\Http\Controllers\Api\RapportController::class, 'telechargerRapport']);
    });
});
