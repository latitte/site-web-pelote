<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création parties finales</title>
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

        <button id="createPoolsButton">Créer les phases finales</button>
        <iframe id="resultsIframe" src="resultats_lancement_finales.html"></iframe>

        <!-- Loader -->
        <div class="loader-container" id="loaderContainer">
            <div class="loader"></div>
        </div>
    </div>

    <script>
    var iframe = document.getElementById('resultsIframe');
    var loaderContainer = document.getElementById('loaderContainer');

    // Fonction pour recharger l'iframe
    function refreshIframe() {
        iframe.src = iframe.src; // Forcer le navigateur à recharger l'iframe
    }

    // Afficher le loader
    function showLoader() {
        loaderContainer.style.display = 'flex';
    }

    // Cacher le loader
    function hideLoader() {
        loaderContainer.style.display = 'none';
    }

    // Vérifier régulièrement si le fichier a été modifié
    setInterval(function() {
        var xhr = new XMLHttpRequest();
        xhr.open('HEAD', 'resultats_lancement_finales.html', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    // Obtenir la date de dernière modification du fichier
                    var lastModified = xhr.getResponseHeader('Last-Modified');
                    // Convertir la date en objet Date
                    var lastModifiedDate = new Date(lastModified);
                    // Obtenir la date actuelle
                    var currentDate = new Date();
                    // Comparer les dates pour voir si le fichier a été modifié depuis le chargement de l'iframe
                    if (lastModifiedDate > currentDate) {
                        // Si oui, recharger l'iframe
                        refreshIframe();
                    }
                }
            }
        };
        xhr.send();
    }, 5000); // Vérifier toutes les 5 secondes (ajuster selon vos besoins)
    
    document.getElementById('createPoolsButton').addEventListener('click', function() {
        if (confirm('Êtes-vous sûr de vouloir créer les phases finales ?')) {
            showLoader(); // Afficher le loader avant l'appel AJAX

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'lancement_finales.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Après la création, cacher le loader et recharger l'iframe immédiatement
                    hideLoader();
                    refreshIframe();
                }
            };
            xhr.send();
        }
    });
    </script>
</body>
</html>
