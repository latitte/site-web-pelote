<?php
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
?>

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
    // Vérifier l'empreinte
    echo "Empreinte générée : " . $fingerprint; // Pour déboguer
    $stmt = $pdo->prepare("SELECT user_id FROM user_fingerprints WHERE fingerprint = :fingerprint");
    $stmt->execute(['fingerprint' => $fingerprint]);
    $user = $stmt->fetch();

    if ($user) {
        echo "Bienvenue, utilisateur #" . $user['user_id'];
        setcookie('fjckjedf8854f4df5dkf', '456936214egfffd5d', 0, "/");  // Cookie valide jusqu'à la fin de la session
        header("Location: ../add_inscription.php"); // Redirection vers une page sécurisée après connexion
    } else {
        echo "Utilisateur non reconnu.";
        header("Location: ./connect_id.php"); // Redirection vers une page sécurisée après connexion
    }
} catch (PDOException $e) {
    // Gestion des erreurs de connexion
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
}
?>
