<?php
include("./assets/extract_parametre.php");

// récuperation de la variable endDate

$date_debut_qualif = $parametres['startDate'];
$date_fin_qualif = $parametres['endDate'];

$jours_dispo_bdd = $parametres['jours_dispo'];


$jours_dispo_bdd = $parametres['jours_dispo'];
$jours_dispo_bdd = explode(", ", $jours_dispo_bdd);


// Tableau associatif pour mapper les abréviations aux noms complets des jours
$jours_complets = [
    "Lun" => "lundi",
    "Mar" => "mardi",
    "Mer" => "mercredi",
    "Jeu" => "jeudi",
    "Ven" => "vendredi",
    "Sam" => "samedi",
    "Dim" => "dimanche"
];

// Initialisation d'un tableau pour les jours complets
$jours1 = [];

// Parcourir le tableau des abréviations et remplacer par les noms complets
foreach ($jours_dispo_bdd as $abbr) {
    if (array_key_exists($abbr, $jours_complets)) {
        $jours1[] = $jours_complets[$abbr];
    }
}


$heures_dispo_bdd = $parametres['heures_dispo'];
$heure1 = explode(", ", $heures_dispo_bdd);






// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Plage de dates pour vérifier la disponibilité
$start_date = new DateTime($date_debut_qualif);
$start_date->modify('-1 day');

$end_date = new DateTime($date_fin_qualif);

// Variable pour stocker tous les messages de sortie
$output = '';

// Récupérer toutes les séries et leurs poules disponibles
$series_poules = array();
$sql_series = "SELECT DISTINCT serie FROM inscriptions";
$result_series = $conn->query($sql_series);

if ($result_series->num_rows > 0) {
    while ($row_series = $result_series->fetch_assoc()) {
        $serie_name = $row_series['serie'];
        $sql_poules = "SELECT DISTINCT poule FROM inscriptions WHERE serie = '$serie_name'";
        $result_poules = $conn->query($sql_poules);

        if ($result_poules->num_rows > 0) {
            while ($row_poules = $result_poules->fetch_assoc()) {
                $poule_name = $row_poules['poule'];
                $series_poules[$serie_name][] = $poule_name;
            }
        }
    }
}


function get_random_date($start_date, $end_date) {
    $start_timestamp = $start_date->getTimestamp();
    $end_timestamp = $end_date->getTimestamp();

    $random_timestamp = mt_rand($start_timestamp, $end_timestamp);
    return new DateTime(date('Y-m-d', $random_timestamp));
}




// Fonction pour vérifier la disponibilité dans le calendrier dans une plage de dates
function is_slot_available($conn, $day, $time, $start_date, $end_date, &$reserved_slots, $team1_id, $team2_id, &$team_match_dates) {
    $current_date = clone $start_date;
    $day_of_week = array("lundi" => 1, "mardi" => 2, "mercredi" => 3, "jeudi" => 4, "vendredi" => 5, "samedi" => 6, "dimanche" => 7);

    // Trouver le prochain jour spécifique
    $day_diff = ($day_of_week[$day] + 7 - $current_date->format('N')) % 7;
    if ($day_diff == 0) {
        $day_diff = 7;
    }
    $current_date->modify("+$day_diff days");
    $date_str = $current_date->format('Y-m-d');

    while ($current_date <= $end_date) {
        $slot_key = $date_str . " " . $time;

        $sql = "SELECT * FROM calendrier WHERE jours = '$date_str' AND heure = '$time'";
        $result = $conn->query($sql);
        $team1_has_match = isset($team_match_dates[$team1_id]) && in_array($date_str, $team_match_dates[$team1_id]);
        $team2_has_match = isset($team_match_dates[$team2_id]) && in_array($date_str, $team_match_dates[$team2_id]);

        if ($result && $result->num_rows == 0 && !in_array($slot_key, $reserved_slots) && !$team1_has_match && !$team2_has_match) {
            // Vérifier les périodes d'indisponibilité pour les deux équipes
            $team1_indispo_periods = get_indispo_periods($team1_id);
            $team2_indispo_periods = get_indispo_periods($team2_id);

            $time_formatted = date('H:i', strtotime($time)); // Convertir "19h15" en "19:15" pour la comparaison

            // Vérifier si le créneau est dans une période d'indisponibilité pour l'une des équipes
            if (!is_within_indispo_period($date_str . ' ' . $time_formatted, $team1_indispo_periods) &&
                !is_within_indispo_period($date_str . ' ' . $time_formatted, $team2_indispo_periods)) {
                // Vérifier si une des équipes a déjà un match programmé ce jour-là
                if (!is_team_playing_on_day($conn, $date_str, $team1_id, $team_match_dates) && !is_team_playing_on_day($conn, $date_str, $team2_id, $team_match_dates)) {
                    return $date_str;
                }
            }
        }
        $current_date->modify('+7 days');
        $date_str = $current_date->format('Y-m-d');
    }

    return false;
}

