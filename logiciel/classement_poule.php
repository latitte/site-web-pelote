<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classement des équipes par série</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 20px;
        }
        .table {
            margin-top: 20px;
        }
        .table thead th {
            background-color: #f8f9fa;
            border-top: 0;
            border-bottom: 0;
            font-weight: bold;
        }
        .table th, .table td {
            border: 0;
            vertical-align: middle;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<?php
include("./assets/extract_parametre.php");
$series = explode(",", $parametres['series']);

include("./assets/menu.php");
include("./assets/conn_bdd.php");
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connexion échouée: " . $conn->connect_error);
?>

<div class="container">
    <h1 class="text-center mt-5 mb-4">Classement des équipes</h1>

    <form method="POST" action="">
        <button type="submit" name="save_ranking" class="btn btn-primary mt-4">Enregistrer le classement</button>
    </form>

<?php
$sql = "SELECT partie, score FROM calendrier";
$result = $conn->query($sql);
$teams = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $score = $row['score'] ?? "0/0";
        list($team1, $team2) = explode('/', $row['partie']);
        list($score1, $score2) = explode('/', $score);

        foreach ([$team1, $team2] as $team) {
            if (!isset($teams[$team])) {
                $teams[$team] = ['points' => 0, 'goal_average' => 0, 'players' => '', 'serie' => '', 'poule' => '', 'matches_played' => 0, 'wins' => 0, 'losses' => 0];
            }
        }

        if ($score1 > $score2) {
            $teams[$team1]['points']++;
            $teams[$team1]['wins']++;
            $teams[$team2]['losses']++;
        } elseif ($score2 > $score1) {
            $teams[$team2]['points']++;
            $teams[$team2]['wins']++;
            $teams[$team1]['losses']++;
        }

        $teams[$team1]['goal_average'] += ($score1 - $score2);
        $teams[$team2]['goal_average'] += ($score2 - $score1);

        $teams[$team1]['matches_played']++;
        $teams[$team2]['matches_played']++;
    }
}

foreach ($teams as $team_id => $data) {
    $sql = "SELECT `Joueur 1`, `Joueur 2`, serie, poule FROM inscriptions WHERE id = '$team_id' AND forfait = '0'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $teams[$team_id]['players'] = $row['Joueur 1'] . ' et ' . $row['Joueur 2'];
        $teams[$team_id]['serie'] = $row['serie'];
        $teams[$team_id]['poule'] = $row['poule'];

        if ($teams[$team_id]['matches_played'] == 4) {
            $teams[$team_id]['points'] = ceil($teams[$team_id]['points'] * 2.5);
            $teams[$team_id]['coefficient'] = 2.5;
        } elseif ($teams[$team_id]['matches_played'] == 3) {
            $teams[$team_id]['points'] = ceil($teams[$team_id]['points'] * (10/3));
            $teams[$team_id]['coefficient'] = round(10/3, 2);
        }
    }
}

// === Classement général par série ===
$sorted_teams_by_serie = [];
foreach ($series as $serie) $sorted_teams_by_serie[$serie] = [];

foreach ($teams as $id => $data) {
    if (in_array($data['serie'], $series)) {
        $sorted_teams_by_serie[$data['serie']][$id] = $data;
    }
}

foreach ($sorted_teams_by_serie as &$serie_teams) {
    uasort($serie_teams, function($a, $b) {
        return ($a['points'] == $b['points']) ? $b['goal_average'] <=> $a['goal_average'] : $b['points'] <=> $a['points'];
    });
}

// === Classement par poule ===
$sorted_teams_by_serie_poule = [];
foreach ($teams as $id => $data) {
    $serie = $data['serie'];
    $poule = $data['poule'];
    $sorted_teams_by_serie_poule[$serie][$poule][$id] = $data;
}

foreach ($sorted_teams_by_serie_poule as &$series_data) {
    foreach ($series_data as &$poule_teams) {
        uasort($poule_teams, function($a, $b) {
            return ($a['points'] == $b['points']) ? $b['goal_average'] <=> $a['goal_average'] : $b['points'] <=> $a['points'];
        });
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_ranking'])) {
    foreach ($sorted_teams_by_serie as $serie => $teams) {
        $place = 1;
        foreach ($teams as $team_id => $data) {
            $sql = "INSERT INTO classement (id, place, equipe, joueurs, points, average, serie)
                    VALUES ('$team_id', '$place', '$team_id', '{$data['players']}', '{$data['points']}', '{$data['goal_average']}', '$serie')
                    ON DUPLICATE KEY UPDATE place='$place', points='{$data['points']}', average='{$data['goal_average']}', joueurs='{$data['players']}', serie='$serie'";
            $conn->query($sql);
            $place++;
        }
    }

    foreach ($sorted_teams_by_serie_poule as $serie => $poules) {
        foreach ($poules as $poule => $teams) {
            $place = 1;
            foreach ($teams as $team_id => $data) {
                $sql = "INSERT INTO classement_poule (id, place, equipe, joueurs, points, average, serie, niveau)
                        VALUES ('$team_id', '$place', '$team_id', '{$data['players']}', '{$data['points']}', '{$data['goal_average']}', '$serie', '$poule')
                        ON DUPLICATE KEY UPDATE place='$place', points='{$data['points']}', average='{$data['goal_average']}', joueurs='{$data['players']}', serie='$serie', niveau='$poule'";
                $conn->query($sql);
                $place++;
            }
        }
    }
}

// === Affichage du classement général par série ===
foreach ($series as $serie) {
    if (!empty($sorted_teams_by_serie[$serie])) {
        echo "<h2 class='mt-5'>Classement général - Série : $serie</h2>";
        echo "<div class='table-responsive'><table class='table table-bordered table-striped'>";
        echo "<thead class='thead-light'><tr><th>#</th><th>Équipe</th><th>Joueurs</th><th>Points</th><th>Victoires</th><th>Défaites</th><th>Goal Average</th></tr></thead><tbody>";
        $place = 1;
        foreach ($sorted_teams_by_serie[$serie] as $id => $data) {
            echo "<tr><td>$place</td><td>$id</td><td>{$data['players']}</td><td>{$data['points']}</td><td>{$data['wins']}</td><td>{$data['losses']}</td><td>{$data['goal_average']}</td></tr>";
            $place++;
        }
        echo "</tbody></table></div>";
    }
}

// === Affichage du classement par poule ===
foreach ($series as $serie) {
    if (!empty($sorted_teams_by_serie_poule[$serie])) {
        echo "<h2 class='mt-5'>Classement par poules - Série : $serie</h2>";
        foreach ($sorted_teams_by_serie_poule[$serie] as $poule => $teams) {
            echo "<div class='table-responsive'><table class='table table-bordered table-striped'>";
            echo "<caption>Poule $poule</caption>";
            echo "<thead class='thead-light'><tr><th>#</th><th>Équipe</th><th>Joueurs</th><th>Points</th><th>Victoires</th><th>Défaites</th><th>Goal Average</th></tr></thead><tbody>";
            $place = 1;
            foreach ($teams as $id => $data) {
                echo "<tr><td>$place</td><td>$id</td><td>{$data['players']}</td><td>{$data['points']}</td><td>{$data['wins']}</td><td>{$data['losses']}</td><td>{$data['goal_average']}</td></tr>";
                $place++;
            }
            echo "</tbody></table></div>";
        }
    }
}
?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
