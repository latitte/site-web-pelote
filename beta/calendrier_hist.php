<?php
include("../logiciel/assets/extract_parametre.php");

// Paramètres
$jours_disponibles = explode(", ", $parametres['jours_dispo']);
$heures_dispo = explode(", ", $parametres['heures_dispo']);

// --- BACKUPS ---
$backup_dir = __DIR__ . "/../backup/backups/";
$files = glob($backup_dir . "*.sql");
$selected_sql = isset($_GET["sql"]) ? $_GET["sql"] : "";

// --- NAVIGATION MOIS ---
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year  = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

if ($current_month < 1) { $current_month = 12; $current_year--; }
if ($current_month > 12) { $current_month = 1; $current_year++; }

$mois_map = [
    1=>"Janvier",2=>"Février",3=>"Mars",4=>"Avril",5=>"Mai",6=>"Juin",
    7=>"Juillet",8=>"Août",9=>"Septembre",10=>"Octobre",11=>"Novembre",12=>"Décembre"
];
$mois = $mois_map[$current_month];

// -------------------------------------------
// CHARGEMENT DES MATCHS (BDD ou BACKUP SQL)
// -------------------------------------------
$matches = [];

if ($selected_sql !== "" && file_exists($backup_dir . $selected_sql)) {

    // MODE HISTORIQUE : LECTURE TOUT LE FICHIER SQL
    $content = file_get_contents($backup_dir . $selected_sql);

    preg_match_all('/INSERT INTO `calendrier` .*?\((.*?)\);/is', $content, $inserts);

    foreach ($inserts[1] as $values) {

        $vals = str_getcsv($values, ',', "'");

        // Respect ordre colonne backup
        list($id, $jours, $heure, $partie, $score, $niveau) = [
            trim($vals[0], "' "),
            trim($vals[1], "' "),
            trim($vals[2], "' "),
            trim($vals[3], "' "),
            trim($vals[4], "' "),
            trim($vals[5], "' ")
        ];

        if ($jours != "0000-00-00") {
            $matches[$jours][] = [
                "id"=>$id,
                "jours"=>$jours,
                "heure"=>$heure,
                "partie"=>$partie,
                "score"=>$score,
                "niveau"=>$niveau
            ];
        }
    }

} else {

    // MODE NORMAL (BDD)
    include("./assets/conn_bdd.php");
    $conn = new mysqli($servername, $username, $password, $dbname);

    $sql = "SELECT id, jours, heure, partie, score, niveau FROM calendrier";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $matches[$row["jours"]][] = $row;
    }
    $conn->close();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Calendrier</title>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

<style>
.day { padding:10px;text-align:center;border:1px solid #eee;font-weight:bold; }
.day.disabled { background:#ddd;color:#aaa; }
.date { border:1px solid #eee; min-height:130px; padding:4px; position:relative; width:14.28%; }
.match { background:#007bff;color:white;padding:4px;margin-top:5px;border-radius:4px;font-size:13px; }
.green { background:#28a745!important; }
.barrage { background:#89000d!important; }
.history-menu { background:white;padding:15px;border-radius:8px;margin:20px 0; }
</style>
</head>

<body>

<div class="container">

<!-- MENU FICHIERS SQL -->
<div class="history-menu">
    <form method="GET">
        <label><b>Fichier SQL :</b></label>
        <select name="sql" onchange="this.form.submit()">
            <option value="">Calendrier actuel (BDD)</option>

            <?php foreach ($files as $f): $name = basename($f); ?>
                <option value="<?= $name ?>" <?= ($selected_sql == $name ? "selected":"") ?>>
                    <?= $name ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="hidden" name="month" value="<?= $current_month ?>">
        <input type="hidden" name="year" value="<?= $current_year ?>">
    </form>
</div>

<!-- NAVIGATION -->
<div class="navigation d-flex justify-content-between mb-3">
    <a href="?month=<?= ($current_month==1?12:$current_month-1) ?>&year=<?= ($current_month==1?$current_year-1:$current_year) ?>&sql=<?= $selected_sql ?>">&laquo; Précédent</a>
    <h4><?= $mois ?> <?= $current_year ?></h4>
    <a href="?month=<?= ($current_month==12?1:$current_month+1) ?>&year=<?= ($current_month==12?$current_year+1:$current_year) ?>&sql=<?= $selected_sql ?>">Suivant &raquo;</a>
</div>

<!-- JOURS -->
<div class="d-flex">
<?php
$jours = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
foreach ($jours as $j) {
    $cls = in_array($j,$jours_disponibles) ? "day" : "day disabled";
    echo "<div class='$cls' style='width:14.28%;'>$j</div>";
}
?>
</div>

<!-- CALENDRIER -->
<div class="d-flex flex-wrap">

<?php
$start = new DateTime("$current_year-$current_month-01");
$end   = new DateTime($start->format("Y-m-t"));

// Espaces avant le premier jour
for ($i=1; $i < $start->format("N"); $i++) {
    echo "<div class='date'></div>";
}

$cur = clone $start;

while ($cur <= $end) {

    $date = $cur->format("Y-m-d");

    echo "<div class='date'>";
    echo "<b>".$cur->format("j")."</b>";

    if (isset($matches[$date])) {
        foreach ($matches[$date] as $m) {

            $cls = "match";
            if (!empty($m["score"])) $cls .= " green";
            if ($m["niveau"] == "2") $cls .= " barrage";

            echo "<div class='$cls'>
                    <a href='./details_partie.php?partie={$m['id']}' style='color:white;'>
                        {$m['heure']}<br>{$m['partie']}
                    </a>";

            if (!empty($m["score"])) echo "<br><b>{$m['score']}</b>";

            echo "</div>";
        }
    }

    echo "</div>";

    $cur->modify("+1 day");
}

?>
</div>

</div>

</body>
</html>