// Fonction pour vérifier si une équipe joue déjà ce jour-là
function is_team_playing_on_day($conn, $date_str, $team_id, $team_match_dates) {
    $sql = "SELECT * FROM calendrier WHERE jours = '$date_str' AND (LEFT(partie, LOCATE('/', partie) - 1) = '$team_id' OR RIGHT(partie, LENGTH(partie) - LOCATE('/', partie)) = '$team_id')";
    $result = $conn->query($sql);
    return $result->num_rows > 0;
}

// Fonction pour vérifier si une date et heure spécifique est dans une période d'indisponibilité
function is_within_indispo_period($datetime, $indispo_periods) {
    if (empty($indispo_periods)) {
        return false; // Aucune période d'indisponibilité, donc toujours disponible
    }

    $check_date = new DateTime($datetime);
    foreach ($indispo_periods as $period) {
        $start_date = new DateTime($period['start']);
        $end_date = new DateTime($period['end']);
        if ($check_date >= $start_date && $check_date <= $end_date) {
            return true;
        }
    }
    return false;
}

// Fonction pour récupérer les périodes d'indisponibilité d'une équipe
function get_indispo_periods($team_id) {
    global $conn;
    $sql = "SELECT periodes_indispo FROM inscriptions WHERE id = '$team_id'";
    $result = $conn->query($sql);
    $indispo_periods = array();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $indispo_str = $row['periodes_indispo'];
        $periods = explode(',', $indispo_str);
        foreach ($periods as $period) {
            $dates = explode(' au ', $period);
            if (count($dates) == 2) {
                $indispo_periods[] = array('start' => trim($dates[0]), 'end' => trim($dates[1]));
            }
        }
    }
    return $indispo_periods;
}

// Comparer les créneaux des deux équipes
function find_common_slots($team1, $team2) {

    global $jours1;
    global $heure1;
    $common_slots = array();

    // Variable qui determine les jours a placer
    $days = $jours1;

    $times = $heure1;
    $heure_value = count($heure1);

    foreach ($days as $day) {
        $team1_day_slots = str_split($team1[$day]);
        $team2_day_slots = str_split($team2[$day]);

        for ($i = 0; $i < $heure_value; $i++) {
            if ($team1_day_slots[$i] == '1' && $team2_day_slots[$i] == '1') {
                $common_slots[] = array("day" => $day, "time" => $times[$i]);
            }
        }
    }

    return $common_slots;
}

