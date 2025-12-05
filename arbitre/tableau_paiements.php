<?php
// Vérification de l'existence du cookie
if (!isset($_COOKIE['fjckjedf8854f4df5dkf'])) {
    // Si le cookie n'existe pas, rediriger vers la page de connexion
    header('Location: ./login/');  // Remplacez /login.php par l'URL de votre page de connexion
    exit();  // Assurez-vous d'arrêter l'exécution du script
}
?>


<?php
// Inclure les informations de connexion
include '../logiciel/assets/conn_bdd.php';

try {
    // Établir la connexion avec PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : connexion à la base de données non établie. " . $e->getMessage());
}



// Récupération de la date du jour
$date_aujourdhui = date('Y-m-d');

// Requête pour récupérer les parties du jour
$sql = "SELECT id, heure, partie FROM calendrier WHERE jours = ? ORDER BY heure ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$date_aujourdhui]);
$parties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir une Partie</title>
	<link rel="manifest" href="./assets/manifest.json">
                <link rel="icon" type="image/x-icon" href="../client/assets/tournoi-pelote.ico">
    <style>
        body {
            font-family: 'San Francisco', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: rgb(43 98 38 / 79%)!important;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%; /* Largeur ajustée à 90% de la fenêtre */
            max-width: 1200px; /* Ajuste cette valeur selon la largeur maximale souhaitée */
            padding: 20px;
            box-sizing: border-box;
            position: relative;
            overflow-y: auto;
            max-height: 95vh; /* Hauteur ajustée à 95% de la fenêtre */
            height: 95vh;
        }
        .container h2 {
            margin: 0 0 20px;
            font-size: 24px;
            color: #333;
        }
        .partie {
            margin: 10px 0;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: left;
        }
        .partie a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        .partie a:hover {
            text-decoration: underline;
        }
        .partie span {
            font-size: 16px;
            color: #333;
        }
    </style>
</head>
<body>


<?php
// Inclure les informations de connexion
include '../logiciel/assets/conn_bdd.php';

try {
    // Établir la connexion avec PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : connexion à la base de données non établie. " . $e->getMessage());
}

// Initialiser les compteurs
$nombre_paye = 0;
$nombre_non_paye = 0;

// Requête SQL pour récupérer les informations des équipes
$sql = "SELECT id, `joueur`, `status_paiement`, id_equipe FROM joueurs";
$stmt = $pdo->query($sql);
$equipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer le nombre de payés et non-payés
foreach ($equipes as $equipe) {
    if ($equipe['status_paiement'] == 1) {
        $nombre_paye++;
    } else {
        $nombre_non_paye++;
    }
}

// Gérer la recherche
$searchResult = null;
if (isset($_POST['search'])) {
    $searchTerm = trim($_POST['search']);

    // Vérifier si le terme de recherche est un numéro d'équipe ou un nom de joueur
    if (is_numeric($searchTerm)) {
        // Recherche par ID d'équipe
        $searchSql = "SELECT `joueur`, `montant`, 'status_paiement' FROM joueurs WHERE id_equipe = ?";
        $searchStmt = $pdo->prepare($searchSql);
        $searchStmt->execute([$searchTerm]);
    } else {
        // Recherche par nom de joueur
        $searchSql = "SELECT `joueur`, `montant`, 'status_paiement' FROM joueurs WHERE `joueur` LIKE ?";
        $searchStmt = $pdo->prepare($searchSql);
        $searchStmt->execute(['%' . $searchTerm . '%']);
    }
    $searchResult = $searchStmt->fetch(PDO::FETCH_ASSOC);
}
?>

<?php
// Inclure les informations de connexion
include '../logiciel/assets/conn_bdd.php';

try {
    // Établir la connexion avec PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : connexion à la base de données non établie. " . $e->getMessage());
}

// Initialiser les compteurs
$nombre_paye = 0;
$nombre_non_paye = 0;

// Requête SQL pour récupérer les informations des équipes
$sql = "SELECT id_equipe, `joueur`, `montant`, `status_paiement` FROM joueurs";
$stmt = $pdo->query($sql);
$equipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer le nombre de payés et non-payés
foreach ($equipes as $equipe) {
    if ($equipe['status_paiement'] == 1) {
        $nombre_paye++;
    } else {
        $nombre_non_paye++;
    }
}

