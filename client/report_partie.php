<?php
// Détection de la langue à partir de l'URL, sinon défaut en français
$lang_code = isset($_GET['lang']) ? $_GET['lang'] : 'fr';

// Chargement du fichier de langue approprié
if ($lang_code == 'eus') {
    include("./assets/lang/lang_eus.php");
} else {
    include("./assets/lang/lang_fr.php");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['title_report']; ?></title>
    <link rel="stylesheet" href="./assets/style.css">
    
</head>
<body>

<div class="popup">
    <div class="header">
        <h1><?php echo $lang['tournament']; ?></h1>
    </div>

    <div class="menu">
    <?php include("./assets/menu.php"); ?>
    </div>

    <div class="content">

        

        <iframe id="resultsIframe" style="width: 100%; height: 100vh; border: none;" src="./changement_creneau/change_partie.php"></iframe>

    </div>

</div>

</body>
</html>
