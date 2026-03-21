<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmailVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'verified',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified' => 'boolean',
        ];
    }

    /**
     * Vérifier si le code est expiré
     */
    public function isExpired(): bool
    {
        return Carbon::now()->greaterThan($this->expires_at);
    }

    /**
     * Générer un code aléatoire à 6 chiffres
     */
    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Créer ou mettre à jour la vérification email
     */
    public static function createForEmail(string $email): self
    {
        // Invalider les anciens codes
        self::where('email', $email)->delete();

        return self::create([
            'email' => $email,
            'code' => self::generateCode(),
            'expires_at' => Carbon::now()->addMinutes(config('auth.verification.expire', 30)),
        ]);
    }

    /**
     * Vérifier le code
     */
    public static function verify(string $email, string $code): bool
    {
        $verification = self::where('email', $email)
            ->where('code', $code)
            ->where('verified', false)
            ->first();

        if (!$verification || $verification->isExpired()) {
            return false;
        }

        $verification->update(['verified' => true]);
        return true;
    }
}
