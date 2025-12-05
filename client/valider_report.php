<?php
// ==== DEBUG CHECK ====
echo "VALIDER_REPORT_VERSION = 2.1<br>";

include("../logiciel/assets/conn_bdd.php");
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$token = $_GET['token'] ?? null;
$action = $_GET['action'] ?? null;
$auto = isset($_GET['auto']) && $_GET['auto'] == '1';

if (!$token || !in_array($action, ['accepte', 'refuse'])) {
    die("Paramètres invalides.");
}

// Récupération de la demande
$stmt = $conn->prepare("SELECT * FROM demandes_report WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$demande = $result->fetch_assoc();

if (!$demande) {
    die("Demande introuvable.");
}

$partie_id = $demande['partie_id'];
$jour = $demande['jour'];
$heure = $demande['heure'];
$token_tag = "#" . $token;

// ==== VERSION SANS IntlDateFormatter ====
function formatDateFr($date)
{
    $jours = ["dimanche","lundi","mardi","mercredi","jeudi","vendredi","samedi"];
    $mois = ["","janvier","février","mars","avril","mai","juin","juillet","août","septembre","octobre","novembre","décembre"];

    $ts = strtotime($date);
    if (!$ts) return $date;

    $j = $jours[date("w", $ts)];
    $d = date("j", $ts);
    $m = $mois[date("n", $ts)];

    return ucfirst("$j $d $m");
}

$date_affichee = formatDateFr($jour);

// Récupération du libellé partie
$stmt = $conn->prepare("SELECT partie FROM calendrier WHERE id = ?");
$stmt->bind_param("i", $partie_id);
$stmt->execute();
$result = $stmt->get_result();
$calendrier = $result->fetch_assoc();
$partie_libelle = $calendrier['partie'] ?? 'inconnue';

// Suppression ligne temporaire (#TOKEN)
$stmt = $conn->prepare("DELETE FROM calendrier WHERE partie = ?");
$stmt->bind_param("s", $token_tag);
$stmt->execute();

// ==== Action du report ====
if ($action === "accepte") {
    $stmt = $conn->prepare("UPDATE calendrier SET jours = ?, heure = ? WHERE id = ?");
    $stmt->bind_param("ssi", $jour, $heure, $partie_id);
    $stmt->execute();

    $statut = 'accepte';
    $message = "✅ Le report a été accepté. La partie $partie_libelle est déplacée au $date_affichee à $heure.";
} 
else {
    $statut = 'refuse';
    $message = "❌ Le report au $date_affichee à $heure a été refusé. La partie $partie_libelle reste à son créneau initial.";
    if ($auto) $message .= " ⏰ Délai dépassé.";
}

// Mise à jour du statut
$stmt = $conn->prepare("UPDATE demandes_report SET statut = ? WHERE token = ?");
$stmt->bind_param("ss", $statut, $token);
$stmt->execute();

// ==== Envoi des SMS ====
if ($partie_libelle && preg_match('/^\d+\/\d+$/', $partie_libelle)) {
    list($equipeA, $equipeB) = explode("/", $partie_libelle);
    $ids = [$equipeA, $equipeB];

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $conn->prepare("SELECT telephone FROM inscriptions WHERE id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $equipes = $result->fetch_all(MYSQLI_ASSOC);

    $apiUrl = "https://api.topmessage.fr/v1/messages";
    $apiKey = "cbf56aaebc95077b03b8c21160f42691";

    foreach ($equipes as $equipe) {
        $num = preg_replace('/^0/', '33', $equipe['telephone']);
        if (!preg_match('/^33\d{9}$/', $num)) continue;

        $payload = [
            "data" => [
                "from"         => "IlharrePala",
                "to"           => [$num],
                "text"         => $message,
                "request_id"   => uniqid("rep_"),
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_exec($ch);
        curl_close($ch);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; }
        .result {
            background-color: #f0f0f0;
            border-left: 6px solid <?= $action === 'accepte' ? '#28a745' : '#dc3545' ?>;
            padding: 1rem;
            max-width: 600px;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<div class="result">
    <h2>Réponse enregistrée</h2>
    <p><?= htmlspecialchars($message) ?></p>
</div>

</body>
</html>
