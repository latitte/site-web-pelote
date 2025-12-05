<?php
// Définir un tableau associatif avec des titres personnalisés et des URLs
$apis = [
    "Test de l'API Rappel Arbitre demain" => "https://ilharre.tournoi-pelote.com/arbitre/arbitre.php?demain",
    "Rappel partie joueur 18h15" => "https://ilharre.tournoi-pelote.com/api/rappel_joueur.php?heure=18h15",
    "Rappel partie joueur 19h30" => "https://ilharre.tournoi-pelote.com/api/rappel_joueur.php?heure=19h30",
    "Rappel partie joueur 20h30" => "https://ilharre.tournoi-pelote.com/api/rappel_joueur.php?heure=20h30",
    // Ajoutez d'autres titres et URLs ici
];

// Fonction pour tester une API
function testerApi($titre, $url) {
    // Initialiser cURL
    $ch = curl_init();
    
    // Configurer l'URL et les options de cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout après 10 secondes
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Désactiver la vérification SSL (si nécessaire)

    // Exécuter la requête
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Vérifier les erreurs de cURL
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        echo "<p>Erreur avec l'URL <strong>$url</strong>: $error_msg</p>";
    } else {
        echo "<h3>$titre</h3>";  // Titre personnalisé pour l'API
        echo "<p>URL testée: $url</p>";
        echo "<p>Code HTTP: $http_code</p>";
        
        // Afficher la réponse JSON si c'est le cas
        $json = json_decode($response, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            echo "<pre>" . json_encode($json, JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "<p>Réponse: $response</p>";
        }
    }

    // Fermer la session cURL
    curl_close($ch);
}

// Parcourir chaque API et tester
foreach ($apis as $titre => $url) {
    echo "<h2>$titre</h2>";  // Titre personnalisé avant chaque test
    testerApi($titre, $url);
}
?>
