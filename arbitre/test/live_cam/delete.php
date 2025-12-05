<?php
if (isset($_GET['f'])) {
    $file = basename($_GET['f']);
    if (file_exists($file) && strpos($file, "live") === 0) {
        unlink($file);

        // Mise à jour de status.json
        $statusFile = "status.json";
        $status = file_exists($statusFile) ? json_decode(file_get_contents($statusFile), true) : ["files" => []];
        $status["files"] = array_filter($status["files"], function ($f) use ($file) {
            return $f["name"] !== $file;
        });
        file_put_contents($statusFile, json_encode($status));

        echo "✅ Supprimé $file";
    } else {
        echo "❌ Fichier invalide";
    }
} else {
    echo "❌ Paramètre manquant";
}
