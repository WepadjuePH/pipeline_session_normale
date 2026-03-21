<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convocation - {{ $candidature->code_candidat }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Calibri', 'Arial', sans-serif;
            background: white;
            color: #000;
            line-height: 1.4;
        }
        
        .page {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 12mm;
            background: white;
        }
        
        /* En-tête */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        
        .header-left, .header-right {
            flex: 1;
            font-size: 8px;
            text-align: center;
            line-height: 1.2;
            font-weight: bold;
        }
        
        .header-center {
            flex: 1;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .logo {
            width: 50px;
            height: 50px;
            margin: 0 auto 3px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 9px;
            border: 2px solid #000;
        }
        
        .institution-name {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 2px;
        }
        
        .institution-subtitle {
            font-size: 8px;
        }
        
        /* Titre principal - VERT pour convocation */
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin: 10px 0;
            border: 2px solid #28a745;
            padding: 6px;
            background: #28a745;
            color: white;
        }
        
        .subtitle {
            text-align: center;
            font-size: 10px;
            color: #28a745;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        /* Numéro d'inscription */
        .inscription-number {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 10px;
        }
        
        .inscription-number-left {
            flex: 1;
        }
        
        .inscription-number-right {
            flex: 1;
            text-align: right;
            border: 1px solid #000;
            padding: 3px;
            font-size: 8px;
        }
        
        /* Section avec photo */
        .photo-section {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .photo-box {
            width: 80px;
            height: 100px;
            border: 2px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            flex-shrink: 0;
            font-size: 9px;
            color: #999;
            overflow: hidden;
        }
        
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #999;
        }
        
        .info-section {
            flex: 1;
        }
        
        /* Sections d'informations */
        .section {
            margin-bottom: 8px;
        }
        
        .section-title {
            background: #000;
            color: white;
            padding: 3px 6px;
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 4px;
        }
        
        .section-title.red {
            background: #cc0000;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 2px;
            font-size: 9px;
        }
        
        .info-label {
            font-weight: bold;
            width: 35%;
            padding-right: 8px;
        }
        
        .info-value {
            width: 65%;
            padding-bottom: 1px;
        }
        
        .info-row-two-col {
            display: flex;
            gap: 15px;
            margin-bottom: 3px;
            font-size: 9px;
        }
        
        .info-row-two-col > div {
            flex: 1;
        }
        
        /* Documents */
        .documents-list {
        }
        
        .documents-list {
            font-size: 8px;
            line-height: 1.3;
            margin-left: 8px;
        }
        
        .documents-list li {
            margin-bottom: 2px;
        }
        
        /* QR Code */
        .qr-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #000;
        }
        
        .qr-box {
            width: 80px;
            height: 80px;
            border: 2px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
        }
        
        .qr-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .qr-info {
            flex: 1;
            margin-left: 10px;
            font-size: 8px;
        }
        
        .qr-info-row {
            margin-bottom: 2px;
        }
        
        .qr-info-label {
            font-weight: bold;
            display: inline-block;
            width: 70px;
        }
        
        /* Footer */
        .footer {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #999;
            font-size: 7px;
            text-align: center;
            color: #666;
        }
        
        /* Impression */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .page {
                box-shadow: none;
                margin: 0;
                padding: 12mm;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- En-tête -->
        <div class="header">
            <div class="header-left">
                <strong>REPUBLIQUE DU CAMEROUN</strong><br>
                Paix - Travail - Patrie<br><br>
                <strong>MINISTÈRE DE L'ENSEIGNEMENT SUPÉRIEUR</strong><br><br>
                <strong>SGECN</strong><br>
                Système de Gestion d'Enrôlement<br>
                aux Concours Nationaux
            </div>
            
            <div class="header-center">
                <div class="logo">SGECN</div>
                <div class="institution-name">SGECN</div>
                <div class="institution-subtitle">Concours Nationaux</div>
            </div>
            
            <div class="header-right">
                <strong>REPUBLIC OF CAMEROON</strong><br>
                Peace - Work - Fatherland<br><br>
                <strong>MINISTRY OF HIGHER EDUCATION</strong><br><br>
                <strong>SGECN</strong><br>
                National Examination<br>
                Enrollment Management System
            </div>
        </div>
        
        <!-- Titre - Convocation Validée (VERT) -->
        <div class="title" style="background: #28a745; color: white; border-color: #28a745;">
            [✓] FICHE DE CANDIDATURE VALIDEE<br>
            REGISTRATION FORM VALIDATED
        </div>
        
        <div class="subtitle" style="color: #28a745;">
            {{ strtoupper($candidature->concours?->nom ?? 'CONCOURS') }} - SESSION {{ $candidature->concours->annee ?? date('Y') }}
        </div>
        
        <!-- Numéro d'inscription -->
        <div class="inscription-number">
            <div class="inscription-number-left">
                INSCRIPTION N° <strong>{{ $candidature->code_candidat }}</strong>
            </div>
            <div class="inscription-number-right">
                Timbre Fiscal ici /<br>
                Stamp here
            </div>
        </div>
        
        <!-- Section Photo et Infos Personnelles -->
        <div class="photo-section">
            <div class="photo-box">
                @if($candidature->photo_candidat)
                    @php
                        $photoPath = storage_path('app/public/' . $candidature->photo_candidat);
                        if (file_exists($photoPath)) {
                            $photoMime = mime_content_type($photoPath);
                            // Vérifier si c'est une image (pas un PDF)
                            if (strpos($photoMime, 'image/') === 0) {
                                $photoData = base64_encode(file_get_contents($photoPath));
                                echo '<img src="data:' . $photoMime . ';base64,' . $photoData . '" alt="Photo du candidat">';
                            } else {
                                // Si c'est un PDF ou autre, afficher placeholder
                                echo '<div class="photo-placeholder" style="font-size: 7px; color: #dc3545;">Photo invalide<br>(PDF détecté)<br>Utilisez JPG/PNG</div>';
                            }
                        } else {
                            echo '<div class="photo-placeholder">Photo<br>4x4</div>';
                        }
                    @endphp
                @else
                    <div class="photo-placeholder">
                        Photo<br>4x4
                    </div>
                @endif
            </div>
            
            <div class="info-section">
                <!-- Informations Personnelles -->
                <div class="section">
                    <div class="section-title">Informations Personnelles / Personal Informations</div>
                    
                    <div class="info-row-two-col">
                        <div>
                            <div class="info-row">
                                <span class="info-label">Nom:</span>
                                <span class="info-value">{{ strtoupper($candidature->user->nom) }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Date naissance:</span>
                                <span class="info-value">{{ $candidature->date_naissance ? $candidature->date_naissance->format('Y-m-d') : 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Nationalité:</span>
                                <span class="info-value">{{ $candidature->nationalite ?? 'Camerounaise' }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="info-row">
                                <span class="info-label">Prénom:</span>
                                <span class="info-value">{{ strtoupper($candidature->user->prenom) }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Lieu naissance:</span>
                                <span class="info-value">{{ $candidature->lieu_naissance }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Sexe:</span>
                                <span class="info-value">{{ ucfirst($candidature->sexe) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">CNI:</span>
                        <span class="info-value">{{ $candidature->cni }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Téléphone:</span>
                        <span class="info-value">{{ $candidature->telephone }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Adresse:</span>
                        <span class="info-value">{{ $candidature->adresse }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ $candidature->user->email }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">1ère Langue:</span>
                        <span class="info-value">{{ $candidature->premiere_langue }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Informations Académiques -->
        <div class="section">
            <div class="section-title">Informations Académique / Academic Informations</div>
            
            <div class="info-row-two-col">
                <div>
                    <div class="info-row">
                        <span class="info-label">Cursus:</span>
                        <span class="info-value">{{ $candidature->concours->cursus ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Filière:</span>
                        <span class="info-value">{{ $candidature->filiere }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Année diplôme:</span>
                        <span class="info-value">{{ $candidature->annee_diplome }}</span>
                    </div>
                </div>
                <div>
                    <div class="info-row">
                        <span class="info-label">Diplôme d'admission:</span>
                        <span class="info-value">{{ $candidature->diplome_admission }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Mention:</span>
                        <span class="info-value">{{ ucfirst($candidature->mention_diplome ?? 'N/A') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="info-row-two-col">
                <div>
                    <div class="info-row">
                        <span class="info-label">Centre d'examen:</span>
                        <span class="info-value">{{ $candidature->centreExamen?->nom ?? 'À déterminer' }}</span>
                    </div>
                </div>
                <div>
                    <div class="info-row">
                        <span class="info-label">Centre de dépôt:</span>
                        <span class="info-value">{{ $candidature->centreDepot?->nom ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Autres Informations -->
        <div class="section">
            <div class="section-title">Autres Informations / Others Informations</div>
            
            <div class="info-row-two-col">
                <div>
                    <div class="info-row">
                        <span class="info-label">Nom du père:</span>
                        <span class="info-value">{{ $candidature->nom_pere ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tél. père:</span>
                        <span class="info-value">{{ $candidature->telephone_pere ?? 'N/A' }}</span>
                    </div>
                </div>
                <div>
                    <div class="info-row">
                        <span class="info-label">Nom de la mère:</span>
                        <span class="info-value">{{ $candidature->nom_mere ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tél. mère:</span>
                        <span class="info-value">{{ $candidature->telephone_mere ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- MESSAGES POUR CONVOCATION -->
        
        <!-- TRÈS IMPORTANT -->
        <div class="section">
            <div class="section-title red">[!] TRES IMPORTANT - A NE PAS OUBLIER / VERY IMPORTANT - DO NOT FORGET</div>
            
            <div style="font-size: 10px; line-height: 1.5; margin: 8px 0;">
                <strong>Présentez-vous 30 minutes avant l'heure du concours. Aucun retard ne sera toléré!</strong><br>
                <strong>Present yourself 30 minutes before the exam time. No delay will be tolerated!</strong>
            </div>
        </div>

        <!-- Documents à Apporter -->
        <div class="section">
            <div class="section-title red">Documents à Apporter le Jour du Concours / Documents to Bring on Exam Day</div>
            
            <div class="documents-list">
                <ul>
                    <li>Cette fiche/convocation imprimée (avec QR code visible) / This printed sheet/summons (with visible QR code)</li>
                    <li>Carte Nationale d'Identité (originale) / National Identity Card (original)</li>
                    <li>Reçu de paiement des frais de concours / Receipt of exam fees payment</li>
                    <li>2 stylos à bille (bleu ou noir) / 2 ballpoint pens (blue or black)</li>
                    <li>Crayon à papier et gomme / Pencil and eraser</li>
                    <li>Calculatrice non programmable (si autorisée) / Non-programmable calculator (if allowed)</li>
                </ul>
            </div>
        </div>

        <!-- STRICTEMENT INTERDIT -->
        <div class="section">
            <div class="section-title red">[X] STRICTEMENT INTERDIT / STRICTLY FORBIDDEN</div>
            
            <div class="documents-list">
                <ul>
                    <li>Téléphones portables et montres connectées / Mobile phones and smart watches</li>
                    <li>Tout appareil électronique (sauf calculatrice autorisée) / Any electronic device (except authorized calculator)</li>
                    <li>Documents, notes, livres / Documents, notes, books</li>
                    <li>Communication avec d'autres candidats / Communication with other candidates</li>
                </ul>
            </div>
        </div>

        <!-- Déroulement de l'Examen -->
        <div class="section">
            <div class="section-title red">Déroulement de l'Examen / Exam Procedure</div>
            
            <div style="font-size: 9px; line-height: 1.4; margin: 8px 0;">
                <strong>Arrivée:</strong> Présentez-vous 30 min avant au centre / Present yourself 30 min before at the center<br>
                <strong>Contrôle:</strong> L'agent scannera votre QR code à l'entrée / The agent will scan your QR code at the entrance<br>
                <strong>Installation:</strong> Dirigez-vous vers votre salle et table / Go to your room and table<br>
                <strong>Vérification:</strong> Présentez votre CNI et convocation / Present your ID and summons<br>
                <strong>Composition:</strong> Suivez les instructions du surveillant / Follow the proctor's instructions
            </div>
        </div>

        <!-- Horaires -->
        <div class="section">
            <div class="section-title red">Horaires / Schedule</div>
            
            <div style="font-size: 10px; line-height: 1.6; margin: 8px 0;">
                <strong>Ouverture des portes:</strong> 07:00<br>
                <strong>Fermeture des portes:</strong> {{ $candidature->concours->heure_examen ?? '08:00' }} (AUCUN RETARD / NO DELAY)<br>
                <strong>Début de l'épreuve:</strong> {{ $candidature->concours->heure_examen ?? '08:00' }}<br>
                <strong>Date de l'examen:</strong> {{ $candidature->concours->date_examen ? \Carbon\Carbon::parse($candidature->concours->date_examen)->format('d/m/Y') : 'À confirmer' }}<br><br>
                <strong style="color: #cc0000;">Bonne chance pour votre concours! / Good luck for your exam!</strong>
            </div>
        </div>
        
        <!-- QR Code et Informations de Sécurité -->
        <div class="qr-section">
            <div class="qr-box">
                @if($qr_code_path && file_exists($qr_code_path))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($qr_code_path)) }}" alt="QR Code">
                @else
                    <div style="text-align: center; color: #999;">QR Code</div>
                @endif
            </div>
            
            <div class="qr-info">
                <div class="qr-info-row">
                    <span class="qr-info-label">Code Candidat:</span>
                    <strong>{{ $candidature->code_candidat }}</strong>
                </div>
                <div class="qr-info-row">
                    <span class="qr-info-label">Salle Examen:</span>
                    {{ $candidature->salleExamen?->nom ?? 'À déterminer' }}
                </div>
                <div class="qr-info-row">
                    <span class="qr-info-label">Numéro Table:</span>
                    {{ $candidature->numero_table ?? 'À déterminer' }}
                </div>
                <div class="qr-info-row">
                    <span class="qr-info-label">Statut:</span>
                    {{ ucfirst(str_replace('_', ' ', $candidature->statut)) }}
                </div>
                <div class="qr-info-row">
                    <span class="qr-info-label">Généré:</span>
                    {{ $candidature->created_at->format('d/m/Y H:i') }}
                </div>
                <div class="qr-info-row" style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #ddd;">
                    <strong style="color: #cc0000;">[!] IMPORTANT:</strong><br>
                    <span style="font-size: 8px;">Ce QR code sera scanné à l'entrée de la salle d'examen. Assurez-vous qu'il est bien visible sur votre convocation imprimée.</span><br>
                    <span style="font-size: 8px;">This QR code will be scanned at the exam room entrance. Make sure it is clearly visible on your printed summons.</span>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p style="font-weight: bold; color: #28a745; font-size: 9px;">
                [✓] Convocation officielle - Présentez-vous 30 minutes avant l'heure du concours avec cette convocation imprimée.<br>
                [✓] Official summons - Present yourself 30 minutes before the exam time with this printed summons.
            </p>
            <p style="margin-top: 10px;">Imprimée le {{ now()->format('d/m/Y à H:i') }} | Document officiel - Official Document | SGECN {{ date('Y') }}</p>
            <p>Cet document est protégé par un code QR anti-fraude / This document is protected by an anti-fraud QR code</p>
        </div>
    </div>
</body>
</html>
