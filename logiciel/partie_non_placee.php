<?php

// Inclure les paramètres et autres configurations
include("./assets/extract_parametre.php");

$heures_dispo = $parametres['heures_dispo'];
$heures_dispo = explode(", ", $heures_dispo);


include("./assets/conn_bdd.php");

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Requête pour récupérer toutes les parties possibles
$sql_matches = "SELECT t1.id AS team1_id, t2.id AS team2_id, t1.serie, t1.poule
                FROM inscriptions t1
                JOIN inscriptions t2 ON t1.serie = t2.serie 
                                     AND t1.poule = t2.poule 
                                     AND t1.id < t2.id
                WHERE t1.forfait = '0' AND t2.forfait = '0'
                ORDER BY t1.serie, t1.poule, t1.id, t2.id";


$result_matches = $conn->query($sql_matches);

$unplaced_matches = []; // Initialisation du tableau des parties non placées


if ($result_matches->num_rows > 0) {
    while ($row = $result_matches->fetch_assoc()) {
        $team1_id = $row['team1_id'];
        $team2_id = $row['team2_id'];
        $serie = $row['serie'];
        $poule = $row['poule'];
        $partie = $team1_id . "/" . $team2_id;

        // Vérifier si la partie est placée dans le calendrier
        $sql_check = "SELECT * FROM calendrier WHERE partie = '$partie'";
        $result_check = $conn->query($sql_check);

        if ($result_check->num_rows == 0) {
            // Partie non placée
            $unplaced_matches[] = array('team1_id' => $team1_id, 'team2_id' => $team2_id, 'serie' => $serie, 'poule' => $poule);
        }
    }
}

// Fermer la connexion MySQL
$conn->close();
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
            background-color: #ffffff;
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
        .match p {
            margin: 5px 0;
        }
        .match.reserved {
            background-color: #dc3545;
        }
        .d-flex {
        display: flex !important;
        flex-direction: column;
        }
    </style>
</head>
<body>
    <div class="d-flex">
<?php
include("./assets/menu.php");
?>
</head>
<body>
<div class="unplaced-matches" style="margin-left:20%; width:60%;">
    <?php if (count($unplaced_matches) > 0): ?>
        <h3>Parties non placées :</h3>

        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f2f2f2;">
                    <th style="padding:8px; border:1px solid #ccc;">Série</th>
                    <th style="padding:8px; border:1px solid #ccc;">Poule</th>
                    <th style="padding:8px; border:1px solid #ccc;">Match</th>
                    <th style="padding:8px; border:1px solid #ccc;">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($unplaced_matches as $match): ?>
                    <tr>
                        <td style="padding:8px; border:1px solid #ccc; text-align:center;">
                            <?= htmlspecialchars($match['serie']) ?>
                        </td>

                        <td style="padding:8px; border:1px solid #ccc; text-align:center;">
                            <?= htmlspecialchars($match['poule']) ?>
                        </td>

                        <td style="padding:8px; border:1px solid #ccc; text-align:center;">
                            <?= htmlspecialchars($match['team1_id']) ?> vs <?= htmlspecialchars($match['team2_id']) ?>
                        </td>

                        <td style="padding:8px; border:1px solid #ccc; text-align:center;">

                            <a href="./assets/edit_partie_implacee.php?team1=<?= urlencode($match['team1_id']) ?>&team2=<?= urlencode($match['team2_id']) ?>"style="display:inline-block; padding:6px 12px; background:#007bff; color:white; border-radius:4px; text-decoration:none;">
                                Modifier
                            </a>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>

    <?php else: ?>
        <p>Aucune partie non placée.</p>
    <?php endif; ?>
</div>


    <div class="form-container" style="display: none; margin-left:20%;">
        <h3>Choisir une date et un créneau horaire :</h3>
        <form id="placementForm" onsubmit="return placeMatch()">
            <input type="hidden" id="team1_id" name="team1_id">
            <input type="hidden" id="team2_id" name="team2_id">
            <input type="hidden" id="serie" name="serie">
            <input type="hidden" id="poule" name="poule">
            <label for="date">Date :</label>
            <input type="date" id="date" name="date" required>
            <br><br>
            <label for="heure">Heure :</label>
            <select id="heure" name="heure" required>
                <?php foreach ($heures_dispo as $heure) : ?>
                    <option value="<?php echo $heure; ?>"><?php echo $heure; ?></option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <button type="submit" id="submitBtn">Placer la partie</button>
        </form>
    </div>

    <script>
        function showForm(team1_id, team2_id, serie, poule) {
            document.getElementById('team1_id').value = team1_id;
            document.getElementById('team2_id').value = team2_id;
            document.getElementById('serie').value = serie;
            document.getElementById('poule').value = poule;
            document.querySelector('.form-container').style.display = 'block';
            document.getElementById('submitBtn').disabled = false; // Réinitialiser l'état du bouton
        }

        function placeMatch() {
            var form = document.getElementById('placementForm');
            var formData = new FormData(form);

            // Envoyer la requête AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "place_match.php", true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert(xhr.responseText); // Afficher la réponse (message de succès ou d'erreur)
                    if (xhr.responseText.includes("succès")) {
                        location.reload(); // Recharger la page pour mettre à jour le calendrier si nécessaire
                    }
                }
            };
            xhr.send(formData);

            return false; // Empêcher le formulaire de se soumettre normalement
        }
    </script>
</body>
</html>


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
        .main-content {
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
        <main class="main-content">
            <div class="calendar">
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
                                if (in_array($day_of_week_fr, $jours_disponibles)) {
                                    echo '<a style="color:white;" href="./assets/edit_partie_calendrier.php?id=' . $id_partie . '&heure=' . urlencode($creneau) . '&jour=' . urlencode($date_str) . '"><div class="' . $classes . '">';
                                    echo '<p>' . $creneau . '<br>' . $creneau_partie . '</p>';
                                    if (!empty($creneau_score)) {
                                        echo '<p>Score: ' . $creneau_score . '</p>'; // Afficher le score
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
