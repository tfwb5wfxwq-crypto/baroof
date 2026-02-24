<?php
// Headers de sécurité
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CORS restrictif
$allowed_origin = 'https://baroof.fr';
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $allowed_origin) {
    header('Access-Control-Allow-Origin: ' . $allowed_origin);
} else {
    // Fallback pour développement local
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        header('Access-Control-Allow-Origin: *');
    }
}
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Importer PHPMailer
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Charger configuration depuis .env si disponible
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    $smtp_host = $env['SMTP_HOST'] ?? 'ssl0.ovh.net';
    $smtp_port = $env['SMTP_PORT'] ?? 587;
    $smtp_username = $env['SMTP_USERNAME'] ?? 'contact@baroof.fr';
    $smtp_password = $env['SMTP_PASSWORD'] ?? '***REMOVED***';
    $from_email = $env['FROM_EMAIL'] ?? 'm.battais@baroof.fr';
    $from_name = $env['FROM_NAME'] ?? "Bar'OOF";
    $to_email = $env['TO_EMAIL'] ?? 'm.battais@baroof.fr';
} else {
    // Fallback si .env n'existe pas (prod actuelle)
    $smtp_host = 'ssl0.ovh.net';
    $smtp_port = 587;
    $smtp_username = 'contact@baroof.fr';
    $smtp_password = '***REMOVED***';
    $from_email = 'm.battais@baroof.fr';
    $from_name = "Bar'OOF";
    $to_email = 'm.battais@baroof.fr';
}

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

// Fonction pour créer un email PHPMailer
function createMailer($smtp_host, $smtp_port, $smtp_username, $smtp_password) {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $smtp_host;
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_username;
    $mail->Password = $smtp_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $smtp_port;
    $mail->CharSet = 'UTF-8';
    return $mail;
}

try {
    // ========================================
    // EMAIL 1 : Envoyer à Marc (demande de devis)
    // ========================================
    $mail_marc = createMailer($smtp_host, $smtp_port, $smtp_username, $smtp_password);

    $mail_marc->setFrom($from_email, $from_name);
    $mail_marc->addAddress($to_email);
    $mail_marc->addReplyTo($email, $name);

    $mail_marc->Subject = "[Bar'OOF Contact] $eventType - $name";
    $mail_marc->isHTML(true);

    $mail_marc->Body = "
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

    $mail_marc->send();

    // ========================================
    // EMAIL 2 : Confirmation au client
    // ========================================
    $mail_client = createMailer($smtp_host, $smtp_port, $smtp_username, $smtp_password);

    $mail_client->setFrom($from_email, $from_name);
    $mail_client->addAddress($email, $name);
    $mail_client->addReplyTo($to_email, $from_name);

    $mail_client->Subject = "Demande bien reçue - Bar'OOF";
    $mail_client->isHTML(true);

    $mail_client->Body = "
    <!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Confirmation - Bar'OOF</title>
    </head>
    <body style='margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; background-color: #0f1419 !important;' bgcolor='#0f1419'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #0f1419 !important; padding: 40px 20px;' bgcolor='#0f1419'>
            <tr>
                <td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0' style='max-width: 600px; background-color: #1a1f25 !important; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.3);' bgcolor='#1a1f25'>

                        <!-- HEADER AVEC LOGO - FOND NOIR FORCÉ -->
                        <tr>
                            <td style='background-color: #0f1419 !important; padding: 50px 30px; text-align: center;' bgcolor='#0f1419'>
                                <img src='https://baroof.fr/logo-email.png' alt=\"Bar'OOF Logo\" style='max-width: 150px; height: auto; margin: 0 auto 15px; display: block;'>
                                <p style='margin: 10px 0 0 0; font-size: 13px; color: #D6AC7A !important; text-transform: uppercase; letter-spacing: 2.5px; font-weight: 600;'>Bar à cocktails mobile</p>
                            </td>
                        </tr>

                        <!-- CONTENU PRINCIPAL -->
                        <tr>
                            <td style='padding: 50px 40px;'>
                                <!-- Icône de validation -->
                                <div style='text-align: center; margin-bottom: 30px;'>
                                    <div style='width: 60px; height: 60px; margin: 0 auto; background: linear-gradient(135deg, #34C759, #28a745); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 20px rgba(52, 199, 89, 0.3);'>
                                        <svg width='32' height='32' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'>
                                            <path d='M20 6L9 17l-5-5'/>
                                        </svg>
                                    </div>
                                </div>

                                <h2 style='margin: 0 0 20px 0; font-size: 26px; font-weight: 700; color: #ffffff; text-align: center; letter-spacing: -0.5px;'>
                                    Demande bien reçue !
                                </h2>

                                <p style='margin: 0 0 30px 0; font-size: 17px; line-height: 1.6; color: rgba(255,255,255,0.8); text-align: center;'>
                                    Merci pour votre confiance. Votre demande de devis a été transmise à notre équipe.
                                </p>

                                <!-- BOX INFO -->
                                <div style='background: rgba(214, 172, 122, 0.1); border-left: 4px solid #D6AC7A; border-radius: 8px; padding: 25px; margin: 30px 0;'>
                                    <p style='margin: 0 0 15px 0; font-size: 15px; color: #D6AC7A; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;'>
                                        ⏱️ Prochaines étapes
                                    </p>
                                    <p style='margin: 0; font-size: 16px; line-height: 1.7; color: rgba(255,255,255,0.9);'>
                                        <strong style='color: #D6AC7A;'>Notre équipe</strong> reviendra vers vous <strong style='color: #D6AC7A;'>sous 24h</strong> pour échanger sur votre événement et vous proposer un devis personnalisé.
                                    </p>
                                </div>

                                <!-- CONTACT -->
                                <div style='text-align: center; margin: 40px 0 20px 0;'>
                                    <p style='margin: 0 0 15px 0; font-size: 14px; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 1.5px;'>
                                        Une question urgente ?
                                    </p>
                                    <a href='tel:+33622287195' style='display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #D6AC7A, #B89968); color: #ffffff; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 15px; box-shadow: 0 4px 15px rgba(214, 172, 122, 0.3); transition: all 0.3s;'>
                                        📞 06 22 28 71 95
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- FOOTER -->
                        <tr>
                            <td style='background: rgba(255,255,255,0.03); padding: 30px 40px; border-top: 1px solid rgba(255,255,255,0.1);'>
                                <p style='margin: 0 0 10px 0; font-size: 14px; color: rgba(255,255,255,0.5); text-align: center; line-height: 1.6;'>
                                    Bar'OOF – Bar à cocktails mobile événementiel<br>
                                    Île-de-France et partout en France
                                </p>
                                <p style='margin: 10px 0 0 0; font-size: 12px; color: rgba(255,255,255,0.3); text-align: center;'>
                                    <a href='https://baroof.fr' style='color: #D6AC7A; text-decoration: none;'>baroof.fr</a> •
                                    <a href='mailto:m.battais@baroof.fr' style='color: #D6AC7A; text-decoration: none;'>m.battais@baroof.fr</a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
    ";

    $mail_client->send();

    // Succès
    echo json_encode([
        'success' => true,
        'message' => 'Message envoyé avec succès'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'envoi : ' . $e->getMessage()
    ]);
}
?>