// Boucle principale pour générer les matchs pour chaque série et poule
foreach ($series_poules as $serie => $poules) {
    foreach ($poules as $poule) {
        // Requête SQL pour récupérer les équipes de la série et poule actuelles
        $sql = "SELECT * FROM inscriptions WHERE serie = '$serie' AND poule = '$poule'";
        $result = $conn->query($sql);

        $teams = array();
        if ($result->num_rows > 0) {
            // Lire les équipes
            while ($row = $result->fetch_assoc()) {
                $teams[] = $row;
            }

            // Générer tous les matchs possibles
            $matches = array();
            for ($i = 0; $i < count($teams); $i++) {
                for ($j = $i + 1; $j < count($teams); $j++) {
                    $matches[] = array("team1" => $teams[$i], "team2" => $teams[$j]);
                }
            }

            // Tableau pour stocker les créneaux réservés et les dates de match pour chaque équipe
            $reserved_slots = array();
            $team_match_dates = array();

            // Trouver et afficher les créneaux pour chaque match
            foreach ($matches as $match) {
                $team1 = $match["team1"];
                $team2 = $match["team2"];
                $team1_id = $team1["id"];
                $team2_id = $team2["id"];
                $partie = $team1_id . "/" . $team2_id;

                // Vérifier si la partie existe déjà dans le calendrier
                $check_sql = "SELECT * FROM calendrier WHERE partie = '$partie'";
                $check_result = $conn->query($check_sql);

                if ($check_result && $check_result->num_rows > 0) {
                    $output .= "<a style='color:green;'>Le match entre les équipes $team1_id et $team2_id est déjà programmé pour la poule $poule de la série $serie.</a><br>";
                    continue;
                }

                // Générer une date de départ aléatoire pour chaque match
                $random_start_date = get_random_date($start_date, $end_date);
                $found_slot = false;

                $common_slots = find_common_slots($team1, $team2);

                foreach ($common_slots as $slot) {
                    // Vérifier la disponibilité en utilisant la date de départ aléatoire
                    $available_date = is_slot_available($conn, $slot["day"], $slot["time"], $random_start_date, $end_date, $reserved_slots, $team1_id, $team2_id, $team_match_dates);
                    if ($available_date) {
                        // Insérer dans la base de données
                        $sql_insert = "INSERT INTO calendrier (partie, jours, heure) VALUES ('$partie', '$available_date', '" . $slot["time"] . "')";

                        if ($conn->query($sql_insert) === TRUE) {
                            $reserved_slots[] = $available_date . " " . $slot["time"];
                            $team_match_dates[$team1_id][] = $available_date;
                            $team_match_dates[$team2_id][] = $available_date;
                            $found_slot = true;
                            $output .= "Le match entre les équipes $team1_id et $team2_id a été programmé pour le $available_date à " . $slot["time"] . " pour la poule $poule de la série $serie.<br>";
                            break;
                        } else {
                            $output .= "<a style='color:red;'>Erreur lors de l'insertion dans le calendrier : " . $conn->error . "</a><br>";
                        }
                    }
                }

                if (!$found_slot) {
                    $output .= "<a style='color:red;'>Aucun créneau commun disponible trouvé pour les équipes $team1_id et $team2_id dans la plage de dates spécifiée pour la poule $poule de la série $serie.</a><br>";
                }
            }
        } else {
            $output .= "<a style='color:red;'>Aucune équipe trouvée dans la poule $poule de la série $serie.</a><br>";
        }
    }
}


// Écrire le contenu dans un fichier HTML (écrase le contenu précédent)
$file = 'resultats_calendrier.html';
$handle = fopen($file, 'w'); // Ouverture en mode écriture, écrase le contenu existant
fwrite($handle, $output);
fclose($handle);

// Fermer la connexion à la base de données
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier Automatique</title>
    <style>
        /* CSS pour le loader */
        .loader {
            border: 16px solid #f3f3f3; /* Light grey */
            border-top: 16px solid #3498db; /* Blue */
            border-radius: 50%;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
            position: fixed;
            top: 50%;
            left: 50%;
            margin-top: -60px;
            margin-left: -60px;
            z-index: 1000; /* Assure que le loader est au-dessus du contenu */
            display: none; /* Caché par défaut */
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Loader -->
    <div class="loader" id="loader"></div>

    <!-- Script pour montrer/cacher le loader -->
    <script>
        // Montrer le loader pendant l'exécution du script PHP
        document.getElementById('loader').style.display = 'block';
        // Cacher le loader une fois le script PHP terminé
        window.onload = function() {
            document.getElementById('loader').style.display = 'none';
        }
    </script>
</body>
</html>