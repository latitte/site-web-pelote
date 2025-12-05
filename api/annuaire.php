<?php
// Définir les URLs des API
$api_urls = [
    'arbitre_demain' => 'https://192.168.1.30/arbitre/arbitre.php?demain',
    'arbitre_apresdemain' => 'https://ilharre.tournoi-pelote.com/pelote_ilharre/arbitre/arbitre.php?apresdemain',
    'rappel_joueur' => 'https://ilharre.tournoi-pelote.com/pelote_ilharre/api/rappel_joueur.php?heure=20h00'
];

// Fonction pour appeler une API
function call_api($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

// Vérifier que le paramètre 'api' est fourni
if (isset($_GET['api']) && array_key_exists($_GET['api'], $api_urls)) {
    $api_key = $_GET['api'];
    $url = $api_urls[$api_key];
    
    // Appeler l'API et obtenir la réponse
    // $response = call_api($url);
    
    // Afficher l'URL et la réponse

    echo '<p>' . htmlspecialchars($url) . '</p>';
    

    // echo '<pre>' . htmlspecialchars($response) . '</pre>';
} else {
    // Gérer les erreurs
    http_response_code(400);
    echo '<h2>Erreur :</h2>';
    echo '<p>Paramètre API invalide ou manquant.</p>';
}
?>
