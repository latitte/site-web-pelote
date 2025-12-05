<?php
include("./assets/extract_parametre.php");
$series_bdd = array_map('trim', explode(",", $parametres['series']));

// Connexion BDD
include("./assets/conn_bdd.php");
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("<div class='error-box'>Erreur de connexion : " . $conn->connect_error . "</div>");
}

// Traitement formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['matchups'])) {
    foreach ($_POST['matchups'] as $niveau => $equipe_ids) {
        if (is_array($equipe_ids) && count($equipe_ids) === 2) {
            $equipeA = $equipe_ids[0];
            $equipeB = $equipe_ids[1];

            if (!empty($equipeA) && !empty($equipeB)) {
                $verif = $conn->prepare("SELECT id FROM calendrier WHERE niveau = ? LIMIT 1");
                $verif->bind_param("s", $niveau);
                $verif->execute();
                $verif_result = $verif->get_result();

                if ($verif_result->num_rows > 0) {
                    echo "<div class='alert alert-red'>⚠️ Partie de niveau $niveau déjà existante.</div>";
                    continue;
                }

                foreach ($equipe_ids as $equipe_id) {
                    $stmt = $conn->prepare("UPDATE classement SET niveau = ? WHERE id = ?");
                    $stmt->bind_param("si", $niveau, $equipe_id);
                    $stmt->execute();
                }

                $stmt2 = $conn->prepare("INSERT INTO calendrier (jours, heure, partie, niveau, ia, validation_score, commentaire, partie_jouee, verif)
                                         VALUES ('0000-00-00', '19h00', ?, ?, 1, 0, '', 0, 0)");
                $partie = $equipeA . '/' . $equipeB;
                $stmt2->bind_param("ss", $partie, $niveau);
                $stmt2->execute();
            }
        }
    }
    echo "<div class='alert alert-green'>✅ Arbre et parties enregistrés avec succès.</div>";
}

// Séries et classement
$serie_nom = $_GET['serie'] ?? trim($series_bdd[0]);
$niveau_map = [];
$sql_niveaux = "SELECT id, niveau FROM classement WHERE serie = ?";
$stmt_niveaux = $conn->prepare($sql_niveaux);
$stmt_niveaux->bind_param("s", $serie_nom);
$stmt_niveaux->execute();
$res_niv = $stmt_niveaux->get_result();
while ($row = $res_niv->fetch_assoc()) {
    if (!empty($row['niveau'])) {
        $niveau_map[$row['niveau']][] = $row['id'];
    }
}

$prefixe_serie = strtoupper(substr($serie_nom, 0, 1));
$classement_mode = $_GET['classement'] ?? 'serie';

$equipes = [];
if ($classement_mode === 'poule') {
    $sql = "SELECT * FROM classement_poule WHERE serie = '$serie_nom' ORDER BY niveau, place ASC";
} else {
    $sql = "SELECT * FROM classement WHERE serie = '$serie_nom' ORDER BY place ASC";
}
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) $equipes[] = $row;

$poules_grouped = [];
if ($classement_mode === 'poule') {
    foreach ($equipes as $equipe) $poules_grouped[$equipe['niveau']][] = $equipe;
}

function renderMatch($cle, $label, $equipes, $niveau_map, $drag = false) {
    ob_start();
    echo "<div class='match' data-niveau='$cle'>";
    echo "<h4>$label</h4>";
    if ($drag) {
        echo "<div class='dropzone' ondrop='drop(event)' ondragover='allowDrop(event)' data-niveau='$cle'>";
        foreach ($niveau_map[$cle] ?? [] as $equipe_id) {
            foreach ($equipes as $equipe) {
                if ($equipe['id'] == $equipe_id) {
                    echo "<div class='draggable' draggable='true' ondragstart='drag(event)' data-id='{$equipe['id']}' onclick='this.remove()'>" . htmlspecialchars($equipe['joueurs']) . "</div>";
                }
            }
        }
        echo "</div>";
    } else {
        echo "<div class='select-row'>";
        echo "<select name=\"matchups[$cle][]\"><option value=''>-- Équipe A --</option>";
        foreach ($equipes as $equipe) {
            $selected = (isset($niveau_map[$cle][0]) && $niveau_map[$cle][0] == $equipe['id']) ? 'selected' : '';
            echo "<option value='{$equipe['id']}' $selected>" . htmlspecialchars($equipe['joueurs']) . "</option>";
        }
        echo "</select>";
        echo "<select name=\"matchups[$cle][]\"><option value=''>-- Équipe B --</option>";
        foreach ($equipes as $equipe) {
            $selected = (isset($niveau_map[$cle][1]) && $niveau_map[$cle][1] == $equipe['id']) ? 'selected' : '';
            echo "<option value='{$equipe['id']}' $selected>" . htmlspecialchars($equipe['joueurs']) . "</option>";
        }
        echo "</select>";
        echo "</div>";
    }
    echo "</div>";
    return ob_get_clean();
}

