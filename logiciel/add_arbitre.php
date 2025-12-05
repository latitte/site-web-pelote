<?php
include("./assets/extract_parametre.php");

// Connexion à la base de données en utilisant les variables définies dans config.php
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Suppression d'un arbitre
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id']; // Sécurisation de l'ID à supprimer
    $sql_delete = "DELETE FROM arbitre WHERE id = $delete_id";
    if ($conn->query($sql_delete) === TRUE) {
        echo "Arbitre supprimé avec succès !";
    } else {
        echo "Erreur lors de la suppression : " . $conn->error;
    }
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $prenoms = $_POST['prenom'];
    $tels = $_POST['tel'];
    
    // Préparer la requête d'insertion
    $sql = "INSERT INTO arbitre (prenom, tel) VALUES ";
    $values = [];
    foreach ($prenoms as $key => $prenom) {
        $prenom = htmlspecialchars($prenom);
        $tel = htmlspecialchars($tels[$key]);
        $values[] = "('$prenom', '$tel')";
    }
    $sql .= implode(", ", $values);
    
    // Exécuter la requête
    if ($conn->query($sql) === TRUE) {
        echo "Arbitres ajoutés avec succès !";
    } else {
        echo "Erreur : " . $conn->error;
    }
}

// Récupérer la liste des arbitres inscrits
$result = $conn->query("SELECT * FROM arbitre");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter des arbitres</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .arbitre {
            margin-bottom: 10px;
        }
        .arbitre input {
            margin-right: 10px;
        }
        button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .delete-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
    <script>
        function ajouterArbitre() {
            const container = document.getElementById('arbitres-container');
            const newArbitre = document.createElement('div');
            newArbitre.classList.add('arbitre');
            newArbitre.innerHTML = `
                <input type="text" name="prenom[]" placeholder="Prénom de l'arbitre" required>
                <input type="tel" name="tel[]" placeholder="Téléphone de l'arbitre" required>
            `;
            container.appendChild(newArbitre);
        }
    </script>
</head>
<body>

<h1>Ajouter des arbitres</h1>

<form method="POST" action="">
    <div id="arbitres-container">
        <div class="arbitre">
            <input type="text" name="prenom[]" placeholder="Prénom de l'arbitre" required>
            <input type="tel" name="tel[]" placeholder="Téléphone de l'arbitre" required>
        </div>
    </div>

    <button type="button" onclick="ajouterArbitre()">Ajouter un autre arbitre</button>
    <button type="submit">Enregistrer les arbitres</button>
</form>

<h2>Liste des arbitres inscrits</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Prénom</th>
            <th>Téléphone</th>
            <th>Permanance</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['prenom']); ?></td>
                <td><?php echo htmlspecialchars($row['tel']); ?></td>
                <td><?php echo htmlspecialchars($row['permanence']); ?></td>
                <td>
                    <a href="?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet arbitre ?');">
                        <button class="delete-btn">Supprimer</button>
                    </a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

</body>
</html>

<?php
// Fermer la connexion à la base de données
$conn->close();
?>
