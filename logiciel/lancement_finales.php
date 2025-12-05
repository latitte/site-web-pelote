<?php

include("./assets/extract_parametre.php");

$startDateFinales = $parametres['startDateFinales'];
$endDateFinales = $parametres['endDateFinales'];

$series_bdd = $parametres['series'];
$series_bdd = explode(",", $series_bdd);

// Créez la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifiez la connexion
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Ouvrez le fichier en mode écriture
$file = fopen("resultats_lancement_finales.html", "w");

function ecrireDansFichier($file, $content) {
    fwrite($file, $content);
}

function genererMatchs($equipes, $file, $serie, $startDateFinales, $endDateFinales) {
    global $var_tournoi;
    $nb_equipes = count($equipes);
    $rounds = [];
    $niveau_matchs = [];

    if ($nb_equipes == 16) {
        $rounds = [8, "1/8 de finale"];
        $niveau_matchs = [31, 35, 32, 36, 33, 37, 34, 38];
    } elseif ($nb_equipes == 8) {
        $rounds = [4, "1/4 de finale"];
        $niveau_matchs = [41, 43, 42, 44];
    } elseif ($nb_equipes == 4) {
        $rounds = [2, "1/2 finale"];
        $niveau_matchs = [51, 52];
    } elseif ($nb_equipes == 2) {
        $rounds = [1, "finale"];
        $niveau_matchs = [60];
    } else {
        ecrireDansFichier($file, "<p>Le nombre d'équipes n'est pas suffisant pour organiser des matchs dans la série $serie.</p>");
        return;
    }

    ecrireDansFichier($file, "<h2>Matchs de " . $rounds[1] . " pour la série $serie</h2>");
    ecrireDansFichier($file, "<table border='1'>
            <tr>
                <th>ID Partie</th>
                <th>Match</th>
                <th>Equipe 1</th>
                <th>Equipe 2</th>
                <th>Niveau</th>
                <th>API Status</th>
            </tr>");
    
    for ($i = 0; $i < $rounds[0]; $i++) {
        $equipe1 = $equipes[$i];
        $equipe2 = $equipes[$nb_equipes - 1 - $i];
        $niveau_match = $niveau_matchs[$i % count($niveau_matchs)];
        $id_partie = $niveau_match . substr($serie, 0, 1); // Ajouter la première lettre de la série
        
        // Appel à l'API
        $team1_id = $equipe1["id"];
        $team2_id = $equipe2["id"];

        $api_url = "https://$var_tournoi.tournoi-pelote.com/logiciel/assets/api_calendar_add.php?team1_id=$team1_id&team2_id=$team2_id&niveau=$id_partie&date_start=$startDateFinales&date_end=$endDateFinales";

        // Initialisation des options du contexte HTTP
        $options = [
            "http" => [
                "method" => "GET"
            ]
        ];
        $context = stream_context_create($options);
        
        // Exécution de la requête et gestion des erreurs
        $api_response = @file_get_contents($api_url, false, $context);
        $http_code = isset($http_response_header[0]) ? explode(' ', $http_response_header[0])[1] : 'N/A';

        // Décodage de la réponse JSON
        $decoded_response = json_decode($api_response, true);
        $api_message = isset($decoded_response[0]['message']) ? $decoded_response[0]['message'] : 'No message';
        $api_status = isset($decoded_response[0]['status']) ? $decoded_response[0]['status'] : 'No status';

        // Déterminez le style de la cellule en fonction de l'état
        $style = ($api_status == 'error') ? "style='background-color:red;'" : "";
        
        ecrireDansFichier($file, "<tr>
                <td>" . $id_partie . "</td>
                <td>Match " . ($i + 1) . "</td>
                <td>" . $equipe1["equipe"] . " (Place: " . $equipe1["place"] . ")</td>
                <td>" . $equipe2["equipe"] . " (Place: " . $equipe2["place"] . ")</td>
                <td>" . $niveau_match . "</td>
                <td $style>HTTP Code: " . $http_code . "<br>Message: " . htmlspecialchars($api_message) . "<br>Status: " . htmlspecialchars($api_status) . "</td>
              </tr>");
    }
    ecrireDansFichier($file, "</table>");
}

ob_start(); // Commence à capturer la sortie

// Les séries à traiter
$series = $series_bdd;

// Boucle sur chaque série
foreach ($series as $serie) {
    // Récupérez les données de la table pour chaque série
    $sql = "SELECT * FROM classement WHERE niveau != 'elimine' AND serie = '$serie' ORDER BY place ASC";
    $result = $conn->query($sql);

    $equipes = []; // Tableau pour stocker les équipes

    if ($result->num_rows > 0) {
        ecrireDansFichier($file, "<h2>Classement des équipes pour la série $serie</h2>");
        ecrireDansFichier($file, "<table border='1'>
                <tr>
                    <th>ID</th>
                    <th>Place</th>
                    <th>Equipe</th>
                    <th>Joueurs</th>
                    <th>Points</th>
                    <th>Average</th>
                    <th>Serie</th>
                    <th>Niveau</th>
                </tr>");
        while ($row = $result->fetch_assoc()) {
            $equipes[] = $row; // Ajoutez chaque ligne au tableau des équipes
            ecrireDansFichier($file, "<tr>
                    <td>" . $row["id"] . "</td>
                    <td>" . $row["place"] . "</td>
                    <td>" . $row["equipe"] . "</td>
                    <td>" . $row["joueurs"] . "</td>
                    <td>" . $row["points"] . "</td>
                    <td>" . $row["average"] . "</td>
                    <td>" . $row["serie"] . "</td>
                    <td>" . $row["niveau"] . "</td>
                </tr>");
        }
        ecrireDansFichier($file, "</table>");

        // Appel de la fonction genererMatchs() pour chaque série
        genererMatchs($equipes, $file, $serie, $startDateFinales, $endDateFinales);
    } else {
        ecrireDansFichier($file, "<p>0 résultats pour la série $serie</p>");
    }
}

$conn->close();

$content = ob_get_clean(); // Récupère la sortie capturée

// Écrit la sortie capturée dans le fichier
fwrite($file, $content);

// Ferme le fichier
fclose($file);
?>
