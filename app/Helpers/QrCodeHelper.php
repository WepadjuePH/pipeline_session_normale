<?php

namespace App\Helpers;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeHelper
{
    public static function generate($candidature): string
    {
        $qrData = json_encode([
            'code_candidat' => $candidature->code_candidat,
            'candidature_id' => $candidature->id,
            'user_id' => $candidature->user_id,
            'concours_id' => $candidature->concours_id,
            'timestamp' => now()->timestamp,
        ]);
        
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($qrData)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();
        
        $qrCodePath = storage_path('app/public/qrcodes/' . $candidature->code_candidat . '.png');
        
        // Créer le dossier si nécessaire
        if (!file_exists(dirname($qrCodePath))) {
            mkdir(dirname($qrCodePath), 0755, true);
        }
        
        file_put_contents($qrCodePath, $result->getString());
        
        return $qrCodePath;
    }
}