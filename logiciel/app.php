<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .app-icon {
            width: 100px;
            height: 100px;
            margin: 10px;
            background-color: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            cursor: pointer;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Menu vertical -->
        <?php include("./assets/menu.php"); ?>

        <!-- Contenu principal -->
        <main role="main" class="container">

            <!-- Icônes des applications -->
            <div class="d-flex flex-wrap">
                <div class="app-icon" onclick="loadApp('verif_compatibilite_poules.php')">Verif compatibilite poules</div>
                <div class="app-icon" onclick="loadApp('verif_partie_placee.php')">Verif respect créneau partie</div>
                <div class="app-icon" onclick="loadApp('verif_coherence_partie.php')">Vérif cohérence parties</div>
                <div class="app-icon" onclick="loadApp('verif_chevauchement_partie.php')">Vérif chevauchement parties</div>
                <div class="app-icon" onclick="loadApp('joueur_status_paiement.php')">Calcul Prix joueur</div>
                <div class="app-icon" onclick="loadApp('edit_team.php')">Editer une équipe</div>
                <div class="app-icon" onclick="loadApp('convert_team_forfait.php')">Ajouter une équipe forfait</div>
                <div class="app-icon" onclick="loadApp('api_test.php')">API test</div>
                <div class="app-icon" onclick="loadApp('regul_paiement.php')">Régulariser un paiement</div>
                <div class="app-icon" onclick="loadApp('add_arbitre.php')">Ajouter un arbitre</div>
                <div class="app-icon" onclick="loadApp('log_api_interne.php')">Log API interne</div>
                <div class="app-icon" onclick="loadApp('log_bdd_affich.php')">Log BDD</div>
                <!-- Ajoutez d'autres icônes ici -->
            </div>

            <!-- Iframe pour charger les applications -->
            <iframe id="appFrame" style="width: 100%; height: 100vh; border: none;"></iframe>

        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function loadApp(url) {
            document.getElementById('appFrame').src = url;
        }
    </script>
</body>
</html>
