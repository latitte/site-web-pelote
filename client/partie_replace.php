
<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['id'])) {
    $team_id = $_SESSION['id'];
    // echo "Utilisateur connecté, ID : " . htmlspecialchars($team_id);
} else {
    echo "Aucune session active. Utilisateur non connecté.";
}

include("../logiciel/assets/extract_parametre.php");




// Vérifier si l'utilisateur est connecté
if (isset($_GET['partie'])) {
    $partie_a_replacer = $_GET['partie'];
    $equipe_id = $_GET['equipe_id'];
} else {
  header('Location: ./compte-index.php');
  exit();
}
?>



<?php


include("../logiciel/assets/extract_parametre.php");

// Table de correspondance des jours abrégés vers les jours complets
$jours_dispo_bdd = $parametres['jours_dispo'];
$jours_disponibles = explode(", ", $jours_dispo_bdd);

$heures_dispo_bdd = $parametres['heures_dispo'];
$heures_dispo = explode(", ", $heures_dispo_bdd);

$jours_complets = [
    'Lun' => 'lundi',
    'Mar' => 'mardi',
    'Mer' => 'mercredi',
    'Jeu' => 'jeudi',
    'Ven' => 'vendredi',
    'Sam' => 'samedi',
    'Dim' => 'dimanche'
];

$jours_complets_list = array_map(function($jour) use ($jours_complets) {
    return $jours_complets[$jour] ?? 'Inconnu'; // Utiliser "Inconnu" si la clé n'existe pas
}, $jours_disponibles);


// Détection de la langue à partir de l'URL, sinon défaut en français
$lang_code = isset($_GET['lang']) ? $_GET['lang'] : 'fr';

