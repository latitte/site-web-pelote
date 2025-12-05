<?php
// Inclure les informations de connexion
include '../logiciel/assets/extract_parametre.php';
$duree_partie = $parametres['duree_partie'];

try {
    // Établir la connexion avec PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : connexion à la base de données non établie. " . $e->getMessage());
}

if (!isset($_GET['id'])) {
    die("ID de la partie non spécifié.");
}

$partie_id = intval($_GET['id']);

// Requête pour récupérer les informations de la partie
$sql = "SELECT partie, live_score_A, live_score_B FROM calendrier WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$partie_id]);
$partie = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$partie) {
    die("Partie non trouvée.");
}

// Extraire les numéros des équipes depuis le champ `partie`
list($equipe_A_num, $equipe_B_num) = explode('/', $partie['partie']);

// Récupérer les informations des joueurs pour chaque équipe en fonction des numéros d'équipe
$sql_equipe_A = "SELECT `Joueur 1`, `Joueur 2` FROM inscriptions WHERE id = ?";
$stmt_equipe_A = $pdo->prepare($sql_equipe_A);
$stmt_equipe_A->execute([$equipe_A_num]);
$equipe_A_info = $stmt_equipe_A->fetch(PDO::FETCH_ASSOC);

$sql_equipe_B = "SELECT `Joueur 1`, `Joueur 2` FROM inscriptions WHERE id = ?";
$stmt_equipe_B = $pdo->prepare($sql_equipe_B);
$stmt_equipe_B->execute([$equipe_B_num]);
$equipe_B_info = $stmt_equipe_B->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="manifest" href="./assets/manifest.json">
    <title>Saisir le Score</title>
    <style>
body {
    font-family: 'San Francisco', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    overflow: hidden;
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgb(43 98 38 / 79%);
    display: flex;
    justify-content: center;
    align-items: center;
}

.popup {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
    width: 500px;
    padding: 30px;
    text-align: center;
    position: relative;
    max-width: 90%;
    max-height: 80%;
    overflow: auto;
}

.popup h2 {
    margin: 0 0 20px;
    font-size: 24px;
    color: #333;
}

.scoreboard {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: nowrap; /* Enlève les retours à la ligne sur les grands écrans */
}

.team {
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    width: 230px;
    text-align: center;
    margin: 10px;
    box-sizing: border-box; /* Assure que le padding et la bordure sont inclus dans la largeur */
}

.team.red {
    background-color: #ffdddd;
}

.team.blue {
    background-color: #ddeeff;
}

.team h3 {
    margin: 0;
    font-size: 20px;
    color: #333;
}

.team .score {
    font-size: 48px;
    margin: 20px 0;
}

.team button {
    background-color: #007aff;
    color: #fff;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.team button.minus {
    background-color: #ff3b30;
}

.team button:hover {
    opacity: 0.8;
}

.popup .close-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #ddd;
    border: none;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 20px;
    color: #333;
}

/* Responsive styles for mobile devices */
@media (max-width: 768px) {
    .popup {
        width: 90%;
        height: 90%;
        /* display: flex;
        flex-direction: column;
        justify-content: center; */
    }

    .overlay{
        padding: 0%;
        height: 110%;
        top:auto;
    }

    .scoreboard {
        flex-direction: column; /* Affichage en colonne sur les petits écrans */
        align-items: stretch; /* Assure que les éléments prennent toute la largeur disponible */
    }

    .team {
        width: 100%; /* Les équipes prennent toute la largeur disponible sur mobile */
        margin: 10px 0; /* Espace entre les équipes en colonne */
    }
}

    </style>
</head>
<body>

<div class="overlay">
    <div class="popup">
        <button class="close-btn" onclick="redirectToPage()">×</button>
        <h2>Saisir le Score pour la Partie: <?php echo htmlspecialchars($partie['partie']); ?></h2>
        <div class="scoreboard">
            <div class="team red" id="teamA">
                <h3>Équipe <?php echo htmlspecialchars($equipe_A_num); ?></h3>
                <p><?php echo htmlspecialchars($equipe_A_info['Joueur 1']); ?><br><?php echo htmlspecialchars($equipe_A_info['Joueur 2']); ?></p>
                <div class="score" id="scoreA"><?php echo intval($partie['live_score_A']); ?></div>
                <button class="plus" onclick="updateScore('A', 1)">+</button>
                <button class="minus" onclick="updateScore('A', -1)">-</button>
            </div>
            <div class="team blue" id="teamB">
                <h3>Équipe <?php echo htmlspecialchars($equipe_B_num); ?></h3>
                <p><?php echo htmlspecialchars($equipe_B_info['Joueur 1']); ?><br><?php echo htmlspecialchars($equipe_B_info['Joueur 2']); ?></p>
                <div class="score" id="scoreB"><?php echo intval($partie['live_score_B']); ?></div>
                <button class="plus" onclick="updateScore('B', 1)">+</button>
                <button class="minus" onclick="updateScore('B', -1)">-</button>
            </div>
        </div>
    </div>
</div>

<script>
function redirectToPage() {
    window.location.href = "./live_score.php"; // Changez cette URL vers la page où vous voulez rediriger
}

function updateScore(team, delta) {
    var xhr = new XMLHttpRequest();
    var scoreElement = document.getElementById("score" + (team === 'A' ? 'A' : 'B'));
    var currentScore = parseInt(scoreElement.textContent);
    var newScore = currentScore + delta;

    // Limiter le score à 30 points
    if (newScore < 0) newScore = 0; // éviter les scores négatifs
    if (newScore > <?php echo $duree_partie; ?>) newScore = <?php echo $duree_partie; ?>; // limiter à 30 points

    // Mise à jour visuelle du score
    scoreElement.textContent = newScore;

    // Envoi de la mise à jour à la base de données
    xhr.open("POST", "update_score.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
        if (xhr.status === 200) {
            if (xhr.responseText.trim() === "FINI") {
                alert("Partie terminée !");
                // Désactiver les boutons pour éviter d'autres clics
                document.querySelectorAll("button.plus, button.minus").forEach(btn => btn.disabled = true);
            }
        } else {
            alert("Erreur lors de la mise à jour du score");
        }
    }
};

    xhr.send("score=" + newScore + "&team=" + team + "&partie_id=<?php echo $partie_id; ?>");
}

</script>

</body>
</html>
