<?php
// Démarrer la session
session_start();

// Supprimer toutes les variables de session
$_SESSION = [];

// Détruire la session
session_destroy();

// Rediriger vers la page d'accueil ou de connexion
header("Location: connexion.php"); // Remplace "login.php" par ta page de destination après déconnexion
exit;
?>
