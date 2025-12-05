<?php
session_start();

if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 1;  // Commence à 1 pour correspondre aux étapes
    $_SESSION['data'] = [];
}

$steps = [
    1 => "Bonjour ! Pour commencer, quel est votre nom ?",
    2 => "Merci {name}! Quel est votre prénom ?",
    3 => "Merci {first_name}. Quel est votre âge ?",
    4 => "Quel est votre sexe ? (H/F)",
    5 => "Quel est votre email pour qu'on puisse vous contacter ?",
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = trim($_POST['response']);
    
    // Enregistre la réponse utilisateur en fonction de l'étape
    switch ($_SESSION['step']) {
        case 1:
            $_SESSION['data']['name'] = $response;
            break;
        case 2:
            $_SESSION['data']['first_name'] = $response;
            break;
        case 3:
            $_SESSION['data']['age'] = $response;
            break;
        case 4:
            $_SESSION['data']['gender'] = strtoupper($response) === 'H' ? 'Homme' : 'Femme';
            break;
        case 5:
            $_SESSION['data']['email'] = $response;
            break;
    }

    // Passe à l'étape suivante ou termine avec le bilan
    if ($_SESSION['step'] < count($steps)) {
        $_SESSION['step']++;
        $message = $steps[$_SESSION['step']];
        $message = str_replace(
            ['{name}', '{first_name}'], 
            [$_SESSION['data']['name'] ?? '', $_SESSION['data']['first_name'] ?? ''], 
            $message
        );
    } else {
        // Fin de la conversation avec un bilan
        $message = "Merci pour vos réponses ! Voici un bilan :\n";
        $message .= "Nom : {$_SESSION['data']['name']}\n";
        $message .= "Prénom : {$_SESSION['data']['first_name']}\n";
        $message .= "Âge : {$_SESSION['data']['age']} ans\n";
        $message .= "Sexe : {$_SESSION['data']['gender']}\n";
        $message .= "Email : {$_SESSION['data']['email']}\n";
        
        session_destroy();  // Réinitialise la session à la fin
    }

    echo json_encode(['message' => $message]);
    exit;
} else {
    // Si la requête est GET, envoie la première question
    $message = $steps[$_SESSION['step']];
    echo json_encode(['message' => $message]);
    exit;
}
