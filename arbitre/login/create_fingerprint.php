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

    // Définir les cookies si nécessaires et recharger la page pour capturer les valeurs
    function setFingerprintCookies() {
        echo "<script>
            if (!document.cookie.includes('screen_resolution')) {
                document.cookie = 'screen_resolution=' + screen.width + 'x' + screen.height;
            }
            if (!document.cookie.includes('timezone')) {
                document.cookie = 'timezone=' + new Date().getTimezoneOffset();
            }
            location.reload();  // Recharger la page pour récupérer les cookies définis
        </script>";
    }

    // Si les cookies ne sont pas définis, on les configure et on attend le rechargement de la page
    if (!isset($_COOKIE['screen_resolution']) || !isset($_COOKIE['timezone'])) {
        setFingerprintCookies();
        exit;  // Arrêter le script ici pour attendre le rechargement avec les cookies définis
    }

    // Générer l'empreinte
    $fingerprint = generateFingerprint();

    // Vérifier si l'empreinte existe déjà
    $stmt = $pdo->prepare("SELECT id FROM user_fingerprints WHERE fingerprint = :fingerprint");
    $stmt->execute(['fingerprint' => $fingerprint]);

    if (!$stmt->fetch()) {
        // L'empreinte n'existe pas, on l'ajoute
        $newUserId = 1;  // Attribuer un ID unique pour l’utilisateur ou un autre système de gestion d’utilisateurs
        $stmt = $pdo->prepare("INSERT INTO user_fingerprints (fingerprint, user_id) VALUES (:fingerprint, :user_id)");
        $stmt->execute(['fingerprint' => $fingerprint, 'user_id' => $newUserId]);
        echo "Nouvel utilisateur ajouté avec l'empreinte : " . $fingerprint;
        setcookie('fjckjedf8854f4df5dkf', '456936214egfffd5d', 0, "/");  // Cookie valide jusqu'à la fin de la session
        header("Location: ../"); // Redirection vers une page sécurisée après connexion
    } else {
        echo "L'empreinte existe déjà. Veuillez contacter le développeur";
        header("Location: ../"); // Redirection vers une page sécurisée après connexion
    }
} catch (PDOException $e) {
    // Gestion des erreurs de connexion
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
}
?>
