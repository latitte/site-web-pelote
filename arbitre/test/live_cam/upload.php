<?php
$index = isset($_GET['f']) && $_GET['f'] === '1' ? '1' : '0';
$target = "live{$index}.webm";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video'])) {
    if (file_exists($target)) unlink($target);

    if (move_uploaded_file($_FILES['video']['tmp_name'], $target)) {
        // Mise à jour du status
        file_put_contents("status.json", json_encode([
            "ready" => $index,
            "timestamp" => time()
        ]));
        echo "✅ Upload $target + status mis à jour";
    } else {
        echo "❌ Erreur de déplacement";
    }
} else {
    echo "❌ Aucun fichier reçu";
}
