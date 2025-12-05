<?php
// Récupérer le nom de l'hôte complet
$host = $_SERVER['HTTP_HOST'];

// Séparer le nom de l'hôte en utilisant le point comme délimiteur
$domain_parts = explode('.', $host);

// Vérifier si l'hôte est une adresse IP
if (filter_var($host, FILTER_VALIDATE_IP)) {
    // C'est une adresse IP, gérer comme cas spécial
    $var_tournoi = 'cmr'; // ou toute autre valeur par défaut pour les environnements locaux
} elseif (count($domain_parts) > 2) {
    // Il y a un sous-domaine
    $var_tournoi = $domain_parts[0];
} else {
    // Pas de sous-domaine ou seulement un domaine de premier niveau
    $var_tournoi = 'cmr'; // ou une autre valeur par défaut appropriée
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