<?php
// Connexion à la base de données
include("./conn_bdd.php");

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les données du formulaire
$id = isset($_POST['id']) ? $_POST['id'] : null;
$jours = isset($_POST['jours']) ? $_POST['jours'] : "";
$heure = isset($_POST['heure']) ? $_POST['heure'] : "";
$partie = isset($_POST['partie']) ? $_POST['partie'] : "";
$score = isset($_POST['score']) ? $_POST['score'] : "";
$niveau = isset($_POST['niveau']) ? $_POST['niveau'] : "";
$en_attente = isset($_POST['en_attente']) ? $_POST['en_attente'] : 0;
$commentaire = isset($_POST['commentaire']) ? $_POST['commentaire'] : "";

// Si le score est vide, on le met à NULL
$score = empty($score) ? null : $score;

if ($id) {
    // Vérifier si l'ID existe dans la base de données
    $sql = "SELECT * FROM calendrier WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Récupérer l'ancien jour et heure avant la mise à jour
        $row = $result->fetch_assoc();
        $ancien_jour = $row['jours'];
        $ancienne_heure = $row['heure'];

        // Si la partie est mise en attente, modifier la date
        if ($en_attente == 1) {
            $jours = "0000-00-00"; // Remplacer la date par "0000-00-00" pour mettre en attente
        }

        // Mettre à jour les informations existantes
        $sql = "UPDATE calendrier SET jours = ?, heure = ?, partie = ?, score = ?, niveau = ?, commentaire = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $jours, $heure, $partie, $score, $niveau, $commentaire, $id);
        $stmt->execute();

        // Écrire dans le fichier notifs.txt
        $notif = "[$id] Changement pour la partie '$partie' : Ancien jour et heure: $ancien_jour $ancienne_heure - Nouveau jour et heure: $jours $heure par " . $_SESSION['identifiant_organisateur'] . " le " . date('Y-m-d H:i:s') . "\n";
        file_put_contents('notifs.txt', $notif, FILE_APPEND);
    } else {
        // Insérer une nouvelle ligne si l'ID n'existe pas
        $sql = "INSERT INTO calendrier (jours, heure, partie, score, niveau, commentaire) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $jours, $heure, $partie, $score, $niveau, $commentaire);
        $stmt->execute();
    }
} else {
    // Insérer une nouvelle ligne si l'ID n'est pas fourni
    $sql = "INSERT INTO calendrier (jours, heure, partie, score, niveau, commentaire) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $jours, $heure, $partie, $score, $niveau, $commentaire);
    $stmt->execute();
}

$conn->close();

// Rediriger vers la page calendrier
header("Location: ../calendrier.php");
exit();
?>
