<?php
header('Content-Type: application/json');

include("./extract_parametre.php");
include("./conn_bdd.php");

$team1_id = isset($_GET['team1_id']) ? intval($_GET['team1_id']) : null;
$team2_id = isset($_GET['team2_id']) ? intval($_GET['team2_id']) : null;
$niveau = isset($_GET['niveau']) ? $_GET['niveau'] : null;

if ($team1_id === null || $team2_id === null || $niveau === null) {
    echo json_encode(['error' => 'Missing parameters.']);
    exit;
}

// Partie à insérer
$partie = $team1_id . "/" . $team2_id;
$date = "1999-01-01";
$heure = "00:00";

// Connexion BDD
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Vérifier si la partie existe déjà
$check_sql = $conn->prepare("SELECT * FROM calendrier WHERE partie = ? AND niveau = ?");
$check_sql->bind_param("ss", $partie, $niveau);
$check_sql->execute();
$check_result = $check_sql->get_result();

if ($check_result && $check_result->num_rows > 0) {
    echo json_encode(['message' => "Le match entre les équipes $team1_id et $team2_id est déjà programmé.", 'status' => 'success']);
    exit;
}

// Insertion dans la base
$insert_sql = $conn->prepare("INSERT INTO calendrier (partie, jours, heure, niveau) VALUES (?, ?, ?, ?)");
$insert_sql->bind_param("ssss", $partie, $date, $heure, $niveau);

if ($insert_sql->execute()) {
    echo json_encode(['message' => "Match ajouté le 1/1/1999 à 00:00 pour les équipes $team1_id et $team2_id au niveau $niveau.", 'status' => 'success']);
} else {
    echo json_encode(['message' => "Erreur insertion : " . $conn->error, 'status' => 'error']);
}

$conn->close();
?>
