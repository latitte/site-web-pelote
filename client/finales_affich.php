<?php

include '../logiciel/assets/extract_parametre.php';
$duree_partie = $parametres['duree_partie'];
$duree_partie = intval($duree_partie);

$series = explode(",", $parametres['series']);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir une série</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        form {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        input[type="submit"] {
            background-color: #6c757d; /* Couleur de fond gris discret */
            color: #fff;
            border: 1px solid #6c757d; /* Bordure gris discrète */
            padding: 8px 16px; /* Réduit la taille du bouton */
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px; /* Taille du texte plus petite */
            transition: background-color 0.3s, border-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #5a6268; /* Couleur de fond légèrement plus foncée au survol */
            border-color: #5a6268; /* Bordure légèrement plus foncée au survol */
        }

        input[type="submit"]:active {
            background-color: #4e555b; /* Couleur de fond au clic */
            border-color: #4e555b; /* Bordure au clic */
        }


        /* Conteneur visible, masque le débordement */
#viewport{
  position: relative;
  width: 100%;
  height: calc(100vh - 120px); /* ajuste si besoin */
  overflow: hidden;
  background: #f4f4f4;
  border: 1px solid #e3e3e3;
  border-radius: 8px;
}

/* Élément que l'on déplace/zoome */
#canvas{
  position: absolute;
  top: 0; left: 0;
  transform-origin: 0 0; /* coin haut-gauche pour des maths simples */
  will-change: transform;
  cursor: grab;
}
#canvas.dragging { cursor: grabbing; }

/* Optionnel : taille minimale pour que l'arbre ait de la place au départ */
.tournament-tree{
  min-width: 1200px;   /* ajuste à la largeur de ton arbre */
  min-height: 800px;   /* ajuste à la hauteur de ton arbre */
  margin: 40px;        /* un peu d’air autour */
}



    </style>
</head>
<body>

<form method="get" action="">
    <label for="serie">Choisissez une série:</label>
    <select name="serie" id="serie">
        <option value="P" <?php if (isset($_GET['serie']) && $_GET['serie'] == 'P') echo 'selected'; ?>>Première Série</option>
        <option value="S" <?php if (isset($_GET['serie']) && $_GET['serie'] == 'S') echo 'selected'; ?>>Seconde Série</option>
        <option value="T" <?php if (isset($_GET['serie']) && $_GET['serie'] == 'T') echo 'selected'; ?>>Troisième Série</option>
        <option value="F" <?php if (isset($_GET['serie']) && $_GET['serie'] == 'F') echo 'selected'; ?>>Série Féminine</option>
        <option value="M" <?php if (isset($_GET['serie']) && $_GET['serie'] == 'M') echo 'selected'; ?>>Série Mixte</option>
        <option value="D" <?php if (isset($_GET['serie']) && $_GET['serie'] == 'D') echo 'selected'; ?>>Série Débutante</option>
        <!-- Ajoutez d'autres options si nécessaire -->
    </select>
    <input type="submit" value="Valider">
</form>

<?php

include '../logiciel/assets/extract_parametre.php';
$duree_partie = $parametres['duree_partie'];
$duree_partie = intval($duree_partie);

$series = explode(",", $parametres['series']);

