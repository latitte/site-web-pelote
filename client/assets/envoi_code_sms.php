<?php

// Connexion à la base de données
require_once '../../logiciel/assets/conn_bdd.php';

// Connexion PDO
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base : " . $e->getMessage());
}

// Récupération des équipes non forfaits
$sql = "SELECT `id`, `Joueur 1`, `Joueur 2`, `telephone`, `code` FROM inscriptions WHERE forfait = 1 AND telephone IS NOT NULL AND telephone != ''";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Paramètres de l'API TopMessage
$apiUrl = "https://api.topmessage.fr/v1/messages";
$apiKey = "cbf56aaebc95077b03b8c21160f42691";

// Pour chaque inscription, on envoie un SMS
foreach ($inscriptions as $entry) {
    $joueur1 = $entry['Joueur 1'];
    $joueur2 = $entry['Joueur 2'];
    $tel     = preg_replace('/^0/', '33', $entry['telephone']);
    $code    = $entry['code'];
    $id_team    = $entry['id'];
    // Message à personnaliser
    $message = "Bonjour $joueur1 et $joueur2,<br><br><br>Votre identifiant est votre numéro d'équipe : $id_team<br>Voici votre code d'équipe pour le tournoi : $code<br><br>Depuis le site, vous pouvez accéder à l'onglet 'Mon Compte'.<br>Vous pourrez consulter vos informations ainsi que reprogrammer vos parties sans passer par l'organisateur.<br><br>Attention : ne reprogrammez la partie que si l'équipe adverse vous donne son accord.<br>NE PAS REPONDRE";

    $payload = [
        "data" => [
            "from"       => "IlharrePala",
            "to"         => [ $tel ],
            "text"       => $message,
            "request_id" => uniqid("req_"),
            "shorten_URLs" => true
        ]
    ];

    // Envoi via cURL
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "X-TopMessage-Key: $apiKey"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    $resp = curl_exec($ch);
    $code_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Log
    echo "Envoi à $tel : HTTP $code_http\n";
    echo "Réponse : $resp\n\n";
}

?>
