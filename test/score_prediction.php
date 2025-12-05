<?php
require_once("../logiciel/assets/conn_bdd.php");

// Connexion à la base
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Étape 1 : Récupérer tous les matchs impliquant équipes 1 à 4
$sql = "SELECT partie, score FROM calendrier WHERE partie_jouee = 1";
$result = $conn->query($sql);

// Initialisation des forces
$forces = [];
$matchs = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        list($equipeA, $equipeB) = explode('/', $row['partie']);
        $equipeA = intval($equipeA);
        $equipeB = intval($equipeB);

        if (in_array($equipeA, [1, 2, 3, 4]) && in_array($equipeB, [1, 2, 3, 4])) {
            list($scoreA, $scoreB) = explode('/', $row['score']);
            $scoreA = intval($scoreA);
            $scoreB = intval($scoreB);

            // Enregistrer les matchs
            $matchs[] = [
                'A' => $equipeA,
                'B' => $equipeB,
                'scoreA' => $scoreA,
                'scoreB' => $scoreB
            ];

            // Accumuler la force de chaque équipe (somme points marqués - encaissés)
            if (!isset($forces[$equipeA])) $forces[$equipeA] = 0;
            if (!isset($forces[$equipeB])) $forces[$equipeB] = 0;

            $forces[$equipeA] += ($scoreA - $scoreB);
            $forces[$equipeB] += ($scoreB - $scoreA);
        }
    }
}

// Étape 2 : Prédiction pour le match équipe 3 vs équipe 4
$equipeX = 3;
$equipeY = 4;

// Valeurs par défaut si équipes jamais vues
$forceX = isset($forces[$equipeX]) ? $forces[$equipeX] : 0;
$forceY = isset($forces[$equipeY]) ? $forces[$equipeY] : 0;

// Différence relative
$diff = $forceX - $forceY;

// Score de base
$base_score = 35;
$ecart = min(15, max(-15, round($diff / 5))); // Limite l’écart max

$scoreX = $base_score;
$scoreY = $base_score - abs($ecart);

if ($ecart < 0) {
    // équipeY est plus forte
    $scoreY = $base_score;
    $scoreX = $base_score - abs($ecart);
}

// Affichage de la prédiction
echo "Prédiction pour équipe $equipeX vs équipe $equipeY : $scoreX / $scoreY";

$conn->close();
?>
