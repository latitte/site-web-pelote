<?php

include("./assets/extract_parametre.php");

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les séries dans l'ordre spécifié
$series_bdd = $parametres['series'];
$series_bdd = explode(",", $series_bdd);

// Fonction pour vérifier si toutes les équipes d'une poule ont au moins un créneau commun
function hasCommonSlotForAll($pool) {
    $days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

    // Initialisation pour les créneaux disponibles communs sur chaque jour
    $commonSlots = array_fill(0, 7, '111'); // '111' pour tous les créneaux disponibles (18h30, 19h30, 20h30)

    // Comparer les créneaux entre les équipes
    foreach ($pool as $team) {
        foreach ($days as $index => $day) {
            $commonSlots[$index] = $commonSlots[$index] & $team[$day];
        }
    }

    // Vérifier s'il y a au moins un créneau commun pour chaque jour
    foreach ($commonSlots as $slots) {
        if (strpos($slots, '1') !== false) {
            return [true, []]; // Il y a un créneau commun
        }
    }

    // Identifier les équipes problématiques
    $problemTeams = [];
    foreach ($pool as $team) {
        $teamCommon = array_fill(0, 7, '111');
        foreach ($pool as $otherTeam) {
            if ($team === $otherTeam) continue; // Ne pas comparer l'équipe avec elle-même
            foreach ($days as $index => $day) {
                $teamCommon[$index] = $teamCommon[$index] & $otherTeam[$day];
            }
        }
        
        // Si une équipe n'a pas de créneau commun avec les autres, elle est problématique
        $isProblematic = true;
        foreach ($teamCommon as $slots) {
            if (strpos($slots, '1') !== false) {
                $isProblematic = false;
                break;
            }
        }
        
        if ($isProblematic) {
            $problemTeams[] = $team;
        }
    }

    return [false, $problemTeams]; // Pas de créneau commun, renvoie les équipes problématiques
}

// Requête SQL pour récupérer toutes les poules et leurs équipes, triées par série et poule
$sql = "SELECT serie, poule, id, `Joueur 1`, `Joueur 2`, lundi, mardi, mercredi, jeudi, vendredi, samedi, dimanche FROM inscriptions ORDER BY serie, poule";
$result = $conn->query($sql);

// Tableau pour stocker les équipes par poule dans chaque série
$series = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $serie = $row['serie'];
        $poule = $row['poule'];

        // Ajouter chaque équipe à la poule correspondante dans la série correspondante
        if (!isset($series[$serie])) {
            $series[$serie] = [];
        }
        if (!isset($series[$serie][$poule])) {
            $series[$serie][$poule] = [];
        }
        $series[$serie][$poule][] = [
            'id' => $row['id'],
            'joueur1' => $row['Joueur 1'],
            'joueur2' => $row['Joueur 2'],
            'lundi' => $row['lundi'],
            'mardi' => $row['mardi'],
            'mercredi' => $row['mercredi'],
            'jeudi' => $row['jeudi'],
            'vendredi' => $row['vendredi'],
            'samedi' => $row['samedi'],
            'dimanche' => $row['dimanche']
        ];
    }

    // Parcourir les séries dans l'ordre spécifié par $series_bdd
    foreach ($series_bdd as $serieName) {
        if (isset($series[$serieName])) {
            echo "<h2>Série $serieName</h2>";
            
            foreach ($series[$serieName] as $pouleName => $pouleTeams) {
                list($isCompatible, $problemTeams) = hasCommonSlotForAll($pouleTeams);

                // Afficher un rond vert ou rouge pour chaque poule
                echo "<h3>Poule $pouleName: <span style='color:" . ($isCompatible ? "green" : "red") . ";'>●</span></h3>";

                // Afficher les détails des équipes dans la poule
                echo "<table border='1'>";
                echo "<tr><th>ID</th><th>Joueur 1</th><th>Joueur 2</th><th>Lundi</th><th>Mardi</th><th>Mercredi</th><th>Jeudi</th><th>Vendredi</th><th>Samedi</th><th>Dimanche</th></tr>";
                foreach ($pouleTeams as $team) {
                    $highlight = in_array($team, $problemTeams) ? " style='background-color: #f8d7da;'" : ""; // Colorer les équipes problématiques en rouge
                    echo "<tr$highlight>";
                    echo "<td>" . htmlspecialchars($team['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($team['joueur1']) . "</td>";
                    echo "<td>" . htmlspecialchars($team['joueur2']) . "</td>";
                    echo "<td>" . htmlspecialchars($team['lundi']) . "</td>";
                    echo "<td>" . htmlspecialchars($team['mardi']) . "</td>";
                    echo "<td>" . htmlspecialchars($team['mercredi']) . "</td>";
                    echo "<td>" . htmlspecialchars($team['jeudi']) . "</td>";
                    echo "<td>" . htmlspecialchars($team['vendredi']) . "</td>";
                    echo "<td>" . htmlspecialchars($team['samedi']) . "</td>";
                    echo "<td>" . htmlspecialchars($team['dimanche']) . "</td>";
                    echo "</tr>";
                }
                echo "</table><br>";

                // Si une poule est en rouge, afficher les équipes problématiques
                if (!$isCompatible) {
                    echo "<p style='color: red;'>Équipes problématiques dans la poule $pouleName:</p>";
                    echo "<ul>";
                    foreach ($problemTeams as $team) {
                        echo "<li>ID: " . htmlspecialchars($team['id']) . " - " . htmlspecialchars($team['joueur1']) . " / " . htmlspecialchars($team['joueur2']) . "</li>";
                    }
                    echo "</ul><br>";
                }
            }
        } else {
            echo "<h2>Série $serieName</h2>";
            echo "<p>Aucune poule trouvée pour cette série.</p>";
        }
    }
} else {
    echo "Aucune équipe trouvée.";
}

$conn->close();

?>
