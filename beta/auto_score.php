<?php
include '../logiciel/assets/extract_parametre.php';
$duree_partie = $parametres['duree_partie'];

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fonction pour générer un score aléatoire entre 0 et 39
function generateScore() {
    return rand(0, 34);
}

// Fonction pour générer le score total
function generateTotalScore() {
    $score1 = 35;
    $score2 = generateScore();

    // Déterminer aléatoirement l'ordre des scores
    if (rand(0, 1) === 0) {
        return "$score1/$score2";
    } else {
        return "$score2/$score1";
    }
}

// Requête pour sélectionner les parties sans score
$sql = "SELECT id FROM calendrier WHERE score IS NULL";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Mettre à jour les scores pour chaque partie sans score
    while($row = $result->fetch_assoc()) {
        $totalScore = generateTotalScore();
        $updateSql = "UPDATE calendrier SET score = '$totalScore' WHERE id = " . $row['id'];
        if ($conn->query($updateSql) === TRUE) {
            echo "Score for id " . $row['id'] . " updated successfully<br>";
        } else {
            echo "Error updating score for id " . $row['id'] . ": " . $conn->error . "<br>";
        }
    }
} else {
    echo "No records found without scores";
}

$conn->close();
?>
