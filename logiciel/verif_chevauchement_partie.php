<?php
// Connexion à la base de données
$servername = "mysql-tittdev.alwaysdata.net";
$username = "tittdev";
$password = "titi64120$";
$dbname = "tittdev_ilharre";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Récupérer toutes les parties triées par jour et heure
$sql = "SELECT id, jours, heure FROM calendrier ORDER BY jours, heure";
$result = $conn->query($sql);

$overlapping = false;
$previous = null;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Extraire les données de la partie actuelle
        $currentId = $row['id'];
        $currentJour = $row['jours'];
        $currentHeure = $row['heure'];

        // Si on a une partie précédente à comparer
        if ($previous) {
            $previousJour = $previous['jours'];
            $previousHeure = $previous['heure'];

            // Comparer les jours et heures pour voir s'il y a chevauchement
            if ($currentJour === $previousJour) {
                // Convertir les heures au format DateTime pour comparaison
                $currentDateTime = DateTime::createFromFormat('H\hi', $currentHeure);
                $previousDateTime = DateTime::createFromFormat('H\hi', $previousHeure);

                // Ajouter une heure à l'heure de début de la partie précédente
                $previousEndTime = clone $previousDateTime;
                $previousEndTime->modify('+45 minutes');

                // Vérifier s'il y a chevauchement
                if ($currentDateTime < $previousEndTime) {
                    echo "Conflit détecté entre les parties d'ID $currentId et " . $previous['id'] . " (même jour et créneau horaire qui se chevauchent).<br>";
                    $overlapping = true;
                }
            }
        }

        // Mise à jour de la partie précédente pour la prochaine comparaison
        $previous = $row;
    }
} else {
    echo "Aucune partie trouvée dans la base de données.";
}

if (!$overlapping) {
    echo "Aucun chevauchement détecté entre les parties.";
}

$conn->close();
?>
