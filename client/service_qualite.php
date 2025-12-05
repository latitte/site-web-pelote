<?php
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
    <title>Enquête de Satisfaction</title>
    <link rel="stylesheet" href="./assets/style.css">
    <link rel="icon" type="image/x-icon" href="./assets/tournoi-pelote.ico">


<style>

.content input {
    width: auto!important;
}

</style>
</head>
<body>
<div class="popup">
    <div class="header">
        <h1><?php echo $lang['tournament']; ?></h1>
    </div>

    <div class="menu">
        <?php include("./assets/menu.php"); ?>
    </div>

    <div class="content">
    <?php
    include("../logiciel/assets/extract_parametre.php");

    $ouverture_form = $parametres['openRegistration'];

    $jours_dispo_bdd = $parametres['jours_dispo'];
    $jours_disponibles = explode(", ", $jours_dispo_bdd);

    $heures_dispo_bdd = $parametres['heures_dispo'];
    $heures_dispo = explode(", ", $heures_dispo_bdd);

    $series = explode(",", $parametres['series']);
    $quota_series = explode(",", $parametres['quota_series']);


    try {
        // Connexion à la base de données avec PDO
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Requête SQL pour compter les inscriptions par série
        $sql = "SELECT serie, COUNT(*) AS nombre_inscriptions
                FROM inscriptions
                GROUP BY serie";
        $stmt = $pdo->query($sql);

        // Initialisation du tableau pour stocker les inscriptions par série
        $nbr_inscriptions_par_serie = [];

        // Récupération des résultats et affectation au tableau
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $serie = $row['serie'];
            $nombre_inscriptions = $row['nombre_inscriptions'];
            $nbr_inscriptions_par_serie[$serie] = $nombre_inscriptions;
        }
    } catch (PDOException $e) {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
    }
    ?>

<!-------------------->

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qualité de Service</title>
    <style>


        h2 {
            text-align: center;
            color: #1c1c1e;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 1.1em;
            margin-bottom: 8px;
        }
        select, textarea {
            width: 90%;
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }

        button:hover {
            background-color: #005bb5;
        }
    </style>
</head>
<body>
    <div class="container">

        <form action="traitement_qualite.php" method="POST">
    <h2>Enquête de Satisfaction - Qualité de Service</h2>

    <div class="form-group">
        <label for="ergonomie_facilite">1. Comment évalueriez-vous l'ergonomie et la facilité de navigation sur notre site ?</label>
        <select id="ergonomie_facilite" name="ergonomie_facilite" required>
            <option value="">Sélectionnez une option</option>
            <option value="Excellente">Excellente</option>
            <option value="Bonne">Bonne</option>
            <option value="Moyenne">Moyenne</option>
            <option value="Médiocre">Médiocre</option>
            <option value="Insuffisante">Insuffisante</option>
        </select>
    </div>

    <div class="form-group">
        <label for="temps_chargement">2. Le temps de chargement des pages du site est-il satisfaisant ?</label>
        <select id="temps_chargement" name="temps_chargement" required>
            <option value="">Sélectionnez une option</option>
            <option value="Très satisfaisant">Très satisfaisant</option>
            <option value="Satisfaisant">Satisfaisant</option>
            <option value="Moyennement satisfaisant">Moyennement satisfaisant</option>
            <option value="Insatisfaisant">Insatisfaisant</option>
        </select>
    </div>

    <div class="form-group">
        <label for="aide_organisation">3. Avez-vous trouvé que l'organisation a bien répondu à vos attentes et vous a apporté l'aide nécessaire ?</label>
        <select id="aide_organisation" name="aide_organisation" required>
            <option value="">Sélectionnez une option</option>
            <option value="Tout à fait">Tout à fait</option>
            <option value="Plutôt bien">Plutôt bien</option>
            <option value="Moyennement">Moyennement</option>
            <option value="Pas vraiment">Pas vraiment</option>
            <option value="Pas du tout">Pas du tout</option>
        </select>
    </div>

    <div class="form-group">
        <label for="clarte_informations">4. Les informations présentes sur le site étaient-elles claires et faciles à comprendre ?</label>
        <select id="clarte_informations" name="clarte_informations" required>
            <option value="">Sélectionnez une option</option>
            <option value="Très claires">Très claires</option>
            <option value="Claires">Claires</option>
            <option value="Moyennement claires">Moyennement claires</option>
            <option value="Peu claires">Peu claires</option>
            <option value="Pas claires du tout">Pas claires du tout</option>
        </select>
    </div>

    <div class="form-group">
        <label for="utilite_site">5. Trouvez-vous que le site soit utile pour le tournoi et qu'il apporte des éléments bénéfiques à son déroulement ?</label>
        <select id="utilite_site" name="utilite_site" required>
            <option value="">Sélectionnez une option</option>
            <option value="Très utile">Très utile</option>
            <option value="Utile">Utile</option>
            <option value="Peu utile">Peu utile</option>
            <option value="Pas du tout utile">Pas du tout utile</option>
        </select>
    </div>


    <div class="form-group">
        <label for="qualite_whatsapp">6. Trouvez-vous que la communauté WhatsApp soit suffisamment active et qu'elle vous apporte une aide utile ?</label>
        <select id="qualite_whatsapp" name="qualite_whatsapp" required>
            <option value="">Sélectionnez une option</option>
            <option value="Très utile">Très utile</option>
            <option value="Utile">Utile</option>
            <option value="Pas du tout utile">Pas du tout utile</option>
            <option value="Trop active">Trop active</option>
        </select>
    </div>

    <div class="form-group">
        <label for="suggestions">7. Avez-vous des suggestions ou des améliorations à proposer pour améliorer l'expérience sur le site ou l'organisation ? Ou bien souhaitez-vous justifier vos réponses précédentes ?</label>
        <textarea id="suggestions" name="suggestions" rows="4" placeholder="Vos commentaires ou suggestions ici..."></textarea>
    </div>

    <button style="margin-bottom:10%;" type="submit">Envoyer mon avis</button>
</form>

    </div>
</body>
</html>



<!-------------------->







<footer>

<?php include("./assets/footer.php"); ?>


</footer>

    </div>
    
</div>

</body>


</html>