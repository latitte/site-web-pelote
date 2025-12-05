<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Menu vertical -->
<?php
include("./assets/menu.php");
?>

        <!-- Contenu principal -->
        <main role="main" class="container">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Paiement</h1>
            </div>

            <h2>Editer un paiement</h2>

<?php
// Connexion BDD
include '../logiciel/assets/conn_bdd.php';
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Mise à jour si formulaire envoyé
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['joueurs'] as $id => $data) {
        $paiement = isset($data['status_paiement']) ? 1 : 0;
        $montant = floatval($data['montant']);
        $stmt = $pdo->prepare("UPDATE joueurs SET status_paiement = ?, montant = ? WHERE id = ?");
        $stmt->execute([$paiement, $montant, $id]);
    }
    echo '<script>window.location.href = "./assets/paiement_auto.php";</script>';
    exit;
}

// Récupération des joueurs
$stmt = $pdo->query("SELECT id, id_equipe, joueur, montant, status_paiement FROM joueurs ORDER BY id_equipe");
$joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fonction pour afficher proprement l'équipe
function formatEquipe($id_equipe) {
    $parts = explode('.', $id_equipe);
    if (isset($parts[1]) && $parts[1] != '00') {
        return intval($parts[0]) . ' & ' . intval($parts[1]);
    } else {
        return intval($parts[0]);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Paiements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f7;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 10px;
        }

        .container {
            background: #fff;
            padding: 20px;
  
            max-width: 1200px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-left: 0px!important;
        }

        h2 {
            margin-top: 0;
            text-align: center;
            color: #1c1c1e;
        }

        form {
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ccc;
        }

        th {
            background-color: #e5e5ea;
            color: #000;
            font-size: 14px;
            text-transform: uppercase;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        input[type="checkbox"] {
            transform: scale(1.2);
        }

        .submit-button {
            margin-top: 20px;
            background-color: #007aff;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        .success {
            color: green;
            text-align: center;
            margin-bottom: 10px;
        }



    </style>
</head>
<body>

<div class="container">
    <h2>Modifier les paiements</h2>

    <?php if (isset($_GET['success'])): ?>
        <p class="success">Modifications enregistrées avec succès ✅</p>
    <?php endif; ?>

    <form method="post">
        <table>
            <thead>
                <tr>
                    <th>Équipe</th>
                    <th>Joueur</th>
                    <th>Montant (€)</th>
                    <th>Payé</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($joueurs as $joueur): ?>
                    <tr>
                        <td><?= formatEquipe($joueur['id_equipe']); ?></td>
                        <td><?= htmlspecialchars($joueur['joueur']); ?></td>
                        <td>
                            <input type="number" name="joueurs[<?= $joueur['id'] ?>][montant]" value="<?= htmlspecialchars($joueur['montant']); ?>" required>
                        </td>
                        <td style="text-align: center;">
                            <input type="checkbox" name="joueurs[<?= $joueur['id'] ?>][status_paiement]" <?= $joueur['status_paiement'] == 1 ? 'checked' : '' ?>>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit" class="submit-button">Enregistrer</button>
    </form>
</div>

</body>
</html>

        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
