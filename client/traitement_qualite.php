<?php
// Inclusion du fichier de connexion à la base de données
include('../logiciel/assets/conn_bdd.php');

// Récupérer les données du formulaire
$ergonomie_facilite = $_POST['ergonomie_facilite'];
$temps_chargement = $_POST['temps_chargement'];
$aide_organisation = $_POST['aide_organisation'];
$clarte_informations = $_POST['clarte_informations'];
$utilite_site = $_POST['utilite_site'];
$suggestions = $_POST['suggestions'];
$qualite_whatsapp = $_POST['qualite_whatsapp'];

// Vérification de la connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}

// Préparer la requête d'insertion
$sql = "INSERT INTO satisfaction_enquete (ergonomie_facilite, temps_chargement, aide_organisation, clarte_informations, utilite_site, qualite_whatsapp, suggestions)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

// Préparer la déclaration
$stmt = $conn->prepare($sql);

// Vérifier si la préparation a réussi
if ($stmt === false) {
    die("Erreur de préparation de la requête : " . $conn->error);
}

// Lier les paramètres à la déclaration
$stmt->bind_param("sssssss", $ergonomie_facilite, $temps_chargement, $aide_organisation, $clarte_informations, $utilite_site, $qualite_whatsapp, $suggestions);

// Exécuter la requête
if ($stmt->execute()) {
    header('Location: ./merci.php');
} else {
    header('Location: ./merci.php');
}

// Fermer la déclaration et la connexion
$stmt->close();
$conn->close();
?>
