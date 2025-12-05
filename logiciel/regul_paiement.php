<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche Équipe</title>
</head>
<body>

<h2>Recherche d'Équipe</h2>
<form method="POST" action="">
    <label for="search">ID de l'équipe ou Nom du joueur :</label>
    <input type="text" id="search" name="search" required>
    <button type="submit">Rechercher</button>
</form>

<?php

// Inclure la connexion à la base de données
include './assets/conn_bdd.php';

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Vérifier si le formulaire de recherche a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search = $conn->real_escape_string($_POST['search']);
    
    // Rechercher l'équipe par ID, Joueur 1 ou Joueur 2
    $sql = "SELECT * FROM joueurs WHERE id_equipe='$search' OR `joueur` LIKE '%$search%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Affichage des résultats
        while ($row = $result->fetch_assoc()) {
            echo "<h3>Équipe trouvée</h3>";
            echo "ID: " . $row["id"] . "<br>";
            echo "Joueur: " . $row["joueur"] . "<br>";
            echo "Poule: " . $row["montant"] . "<br>";
            echo "Payé: " . ($row["status_paiement"] == 1 ? "Oui" : "Non") . "<br>";
            
            // Formulaire de confirmation de paiement
            echo "<form method='POST' action=''>";
            echo "<input type='hidden' name='confirm_id' value='" . $row["id"] . "'>";
            echo "<button type='submit' name='confirm' value='1'>Confirmer le paiement</button>";
            echo "</form>";
        }
    } else {
        echo "Aucune équipe trouvée.";
    }
}

// Mise à jour du paiement si confirmation
if (isset($_POST['confirm']) && isset($_POST['confirm_id'])) {
    $confirm_id = $conn->real_escape_string($_POST['confirm_id']);
    $sql = "UPDATE joueurs SET status_paiement=1 WHERE id='$confirm_id'";

    if ($conn->query($sql) === TRUE) {
        echo "Le paiement a été confirmé avec succès.";
    } else {
        echo "Erreur lors de la mise à jour du paiement : " . $conn->error;
    }
}

$conn->close();
?>

</body>
</html>
