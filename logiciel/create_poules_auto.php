<?php

include("./assets/extract_parametre.php");

// Récupère la valeur max d'équipe dans chaque série
$series_bdd = $parametres['series'];
$series_bdd = explode(",", $series_bdd);

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Tableau de configuration des poules en fonction du nombre d'équipes
$configPools = [
    3 => ['poules3' => 1, 'poules4' => 0, 'poules5' => 0],
    4 => ['poules3' => 0, 'poules4' => 1, 'poules5' => 0],
    5 => ['poules3' => 0, 'poules4' => 0, 'poules5' => 1],
    6 => ['poules3' => 2, 'poules4' => 0, 'poules5' => 0],
    7 => ['poules3' => 1, 'poules4' => 1, 'poules5' => 0],
    8 => ['poules3' => 0, 'poules4' => 2, 'poules5' => 0],
    9 => ['poules3' => 0, 'poules4' => 1, 'poules5' => 1],
    10 => ['poules3' => 0, 'poules4' => 0, 'poules5' => 2],
    11 => ['poules3' => 1, 'poules4' => 2, 'poules5' => 0],
    12 => ['poules3' => 0, 'poules4' => 3, 'poules5' => 0],
    13 => ['poules3' => 0, 'poules4' => 2, 'poules5' => 1],
    14 => ['poules3' => 0, 'poules4' => 1, 'poules5' => 2],
    15 => ['poules3' => 0, 'poules4' => 0, 'poules5' => 3],
    16 => ['poules3' => 0, 'poules4' => 4, 'poules5' => 0],
    17 => ['poules3' => 0, 'poules4' => 3, 'poules5' => 1],
    18 => ['poules3' => 0, 'poules4' => 2, 'poules5' => 2],
    19 => ['poules3' => 0, 'poules4' => 1, 'poules5' => 3],
    20 => ['poules3' => 0, 'poules4' => 0, 'poules5' => 4],
    // Ajoutez d'autres configurations selon vos besoins
];

// Alphabet pour les noms de poules
$alphabet = range('A', 'Z');

// Fonction pour créer les poules en fonction de la configuration définie
function createPools($teams, $configPools, $alphabet) {
    $totalTeams = count($teams);
    $pools = [];
    $index = 0;
    $alphabetIndex = 0;

    // Vérifier la configuration pour le nombre d'équipes actuel
    if (isset($configPools[$totalTeams])) {
        $poules3 = $configPools[$totalTeams]['poules3'];
        $poules4 = $configPools[$totalTeams]['poules4'];
        $poules5 = $configPools[$totalTeams]['poules5'];


        // Création des poules de 3 équipes
        for ($i = 0; $i < $poules3; $i++) {
            $pool = array_slice($teams, $index, 3);
            foreach ($pool as &$team) {
                $team['poule'] = '' . $alphabet[$alphabetIndex]; // Attribution de la poule à chaque équipe
            }
            $pools[] = $pool;
            $index += 3;
            $alphabetIndex++;
        }


        // Création des poules de 5 équipes
        for ($i = 0; $i < $poules5; $i++) {
            $pool = array_slice($teams, $index, 5);
            foreach ($pool as &$team) {
                $team['poule'] = '' . $alphabet[$alphabetIndex]; // Attribution de la poule à chaque équipe
            }
            $pools[] = $pool;
            $index += 5;
            $alphabetIndex++;
        }

        // Création des poules de 4 équipes
        for ($i = 0; $i < $poules4; $i++) {
            $pool = array_slice($teams, $index, 4);
            foreach ($pool as &$team) {
                $team['poule'] = '' . $alphabet[$alphabetIndex]; // Attribution de la poule à chaque équipe
            }
            $pools[] = $pool;
            $index += 4;
            $alphabetIndex++;
        }

        return $pools;
    } else {
        echo "Configuration non définie pour $totalTeams équipes.";
        return [];
    }
}

// Fonction pour vérifier si toutes les équipes d'une poule ont au moins un créneau commun avec toutes les autres équipes
function hasCommonSlotForAll($pool) {
    $n = count($pool);

    // On compare chaque équipe avec toutes les autres
    for ($i = 0; $i < $n; $i++) {
        $team1 = $pool[$i]['dispo'];

        for ($j = 0; $j < $n; $j++) {
            if ($i == $j) continue;
            $team2 = $pool[$j]['dispo'];

            // Vérifier s'il y a un créneau commun
            if (strpos($team1 & $team2, '1') === false) {
                return false; // Pas de créneau commun trouvé
            }
        }
    }
    return true; // Tous les créneaux sont communs
}

