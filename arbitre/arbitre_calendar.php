<?php
// Inclure les paramètres et autres configurations
include("../logiciel/assets/extract_parametre.php");

$jours_dispo_bdd = $parametres['jours_dispo'];
$jours_disponibles = explode(", ", $jours_dispo_bdd);

$mois_bdd = $parametres['mois'];
$mois_bdd = explode(", ", $mois_bdd);

/* ----------------------------------------
   PLAGES DE COULEURS DYNAMIQUES
   Chargées depuis parametre.zone_arbitre
---------------------------------------- */

// Connexion déjà ouverte : $conn
/* ----------------------------------------
   Connexion à la base de données
---------------------------------------- */

// Les identifiants viennent de conn_bdd.php
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}


/* ----------------------------------------
   Chargement dynamique des zones d’arbitrage
---------------------------------------- */

$sql = "SELECT valeur FROM parametre WHERE parametre = 'zone_arbitre' LIMIT 1";
$result = $conn->query($sql);

$plages_couleurs = [];

if ($result) {

    $row = $result->fetch_assoc();

    if ($row && !empty($row['valeur'])) {

        // Décodage JSON venant de edit_accueil.php
        $zones = json_decode($row['valeur'], true);

        if (is_array($zones)) {

            foreach ($zones as $z) {
                $plages_couleurs[] = [
                    'start' => $z['debut'],
                    'end'   => $z['fin'],
                    'color' => $z['couleur'],
                    'label' => $z['libelle'] // facultatif
                ];
            }
        }
    }
}



/* ----------------------------------------
   Fonction : Retourne la couleur d'un jour
---------------------------------------- */
function getColorForDate($date, $plages) {
    foreach ($plages as $p) {
        if ($date >= $p['start'] && $date <= $p['end']) {
            return $p['color'];
        }
    }
    return null;
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
            background-color: rgb(43 98 38 / 79%);
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
        .navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px 0;
        }
        .navigation a {
            text-decoration: none;
            color: #007bff;
            font-weight: 700;
            padding: 5px 10px;
        }
        .navigation span {
            font-weight: bold;
            font-size: 1.2em;
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
            transition: background-color 0.2s ease;
        }
        .arbitre {
            background-color: #007bff57;
            color: white;
            padding: 5px;
            border-radius: 4px;
            margin-top: 5px;
        }
        #backToIndexBtn {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            cursor: pointer;
            z-index: 1000;
        }
    </style>
</head>
<body>

<button id="backToIndexBtn" onclick="window.location.href='index.php';">Retour</button>

<div class="container">
<?php
// Calculer le mois et l'année
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year  = isset($_GET['year'])  ? intval($_GET['year'])  : date('Y');

// Corriger les dépassements
if ($current_month < 1) { 
    $current_month = 12; 
    $current_year--; 
}
if ($current_month > 12) { 
    $current_month = 1; 
    $current_year++; 
}

// Noms des mois
$mois_map = [
    1=>"Janvier",2=>"Février",3=>"Mars",4=>"Avril",5=>"Mai",6=>"Juin",
    7=>"Juillet",8=>"Août",9=>"Septembre",10=>"Octobre",11=>"Novembre",12=>"Décembre"
];
?>

<!-- Navigation -->
<div class="navigation">
    <a href="?month=<?php echo ($current_month==1?12:$current_month-1); ?>&year=<?php echo ($current_month==1?$current_year-1:$current_year); ?>">&laquo; Précédent</a>
    <span><?php echo $mois_map[$current_month] . ' ' . $current_year; ?></span>
    <a href="?month=<?php echo ($current_month==12?1:$current_month+1); ?>&year=<?php echo ($current_month==12?$current_year+1:$current_year); ?>">Suivant &raquo;</a>
</div>

<ul class="date-list">
<?php
$start_date = new DateTime("$current_year-$current_month-01");
$end_date   = new DateTime("$current_year-$current_month-" . $start_date->format('t'));

// Charger arbitres
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}
$sql = "SELECT id, prenom, tel, permanence FROM arbitre";
$result = $conn->query($sql);

$arbitres = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        foreach (explode(", ", $row['permanence']) as $d) {
            $d = trim($d);
            if (!isset($arbitres[$d])) $arbitres[$d] = [];
            $arbitres[$d][] = $row;
        }
    }
}
$conn->close();

// Boucle jours
$current_date = clone $start_date;
while ($current_date <= $end_date) {

    $date_str = $current_date->format('Y-m-d');
    $day = $current_date->format('D');
    $map_jours = [
        'Mon' => 'Lun',
        'Tue' => 'Mar',
        'Wed' => 'Mer',
        'Thu' => 'Jeu',
        'Fri' => 'Ven',
        'Sat' => 'Sam',
        'Sun' => 'Dim'
    ];
    $fr = isset($map_jours[$day]) ? $map_jours[$day] : $day;

    if (in_array($fr, $jours_disponibles)) {

        // Couleur du jour
        $color = getColorForDate($date_str, $plages_couleurs);
        $style = $color ? 'style="background-color: '.$color.' !important;"' : '';

        echo '<li '.$style.' id="'.$date_str.'" class="date-list-item">';
        echo '<a href="modifier_jour.php?date='.$date_str.'">';
        echo '<h4>'.$current_date->format('j').' '.$fr.'</h4>';

        if (isset($arbitres[$date_str])) {
            foreach ($arbitres[$date_str] as $arb) {
                if ($arb['prenom']=="NE PAS ARBITRER") {
                    echo '<div class="arbitre" style="background-color:red;"><p>'.$arb['prenom'].'</p></div>';
                } else {
                    echo '<div class="arbitre"><p>'.$arb['prenom'].'</p></div>';
                }
            }
        } else {
            echo '<p>Aucun arbitre prévu.</p>';
        }

        echo '</a>';
        echo '</li>';
    }

    $current_date->modify('+1 day');
}
?>
</ul>

</div>
</body>
</html>
