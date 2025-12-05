<?php
// Détection de la langue à partir de l'URL, sinon défaut en français
$lang_code = isset($_GET['lang']) ? $_GET['lang'] : 'fr';

// Chargement du fichier de langue approprié
if ($lang_code == 'eus') {
    include("./assets/lang/lang_eus.php");
} else {
    include("./assets/lang/lang_fr.php");
}


include("../logiciel/assets/extract_parametre.php");

$phase_finale_affich = $parametres['phase_finale_affich'];


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['title_classement']; ?></title>
    <link rel="stylesheet" href="./assets/style.css">
    <link rel="icon" type="image/x-icon" href="./assets/tournoi-pelote.ico">
</head>
<body>


<style>
    /* Style de la div.menu */
.menu2 {
    display: flex;
    flex-direction: column;
    align-items: center; /* Centre les éléments horizontalement */
    margin: 1rem 0; /* Espacement autour de la div.menu */
}

/* Style pour la section des boutons de navigation */
.navigation-buttons {
    margin-top: 1rem;
    display: flex;
    justify-content: center; /* Centre les boutons horizontalement */
}

.navigation-buttons button {
    padding: 0.5rem 1rem;
    margin: 0.5rem;
    cursor: pointer;
    border: 1px solid #ccc;
    background-color: #f9f9f9;
    border-radius: 5px;
    font-size: 1rem;
}

.navigation-buttons button:hover {
    background-color: #e0e0e0;
}

</style>
<div class="popup">
    <div class="header">
        <h1 style="text-align:center;"><?php echo $lang['tournament']; ?></h1>
    </div>

    <div class="menu">
        <!-- Menu principal inclus -->
        <?php include("./assets/menu.php"); ?>
    </div>

    <div class="menu2">
        <!-- Boutons pour naviguer entre les pages -->
        <div class="navigation-buttons">
            <button onclick="showPage('classement.php?lang=<?php echo $lang_code;?>')"><?php echo $lang['menu-classement']; ?></button>

            <?php
            if($phase_finale_affich == 1){
            ?>
            <button onclick="showPage('finales_affich.php?lang=<?php echo $lang_code;?>')"><?php echo $lang['phase_finale']; ?></button>
            <?php } ?>
            
        </div>
    </div>

    <div class="content">
        <!-- iframe initialisé avec la page finales_affich.php -->
        <iframe id="contentIframe" style="width: 100%; height: 100vh; border: none;" src="./classement.php"></iframe>
    </div>
</div>

<script>
    function showPage(pageUrl) {
        document.getElementById('contentIframe').src = pageUrl;
    }
</script>

</body>


<style>
@media (max-width: 768px) {
    #contentIframe {
        height: 55vh!important;
    }
}
</style>


</html>
