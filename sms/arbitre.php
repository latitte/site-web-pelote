<?php

// 1) Récupération de l’API des arbitres
$sourceUrl = "https://ilharre.tournoi-pelote.com/arbitre/arbitre.php?demain";
$json = file_get_contents($sourceUrl);
$json = trim($json);

if($json == "0"){
    echo "Aucun arbitre pour demain";
}else{

// Nettoyage des glissements d’échappement
$json = str_replace("\\/", "/", $json);
$json = preg_replace('/\\\\+/', '\\', $json);

// Décodage
$data = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Erreur JSON arbitre : " . json_last_error_msg());
}

// 2) Préparation du payload TopMessage
$apiUrl  = "https://api.topmessage.fr/v1/messages";
$apiKey  = "cbf56aaebc95077b03b8c21160f42691";
$to      = [];
$messages = [];

// Pour chaque entrée, on construit un envoi individuel
foreach ($data as $entry) {
    // Normalisation du numéro (0 → 33)
    $num = preg_replace('/^0/', '33', $entry['numero']);
    // Nettoyage du texte et conversion des \n en retours à la ligne réels
    $txt = html_entity_decode($entry['texte'], ENT_QUOTES|ENT_HTML5, 'UTF-8');
    $txt = str_replace('\n', "\n", $txt);

    // On envoie **un message par destinataire** pour garantir l’affichage des sauts de ligne
    $payload = [
        "data" => [
            "from"       => "IlharrePala",
            "to"         => [ $num ],
            "text"       => $txt,
            "request_id" => uniqid("req_"),
            "shorten_URLs" => true
        ]
    ];

    // cURL
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "X-TopMessage-Key: $apiKey"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ));

    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Log / debug
    echo "Envoi à $num : HTTP $code\n";
    echo "Réponse : $resp\n\n";
}
}
?>
