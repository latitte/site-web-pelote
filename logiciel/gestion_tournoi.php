<?php
// Connexion à la base de données
include("./assets/conn_bdd.php");

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Initialiser les valeurs des paramètres
$params = [
    'quota_series' => '',
    'series' => '',
    'startDate' => '',
    'endDate' => '',
    'openRegistration' => '0',
    'jours_dispo' => '',
    'heures_dispo' => '',
    'mois' => '',
    'duree_partie' => '',
    'prix_1serie/joueur' => '',
    'prix_2+serie/joueur' => '',
    'redirection_accueil' => '',
    'color-site-menu' => '',
    'color-site-fond' => '',
    'nbr_arbitre' => '',
    'date_fin_report' => '',
    'lieu' => '',
    // 🆕 nouveaux paramètres GPS :
    'lieu_lat' => '',
    'lieu_lon' => ''
];

// Récupérer les valeurs existantes
$sql = "SELECT parametre, valeur 
        FROM parametre 
        WHERE parametre IN (
            'quota_series','series','startDate','endDate','openRegistration','jours_dispo','heures_dispo','mois',
            'duree_partie','prix_1serie/joueur','prix_2+serie/joueur','redirection_accueil','color-site-menu',
            'color-site-fond','nbr_arbitre','date_fin_report','lieu','lieu_lat','lieu_lon'
        )";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $params[$row['parametre']] = $row['valeur'];
    }
}

