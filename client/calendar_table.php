<?php
include("../logiciel/assets/extract_parametre.php");

// Récupére la valeur max d'équipe dans chaque série
$jours_dispo_bdd = $parametres['jours_dispo'];
$jours_disponibles = explode(", ", $jours_dispo_bdd);


$heures_dispo_bdd = $parametres['heures_dispo'];
$heures_dispo = explode(", ", $heures_dispo_bdd);

$mois_bdd = $parametres['mois'];
$mois_bdd = explode(", ", $mois_bdd);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier des Parties</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
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
    <a href="?month=<?php echo ($current_month == 1) ? 12 : $current_month - 1; ?>&year=<?php echo ($current_month == 1) ? $current_year - 1 : $current_year; ?>" class="prev" style="color:black;">
        &laquo; Précédent
    </a>
    <span><?php echo $mois; ?> <?php echo $current_year; ?></span>
    <a href="?month=<?php echo ($current_month == 12) ? 1 : $current_month + 1; ?>&year=<?php echo ($current_month == 12) ? $current_year + 1 : $current_year; ?>" class="next" style="color:black;">
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
                            $afficher_date = in_array($day_of_week_fr, $jours_disponibles);

                            // Forcer l'affichage si des matchs existent ce jour-là
                            if (isset($matches[$date_str]) && count($matches[$date_str]) > 0) {
                                $afficher_date = true;
                            }

                            if ($afficher_date) {
                                echo '<div class="date" style="background-color:white!important; display:block!important;"><span>' . $current_date->format('j') . '</span>';
                            } else {
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

                                // Vérifier s'il y a un match pour ce créneau à cette date
                                if (isset($matches[$date_str])) {
                                    foreach ($matches[$date_str] as $match) {
                                        if ($match['heure'] == $creneau) {
                                            $creneau_partie = $match['partie'];
                                            $id_partie = $match['id'];
                                            $creneau_score = isset($match['score']) ? $match['score'] : ''; // Ajouter le score s'il existe
                                            $niveau = $match['niveau'];
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

                                if($niveau < 6){
                                if ($niveau == "2") {
                                    $classes .= ' reserved';
                                    $classes .= ' barrage'; // Ajouter la classe verte si le score est présent
                                }

                                // Afficher le créneau avec la classe appropriée seulement si le jour est disponible
                                $est_dispo = in_array($day_of_week_fr, $jours_disponibles);
                                $contient_match_a_ce_creneau = !empty($creneau_partie);

                                if ($est_dispo || $contient_match_a_ce_creneau) {
                                    echo '<a style="color:white;"><div class="' . $classes . '">';
                                    echo '<p>' . $creneau . '<br><a style="color:white;" href="./details_partie.php?partie=' . $id_partie . '" target="_top">' . $creneau_partie . '</a></p>';
                                    if (!empty($creneau_score)) {
                                        echo '<p><a style="color:white;" href="./details_partie.php?partie=' . $id_partie . '" target="_top">Score: ' . $creneau_score . '</a></p>';
                                    }
                                    echo '</a></div>';
                                }

                            }else{

                                if ($est_dispo || $contient_match_a_ce_creneau) {
                                    echo '<a style="color:white;"><div class="' . $classes . '">';
                                    echo '<p>' . $creneau . '<br><a style="color:white;" href="./details_partie.php?partie=' . $id_partie . '" target="_top">' . $creneau_partie . '</a></p>';
                                    if (!empty($creneau_score)) {
                                        echo '<p><a style="color:white;" href="./details_partie.php?partie=' . $id_partie . '" target="_top">Score: ' . $creneau_score . '</a></p>';
                                    }
                                    echo '</a></div>';
                                }

                            }
                                
                            }
                            // Afficher les autres matchs non inclus dans les créneaux possibles
                    if (isset($matches[$date_str])) {
                        foreach ($matches[$date_str] as $match) {
                            if (!in_array($match['heure'], $creneaux_possibles)) {
                                $classes = 'match reserved';
                                if (!empty($match['score'])) {
                                    $classes .= ' green';
                                }
                                if ($match['niveau'] == "2") {
                                    $classes .= ' barrage';
                                }

                                echo '<a style="color:white;"><div class="' . $classes . '">';
                                echo '<p>' . $match['heure'] . '<br><a style="color:white;" href="./details_partie.php?partie=' . $match['id'] . '" target="_top">' . $match['partie'] . '</a></p>';
                                if (!empty($match['score'])) {
                                    echo '<p><a style="color:white;" href="./details_partie.php?partie=' . $match['id'] . '" target="_top">Score: ' . $match['score'] . '</a></p>';
                                }
                                echo '</a></div>';
                            }
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".date").forEach(dateDiv => {
    const matchDivs = Array.from(dateDiv.querySelectorAll(".match"));

    matchDivs.sort((a, b) => {
      const getHeure = (div) => {
        const text = div.textContent.trim().match(/\d{1,2}h\d{2}/);
        if (!text) return 0;
        const [h, m] = text[0].split("h").map(x => parseInt(x));
        return h * 60 + m;
      };

      return getHeure(a) - getHeure(b);
    });

    matchDivs.forEach(div => dateDiv.appendChild(div));
  });
});
</script>




</body>
</html>