// Calcul du nombre d'équipes et niveaux à afficher
$nb_equipes = count($equipes);
if ($nb_equipes <= 2) $niveaux_a_afficher = ['60'];
elseif ($nb_equipes <= 4) $niveaux_a_afficher = ['51', '52', '60'];
elseif ($nb_equipes <= 8) $niveaux_a_afficher = ['41','42','43','44','51','52','60'];
else $niveaux_a_afficher = ['31','32','33','34','35','36','37','38','41','42','43','44','51','52','60'];

$etiquettes = [
    '31'=>'1/8 A1','32'=>'1/8 A2','33'=>'1/8 A3','34'=>'1/8 A4',
    '35'=>'1/8 B1','36'=>'1/8 B2','37'=>'1/8 B3','38'=>'1/8 B4',
    '41'=>'1/4 A1','42'=>'1/4 A2','43'=>'1/4 B1','44'=>'1/4 B2',
    '51'=>'Demi A','52'=>'Demi B','60'=>'Finale'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscriptions</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Menu vertical -->
        <?php include("./assets/menu.php"); ?>

        <!-- Contenu principal -->
        <main role="main" class="container">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Manuel poule</h1>
            </div>

<?php include "./assets/menu.php"; ?>

<style>
/* ======== LAYOUT GÉNÉRAL ======== */
body {
    font-family: 'Inter', sans-serif;
    background: #f5f6fa;
    margin: 0;
}
#sidebar, .menu, .main-menu {
    position: fixed !important;
    top: 0;
    left: 0;
    z-index: 1000;
    height: 100vh;
}
.main-content {
    margin-left: 220px;
    width: calc(100% - 220px);
    padding: 25px;
    box-sizing: border-box;
}
@media(max-width: 900px) {
    .main-content {
        margin-left: 0;
        width: 100%;
        padding: 15px;
    }
}

