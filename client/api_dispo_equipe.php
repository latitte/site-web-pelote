<?php
header('Content-Type: application/json');

include("../logiciel/assets/extract_parametre.php");

// Récupération des paramètres de la requête HTTP
$team1_id = isset($_GET['team1_id']) ? intval($_GET['team1_id']) : null;
$team2_id = isset($_GET['team2_id']) ? intval($_GET['team2_id']) : null;

// Créer un objet DateTime avec la date et l'heure actuelles
$date = new DateTime();

// Ajouter 1 jour
$date->modify('+1 day');

// Afficher la date au format 'Y-m-d' (année-mois-jour)

$date_debut_qualif = $date->format('Y-m-d');
$date_fin_qualif = $parametres['endDate'];


// Vérification des paramètres
if ($team1_id === null || $team2_id === null) {
    echo json_encode(['error' => 'Missing parameters.']);
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
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Plage de dates pour vérifier la disponibilité
$start_date = new DateTime($date_debut_qualif);
$end_date = new DateTime($date_fin_qualif);

// Fonction pour vérifier la disponibilité dans le calendrier et collecter les créneaux disponibles
function is_slot_available($conn, $day, $time, $start_date, $end_date, &$reserved_slots, $team1_id, $team2_id, &$team_match_dates) {
    $current_date = clone $start_date;
    $day_of_week = ["lundi" => 1, "mardi" => 2, "mercredi" => 3, "jeudi" => 4, "vendredi" => 5, "samedi" => 6, "dimanche" => 7];
    $day_diff = ($day_of_week[$day] + 7 - $current_date->format('N')) % 7;
    if ($day_diff == 0) $day_diff = 7;
    $current_date->modify("+$day_diff days");

    $available_slots = [];
    $iterations = 0;

    while ($current_date <= $end_date && $iterations < 100) {
        $date_str = $current_date->format('Y-m-d');
        $slot_key = $date_str . " " . $time;

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
            $available_slots[] = $slot_key;
            $iterations++;
        }

        $current_date->modify('+7 days');
    }

    return $available_slots;
}

// Récupérer les informations des équipes
$sql_team1 = $conn->prepare("SELECT * FROM inscriptions WHERE id = ?");
$sql_team1->bind_param("i", $team1_id);
$sql_team1->execute();
$result_team1 = $sql_team1->get_result();
$team1 = $result_team1->fetch_assoc();

$sql_team2 = $conn->prepare("SELECT * FROM inscriptions WHERE id = ?");
$sql_team2->bind_param("i", $team2_id);
$sql_team2->execute();
$result_team2 = $sql_team2->get_result();
$team2 = $result_team2->fetch_assoc();

// Tableau pour stocker les créneaux réservés et les dates de match pour chaque équipe
$reserved_slots = [];
$team_match_dates = [];

// Trouver les créneaux communs
$common_slots = find_common_slots($team1, $team2);

// Trouver et afficher les créneaux pour le match spécifique
$output = [];
foreach ($common_slots as $slot) {
    $available_slots = is_slot_available($conn, $slot["day"], $slot["time"], $start_date, $end_date, $reserved_slots, $team1_id, $team2_id, $team_match_dates);
    if ($available_slots) {
        $output[] = $available_slots;
    }
}

echo json_encode($output);

// Fonction pour trouver les créneaux communs
function find_common_slots($team1, $team2) {
    global $jours1, $heure1;
    $common_slots = [];
    $days = $jours1;
    $times = $heure1;
    $heure_value = count($heure1);

    foreach ($days as $day) {
        if (!isset($team1[$day]) || !isset($team2[$day])) continue;

        $team1_day_slots = str_split($team1[$day]);
        $team2_day_slots = str_split($team2[$day]);

        for ($i = 0; $i < $heure_value; $i++) {
            if ($team1_day_slots[$i] == '1' && $team2_day_slots[$i] == '1') {
                $common_slots[] = ["day" => $day, "time" => $times[$i]];
            }
        }
    }

    return $common_slots;
}
?>
