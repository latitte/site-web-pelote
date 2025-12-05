<?php
header('Content-Type: application/json');

// Vérifie si les données POST sont présentes
if (isset($_POST['partieId'], $_POST['newDate'], $_POST['newHeure'])) {
    $partieId = $_POST['partieId'];
    $newDate = $_POST['newDate'];
    $newHeure = $_POST['newHeure'];

    // Connexion à la base de données
    include("../../logiciel/assets/conn_bdd.php");

    $conn = new mysqli($servername, $username, $password, $database);

    // Vérifier la connexion
    if ($conn->connect_error) {
        $response = ['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error];
        echo json_encode($response);
        exit;
    }

    // Préparer et exécuter la mise à jour dans la base de données
    $stmt = $conn->prepare('UPDATE calendrier SET jours = ?, heure = ? WHERE id = ?');
    if ($stmt === false) {
        $response = ['success' => false, 'message' => 'Erreur de préparation de la requête : ' . $conn->error];
        echo json_encode($response);
        exit;
    }
    
    // Binder les paramètres et exécuter la requête
    $stmt->bind_param('ssi', $newDate, $newHeure, $partieId);

    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Date mise à jour avec succès.'];
    } else {
        $response = ['success' => false, 'message' => 'Erreur lors de la mise à jour de la date : ' . $stmt->error];
    }

    $stmt->close();
    $conn->close();

    echo json_encode($response);
} else {
    // Si les données POST requises ne sont pas fournies
    $response = ['success' => false, 'message' => 'Données manquantes pour la mise à jour de la date.'];
    echo json_encode($response);
}
?>
