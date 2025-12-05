<?php
header('Content-Type: application/json');

include("./extract_parametre.php");

// Récupération des paramètres de la requête HTTP
$team1_id = isset($_GET['team1_id']) ? intval($_GET['team1_id']) : null;
$team2_id = isset($_GET['team2_id']) ? intval($_GET['team2_id']) : null;
$niveau = isset($_GET['niveau']) ? $_GET['niveau'] : null;

if (isset($_GET['date_start']) && isset($_GET['date_end'])) {
    $date_debut_qualif = $_GET['date_start'];
    $date_fin_qualif = $_GET['date_end'];
} else {
    $date_debut_qualif = $parametres['startDate'];
    $date_fin_qualif = $parametres['endDate'];
}

// Vérification des paramètres
if ($team1_id === null || $team2_id === null || $niveau === null) {
    $output = json_encode(['error' => 'Missing parameters.']);
    log_api_output($output);
    echo $output;
    exit;
}

// Récupération des paramètres
$jours_dispo_bdd = explode(", ", $parametres['jours_dispo']);
$heure1 = explode(", ", $parametres['heures_dispo']);

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
foreach ($jours_dispo_bdd as $abbr) {
    if (array_key_exists($abbr, $jours_complets)) {
        $jours1[] = $jours_complets[$abbr];
    }
}

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    $output = json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    log_api_output($output);
    echo $output;
    exit;
}

// Plage de dates pour vérifier la disponibilité
$start_date = new DateTime($date_debut_qualif);
$end_date = new DateTime($date_fin_qualif);

// Fonction pour vérifier la disponibilité dans le calendrier dans une plage de dates
function is_slot_available($conn, $day, $time, $start_date, $end_date, &$reserved_slots, $team1_id, $team2_id, &$team_match_dates) {
    $current_date = clone $start_date;
    $day_of_week = ["lundi" => 1, "mardi" => 2, "mercredi" => 3, "jeudi" => 4, "vendredi" => 5, "samedi" => 6, "dimanche" => 7];

    // Trouver la première occurrence du jour
    $day_diff = ($day_of_week[$day] + 7 - $current_date->format('N')) % 7;
    if ($day_diff == 0) {
        $day_diff = 7;
    }
    $current_date->modify("+$day_diff days");

    while ($current_date <= $end_date) {
        $date_str = $current_date->format('Y-m-d');
        $slot_key = $date_str . " " . $time;

        // Vérifier la disponibilité
        $sql = $conn->prepare("SELECT * FROM calendrier WHERE jours = ? AND heure = ?");
        $sql->bind_param("ss", $date_str, $time);
        $sql->execute();
        $result = $sql->get_result();

        if ($result === false) {
            return false;
        }

        $team1_has_match = isset($team_match_dates[$team1_id]) && in_array($date_str, $team_match_dates[$team1_id]);
        $team2_has_match = isset($team_match_dates[$team2_id]) && in_array($date_str, $team_match_dates[$team2_id]);

        if ($result->num_rows == 0 && !in_array($slot_key, $reserved_slots) && !$team1_has_match && !$team2_has_match) {
            $time_formatted = date('H:i', strtotime($time));

            if (!is_within_indispo_period($date_str . ' ' . $time_formatted, get_indispo_periods($team1_id)) &&
                !is_within_indispo_period($date_str . ' ' . $time_formatted, get_indispo_periods($team2_id))) {
                
                // Vérifier si les équipes ont déjà un match cette semaine
                if (!has_team_played_this_week($conn, $team1_id, $date_str) && !has_team_played_this_week($conn, $team2_id, $date_str)) {
                    return $date_str;
                }
            }
        }
        
        // Passer à la semaine suivante
        $current_date->modify('+7 days');
    }

    return false;
}


// Fonction pour vérifier si une équipe joue déjà ce jour-là
function is_team_playing_on_day($conn, $date_str, $team_id, $team_match_dates) {
    $sql = $conn->prepare("SELECT * FROM calendrier WHERE jours = ? AND (LEFT(partie, LOCATE('/', partie) - 1) = ? OR RIGHT(partie, LENGTH(partie) - LOCATE('/', partie)) = ?)");
    $sql->bind_param("sss", $date_str, $team_id, $team_id);
    $sql->execute();
    $result = $sql->get_result();
    
    if ($result === false) {
        return false;
    }
    return $result->num_rows > 0;
}

