<?php
// Vérification de l'existence du cookie
if (!isset($_COOKIE['fjckjedf8854f4df5dkf'])) {
    // Si le cookie n'existe pas, rediriger vers la page de connexion
    header('Location: ./login/');  // Remplacez /login.php par l'URL de votre page de connexion
    exit();  // Assurez-vous d'arrêter l'exécution du script
}
?>


<?php
// Inclure les informations de connexion
include '../logiciel/assets/conn_bdd.php';

try {
    // Établir la connexion avec PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : connexion à la base de données non établie. " . $e->getMessage());
}

// Récupération de la date du jour
$date_aujourdhui = date('Y-m-d');

// Requête pour récupérer les parties du jour
$sql = "SELECT id, heure, partie FROM calendrier WHERE jours = ? and partie != 'Bloqué' ORDER BY heure ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$date_aujourdhui]);
$parties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir une Partie</title>
	<link rel="manifest" href="./assets/manifest.json">
                <link rel="icon" type="image/x-icon" href="../client/assets/tournoi-pelote.ico">
    <style>
        body {
            font-family: 'San Francisco', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: rgb(43 98 38 / 79%)!important;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
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
        .container h2 {
            margin: 0 0 20px;
            font-size: 24px;
            color: #333;
        }
        .partie {
            margin: 10px 0;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: left;
        }
        .partie a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        .partie a:hover {
            text-decoration: underline;
        }
        .partie span {
            font-size: 16px;
            color: #333;
        }
    </style>
</head>
<body>



<div class="container">

<div class="menu">
    <?php include("../arbitre/assets/menu.php"); ?>
</div>

    <h2>Choisir une Partie du <?php echo date('d/m/Y'); ?></h2>

    <?php if (count($parties) > 0): ?>
        <?php foreach ($parties as $partie): ?>
            <div class="partie">
                <span><?php echo htmlspecialchars($partie['heure']); ?><br><strong>Partie <?php echo htmlspecialchars($partie['partie']); ?></strong></span>
                <a href="saisie_score.php?id=<?php echo $partie['id']; ?>">Saisir le Score</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucune partie prévue pour aujourd'hui.</p>
    <?php endif; ?>
</div>

</body>
</html>
