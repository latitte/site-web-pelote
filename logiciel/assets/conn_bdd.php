<?php
// Récupérer le nom de l'hôte complet
$host = $_SERVER['HTTP_HOST'];

// Séparer le nom de l'hôte en utilisant le point comme délimiteur
$domain_parts = explode('.', $host);

// Vérifier si l'hôte est une adresse IP
if (filter_var($host, FILTER_VALIDATE_IP)) {
    // C'est une adresse IP, gérer comme cas spécial
    $var_tournoi = 'ilharre'; // ou toute autre valeur par défaut pour les environnements locaux
} elseif (count($domain_parts) > 2) {
    // Il y a un sous-domaine
    $var_tournoi = $domain_parts[0];
} else {
    // Pas de sous-domaine ou seulement un domaine de premier niveau
    // $var_tournoi = 'ilharre'; // ou une autre valeur par défaut appropriée
    header('Location: ../controller/no_domain.php');
    exit();
}

// Connexion à la base de données
$servername = "mysql-tittdev.alwaysdata.net";
$username = "tittdev";
$password = "titi64120$";
$dbname = "tittdev_$var_tournoi";

// Affichage pour vérification (à retirer en production)
// echo "Sous-domaine : $var_tournoi\n";
// echo "Nom de la base de données : $dbname\n";

?>



<?php
// Détection de l'environnement
$ip_client = $_SERVER['REMOTE_ADDR'];
$environnement_dev = ($ip_client === '127.0.0.1' || $ip_client === '::1' || $_SERVER['HTTP_HOST'] === 'localhost');

if ($environnement_dev) {
    // En local : afficher toutes les erreurs
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // En production : masquer les erreurs
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);

    // Créer dossier logs si nécessaire
    $log_dir = __DIR__ . '/logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0777, true);
    }

    // Gestion des erreurs classiques
    set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($log_dir) {
        $msg = "Erreur [$errno] : $errstr dans $errfile à la ligne $errline";
        error_log("[" . date("Y-m-d H:i:s") . "] $msg\n", 3, "$log_dir/erreurs_php.log");

        if (in_array($errno, [E_ERROR, E_USER_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
            afficher_message_erreur_utilisateur($msg);
            exit;
        }

        return true;
    });

    // Gestion des exceptions
    set_exception_handler(function ($exception) use ($log_dir) {
        $msg = "Exception : " . $exception->getMessage() . " dans " . $exception->getFile() . " à la ligne " . $exception->getLine();
        error_log("[" . date("Y-m-d H:i:s") . "] $msg\n", 3, "$log_dir/erreurs_php.log");
        afficher_message_erreur_utilisateur($msg);
        exit;
    });

    // Gestion des erreurs fatales
    register_shutdown_function(function () use ($log_dir) {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $msg = "Erreur fatale : {$error['message']} dans {$error['file']} à la ligne {$error['line']}";
            error_log("[" . date("Y-m-d H:i:s") . "] $msg\n", 3, "$log_dir/erreurs_php.log");
            afficher_message_erreur_utilisateur($msg);
        }
    });
}

// Définir la fonction si elle n'existe pas déjà
if (!function_exists('afficher_message_erreur_utilisateur')) {
    function afficher_message_erreur_utilisateur($details = '') {
        echo <<<HTML
<div style="padding:20px; background-color:#ffdddd; color:#a00; border:1px solid #a00; font-family:sans-serif; max-width:600px; margin:40px auto;">
    <p><strong>Une erreur est survenue.</strong><br>
    Veuillez prévenir l’administrateur <strong>Lalanne Titoan</strong>.<br>
    <a href="mailto:admin@tournoi-pelote.com">admin@tournoi-pelote.com</a><br>
    Merci de votre compréhension.</p>
HTML;

        if (!empty($details)) {
            echo <<<HTML
    <details style="margin-top:10px;">
        <summary style="cursor:pointer; color:#a00; font-weight:bold;">Afficher les détails techniques</summary>
        <pre style="white-space:pre-wrap; background:#fff3f3; padding:10px; border:1px solid #ccc; color:#333;">{$details}</pre>
    </details>
HTML;
        }

        echo "</div>";
    }
}
?>
