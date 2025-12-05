<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complète score</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Menu vertical -->
        <?php
        ob_start(); // Commence la mise en mémoire tampon de sortie

        include("./assets/menu.php");
        ?>
    </div>

    <!-- Contenu principal -->


    <head>
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
    </style>
    </head>
    <main role="main" class="container">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Phase de qualifications</h1>
        </div>

        <div class="container">
            <h2>Remplir les Scores des Parties</h2>
            <?php
            // Connexion à la base de données
            include("./assets/conn_bdd.php");

            // Créer une connexion
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Vérifier la connexion
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Définir le fuseau horaire
            date_default_timezone_set('Europe/Paris');
            $current_date = date('Y-m-d');

            // Requête pour récupérer les parties sans score
            $sql_matches = "SELECT * FROM calendrier WHERE jours <= '$current_date' AND score IS NULL AND niveau = 1 AND jours != '0000-00-00' ORDER BY jours, heure";
            $result_matches = $conn->query($sql_matches);

            // Traitement de la soumission du formulaire
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $scores = $_POST['scores'];
                
                foreach ($scores as $id => $score) {
                    if (!empty($score)) {
                        $sql_update = "UPDATE calendrier SET score='$score' WHERE id=$id";
                        $conn->query($sql_update);
                    }
                }
                // Rediriger après la mise à jour pour éviter la soumission de formulaire répétée
                header("Location: ./complete_score.php");
                exit();
            }

            ob_end_flush(); // Vide la mémoire tampon de sortie

            // Afficher le formulaire de scores
            echo '<form method="POST" action="">
                    <table>
                        <thead>
                            <tr>
                                <th>Jour</th>
                                <th>Heure</th>
                                <th>Partie</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>';

            if ($result_matches->num_rows > 0) {
                while ($row = $result_matches->fetch_assoc()) {
                    $row_color = ($row['niveau'] == "2F") ? 'style="background-color:red;"' : '';
                    echo "<tr $row_color>";
                    echo '<td>' . $row['jours'] . '</td>';
                    echo '<td>' . $row['heure'] . '</td>';
                    echo '<td>' . $row['partie'] . '</td>';
                    echo '<td><input type="text" name="scores[' . $row['id'] . ']" placeholder="Score"></td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="4">Aucune partie disponible pour remplir les scores.</td></tr>';
            }

            echo '        </tbody>
                    </table>
                    <button type="submit">Enregistrer les Scores</button>
                </form>';

            // Fermer la connexion MySQL
            $conn->close();
            ?>
        </div>
    </main>
</body>
</html>
