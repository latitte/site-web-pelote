<?php
// Inclure la connexion à la base de données
include './conn_bdd.php';

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Récupérer les données envoyées par le formulaire
$ids = $_POST['ids'];
$horodateurs = $_POST['Horodateur'];
$joueur1s = $_POST['Joueur_1'];
$joueur2s = $_POST['Joueur_2'];
$telephones = $_POST['telephone'];
$series = $_POST['serie'];
$poules = $_POST['poule'];
$lundis = $_POST['lundi'];
$mar_dis = $_POST['mardi'];
$mercredis = $_POST['mercredi'];
$jeudis = $_POST['jeudi'];
$vendredis = $_POST['vendredi'];
$samedis = $_POST['samedi'];
$dimanches = $_POST['dimanche'];
$periodes_indispos = $_POST['periodes_indispo'];
$codes = $_POST['code'];
$commentaires = $_POST['commentaire'];

foreach ($ids as $index => $id) {
    $horodateur = $horodateurs[$index];
    $joueur1 = $joueur1s[$index];
    $joueur2 = $joueur2s[$index];
    $telephone = $telephones[$index];
    $serie = $series[$index];
    $poule = $poules[$index];
    $lundi = $lundis[$index];
    $mardi = $mar_dis[$index];
    $mercredi = $mercredis[$index];
    $jeudi = $jeudis[$index];
    $vendredi = $vendredis[$index];
    $samedi = $samedis[$index];
    $dimanche = $dimanches[$index];
    $periodes_indispo = $periodes_indispos[$index];
    $code = $codes[$index];
    $commentaire = $commentaires[$index];
    $forfait = $_POST['forfait_' . $id];
    $paye = $_POST['paye_' . $id];

    // Créer la requête SQL de mise à jour pour chaque enregistrement
    $sql = "UPDATE inscriptions SET 
        Horodateur = '$horodateur',
        `Joueur 1` = '$joueur1', 
        `Joueur 2` = '$joueur2',
        telephone = '$telephone',
        serie = '$serie',
        poule = '$poule',
        lundi = '$lundi',
        mardi = '$mardi',
        mercredi = '$mercredi',
        jeudi = '$jeudi',
        vendredi = '$vendredi',
        samedi = '$samedi',
        dimanche = '$dimanche',
        periodes_indispo = '$periodes_indispo',
        code = '$code',
        commentaire = '$commentaire',
        forfait = '$forfait',
        paye = '$paye'
        WHERE id = $id";

    // Exécuter la requête et vérifier le résultat
    if ($conn->query($sql) !== TRUE) {
        echo "Erreur lors de la mise à jour de l'enregistrement avec l'ID $id : " . $conn->error . "<br>";
    }
}

// Afficher un message de succès
echo "Les données ont été mises à jour avec succès.";

// Fermer la connexion
$conn->close();
?>
