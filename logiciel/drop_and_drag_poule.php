<?php
// Connexion à la BDD + paramètres
include("./assets/conn_bdd.php");
include("./assets/extract_parametre.php");

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

/* ---------------------------------------------------------
   🔥 Fonction de normalisation (anti bug accents & espaces)
--------------------------------------------------------- */
function normalize($str) {
    $str = trim($str);
    $str = preg_replace('/\s+/', ' ', $str); // espaces multiples → 1
    $str = mb_strtolower($str, 'UTF-8');     // minuscule

    // Suppression des accents
    $str = str_replace(
        ['é','è','ê','ë','à','â','î','ï','ô','ö','ù','û','ç'],
        ['e','e','e','e','a','a','i','i','o','o','u','u','c'],
        $str
    );
    return $str;
}

/* ---------------------------------------------------------
   🔥 Récupération & normalisation des séries
--------------------------------------------------------- */
$liste_series_raw = explode(',', $parametres['series']);
$liste_series = array_map('trim', $liste_series_raw);

// Série sélectionnée dans l'URL, sinon la première
$serie = isset($_GET['serie']) ? trim($_GET['serie']) : $liste_series[0];
$serie_norm = normalize($serie);

/* ---------------------------------------------------------
   🔥 Requête SQL compatible accents / casse / espaces
--------------------------------------------------------- */
$sql = $conn->prepare("
    SELECT id, `Joueur 1`, `Joueur 2`, poule,
    LOWER(REPLACE(REPLACE(REPLACE(serie,'é','e'),'è','e'),'ê','e')) AS serie_norm
    FROM inscriptions
    WHERE LOWER(REPLACE(REPLACE(REPLACE(serie,'é','e'),'è','e'),'ê','e')) = ?
");

$sql->bind_param("s", $serie_norm);
$sql->execute();
$result = $sql->get_result();

/* ---------------------------------------------------------
   🔥 Regroupement des équipes par poule
--------------------------------------------------------- */
$equipes = [];
while ($row = $result->fetch_assoc()) {
    $p = trim($row['poule'] ?: "Sans poule");
    $equipes[$p][] = $row;
}

/* ---------------------------------------------------------
   ✅ Trouver la série EXACTE à afficher (respect accents)
--------------------------------------------------------- */
$serie_affichage = $liste_series[0];
foreach ($liste_series as $s) {
    if (normalize($s) === $serie_norm) {
        $serie_affichage = $s;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edition Poules Drop & Drag</title>

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <style>
        .zone-poules { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px; }
        .poule-zone {
            flex: 1;
            min-width: 250px;
            background: #f0f0f0;
            border: 2px dashed #999;
            padding: 10px;
            border-radius: 8px;
        }
        .equipe {
            background: white;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 5px;
            cursor: grab;
        }
    </style>
</head>

<body>

<div class="d-flex">

    <!-- Menu vertical -->
    <?php include("./assets/menu.php"); ?>

    <!-- CONTENU PRINCIPAL -->
    <main role="main" class="container">

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Organisation des poules</h1>
        </div>

        <!-- Sélecteur de série -->
        <form method="GET">
            <label for="serie"><strong>Choisir une série :</strong></label>
            <select name="serie" id="serie" class="form-control w-auto d-inline-block ml-2" onchange="this.form.submit()">
                <?php foreach ($liste_series as $s): ?>
                    <option value="<?= htmlspecialchars($s) ?>"
                        <?= (normalize($s) === $serie_norm ? 'selected' : '') ?>>
                        <?= htmlspecialchars($s) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <h4 class="mt-3">Série sélectionnée : <?= htmlspecialchars($serie_affichage) ?></h4>

        <!-- ZONES DES POULES -->
        <div class="zone-poules" id="zones">
            <?php foreach ($equipes as $poule => $liste): ?>
                <div class="poule-zone"
                    data-poule="<?= htmlspecialchars($poule) ?>"
                    ondrop="drop(event)"
                    ondragover="allowDrop(event)">

                    <h3><?= htmlspecialchars($poule) ?></h3>

                    <?php foreach ($liste as $e): ?>
                        <div class="equipe"
                            draggable="true"
                            ondragstart="drag(event)"
                            id="equipe-<?= $e['id'] ?>">
                            <?= htmlspecialchars($e['Joueur 1'] . " / " . $e['Joueur 2']) ?>
                        </div>
                    <?php endforeach; ?>

                </div>
            <?php endforeach; ?>
        </div>

    </main>
</div>

<script>
function allowDrop(ev) { ev.preventDefault(); }

function drag(ev) {
  ev.dataTransfer.setData("text", ev.target.id);
}

function drop(ev) {
  ev.preventDefault();
  const data = ev.dataTransfer.getData("text");
  const equipe = document.getElementById(data);
  const newPoule = ev.currentTarget.getAttribute("data-poule");

  ev.currentTarget.appendChild(equipe);

  const id = data.split('-')[1];

  fetch('update_poule.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${id}&poule=${encodeURIComponent(newPoule)}`
  })
  .then(res => res.text())
  .then(console.log);
}
</script>

</body>
</html>
