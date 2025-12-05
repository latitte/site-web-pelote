<?php
header('Content-Type: application/json');

include("./conn_bdd.php");

// Fonction pour écrire dans le fichier log
function logApiChangeNiveau($message) {
    $logFile = 'log_api_change_niveau.txt';
    $currentTime = date('Y-m-d H:i:s');
    $logMessage = "[$currentTime] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['equipe']) && isset($_GET['niveau'])) {
            $equipe = $_GET['equipe'];
            $niveau = $_GET['niveau'];

            // Extraire le numéro et la série
            $niveauNumero = substr($niveau, 0, -1);
            $serie = substr($niveau, -1);

            // Transformation des niveaux
            $nouveauNiveauNumero = '';
            if (in_array($niveauNumero, ['31', '32'])) {
                $nouveauNiveauNumero = '41';
            } elseif (in_array($niveauNumero, ['33', '34'])) {
                $nouveauNiveauNumero = '42';
            } elseif (in_array($niveauNumero, ['35', '36'])) {
                $nouveauNiveauNumero = '43';
            } elseif (in_array($niveauNumero, ['37', '38'])) {
                $nouveauNiveauNumero = '44';
            } elseif (in_array($niveauNumero, ['41', '42'])) {
                $nouveauNiveauNumero = '51';
            } elseif (in_array($niveauNumero, ['43', '44'])) {
                $nouveauNiveauNumero = '52';
            } elseif (in_array($niveauNumero, ['51', '52'])) {
                $nouveauNiveauNumero = '60';
            } elseif (in_array($niveauNumero, ['60'])) {
                $nouveauNiveauNumero = 'W';
            } else {
                $response = ['status' => 'error', 'message' => 'Niveau non reconnu.'];
                echo json_encode($response);
                logApiChangeNiveau("Equipe: $equipe, Niveau: $niveau, Réponse: " . json_encode($response));
                exit;
            }

            // Composer le nouveau niveau avec la série
            $nouveauNiveau = $nouveauNiveauNumero . $serie;

            $stmt = $conn->prepare("UPDATE classement SET niveau = :niveau WHERE equipe = :equipe");
            $stmt->bindParam(':niveau', $nouveauNiveau);
            $stmt->bindParam(':equipe', $equipe);

            if ($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Niveau mis à jour avec succès.'];
                echo json_encode($response);
                logApiChangeNiveau("Equipe: $equipe, Ancien Niveau: $niveau, Nouveau Niveau: $nouveauNiveau, Réponse: " . json_encode($response));
            } else {
                $response = ['status' => 'error', 'message' => 'Échec de la mise à jour du niveau.'];
                echo json_encode($response);
                logApiChangeNiveau("Equipe: $equipe, Niveau: $niveau, Réponse: " . json_encode($response));
            }
        } else {
            $response = ['status' => 'error', 'message' => 'Paramètres manquants.'];
            echo json_encode($response);
            logApiChangeNiveau("Réponse: " . json_encode($response));
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Méthode de requête non valide.'];
        echo json_encode($response);
        logApiChangeNiveau("Réponse: " . json_encode($response));
    }
} catch(PDOException $e) {
    $response = ['status' => 'error', 'message' => 'Connection failed: ' . $e->getMessage()];
    echo json_encode($response);
    logApiChangeNiveau("Réponse: " . json_encode($response));
}
?>
