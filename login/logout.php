<?php
// Vérifie si le cookie 'identifiant_organisateur' existe et le supprime
if (isset($_COOKIE['identifiant_organisateur'])) {
    // Supprime le cookie en définissant une date d'expiration dans le passé
    setcookie('identifiant_organisateur', '', time() - 3600, "/");
}

// Optionnel : Supprimer également d'autres cookies si nécessaire

// Redirige vers la page de connexion ou d'accueil
header("Location: ./");
exit;
?>
