<?php
include("./assets/extract_parametre.php");

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
        .container {
            margin-left: 250px;
            padding: 20px;
        }
        .calendar {
            background-color: #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
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
        }
        .match {
            position: relative;
            background-color: #007bff;
            padding: 5px;
            margin: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            color: #ffffff;
            text-align: center;
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
        }




    @media (max-width: 768px) {
        .calendar {
        background-color: #ffffff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        width: 200%;
        }
    }


    </style>
</head>
<body>
    <div class="d-flex">
        <!-- sidebar -->
        <?php include("./assets/menu.php"); ?>

        <?php
            // Définir le fuseau horaire
            date_default_timezone_set('Europe/Paris');

            // Récupérer le mois à afficher
            $current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
            $current_year = date('Y');
			
			// Récupérer le mois et l'année à afficher
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year = date('Y');

// Si on sélectionne un mois plus petit que le mois actuel, passer à l'année suivante
if ($current_month < date('n')) {
    $current_year++;
}


            if ($current_month == 1) {
                $mois = "Janvier";
            } elseif ($current_month == 2) {
                $mois = "Février";
            } elseif ($current_month == 3) {
                $mois = "Mars";
            } elseif ($current_month == 4) {
                $mois = "Avril";
            } elseif ($current_month == 5) {
                $mois = "Mai";
            } elseif ($current_month == 6) {
                $mois = "Juin";
            } elseif ($current_month == 7) {
                $mois = "Juillet";
            } elseif ($current_month == 8) {
                $mois = "Août";
            } elseif ($current_month == 9) {
                $mois = "Septembre";
            } elseif ($current_month == 10) {
                $mois = "Octobre";
            } elseif ($current_month == 11) {
                $mois = "Novembre";
            } elseif ($current_month == 12) {
                $mois = "Décembre";
            } else {
                $mois = "Mois invalide";
            }


        ?>

        <!-- Contenu principal -->
        <main class="container">
            <div class="calendar">


<a href="./assets/edit_partie_calendrier.php" class="btn-ajout-partie">➕ Ajouter une Partie</a>

<style>
.btn-ajout-partie {
    display: inline-block;
    background-color: #007aff;
    color: #fff;
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    margin-bottom: 20px;
    transition: background-color 0.3s ease;
}

.btn-ajout-partie:hover {
    background-color: #005bb5;
}
</style>




                <h2>Calendrier des Parties</h2>
                <h3><?php echo $mois; ?></h3>

<?php
                // Tableau pour mapper les noms des mois aux numéros de mois
$mois_num_map = [
    'Janvier' => 1,
    'Février' => 2,
    'Mars' => 3,
    'Avril' => 4,
    'Mai' => 5,
    'Juin' => 6,
    'Juillet' => 7,
    'Août' => 8,
    'Septembre' => 9,
    'Octobre' => 10,
    'Novembre' => 11,
    'Décembre' => 12
];


?>

<div class="navigation">
    <?php foreach ($mois_bdd as $month_name): ?>
        <a href="?month=<?php echo $mois_num_map[$month_name]; ?>"><?php echo $month_name; ?></a>
    <?php endforeach; ?>
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
                            echo '<div class="date" style="background-color:white!important;"><span>' . $current_date->format('j') . '</span>';
                            }else{
                                echo '<div class="date"><span>' . $current_date->format('j') . '</span>';
                            }
                            $creneau_exeptionnel = 1;
                            // Vérifier chaque créneau possible
                            foreach ($creneaux_possibles as $creneau) {
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

                                
                                if ($niveau == "2") {
                                    $classes .= ' reserved';
                                    $classes .= ' barrage'; // Ajouter la classe verte si le score est présent
                                }

                                // Afficher le créneau avec la classe appropriée seulement si le jour est disponible
                                $est_dispo = in_array($day_of_week_fr, $jours_disponibles);
                                $contient_match_a_ce_creneau = !empty($creneau_partie);

                                if ($est_dispo || $contient_match_a_ce_creneau) {
                                    echo '<a style="color:white;" href="./assets/edit_partie_calendrier.php?id=' . $id_partie . '&heure=' . urlencode($creneau) . '&jour=' . urlencode($date_str) . '"><div class="' . $classes . '">';
                                    echo '<p>' . $creneau . '<br><a style="color:white;" href="./assets/edit_partie_calendrier.php?id=' . $id_partie . '&heure=' . urlencode($creneau) . '&jour=' . urlencode($date_str) . '">' . $creneau_partie . '</a></p>';
                                    if (!empty($creneau_score)) {
                                        echo '<p><a style="color:white;" href="./assets/edit_partie_calendrier.php?id=' . $id_partie . '&heure=' . urlencode($creneau) . '&jour=' . urlencode($date_str) . '">Score: ' . $creneau_score . '</a></p>';
                                    }
                                    echo '</a></div>';
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

            $id_match = $match['id'];
            $heure_match = urlencode($match['heure']);
            $jour_match = urlencode($date_str);
            $partie = htmlspecialchars($match['partie'] ?? '');
            $score = htmlspecialchars($match['score'] ?? '');

            echo '<div class="' . $classes . '">';
            echo '<a style="color:white;" href="./assets/edit_partie_calendrier.php?id=' . $id_match . '&heure=' . $heure_match . '&jour=' . $jour_match . '">';

            echo '<p>' . $match['heure'] . '<br>' . $partie . '</p>';
            if (!empty($score)) {
                echo '<p>Score: ' . $score . '</p>';
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
