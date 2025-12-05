<?php
// Récupérer les données POST
$team1_id = $_POST['team1_id'];
$team2_id = $_POST['team2_id'];
$serie = $_POST['serie'];
$poule = $_POST['poule'];
$date = $_POST['date'];
$heure = $_POST['heure'];

// Connexion à la base de données
include("./assets/conn_bdd.php");

$session = "titoan";
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Vérifier si le créneau est libre
$sql_check = "SELECT * FROM calendrier WHERE jours = '$date' AND heure = '$heure'";
$result_check = $conn->query($sql_check);

if ($result_check->num_rows == 0) {
    // Créneau libre : insérer la partie dans le calendrier
    $partie = $team1_id . "/" . $team2_id;
    $sql_insert = "INSERT INTO calendrier (jours, heure, partie, ia) VALUES ('$date', '$heure', '$partie', '0')";
    if ($conn->query($sql_insert) === TRUE) {
        echo "Partie placée avec succès le $date à $heure.";

        // Écrire dans le fichier notifs.txt
        $notif = "Partie placée '$partie' : Le jour et heure: $date à $heure par " . $session . " le " . date('Y-m-d H:i:s') . "\n";
        file_put_contents('./assets/notifs.txt', $notif, FILE_APPEND);


    } else {
        echo "Erreur lors de l'insertion de la partie : " . $conn->error;
    }
} else {
    // Créneau déjà occupé
    echo "Le créneau $heure le $date est déjà occupé.";
}

$conn->close();
?>
