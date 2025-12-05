<!--

Modif ligne 304 - 306 pour bug de dates prises. Fonctionne mieux depuis 08/08/2024-08-01

-->
<?php
// Vérification de l'existence du cookie
if (!isset($_COOKIE['fjckjedf8854f4df5dkf'])) {
    // Si le cookie n'existe pas, rediriger vers la page de connexion
    header('Location: ./login/');  // Remplacez /login.php par l'URL de votre page de connexion
    exit();  // Assurez-vous d'arrêter l'exécution du script
}
?>


<?php
include("../logiciel/assets/extract_parametre.php");


$debut_tournoi = $parametres['startDate'];
$endDateFinales = $parametres['endDateFinales'];
$nbr_arbitre = $parametres['nbr_arbitre'];

try {
    $dsn = "mysql:host=$servername;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}

// Récupération des arbitres
$query = $pdo->query("SELECT id, prenom FROM arbitre");
$arbitres = $query->fetchAll(PDO::FETCH_ASSOC);
$arbitre_ids = array_column($arbitres, 'id');

// Récupération des jours de permanence déjà pris
$query = $pdo->query("SELECT permanence FROM arbitre");
$permanences = $query->fetchAll(PDO::FETCH_COLUMN);

// Conversion des permanences en tableau de dates
$compteurJours = [];
foreach ($permanences as $permanence) {
    $jours = explode(', ', $permanence); // Assure-toi que le format est bien "YYYY-MM-DD, YYYY-MM-DD"
    foreach ($jours as $jour) {
        $jour = trim($jour);
        if (!empty($jour)) {
            if (!isset($compteurJours[$jour])) {
                $compteurJours[$jour] = 1;
            } else {
                $compteurJours[$jour]++;
            }
        }
    }
}

// Ajouter à $joursPris seulement les dates qui apparaissent au moins 2 fois
$joursPris = [];
foreach ($compteurJours as $jour => $count) {
    if ($count >= $nbr_arbitre) { // a mettre a dynamique (parametre a ajouter)
        $joursPris[] = $jour;
    }
}



// Affichage des jours déjà pris (à des fins de débogage)
// Trier les jours pris par date
// sort($joursPris);

// // Afficher les jours pris triés
// echo '<pre>Jours pris: ' . print_r($joursPris, true) . '</pre>';

// Validation côté serveur
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $arbitre_id = intval($_POST['arbitre_id']);
    $date = $_POST['date'];

    // Déboguer la date soumise
    echo '<pre>Date soumise: ' . $date . '</pre>';

    if (!in_array($arbitre_id, $arbitre_ids)) {
        die('Arbitre non valide.');
    }

    if (in_array($date, $joursPris)) {
        die('La date est déjà prise.');
    }

    // Mise à jour de la permanence dans la base de données
    $query = $pdo->prepare("SELECT permanence FROM arbitre WHERE id = :arbitre_id");
    $query->execute([':arbitre_id' => $arbitre_id]);
    $permanence_actuelle = $query->fetchColumn();

    if ($permanence_actuelle !== false) {
        // Ajout de la nouvelle permanence
        if ($permanence_actuelle) {
            $nouvelle_permanence = $permanence_actuelle . ', ' . $date; // Assurez-vous que la virgule est suivie d'un espace
        } else {
            $nouvelle_permanence = $date;
        }

        // Mise à jour de la permanence de l'arbitre
        $update_query = $pdo->prepare("UPDATE arbitre SET permanence = :nouvelle_permanence WHERE id = :arbitre_id");
        $update_query->execute([
            ':nouvelle_permanence' => $nouvelle_permanence,
            ':arbitre_id' => $arbitre_id
        ]);

        if ($update_query->rowCount() > 0) {
            echo 'Permanence ajoutée avec succès.';
            header('Location: ./merci.php');
            exit();
        } else {
            die('Erreur lors de la mise à jour de la permanence.');
        }
    } else {
        die('Erreur : Arbitre non trouvé.');
    }
}

// Génération des jours disponibles en août
$joursDisponibles = [];
$mois = 11; // Août
$annee = date('Y'); // Année actuelle

