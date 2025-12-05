<?php

include './assets/conn_bdd.php';

$dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

// Récupérer les parties
$stmt = $pdo->query('SELECT partie FROM calendrier');
$matches = $stmt->fetchAll(PDO::FETCH_COLUMN);

$teams = [];
$duplicates = [];
$countMatches = [];

foreach ($matches as $match) {
    list($team1, $team2) = explode("/", $match);
    $key = "$team1/$team2";
    $reverseKey = "$team2/$team1";
    
    // Compter le nombre de matchs pour chaque équipe
    if (!isset($countMatches[$team1])) $countMatches[$team1] = 0;
    if (!isset($countMatches[$team2])) $countMatches[$team2] = 0;
    $countMatches[$team1]++;
    $countMatches[$team2]++;
    
    // Vérifier les doublons
    if (isset($teams[$key]) || isset($teams[$reverseKey])) {
        $duplicates[] = $match;
    } else {
        $teams[$key] = true;
    }
}

// Afficher les doublons
echo "<h3>Doublons trouvés :</h3>";
if (empty($duplicates)) {
    echo "<p>Aucun doublon trouvé.</p>";
} else {
    echo "<ul>";
    foreach ($duplicates as $dup) {
        echo "<li>$dup</li>";
    }
    echo "</ul>";
}

// Afficher le nombre de matchs par équipe
echo "<h3>Nombre de matchs par équipe :</h3>";
echo "<ul>";
foreach ($countMatches as $team => $count) {
    echo "<li>Équipe $team : $count matchs</li>";
}
echo "</ul>";

// Résumé du nombre de matchs par équipe
$summary = array_count_values($countMatches);
echo "<h3>Résumé :</h3>";
echo "<ul>";
foreach ($summary as $matchCount => $teamCount) {
    echo "<li>$teamCount équipes ont $matchCount matchs</li>";
}
echo "</ul>";

?>
