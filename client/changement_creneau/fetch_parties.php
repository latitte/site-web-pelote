<?php
// Connexion à la base de données (mêmes informations de connexion)
include("../../logiciel/assets/conn_bdd.php");

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupération de l'identifiant d'équipe et joueur envoyé depuis la requête AJAX
$numequipe = $_POST['numequipe'];


// Préparation de la requête SQL sécurisée
// $sql = "SELECT id, jours, heure, partie FROM calendrier WHERE partie REGEXP CONCAT('[[:<:]]', ?, '[[:>:]]')";
$sql = "SELECT id, jours, heure, partie 
        FROM calendrier 
        WHERE partie REGEXP CONCAT('[[:<:]]', ?, '[[:>:]]') 
        AND jours >= DATE_ADD(CURDATE(), INTERVAL 2 DAY)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $numequipe);
$stmt->execute();
$result = $stmt->get_result();

// Préparation du tableau des parties à renvoyer en JSON
$parties = array();
while ($row = $result->fetch_assoc()) {
    $partie = array(
        'id' => $row['id'],
        'jours' => $row['jours'],
        'heure' => $row['heure'],
        'partie' => $row['partie']
    );
    $parties[] = $partie;
}

// Fermeture de la connexion et envoi de la réponse JSON
$stmt->close();
$conn->close();

$response = array(
    'success' => true,
    'parties' => $parties
);

header('Content-Type: application/json');
echo json_encode($response);
?>
