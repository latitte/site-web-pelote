<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barrages</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 20px;
        }
        .table {
            margin-top: 20px;
        }
        .table thead th {
            background-color: #f8f9fa;
            border-top: 0;
            border-bottom: 0;
            font-weight: bold;
        }
        .table th, .table td {
            border: 0;
            vertical-align: middle;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include("./assets/menu.php"); ?>
    <div class="container">
        <h1 class="text-center mt-5 mb-4">Barrages par série</h1>

<?php


include("./assets/extract_parametre.php");


// Dates de plage pour les matchs
$dateStart = $parametres['startDateBarrage'];
$dateEnd = $parametres['endDateBarrage'];

$series = $parametres['series'];
$series = explode(",", $series);


// Démarrer la mise en mémoire tampon de sortie
ob_start();



// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    echo "<a style='color:red;'>Erreur de connexion : " . $conn->connect_error . "</a><br>";
    $conn->close();
    $output = ob_get_clean();
    file_put_contents('resultats_barrages.html', $output);
    exit;
}

// Liste des séries à traiter
// $series = ['Première série', 'Deuxième série', 'Troisième série', 'Mixte', 'Féminine'];

// Fonction pour calculer les équipes à éliminer et les phases finales
function equipesAEliminerEtPhase($nombreEquipes) {
    $finalesPossibles = [
        16 => '1/8 finale',
        8 => '1/4 finale',
        4 => '1/2 finale',
        2 => 'Finale'
    ];

    $phaseFinale = '';
    $equipesRestantes = 0;

    foreach ($finalesPossibles as $finales => $phase) {
        if ($nombreEquipes >= $finales) {
            $equipesRestantes = $finales;
            $phaseFinale = $phase;
            break;
        }
    }

    $equipesAEliminer = $nombreEquipes - $equipesRestantes;
    $nbr_barrage = $equipesAEliminer * 2;

    return [$equipesAEliminer, $phaseFinale, $nbr_barrage];
}

foreach ($series as $serie) {
    $serieCode = substr($serie, 0, 1); // Prendre la première lettre de la série
    $niveau = "2" . $serieCode; // Ajouter le niveau "2" au code de la série

    // Récupérer les lignes pour la série actuelle
    $sql = "SELECT * FROM classement WHERE serie = '$serie' ORDER BY place ASC";
    $result = $conn->query($sql);

    if ($result === false) {
        echo "<a style='color:red;'>Erreur SQL pour la série $serie : " . $conn->error . "</a><br>";
        continue; // Passer à la série suivante en cas d'erreur SQL
    }

    $equipes = [];
    if ($result->num_rows > 0) {
        // Remplir le tableau avec les équipes
        while ($row = $result->fetch_assoc()) {
            $equipes[] = $row['equipe'];
        }
    } else {
        echo "<a style='color:red;'>0 résultats pour la série $serie</a><br>";
        continue; // Passer à la série suivante s'il n'y a pas de résultats
    }

    $nombreEquipes = count($equipes);

    if ($nombreEquipes == 16 || $nombreEquipes == 8 || $nombreEquipes == 4 || $nombreEquipes == 2) {
        echo "Pour $nombreEquipes équipes dans la série $serie, il faut commencer directement en ";
        if ($nombreEquipes == 16) {
            echo "1/8 finale.";
        } elseif ($nombreEquipes == 8) {
            echo "1/4 finale.";
        } elseif ($nombreEquipes == 4) {
            echo "1/2 finale.";
        } else {
            echo "Finale.";
        }
        echo "<br>";
    } else {
        list($eliminer, $phase, $nbr_barrage) = equipesAEliminerEtPhase($nombreEquipes);

        $equipesBarrage = array_slice($equipes, -$nbr_barrage);

        $matchesBarrage = [];
        $total = count($equipesBarrage);
        for ($i = 0; $i < $total / 2; $i++) {
            $matchesBarrage[] = [$equipesBarrage[$i], $equipesBarrage[$total - $i - 1]];
        }

        echo "Pour $nombreEquipes équipes dans la série $serie, il faut commencer en $phase. Il faut éliminer $eliminer équipes pour atteindre les phases finales ($phase), ce qui nécessite $nbr_barrage équipes en barrage.<br>";
        echo "Les équipes suivantes doivent jouer en barrage :<br>";

        echo "<table border='1'>";
        echo "<tr><th>Match</th><th>Résultat</th></tr>";
        foreach ($matchesBarrage as $index => $match) {
            $team1_id = $match[0];
            $team2_id = $match[1];

            $apiUrl = "https://$var_tournoi.tournoi-pelote.com/logiciel/assets/api_calendar_add.php";
            $api_url = "$apiUrl?team1_id=$team1_id&team2_id=$team2_id&niveau=$niveau&date_start=$dateStart&date_end=$dateEnd";
            

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout pour éviter que la requête ne prenne trop de temps

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                $message = 'Erreur lors de l\'appel à l\'API : ' . curl_error($ch);
            } else {
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($http_code != 200) {
                    $message = 'Erreur HTTP : ' . $http_code;
                } else {
                    $result = json_decode($response, true);
                    $message = isset($result[0]['message']) ? $result[0]['message'] : 'Erreur inconnue';
                }
            }
            curl_close($ch);

            echo "<tr><td>" . htmlspecialchars($team1_id) . " contre " . htmlspecialchars($team2_id) . "</td><td>" . htmlspecialchars($message) . "</td></tr>";
        }
        echo "</table><br>";
    }
}

// Capturer la sortie et écrire dans le fichier HTML
$output = ob_get_clean();
file_put_contents('resultats_barrages.html', $output);
$conn->close();
?>
    </div>
</body>
</html>
