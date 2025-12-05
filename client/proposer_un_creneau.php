<?php
include("../logiciel/assets/conn_bdd.php");
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$jour = $_GET['jour'] ?? null;
$heure = $_GET['heure'] ?? null;
$partie = intval($_GET['partie'] ?? 0);
$equipe_id = $_GET['equipe_id'] ?? '';
$equipe_demande = intval($_GET['equipe_demande'] ?? 0);

if (!$jour || !$heure || !$partie || !$equipe_id || !$equipe_demande) {
    die("Paramètres manquants.");
}

list($equipeA, $equipeB) = explode("/", $equipe_id);
$equipe_adverse = ($equipe_demande == $equipeA) ? $equipeB : $equipeA;

// Vérifie si un créneau est déjà pris à cette date/heure
$stmt = $conn->prepare("SELECT id FROM calendrier WHERE jours = ? AND heure = ?");
$stmt->bind_param("ss", $jour, $heure);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("Ce créneau est déjà réservé.");
}

// Génère un token et enregistre la demande
$token = bin2hex(random_bytes(16));

$stmt = $conn->prepare("INSERT INTO demandes_report (partie_id, equipe_demande, equipe_adverse, jour, heure, token) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiisss", $partie, $equipe_demande, $equipe_adverse, $jour, $heure, $token);
$stmt->execute();

// Réserve le créneau temporairement dans le calendrier
$partie_token = "#" . $token;
$niveau = 1;
$validation_score = 0;
$partie_jouee = 0;
$ia = 1;

$stmt = $conn->prepare("INSERT INTO calendrier (jours, heure, partie, niveau, validation_score, partie_jouee, ia) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssiiii", $jour, $heure, $partie_token, $niveau, $validation_score, $partie_jouee, $ia);
$stmt->execute();

// Récupère l'équipe adverse
$stmt = $conn->prepare("SELECT `Joueur 1`, `Joueur 2`, `telephone` FROM inscriptions WHERE id = ?");
$stmt->bind_param("i", $equipe_adverse);
$stmt->execute();
$res = $stmt->get_result();
$adversaire = $res->fetch_assoc();

if (!$adversaire) {
    die("Équipe adverse introuvable.");
}

$host = $_SERVER['HTTP_HOST'];
$lien_unique = "http://$host/pelote/client/choix_report.php?token=$token";

$message_sms = "[Report de match]\n"
             . "L'équipe $equipe_demande demande le report de la partie #$partie prévue le $jour à $heure.\n"
             . "👉 Répondre à la demande : $lien_unique";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Simulation SMS</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
        }
        .sms {
            background-color: #eef;
            border: 1px solid #ccd;
            border-radius: 10px;
            padding: 1rem;
            max-width: 600px;
        }
        .sms p {
            margin: 0.5rem 0;
        }
        pre {
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            max-width: 600px;
        }
        .lien {
            margin-top: 1rem;
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 14px;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>
<body>

    <h2>💬 Message simulé envoyé par SMS</h2>

    <div class="sms">
        <p><strong>[Report de match]</strong></p>
        <p>L'équipe <strong><?= $equipe_demande ?></strong> demande le report de la partie <strong>#<?= $partie ?></strong> prévue le <strong><?= $jour ?></strong> à <strong><?= $heure ?></strong>.</p>
        <a class="lien" href="<?= $lien_unique ?>">👉 Répondre à la demande</a>
    </div>

    <h3>📄 Contenu brut du message envoyé :</h3>
    <pre><?= htmlspecialchars($message_sms) ?></pre>

    <p><small>Destinataires : <?= htmlspecialchars($adversaire['Joueur 1']) ?>, <?= htmlspecialchars($adversaire['Joueur 2']) ?> (<?= $adversaire['telephone'] ?>)</small></p>



</body>
</html>

<?php
// Construction de l'URL vers propose_creneau_sms.php avec tous les paramètres utiles
$query_string = http_build_query([
    'jour'            => $jour,
    'heure'           => $heure,
    'partie'          => $equipe_id,
    'equipe_id'       => $equipe_id,
    'equipe_demande'  => $equipe_demande,
    'token'           => $token
]);

header("Location: ./propose_creneau_sms.php?$query_string");
exit();
?>