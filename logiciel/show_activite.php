<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activite</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Menu vertical -->
        <?php include("./assets/menu.php"); ?>

        <!-- Contenu principal -->
        <main role="main" class="container">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Activité</h1>
            </div>

            <?php
            include("./assets/conn_bdd.php");

            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Compter le nombre d'équipes
                $stmt_count = $conn->prepare("SELECT COUNT(*) as total_activite FROM activite");
                $stmt_count->execute();
                $count_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
                $total_activite = $count_result['total_activite'];

                // Afficher le nombre d'équipes
                echo "<h3>Nombre total de reports : $total_activite</h3>";

                // Récupérer les inscriptions
                $stmt = $conn->prepare("SELECT * FROM activite");
                $stmt->execute();

                $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($inscriptions) > 0) {
                    echo '<table class="table table-striped">';
                    echo '<thead><tr><th>Horodateur</th><th>ID</th><th>Type</th><th>Joueur A</th><th>Joueur B</th><th>detail</th></tr></thead>';
                    echo '<tbody>';
                    foreach($inscriptions as $row) {
                        echo '<tr>';
                        echo '<td>'.$row["horodateur"].'</td>';
                        echo '<td>'.$row["id"].'</td>';
                        echo '<td>'.$row["type"].'</td>';
                        echo '<td>'.$row["equipeA"].'</td>';
                        echo '<td>'.$row["equipeB"].'</td>';
                        echo '<td>'.$row["detail"].'</td>';

                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                } else {
                    echo '<p>Aucune inscription trouvée.</p>';
                }
            } catch(PDOException $e) {
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
