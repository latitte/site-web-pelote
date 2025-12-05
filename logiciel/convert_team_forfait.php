<?php
// Inclure la connexion à la base de données
include './assets/extract_parametre.php';
$duree_partie = $parametres['duree_partie'];

// Initialiser un message de retour
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Créer la connexion
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Vérifier la connexion
    if ($conn->connect_error) {
        die("Connexion échouée : " . $conn->connect_error);
    }

    // Récupérer l'ID de l'équipe envoyé par le formulaire
    $team_id = $_POST['team_id'];

    // 1. Mettre à jour la colonne "forfait" de l'équipe
    $sql_update_forfait = "UPDATE inscriptions 
                           SET forfait = 1 
                           WHERE id = ?";
    
    $stmt = $conn->prepare($sql_update_forfait);
    $stmt->bind_param("i", $team_id);

    if ($stmt->execute()) {
        $message = "L'équipe avec l'ID $team_id a été mise en forfait avec succès.";
    } else {
        $message = "Erreur lors de la mise en forfait de l'équipe avec l'ID $team_id : " . $stmt->error;
    }
    
    // 2. Chercher toutes les parties de cette équipe dans la table "calendrier"
    $teamNumber = $team_id;  // ID de l'équipe forfait
    $sql_find_games = "SELECT * FROM calendrier 
                       WHERE partie LIKE '$teamNumber/%' 
                       OR partie LIKE '%/$teamNumber' 
                       ORDER BY jours ASC";

    $result = $conn->query($sql_find_games);

    if ($result->num_rows > 0) {
        // 3. Mettre à jour les scores pour refléter une défaite de l'équipe forfait
        while ($row = $result->fetch_assoc()) {
            $partie = $row['partie'];
            $id_partie = $row['id'];

            // Séparer les équipes
            list($teamA, $teamB) = explode('/', $partie);

            if ($teamA == $teamNumber) {
                // Si l'équipe forfait est l'équipe A
                $score = "0/$duree_partie";  // Défaite 40 à 0
            } else {
                // Si l'équipe forfait est l'équipe B
                $score = "$duree_partie/0";  // Défaite 40 à 0
            }

            // Mettre à jour le score dans la table calendrier
            $sql_update_score = "UPDATE calendrier 
                                 SET score = ?, partie_jouee = 1 
                                 WHERE id = ?";
            $stmt_update_score = $conn->prepare($sql_update_score);
            $stmt_update_score->bind_param("si", $score, $id_partie);
            
            if ($stmt_update_score->execute()) {
                $message .= "<br>La partie ID $id_partie a été mise à jour avec le score $score.";
            } else {
                $message .= "<br>Erreur lors de la mise à jour de la partie ID $id_partie : " . $stmt_update_score->error;
            }
        }
    } else {
        $message .= "<br>Aucune partie trouvée pour l'équipe $team_id.";
    }

    // Fermer les connexions
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise en Forfait d'une Équipe</title>
</head>
<body>
    <h1>Mettre une Équipe en Forfait</h1>

    <?php if (!empty($message)) : ?>
        <p><?php echo nl2br($message); ?></p>
    <?php endif; ?>

    <form action="" method="post">
        <label for="team_id">ID de l'équipe :</label>
        <input type="number" id="team_id" name="team_id" required>
        <br><br>
        <input type="submit" value="Valider">
    </form>
</body>
</html>
