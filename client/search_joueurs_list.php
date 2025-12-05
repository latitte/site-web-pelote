<?php
include("../master/conn_bdd_master.php");
header('Content-Type: application/json');

// Crée la connexion ici, puisque conn_bdd_master.php ne le fait pas
$conn_master = new mysqli($servername, $username, $password, $dbname);
if ($conn_master->connect_error) {
    echo json_encode(['error' => 'Erreur de connexion à la base de données']);
    exit;
}

// Récupération du paramètre "query"
$query = $_GET['query'] ?? '';
if (!$query || strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$query = "%" . $query . "%";

// Requête préparée
$stmt = $conn_master->prepare("
    SELECT nom, prenom, serie 
    FROM joueur 
    WHERE CONCAT(nom, ' ', prenom) LIKE ? 
    ORDER BY nom 
    LIMIT 10
");
$stmt->bind_param("s", $query);
$stmt->execute();
$result = $stmt->get_result();

// Construction de la réponse JSON
$joueurs = [];
while ($row = $result->fetch_assoc()) {
    $joueurs[] = [
        'nom' => $row['nom'],
        'prenom' => $row['prenom'],
        'serie' => $row['serie']
    ];
}

echo json_encode($joueurs);
?>
