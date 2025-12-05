<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['id'])) {
    $team_id = $_SESSION['id'];
    echo "✅ Utilisateur connecté, ID : " . htmlspecialchars($team_id) . "<br>";
} else {
    die("❌ Aucune session active. Utilisateur non connecté.");
}

// Connexion à la base de données
include('../logiciel/assets/conn_bdd.php');
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Échec de la connexion : " . $conn->connect_error);
}

// Récupération des paramètres GET
$jour = $_GET['jour'] ?? null;
$heure = $_GET['heure'] ?? null;
$partie_id = $_GET['partie'] ?? null; // ID dans la BDD
$equipe_id = $_GET['equipe_id'] ?? null;

if ($jour && $heure && $partie_id && $equipe_id) {
    $equipes = explode("/", $equipe_id);
    if (count($equipes) == 2) {
        $equipe1 = intval($equipes[0]);
        $equipe2 = intval($equipes[1]);

        // Identifier l'autre équipe
        if ($team_id == $equipe1) {
            $equipe_adverse = $equipe2;
        } elseif ($team_id == $equipe2) {
            $equipe_adverse = $equipe1;
        } else {
            die("❌ Erreur : cette partie ne concerne pas votre équipe.");
        }

        // Récupérer l'ancien créneau depuis la BDD
        $stmt = $conn->prepare("SELECT jours, heure FROM calendrier WHERE id = ?");
        $stmt->bind_param("i", $partie_id);
        $stmt->execute();
        $stmt->bind_result($ancien_jour, $ancienne_heure);
        $stmt->fetch();
        $stmt->close();

        if ($ancien_jour && $ancienne_heure) {
            // Mise à jour du créneau
            $stmt = $conn->prepare("UPDATE calendrier SET jours = ?, heure = ? WHERE id = ?");
            $stmt->bind_param("ssi", $jour, $heure, $partie_id);
            if ($stmt->execute()) {
                echo "✅ Créneau modifié avec succès pour la partie $equipe_id : $jour à $heure.<br>";

                // Ajout dans le journal
                $horodateur = date("Y-m-d H:i:s");
                $type = "Modification créneau";
                $detail = "créneau modifié : $ancien_jour à $ancienne_heure → $jour à $heure";

                $stmt2 = $conn->prepare("INSERT INTO activite (horodateur, type, equipeA, equipeB, detail) VALUES (?, ?, ?, ?, ?)");
                $stmt2->bind_param("sssss", $horodateur, $type, $team_id, $equipe_adverse, $detail);
                $stmt2->execute();
                $stmt2->close();

                header('Location: ./sms_notification_changement.php?partie=' . $equipe_id . '');
                exit();
            } else {
                echo "❌ Erreur lors de la mise à jour : " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "❗ Partie non trouvée dans le calendrier.";
        }
    } else {
        echo "❌ Format de equipe_id invalide (attendu : X/Y).";
    }
} else {
    echo "❌ Paramètres GET manquants.";
}
?>
