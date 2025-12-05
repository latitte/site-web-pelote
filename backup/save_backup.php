<?php
// Inclure le fichier de configuration pour les paramètres de connexion à la base de données
include '../logiciel/assets/conn_bdd.php'; // Connexion à la base de données

// Chemin où tu veux stocker la sauvegarde sur le serveur
$backup_dir = './backups/'; // Par exemple, un répertoire "backups" dans le répertoire courant
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0777, true); // Crée le répertoire si nécessaire
}

// Nom du fichier SQL exporté
$backup_file = $backup_dir . 'backup_' . $var_tournoi . '_' . date("Y-m-d_H-i-s") . '.sql'; // Chemin complet du fichier

// Ouvrir un fichier pour écrire le SQL
$handle = fopen($backup_file, 'w');

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Obtenir la liste des tables
$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

// Parcourir chaque table et créer l'export SQL
foreach ($tables as $table) {
    // Ajouter la requête CREATE TABLE
    $result = $conn->query("SHOW CREATE TABLE `$table`");
    $row = $result->fetch_assoc();
    $create_table_sql = $row['Create Table'] . ";\n\n";
    fwrite($handle, $create_table_sql);

    // Ajouter les données de la table
    $result = $conn->query("SELECT * FROM `$table`");
    while ($row = $result->fetch_assoc()) {
        $columns = array_keys($row);

        // Échapper les valeurs avec real_escape_string, en gérant les NULL
        $values = array_map(function($value) use ($conn) {
            return $value === null ? 'NULL' : "'" . $conn->real_escape_string($value) . "'";
        }, array_values($row));

        $insert_sql = "INSERT INTO `$table` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $values) . ");\n";
        fwrite($handle, $insert_sql);
    }
    fwrite($handle, "\n\n");
}

// Fermer le fichier
fclose($handle);

// Fermer la connexion à la base de données
$conn->close();

echo "Sauvegarde réussie. Fichier enregistré : " . $backup_file;

// Redirige vers la page précédente
if (isset($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    echo "La page précédente n'est pas disponible.";
}
?>