// Vérifiez si une série a été choisie dans le formulaire
if (isset($_GET['serie']) && !empty($_GET['serie'])) {
    $serie_choisi = $_GET['serie'];
} else {
    $serie_choisi = "P"; // Valeur par défaut si aucune série n'est choisie
}

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Préparation et exécution de la requête
    $stmt = $conn->prepare("SELECT * FROM calendrier WHERE niveau LIKE :levelPattern");
    $stmt->execute(['levelPattern' => "%$serie_choisi%"]);

    // Récupération des résultats dans un tableau associatif
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Niveaux à rechercher
    $levelsToSearch = ['31', '32', '33', '34', '35', '36', '37', '38', '41', '42', '43', '44', '51', '52', '60'];
    
    // Dictionnaire pour stocker les résultats
    $levelData = [];

    // Fonction pour filtrer les résultats selon le niveau spécifié
    function filterByLevel($data, $level) {
        foreach ($data as $row) {
            if (strpos($row['niveau'], $level) !== false) {
                return $row;
            }
        }
        return null;
    }

    // Boucle pour récupérer les données de chaque niveau
    foreach ($levelsToSearch as $level) {
        $filteredRow = filterByLevel($result, $level);
        if ($filteredRow) {
            $partieParts = isset($filteredRow['partie']) ? explode('/', $filteredRow['partie']) : [null, null];
            $scoreParts = isset($filteredRow['score']) ? explode('/', $filteredRow['score']) : [null, null];

            $score1 = isset($scoreParts[0]) ? (int)$scoreParts[0] : null;
            $score2 = isset($scoreParts[1]) ? (int)$scoreParts[1] : null;

            // Déterminer l'équipe gagnante
            $winningTeam = null;
            if ($score1 >= $duree_partie) {
                $winningTeam = isset($partieParts[0]) ? $partieParts[0] : null;
            } elseif ($score2 >= $duree_partie) {
                $winningTeam = isset($partieParts[1]) ? $partieParts[1] : null;
            }

            $levelData[$level] = [
                'date' => $filteredRow['jours'],
                'equipe1' => isset($partieParts[0]) ? $partieParts[0] : null,
                'equipe2' => isset($partieParts[1]) ? $partieParts[1] : null,
                'score1' => $score1,
                'score2' => $score2,
                'equipe_gagnante' => $winningTeam
            ];
        } else {
            $levelData[$level] = null;
        }
    }

    // Déterminer à quel tour commence le joueur ou l'équipe
    $tour_commence = 'Non trouvé';
    foreach ($levelsToSearch as $level) {
        if ($levelData[$level] !== null) {
            if (in_array($level, ['31', '32', '33', '34', '35', '36', '37', '38'])) {
                $tour_commence = "1/8 de finales (niveau $level)";
                $tour = 3;
                break;
            } elseif (in_array($level, ['41', '42', '43', '44'])) {
                $tour_commence = "1/4 de finales (niveau $level)";
                $tour = 4;
                break;
            } elseif (in_array($level, ['51', '52'])) {
                $tour_commence = "1/2 finales (niveau $level)";
                $tour = 5;
                break;
            } elseif ($level == '60') {
                $tour_commence = "Finale (niveau $level)";
                $tour = 6;
                break;
            }
        }
    }

    // Affichage des résultats
    // echo "<h2>Tour de début: $tour_commence</h2>";
    // echo "<pre>";
    // print_r($levelData);
    // echo "</pre>";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}

