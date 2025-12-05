<?php
require 'vendor/autoload.php'; // Installé via Composer

use Minishlink\WebPush\VAPID;

$keys = VAPID::createVapidKeys();

echo "Clé publique : " . $keys['publicKey'] . "\n";
echo "Clé privée : " . $keys['privateKey'] . "\n";
