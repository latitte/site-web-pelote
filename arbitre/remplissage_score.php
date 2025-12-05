<?php
// Vérification du cookie
if (!isset($_COOKIE['fjckjedf8854f4df5dkf'])) {
    header('Location: ./login/');
    exit();
}

include("../logiciel/assets/extract_parametre.php");
include '../logiciel/assets/conn_bdd.php';

$duree_partie = $parametres['duree_partie'];


try {
    $dsn = "mysql:host=$servername;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}

// Récupération des parties de la soirée
$query = $pdo->query("SELECT id, jours, heure, partie, score, commentaire, partie_jouee FROM calendrier WHERE jours = CURDATE() and partie != 'Bloqué' ORDER BY heure ASC");
$nombre_de_partie = $query->rowCount();
$parties = $query->fetchAll(PDO::FETCH_ASSOC);

// Mise à jour des scores
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scores = $_POST['scores'];
    $commentaires = $_POST['commentaires'];
    $parties_jouees = $_POST['parties_jouees'];

    foreach ($scores as $id => $score_array) {
        $val1 = intval($score_array['equipe1']);
        $val2 = intval($score_array['equipe2']);
        $score = ($val1 === 0 && $val2 === 0) ? null : "$val1/$val2";

        $commentaire = $commentaires[$id] ?? '';
        $partie_jouee = isset($parties_jouees[$id]) ? 1 : 0;

        $stmt = $pdo->prepare("UPDATE calendrier SET score = ?, commentaire = ?, partie_jouee = ? WHERE id = ?");
        $stmt->execute([$score, $commentaire, $partie_jouee, $id]);

    }

    header('Location: ./score_ok.php');
    exit();
}

function getJoueurs($id) {
    global $var_tournoi;
    $api_url = "https://$var_tournoi.tournoi-pelote.com/arbitre/get_joueurs.php?id=" . intval($id);
    $json = file_get_contents($api_url);
    if ($json === false) return null;

    $data = json_decode($json, true);
    if (isset($data['joueur_1']) && isset($data['joueur_2'])) {
        return $data['joueur_1'] . ' & ' . $data['joueur_2'];
    } else {
        return "Équipe $id introuvable";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mettre à jour les scores</title>
    <link rel="icon" type="image/x-icon" href="../client/assets/tournoi-pelote.ico">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: rgb(43 98 38 / 79%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
            overflow: hidden;
        }
        .header-button {
            margin-bottom: 20px;
        }
        .header-button a {
            text-decoration: none;
            color: black;
            font-weight: 600;
            font-size: 18px;
            border: 2px solid #000000;
            border-radius: 8px;
            padding: 10px 20px;
            background-color: #ffffff;
            display: inline-block;
        }
        .header-button a:hover {
            background-color: #f0f0f0;
        }
        .popup-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 1200px;
            padding: 20px;
            box-sizing: border-box;
            position: relative;
            overflow-y: auto;
            max-height: 95vh;
            height: 95vh;
        }

        .popup-container h1 {
            margin-top: 0;
            font-size: 24px;
            font-weight: 600;
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
            position: relative;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        select, input[type="number"], input[type="submit"], textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
        }
        input[type="submit"] {
            background-color: #007aff;
            color: #ffffff;
            border: none;
            cursor: pointer;
            font-weight: 600;
            padding: 10px 0;
        }
        input[type="submit"]:hover {
            background-color: #005bb5;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
        }
        .score-inputs {
            display: flex;
            gap: 10px;
        }
        .score-inputs input {
            width: 50%;
            text-align: center;
        }

        @media (max-width: 600px) {
            .popup-container {
                width: 95%;
                padding: 15px;
                max-height: 90vh;
            }
            .popup-container h1 {
                font-size: 20px;
            }
            input[type="submit"] {
                font-size: 14px;
                padding: 8px 0;
            }
        }
    </style>
</head>
<body>
    <div class="popup-container">
        <div class="menu">
            <?php include("./assets/menu.php"); ?>
        </div>

        <h1>Mettre à jour les scores</h1>

        <form id="scoreForm" action="" method="post">
            <?php if (!empty($parties)): ?>
                <?php foreach ($parties as $partie): ?>
                    <?php
                        list($id_equipe1, $id_equipe2) = explode('/', $partie['partie']);
                        $equipe1 = getJoueurs($id_equipe1);
                        $equipe2 = getJoueurs($id_equipe2);
                        list($score1, $score2) = explode('/', $partie['score'] ?? '0/0');
                    ?>
                    <div class="form-group">
                        <label for="score_<?php echo $partie['id']; ?>">
                            Partie <?php echo $partie['partie']; ?> (<?php echo "$equipe1 / $equipe2"; ?>) à <?php echo $partie['heure']; ?>
                        </label>
                        <div class="score-inputs">
                            <input type="number" min="0" max="<?php echo $duree_partie;?>" name="scores[<?php echo $partie['id']; ?>][equipe1]" placeholder="<?php echo $equipe1; ?>" value="<?php echo $score1; ?>">
                            /
                            <input type="number" min="0" max="<?php echo $duree_partie;?>" name="scores[<?php echo $partie['id']; ?>][equipe2]" placeholder="<?php echo $equipe2; ?>" value="<?php echo $score2; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="commentaire_<?php echo $partie['id']; ?>">Commentaire</label>
                        <textarea id="commentaire_<?php echo $partie['id']; ?>" name="commentaires[<?php echo $partie['id']; ?>]" rows="3"><?php echo $partie['commentaire']; ?></textarea>
                    </div>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="partie_jouee_<?php echo $partie['id']; ?>" name="parties_jouees[<?php echo $partie['id']; ?>]" <?php echo $partie['partie_jouee'] ? 'checked' : ''; ?>>
                        <label for="partie_jouee_<?php echo $partie['id']; ?>">Partie jouée</label>
                    </div>
                    <hr>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune partie prévue pour ce soir.</p>
            <?php endif; ?>
            <input type="submit" value="Mettre à jour les scores">
        </form>
    </div>
</body>
</html>
