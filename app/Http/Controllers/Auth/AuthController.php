<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerification;
use App\Mail\VerificationCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Inscription d'un nouveau candidat
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'telephone'=>'required|min:9|max:15'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Créer l'utilisateur
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telephone' =>$request->telephone,
            'role' => 'candidat',
        ]);

        // Générer le code de vérification (email dans les logs)
        $verification = EmailVerification::createForEmail($user->email);
        
        try {
            Mail::to($user->email)->send(new VerificationCodeMail($verification->code));
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer l'inscription
            \Log::error('Email verification failed: ' . $e->getMessage());
        }
        
        // Log le code pour le développement
        \Log::info('Code de vérification pour ' . $user->email . ': ' . $verification->code);

        return response()->json([
            'message' => 'Inscription réussie. Un code de vérification a été envoyé à votre email.',
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
            ]
        ], 201);
    }

    /**
     * Vérification de l'email avec code à 6 chiffres
     */
    public function verifyEmail(Request $request)
    {
        \Log::info('Tentative de vérification', [
            'email' => $request->email,
            'code' => $request->code
        ]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation échouée', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verificationResult = EmailVerification::verify($request->email, $request->code);
        \Log::info('Résultat de vérification', ['result' => $verificationResult]);

        if ($verificationResult) {
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                \Log::error('Utilisateur non trouvé', ['email' => $request->email]);
                return response()->json([
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }
            
            \Log::info('Utilisateur trouvé', [
                'user_id' => $user->id,
                'email_verified_at_avant' => $user->email_verified_at
            ]);
            
            // Mettre à jour l'email_verified_at
            $updated = $user->update(['email_verified_at' => now()]);
            
            \Log::info('Mise à jour effectuée', [
                'success' => $updated,
                'email_verified_at_apres' => $user->fresh()->email_verified_at
            ]);

            $token = JWTAuth::fromUser($user);

            \Log::info('Vérification réussie', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Email vérifié avec succès',
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ]);
        }

        \Log::warning('Code invalide ou expiré', [
            'email' => $request->email,
            'code' => $request->code
        ]);

        return response()->json([
            'message' => 'Code de vérification invalide ou expiré'
        ], 400);
    }

    /**
     * Renvoyer le code de vérification
     */
    public function resendVerificationCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Cet email est déjà vérifié'
            ], 400);
        }

        $verification = EmailVerification::createForEmail($user->email);
        
        try {
            Mail::to($user->email)->send(new VerificationCodeMail($verification->code));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'envoi de l\'email'
            ], 500);
        }
        
        // Log le code pour le développement
        \Log::info('Code de vérification renvoyé pour ' . $user->email . ': ' . $verification->code);

        return response()->json([
            'message' => 'Un nouveau code de vérification a été envoyé'
        ]);
    }

    /**
     * Connexion
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Email ou mot de passe incorrect'], 401);
        }

        $user = auth('api')->user();

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Veuillez vérifier votre email avant de vous connecter'
            ], 403);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Votre compte a été désactivé. Contactez l\'administrateur'
            ], 403);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Connexion avec Google
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // Mettre à jour le google_id si nécessaire
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->id,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ]);
                }
            } else {
                // Créer un nouveau compte
                $user = User::create([
                    'nom' => $googleUser->user['family_name'] ?? 'Nom',
                    'prenom' => $googleUser->user['given_name'] ?? 'Prénom',
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'password' => Hash::make(uniqid()),
                    'role' => 'candidat',
                    'email_verified_at' => now(),
                ]);
            }

            $token = JWTAuth::fromUser($user);

            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la connexion avec Google',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mot de passe oublié
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Générer un token de réinitialisation
        $token = \Str::random(64);

        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Envoyer l'email (à implémenter)
        // Mail::to($user->email)->send(new ResetPasswordMail($token));

        return response()->json([
            'message' => 'Un lien de réinitialisation a été envoyé à votre email',
            'reset_token' => $token // À retirer en production
        ]);
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reset = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return response()->json(['message' => 'Token invalide ou expiré'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès']);
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Déconnexion réussie']);
    }

    /**
     * Rafraîchir le token
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Obtenir l'utilisateur connecté
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Formater la réponse avec le token
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'user' => auth('api')->user(),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ]);
    }
}
