<?php
// Récupération de l'ID de la partie sous forme X/Y
$equipe_id = $_GET['partie'] ?? '';
if (!preg_match('/^\d+\/\d+$/', $equipe_id)) {
    die("❌ Format de partie invalide.");
}

list($equipeA, $equipeB) = explode("/", $equipe_id);

// Connexion à la base
include('../logiciel/assets/conn_bdd.php');
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupération des deux équipes concernées
$ids = [$equipeA, $equipeB];
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $conn->prepare("SELECT id, telephone FROM inscriptions WHERE id IN ($placeholders)");
$stmt->execute($ids);
$equipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$equipes) {
    die("❌ Aucune équipe trouvée pour cette partie.");
}

// Message à envoyer
$jour           = $_GET['jour'] ?? '';
$heure          = $_GET['heure'] ?? '';
$partie         = $_GET['partie'] ?? '';
$equipe_id      = $_GET['equipe_id'] ?? '';
$equipe_demande = $_GET['equipe_demande'] ?? '';
$token          = $_GET['token'] ?? '';

$host = $_SERVER['HTTP_HOST'];
$lien_unique = "http://$host/client/choix_report.php?token=$token";

$message = "Report de match\n\n"
         . "L'équipe $equipe_demande souhaite déplacer la partie $partie au $jour à $heure.\n"
         . "👉 Répondre à la demande : $lien_unique";


// Envoi via TopMessage
$apiUrl  = "https://api.topmessage.fr/v1/messages";
$apiKey  = "cbf56aaebc95077b03b8c21160f42691";


$sms_envoyes = 0;

foreach ($equipes as $equipe) {
    // Ne pas envoyer au demandeur
    if ($equipe['id'] == $equipe_demande) continue;

    $sms_envoyes += 1;
    $num = preg_replace('/^0/', '33', $equipe['telephone']);
    if (!preg_match('/^33\d{9}$/', $num)) {
        echo "❌ Numéro invalide : " . htmlspecialchars($equipe['telephone']) . "<br>";
        continue;
    }

    $payload = [
        "data" => [
            "from"         => "IlharrePala",
            "to"           => [ $num ],
            "text"         => $message,
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
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "✅ Envoi à {$num} — HTTP $httpCode<br>";
    echo "Réponse : $response<br><br>";
}



if($sms_envoyes == 1){
    header('Location: ./modif_demande_creneau_ok.php');
    exit();
}
?>
