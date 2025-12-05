<?php
include '../logiciel/assets/conn_bdd.php';

// Connexion à la base de données
try {
    $dsn = "mysql:host=$servername;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}

// Récupération des données du formulaire
$arbitre_id = $_POST['arbitre'];
$date = $_POST['date'];

// Mise à jour de la permanence de l'arbitre
$query = $pdo->prepare("UPDATE arbitre SET permanence = CONCAT(permanence, ', ', :date) WHERE prenom = :arbitre_id");
$query->execute([
    ':date' => $date,
    ':arbitre_id' => $arbitre_id
]);

// Vérifier si la mise à jour a eu lieu
if ($query->rowCount() > 0) {
    // Redirection vers la page de remerciement si la mise à jour a réussi
    header("Location: ./merci.php");
    exit();
} else {
    // Afficher un message d'erreur ou rediriger vers une autre page si aucune mise à jour n'a eu lieu
    echo "Erreur : Aucun changement n'a été effectué. Vérifiez l'ID de l'arbitre ou la date.";
    exit();
}
?>