// Parser les chaînes pour extraire les quotas et les noms des séries
$quota_series = array_filter($params['quota_series'] === '' ? [] : explode(',', $params['quota_series']), 'strlen');
$series = array_filter($params['series'] === '' ? [] : explode(',', $params['series']), 'strlen');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialiser des tableaux pour stocker les nouvelles valeurs
    $numTeams = [];
    $seriesNames = [];
    $indexesToDelete = [];
    $newSeries = []; // Nouveau tableau pour les nouvelles séries et quotas

    // Récupération dynamique des valeurs des séries
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'numTeams') === 0) {
            $index = (int)str_replace('numTeams', '', $key);
            $numTeams[$index] = $value;
        } elseif (strpos($key, 'seriesName') === 0) {
            $index = (int)str_replace('seriesName', '', $key);
            $seriesNames[$index] = $value;
        } elseif (strpos($key, 'deleteSeries') === 0) {
            $indexesToDelete[] = (int)str_replace('deleteSeries', '', $key);
        } elseif (strpos($key, 'newSeriesName') === 0) {
            $newIndex = (int)str_replace('newSeriesName', '', $key);
            $newSeries[$newIndex]['name'] = $value;
        } elseif (strpos($key, 'newNumTeams') === 0) {
            $newIndex = (int)str_replace('newNumTeams', '', $key);
            $newSeries[$newIndex]['quota'] = $value;
        }
    }

    // Supprimer les séries sélectionnées
    foreach ($indexesToDelete as $index) {
        unset($numTeams[$index], $seriesNames[$index]);
    }

    // Re-indexer
    $numTeams = array_values($numTeams);
    $seriesNames = array_values($seriesNames);

    // Ajouter les nouvelles séries et quotas
    foreach ($newSeries as $newSerie) {
        if (!empty($newSerie['name']) && !empty($newSerie['quota'])) {
            $seriesNames[] = $newSerie['name'];
            $numTeams[] = $newSerie['quota'];
        }
    }

    $startDate = $_POST['startDate'] ?? $params['startDate'];
    $endDate = $_POST['endDate'] ?? $params['endDate'];
    $openRegistration = $_POST['openRegistration'] ?? $params['openRegistration'];
    $joursDispo = isset($_POST['joursDispo']) ? implode(', ', $_POST['joursDispo']) : $params['jours_dispo'];
    $heuresDispo = $_POST['heuresDispo'] ?? $params['heures_dispo'];
    $mois = isset($_POST['mois']) ? implode(', ', $_POST['mois']) : $params['mois'];
    $duree_partie = $_POST['duree_partie'] ?? $params['duree_partie'];
    $prix_1seriePar_joueur = $_POST['prix_1serie/joueur'] ?? $params['prix_1serie/joueur'];
    $prix_2PlusseriePar_joueur = $_POST['prix_2+serie/joueur'] ?? $params['prix_2+serie/joueur'];
    $redirection_accueil = $_POST['redirection_accueil'] ?? $params['redirection_accueil'];
    $color_site_menu = $_POST['color-site-menu'] ?? $params['color-site-menu'];
    $color_site_fond = $_POST['color-site-fond'] ?? $params['color-site-fond'];
    $nbr_arbitre = $_POST['nbr_arbitre'] ?? $params['nbr_arbitre'];
    $date_fin_report = $_POST['date_fin_report'] ?? $params['date_fin_report'];
    $lieu = $_POST['lieu'] ?? $params['lieu'];

    // 🆕 récupérer lat/lon
    $lieu_lat = isset($_POST['lieu_lat']) ? trim($_POST['lieu_lat']) : $params['lieu_lat'];
    $lieu_lon = isset($_POST['lieu_lon']) ? trim($_POST['lieu_lon']) : $params['lieu_lon'];

    // Mettre à jour quota_series et series
    $quota_series_string = implode(',', $numTeams);
    $series_string = implode(',', $seriesNames);

    $paramsToUpdate = [
        'quota_series' => $quota_series_string,
        'series' => $series_string,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'openRegistration' => $openRegistration,
        'jours_dispo' => $joursDispo,
        'heures_dispo' => $heuresDispo,
        'mois' => $mois,
        'duree_partie' => $duree_partie,
        'prix_1serie/joueur' => $prix_1seriePar_joueur,
        'prix_2+serie/joueur' => $prix_2PlusseriePar_joueur,
        'redirection_accueil' => $redirection_accueil,
        'color-site-menu' => $color_site_menu,
        'color-site-fond' => $color_site_fond,
        'nbr_arbitre' => $nbr_arbitre,
        'date_fin_report' => $date_fin_report,
        'lieu' => $lieu,
        // 🆕 inclure lat/lon
        'lieu_lat' => $lieu_lat,
        'lieu_lon' => $lieu_lon
    ];

    $success = true;
    foreach ($paramsToUpdate as $key => $value) {
        // Met à jour uniquement si changé
        if (!array_key_exists($key, $params) || $params[$key] !== $value) {
            $stmt = $conn->prepare("UPDATE parametre SET valeur=? WHERE parametre=?");
            $stmt->bind_param("ss", $value, $key);
            if (!$stmt->execute()) {
                $success = false;
                echo "<div class='alert alert-danger mt-3'>Erreur : " . htmlspecialchars($stmt->error) . "</div>";
                $stmt->close();
                break;
            }
            // Si aucune ligne affectée, on insère (au cas où la clé n'existe pas encore)
            if ($conn->affected_rows === 0) {
                $stmt->close();
                $stmtIns = $conn->prepare("INSERT IGNORE INTO parametre (parametre, valeur) VALUES (?, ?)");
                $stmtIns->bind_param("ss", $key, $value);
                if (!$stmtIns->execute()) {
                    $success = false;
                    echo "<div class='alert alert-danger mt-3'>Erreur insertion : " . htmlspecialchars($stmtIns->error) . "</div>";
                    $stmtIns->close();
                    break;
                }
                $stmtIns->close();
            } else {
                $stmt->close();
            }
        }
    }

    if ($success) {
        // echo "<div class='alert alert-success mt-3'>Les paramètres ont été enregistrés avec succès.</div>";
    }

    // Mettre à jour les valeurs affichées après l'enregistrement
    foreach ($paramsToUpdate as $key => $value) {
        $params[$key] = $value;
    }
}

