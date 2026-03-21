<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Fiche Provisoire - {{ $candidature->code_candidat }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 11pt;
            line-height: 1.4;
        }
        .page { 
            width: 210mm; 
            min-height: 297mm;
            padding: 15mm;
        }
        .header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .header h1 { 
            font-size: 20pt; 
            margin-bottom: 5px;
            font-weight: bold;
        }
        .header p { 
            font-size: 10pt; 
            margin: 3px 0;
        }
        .code-box {
            background: #FFF9C4;
            border: 3px solid #FBC02D;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
            border-radius: 5px;
        }
        .code-box h2 {
            color: #F57C00;
            font-size: 14pt;
            margin-bottom: 10px;
        }
        .code-box .code {
            font-size: 24pt;
            font-weight: bold;
            color: #E65100;
            letter-spacing: 2px;
        }
        .section {
            margin: 15px 0;
        }
        .section-title {
            background: #E3F2FD;
            color: #1976D2;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 10px;
            border-left: 4px solid #1976D2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #E0E0E0;
        }
        th {
            background-color: #F5F5F5;
            font-weight: bold;
            width: 40%;
        }
        .message-box {
            background: #E8F5E9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .message-box strong {
            color: #2E7D32;
            display: block;
            margin-bottom: 8px;
            font-size: 12pt;
        }
        .message-box p {
            margin: 5px 0;
            line-height: 1.6;
        }
        .message-box ul {
            margin-left: 20px;
            margin-top: 10px;
        }
        .message-box li {
            margin: 5px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #E0E0E0;
            text-align: center;
            font-size: 9pt;
            color: #757575;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <h1>✅ FICHE PROVISOIRE DE CANDIDATURE</h1>
            <p>Candidature soumise avec succès</p>
            <p>République du Cameroun - Paix - Travail - Patrie</p>
        </div>

        <!-- Code candidat -->
        <div class="code-box">
            <h2>Code Candidat :</h2>
            <div class="code">{{ $candidature->code_candidat }}</div>
        </div>

        <!-- Informations personnelles -->
        <div class="section">
            <div class="section-title">Informations Personnelles</div>
            <table>
                <tr>
                    <th>Nom</th>
                    <td>{{ $candidature->user->nom }}</td>
                </tr>
                <tr>
                    <th>Prénom</th>
                    <td>{{ $candidature->user->prenom }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $candidature->user->email }}</td>
                </tr>
                <tr>
                    <th>Téléphone</th>
                    <td>{{ $candidature->telephone }}</td>
                </tr>
                <tr>
                    <th>Date de naissance</th>
                    <td>{{ $candidature->date_naissance->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Lieu de naissance</th>
                    <td>{{ $candidature->lieu_naissance }}</td>
                </tr>
                <tr>
                    <th>CNI</th>
                    <td>{{ $candidature->cni }}</td>
                </tr>
                <tr>
                    <th>Région d'origine</th>
                    <td>{{ $candidature->region_origine }}</td>
                </tr>
                <tr>
                    <th>Département d'origine</th>
                    <td>{{ $candidature->departement_origine }}</td>
                </tr>
            </table>
        </div>

        <!-- Informations académiques -->
        <div class="section">
            <div class="section-title">Informations Académiques</div>
            <table>
                <tr>
                    <th>Concours</th>
                    <td>{{ $candidature->concours->nom }}</td>
                </tr>
                <tr>
                    <th>Filière</th>
                    <td>{{ $candidature->filiere }}</td>
                </tr>
                @if($candidature->cursus)
                <tr>
                    <th>Cursus</th>
                    <td>{{ $candidature->cursus }}</td>
                </tr>
                @endif
                <tr>
                    <th>Diplôme d'admission</th>
                    <td>{{ $candidature->diplome_admission }}</td>
                </tr>
                <tr>
                    <th>Année diplôme</th>
                    <td>{{ $candidature->annee_diplome }}</td>
                </tr>
            </table>
        </div>

        <!-- Centre de dépôt -->
        <div class="section">
            <div class="section-title">Centre de Dépôt</div>
            <table>
                <tr>
                    <th>Centre</th>
                    <td>{{ $candidature->centreDepot->nom }}</td>
                </tr>
                <tr>
                    <th>Code</th>
                    <td>{{ $candidature->centreDepot->code }}</td>
                </tr>
                <tr>
                    <th>Ville</th>
                    <td>{{ $candidature->centreDepot->ville }}</td>
                </tr>
            </table>
        </div>

        <!-- Message important -->
        <div class="message-box">
            <strong>⚠️ ÉTAPE SUIVANTE - À NE PAS OUBLIER :</strong>
            <p><strong>Veuillez passer au centre de dépôt pour déposer vos documents physiques avec cette fiche imprimée.</strong></p>
            <p><strong>Centre de dépôt :</strong> {{ $candidature->centreDepot->nom }}</p>
            <p><strong>Code candidat :</strong> {{ $candidature->code_candidat }}</p>
            <p><strong>Documents à apporter :</strong></p>
            <ul>
                <li>Cette fiche imprimée</li>
                <li>CNI original</li>
                <li>Diplôme d'admission</li>
                <li>Acte de naissance</li>
                <li>Reçu de paiement</li>
                <li>Photo d'identité</li>
            </ul>
            <p><strong>Important :</strong> Vous ne pourrez pas composer à l'examen sans cette validation au centre de dépôt.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Pour toute question : support@sgecn.cm</strong></p>
            <p>Imprimé le {{ now()->format('d/m/Y à H:i') }}</p>
            <p>République du Cameroun - SGECN</p>
        </div>
    </div>
</body>
</html>
