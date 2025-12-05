<?php
include("../logiciel/assets/extract_parametre.php");
$series = explode(",", $parametres['series']);


// Détection de la langue à partir de l'URL, sinon défaut en français
$lang_code = isset($_GET['lang']) ? $_GET['lang'] : 'fr';

// Chargement du fichier de langue approprié
if ($lang_code == 'eus') {
    include("./assets/lang/lang_eus.php");
} else {
    include("./assets/lang/lang_fr.php");
}


// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);


// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Requête SQL pour récupérer les données du classement triées par points (descendant) et average (descendant)
$sql = "SELECT place, equipe, joueurs, points, average, serie, niveau FROM classement ORDER BY place asc";
$result = $conn->query($sql);

// Tableau pour stocker les séries
$classement_par_serie = [];

if ($result->num_rows > 0) {
    // Organiser les données par série
    while($row = $result->fetch_assoc()) {
        $classement_par_serie[$row['serie']][] = $row;
    }
} else {
    echo "Aucune équipe trouvée.";
}

// Fermer la connexion
$conn->close();

// Définir l'ordre des séries
$ordre_series = $series;

// Trier les séries selon l'ordre défini
uksort($classement_par_serie, function($a, $b) use ($ordre_series) {
    $index_a = array_search($a, $ordre_series);
    $index_b = array_search($b, $ordre_series);

    // Si les deux séries sont dans l'ordre défini
    if ($index_a !== false && $index_b !== false) {
        return $index_a - $index_b;
    }
    // Si l'une des séries est dans l'ordre défini, elle est prioritaire
    if ($index_a !== false) {
        return -1;
    }
    if ($index_b !== false) {
        return 1;
    }
    // Si aucune des séries n'est dans l'ordre défini, les trier par ordre alphabétique
    return strcmp($a, $b);
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classement par Série</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f8f8;
            color: #333;
        }

        .container {
    max-width: 1000px;
    margin: 40px auto;
    padding: 20px;
     /*background: #fff; */
    border-radius: 12px;
    /* box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);*/
        }
        h1 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 20px;
            color: #1d1d1f;
        }

        h2 {
            font-size: 1.5rem;
            color: #0070c9;
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            text-align: left;
            padding: 12px 15px;
        }

        th {
            background-color: #f5f5f7;
            font-weight: 600;
            color: #6e6e73;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #e5e5ea;
        }

        td {
            color: #1d1d1f;
            border-bottom: 1px solid #e5e5ea;
        }

        .rank {
            font-weight: bold;
            color: #0070c9;
        }

        .team-name {
            font-weight: 500;
        }

        .score {
            font-weight: 500;
            color: #ff2d55;
        }

        .elimine {
            background-color: #ffe5e5!important; /* Fond rouge clair pour signaler l'élimination */
            color: #ff2d55; /* Texte rouge */
        }

        .footer {
            text-align: center;
            font-size: 0.9rem;
            color: #6e6e73;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Classement par Série</h1>
        <?php foreach ($classement_par_serie as $serie => $equipes): ?>
            <h2><?php echo htmlspecialchars($serie); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?php echo $lang['menu-classement'] ?></th>
                        <th><?php echo $lang['equipe'] ?></th>
                        <th><?php echo $lang['joueurs'] ?></th>
                        <th><?php echo $lang['points'] ?></th>
                        <th><?php echo $lang['average'] ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($equipes as $equipe): ?>
                        <?php
                        // Vérifier si l'équipe est éliminée
                        $elimine = strtolower($equipe['niveau']) === 'elimine';

                        // Exemple de chaîne contenant les mots à découper
                        $chaine = htmlspecialchars($equipe['joueurs']); // Vous pouvez remplacer ceci par votre variable contenant la chaîne

                        // Découpe la chaîne en mots
                        $equipe_tableau = explode(' et ', $chaine); // Décompose en mots, utilisant l'espace comme délimiteur
                        



                        ?>
                        <tr class="<?php echo $elimine ? 'elimine' : ''; ?>">
                            <td class="rank"><?php echo htmlspecialchars($equipe['place']); ?></td>

                            <td class="team-name"><?php echo htmlspecialchars($equipe['equipe']); ?></td>

                            <td><?php echo "{$equipe_tableau[0]} {$lang['et']} {$equipe_tableau[1]}"; ?></td>

                            <td class="score"><?php echo htmlspecialchars($equipe['points']); ?></td>
                            <td><?php echo htmlspecialchars($equipe['average']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
        <!-- <div class="footer">
            &copy; 2024 ILHARRE - Tous droits réservés.
        </div> -->
    </div>
</body>
</html>
