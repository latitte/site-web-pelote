<?php
// Vérification de l'existence du cookie
if (!isset($_COOKIE['fjckjedf8854f4df5dkf'])) {
    // Si le cookie n'existe pas, rediriger vers la page de connexion
    header('Location: ./login/');  // Remplacez /login.php par l'URL de votre page de connexion
    exit();  // Assurez-vous d'arrêter l'exécution du script
}
?>


<?php
include '../logiciel/assets/conn_bdd.php';

try {
    $dsn = "mysql:host=$servername;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}




// Récupération des parties de la soirée en cours triées par heure
$query = $pdo->query("SELECT id, jours, heure, partie, score, commentaire, partie_jouee FROM calendrier WHERE jours = CURDATE() ORDER BY heure ASC");

// Compter les lignes
$nombre_de_partie = $query->rowCount();

// Récupérer les résultats
$parties = $query->fetchAll(PDO::FETCH_ASSOC);



// Mise à jour des scores et autres informations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scores = $_POST['scores'];
    $commentaires = $_POST['commentaires'];
    $parties_jouees = $_POST['parties_jouees'];


    foreach ($scores as $id => $score) {
        $commentaire = $commentaires[$id] ?? '';
        $partie_jouee = isset($parties_jouees[$id]) ? 1 : 0;
        
        $stmt = $pdo->prepare("UPDATE calendrier SET score = ?, commentaire = ?, partie_jouee = ? WHERE id = ?");
        $stmt->execute([$score, $commentaire, $partie_jouee, $id]);
    }

    echo 'Informations mises à jour avec succès.';
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="manifest" href="./assets/manifest.json">
    <title>Mettre à jour les scores</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: rgb(43 98 38 / 79%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
            overflow: hidden;
        }
        .header-button {
            margin-bottom: 20px;
        }
        .header-button a {
            text-decoration: none;
            color: black;
            font-weight: 600;
            font-size: 18px;
            border: 2px solid #000000;
            border-radius: 8px;
            padding: 10px 20px;
            background-color: #ffffff;
            display: inline-block;
        }
        .header-button a:hover {
            background-color: #f0f0f0;
        }
        .popup-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%; /* Largeur ajustée à 90% de la fenêtre */
            max-width: 1200px; /* Ajuste cette valeur selon la largeur maximale souhaitée */
            padding: 20px;
            box-sizing: border-box;
            position: relative;
            overflow-y: auto;
            max-height: 95vh; /* Hauteur ajustée à 95% de la fenêtre */
            height: 95vh;
        }

        .popup-container h1 {
            margin-top: 0;
            font-size: 24px;
            font-weight: 600;
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
            position: relative;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        select, input[type="text"], input[type="submit"], textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
        }
        input[type="text"] {
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #007aff;
            color: #ffffff;
            border: none;
            cursor: pointer;
            font-weight: 600;
            padding: 10px 0;
        }
        input[type="submit"]:hover {
            background-color: #005bb5;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
        }

        @media (max-width: 600px) {
            .popup-container {
                width: 95%;
                padding: 15px;
                max-height: 90vh;
            }
            .popup-container h1 {
                font-size: 20px;
            }
            input[type="submit"] {
                font-size: 14px;
                padding: 8px 0;
            }
        }
    </style>
</head>
<body>
    <!-- <div class="header-button">
        <a href="calendar.php">Voir le Calendrier</a>
    </div> -->
    <div class="popup-container">

    <div class="menu">
    <?php include("./assets/menu.php"); ?>
    </div>
    
    <style>
        :root {
            --background: #f0f0f5;
            --foreground: #333333;
            --primary: #007aff;
            --border: #d1d1d6;
            --input: #ffffff;
            --ring: #007aff;
            --primary-foreground: #ffffff;
            --secondary: #8e8e93;
            --accent: #34c759;
            --destructive: #ff3b30;
            --muted: #aeaeb2;
            --card: #ffffff;
        }



        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;

        }

        .subtitle {
            font-size: 18px;

        }

        .content {
            margin-top: 20px;
        }

        h2 {
            font-size: 20px;

        }

        ul {
            list-style-type: disc;
            margin: 10px 0;
            padding-left: 20px;
        }

        li {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .important {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">

        <div class="content" style="text-align: left;">

        


            <style>
                #resultsIframe {
                    width: 100%;
                    height: 150vh;
                    border: none;
                }
                @media (max-width: 768px) {
                    #resultsIframe {
                        height: 250vh;
                    }
                }
            </style>
          <iframe id="resultsIframe" src="../client/doc.php"></iframe>




        </div>
    </div>
</body>
</body>
</html>
