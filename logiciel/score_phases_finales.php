<?php
include("./assets/extract_parametre.php");

// Dates de plage pour les matchs
$url_redirect = $parametres['url_redirect'];

// Créer une connexion PDO
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Définir le fuseau horaire
date_default_timezone_set('Europe/Paris');
$current_date = date('Y-m-d'); // Date actuelle
$current_date = date('2025-11-11');
// Tableau des niveaux à rechercher
$niveau_recherche = [2, '31F', '32F', '33F', '34F', '35F', '36F', '37F', '38F', '41F', '42F', '43F', '44F', '51F', '52F', '60F'];

// Fonction pour extraire la partie numérique des éléments contenant des lettres "F"
function extract_numeric($value) {
    return intval($value);
}

// Utilisation de array_map avec la fonction extract_numeric pour traiter chaque élément
$niveau_in_clause = implode(', ', array_map('extract_numeric', $niveau_recherche));

// Requête pour récupérer les parties
$sql_matches = "SELECT * FROM calendrier WHERE jours <= :current_date AND niveau IN ($niveau_in_clause) AND verif = '0' ORDER BY jours, heure";

$stmt_matches = $pdo->prepare($sql_matches);
$stmt_matches->execute(['current_date' => $current_date]);
$matches = $stmt_matches->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $scores = $_POST['scores'];

    // Mise à jour des scores dans la table calendrier
    foreach ($scores as $id => $score) {
        if (!empty($score)) {
            // Mise à jour dans la base de données
            $sql_update_calendrier = "UPDATE calendrier SET score = :score, verif = 1 WHERE id = :id";
            $stmt_update_calendrier = $pdo->prepare($sql_update_calendrier);
            $stmt_update_calendrier->execute([
                'score' => $score,
                'id' => $id
            ]);


            // Récupération des données pour l'envoi à l'API
            $sql_get_match = "SELECT * FROM calendrier WHERE id = :id";
            $stmt_get_match = $pdo->prepare($sql_get_match);
            $stmt_get_match->execute(['id' => $id]);
            $match = $stmt_get_match->fetch(PDO::FETCH_ASSOC);

            // Extraction des équipes de la partie
            list($equipe1, $equipe2) = explode('/', $match['partie']);
            
            // Extraction des scores
            list($score1, $score2) = explode('/', $score);

            // Détermination de l'équipe gagnante
            if ($score1 > $score2) {
                $equipe_gagnante = $equipe1;
            } elseif ($score1 < $score2) {
                $equipe_gagnante = $equipe2;
            } else {
                // Gérer le cas où aucun score n'atteint 40
                continue;
            }

            // Construction de l'URL des API
            $partie = urlencode($match['partie']);
            $api_score = urlencode($score);
            $api_url = "$url_redirect/logiciel/assets/elimine_team.php?partie=$partie&score=$api_score";
            
            // Envoi des données à l'API elimine_team
            $response = file_get_contents($api_url);

            // Optionnel: Vous pouvez vérifier la réponse de l'API ici
            if ($response === FALSE) {
                // Gestion des erreurs de l'API si nécessaire
            }

            // Construction de l'URL de l'API change_niveau_classement
            $niveau = urlencode($match['niveau']);
            $api_niveau_url = "$url_redirect/logiciel/assets/api_change_niveau_classement.php?equipe=$equipe_gagnante&niveau=$niveau";
            
            // Envoi des données à l'API change_niveau_classement
            $response_niveau = file_get_contents($api_niveau_url);

            // Optionnel: Vous pouvez vérifier la réponse de l'API ici
            if ($response_niveau === FALSE) {
                // Gestion des erreurs de l'API si nécessaire
            }

            // Exécuter la page switch_tour_fianles.php
            $switch_url = "$url_redirect/logiciel/assets/switch_tour_finales.php";
            $response_switch = file_get_contents($switch_url);

            // Optionnel: Vous pouvez vérifier la réponse de l'API ici
            if ($response_switch === FALSE) {
                // Gestion des erreurs de l'API si nécessaire
            }
        }
    }

    // Redirection pour éviter le rechargement du formulaire
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complète Score</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <!-- Menu vertical -->
    <?php include("./assets/menu.php"); ?>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .container {
            width: 95%;
            max-width: 1200px;
            margin: 20px;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dddddd;
        }
        th {
            background-color: #f8f9fa;
            color: #333333;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
            display: block;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }

        .legend {
            margin-bottom: 20px;
        }
        .legend div {
            display: inline-block;
            width: 100px;
            height: 20px;
            margin-right: 10px;
            vertical-align: middle;
        }
        
    </style>
</head>
<body>


        <!-- Légende -->
        <div class="legend">
            <div style="background-color: blue;"></div><span>2P: Première série</span><br>
            <div style="background-color: green;"></div><span>2D: Deuxième série</span><br>
            <div style="background-color: yellow;"></div><span>2T: Troisième série</span><br>
            <div style="background-color: red;"></div><span>2F: Féminine</span><br>
            <div style="background-color: purple;"></div><span>2M: Mixte</span>
        </div>



    <div class="container">
        <h2>Remplir les Scores des Parties de Phases Finales</h2>
        <form method="POST" action="">
            <table>
                <thead>
                    <tr>
                        <th>Jour</th>
                        <th>Heure</th>
                        <th>Partie</th>
                        <th>Score</th>
                        <th>Niveau</th>
                        <th>En attente par l'IA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (count($matches) > 0) {
                        foreach ($matches as $row) {
                            // Déterminer le style basé sur le niveau
                            $style = '';
                            switch ($row['niveau']) {
                                case "2F":
                                    $style = 'background-color: red;';
                                    break;
                                case "2P":
                                    $style = 'background-color: blue;';
                                    break;
                                case "2D":
                                    $style = 'background-color: green;';
                                    break;
                                case "2T":
                                    $style = 'background-color: yellow;';
                                    break;
                                case "2M":
                                    $style = 'background-color: purple;';
                                    break;
                                default:
                                    $style = ''; // Pas de style par défaut
                                    break;
                            }

                            // Afficher la ligne du tableau avec le style déterminé
                            echo '<tr style="' . htmlspecialchars($style) . '">';
                            echo '<td>' . htmlspecialchars($row['jours']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['heure']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['partie']) . '</td>';
                            echo '<td><input type="text" name="scores[' . htmlspecialchars($row['id']) . ']"' . 
                            ' value="' . (!empty($row['score']) ? htmlspecialchars($row['score']) : '') . '"' . 
                            ' placeholder="Score"></td>';
                       
                            echo '<td>' . htmlspecialchars($row['niveau']) . '</td>';






                            if($row['jours'] == "1999-01-01"){

                            echo '<td>X</td>';                       

                            }


                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5">Aucune partie disponible pour remplir les scores.</td></tr>';
                    }
                    ?>
                </tbody>

            </table>
            <button type="submit">Enregistrer les Scores</button>
        </form>
    </div>
</body>
</html>
