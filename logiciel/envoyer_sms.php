<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: sms_form.html?error=Accès non autorisé");
    exit;
}

$numero = $_POST['numero'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($numero) || empty($message)) {
    header("Location: sms_form.html?error=Champs manquants");
    exit;
}

// Normalisation du numéro
$numero = preg_replace('/\D/', '', $numero);
$numero = preg_replace('/^0/', '33', $numero);

// Nettoyage du message
$texte = html_entity_decode($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$texte = str_replace(["\r\n", "\r", "\n"], "<br>", $texte);

// Envoi via TopMessage
$payload = [
    "data" => [
        "from"         => "IlharrePala",
        "to"           => [ $numero ],
        "text"         => $texte,
        "request_id"   => uniqid("req_"),
        "shorten_URLs" => true
    ]
];

$apiUrl = "https://api.topmessage.fr/v1/messages";
$apiKey = "cbf56aaebc95077b03b8c21160f42691";

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "X-TopMessage-Key: $apiKey"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Redirection avec résultat
if ($httpCode >= 200 && $httpCode < 300) {
    header("Location: ./sms.php?success=1");
} else {
    $errorMsg = urlencode("Erreur API ($httpCode)");
    header("Location: ./sms.php?error=$errorMsg");
}
exit;