// Fermer la connexion
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Tournoi</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        #map { height: 360px; border-radius: 8px; }
        .input-group-append .btn { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Menu vertical -->
        <?php include("./assets/menu.php"); ?>

        <!-- Contenu principal -->
        <main role="main" class="container">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestion Tournoi</h1>
            </div>

            <h2>Paramètres</h2>
            <?php
            if (isset($success) && $success) {
                echo "<div class='alert alert-success mt-3'>Les paramètres ont été enregistrés avec succès.</div>";
                echo "<script>
                        if (!window.location.href.includes('reloaded=true')) {
                            setTimeout(function() {
                                window.location.href = window.location.href + (window.location.href.includes('?') ? '&' : '?') + 'reloaded=true';
                            }, 100);
                        }
                      </script>";
            }
            ?>

            <form method="POST" action="">
                <h3>Séries</h3>
                <div id="seriesContainer">
                    <?php
                    // Afficher les champs pour chaque série
                    foreach ($quota_series as $index => $quota) {
                        $seriesName = $series[$index] ?? '';
                        echo '<div class="form-group">';
                        echo '<label for="seriesName' . $index . '">Nom de la série ' . ($index + 1) . ' :</label>';
                        echo '<input type="text" class="form-control" id="seriesName' . $index . '" name="seriesName' . $index . '" value="' . htmlspecialchars($seriesName) . '">';
                        echo '<label for="numTeams' . $index . '">Nombre d\'équipes max pour la série ' . ($index + 1) . ' :</label>';
                        echo '<input type="number" class="form-control" id="numTeams' . $index . '" name="numTeams' . $index . '" min="1" value="' . htmlspecialchars($quota) . '">';
                        echo '<button type="submit" class="btn btn-danger mt-2" name="deleteSeries' . $index . '">Supprimer cette série</button>';
                        echo '</div>';
                    }
                    ?>
                </div>
                <button type="button" class="btn btn-secondary mb-3" onclick="addSeries()">Ajouter une série</button>

                <div class="form-group">
                    <label for="startDate">Date de début prévue des phases de qualification :</label>
                    <input type="date" class="form-control" id="startDate" name="startDate" value="<?php echo htmlspecialchars($params['startDate']); ?>">
                </div>
                <div class="form-group">
                    <label for="endDate">Date de fin prévue des phases de qualification :</label>
                    <input type="date" class="form-control" id="endDate" name="endDate" value="<?php echo htmlspecialchars($params['endDate']); ?>">
                </div>
                <div class="form-group">
                    <label>Ouvrir le formulaire d'inscription :</label>
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="openRegistrationYes" name="openRegistration" value="1" <?php echo $params['openRegistration'] == '1' ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="openRegistrationYes">Oui</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="openRegistrationNo" name="openRegistration" value="0" <?php echo $params['openRegistration'] == '0' ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="openRegistrationNo">Non</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="joursDispo">Jours disponibles :</label><br>
                    <?php
                    $jours = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
                    foreach ($jours as $jour) {
                        $checked = strpos($params['jours_dispo'], $jour) !== false ? 'checked' : '';
                        echo "<div class='custom-control custom-checkbox'>
                            <input type='checkbox' class='custom-control-input' id='jour$jour' name='joursDispo[]' value='$jour' $checked>
                            <label class='custom-control-label' for='jour$jour'>$jour</label>
                        </div>";
                    }
                    ?>
                </div>
                <div class="form-group">
                    <label for="heuresDispo">Heures disponibles :</label>
                    <input type="text" class="form-control" id="heuresDispo" name="heuresDispo" value="<?php echo htmlspecialchars($params['heures_dispo']); ?>" placeholder="Exemple : 08:00-12:00, 14:00-18:00">
                </div>
                <div class="form-group">
                    <label for="mois">Mois :</label><br>
                    <?php
                    $mois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                    foreach ($mois as $m) {
                        $checked = strpos($params['mois'], $m) !== false ? 'checked' : '';
                        echo "<div class='custom-control custom-checkbox'>
                            <input type='checkbox' class='custom-control-input' id='mois$m' name='mois[]' value='$m' $checked>
                            <label class='custom-control-label' for='mois$m'>$m</label>
                        </div>";
                    }
                    ?>
                </div>

                <div class="form-group">
                    <label for="duree_partie">Durée partie :</label>
                    <input type="text" class="form-control" id="duree_partie" name="duree_partie" value="<?php echo htmlspecialchars($params['duree_partie']); ?>" placeholder="Exemple : 40 points">
                </div>

                <div class="form-group">
                    <label for="prix_1serie/joueur">Prix 1série :</label>
                    <input type="text" class="form-control" id="prix_1serie/joueur" name="prix_1serie/joueur" value="<?php echo htmlspecialchars($params['prix_1serie/joueur']); ?>" placeholder="Ne pas mettre d'unité (X€)">
                </div>

                <div class="form-group">
                    <label for="prix_2+serie/joueur">Prix 2séries :</label>
                    <input type="text" class="form-control" id="prix_2+serie/joueur" name="prix_2+serie/joueur" value="<?php echo htmlspecialchars($params['prix_2+serie/joueur']); ?>" placeholder="Ne pas mettre d'unité (X€)">
                </div>

                <div class="form-group">
                    <label for="redirection_accueil">Page de redirection url :</label>
                    <select id="redirection_accueil" name="redirection_accueil" class="form-control">
                        <option value="index" <?php echo ($params['redirection_accueil'] == 'index') ? 'selected' : ''; ?>>Accueil</option>
                        <option value="equipes" <?php echo ($params['redirection_accueil'] == 'equipes') ? 'selected' : ''; ?>>Les équipes</option>
                        <option value="calendrier" <?php echo ($params['redirection_accueil'] == 'calendrier') ? 'selected' : ''; ?>>Calendrier</option>
                        <option value="arbre_tournoi" <?php echo ($params['redirection_accueil'] == 'arbre_tournoi') ? 'selected' : ''; ?>>Classement</option>
                        <option value="inscription_joueur" <?php echo ($params['redirection_accueil'] == 'inscription_joueur') ? 'selected' : ''; ?>>Inscription</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="color-site-menu">color-site-menu :</label>
                    <input type="color" class="form-control" id="color-site-menu" name="color-site-menu" value="<?php echo htmlspecialchars($params['color-site-menu']); ?>" placeholder="Couleur">
                </div>

                <div class="form-group">
                    <label for="color-site-fond">color-site-fond :</label>
                    <input type="color" class="form-control" id="color-site-fond" name="color-site-fond" value="<?php echo htmlspecialchars($params['color-site-fond']); ?>" placeholder="Couleur">
                </div>

                <div class="form-group">
                    <label for="nbr_arbitre">Nombre d'arbitre par soirée :</label>
                    <input type="number" class="form-control" id="nbr_arbitre" name="nbr_arbitre" value="<?php echo htmlspecialchars($params['nbr_arbitre']); ?>" placeholder="Nombre">
                </div>

                <div class="form-group">
                    <label for="date_fin_report">Date de fin des reports :</label>
                    <input type="date" class="form-control" id="date_fin_report" name="date_fin_report" value="<?php echo htmlspecialchars($params['date_fin_report']); ?>">
                </div>

                <div class="form-group">
                    <label for="lieu">Lieu du tournoi (nom/adresse) :</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="lieu" name="lieu" value="<?php echo htmlspecialchars($params['lieu']); ?>" placeholder="Ex : Fronton Ilharre">
                        <div class="input-group-append">
                            <button type="button" id="btnGeocode" class="btn btn-outline-primary">Localiser</button>
                        </div>
                    </div>
                    <small class="form-text text-muted">Tu peux saisir un nom/adresse puis cliquer « Localiser », ou bien cliquer directement sur la carte.</small>
                </div>

                <!-- 🗺️ Carte + champs coordonnés -->
                <div class="form-group">
                    <label>Position GPS :</label>
                    <div id="map"></div>
                    <div class="form-row mt-2">
                        <div class="col">
                            <label for="lieu_lat">Latitude</label>
                            <input type="text" class="form-control" id="lieu_lat" name="lieu_lat" value="<?php echo htmlspecialchars($params['lieu_lat']); ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="lieu_lon">Longitude</label>
                            <input type="text" class="form-control" id="lieu_lon" name="lieu_lon" value="<?php echo htmlspecialchars($params['lieu_lon']); ?>" readonly>
                        </div>
                    </div>
                    <small class="form-text text-muted">Clique sur la carte pour placer le marqueur. Les coordonnées seront enregistrées.</small>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </main>
    </div>

    <script>
        function addSeries() {
            var seriesContainer = document.getElementById('seriesContainer');
            var seriesCount = seriesContainer.querySelectorAll('input[name^="seriesName"]').length;
            var newSeriesIndex = seriesCount;

            var newSeriesDiv = document.createElement('div');
            newSeriesDiv.className = 'form-group';

            var seriesNameLabel = document.createElement('label');
            seriesNameLabel.innerText = 'Nom de la nouvelle série ' + (newSeriesIndex + 1) + ' :';
            newSeriesDiv.appendChild(seriesNameLabel);

            var seriesNameInput = document.createElement('input');
            seriesNameInput.type = 'text';
            seriesNameInput.name = 'newSeriesName' + newSeriesIndex;
            seriesNameInput.className = 'form-control';
            seriesNameInput.placeholder = 'Nom de la nouvelle série';
            newSeriesDiv.appendChild(seriesNameInput);

            var numTeamsLabel = document.createElement('label');
            numTeamsLabel.innerText = 'Nombre d\'équipes max pour la nouvelle série ' + (newSeriesIndex + 1) + ' :';
            newSeriesDiv.appendChild(numTeamsLabel);

            var numTeamsInput = document.createElement('input');
            numTeamsInput.type = 'number';
            numTeamsInput.name = 'newNumTeams' + newSeriesIndex;
            numTeamsInput.className = 'form-control';
            numTeamsInput.min = 1;
            numTeamsInput.placeholder = 'Nombre d\'équipes max';
            newSeriesDiv.appendChild(numTeamsInput);

            seriesContainer.appendChild(newSeriesDiv);
        }
    </script>

    <!-- jQuery / Bootstrap -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        (function initMap(){
            var latInput = document.getElementById('lieu_lat');
            var lonInput = document.getElementById('lieu_lon');

            // Centre par défaut : France
            var lat = parseFloat(latInput.value);
            var lon = parseFloat(lonInput.value);
            var hasCoords = !isNaN(lat) && !isNaN(lon);

            var map = L.map('map');
            var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            var marker = null;

            function setMarker(latlng, fit=false) {
                if (!marker) {
                    marker = L.marker(latlng, { draggable: true }).addTo(map);
                    marker.on('dragend', function(e) {
                        var p = e.target.getLatLng();
                        latInput.value = p.lat.toFixed(6);
                        lonInput.value = p.lng.toFixed(6);
                    });
                } else {
                    marker.setLatLng(latlng);
                }
                latInput.value = latlng.lat.toFixed(6);
                lonInput.value = latlng.lng.toFixed(6);
                if (fit) map.setView(latlng, 15);
            }

            if (hasCoords) {
                map.setView([lat, lon], 15);
                setMarker({lat: lat, lng: lon});
            } else {
                map.setView([46.5, 2.5], 5); // France
            }

            map.on('click', function(e) {
                setMarker(e.latlng, false);
            });

            // Géocoder le champ "lieu"
            document.getElementById('btnGeocode').addEventListener('click', function() {
                var q = document.getElementById('lieu').value.trim();
                if (!q) return;

                // Nominatim (usage raisonnable)
                var url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q);
                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(data => {
                        if (data && data[0]) {
                            var p = { lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon) };
                            setMarker(p, true);
                        } else {
                            alert('Aucun résultat trouvé pour "' + q + '".');
                        }
                    })
                    .catch(() => alert('Erreur lors de la recherche.'));
            });
        })();
    </script>
</body>
</html>
