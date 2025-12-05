<?php
// Connexion à la base de données
include '../logiciel/assets/conn_bdd.php';

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Obtenir la date de demain
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Obtenir le paramètre 'heure' de la requête GET
$requested_hour = isset($_GET['heure']) ? $_GET['heure'] : null;

// Préparer et exécuter la requête pour les parties
$sql = "SELECT * FROM calendrier WHERE jours = '$tomorrow'";
if ($requested_hour) {
    $sql .= " AND heure = '$requested_hour'";
}
$result = $conn->query($sql);

// Initialiser une variable pour organiser les détails de la partie
$partie = null;

// Fonction pour obtenir les informations d'une équipe
function getTeamInfo($conn, $team_id) {
    $sql = "SELECT telephone, paye FROM inscriptions WHERE id = $team_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

// Vérifier s'il y a des résultats
if ($result->num_rows > 0) {
    // Prendre la première partie trouvée
    $row = $result->fetch_assoc();

    // Séparer les équipes
    list($team1_id, $team2_id) = explode('/', $row["partie"]);

    // Obtenir les informations des équipes
    $team1_info = getTeamInfo($conn, $team1_id);
    $team2_info = getTeamInfo($conn, $team2_id);

    // Construire les messages pour chaque équipe
    $message1 = "Bonsoir,<br>Rappel partie demain " . $row["heure"] . " à Ilharre<br><br>";
    $message2 = "Bonsoir,<br>Rappel partie demain " . $row["heure"] . " à Ilharre<br><br>";

    if ($team1_info['paye'] == 0) {
        $message1 .= "Merci de régulariser votre paiement avant la partie.";
    }
    if ($team2_info['paye'] == 0) {
        $message2 .= "Merci de régulariser votre paiement avant la partie.";
    }

    // Ajouter les détails communs
    $message1 .= "<br>Plus de renseignement sur https://$var_tournoi.tournoi-pelote.com<br>NE PAS REPONDRE";
    $message2 .= "<br>Plus de renseignement sur https://$var_tournoi.tournoi-pelote.com<br>NE PAS REPONDRE";

    // Ajouter les détails de la partie
    $partie = [
        "jours" => $row["jours"],
        "heure" => $row["heure"],
        "partie" => $row["partie"],
        "score" => $row["score"],
        "niveau" => $row["niveau"],
        "tel1" => $team1_info['telephone'],
        "tel2" => $team2_info['telephone'],
        "message1" => $message1,
        "message2" => $message2,
    ];

    // Afficher les résultats sous forme de dictionnaire
    echo json_encode($partie, JSON_PRETTY_PRINT);
} else {
    echo "0";
}

// Fermer la connexion
$conn->close();
?>
