<?php
// Connexion à la base de données
include("./conn_bdd.php");

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer l'ID de la ligne à supprimer
$id = isset($_POST['id']) ? $_POST['id'] : null;

if ($id) {
    // Supprimer la ligne de la base de données
    $sql = "DELETE FROM calendrier WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

$conn->close();
// Rediriger vers la page calendrier
header("Location: ../calendrier.php");
exit();


?>