// Fermeture de la connexion (optionnel, PDO le fait automatiquement en fin de script)
$conn = null;
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Phases Finales</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="./assets/styles_arbre.css" />
</head>
<body>
    <h1>Phases Finales</h1>
    <div id="viewport">
  <div id="canvas">
    <div class="tournament-tree">

    <?php if($tour == 3){ ?>
        <div class="slot slot--18-1 slot--top slot--left">
            <div class="team team--home">
                <span id="1" class="team__name"><?php echo $levelData['31']['equipe1'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['31']['score1'] ?? '0'; ?></span>
            </div>
            <div class="team team--away team--winner">
                <span id="2" class="team__name"><?php echo $levelData['31']['equipe2'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['31']['score2'] ?? '0'; ?></span>
            </div>
        </div>
        <div class="slot slot--18-2 slot--left">
            <div class="team team--home">
                <span class="team__name"><?php echo $levelData['32']['equipe1'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['32']['score1'] ?? '0'; ?></span>
            </div>
            <div class="team team--away team--winner">
                <span class="team__name"><?php echo $levelData['32']['equipe2'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['32']['score2'] ?? '0'; ?></span>
            </div>
        </div>
        <div class="slot slot--18-3 slot--top slot--left">
            <div class="team team--home team--winner">
                <span class="team__name"><?php echo $levelData['33']['equipe1'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['33']['score1'] ?? '0'; ?></span>
            </div>
            <div class="team team--away">
                <span class="team__name"><?php echo $levelData['33']['equipe2'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['33']['score2'] ?? '0'; ?></span>
            </div>
        </div>
        <div class="slot slot--18-4 slot--left">
            <div class="team team--home">
                <span class="team__name"><?php echo $levelData['34']['equipe1'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['34']['score1'] ?? '0'; ?></span>
            </div>
            <div class="team team--away team--winner">
                <span class="team__name"><?php echo $levelData['34']['equipe2'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['34']['score2'] ?? '0'; ?></span>
            </div>
        </div>
        <div class="slot slot--18-5 slot--top slot--right">
            <div class="team team--home team--winner">
                <span class="team__name"><?php echo $levelData['35']['equipe1'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['35']['score1'] ?? '0'; ?></span>
            </div>
            <div class="team team--away">
                <span class="team__name"><?php echo $levelData['35']['equipe2'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['35']['score2'] ?? '0'; ?></span>
            </div>
        </div>
        <div class="slot slot--18-6 slot--right">
            <div class="team team--home team--winner">
                <span class="team__name"><?php echo $levelData['36']['equipe1'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['36']['score1'] ?? '0'; ?></span>
            </div>
            <div class="team team--away">
                <span class="team__name"><?php echo $levelData['36']['equipe2'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['36']['score2'] ?? '0'; ?></span>
            </div>
        </div>
        <div class="slot slot--18-7 slot--top slot--right">
            <div class="team team--home team--winner">
                <span class="team__name"><?php echo $levelData['37']['equipe1'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['37']['score1'] ?? '0'; ?></span>
            </div>
            <div class="team team--away">
                <span class="team__name"><?php echo $levelData['37']['equipe2'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['37']['score2'] ?? '0'; ?></span>
            </div>
        </div>
        <div class="slot slot--18-8 slot--right">
            <div class="team team--home">
                <span class="team__name"><?php echo $levelData['38']['equipe1'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['38']['score1'] ?? '0'; ?></span>
            </div>
            <div class="team team--away team--winner">
                <span class="team__name"><?php echo $levelData['38']['equipe2'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['38']['score2'] ?? '0'; ?></span>
            </div>
        </div>


        <?php } 
        if($tour == 3 || $tour == 4){ ?>
        <div class="slot slot--14-1 slot--left">
            <div class="team team--home">
                <span class="team__name"><?php echo $levelData['31']['equipe_gagnante'] ?? $levelData['41']['equipe1'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['41']['score1'] ?? '0'; ?></span>
            </div>
            <div class="team team--away team--winner">
                <span class="team__name"><?php echo $levelData['32']['equipe_gagnante'] ?? $levelData['41']['equipe2'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['41']['score2'] ?? '0'; ?></span>
            </div>
        </div>
        <div class="slot slot--14-2 slot--left">
            <div class="team team--home team--winner">
                <span class="team__name"><?php echo $levelData['33']['equipe_gagnante'] ?? $levelData['42']['equipe1'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['42']['score1'] ?? '0'; ?></span>
            </div>
            <div class="team team--away">
                <span class="team__name"><?php echo $levelData['34']['equipe_gagnante']  ?? $levelData['42']['equipe2'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['42']['score2'] ?? '0'; ?></span>
            </div>
        </div>
        <div class="slot slot--14-3 slot--right">
            <div class="team team--home team--winner">
                <span class="team__name"><?php echo $levelData['35']['equipe_gagnante']  ?? $levelData['43']['equipe1'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['43']['score1'] ?? '0'; ?></span>
            </div>
            <div class="team team--away">
                <span class="team__name"><?php echo $levelData['36']['equipe_gagnante']  ?? $levelData['43']['equipe2'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['43']['score2'] ?? '0'; ?></span>
            </div>
        </div>
        <div class="slot slot--14-4 slot--right">
            <div class="team team--home team--winner">
                <span class="team__name"><?php echo $levelData['37']['equipe_gagnante']  ?? $levelData['44']['equipe1'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['44']['score1'] ?? '0'; ?></span>
            </div>
            <div class="team team--away">
                <span class="team__name"><?php echo $levelData['38']['equipe_gagnante']  ?? $levelData['44']['equipe2'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['44']['score2'] ?? '0'; ?></span>
            </div>
        </div>

        <?php } 
        if($tour == 3 || $tour == 4 || $tour == 5){ ?>


        <div class="slot slot--12-1 slot--left">
            <div class="team team--home team--winner">
                <span class="team__name"><?php echo $levelData['41']['equipe_gagnante']  ?? $levelData['51']['equipe1'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['51']['score1'] ?? '0'; ?></span>
            </div>
            <div class="team team--away">
                <span class="team__name"><?php echo $levelData['42']['equipe_gagnante']  ?? $levelData['51']['equipe2'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['51']['score2'] ?? '0'; ?></span>
            </div>
        </div>
        <div class="slot slot--12-2 slot--right">
            <div class="team team--home">
                <span class="team__name"><?php echo $levelData['43']['equipe_gagnante']  ?? $levelData['52']['equipe1'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['52']['score1'] ?? '0'; ?></span>
            </div>
            <div class="team team--away team--winner">
                <span class="team__name"><?php echo $levelData['44']['equipe_gagnante']  ?? $levelData['52']['equipe2'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['52']['score2'] ?? '0'; ?></span>
            </div>
        </div>

        <?php } 
        if($tour == 3 || $tour == 4 || $tour == 5 || $tour == 6){ ?>

        <div class="slot slot--11-1">    
            <div class="team team--home team--winner">
                <span class="team__name"><?php echo $levelData['51']['equipe_gagnante']  ?? $levelData['60']['equipe1'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['60']['score1'] ?? '0'; ?></span>
            </div>
            <div class="team team--away">
                <span class="team__name"><?php echo $levelData['52']['equipe_gagnante']  ?? $levelData['60']['equipe2'] ?? 'No Data'; ?></span><span class="team__score"><?php echo $levelData['60']['score2'] ?? '0'; ?></span>
            </div>
        </div>
        <?php } ?>
    </div>
  </div>
</div>


<script>
(function(){
  const viewport = document.getElementById('viewport');
  const canvas   = document.getElementById('canvas');

  // Place le contenu au départ
  let scale = 1, minScale = 0.3, maxScale = 2.5;
  let pos = { x: 20, y: 20 };      // décalage initial
  let isDragging = false;
  let dragStart = { x: 0, y: 0 };
  let panStart = { x: 0, y: 0 };

  function applyTransform(){
    canvas.style.transform = `translate(${pos.x}px, ${pos.y}px) scale(${scale})`;
  }
  applyTransform();

  // Molette = zoom (centré sur le pointeur)
  viewport.addEventListener('wheel', (e) => {
    e.preventDefault();

    const rect = viewport.getBoundingClientRect();
    const pointer = { x: e.clientX - rect.left, y: e.clientY - rect.top };

    const prevScale = scale;
    const zoomFactor = Math.exp(-e.deltaY * 0.0015); // doux et fluide
    scale = Math.max(minScale, Math.min(maxScale, scale * zoomFactor));

    // Conserver le point sous le curseur (zoom focus)
    const sx = (pointer.x - pos.x) / prevScale;
    const sy = (pointer.y - pos.y) / prevScale;
    pos.x = pointer.x - sx * scale;
    pos.y = pointer.y - sy * scale;

    applyTransform();
  }, { passive: false });

  // Drag à la souris
  viewport.addEventListener('pointerdown', (e) => {
    isDragging = true;
    canvas.classList.add('dragging');
    dragStart.x = e.clientX;
    dragStart.y = e.clientY;
    panStart.x = pos.x;
    panStart.y = pos.y;
    canvas.setPointerCapture(e.pointerId);
  });

  viewport.addEventListener('pointermove', (e) => {
    if(!isDragging) return;
    const dx = e.clientX - dragStart.x;
    const dy = e.clientY - dragStart.y;
    pos.x = panStart.x + dx;
    pos.y = panStart.y + dy;
    applyTransform();
  });

  viewport.addEventListener('pointerup', (e) => {
    isDragging = false;
    canvas.classList.remove('dragging');
    canvas.releasePointerCapture?.(e.pointerId);
  });

  // Double-clic pour zoomer/dézoomer rapide
  viewport.addEventListener('dblclick', (e) => {
    const rect = viewport.getBoundingClientRect();
    const pointer = { x: e.clientX - rect.left, y: e.clientY - rect.top };

    const prevScale = scale;
    const target = e.shiftKey ? Math.max(minScale, scale * 0.8) : Math.min(maxScale, scale * 1.25);
    scale = target;

    const sx = (pointer.x - pos.x) / prevScale;
    const sy = (pointer.y - pos.y) / prevScale;
    pos.x = pointer.x - sx * scale;
    pos.y = pointer.y - sy * scale;

    applyTransform();
  });

  // Pinch-to-zoom (mobile)
  let pinch = { active:false, d0:0, scale0:1, cx:0, cy:0, pos0:{x:0,y:0} };

  function distance(t1, t2){
    const dx = t1.clientX - t2.clientX, dy = t1.clientY - t2.clientY;
    return Math.hypot(dx, dy);
  }
  function center(t1, t2){
    return { x:(t1.clientX + t2.clientX)/2, y:(t1.clientY + t2.clientY)/2 };
  }

  viewport.addEventListener('touchstart', (e) => {
    if(e.touches.length === 2){
      pinch.active = true;
      pinch.d0 = distance(e.touches[0], e.touches[1]);
      pinch.scale0 = scale;
      const rect = viewport.getBoundingClientRect();
      const c = center(e.touches[0], e.touches[1]);
      pinch.cx = c.x - rect.left;
      pinch.cy = c.y - rect.top;
      pinch.pos0 = { x: pos.x, y: pos.y };
    }
  }, { passive: false });

  viewport.addEventListener('touchmove', (e) => {
    if(pinch.active && e.touches.length === 2){
      e.preventDefault();
      const d1 = distance(e.touches[0], e.touches[1]);
      const prevScale = scale;
      scale = Math.max(minScale, Math.min(maxScale, pinch.scale0 * (d1 / pinch.d0)));

      const sx = (pinch.cx - pinch.pos0.x) / prevScale;
      const sy = (pinch.cy - pinch.pos0.y) / prevScale;
      pos.x = pinch.cx - sx * scale;
      pos.y = pinch.cy - sy * scale;

      applyTransform();
    }
  }, { passive: false });

  viewport.addEventListener('touchend', () => {
    if(pinch.active && (event.touches?.length ?? 0) < 2){
      pinch.active = false;
    }
  });

  // Boutons utilitaires (optionnels)
  window.fitToView = function(){
    // Essaie de centrer et de mettre une échelle de départ raisonnable
    const rect = viewport.getBoundingClientRect();
    // Estimation d’une taille logique (ajuste si tu connais la taille réelle)
    const contentW = canvas.scrollWidth || 1200;
    const contentH = canvas.scrollHeight || 800;
    const s = Math.min(rect.width / (contentW + 80), rect.height / (contentH + 80));
    scale = Math.max(minScale, Math.min(maxScale, s));
    pos.x = (rect.width  - contentW * scale) / 2;
    pos.y = (rect.height - contentH * scale) / 2;
    applyTransform();
  };

  // Appel initial pour cadrer
  window.addEventListener('load', () => setTimeout(window.fitToView, 0));
})();
</script>



</body>
</html>
