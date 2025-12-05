<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscriptions par Série et Poule</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Menu vertical -->
<?php
include("./assets/menu.php");
?>

        <!-- Contenu principal -->
        <main role="main" class="container">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Inscriptions par Série et Poule</h1>
            </div>

            <?php
// Informations de connexion à la base de données
include("./assets/extract_parametre.php");

$series_bdd = $parametres['series'];
$series_bdd = explode(",", $series_bdd);

try {
    // Connexion à la base de données MySQL via PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Définir l'ordre des séries spécifié
    $ordered_series = $series_bdd;

    // Parcourir chaque série dans l'ordre spécifié
    foreach ($ordered_series as $serie) {
        // Compter le nombre de joueurs pour cette série
        $stmt_count = $conn->prepare("SELECT COUNT(*) AS nb_joueurs FROM inscriptions WHERE serie = :serie");
        $stmt_count->bindParam(':serie', $serie);
        $stmt_count->execute();
        $result_count = $stmt_count->fetch(PDO::FETCH_ASSOC);

        // Afficher le nom de la série et le nombre de joueurs
        echo '<h2>' . htmlspecialchars($serie) . ' (Nombre de joueurs : ' . $result_count['nb_joueurs'] . ')</h2>';

        // Récupérer toutes les poules distinctes pour cette série
        $stmt_poules = $conn->prepare("SELECT DISTINCT poule FROM inscriptions WHERE serie = :serie ORDER BY poule");
        $stmt_poules->bindParam(':serie', $serie);
        $stmt_poules->execute();
        $poules = $stmt_poules->fetchAll(PDO::FETCH_COLUMN);

        // Parcourir chaque poule pour afficher les inscriptions
        foreach ($poules as $poule) {
            echo '<h3>Poule ' . htmlspecialchars($poule) . '</h3>';

            // Récupérer les données d'inscription pour cette série et cette poule
            $stmt = $conn->prepare("SELECT * FROM inscriptions WHERE serie = :serie AND poule = :poule");
            $stmt->bindParam(':serie', $serie);
            $stmt->bindParam(':poule', $poule);
            $stmt->execute();
            $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Vérifier s'il y a des inscriptions à afficher
            if (count($inscriptions) > 0) {
                echo '<table class="table table-striped">';
                echo '<thead>';
                echo '<tr><th>ID</th><th>Joueur 1</th><th>Joueur 2</th></tr>';
                echo '</thead>';
                echo '<tbody>';
                foreach ($inscriptions as $inscription) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($inscription['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($inscription['Joueur 1']) . '</td>';
                    echo '<td>' . htmlspecialchars($inscription['Joueur 2']) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
            } else {
                echo '<p>Aucune inscription trouvée pour la série ' . htmlspecialchars($serie) . ' et la poule ' . htmlspecialchars($poule) . '.</p>';
            }
        }
    }
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}
$conn = null;
?>

        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
