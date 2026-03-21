<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de Vérification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 40px 30px;
        }
        .code-box {
            background: #f0f8ff;
            border: 2px dashed #4CAF50;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #4CAF50;
        }
        .message {
            color: #555;
            line-height: 1.6;
            font-size: 15px;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #777;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 SGECN - Vérification de votre email</h1>
        </div>
        
        <div class="content">
            <p class="message">Bonjour,</p>
            
            <p class="message">
                Merci de vous être inscrit sur le Système de Gestion d'Enrôlement aux Concours Nationaux (SGECN).
            </p>
            
            <p class="message">
                Pour finaliser votre inscription, veuillez utiliser le code de vérification ci-dessous :
            </p>
            
            <div class="code-box">
                <div class="code">{{ $code }}</div>
            </div>
            
            <p class="message">
                Ce code est valide pendant <strong>30 minutes</strong>.
            </p>
            
            <div class="warning">
                <strong>⚠️ Important :</strong> Si vous n'avez pas demandé cette vérification, veuillez ignorer cet email.
            </div>
            
            <p class="message">
                Après validation de votre email, vous pourrez accéder à toutes les fonctionnalités de la plateforme et soumettre vos candidatures aux concours.
            </p>
            
            <p class="message">
                Cordialement,<br>
                <strong>L'équipe SGECN</strong>
            </p>
        </div>
        
        <div class="footer">
            <p>Système de Gestion d'Enrôlement aux Concours Nationaux</p>
            <p>République du Cameroun - Paix - Travail - Patrie</p>
            <p>Pour toute question : support@sgecn.cm</p>
        </div>
    </div>
</body>
</html>
