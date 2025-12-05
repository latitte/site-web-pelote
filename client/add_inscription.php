<?php
include("../logiciel/assets/extract_parametre.php");

$jours_dispo_bdd = $parametres['jours_dispo'];
$jours_disponibles = explode(", ", $jours_dispo_bdd);

// Définir les horaires dynamiquement
$heures_dispo_bdd = $parametres['heures_dispo'];
$horaires = explode(", ", $heures_dispo_bdd);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $joueur1 = $_POST['joueur1'];
    $joueur2 = $_POST['joueur2'];
    $telephone = $_POST['telephone'];
    $serie = $_POST['serie'];

    // Fonction pour générer la chaîne de disponibilité dynamiquement
    function generateDispoString($jour, $dispos, $jours_disponibles, $horaires) {
        if (!in_array($jour, $jours_disponibles)) {
            return str_repeat('0', count($horaires)); // Retourne une chaîne de '0' si le jour est désactivé
        }
        $dispo_str = str_repeat('1', count($horaires)); // Par défaut, toutes les heures sont disponibles
        foreach ($dispos as $dispo) {
            $index = array_search($dispo, $horaires);
            if ($index !== false) {
                $dispo_str[$index] = '0'; // Marquer l'heure comme indisponible
            }
        }
        return $dispo_str;
    }

    // Traitement des indisponibilités par jour
    $indispo_lundi = generateDispoString('Lun', isset($_POST['indispo_Lun']) ? $_POST['indispo_Lun'] : [], $jours_disponibles, $horaires);
    $indispo_mardi = generateDispoString('Mar', isset($_POST['indispo_Mar']) ? $_POST['indispo_Mar'] : [], $jours_disponibles, $horaires);
    $indispo_mercredi = generateDispoString('Mer', isset($_POST['indispo_Mer']) ? $_POST['indispo_Mer'] : [], $jours_disponibles, $horaires);
    $indispo_jeudi = generateDispoString('Jeu', isset($_POST['indispo_Jeu']) ? $_POST['indispo_Jeu'] : [], $jours_disponibles, $horaires);
    $indispo_vendredi = generateDispoString('Ven', isset($_POST['indispo_Ven']) ? $_POST['indispo_Ven'] : [], $jours_disponibles, $horaires);
    $indispo_samedi = generateDispoString('Sam', isset($_POST['indispo_Sam']) ? $_POST['indispo_Sam'] : [], $jours_disponibles, $horaires);
    $indispo_dimanche = generateDispoString('Dim', isset($_POST['indispo_Dim']) ? $_POST['indispo_Dim'] : [], $jours_disponibles, $horaires);

    // Traitement des périodes d'indisponibilité
    $periodes_indispo = [];
    $index = 1;
    while (isset($_POST["periode_indispo_debut_$index"])) {
        $debut = $_POST["periode_indispo_debut_$index"];
        $fin = $_POST["periode_indispo_fin_$index"];
        if ($debut && $fin) {
            $periodes_indispo[] = "$debut au $fin";
        }
        $index++;
    }
    $periodes_indispo_str = implode(', ', $periodes_indispo);

    // Vérification si periodes_indispo_str est vide
    if (empty($periodes_indispo_str)) {
        $periodes_indispo_str = '0000-00-00 au 0000-00-00';
    }

    $timestamp = date('Y-m-d H:i:s');

    // Connexion à la base de données


    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("La connexion a échoué: " . $conn->connect_error);
    }

    // Préparation de la requête SQL
    $sql = "INSERT INTO inscriptions (Horodateur, `Joueur 1`, `Joueur 2`, Telephone, Serie, lundi, mardi, mercredi, jeudi, vendredi, samedi, dimanche, periodes_indispo)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssss", $timestamp, $joueur1, $joueur2, $telephone, $serie, $indispo_lundi, $indispo_mardi, $indispo_mercredi, $indispo_jeudi, $indispo_vendredi, $indispo_samedi, $indispo_dimanche, $periodes_indispo_str);

    // Exécution de la requête SQL
    if ($stmt->execute() === TRUE) {
        // echo "Inscription réussie";
        header('Location: ./inscription_reussie.php');
        exit();
    } else {
        echo "Erreur: " . $sql . "<br>" . $conn->error;
    }

    // Fermeture de la connexion
    $stmt->close();
    $conn->close();
}
?>
