<?php

include("./assets/extract_parametre.php");

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fonction pour obtenir les disponibilités d'une équipe
function getTeamAvailability($teamId) {
    global $conn;

    $sql = "SELECT lundi, mardi, mercredi, jeudi, vendredi, samedi, dimanche FROM inscriptions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teamId);
    $stmt->execute();
    $result = $stmt->get_result();
    $availability = [];

    if ($row = $result->fetch_assoc()) {
        $availability = [
            'lundi' => $row['lundi'],
            'mardi' => $row['mardi'],
            'mercredi' => $row['mercredi'],
            'jeudi' => $row['jeudi'],
            'vendredi' => $row['vendredi'],
            'samedi' => $row['samedi'],
            'dimanche' => $row['dimanche'],
        ];
    }

    $stmt->close();
    return $availability;
}

// Fonction pour vérifier la disponibilité des créneaux horaires pour deux équipes
function isSlotAvailableForTeams($team1Id, $team2Id, $day, $timeSlot) {
    global $conn;

    // Disponibilités des créneaux horaires
    $timeSlots = [
        "18h15" => 0,
        "19h30" => 1,
        "20h30" => 2
    ];

    if (!isset($timeSlots[$timeSlot])) {
        return false; // Créneau horaire invalide
    }

    $timeIndex = $timeSlots[$timeSlot];

    // Convertir les jours en français en jours en anglais pour les correspondances avec la disponibilité
    $dayMap = [
        "lundi" => "lundi",
        "mardi" => "mardi",
        "mercredi" => "mercredi",
        "jeudi" => "jeudi",
        "vendredi" => "vendredi",
        "samedi" => "samedi",
        "dimanche" => "dimanche"
    ];

    if (!isset($dayMap[$day])) {
        return false; // Jour invalide
    }

    // Récupérer les disponibilités des équipes
    $dispoEquipe1 = getTeamAvailability($team1Id);
    $dispoEquipe2 = getTeamAvailability($team2Id);

    // Vérifier les disponibilités pour le jour et le créneau horaire
    $dispo1 = $dispoEquipe1[$dayMap[$day]] ?? '000';
    $dispo2 = $dispoEquipe2[$dayMap[$day]] ?? '000';

    // Vérifier les créneaux horaires
    $availableForTeam1 = isset($dispo1[$timeIndex]) && $dispo1[$timeIndex] === '1';
    $availableForTeam2 = isset($dispo2[$timeIndex]) && $dispo2[$timeIndex] === '1';

    return $availableForTeam1 && $availableForTeam2;
}

// Convertir une date en jour de la semaine en français
function getFrenchDay($date) {
    $dateTime = new DateTime($date);
    $dayOfWeek = $dateTime->format('l'); // Obtenir le jour en anglais

    $dayMap = [
        "Monday" => "lundi",
        "Tuesday" => "mardi",
        "Wednesday" => "mercredi",
        "Thursday" => "jeudi",
        "Friday" => "vendredi",
        "Saturday" => "samedi",
        "Sunday" => "dimanche"
    ];

    return $dayMap[$dayOfWeek] ?? '';
}

// Requête SQL pour récupérer les parties programmées
$sql = "SELECT id, jours, heure, partie FROM calendrier";
$result = $conn->query($sql);

