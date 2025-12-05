<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscriptions</title>
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
                <h1 class="h2">Inscriptions</h1>
            </div>



            <?php
            include("./assets/conn_bdd.php");

            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Compter le nombre d'équipes
                $stmt_count = $conn->prepare("SELECT COUNT(*) as total_equipes FROM inscriptions");
                $stmt_count->execute();
                $count_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
                $total_equipes = $count_result['total_equipes'];

                // Afficher le nombre d'équipes
                echo "<h3>Nombre total d'équipes inscrites : $total_equipes</h3>";

                // Récupérer les inscriptions
                $stmt = $conn->prepare("SELECT * FROM inscriptions");
                $stmt->execute();

                $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($inscriptions) > 0) {
                    echo '<table class="table table-striped">';
                    echo '<thead><tr><th>Horodateur</th><th>ID</th><th>Joueur 1</th><th>Joueur 2</th><th>Téléphone</th><th>Série</th><th>Poule</th><th>Lundi</th><th>Mardi</th><th>Mercredi</th><th>Jeudi</th><th>Vendredi</th><th>Indispo</th><th>Code</th></tr></thead>';
                    echo '<tbody>';
                    foreach($inscriptions as $row) {
                        echo '<tr>';
                        echo '<td>'.$row["Horodateur"].'</td>';
                        echo '<td>'.$row["id"].'</td>';
                        echo '<td>'.$row["Joueur 1"].'</td>';
                        echo '<td>'.$row["Joueur 2"].'</td>';
                        echo '<td>'.$row["telephone"].'</td>';
                        echo '<td>'.$row["serie"].'</td>';
                        echo '<td>'.$row["poule"].'</td>';
                        echo '<td>'.$row["lundi"].'</td>';
                        echo '<td>'.$row["mardi"].'</td>';
                        echo '<td>'.$row["mercredi"].'</td>';
                        echo '<td>'.$row["jeudi"].'</td>';
                        echo '<td>'.$row["vendredi"].'</td>';
                        echo '<td>'.$row["periodes_indispo"].'</td>';
                        echo '<td>'.$row["code"].'</td>';
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
