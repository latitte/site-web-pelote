<?php
// Récupérer le corps de la requête
$payload = file_get_contents("php://input");

// Tu peux ensuite le loguer ou l'enregistrer
file_put_contents("log_inbound.txt", $payload . PHP_EOL, FILE_APPEND);

// Répondre avec un statut 200
http_response_code(200);
echo "OK";