// Tableau pour stocker les résultats de la vérification
$results = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
		$id_partie = $row['id'];
        $partie = $row['partie'];
        $day = getFrenchDay($row['jours']); // Convertir la date en jour de la semaine en français
        $timeSlot = $row['heure']; // 18h30, 19h30 ou 20h30

        // Extraire les IDs des équipes de la partie
        list($team1Id, $team2Id) = explode('/', $partie);

        // Vérifier la disponibilité des créneaux pour les équipes
        $isAvailable = isSlotAvailableForTeams($team1Id, $team2Id, $day, $timeSlot);
        
        // Ajouter les résultats dans le tableau uniquement si la partie est en erreur
        if (!$isAvailable) {
            $results[] = [
			    'id_partie' => $id_partie,
                'partie' => $partie,
                'day' => $day,
                'timeSlot' => $timeSlot,
                'team1Id' => $team1Id,
                'team2Id' => $team2Id,
            ];
        }
    }

    // Afficher le bilan des parties en erreur
    echo "<h2>Parties en Erreur (Non Disponibles)</h2>";
    
    foreach ($results as $result) {
        echo "<h3>Partie " . htmlspecialchars($result['partie']) . ": <span style='color: red;'>●</span></h3>";
        echo "<p>Date: " . htmlspecialchars($result['day']) . ", Heure: " . htmlspecialchars($result['timeSlot']) . "</p>";
		echo "<p><a href='./assets/edit_partie_calendrier.php?id=" . $result['id_partie'] . "'>Edit</a></p>";

		
        // Afficher les disponibilités des équipes
        list($team1Id, $team2Id) = explode('/', $result['partie']);
        $sqlDispo = "SELECT id, `Joueur 1`, `Joueur 2`, lundi, mardi, mercredi, jeudi, vendredi, samedi, dimanche FROM inscriptions WHERE id IN ($team1Id, $team2Id)";
        $resDispo = $conn->query($sqlDispo);

        $availability = [];
        while ($row = $resDispo->fetch_assoc()) {
            $availability[$row['id']] = [
                'Joueur 1' => $row['Joueur 1'] ?? 'Inconnu',
                'Joueur 2' => $row['Joueur 2'] ?? 'Inconnu',
                'lundi' => $row['lundi'],
                'mardi' => $row['mardi'],
                'mercredi' => $row['mercredi'],
                'jeudi' => $row['jeudi'],
                'vendredi' => $row['vendredi'],
                'samedi' => $row['samedi'],
                'dimanche' => $row['dimanche'],
            ];
        }

        // Afficher les disponibilités
        echo "<h4>Disponibilités des Équipes :</h4>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Joueur 1</th><th>Joueur 2</th><th>Lundi</th><th>Mardi</th><th>Mercredi</th><th>Jeudi</th><th>Vendredi</th><th>Samedi</th><th>Dimanche</th></tr>";

        foreach ($availability as $id => $disp) {
            // Mettre en surbrillance les créneaux problématiques
            $highlight = '';
            $timeSlots = ["18h30", "19h30", "20h30"];
            $timeIndex = $timeSlots[$timeSlot] ?? -1; // Correction ici

            if ($timeIndex != -1) {
                $dayAvailability = $disp[$result['day']] ?? '000';
                if (isset($dayAvailability[$timeIndex]) && $dayAvailability[$timeIndex] === '0') {
                    $highlight = 'background-color: red; color: white;';
                }
            }

            echo "<tr>";
            echo "<td>" . htmlspecialchars($id) . "</td>";
            echo "<td>" . htmlspecialchars($disp['Joueur 1']) . "</td>";
            echo "<td>" . htmlspecialchars($disp['Joueur 2']) . "</td>";
            echo "<td style='$highlight'>" . htmlspecialchars($disp['lundi']) . "</td>";
            echo "<td style='$highlight'>" . htmlspecialchars($disp['mardi']) . "</td>";
            echo "<td style='$highlight'>" . htmlspecialchars($disp['mercredi']) . "</td>";
            echo "<td style='$highlight'>" . htmlspecialchars($disp['jeudi']) . "</td>";
            echo "<td style='$highlight'>" . htmlspecialchars($disp['vendredi']) . "</td>";
            echo "<td style='$highlight'>" . htmlspecialchars($disp['samedi']) . "</td>";
            echo "<td style='$highlight'>" . htmlspecialchars($disp['dimanche']) . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
} else {
    echo "Aucune partie programmée trouvée.";
}

$conn->close();
?>
