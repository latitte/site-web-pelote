<?php
// Répertoire contenant les fichiers logs
$logDir = '../backup/backups/';

// Liste tous les fichiers du répertoire, en ignorant "." et ".."
$logFiles = array_diff(scandir($logDir), array('..', '.'));

// Créer un tableau associatif avec le nom du fichier et sa date de modification
$fileData = [];
foreach ($logFiles as $logFile) {
    $filePath = $logDir . $logFile;
    $fileData[$logFile] = filemtime($filePath);  // Stocker la date de modification
}

// Trier les fichiers par date de modification, du plus ancien au plus récent
asort($fileData);

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
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h1>Menu des fichiers logs</h1>
<table>
    <thead>
        <tr>
            <th>Nom du fichier</th>
            <th>Taille</th>
            <th>Date de modification</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($fileData as $logFile => $fileDate): ?>
            <?php 
                $filePath = $logDir . $logFile;
                $fileSize = filesize($filePath);
                $formattedDate = date("d/m/Y H:i:s", $fileDate);
            ?>
            <tr>
                <td><?php echo htmlspecialchars($logFile); ?></td>
                <td><?php echo round($fileSize / 1024, 2); ?> Ko</td> <!-- Taille en Ko -->
                <td><?php echo $formattedDate; ?></td>
                <td><a href="?file=<?php echo urlencode($logFile); ?>">Voir le contenu</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Contenu du fichier</h2>
<pre><?php echo htmlspecialchars($content); ?></pre>

</body>
</html>
