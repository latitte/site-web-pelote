<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

include("./conn_bdd.php");

// Fonction pour écrire dans le fichier log
function logApiElimineTeam($message) {
    $logFile = 'log_api_elimine_team.txt';
    $currentTime = date('Y-m-d H:i:s');
    $logMessage = "[$currentTime] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    $response = array("error" => "Connection failed: " . $conn->connect_error);
    echo json_encode($response);
    logApiElimineTeam(json_encode($response));
    exit();
}

// Récupération des données de la requête GET
if (!isset($_GET['partie']) || !isset($_GET['score'])) {
    $response = array("error" => "Invalid input");
    echo json_encode($response);
    logApiElimineTeam(json_encode($response));
    exit();
}

$partie = $_GET['partie']; // Format attendu : "29/65"
$score = $_GET['score']; // Format attendu : "40/12"

// Validation du format
if (!preg_match('/^\d+\/\d+$/', $partie) || !preg_match('/^\d+\/\d+$/', $score)) {
    $response = array("error" => "Invalid format for partie or score.");
    echo json_encode($response);
    logApiElimineTeam(json_encode($response));
    exit();
}

// Extraction des IDs des équipes
list($equipeA, $equipeB) = explode('/', $partie);
list($scoreA, $scoreB) = explode('/', $score);

// Vérification des scores
if ($scoreA > $scoreB) {
    $perdant = $equipeB;
} elseif ($scoreA < $scoreB) {
    $perdant = $equipeA;
} else {
    $response = array("error" => "Scores are tied. No team has lost.");
    echo json_encode($response);
    logApiElimineTeam(json_encode($response));
    exit();
}

// Mise à jour du niveau de l'équipe perdante
$sql = "UPDATE classement SET niveau='elimine' WHERE equipe=?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $response = array("error" => "Prepare failed: " . $conn->error);
    echo json_encode($response);
    logApiElimineTeam(json_encode($response));
    exit();
}

$stmt->bind_param("i", $perdant);

if ($stmt->execute()) {
    $response = array("success" => "Team $perdant has been eliminated.");
    echo json_encode($response);
    logApiElimineTeam(json_encode($response));
} else {
    $response = array("error" => "Error updating record: " . $stmt->error);
    echo json_encode($response);
    logApiElimineTeam(json_encode($response));
}

$stmt->close();
$conn->close();
?>
