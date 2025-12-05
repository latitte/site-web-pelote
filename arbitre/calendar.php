<?php
include("../logiciel/assets/extract_parametre.php");

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

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
	<link rel="manifest" href="./assets/manifest.json">

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
    </style>
</head>
<body>
    <div class="container">
<?php
// Définir le fuseau horaire
date_default_timezone_set('Europe/Paris');

// Mois en français et leurs numéros correspondants
$mois_francais = [
    "Janvier" => 1,
    "Février" => 2,
    "Mars" => 3,
    "Avril" => 4,
    "Mai" => 5,
    "Juin" => 6,
    "Juillet" => 7,
    "Août" => 8,
    "Septembre" => 9,
    "Octobre" => 10,
    "Novembre" => 11,
    "Décembre" => 12
];

// Récupérer le mois et l'année à afficher
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year = date('Y');

// Si on sélectionne un mois plus petit que le mois actuel, passer à l'année suivante
if ($current_month < date('n')) {
    $current_year++;
}

// Récupérer le nom du mois actuel en français
$mois = array_search($current_month, $mois_francais);
?>

<h2>Calendrier des Parties</h2>
<h3><?php echo $mois . ' ' . $current_year; ?></h3>
        <div class="navigation">
            <?php
                foreach ($mois_bdd as $mois_nom) {
                    $mois_numero = $mois_francais[$mois_nom] ?? null;
                    if ($mois_numero) {
                        echo '<a href="?month=' . $mois_numero . '">' . $mois_nom . '</a>';
                    }
                }
            ?>
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
                    if (in_array($day_of_week_fr, $jours_disponibles)) {
                        echo '<li id="' . $current_date->format('Y-m-d') . '" class="date-list-item' . $is_today . '">';
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



                                echo '<p>' . $match['heure'] . '<br>' . $creneau_partie . '</p>';
                                if (!empty($creneau_score)) {
                                    echo '<p style="color:black;">'. $lang['score']. ': ' . $creneau_score . '</p>';
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


</html>