// Fonction pour vérifier si une date et heure spécifique est dans une période d'indisponibilité
function is_within_indispo_period($datetime, $indispo_periods) {
    if (empty($indispo_periods)) {
        return false;
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

function has_team_played_this_week($conn, $team_id, $date) {
    // Déterminer le début et la fin de la semaine en cours pour la date donnée
    $date_obj = new DateTime($date);
    $start_of_week = $date_obj->modify('Monday this week')->format('Y-m-d');
    $end_of_week = $date_obj->modify('Sunday this week')->format('Y-m-d');
    
    // Requête SQL pour vérifier combien de matchs ont été programmés pour l'équipe pendant cette semaine
    $sql = $conn->prepare("SELECT COUNT(*) as match_count FROM calendrier WHERE partie LIKE CONCAT(?, '%') AND jours BETWEEN ? AND ?");
    $sql->bind_param("sss", $team_id, $start_of_week, $end_of_week);
    $sql->execute();
    $result = $sql->get_result();
    
    if ($result === false) {
        return false;
    }

    $row = $result->fetch_assoc();
    return $row['match_count'] > 0;
}



// Fonction pour récupérer les périodes d'indisponibilité d'une équipe
function get_indispo_periods($team_id) {
    global $conn;
    $sql = $conn->prepare("SELECT periodes_indispo FROM inscriptions WHERE id = ?");
    $sql->bind_param("i", $team_id);
    $sql->execute();
    $result = $sql->get_result();
    
    if ($result === false) {
        return array();
    }
    
    $indispo_periods = array();
    if ($result->num_rows > 0) {
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

// Fonction pour trouver les créneaux communs
function find_common_slots($team1, $team2) {
    global $jours1;
    global $heure1;
    $common_slots = array();

    $days = $jours1;
    $times = $heure1;
    $heure_value = count($heure1);

    foreach ($days as $day) {
        if (!isset($team1[$day]) || !isset($team2[$day])) {
            continue;
        }
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

// Fonction pour ajouter les parties non placées au fichier HTML
function log_unplaced_match($team1_id, $team2_id, $niveau) {
    $file_path = __DIR__ . '/parties_non_placees.html';
    $date = date('Y-m-d H:i:s');
    $content = "<p>[$date] Aucun créneau commun disponible trouvé pour les équipes $team1_id et $team2_id au niveau $niveau.</p>\n";
    file_put_contents($file_path, $content, FILE_APPEND);
}

// Requête SQL pour récupérer les informations des équipes
$sql_team1 = $conn->prepare("SELECT * FROM inscriptions WHERE id = ?");
$sql_team1->bind_param("i", $team1_id);
$sql_team1->execute();
$result_team1 = $sql_team1->get_result();

if ($result_team1 === false) {
    $output = json_encode(['error' => "Error retrieving team 1: " . $conn->error]);
    log_api_output($output);
    echo $output;
    exit;
}
$team1 = $result_team1->fetch_assoc();

$sql_team2 = $conn->prepare("SELECT * FROM inscriptions WHERE id = ?");
$sql_team2->bind_param("i", $team2_id);
$sql_team2->execute();
$result_team2 = $sql_team2->get_result();

if ($result_team2 === false) {
    $output = json_encode(['error' => "Error retrieving team 2: " . $conn->error]);
    log_api_output($output);
    echo $output;
    exit;
}
$team2 = $result_team2->fetch_assoc();

$matches = array(array("team1" => $team1, "team2" => $team2));

// Tableau pour stocker les créneaux réservés et les dates de match pour chaque équipe
$reserved_slots = array();
$team_match_dates = array();

// Trouver et afficher les créneaux pour le match spécifique
$output = [];
foreach ($matches as $match) {
    $team1 = $match["team1"];
    $team2 = $match["team2"];
    $team1_id = $team1["id"];
    $team2_id = $team2["id"];
    $partie = $team1_id . "/" . $team2_id;

    // Vérifier si la partie existe déjà dans le calendrier
    $check_sql = $conn->prepare("SELECT * FROM calendrier WHERE partie = ? AND niveau = ?");
    $check_sql->bind_param("si", $partie, $niveau);
    $check_sql->execute();
    $check_result = $check_sql->get_result();

    if ($check_result === false) {
        $output[] = ['message' => "Error checking match existence: " . $conn->error, 'status' => 'error'];
        continue;
    }

    if ($check_result->num_rows > 0) {
        $output[] = ['message' => "Le match entre les équipes $team1_id et $team2_id est déjà programmé.", 'status' => 'success'];
        continue;
    }

    $common_slots = find_common_slots($team1, $team2);
    $found_slot = false;

    // Sort common slots to try earliest dates first
    usort($common_slots, function($a, $b) {
        return strcmp($a['time'], $b['time']);
    });

    foreach ($common_slots as $slot) {
        $available_date = is_slot_available($conn, $slot["day"], $slot["time"], $start_date, $end_date, $reserved_slots, $team1_id, $team2_id, $team_match_dates);
        if ($available_date) {
            // Insérer dans la base de données
            $sql_insert = $conn->prepare("INSERT INTO calendrier (partie, jours, heure, niveau) VALUES (?, ?, ?, ?)");
            $sql_insert->bind_param("ssss", $partie, $available_date, $slot["time"], $niveau);

            if ($sql_insert->execute()) {
                $reserved_slots[] = $available_date . " " . $slot["time"];
                $team_match_dates[$team1_id][] = $available_date;
                $team_match_dates[$team2_id][] = $available_date;
                $found_slot = true;
                $output[] = ['message' => "Le match entre les équipes $team1_id et $team2_id a été programmé pour le $available_date à " . $slot["time"] . " à un niveau $niveau.", 'status' => 'success'];
                break;
            } else {
                $output[] = ['message' => "Erreur lors de l'insertion dans le calendrier : " . $conn->error, 'status' => 'error'];
            }
        }
    }

    if (!$found_slot) {
        $output[] = ['message' => "Aucun créneau commun disponible trouvé pour les équipes $team1_id et $team2_id.", 'status' => 'error'];
        log_unplaced_match($team1_id, $team2_id, $niveau);

                // Écrire dans le fichier notifs.txt
                $notif = "Aucun créneau commun disponible trouvé pour les équipes $team1_id et $team2_id.\n";
                file_put_contents('./notifs.txt', $notif, FILE_APPEND);

    }
}

// Fermer la connexion à la base de données
$conn->close();

$output_json = json_encode($output);
log_api_output($output_json);
echo $output_json;

function log_api_output($output) {
    $log_file = __DIR__ . '/log_api_add_partie.txt';
    if (file_put_contents($log_file, $output . PHP_EOL, FILE_APPEND) === false) {
        error_log("Échec de l'écriture dans le fichier de log : " . $log_file);
    }
}
?>
