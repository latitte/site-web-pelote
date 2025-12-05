<?php
// Vérification de l'existence du cookie
if (!isset($_COOKIE['fjckjedf8854f4df5dkf'])) {
    // Si le cookie n'existe pas, rediriger vers la page de connexion
    header('Location: ./login/');  // Remplacez /login.php par l'URL de votre page de connexion
    exit();  // Assurez-vous d'arrêter l'exécution du script
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="./assets/style.css">
            <link rel="icon" type="image/x-icon" href="../client/assets/tournoi-pelote.ico">
	<link rel="manifest" href="./assets/manifest.json">
</head>
<body>

<div class="popup">
    <div class="header">

    </div>

    <div class="menu">
        <?php include("./assets/menu.php"); ?>
    </div>

    <div class="content">

        <iframe id="resultsIframe" style="width: 100%; height: 100vh; border: none;" src="./calendar.php"></iframe>
    </div>
</div>



</body>


</html>
