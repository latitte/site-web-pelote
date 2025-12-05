<?php
header('Content-Type: application/json');

// Définir le fuseau horaire
date_default_timezone_set('Europe/Paris');

// Connexion à la base de données
include("../../logiciel/assets/conn_bdd.php");

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

// Date actuelle pour commencer la génération des créneaux
$current_date = new DateTime();

// Date de fin (1er septembre 2024)
$end_date = new DateTime('2024-09-01');

// Tableau des créneaux possibles
$creneaux_possibles = ['18h30', '19h15', '20h00'];

$slots = [];

// Générer les options pour les créneaux disponibles
while ($current_date <= $end_date) {
    $jour_semaine = $current_date->format('N'); // 1 (lundi) à 7 (dimanche)
    
    // Exclure les samedis (6) et dimanches (7)
    if ($jour_semaine >= 6) {
        $current_date->modify('+1 day');
        continue;
    }

    $date_str = $current_date->format('Y-m-d');

    foreach ($creneaux_possibles as $creneau) {
        $sql = "SELECT COUNT(*) AS count FROM calendrier WHERE jours = ? AND heure = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $date_str, $creneau); // Utiliser $date_str au lieu de $date
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count == 0) {
            $slots[] = ['date' => $date_str, 'heure' => $creneau]; // Ajouter la date et l'heure au tableau $slots
        }
    }

    // Passer à la prochaine date
    $current_date->modify('+1 day');
}

$conn->close();

echo json_encode(['success' => true, 'slots' => $slots]);
?>