/* ======== STYLE DES BLOCS ======== */
.top-controls {
    text-align: center;
    margin-bottom: 30px;
}
select {
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background: #fff;
    font-size: 15px;
    margin: 0 5px;
}
.container {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 25px;
}
.classement, .arbre {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
}
.classement {
    width: 280px;
}
.classement h3 {
    text-align: center;
    margin-bottom: 10px;
}
.draggable {
    padding: 8px 10px;
    margin: 6px 0;
    background: #e3ecff;
    border-radius: 8px;
    cursor: grab;
    transition: all .2s ease;
}
.draggable:hover {
    background: #d0dbff;
    transform: translateY(-1px);
}
.arbre {
    flex: 1;
    min-width: 600px;
}
.arbre h2 {
    text-align: center;
    margin-bottom: 20px;
}
.bracket {
    display: flex;
    justify-content: center;
    gap: 30px;
}
.round {
    display: flex;
    flex-direction: column;
    gap: 30px;
}
.match {
    background: #f8f9fc;
    border: 1px solid #e3e3e3;
    border-radius: 10px;
    padding: 10px;
    text-align: center;
}
.match h4 {
    margin: 0 0 10px;
    font-size: 14px;
    color: #333;
}
.select-row select {
    width: 48%;
}
.dropzone {
    min-height: 60px;
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 8px;
    background: #fff;
}
button {
    background: #007aff;
    border: none;
    padding: 12px 26px;
    border-radius: 10px;
    color: white;
    font-size: 16px;
    cursor: pointer;
    margin-top: 25px;
}
button:hover {
    background: #0062cc;
}
.alert {
    text-align: center;
    margin: 10px auto;
    padding: 12px 18px;
    border-radius: 8px;
    font-weight: 500;
    width: fit-content;
}
.alert-red { background: #ffe2e2; color: #a40000; }
.alert-green { background: #e1ffea; color: #007a24; }
.error-box {
    background: #fff3f3;
    border: 1px solid #d55;
    padding: 15px;
    border-radius: 8px;
    color: #b00;
    margin: 20px auto;
    width: fit-content;
}
</style>

<script>
function toggleMode() {
    const mode = document.getElementById('modeSelect').value;
    const classement = document.getElementById('classementSelect').value;
    location.href = '?mode=' + mode + '&classement=' + classement;
}
function toggleSerie() {
    const serie = document.getElementById('serieSelect').value;
    const mode = document.getElementById('modeSelect').value;
    const classement = document.getElementById('classementSelect').value;
    location.href = '?mode=' + mode + '&classement=' + classement + '&serie=' + serie;
}
function toggleClassement() { toggleMode(); }
function allowDrop(ev) { ev.preventDefault(); }
function drag(ev) { ev.dataTransfer.setData("text", ev.target.dataset.id); }
function drop(ev) {
    ev.preventDefault();
    const data = ev.dataTransfer.getData("text");
    const dropzone = ev.target.closest('.dropzone');
    const match = dropzone.closest('.match');
    if (dropzone && match && dropzone.children.length < 2) {
        const joueur = document.querySelector(`.draggable[data-id='${data}']`);
        if (joueur && !dropzone.contains(joueur)) {
            const clone = joueur.cloneNode(true);
            clone.setAttribute('onclick', 'this.remove()');
            dropzone.appendChild(clone);
            const niveau = match.dataset.niveau;
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `matchups[${niveau}][]`;
            input.value = data;
            match.appendChild(input);
        }
    }
}
</script>
</head>

<body>
<div class="main-content">

    <div class="top-controls">
        <label>Mode :</label>
        <select id="modeSelect" onchange="toggleMode()">
            <option value="select" <?= ($_GET['mode'] ?? '') !== 'drag' ? 'selected' : '' ?>>Menu déroulant</option>
            <option value="drag" <?= ($_GET['mode'] ?? '') === 'drag' ? 'selected' : '' ?>>Glisser-déposer</option>
        </select>
        <label>Classement :</label>
        <select id="classementSelect" onchange="toggleClassement()">
            <option value="serie" <?= ($_GET['classement'] ?? '') !== 'poule' ? 'selected' : '' ?>>Par série</option>
            <option value="poule" <?= ($_GET['classement'] ?? '') === 'poule' ? 'selected' : '' ?>>Par poule</option>
        </select>
        <label>Série :</label>
        <select id="serieSelect" onchange="toggleSerie()">
            <?php foreach ($series_bdd as $serie): $serie_trimmed = trim($serie); ?>
                <option value="<?= urlencode($serie_trimmed) ?>" <?= ($_GET['serie'] ?? '') === $serie_trimmed ? 'selected' : '' ?>>
                    <?= htmlspecialchars($serie_trimmed) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="container">
        <div class="classement">
            <h3>Classement</h3>
            <?php if ($classement_mode === 'poule'): ?>
                <?php foreach ($poules_grouped as $poule => $equipe_list): ?>
                    <div class="poule-title">Poule <?= htmlspecialchars($poule) ?></div>
                    <ol>
                        <?php foreach ($equipe_list as $equipe): ?>
                            <li class="draggable" draggable="true" ondragstart="drag(event)" data-id="<?= $equipe['id'] ?>">
                                <strong><?= htmlspecialchars($equipe['joueurs']) ?></strong><br>
                                Points : <?= $equipe['points'] ?> | Diff : <?= $equipe['average'] ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endforeach; ?>
            <?php else: ?>
                <ol>
                    <?php foreach ($equipes as $equipe): ?>
                        <li class="draggable" draggable="true" ondragstart="drag(event)" data-id="<?= $equipe['id'] ?>">
                            <strong><?= htmlspecialchars($equipe['joueurs']) ?></strong><br>
                            Points : <?= $equipe['points'] ?> | Diff : <?= $equipe['average'] ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>

        <div class="arbre">
            <h2>Arbre de tournoi</h2>
            <form method="POST">
                <div class="bracket">
                    <?php
                    $rounds = [];
                    foreach ($niveaux_a_afficher as $niv) {
                        $prefix = intval($niv / 10);
                        $rounds[$prefix][] = $niv;
                    }
                    ksort($rounds);
                    foreach ($rounds as $round_niveaux) {
                        echo "<div class='round'>";
                        foreach ($round_niveaux as $niv) {
                            $code = $niv . $prefixe_serie;
                            $label = $etiquettes[$niv] . ' ' . $prefixe_serie;
                            echo renderMatch($code, $label, $equipes, $niveau_map, ($_GET['mode'] ?? '') === 'drag');
                        }
                        echo "</div>";
                    }
                    ?>
                </div>
                <div style="text-align:center;">
                    <button type="submit">💾 Enregistrer l'arbre</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
