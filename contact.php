<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
$to = 'ldvk@me.com'; // Email de réception (pour tests)
$from = 'noreply@baroof.fr'; // Email expéditeur
$subject_prefix = '[Bar\'OOF Contact] ';

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer les données JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validation des champs requis
$required_fields = ['name', 'email', 'eventType', 'guests', 'date'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Le champ $field est requis"]);
        exit;
    }
}

// Validation email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email invalide']);
    exit;
}

// Nettoyer les données
$name = htmlspecialchars(strip_tags($data['name']));
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$phone = !empty($data['phone']) ? htmlspecialchars(strip_tags($data['phone'])) : 'Non renseigné';
$eventType = htmlspecialchars(strip_tags($data['eventType']));
$guests = (int)$data['guests'];
$date = htmlspecialchars(strip_tags($data['date']));
$formule = !empty($data['formule']) ? htmlspecialchars(strip_tags($data['formule'])) : 'Non spécifiée';
$message = !empty($data['message']) ? htmlspecialchars(strip_tags($data['message'])) : 'Aucun message';

// Sujet de l'email
$subject = $subject_prefix . "$eventType - $name";

// Corps de l'email (HTML)
$html_body = "
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #D6AC7A 0%, #B89968 100%);
            color: white;
            padding: 30px;
            border-radius: 12px 12px 0 0;
            text-align: center;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 12px 12px;
        }
        .info-row {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .label {
            font-weight: 600;
            color: #D6AC7A;
            margin-bottom: 5px;
        }
        .value {
            color: #333;
        }
        .message-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #D6AC7A;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class='header'>
        <h1 style='margin: 0; font-size: 24px;'>🍹 Nouvelle demande Bar'OOF</h1>
    </div>
    <div class='content'>
        <div class='info-row'>
            <div class='label'>Nom</div>
            <div class='value'>$name</div>
        </div>

        <div class='info-row'>
            <div class='label'>Email</div>
            <div class='value'><a href='mailto:$email'>$email</a></div>
        </div>

        <div class='info-row'>
            <div class='label'>Téléphone</div>
            <div class='value'>$phone</div>
        </div>

        <div class='info-row'>
            <div class='label'>Type d'événement</div>
            <div class='value'>$eventType</div>
        </div>

        <div class='info-row'>
            <div class='label'>Nombre d'invités</div>
            <div class='value'>$guests personnes</div>
        </div>

        <div class='info-row'>
            <div class='label'>Date souhaitée</div>
            <div class='value'>$date</div>
        </div>

        <div class='info-row'>
            <div class='label'>Formule</div>
            <div class='value'>$formule</div>
        </div>

        <div class='message-box'>
            <div class='label'>Message</div>
            <div class='value'>" . nl2br($message) . "</div>
        </div>
    </div>

    <div class='footer'>
        <p>Email reçu via le formulaire de contact Bar'OOF</p>
    </div>
</body>
</html>
";

// Version texte brut (fallback)
$text_body = "Nouvelle demande Bar'OOF\n\n";
$text_body .= "Nom: $name\n";
$text_body .= "Email: $email\n";
$text_body .= "Téléphone: $phone\n";
$text_body .= "Type d'événement: $eventType\n";
$text_body .= "Nombre d'invités: $guests personnes\n";
$text_body .= "Date: $date\n";
$text_body .= "Formule: $formule\n\n";
$text_body .= "Message:\n$message\n";

// Headers
$headers = array();
$headers[] = "MIME-Version: 1.0";
$headers[] = "Content-Type: text/html; charset=UTF-8";
$headers[] = "From: Bar'OOF <$from>";
$headers[] = "Reply-To: $name <$email>";
$headers[] = "X-Mailer: PHP/" . phpversion();

// Envoi de l'email
$success = mail($to, $subject, $html_body, implode("\r\n", $headers));

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Message envoyé avec succès'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'envoi. Réessayez ou contactez-nous par email.'
    ]);
}
?>
