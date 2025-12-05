<?php
include("../logiciel/assets/extract_parametre.php");

// Récupére la valeur max d'équipe dans chaque série
$series = $parametres['series'];
$series = explode(",", $series);




// Détection de la langue à partir de l'URL, sinon défaut en français
$lang_code = isset($_GET['lang']) ? $_GET['lang'] : 'fr';

// Chargement du fichier de langue approprié
if ($lang_code == 'eus') {
    include("./assets/lang/lang_eus.php");
} else {
    include("./assets/lang/lang_fr.php");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['title_equipes']; ?></title>
    <link rel="stylesheet" href="./assets/style.css">
    <link rel="icon" type="image/x-icon" href="./assets/tournoi-pelote.ico">

</head>
<body>


<style>
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 30px 0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        background-color: #fff;
    }

    thead {
        background: linear-gradient(90deg, #e0e0e0, #f5f5f5);
    }

    th {
        padding: 16px 20px;
        font-size: 14px;
        font-weight: 600;
        color: #333;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #ddd;
    }

    td {
        padding: 16px 20px;
        font-size: 15px;
        color: #444;
        border-bottom: 1px solid #f0f0f0;
    }

    tbody tr:nth-child(even) {
        background-color: #fafafa;
    }

    tbody tr:hover {
        background-color: #f0f4ff;
        transition: background-color 0.2s ease;
    }

    .line-through {
        text-decoration: line-through;
        color: #bbb;
    }

    a.button-link {
        display: inline-block;
        padding: 6px 12px;
        background-color: #000000;
        color: white;
        border-radius: 10px;
        font-size: 14px;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.2s ease;
    }

    a.button-link:hover {
        background-color: #000000;
    }


@media (max-width: 768px) {
  .content {
    padding-right: 10px !important; /* espace à droite */
    box-sizing: border-box;         /* évite le débordement */
  }
  .content table {
    width: 100% !important;         /* occupe tout l'espace disponible du conteneur */
    margin: 30px 0 !important;
  }



    a.button-link {
        display: inline-block;
        padding: 0px 0px!important;
        background-color: #000000;
        color: white;
        border-radius: 10px;
        font-size: 14px;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.2s ease;
    }


}




</style>


<div class="popup">
    <div class="header">
        <h1><?php echo $lang['title_equipes']; ?></h1>
    </div>

    <div class="menu">
    <?php include("./assets/menu.php"); ?>
    </div>

    <div class="content">
<?php
    try {
        // Connexion à la base de données MySQL via PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Définir l'ordre des séries spécifié
        $ordered_series = $series;

        // Parcourir chaque série dans l'ordre spécifié
        foreach ($ordered_series as $serie) {
            echo '<h2>' . htmlspecialchars($serie) . '</h2>';

            // Récupérer toutes les poules distinctes pour cette série
            $stmt_poules = $conn->prepare("SELECT DISTINCT poule FROM inscriptions WHERE serie = :serie ORDER BY poule");
            $stmt_poules->bindParam(':serie', $serie);
            $stmt_poules->execute();
            $poules = $stmt_poules->fetchAll(PDO::FETCH_COLUMN);

            // Parcourir chaque poule pour afficher les inscriptions
            foreach ($poules as $poule) {
                echo '<h3>' . $lang['equipes_poules'] . ' ' . htmlspecialchars($poule) . '</h3>';

                // Récupérer les données d'inscription pour cette série et cette poule
                $stmt = $conn->prepare("SELECT * FROM inscriptions WHERE serie = :serie AND poule = :poule");
                $stmt->bindParam(':serie', $serie);
                $stmt->bindParam(':poule', $poule);
                $stmt->execute();
                $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Vérifier s'il y a des inscriptions à afficher
                if (count($inscriptions) > 0) {
                    echo '<table>';
                    echo '<thead>';
                    echo '<tr><th>#</th><th>' . $lang['equipes_joueurs'] . '</th><th>' . $lang['equipes_telephone'] . '</th><th></th></tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    foreach ($inscriptions as $inscription) {

                    // Affiche gris si l'équipe est forfait

                    if($inscription['forfait'] == 1){

                        echo '<tr>';
                        echo '<td style="text-decoration:line-through;">' . htmlspecialchars($inscription['id']) . '</td>';
                        echo '<td style="text-decoration:line-through;">' . htmlspecialchars($inscription['Joueur 1']) . ' & ' . htmlspecialchars($inscription['Joueur 2']) . '</td>';
                        echo '<td style="text-decoration:line-through;">' . htmlspecialchars($inscription['telephone']) . '</td>';
                        echo '<td><a class="button-link" href="details_equipe.php?id=' . urlencode($inscription['id']) . '">' . $lang['forfait'] . '</a></td>';
                        echo '</tr>';
                    }else{
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($inscription['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($inscription['Joueur 1']) . ' & ' . htmlspecialchars($inscription['Joueur 2']) . '</td>';
                        echo '<td>' . htmlspecialchars($inscription['telephone']) . '</td>';
                        echo '<td><a class="button-link" href="details_equipe.php?id=' . urlencode($inscription['id']) . '">+</a></td>';
                        echo '</tr>';
                    }
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
</div>


<footer>

<?php include("./assets/footer.php"); ?>


</footer>


</div>

</body>

</html>



