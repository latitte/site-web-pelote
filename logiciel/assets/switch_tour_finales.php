<?php
include("./extract_parametre.php");


// Dates de plage pour les matchs
$dateStart = $parametres['startDateFinales'];
$dateEnd = $parametres['endDateFinales'];

function addMatchToCalendar($team1_id, $team2_id, $niveau, $dateStart, $dateEnd) {
    global $var_tournoi;
    $apiUrl = "https://$var_tournoi.tournoi-pelote.com/logiciel/assets/api_calendar_add_attente_auto.php";
    // $apiUrl = "https://$var_tournoi.tournoi-pelote.com/logiciel/assets/api_calendar_add.php";
    $url = "$apiUrl?team1_id=$team1_id&team2_id=$team2_id&niveau=$niveau&date_start=$dateStart&date_end=$dateEnd";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    echo "API Response for match $team1_id vs $team2_id: $response<br>";

    return $response;
}

try {
    // Créer une connexion PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    // Configurer PDO pour afficher les erreurs en mode exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Préparer et exécuter la requête SQL pour récupérer les équipes par niveau
    $stmt = $conn->prepare("SELECT equipe, niveau FROM classement ORDER BY niveau");
    $stmt->execute();

    // Récupérer toutes les lignes
    $equipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tableau pour stocker les paires d'équipes
    $matches = [];

    // Utiliser un tableau temporaire pour stocker les équipes par niveau
    $tempEquipes = [];

    foreach ($equipes as $equipe) {
        $niveau = $equipe['niveau'];
        $nomEquipe = $equipe['equipe'];

        // Vérifier que le niveau n'est pas vide, nul ou "eliminee"
        if (!empty($niveau) && strtolower($niveau) !== "elimine") {
            // Si le niveau existe déjà dans le tableau temporaire, créer un match
            if (isset($tempEquipes[$niveau])) {
                // Ajouter le match au tableau des matches avec le niveau
                $matches[] = [$tempEquipes[$niveau], $nomEquipe, $niveau];
                // Ajouter la partie au calendrier via l'API et afficher la réponse
                addMatchToCalendar($tempEquipes[$niveau], $nomEquipe, $niveau, $dateStart, $dateEnd);
                // Supprimer l'entrée pour ce niveau car le match est fait
                unset($tempEquipes[$niveau]);
            } else {
                // Sinon, ajouter l'équipe au tableau temporaire
                $tempEquipes[$niveau] = $nomEquipe;
            }
        }
    }

    // Afficher les matches
    echo "<table border='1'>";
    echo "<tr><th>Match</th><th>Equipe 1</th><th>Equipe 2</th><th>Niveau</th></tr>";
    foreach ($matches as $index => $match) {
        echo "<tr><td>Match " . ($index + 1) . "</td><td>" . $match[0] . "</td><td>" . $match[1] . "</td><td>" . $match[2] . "</td></tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$conn = null;
?>
