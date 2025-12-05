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
    <title><?php echo $lang['title_calendrier']; ?></title>
    <link rel="stylesheet" href="./assets/style.css">
    <link rel="icon" type="image/x-icon" href="./assets/tournoi-pelote.ico">
</head>
<body>

<div class="popup">
    <div class="header">
        <h1 style="text-align:center;"><?php echo $lang['tournament']; ?></h1>
    </div>

    <div class="menu">
        <?php include("./assets/menu.php"); ?>
        <!-- Boutons pour sélectionner la vue du calendrier -->
        <button class="apple-button" onclick="showCalendar('calendar')"><?php echo "Liste"; ?></button>
        <button class="apple-button" onclick="showCalendar('calendar_table')"><?php echo "Tableau"; ?></button>
    </div>

    <div class="content">
        <!-- <input type="text" id="searchTeam" placeholder="<?php // echo $lang['entrez_le_numero_de_lequipe']; ?>">
        <button class="apple-button" onclick="searchTeam()"><?php //echo $lang['rechercher']; ?></button>
        <div id="searchResults" style="margin-top: 20px;"></div> -->
        <iframe id="resultsIframe" style="width: 100%; height: 67vh !important; border: none;" src="./calendar_table.php?lang=<?php echo $lang_code; ?>&view=list"></iframe>
    </div>
</div>

<script>
// Fonction pour afficher le calendrier en liste ou en tableau
function showCalendar(view) {
    var lang = '<?php echo $lang_code; ?>';
    document.getElementById('resultsIframe').src = './' + view + '.php?lang=' + lang;
}

// Fonction de recherche d’équipe
function searchTeam() {
    var teamNumber = document.getElementById('searchTeam').value;
    var lang = '<?php echo $lang_code; ?>';
    fetch('./assets/search_results.php?lang=' + lang + '&team=' + teamNumber)
        .then(response => response.text())
        .then(data => {
            document.getElementById('searchResults').innerHTML = data;
        });
}
</script>

</body>

<style>
/* Styles pour les boutons Apple */
.apple-button {
    display: inline-block;
    padding: 10px 20px;
    margin: 10px 0; /* Espacement vertical entre les boutons */
    font-size: 16px;
    font-weight: 500;
    color: #333;
    background-color: #f0f0f5;
    border: 1px solid #ccc;
    border-radius: 12px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    cursor: pointer;
}

.apple-button:hover {
    background-color: #e0e0eb;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
}

.apple-button:active {
    background-color: #d0d0d9;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    transform: scale(0.98);
}

/* Styles pour les petits écrans */
@media (max-width: 768px) {
    #resultsIframe {
        height: 55vh!important;
    }
}


</style>

</html>
