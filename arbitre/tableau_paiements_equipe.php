<?php
// Vérification de l'existence du cookie
if (!isset($_COOKIE['fjckjedf8854f4df5dkf'])) {
    // Si le cookie n'existe pas, rediriger vers la page de connexion
    header('Location: ./login/');  // Remplacez /login.php par l'URL de votre page de connexion
    exit();  // Assurez-vous d'arrêter l'exécution du script
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="./assets/style.css">
    <link rel="icon" type="image/x-icon" href="./assets/tournoi-pelote.ico">
	<link rel="manifest" href="./assets/manifest.json">
                <link rel="icon" type="image/x-icon" href="../client/assets/tournoi-pelote.ico">
</head>
<body>

<div class="popup">
    <div class="header">

    </div>

    <div class="menu">
        <?php include("./assets/menu.php"); ?>
    </div>


    
    <?php
include '../logiciel/assets/conn_bdd.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Fonction pour formater proprement le nom d'équipe
function formatEquipe($id) {
    return intval($id);
}

// Requête : récupérer tous les joueurs
$stmt = $pdo->query("SELECT id_equipe, joueur, status_paiement FROM joueurs");
$joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Création d’un mapping des équipes par racine (ex. 40)
$groupes_equipes = [];

foreach ($joueurs as $joueur) {
    $parts = explode('.', $joueur['id_equipe']);
    $racines = [];

    if (isset($parts[0])) $racines[] = intval($parts[0]);
    if (isset($parts[1]) && $parts[1] !== '00') $racines[] = intval($parts[1]);

    foreach ($racines as $racine) {
        if (!isset($groupes_equipes[$racine])) {
            $groupes_equipes[$racine] = [];
        }
        $groupes_equipes[$racine][] = $joueur;
    }
}

// Identifier les équipes complètes (2 joueurs ayant payé)
$equipes_payees = [];

foreach ($groupes_equipes as $id_equipe => $joueurs_associes) {
    $payes = array_filter($joueurs_associes, fn($j) => $j['status_paiement'] == 1);
    if (count($payes) >= 2) {
        $equipes_payees[] = [
            'id_equipe' => $id_equipe,
            'joueurs' => array_values($payes)
        ];
    }
}
?>


    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            /* background-color: #f5f5f7; */
            padding: 30px;
            margin: 0;
        }

        .equipe {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .equipe h3 {
            margin: 0 0 8px;
            font-size: 18px;
            color: #007aff;
        }

        .joueurs {
            font-size: 16px;
            color: #1c1c1e;
        }
    </style>



<h2>Équipes complètes ayant payé ✅</h2>

<?php if (empty($equipes_payees)): ?>
    <p>Aucune équipe complète n'a encore payé.</p>
<?php else: ?>
    <?php foreach ($equipes_payees as $equipe): ?>
        <div class="equipe">
            <h3>Équipe <?= htmlspecialchars(formatEquipe($equipe['id_equipe'])); ?></h3>
            <div class="joueurs">
                <?php
                $joueur_noms = array_map(fn($j) => htmlspecialchars($j['joueur']), array_slice($equipe['joueurs'], 0, 2));
                echo implode(' & ', $joueur_noms);
                ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>




</div>



</body>


</html>
