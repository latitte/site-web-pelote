<?php
include '../logiciel/assets/conn_bdd.php';
// Inclure les informations de connexion
include '../logiciel/assets/extract_parametre.php';
$duree_partie = $parametres['duree_partie'];

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : connexion à la base de données non établie. " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['score']) && isset($_POST['team']) && isset($_POST['partie_id'])) {
        $score = intval($_POST['score']);
        $team = $_POST['team'];
        $partie_id = intval($_POST['partie_id']);

        $column = $team === 'A' ? 'live_score_A' : 'live_score_B';

        // Mettre à jour le score
        $sql = "UPDATE calendrier SET $column = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$score, $partie_id]);

        // Vérifier les scores actuels
        $stmt = $pdo->prepare("SELECT live_score_A, live_score_B FROM calendrier WHERE id = ?");
        $stmt->execute([$partie_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $scoreA = intval($result['live_score_A']);
            $scoreB = intval($result['live_score_B']);

            // Seuil pour terminer la partie
            $seuil = $duree_partie;

            if ($scoreA >= $seuil || $scoreB >= $seuil) {
                $score_final = "$scoreA/$scoreB";
                $stmt = $pdo->prepare("UPDATE calendrier SET score = ? WHERE id = ?");
                $stmt->execute([$score_final, $partie_id]);
                echo "FINI";
                exit;
            }
        }

        echo "OK";
    } else {
        echo "Données insuffisantes";
    }
} else {
    echo "Méthode HTTP incorrecte";
}
?>
