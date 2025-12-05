<?php
// Définir les variables de connexion
$servername = "mysql-tittdev.alwaysdata.net";
$username = "tittdev";
$password = "titi64120$";
$dbname = "tittdev_ilharre";

try {
    // Connexion à la base de données MySQL
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Configurer les erreurs PDO pour lancer des exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fonction pour générer l'empreinte digitale
    function generateFingerprint() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];  
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $screenResolution = isset($_COOKIE['screen_resolution']) ? $_COOKIE['screen_resolution'] : 'unknown';
        $timezone = isset($_COOKIE['timezone']) ? $_COOKIE['timezone'] : 'unknown';

        // Combiner les informations en une chaîne
        $data = $userAgent . $acceptLanguage . $screenResolution . $timezone;
        return hash('sha256', $data);  // Retourne un hash unique
    }

    // Générer l'empreinte
    $fingerprint = generateFingerprint();

    // Vérifier si l'empreinte existe dans la base de données
    $stmt = $pdo->prepare("SELECT user_id FROM user_fingerprints WHERE fingerprint = :fingerprint");
    $stmt->execute(['fingerprint' => $fingerprint]);
    $user = $stmt->fetch();

    if ($user) {
        echo "Bienvenue, utilisateur #" . $user['user_id'];
    } else {
        echo "Utilisateur non reconnu.";
    }
} catch (PDOException $e) {
    // Gestion des erreurs de connexion
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
}
?>
