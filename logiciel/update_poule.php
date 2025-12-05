<?php
require_once './assets/conn_bdd.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    exit("Erreur de connexion à la BDD");
}

$id = $_POST['id'] ?? null;
$poule = $_POST['poule'] ?? null;

if ($id && $poule !== null) {

    // Mise à jour de la poule dans la table inscriptions
    $stmt = $conn->prepare("UPDATE inscriptions SET poule = ? WHERE id = ?");
    $stmt->bind_param("si", $poule, $id);
    $stmt->execute();

    echo "ok";
} else {
    http_response_code(400);
    echo "Erreur : données manquantes";
}
?>
