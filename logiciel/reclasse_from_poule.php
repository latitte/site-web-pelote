<?php
include("./assets/conn_bdd.php");

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Commencer une transaction
$conn->begin_transaction();

try {
    // Supprimer la colonne temporaire si elle existe déjà
    $conn->query("ALTER TABLE inscriptions DROP COLUMN IF EXISTS temp_id");

    // Ajouter une colonne temporaire pour stocker les nouveaux IDs décalés
    $conn->query("ALTER TABLE inscriptions ADD COLUMN temp_id INT");

    // Récupérer toutes les inscriptions triées par série et poule
    $sql = "SELECT id FROM inscriptions 
            ORDER BY 
            FIELD(serie, 'Première série', 'Deuxième série', 'Troisième série', 'Féminine', 'Mixte'), 
            poule";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $newId = 1;
        $tempOffset = 10000; // Assurez-vous que ce nombre est suffisamment grand pour éviter les conflits

        while ($row = $result->fetch_assoc()) {
            // Stocker les nouveaux IDs décalés dans la colonne temporaire
            $updateSql = "UPDATE inscriptions SET temp_id = " . ($newId + $tempOffset) . " WHERE id = " . $row['id'];
            if (!$conn->query($updateSql)) {
                throw new Exception("Erreur lors de la mise à jour de temp_id: " . $conn->error);
            }
            $newId++;
        }

        // Désactiver temporairement l'AUTO_INCREMENT sur la colonne id si nécessaire
        $conn->query("ALTER TABLE inscriptions MODIFY COLUMN id INT");

        // Remplacer les IDs existants par les nouveaux IDs décalés
        if (!$conn->query("UPDATE inscriptions SET id = temp_id")) {
            throw new Exception("Erreur lors de la mise à jour des IDs: " . $conn->error);
        }

        // Supprimer l'offset pour revenir aux IDs initiaux
        if (!$conn->query("UPDATE inscriptions SET id = id - $tempOffset")) {
            throw new Exception("Erreur lors de l'ajustement final des IDs: " . $conn->error);
        }

        // Supprimer la colonne temporaire
        if (!$conn->query("ALTER TABLE inscriptions DROP COLUMN temp_id")) {
            throw new Exception("Erreur lors de la suppression de la colonne temporaire: " . $conn->error);
        }

        // Réactiver l'AUTO_INCREMENT si nécessaire
        $conn->query("ALTER TABLE inscriptions MODIFY COLUMN id INT AUTO_INCREMENT");

        // Commit la transaction
        $conn->commit();
        echo "Tous les enregistrements ont été mis à jour avec succès.";

    } else {
        echo "Aucun enregistrement trouvé";
    }

} catch (Exception $e) {
    // Rollback la transaction en cas d'erreur
    $conn->rollback();
    echo "Erreur de mise à jour des enregistrements: " . $e->getMessage();
}

$conn->close();
?>
