<?php
header('Content-Type: application/json');

// Inclure ton fichier de paramètres ou autres
include("../../logiciel/assets/extract_parametre.php");

// Fonction pour enregistrer les logs
function log_api_output($output) {
    // Enregistrer les logs dans un fichier
    file_put_contents("api_logs.txt", $output . PHP_EOL, FILE_APPEND);
}

// Récupérer les paramètres de la requête HTTP
$team1_id = isset($_GET['team1_id']) ? intval($_GET['team1_id']) : null;
$team2_id = isset($_GET['team2_id']) ? intval($_GET['team2_id']) : null;
$niveau = isset($_GET['niveau']) ? $_GET['niveau'] : null;

// Récupérer les dates de début et de fin
$date_debut_qualif = isset($_GET['date_start']) ? $_GET['date_start'] : $parametres['startDate'];
$date_fin_qualif = isset($_GET['date_end']) ? $_GET['date_end'] : $parametres['endDate'];

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
if (!empty($jours_dispo_bdd)) {
    foreach ($jours_dispo_bdd as $abbr) {
        if (array_key_exists($abbr, $jours_complets)) {
            $jours1[] = $jours_complets[$abbr];
        }
    }
}

// Vérification si $jours1 est bien un tableau et contient des valeurs
if (empty($jours1)) {
    $output = json_encode(['error' => 'No valid days found.']);
    log_api_output($output);
    echo $output;
    exit;
}

// Vérification des heures de disponibilité
if (empty($heure1)) {
    $output = json_encode(['error' => 'No valid hours found.']);
    log_api_output($output);
    echo $output;
    exit;
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
function get_available_slots($conn, $day, $time, $start_date, $end_date) {
    $available_slots = [];
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

        // Ajouter les créneaux disponibles
        if ($result->num_rows == 0) {
            $time_formatted = date('H:i', strtotime($time));
            $available_slots[] = $date_str . ' ' . $time_formatted;
        }

        // Passer à la semaine suivante
        $current_date->modify('+7 days');
    }

    return $available_slots;
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

// Fonction pour afficher tous les créneaux disponibles pour les équipes
function get_all_available_slots($team1_id, $team2_id, $start_date, $end_date) {
    global $conn;

    // Récupérer les jours et les horaires de disponibilité
    $available_slots = [];
    foreach ($jours1 as $day) {
        foreach ($heure1 as $time) {
            $slots = get_available_slots($conn, $day, $time, $start_date, $end_date);
            if (!empty($slots)) {
                $available_slots[$day][$time] = $slots;
            }
        }
    }

    return $available_slots;
}

// Récupérer les créneaux disponibles pour les deux équipes
$available_slots = get_all_available_slots($team1_id, $team2_id, $start_date, $end_date);

// Formatage de la réponse
$output = [];
foreach ($available_slots as $day => $times) {
    foreach ($times as $time => $slots) {
        foreach ($slots as $slot) {
            $output[] = ['day' => $day, 'time' => $time, 'slot' => $slot];
        }
    }
}

// Retourner la réponse en JSON
echo json_encode($output);

// Fermer la connexion
$conn->close();
?>
