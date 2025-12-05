<?php


if(!isset($_GET['heure'])){
    echo "erreur: heure absente en parametre";
}else{
    $heure_recup = $_GET['heure'];

// 1) Récupération de l’API des rappels joueurs
$sourceUrl = "https://ilharre.tournoi-pelote.com/api/rappel_joueur.php?heure=$heure_recup";
$json = file_get_contents($sourceUrl);
$json = trim($json);

//echo $json;


if($json == "0"){
    echo "Pas de partie demain à $heure_recup";
}else{

// Nettoyage des glissements d’échappement
$json = str_replace("\\/", "/", $json);
$json = preg_replace('/\\\\+/', '\\', $json);

// Décodage
$data = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Erreur JSON rappel_joueur : " . json_last_error_msg());
}

// 2) Préparation de l’envoi via l’API TopMessage
$apiUrl = "https://api.topmessage.fr/v1/messages";
$apiKey = "cbf56aaebc95077b03b8c21160f42691";

// Fonction d'envoi
function envoyerMessage($numero, $texte, $apiUrl, $apiKey) {
    // Normalisation du numéro (0 → 33)
    $num = preg_replace('/^0/', '33', $numero);
    // Nettoyage et remplacement \n → <br>
    $txt = html_entity_decode($texte, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $txt = str_replace(["\r\n", "\r", "\n"], "<br>", $txt);

    $payload = [
        "data" => [
            "from"         => "IlharrePala",
            "to"           => [ $num ],
            "text"         => $txt,
            "request_id"   => uniqid("req_"),
            "shorten_URLs" => true
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "X-TopMessage-Key: $apiKey"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Log
    echo "Envoi à $num : HTTP $code\n";
    echo "Réponse : $resp\n\n";
}

// Envoi des deux messages
if (!empty($data['tel1']) && !empty($data['message1'])) {
    envoyerMessage($data['tel1'], $data['message1'], $apiUrl, $apiKey);
}
if (!empty($data['tel2']) && !empty($data['message2'])) {
    envoyerMessage($data['tel2'], $data['message2'], $apiUrl, $apiKey);
}

}
}
?>
