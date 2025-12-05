<?php
require_once 'conn_bdd.php';

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    exit("Erreur BDD");
}

// Récupération des données POST
$jours = $_POST['jours'] ?? '';
$heure = $_POST['heure'] ?? '';
$id = $_POST['id'] ?? null;

// Validation minimale
if (!$jours || !$heure) {
    http_response_code(400);
    exit("Champs manquants");
}

if ($id) {
    // Si on modifie une partie existante, on ignore son propre ID
    $stmt = $conn->prepare("SELECT COUNT(*) FROM calendrier WHERE jours = ? AND heure = ? AND id != ?");
    $stmt->bind_param("ssi", $jours, $heure, $id);
} else {
    // Sinon, on vérifie juste s'il y a une partie à cette date/heure
    $stmt = $conn->prepare("SELECT COUNT(*) FROM calendrier WHERE jours = ? AND heure = ?");
    $stmt->bind_param("ss", $jours, $heure);
}

$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();
$conn->close();

echo ($count > 0) ? "pris" : "libre";