// Ouvrir le fichier resultats_poules.html pour l'écriture
$file = fopen('resultats_poules.html', 'w');

// Vérifier si le fichier est ouvert avec succès
if (!$file) {
    die("Erreur lors de l'ouverture du fichier resultats_poules.html.");
}

// Tableau des séries à traiter
$series = $series_bdd;

// Boucle pour traiter chaque série
foreach ($series as $serie) {
    // Requête SQL pour récupérer les équipes inscrites pour chaque série
    $sql = "SELECT * FROM inscriptions WHERE serie = '$serie'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $teams = [];

        // Récupérer les équipes et leurs disponibilités
        while ($row = $result->fetch_assoc()) {
            $dispo = $row['lundi'] . $row['mardi'] . $row['mercredi'] . $row['jeudi'] . $row['vendredi'] . $row['samedi'] . $row['dimanche'];
            $teams[] = [
                'id' => $row['id'],
                'joueur1' => $row['Joueur 1'],
                'joueur2' => $row['Joueur 2'],
                'dispo' => $dispo,
                'lundi' => $row['lundi'],
                'mardi' => $row['mardi'],
                'mercredi' => $row['mercredi'],
                'jeudi' => $row['jeudi'],
                'vendredi' => $row['vendredi'],
                'samedi' => $row['samedi'],
                'dimanche' => $row['dimanche'],
                'poule' => '', // Initialisation de la poule
            ];
        }

        // Trier les équipes par disponibilité globale
        usort($teams, function ($a, $b) {
            return strcmp($a['dispo'], $b['dispo']);
        });

        // Appel de la fonction pour créer les poules
        $pools = createPools($teams, $configPools, $alphabet);

        // Mise à jour de la base de données avec la poule attribuée à chaque équipe
        foreach ($pools as $pool) {
            foreach ($pool as $team) {
                $team_id = $team['id'];
                $poule = $team['poule'];
                $update_sql = "UPDATE inscriptions SET poule = '$poule' WHERE id = '$team_id'";
                if ($conn->query($update_sql) !== TRUE) {
                    fwrite($file, "Erreur lors de la mise à jour de la poule pour l'équipe $team_id : " . $conn->error);
                }
            }
        }

        // Écrire les poules pour chaque série dans des tableaux HTML séparés avec disponibilités
        fwrite($file, "<h2>$serie</h2>");
        foreach ($pools as $i => $pool) {
            $perfectCompatibility = hasCommonSlotForAll($pool);
            fwrite($file, "<h3>Poule " . $alphabet[$i] . " <span style='color:" . ($perfectCompatibility ? "green" : "red") . ";'>●</span></h3>");
            fwrite($file, "<table border='1'>");
            fwrite($file, "<tr><th>id</th><th>Joueur 1</th><th>Joueur 2</th><th>Disponibilités</th><th>Poule</th></tr>");
            foreach ($pool as $team) {
                fwrite($file, "<tr>");
                fwrite($file, "<td>" . htmlspecialchars($team['id']) . "</td>");
                fwrite($file, "<td>" . htmlspecialchars($team['joueur1']) . "</td>");
                fwrite($file, "<td>" . htmlspecialchars($team['joueur2']) . "</td>");
                fwrite($file, "<td>" . $team['dispo'] . "</td>");
                fwrite($file, "<td>" . $team['poule'] . "</td>");
                fwrite($file, "</tr>");
            }
            fwrite($file, "</table><br>");
        }
    } else {
        fwrite($file, "Aucune équipe trouvée pour la série $serie.");
    }
}

$conn->close();

// Fermer le fichier après avoir terminé l'écriture
fclose($file);

// Inclure le fichier reclasse_from_poule.php après la fin du traitement
include('reclasse_from_poule.php');

?>

<!-- Ajout du loader et du script JavaScript -->
<div id="loader" style="display: block; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000;">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Chargement...</span>
    </div>
</div>

<script>
    // Cacher le loader une fois que la page est chargée
    window.addEventListener('load', function() {
        var loader = document.getElementById('loader');
        loader.style.display = 'none';
    });
</script>
