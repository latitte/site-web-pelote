<?php
header('Content-Type: application/json');

// Connexion à la BDD
include '../logiciel/assets/conn_bdd.php';

// Créer la connexion PDO
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erreur de connexion : " . $e->getMessage()]);
    exit;
}

// Récupérer l'ID passé en paramètre GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(["error" => "ID d'équipe manquant ou invalide"]);
    exit;
}

$id = (int) $_GET['id'];

// Préparer et exécuter la requête
$sql = "SELECT `Joueur 1`, `Joueur 2` FROM inscriptions WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);

$joueurs = $stmt->fetch(PDO::FETCH_ASSOC);

if ($joueurs) {
    echo json_encode([
        "joueur_1" => $joueurs['Joueur 1'],
        "joueur_2" => $joueurs['Joueur 2']
    ]);
} else {
    echo json_encode(["error" => "Aucune équipe trouvée avec l'ID $id"]);
}
?>