for ($jour = 1; $jour <= 31; $jour++) {
    $date = sprintf('%04d-%02d-%02d', $annee, $mois, $jour);
    $jourSemaine = date('N', strtotime($date));

    // Exclure les samedis et dimanches et les jours déjà pris
    if ($jourSemaine < 5 && !in_array($date, $joursPris)) {
        $joursDisponibles[] = $date;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de Permanence</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
	<link rel="manifest" href="./assets/manifest.json">
                <link rel="icon" type="image/x-icon" href="../client/assets/tournoi-pelote.ico">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: rgb(43 98 38 / 79%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 60vh;
            margin: 0;
            flex-direction: column;
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
        select, input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
            box-sizing: border-box;
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
        .flatpickr-calendar {
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 10px;
            width: auto; /* Ajuste la largeur automatiquement en fonction du contenu */
            max-width: 100%; /* Assure que le calendrier ne dépasse pas le conteneur */
            margin: 0 auto; /* Centre le calendrier horizontalement */
            display: none; /* Cache le calendrier par défaut */
        }
        .flatpickr-day {
            border-radius: 8px;
            padding: 8px;
            font-size: 16px;
        }
        .flatpickr-day.disabled {
            background-color: #f0f0f0;
            color: #b0b0b0;
        }
        .flatpickr-day.selected {
            background-color: #007aff;
            color: #ffffff;
        }
        .flatpickr-day.today {
            border: 2px solid #007aff;
        }
        .flatpickr-day.weekend {
            background-color: #f0f0f0; /* Gris clair pour les week-ends */
            color: #b0b0b0; /* Couleur de texte gris clair */
        }

        /* Media Queries pour les écrans plus petits */
        @media (max-width: 600px) {
            .popup-container {
                width: 95%;
                padding: 15px;
            }
            .popup-container h1 {
                font-size: 20px;
            }
            input[type="submit"] {
                font-size: 14px;
                padding: 8px 0;
            }
            .flatpickr-day {
                font-size: 14px;
                padding: 6px;
            }
        }
        .autocomplete-suggestions {
            position: absolute;
            border-radius: 8px;
            background-color: #ffffff;
            max-height: 150px;
            overflow-y: auto;
            width: calc(100% - 2px);
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .autocomplete-suggestion {
            padding: 10px;
            cursor: pointer;
        }
        .autocomplete-suggestion:hover {
            background-color: #f0f0f0;
        }
        #arbitre_error, #date_error {
            color: red;
            display: none;
        }
    </style>
</head>
<body>




    <div class="popup-container">



    <div class="menu">
    <?php include("./assets/menu.php"); ?>
    </div>


    <div style="margin-top: 20px;" class="header-button">
        <a href="arbitre_calendar.php">Voir le Calendrier des Arbitres</a>
    </div>

    
        <h1>Choisir une Permanence</h1>
        <form id="permanenceForm" action="" method="post">
            <div class="form-group autocomplete-container">
                <details>
                    <summary style="color: #80808061;font-size: 15px;">Comment remplir</summary>
                    <p id="p" style="border: 1px solid;border-radius: 9px;padding: 5px;color: #808080cc;margin-bottom: 50px;margin-top: 20px;"><strong>Entrez les premières lettres de votre nom. Une liste apparait, il vous suffit de cliquer sur votre nom</strong><br>Si aucun nom ne s'affiche lors de la saisie, veuillez recharger la page</p>
                </details>
                <label for="arbitre">Choisissez un Arbitre</label>
                <input type="text" placeholder="prénom nom" id="arbitre" name="arbitre" required autocomplete="off">
                <input type="hidden" id="arbitre_id" name="arbitre_id">
                <div id="suggestions" class="autocomplete-suggestions"></div>
                <span id="arbitre_error">L'arbitre n'existe pas. Veuillez sélectionner un arbitre de la liste.</span>
            </div>
            <div class="form-group">
                <label for="date">Choisissez une Date de Permanence</label>
                <input type="text" id="date" name="date" required>
                <span id="date_error">La date est déjà prise. Veuillez en choisir une autre.</span>
            </div>
            <input type="submit" value="Soumettre">
        </form>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var joursPris = <?php echo json_encode($joursPris); ?>;
                var arbitres = <?php echo json_encode($arbitres); ?>;
                var suggestionsContainer = document.getElementById('suggestions');
                var arbitreInput = document.getElementById('arbitre');
                var arbitreIdInput = document.getElementById('arbitre_id');
                var dateInput = document.getElementById('date');
                var arbitreError = document.getElementById('arbitre_error');
                var dateError = document.getElementById('date_error');

                flatpickr("#date", {
                    minDate: "<?php echo $debut_tournoi; ?>",
                    maxDate: "<?php echo $endDateFinales; ?>",
                    disable: [
                        function(date) {
                            // Désactiver les mercredis (3) et vendredis (5)
                            return date.getDay() === 6 || date.getDay() === 0;
                        },
                        ...joursPris.map(function(date) {
                            return new Date(date);
                        })
                    ],
                    dateFormat: "Y-m-d",
                    locale: "fr",
                    disableMobile: true,
                    onChange: function(selectedDates) {
                        var selectedDate = selectedDates[0];
                        if (selectedDate && joursPris.includes(selectedDate.toLocaleDateString('fr-CA'))) {

                            console.log(selectedDate.toLocaleDateString('fr-CA'))
                            console.log(selectedDate)
                            dateError.style.display = 'inline';
                            dateInput.value = '';
                        } else {
                            dateError.style.display = 'none';
                        }
                    },
                    onMonthChange: function(selectedDates, dateStr, instance) {
                        instance.daysContainer.querySelectorAll('.flatpickr-day').forEach(day => {
                            const dayDate = new Date(day.getAttribute('aria-label'));
                            const dayOfWeek = dayDate.getDay();
                            if (dayOfWeek === 0 || dayOfWeek === 6) {
                                // day.classList.add('weekend');
                            }
                        });
                    }
                });


                arbitreInput.addEventListener('input', function() {
                    var query = arbitreInput.value.toLowerCase();
                    suggestionsContainer.innerHTML = '';
                    if (query.length > 0) {
                        var filteredArbitres = arbitres.filter(function(arbitre) {
                            return arbitre.prenom.toLowerCase().startsWith(query);
                        });
                        filteredArbitres.forEach(function(arbitre) {
                            var suggestion = document.createElement('div');
                            suggestion.classList.add('autocomplete-suggestion');
                            suggestion.textContent = arbitre.prenom;
                            suggestion.addEventListener('click', function() {
                                arbitreInput.value = arbitre.prenom;
                                arbitreIdInput.value = arbitre.id;
                                suggestionsContainer.innerHTML = '';
                            });
                            suggestionsContainer.appendChild(suggestion);
                        });
                        if (filteredArbitres.length === 0) {
                            suggestionsContainer.innerHTML = '<div class="autocomplete-suggestion">Aucun résultat</div>';
                        }
                    }
                });

                arbitreInput.addEventListener('focus', function() {
                    if (arbitreInput.value.length > 0) {
                        suggestionsContainer.style.display = 'block';
                    }
                });

                document.addEventListener('click', function(event) {
                    if (!event.target.closest('.autocomplete-container')) {
                        suggestionsContainer.style.display = 'none';
                    }
                });
            });
        </script>
    </div>
</body>
</html>
