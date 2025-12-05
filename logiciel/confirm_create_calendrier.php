<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création Calendrier Auto</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f7;
        }

        #createPoolsButton {
            background-color: #007aff;
            border: none;
            color: white;
            padding: 15px 30px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            margin-top: 20px;
        }

        #createPoolsButton:hover {
            background-color: #005bb5;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .container {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            width: 100%;
            height: 100%;
            padding-top: 20px; /* Ensure content is not hidden behind the top of the page */
        }
        
        iframe {
            width: 100%;
            height: calc(100% - 80px); /* Adjust the height to fill the remaining space */
            border: none;
            margin-top: 20px;
        }

        .loader-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loader {
            border: 8px solid #f3f3f3; /* Light grey */
            border-top: 8px solid #3498db; /* Blue */
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Menu vertical -->
        <?php include("./assets/menu.php"); ?>

        <button id="createPoolsButton">Créer le calendrier</button>
        <iframe id="resultsIframe" src="resultats_calendrier.html"></iframe>
    </div>

    <!-- Loader -->
    <div class="loader-container" id="loaderContainer">
        <div class="loader"></div>
    </div>

    <script>
    document.getElementById('createPoolsButton').addEventListener('click', function() {
        if (confirm('Êtes-vous sûr de vouloir créer le calendrier ?')) {
            // Afficher le loader
            document.getElementById('loaderContainer').style.display = 'flex';

            // Envoyer la requête AJAX
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'calendrier_automatique.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    // Cacher le loader après traitement
                    document.getElementById('loaderContainer').style.display = 'none';

                    if (xhr.status == 200) {
                        // Success: Actualiser l'iframe avec les résultats
                        var response = JSON.parse(xhr.responseText);
                        var resultsIframe = document.getElementById('resultsIframe');

                        // Display execution time and other results
                        resultsIframe.contentWindow.document.body.innerHTML = 
                            '<h1>Résultats</h1>' +
                            '<p>Temps d\'exécution : ' + response.execution_time + ' secondes</p>' +
                            '<p>Nombre de parties non placées : ' + response.unplaced_matches_count + '</p>' +
                            '<p>Nombre total de parties : ' + response.total_parties + '</p>';
                    } else {
                        // Error: Afficher l'erreur dans la console
                        console.error('Erreur lors de la requête : ' + xhr.status);
                        alert('Une erreur est survenue lors de la création du calendrier.');
                    }
                }
            };
            xhr.send();
        }
    });
    </script>
</body>
</html>