// Chargement du fichier de langue approprié
if ($lang_code == 'eus') {
    include("./assets/lang/lang_eus.php");
} else {
    include("./assets/lang/lang_fr.php");
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['title']; ?></title>
    <link rel="stylesheet" href="./assets/style.css">
    <link rel="manifest" href="./assets/manifest.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="icon" type="image/x-icon" href="./assets/tournoi-pelote.ico">
    <script src="./assets/app.js" defer></script>

</head>
<body>
    <div class="popup">
        <div class="header">
            <h1 style="text-align:center;"><?php echo $lang['tournament']; ?></h1>
        </div>
        <div class="menu">
        <?php include("./assets/menu.php"); ?>
        </div>

        <h3 style="text-align:center; color:red;">Ne modifier une partie qu'après confirmation de l'adversaire</h3>
        

<?php



// ------------------------------------ CALENDRIER ---------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------

?>
<style>

        body {
            font-family: 'Roboto', sans-serif;
            /* background-color: #f8f9fa; */
        }
        .sidebar_cal {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        .sidebar_cal-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
            /* Scrollable contents if viewport is shorter than content. */
        }
        @supports ((position: -webkit-sticky) or (position: sticky)) {
            .sidebar_cal-sticky {
                position: -webkit-sticky;
                position: sticky;
                top: 48px;
            }
        }
        .sidebar_cal .nav-link {
            font-weight: 500;
            color: #333333;
            padding: 10px 20px;
            border-radius: 4px;
            margin-bottom: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .sidebar_cal .nav-link.active {
            background-color: #007bff;
            color: #ffffff;
        }
        .main-content {
            /*margin-left: 250px;*/
            /* padding: 20px; */
        }
        .calendar {
            background-color: #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .calendar {
                /* padding: 20px; */
                margin-bottom: 20px;
                width: 170%;
            }
        }
        .calendar h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }
        .navigation {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            padding: 10px 0;
            border-bottom: 1px solid #eeeeee;
        }
        .navigation a {
            text-decoration: none;
            color: #007bff;
            font-weight: 700;
            transition: color 0.3s ease;
            padding: 0 10px;
        }
        .navigation a:hover {
            color: #0056b3;
        }
        .days, .dates {
            display: flex;
            flex-wrap: wrap;
            border-bottom: 1px solid #eeeeee;
        }
        .day, .date {
            width: calc(100% / 7);
            text-align: center;
            padding: 15px 10px;
            box-sizing: border-box;
            border-right: 1px solid #eeeeee;
            border-bottom: 1px solid #eeeeee;
            font-weight: 700;
            color: #666666;
        }
        .day:last-child, .date:last-child {
            border-right: none;
        }
        .day {
            background-color: #f8f9fa;
        }
        .date {
            min-height: 80px;
            position: relative;
            background-color: #f0f0f0;
            transition: background-color 0.3s ease;
            border-radius: 4px;
            margin-bottom: 5px;
            overflow: hidden;
            /* display: none; */
        }
        .date:hover {
            background-color: #f0f0f0;
        }
        .date span {
            position: absolute;
            top: 10px;
            left: 10px;
            color: #333333;
            font-weight: 700;
            z-index: 100;
        }
        .match {
            position: relative;
            background-color: #007bff;
            padding: 5px;
            /* margin: 5px 10px; */
            border-radius: 4px;
            font-size: 14px;
            color: #ffffff;
            text-align: center;
            margin-top: 20px;
        }
        .match.green {
            background-color: #28a745 !important;
            color: #ffffff;
        }
        .match.barrage {
            background-color: #89000d !important;
            color: #ffffff;
        }
        .match p {
            margin: 5px 0;
        }
        .match.reserved {
            background-color: #dc3545;
        }
        .day.disabled {
            background-color: #f0f0f0; /* Couleur de fond grise */
            color: #ccc; /* Texte gris */
            pointer-events: none; /* Désactiver les interactions */
            /* display: none; */
        }
        .navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px 0;
        }

        .navigation .prev,
        .navigation .next {
            text-decoration: none;
            color: #007bff;
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .navigation .prev:hover,
        .navigation .next:hover {
            color: #0056b3;
        }

        .navigation span {
            font-weight: bold;
            font-size: 1.2em;
        }


        
        @media (max-width: 768px) {
            .d-flex {
                /* padding: 20px; */
                width: 160vh!important;
            }
        }


        .date.past {
            background-color: #e0e0e0 !important;
            color: #999 !important;
            pointer-events: none;
            opacity: 0.6;
        }

    </style>
</head>
<body>
    <div class="d-flex" style="display: block !important;">
        <!-- sidebar -->

        <?php
// Calculer le mois et l'année en fonction de la navigation
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Ajustement de l'année en fonction du mois précédent ou suivant
if ($current_month < 1) {
    $current_month = 12;
    $current_year--;
} elseif ($current_month > 12) {
    $current_month = 1;
    $current_year++;
}

// Mapping des noms des mois
$mois_map = [
    1 => "Janvier", 2 => "Février", 3 => "Mars", 4 => "Avril", 5 => "Mai", 6 => "Juin",
    7 => "Juillet", 8 => "Août", 9 => "Septembre", 10 => "Octobre", 11 => "Novembre", 12 => "Décembre"
];
$mois = $mois_map[$current_month];
?>

<!-- Navigation -->
<div class="navigation">
    <a href="?month=<?php echo ($current_month == 1) ? 12 : $current_month - 1; ?>&year=<?php echo ($current_month == 1) ? $current_year - 1 : $current_year; ?>&partie=<?php echo $partie_a_replacer; ?>&equipe_id=<?php echo $equipe_id; ?>" class="prev">

        &laquo; Précédent
    </a>
    <span><?php echo $mois; ?> <?php echo $current_year; ?></span>
    <a href="?month=<?php echo ($current_month == 12) ? 1 : $current_month + 1; ?>&year=<?php echo ($current_month == 12) ? $current_year + 1 : $current_year; ?>&partie=<?php echo $partie_a_replacer; ?>&equipe_id=<?php echo $equipe_id; ?>" class="next">
        Suivant &raquo;
    </a>
</div>


                <div class="days">
                    <?php
                        // Tableau des jours de la semaine
                        $days_of_week = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

                        // Générer les jours avec une classe conditionnelle pour le style
                        foreach ($days_of_week as $day) {
                            $classes = 'day';
                            if (!in_array($day, $jours_disponibles)) {
                                $classes .= ' disabled'; // Ajouter une classe pour griser les jours non disponibles
                            }
                            echo '<div class="' . $classes . '">' . $day . '</div>';
                        }
                    ?>
                </div>
                <div class="dates">
                    <?php
                        // Calculer la date de début et de fin du mois
                        $start_date = new DateTime("$current_year-$current_month-01");
                        $end_date = new DateTime("$current_year-$current_month-" . $start_date->format('t'));
                        $days_of_week = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];



                        // Connexion à MySQL
                        $conn = new mysqli($servername, $username, $password, $dbname);

                        // Vérifier la connexion
                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }

                        // Requête pour récupérer les matchs du mois sélectionné
                        $sql = "SELECT id, jours, heure, partie, score, niveau FROM calendrier WHERE MONTH(jours) = $current_month";

                        // Exécuter la requête
                        $result = $conn->query($sql);

						// Tableau associatif des matchs
	                        $matches = [];
                        while ($row = $result->fetch_assoc()) {
                            $date = new DateTime($row['jours']);
                            $id_partie = $row['id'];
                            $heure = $row['heure'];
                            $matches[$date->format('Y-m-d')][] = [
                                'id' => $id_partie,
                                'heure' => $heure,
                                'partie' => $row['partie'],
                                'score' => $row['score'], // Ajouter le score
                                'niveau' => $row['niveau']
                            ];
                        }


                        // Fermer la connexion à MySQL
                        $conn->close();

                        // Tableau des créneaux possibles
                        $creneaux_possibles = $heures_dispo;

                        // Espacement pour les jours avant le premier jour du mois
                        for ($i = 1; $i < $start_date->format('N'); $i++) {
                            echo '<div class="date"></div>';
                        }

                        // Générer les dates
                        $current_date = clone $start_date;
                        while ($current_date <= $end_date) {
                            $date_str = $current_date->format('Y-m-d');
                            $day_of_week = $current_date->format('D'); // Récupérer le jour de la semaine (ex: Mon, Tue, etc.)
                            $day_of_week_fr = '';
                            

                            // Convertir en français
                            switch ($day_of_week) {
                                case 'Mon':
                                    $day_of_week_fr = 'Lun';
                                    break;
                                case 'Tue':
                                    $day_of_week_fr = 'Mar';
                                    break;
                                case 'Wed':
                                    $day_of_week_fr = 'Mer';
                                    break;
                                case 'Thu':
                                    $day_of_week_fr = 'Jeu';
                                    break;
                                case 'Fri':
                                    $day_of_week_fr = 'Ven';
                                    break;
                                case 'Sat':
                                    $day_of_week_fr = 'Sam';
                                    break;
                                case 'Sun':
                                    $day_of_week_fr = 'Dim';
                                    break;
                                default:
                                    $day_of_week_fr = '';
                                    break;
                            }
                            if (in_array($day_of_week_fr, $jours_disponibles)) {
                            echo '<div class="date" style="background-color:white!important; display:block!important;"><span>' . $current_date->format('j') . '</span>';
                            }else{
                                echo '<div class="date"><span>' . $current_date->format('j') . '</span>';
                            }
                            // Vérifier chaque créneau possible
                            foreach ($creneaux_possibles as $creneau) {

                                if(($current_date->format('D') == 'Wed') && ($creneau == "18h30")){
                                    continue;
                                }

                                $creneau_partie = '';
                                $creneau_score = '';
                                $id_partie = '';
                                $niveau = '';
                                $creneau_occupe = false;

                                // Vérifier s'il y a un match pour ce créneau à cette date
                                if (isset($matches[$date_str])) {
                                    foreach ($matches[$date_str] as $match) {
                                        if ($match['heure'] == $creneau) {
                                            $creneau_partie = $match['partie'];
                                            $id_partie = $match['id'];
                                            $creneau_score = isset($match['score']) ? $match['score'] : ''; // Ajouter le score s'il existe
                                            $niveau = $match['niveau'];
                                            $creneau_occupe = true;
                                            break;
                                        }
                                    }
                                }

                                // Déterminer les classes à appliquer
                                $classes = 'match';
                                if (!empty($creneau_partie)) {
                                    $classes .= ' reserved';
                                    if (!empty($creneau_score)) {
                                        $classes .= ' green'; // Ajouter la classe verte si le score est présent
                                    }
                                }

                                
                                if ($niveau == "2") {
                                    $classes .= ' reserved';
                                    $classes .= ' barrage'; // Ajouter la classe verte si le score est présent
                                }

                                // Afficher le créneau avec la classe appropriée seulement si le jour est disponible
                                if (in_array($day_of_week_fr, $jours_disponibles)) {

                                    echo '<a style="color:white;"><div class="' . $classes . '">';
                                    echo '<p>' . $creneau . '<br><a style="color:white;">' . $creneau_partie . '</a></p>';
                                        
                                    
                                if ($creneau_occupe) {
                                    echo "<p></p>";
                                } else {
                                    // Format de date lisible ou clé (au choix)
                                    $jour = $current_date->format('Y-m-d'); // ou 'd/m/Y' pour affichage jour de la partie
                                    $heure = $creneau; // heure de la partie du créneau choisi
                                    $today = new DateTime(); // Aujourd'hui
                                    $today = $today->format('Y-m-d'); // ou 'd/m/Y' pour affichage

                                    $date_debut_qualif = $parametres['startDate'];
                                    $date_fin_report = $parametres['date_fin_report'];


                                    $demain = date('Y-m-d', strtotime('+1 day'));
                                    $heure_actuelle = date('H');

                                    $report_demain_apres_18h = ($date_str === $demain && $heure_actuelle >= 18);

                                    // Condition complète :
                                    if (
                                        $today < $date_str &&
                                        $date_str > $date_debut_qualif &&
                                        $date_str < $date_fin_report &&
                                        !$report_demain_apres_18h
                                    ) {

                                    // Encodage dans l’URL (ou autre format)
                                    $url = "nouveau_creneau.php?jour=$jour&heure=$heure&partie=$partie_a_replacer&equipe_id=$equipe_id";

                                    echo "<a href=\"$url\" onclick=\"return confirm('Êtes-vous sûr de vouloir modifier ce créneau ?');\">";
                                    echo "<p>Prendre ce créneau</p>";
                                    echo "</a>";
                                }
                                }



                                    if (!empty($creneau_score)) {
                                        // echo '<p><a style="color:white;" href="./details_partie.php?partie=' . $creneau_partie . '">Score: ' . $creneau_score . '</a></p>'; // Afficher le score
                                    }
                                    echo '</a></div>';
                                }
                            }

                            echo '</div>';
                            $current_date->modify('+1 day');
                        }
                    ?>
                </div>
            </div>
        </main>
    </div>
<?php

// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------








?>





    </div>
</body>



</html>
