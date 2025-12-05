<?php
// Répertoire contenant les fichiers logs
$logDir = 'assets/';

// Liste des fichiers logs ajoutés manuellement
$logFiles = [
    'log_api_add_partie.txt',
    'log_api_elimine_team.txt',
    'log_api_change_niveau.txt',
    'notifs.txt',
    // Ajoute d'autres fichiers ici
];

// Si un fichier est sélectionné, on l'affiche
if (isset($_GET['file'])) {
    $fileName = basename($_GET['file']);
    $filePath = $logDir . $fileName;

    // Vérifier si le fichier existe et est lisible
    if (file_exists($filePath) && is_readable($filePath)) {
        $content = file_get_contents($filePath);
    } else {
        $content = 'Fichier introuvable ou illisible.';
    }
} else {
    $content = 'Sélectionnez un fichier log pour afficher son contenu.';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu des fichiers logs</title>
</head>
<body>

<h1>Menu des fichiers logs</h1>
<ul>
    <?php foreach ($logFiles as $logFile): ?>
        <li><a href="?file=<?php echo urlencode($logFile); ?>"><?php echo htmlspecialchars($logFile); ?></a></li>
    <?php endforeach; ?>
</ul>

<h2>Contenu du fichier</h2>
<pre><?php echo htmlspecialchars($content); ?></pre>

</body>
</html>
