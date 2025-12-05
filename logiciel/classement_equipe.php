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

// Récupére la valeur max d'équipe dans chaque série
$series = $parametres['series'];
$series = explode(",", $series);

?>

    <?php include("./assets/menu.php"); ?>
    <div class="container">
        <h1 class="text-center mt-5 mb-4">Classement des équipes par série</h1>


        <form method="POST" action="">
            <button type="submit" name="save_ranking" class="btn btn-primary mt-4">Enregistrer le classement</button>
        </form>
        
        <?php
        // Configuration de la connexion à la base de données
        include("./assets/conn_bdd.php");

        // Créer la connexion
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Vérifier la connexion
        if ($conn->connect_error) {
            die("La connexion a échoué: " . $conn->connect_error);
        }

        // Récupérer les scores de la base de données
        // $sql = "SELECT partie, score FROM calendrier WHERE score IS NOT NULL";
        $sql = "SELECT partie, score FROM calendrier";
        $result = $conn->query($sql);

        $teams = [];

        // Analyser les scores et calculer les points, victoires, et défaites
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                if($row['score'] == NULL){
                    $score_apres_modif = "0/0";
                }else{
                    $score_apres_modif = $row['score'];
                }

                list($team1, $team2) = explode('/', $row['partie']);
                list($score1, $score2) = explode('/', $score_apres_modif);

                // S'assurer que les scores et les équipes sont bien définis
                if (isset($team1, $team2, $score1, $score2)) {
                    $team1_score = intval($score1);
                    $team2_score = intval($score2);

                    if (!isset($teams[$team1])) {
                        $teams[$team1] = ['points' => 0, 'goal_average' => 0, 'players' => '', 'serie' => '', 'matches_played' => 0, 'wins' => 0, 'losses' => 0];
                    }
                    if (!isset($teams[$team2])) {
                        $teams[$team2] = ['points' => 0, 'goal_average' => 0, 'players' => '', 'serie' => '', 'matches_played' => 0, 'wins' => 0, 'losses' => 0];
                    }

                    // Calcul des points, du goal average, des victoires et des défaites
                    if ($team1_score > $team2_score) {
                        $teams[$team1]['points'] += 1;
                        $teams[$team1]['wins'] += 1;
                        $teams[$team2]['losses'] += 1;
                        // $teams[$team2]['points'] -= 1;
                    } else if ($team2_score > $team1_score) {
                        $teams[$team2]['points'] += 1;
                        $teams[$team2]['wins'] += 1;
                        $teams[$team1]['losses'] += 1;
                        // $teams[$team1]['points'] -= 1;
                    }

                    $teams[$team1]['goal_average'] += ($team1_score - $team2_score);
                    $teams[$team2]['goal_average'] += ($team2_score - $team1_score);

                    // Incrémenter le nombre de matchs joués par équipe
                    $teams[$team1]['matches_played'] += 1;
                    $teams[$team2]['matches_played'] += 1;
                }
            }
        }

        // Récupérer les noms des joueurs et la série pour chaque équipe
        foreach ($teams as $team_id => $team_data) {
            $sql = "SELECT `Joueur 1`, `Joueur 2`, serie FROM inscriptions WHERE id = '$team_id' and forfait = '0'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $teams[$team_id]['players'] = $row['Joueur 1'] . ' et ' . $row['Joueur 2'];
                $teams[$team_id]['serie'] = $row['serie'];

                // Appliquer le coefficient correctif basé sur le nombre de matchs joués
                if ($teams[$team_id]['matches_played'] == 4) {
                    $teams[$team_id]['points'] = ceil($teams[$team_id]['points'] * 2.5); // Poule de 5 équipes
                    $teams[$team_id]['coefficient'] = 2.5;
                } elseif ($teams[$team_id]['matches_played'] == 3) {
                    $teams[$team_id]['points'] = ceil($teams[$team_id]['points'] * (10/3)); // Poule de 4 équipes
                    $teams[$team_id]['coefficient'] = round(10/3, 2); // Pour afficher un coefficient arrondi
                }
                
            } else {
                $teams[$team_id]['players'] = 'Inconnu'; // Gestion des cas où les informations des joueurs ne sont pas trouvées
                $teams[$team_id]['serie'] = 'Inconnu'; // Gestion des cas où la série n'est pas trouvée
            }
        }



        // Organiser les équipes par série dans l'ordre spécifié
        // $serie_order = ['Première série', 'Deuxième série', 'Troisième série', 'Féminine', 'Mixte'];

        $serie_order = $series;
        $sorted_teams_by_serie = [];

        // Initialiser les tableaux par série
        foreach ($serie_order as $serie) {
            $sorted_teams_by_serie[$serie] = [];
        }

        // Remplir les tableaux avec les équipes appropriées
        foreach ($teams as $team_id => $team_data) {
            $serie = $team_data['serie'];
            if (in_array($serie, $serie_order)) {
                $sorted_teams_by_serie[$serie][$team_id] = $team_data;
            }
        }

        // Trier les équipes par points puis par goal average pour chaque série
        foreach ($sorted_teams_by_serie as &$serie_teams) {
            uasort($serie_teams, function($a, $b) {
                if ($a['points'] == $b['points']) {
                    return $b['goal_average'] <=> $a['goal_average'];
                }
                return $b['points'] <=> $a['points'];
            });
        }

        // Si le formulaire est soumis
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_ranking'])) {
            // Boucle pour insérer les données du classement dans la table 'classement'
            foreach ($sorted_teams_by_serie as $serie => $teams) {
                $place = 1;
                foreach ($teams as $team_id => $team_data) {
                    $points = $team_data['points'];
                    $coefficient = $team_data['coefficient'];
                    echo $coefficient;

                    $goal_average = $team_data['goal_average'];
                    $players = $team_data['players'];
                    $serie = $team_data['serie'];

                    // Requête d'insertion ou de mise à jour
                    $sql = "INSERT INTO classement (id, place, equipe, joueurs, points, average, serie)
                            VALUES ('$team_id', '$place', '$team_id', '$players', '$points', '$goal_average', '$serie')
                            ON DUPLICATE KEY UPDATE place='$place', points='$points', average='$goal_average', joueurs='$players', serie='$serie'";

                    if ($conn->query($sql) === TRUE) {
                        echo "Classement de l'équipe $team_id mis à jour avec succès.<br>";
                    } else {
                        echo "Erreur: " . $sql . "<br>" . $conn->error;
                    }
                    $place++;
                }
            }
        }

