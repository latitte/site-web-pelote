<?php
include("../logiciel/assets/extract_parametre.php");

$jours_dispo_bdd = $parametres['jours_dispo'];
$jours_disponibles = explode(", ", $jours_dispo_bdd);

$heures_dispo_bdd = $parametres['heures_dispo'];
$heures_dispo = explode(", ", $heures_dispo_bdd);

$mois_bdd = $parametres['mois'];
$mois_bdd = explode(", ", $mois_bdd);
?>


<?php
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
<html lang="fr">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2, h3 {
            text-align: center;
            color: #333;
        }
        .navigation {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .navigation a {
            text-decoration: none;
            color: #007bff;
            font-weight: 700;
            padding: 10px;
            margin: 0 5px;
            border-radius: 4px;
        }
        .navigation a:hover {
            background-color: #e9ecef;
        }
        .date-list {
            list-style-type: none;
            padding: 0;
        }
        .date-list-item {
            border-bottom: 1px solid #e0e0e0;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            background-color: #f9f9f9;
        }
        .date-list-item h4 {
            margin: 0;
            font-weight: bold;
            color: #333;
        }
        .match {
            background-color: #007bff;
            color: #ffffff;
            padding: 5px;
            border-radius: 4px;
            font-size: 14px;
            margin-top: 5px;
        }
        .match.green {
            background-color: #28a745;
        }
        .match.barrage {
            background-color: #89000d;
        }
        .match p {
            margin: 0;
        }
        .day.disabled {
            background-color: #e0e0e0;
            color: #b0b0b0;
        }
        .today {
            border: 2px solid #007bff;
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
    </style>
</head>
<body>
    <div class="container">
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
        <ul class="date-list">
            <?php
                // Calculer la date de début et de fin du mois
                $start_date = new DateTime("$current_year-$current_month-01");
                $end_date = new DateTime("$current_year-$current_month-" . $start_date->format('t'));

                // Connexion à MySQL
                $conn = new mysqli($servername, $username, $password, $dbname);

                // Vérifier la connexion
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Requête pour récupérer les matchs du mois sélectionné
                $sql = "SELECT id, jours, heure, partie, score, niveau, ia FROM calendrier WHERE MONTH(jours) = $current_month";
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
                        'score' => $row['score'],
                        'niveau' => $row['niveau'],
                        'ia' => $row['ia']
                    ];
                }

                // Fermer la connexion à MySQL
                $conn->close();

                // Générer la liste des jours
                $current_date = clone $start_date;
                while ($current_date <= $end_date) {
                    $date_str = $current_date->format('Y-m-d');
                    $day_of_week = $current_date->format('D');
                    $day_of_week_fr = '';
                    switch ($day_of_week) {
                        case 'Mon': $day_of_week_fr = 'Lun'; break;
                        case 'Tue': $day_of_week_fr = 'Mar'; break;
                        case 'Wed': $day_of_week_fr = 'Mer'; break;
                        case 'Thu': $day_of_week_fr = 'Jeu'; break;
                        case 'Fri': $day_of_week_fr = 'Ven'; break;
                        case 'Sat': $day_of_week_fr = 'Sam'; break;
                        case 'Sun': $day_of_week_fr = 'Dim'; break;
                    }

                    // Déterminez si c'est aujourd'hui
                    $is_today = ($current_date->format('Y-m-d') == date('Y-m-d')) ? ' today' : '';

                    // Afficher uniquement les jours disponibles
$est_disponible = in_array($day_of_week_fr, $jours_disponibles);
$contient_match = isset($matches[$date_str]) && count($matches[$date_str]) > 0;

// Classe de style (grisée si indisponible)
$classes_li = 'date-list-item' . $is_today;
if (!$est_disponible) {
    $classes_li .= ' day disabled';
}

// Affiche la journée si elle est dispo OU contient un match
if ($est_disponible || $contient_match) {
    echo '<li id="' . $current_date->format('Y-m-d') . '" class="' . $classes_li . '">';

                        echo '<h4>' . $current_date->format('j') . ' ' . $day_of_week_fr . '</h4>';

                        // Trier les heures pour chaque jour
                        if (isset($matches[$date_str])) {
                            usort($matches[$date_str], function($a, $b) {
                                return strcmp($a['heure'], $b['heure']);
                            });

                            foreach ($matches[$date_str] as $match) {
                                $creneau_partie = $match['partie'];
                                $creneau_score = $match['score'] ?? '';
                                $id_partie = $match['id'];
                                $niveau = $match['niveau'];
                                $ia = $match['ia'];

                                // Déterminer les classes à appliquer
                                $classes = 'match';
                                if (!empty($creneau_partie)) {
                                    $classes .= ' reserved';
                                    if (!empty($creneau_score)) {
                                        $classes .= ' green';
                                    }
                                }
                                if ($niveau == "2") {
                                    $classes .= ' barrage';
                                }

                                // Extraire la partie numérique uniquement
                                $niveau_numeric = preg_replace('/\D/', '', $niveau);

                                if ($niveau_numeric !== null) {
                                    if (in_array($niveau_numeric, ['1'])) {
                                        $partie_niveau = "";
                                    } elseif (in_array($niveau_numeric, ['2'])) {
                                        $partie_niveau = "Barrage";
                                    } elseif (in_array($niveau_numeric, ['31', '32', '33', '34', '35', '36', '37', '38'])) {
                                        $partie_niveau = "1/8 de finales";
                                    } elseif (in_array($niveau_numeric, ['41', '42', '43', '44'])) {
                                        $partie_niveau = "1/4 de finales";
                                    } elseif (in_array($niveau_numeric, ['51', '52'])) {
                                        $partie_niveau = "1/2 finales";
                                    } elseif ($niveau_numeric == '60') {
                                        $partie_niveau = "Finale";
                                    }
                                }

                                // Afficher le créneau si défini
                                if (isset($partie_niveau)) {
                                    echo '<p>' . $partie_niveau . '</p>';
                                } else {
                                    echo "<p>Aucun niveau correspondant trouvé.</p>";
                                }

                                echo '<a><div class="' . $classes . '">';


                                if ($ia == 1) {
                                    echo '<p style="color:black;"><i class="fa-solid fa-microchip"></i></p>';
                            }



                                echo '<p><a style="color:white;" href="./details_partie.php?partie=' . $id_partie . '" target="_top">' . $match['heure'] . '<br>' . $creneau_partie . '</a></p>';
                                if (!empty($creneau_score)) {
                                    echo '<p style="color:black;"><a style="color:white;" href="./details_partie.php?partie=' . $id_partie . '" target="_top">'. $lang['score']. ': ' . $creneau_score . '</a></p>';
                                }
                                echo '</div></a>';
                            }
                        } else {
                            echo '<p>Aucun match prévu.</p>';
                        }

                        echo '</li>';
                    }

                    $current_date->modify('+1 day');
                }
            ?>
        </ul>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var today = new Date().toISOString().split('T')[0];
            var element = document.getElementById(today);
            if (element) {
                var elementRect = element.getBoundingClientRect();
                var elementTop = elementRect.top + window.pageYOffset;
                var elementHeight = elementRect.height;
                var windowHeight = window.innerHeight;

                var scrollPosition = elementTop - (windowHeight / 2) + (elementHeight / 2);

                window.scrollTo({
                    top: scrollPosition,
                    behavior: 'smooth'
                });
            }
        });
    </script>
</body>


<footer>

<?php include("./assets/footer.php"); ?>


</footer>
</html>