// Gérer la recherche
$searchResults = [];
if (isset($_POST['search'])) {
    $searchTerm = trim($_POST['search']);

    // Vérifier si le terme de recherche est un numéro d'équipe ou un nom de joueur
    if (is_numeric($searchTerm)) {
        // Recherche par ID d'équipe
        $searchSql = "SELECT id_equipe, `joueur`, `montant`, status_paiement FROM joueurs WHERE `id_equipe` = ?";
        $searchStmt = $pdo->prepare($searchSql);
        $searchStmt->execute([$searchTerm]);
    } else {
        // Recherche par nom de joueur
        $searchSql = "SELECT id_equipe, `joueur`, `montant`, status_paiement FROM joueurs WHERE `joueur` LIKE ?";
        $searchStmt = $pdo->prepare($searchSql);
        $searchStmt->execute(['%' . $searchTerm . '%']);
    }
    $searchResults = $searchStmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statut de Paiement des Équipes</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f5f5f7;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
        }
        .container {
            width: 90%;
            max-width: 1200px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            white-space: nowrap; /* Empêche le texte de passer à la ligne */
        }
        th {
            background-color: #007aff;
            color: white;
            text-transform: uppercase;
        }
        .paid {
            background-color: #34c759;
            color: white;
        }
        .not-paid {
            background-color: #ff3b30;
            color: white;
        }
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap; /* Permet à la barre de recherche de passer à la ligne si nécessaire */
        }
        .search-bar input[type="text"] {
            width: 100%;
            max-width: 300px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .search-bar input[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            background-color: #007aff;
            color: white;
            cursor: pointer;
            margin-left: 10px;
        }
        .search-result {
            margin-bottom: 20px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        .search-result span {
            color: #007aff;
        }
        .search-result .paid {
            color: #34c759;
        }
        .search-result .not-paid {
            color: #ff3b30;
        }

        /* Styles spécifiques aux petits écrans */
        @media (max-width: 768px) {
            .container {
                width: 100%;
                padding: 10px;
            }
            .search-bar input[type="submit"] {
                width: 100%;
                margin-left: 0;
                margin-top: 10px;
            }
            table {
                font-size: 14px;
            }
            th, td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>

<div class="container">


<div class="menu">
    <?php include("../arbitre/assets/menu.php"); ?>
</div>



    <h2>Statistiques des Paiements</h2>
    <p>Nombre de joueur ayant payé : <strong><?= $nombre_paye; ?></strong></p>
    <p>Nombre de joueurs n'ayant pas payé : <strong><?= $nombre_non_paye; ?></strong></p>

    <!-- Barre de recherche -->
    <div class="search-bar">
        <form method="POST" action="">
            <input type="text" name="search" placeholder="Rechercher par numéro d'équipe ou joueur..." required>
            <input type="submit" value="Rechercher">
        </form>
    </div>

<!-- Affichage des résultats de la recherche -->
<?php if (!empty($searchResults)): ?>
    <h3>Résultats de la recherche :</h3>
    <?php foreach ($searchResults as $result): ?>
        <div class="search-result">
            Le joueur <span><?= htmlspecialchars($result['joueur']) ?></span> a
            <span style="color: black;" class="<?= $result['status_paiement'] == 1 ? 'paid' : 'not-paid'; ?>">
                <?= $result['status_paiement'] == 1 ? 'payé' : 'pas payé'; ?>
            </span> et doit <span><?= htmlspecialchars($result['montant']) ?></span>€.
        </div>
    <?php endforeach; ?>
<?php elseif (isset($_POST['search'])): ?>
    <div class="search-result">
        Aucun résultat trouvé pour la recherche : <span><?= htmlspecialchars($searchTerm); ?></span>
    </div>
<?php endif; ?>


    <!-- Tableau des équipes -->
    <table>
        <thead>
            <tr>
                <th>Equipe</th>
                <th>Joueur</th>
                <th>Etat Paiement</th>
                <th>Montant</th>

            </tr>
        </thead>
        <tbody>
            <?php if (count($equipes) > 0): ?>
                <?php foreach ($equipes as $equipe): ?>
                    <tr class="<?= $equipe['status_paiement'] == 1 ? 'paid' : 'not-paid'; ?>">
                    <td>
    <?php
    $parts = explode('.', $equipe['id_equipe']);
    if (isset($parts[1]) && $parts[1] != '00') {
        echo intval($parts[0]) . ' & ' . intval($parts[1]);
    } else {
        echo intval($parts[0]);
    }
    ?>
</td>

                        <td><?= htmlspecialchars($equipe['joueur']); ?></td>
                        <td><?= $equipe['status_paiement'] == 1 ? 'Payé' : 'Non payé'; ?></td>
                        <td><?= htmlspecialchars($equipe['montant']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Aucun résultat trouvé.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>






</div>

</body>
</html>
