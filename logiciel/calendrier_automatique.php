<?php
header('Content-Type: application/json'); // Assurez-vous que le contenu est traité comme JSON

include("./assets/extract_parametre.php");

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    echo json_encode(['error' => 'Connexion échouée : ' . $conn->connect_error]);
    exit();
}

// Récupérer les dates de plage pour les matchs
$dateStart = $parametres['startDate'];
$dateEnd = $parametres['endDate'];

// URL de l'API
$apiUrl = "https://$var_tournoi.tournoi-pelote.com/logiciel/assets/api_calendar_add.php";

// Supprimer la limite de temps d'exécution
set_time_limit(0); // Enlève la limite de temps d'exécution

// Chronométrage de l'exécution
$start_time = microtime(true);

// Fonction pour récupérer les séries et poules
function get_series_poules($conn) {
    $series_poules = array();
    $sql_series = "SELECT DISTINCT serie FROM inscriptions";
    $result_series = $conn->query($sql_series);

    if ($result_series->num_rows > 0) {
        while ($row_series = $result_series->fetch_assoc()) {
            $serie_name = $row_series['serie'];
            $sql_poules = "SELECT DISTINCT poule FROM inscriptions WHERE serie = '$serie_name'";
            $result_poules = $conn->query($sql_poules);

            if ($result_poules->num_rows > 0) {
                while ($row_poules = $result_poules->fetch_assoc()) {
                    $poule_name = $row_poules['poule'];
                    $series_poules[$serie_name][] = $poule_name;
                }
            }
        }
    }
    return $series_poules;
}

// Fonction pour afficher les parties possibles
function afficher_parties($teams) {
    $matches = array();
    for ($i = 0; $i < count($teams); $i++) {
        for ($j = $i + 1; $j < count($teams); $j++) {
            $matches[] = array("team1" => $teams[$i], "team2" => $teams[$j]);
        }
    }
    return $matches;
}

// Fonction pour envoyer les parties à l'API
function envoyer_partie_a_api($team1_id, $team2_id, $dateStart, $dateEnd) {
    global $apiUrl;
    
    // Préparer l'URL de la requête API
    $api_url = "$apiUrl?team1_id=$team1_id&team2_id=$team2_id&niveau=1&date_start=$dateStart&date_end=$dateEnd";
    
    // Initialiser cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    // Exécuter la requête
    $response = curl_exec($ch);
    
    // Vérifier les erreurs
    if (curl_errno($ch)) {
        $error_message = curl_error($ch);
        curl_close($ch);
        return "Erreur lors de l'envoi à l'API pour les équipes $team1_id et $team2_id : $error_message";
    }
    
    // Fermer la session cURL
    curl_close($ch);
    
    return $response;
}

// Fonction principale pour générer et placer les parties
function process_matches($conn, $dateStart, $dateEnd, $placed_matches) {
    $all_matches = array();
    $total_parties = 0;
    $series_poules = get_series_poules($conn);

    // Boucle principale pour générer les matchs pour chaque série et poule
    foreach ($series_poules as $serie => $poules) {
        foreach ($poules as $poule) {
            // Requête SQL pour récupérer les équipes de la série et poule actuelles
            $sql = "SELECT * FROM inscriptions WHERE serie = '$serie' AND poule = '$poule'";
            $result = $conn->query($sql);

            $teams = array();
            if ($result->num_rows > 0) {
                // Lire les équipes
                while ($row = $result->fetch_assoc()) {
                    $teams[] = $row['id']; // Assumer que 'id' est le champ qui représente les équipes
                }

                // Générer tous les matchs possibles
                $matches = afficher_parties($teams);
                $total_parties += count($matches);

                // Ajouter les matchs au tableau global
                foreach ($matches as $match) {
                    // Éviter de placer deux fois la même partie
                    if (!in_array($match, $placed_matches)) {
                        $all_matches[] = array(
                            "serie" => $serie,
                            "poule" => $poule,
                            "team1" => $match["team1"],
                            "team2" => $match["team2"]
                        );
                    }
                }
            }
        }
    }

    // Mélanger les parties
    shuffle($all_matches);

    // Placer les parties dans le calendrier en utilisant l'API
    $unplaced_matches_count = 0;
    foreach ($all_matches as $match) {
        $team1_id = $match["team1"];
        $team2_id = $match["team2"];

        // Envoyer chaque partie à l'API
        $response = envoyer_partie_a_api($team1_id, $team2_id, $dateStart, $dateEnd);
        if (strpos($response, 'Erreur') !== false) {
            $unplaced_matches_count++;
        } else {
            // Ajouter la partie au tableau des parties placées
            $placed_matches[] = $match;
        }
    }

    return array(
        'unplaced_matches_count' => $unplaced_matches_count,
        'total_parties' => $total_parties,
        'placed_matches' => $placed_matches
    );
}

// Initialisation pour suivre les parties placées
$placed_matches = array();

// Boucle pour s'assurer que le nombre de parties non placées est stable
$previous_unplaced_count = -1;
$stable_count_found = false;

while (!$stable_count_found) {
    $result = process_matches($conn, $dateStart, $dateEnd, $placed_matches);
    $current_unplaced_count = $result['unplaced_matches_count'];
    $total_parties = $result['total_parties'];
    $placed_matches = $result['placed_matches']; // Met à jour les parties placées

    // Afficher les résultats
    if ($current_unplaced_count === $previous_unplaced_count) {
        $stable_count_found = true;
    } else {
        $previous_unplaced_count = $current_unplaced_count;
    }
}

// Chronométrage de la fin de l'exécution
$end_time = microtime(true);
$execution_time = $end_time - $start_time;

// Afficher le temps d'exécution
echo json_encode([
    'execution_time' => number_format($execution_time, 2),
    'unplaced_matches_count' => $current_unplaced_count,
    'total_parties' => $total_parties
]);

// Fermer la connexion à la base de données
$conn->close();
?>