// Afficher le classement pour chaque série dans l'ordre spécifié
foreach ($serie_order as $serie) {
    if (!empty($sorted_teams_by_serie[$serie])) {
        echo "<div class='table-responsive'>";
        echo "<table class='table table-bordered table-striped'>";
        echo "<caption>Classement de la série {$serie}</caption>";
        echo "<thead class='thead-light'>";
        echo "<tr><th scope='col'>#</th><th scope='col'>Équipe</th><th scope='col'>Joueurs</th><th scope='col'>Points</th><th scope='col'>Victoires</th><th scope='col'>Défaites</th><th scope='col'>Goal Average</th></tr>";
        echo "</thead>";
        echo "<tbody>";
        
        $place = 1;
        foreach ($sorted_teams_by_serie[$serie] as $team => $data) {
            echo "<tr>";
            echo "<td>{$place}</td>";
            echo "<td>{$team}</td>";
            echo "<td>{$data['players']}</td>";
            echo "<td>{$data['points']}</td>";
            echo "<td>{$data['wins']}</td>";
            echo "<td>{$data['losses']}</td>";
            echo "<td>{$data['goal_average']}</td>";
            // echo "<td>{$data['coefficient']}</td>";
            echo "</tr>";
            $place++;
        }
        
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    }
}


        ?>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
