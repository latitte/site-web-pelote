<?php
// Informations de connexion à la base de données
include(__DIR__ . '/conn_bdd.php');



try {
    // Connexion à la base de données avec PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête pour récupérer tous les paramètres
    $sql = "SELECT parametre, valeur FROM parametre";
    $stmt = $pdo->query($sql);

    // Initialisation du tableau associatif pour stocker les paramètres
    $parametres = [];

    // Récupération des résultats et stockage dans le tableau associatif
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $parametres[$row['parametre']] = $row['valeur'];
    }

    // Affichage des valeurs récupérées (à titre d'exemple)
    foreach ($parametres as $parametre => $valeur) {
        // echo "Paramètre : $parametre, Valeur : $valeur <br>";
    }

    // Vous pouvez utiliser $parametres comme un dictionnaire dans votre application

} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
}

?>


