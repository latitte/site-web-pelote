<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
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
                <h1 class="h2">Gestion Phases Finales</h1>
            </div>

            <h2>Paramètres</h2>

            <?php
            // Connexion à la base de données
            include("./assets/conn_bdd.php");

            // Créer la connexion
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Vérifier la connexion
            if ($conn->connect_error) {
                die("Échec de la connexion : " . $conn->connect_error);
            }

            // Récupérer les valeurs existantes
            $params = [
                'startDateBarrage' => '',
                'endDateBarrage' => '',
            ];

            $sql = "SELECT parametre, valeur FROM parametre WHERE parametre IN ('startDateBarrage', 'endDateBarrage', 'startDateFinales', 'endDateFinales', 'phase_finale_affich')";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $params[$row['parametre']] = $row['valeur'];
                }
            }



            if ($_SERVER["REQUEST_METHOD"] == "POST") {

                $startDateBarrage = $_POST['startDateBarrage'];
                $endDateBarrage = $_POST['endDateBarrage'];
            
                $startDateFinales = $_POST['startDateFinales'];
                $endDateFinales = $_POST['endDateFinales'];
            
                // Vérifiez si la case est cochée, sinon définissez à 0
                $phase_finale_affich = isset($_POST['phase_finale_affich']) ? 1 : 0;
            
                // Préparer les requêtes pour chaque paramètre
                $paramsToUpdate = [
                    'startDateBarrage' => $startDateBarrage,
                    'endDateBarrage' => $endDateBarrage,
                    'startDateFinales' => $startDateFinales,
                    'endDateFinales' => $endDateFinales,
                    'phase_finale_affich'=> $phase_finale_affich,
                ];
            
                $success = true;
                foreach ($paramsToUpdate as $key => $value) {
                    $stmt = $conn->prepare("UPDATE parametre SET valeur=? WHERE parametre=?");
                    $stmt->bind_param("ss", $value, $key);
            
                    if (!$stmt->execute()) {
                        $success = false;
                        echo "<div class='alert alert-danger mt-3'>Erreur : " . $stmt->error . "</div>";
                        break;
                    }
            
                    $stmt->close();
                }
            
                // Mettre à jour les valeurs affichées après l'enregistrement
                foreach ($paramsToUpdate as $key => $value) {
                    $params[$key] = $value;
                }
            
                // Récupérer et traiter les séries à partir du POST
                $newSeries = [];
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'serie') === 0) {
                        $newSeries[] = $value;
                    }
                }
            
                if ($success) {
                    echo "<div class='alert alert-success mt-3'>Les paramètres ont été enregistrés avec succès.</div>";
                }
            }
            
            // Fermer la connexion
            $conn->close();
            ?>
            

            <form method="POST" action="">

                <div class="form-group">
                    <label for="startDateBarrage">Date de début prévue des barrages :</label>
                    <input type="date" class="form-control" id="startDateBarrage" name="startDateBarrage" value="<?php echo $params['startDateBarrage']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="endDateBarrage">Date de fin prévue des barrages :</label>
                    <input type="date" class="form-control" id="endDateBarrage" name="endDateBarrage" value="<?php echo $params['endDateBarrage']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="startDateFinales">Date de début prévue des phases finales :</label>
                    <input type="date" class="form-control" id="startDateFinales" name="startDateFinales" value="<?php echo $params['startDateFinales']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="endDateFinales">Date de fin prévue des phases finales :</label>
                    <input type="date" class="form-control" id="endDateFinales" name="endDateFinales" value="<?php echo $params['endDateFinales']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="phase_finale_affich">Afficher l'arbre de tournoi :</label>
                    <input type="checkbox" id="phase_finale_affich" name="phase_finale_affich" value="1" <?php if ($params['phase_finale_affich'] == 1) echo 'checked'; ?>>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>