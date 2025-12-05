
<?php
$lang_code = $_GET['lang'] ?? 'fr';
include($lang_code == 'eus' ? "./assets/lang/lang_eus.php" : "./assets/lang/lang_fr.php");
include("../logiciel/assets/extract_parametre.php");

$lieu = $parametres['lieu'] ?? 'Ilharre';

// Géocodage
$lat = 43.4; $lon = -1.0;
try {
    $geoUrl = "https://nominatim.openstreetmap.org/search?" . http_build_query([
        'q' => $lieu,
        'format' => 'json',
        'limit' => 1
    ]);
    $opts = ['http' => ['header' => "User-Agent: tournoi-pelote"]];
    $context = stream_context_create($opts);
    $geoResp = file_get_contents($geoUrl, false, $context);
    $geoData = json_decode($geoResp, true);
    if (!empty($geoData[0])) {
        $lat = $geoData[0]['lat'];
        $lon = $geoData[0]['lon'];
    }
} catch (Exception $e) {}

$weather = null;
try {
    $query = http_build_query([
        'latitude' => $lat,
        'longitude' => $lon,
        'hourly' => 'temperature_2m,weathercode',
        'timezone' => 'Europe/Paris'
    ]);
    $response = file_get_contents("https://api.open-meteo.com/v1/forecast?$query");
    $data = json_decode($response, true);
    foreach ($data['hourly']['time'] as $i => $hour) {
        if ((int)date('H', strtotime($hour)) >= 20) {
            $weather = [
                'temp' => $data['hourly']['temperature_2m'][$i],
                'code' => $data['hourly']['weathercode'][$i],
                'hour' => $hour
            ];
            break;
        }
    }
} catch (Exception $e) {}

include("../logiciel/assets/conn_bdd.php");
$conn = new mysqli($servername, $username, $password, $dbname);
$today = date('Y-m-d');
$now = new DateTime();

$sql = "SELECT * FROM calendrier WHERE jours = ? ORDER BY heure ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

$parties = [];
while ($row = $result->fetch_assoc()) {
    $heurePartie = DateTime::createFromFormat('H\hi', $row['heure']);
$row['heure_format'] = $heurePartie ? $heurePartie->format('H:i') : null;

    $parties[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="<?= $lang_code ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $lang['title'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="stylesheet" href="./assets/style.css">
    <link rel="manifest" href="./assets/manifest.json">
    <link rel="icon" type="image/x-icon" href="./assets/tournoi-pelote.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="./assets/app.js" defer></script>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }
        .popup {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .content {
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
        }
        .carousel-item {
            height: 100%;
            overflow-y: auto;
            padding: 10px 15px;
        }
        .card {
            background-color: white;
            border-radius: 24px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            padding: 20px;
            margin: 10px auto;
            width: 90%;
            max-width: 500px;
            text-align: center;
        }
        .card.live {
            border: 2px solid #ff3b30;
            background-color: #fff5f5;
        }
        iframe {
            width: 100%;
            height: calc(100vh - 140px);
            border: none;
        }
        .time {
            font-size: 1em;
            color: #666;
        }
        .teams {
            font-size: 1.4em;
            font-weight: bold;
            margin: 10px 0;
        }
        .score {
            font-size: 2em;
            font-weight: 700;
        }
        .status {
            margin-top: 10px;
            font-size: 0.95em;
            color: #007aff;
        }
        /* Boutons discrets */
        .carousel-control-prev,
        .carousel-control-next {
            width: 40px;
            height: 40px;
            top: 10px;
            bottom: auto;
            background: none;
            opacity: 0.2;
            transition: opacity 0.2s ease;
        }
        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            opacity: 0.6;
        }
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            filter: invert(0.6);
            width: 30px;
            height: 30px;
        }
    </style>
</head>
<body>
<div class="popup">
    <div class="header">
        <h1 style="text-align:center;"><?= $lang['tournament'] ?></h1>
    </div>
    <div class="menu">
        <?php include("./assets/menu.php"); ?>
    </div>
    <div class="content">






        <div id="carouselTournoi" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="900000">
            <div class="carousel-inner h-100">

                <!-- Slide 1 : météo + parties -->
                <div class="carousel-item active">

<!-- ======== Encadré pub Titoan Lalanne ======== -->
<link rel="stylesheet" href="https://titoanlalanne.fr/pub/style.css">
<div class="card pub-card">
  <h3>💼 Développement web & solutions numériques locales</h3>
  <p>
Création de sites vitrines et boutiques en ligne, conception d’identité visuelle, installation réseau et automatisation de tâches.
Accompagnement des particuliers, associations et entreprises dans leur transition numérique avec des solutions modernes, personnalisées et accessibles.
  </p>
  <a href="https://latitte.titoanlalanne.fr" target="_blank" class="btn-pub">
    Découvrir mes services →
  </a>
</div>


                    <?php if ($weather): ?>
                        <div class="card">
                            <div class="time">🌤 Météo à <?= htmlspecialchars($lieu) ?> vers 20h</div>
                            <div class="teams"><?= round($weather['temp'], 1) ?>°C</div>
                            <div class="status">
                                <?php
                                $code = $weather['code'];
                                switch ($code) {
                                    case 0:
                                        echo "☀️ Ciel clair";
                                        break;
                                    case 1:
                                    case 2:
                                        echo "🌤 Peu nuageux";
                                        break;
                                    case 3:
                                        echo "☁️ Couvert";
                                        break;
                                    case 45:
                                    case 48:
                                        echo "🌫 Brouillard";
                                        break;
                                    case 51:
                                    case 53:
                                    case 55:
                                        echo "🌦 Bruine";
                                        break;
                                    case 61:
                                    case 63:
                                    case 65:
                                        echo "🌧 Pluie";
                                        break;
                                    case 66:
                                    case 67:
                                        echo "🌧 Verglas";
                                        break;
                                    case 71:
                                    case 73:
                                    case 75:
                                        echo "❄️ Neige";
                                        break;
                                    case 95:
                                        echo "⛈ Orage";
                                        break;
                                    default:
                                        echo "🌥 Temps variable";
                                }

                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

<?php if (!empty($parties)): ?>
    <h2>📋 Programme de la soirée</h2>
    <?php
    // Assure-toi que $now est bien un DateTime
    if (!isset($now) || !($now instanceof DateTime)) {
        $now = new DateTime('now', new DateTimeZone('Europe/Paris'));
    }

    foreach ($parties as $partie):
        $scoreRenseigne = isset($partie['score']) && $partie['score'] !== null && $partie['score'] !== '';

        // Parse heure (accepte "HH:MM" ou "HH:MM:SS")
        $heureStr = isset($partie['heure']) ? trim((string)$partie['heure']) : '';
        $matchTime = null;

        if ($heureStr !== '') {
            $dt = DateTime::createFromFormat('H:i', $heureStr);
            if ($dt === false) {
                $dt = DateTime::createFromFormat('H:i:s', $heureStr);
            }
            if ($dt !== false) {
                // Caler la date du match sur la date actuelle
                $matchTime = (clone $now);
                [$h, $m, $s] = array_map('intval', array_pad(explode(':', $heureStr), 3, 0));
                $matchTime->setTime($h, $m, $s);
            }
        }

        // Calcul des états
        $isPlayed  = $scoreRenseigne;
        $isLive    = false;
        $isUpcoming = false;

        if (!$isPlayed && $matchTime instanceof DateTime) {
            $minutesSinceStart = floor(($now->getTimestamp() - $matchTime->getTimestamp()) / 60);
            $isLive    = ($minutesSinceStart >= 0 && $minutesSinceStart <= 45); // en direct pendant 45 min
            $isUpcoming = ($minutesSinceStart < 0);
        }
    ?>


<a href="./details_partie.php?partie=<?php echo $partie['id']; ?>" style="text-decoration: none; color: inherit;">
        <div class="card <?= $isLive ? 'live' : '' ?>">
            <div class="time">



            <?php
                $niveauMap = [
                    '1' => 'Qualification',
                    '2' => 'Barrage',
                    '3' => '1/8 de finale',
                    '4' => '1/4 de finale',
                    '5' => '1/2 finale',
                    '6' => 'Finale'
                ];

                $niveauCode = (string)$partie['niveau'];
                $premierChiffre = substr($niveauCode, 0, 1);
                $niveauTexte = $niveauMap[$premierChiffre] ?? 'Niveau inconnu';

            ?>





                <?= htmlspecialchars($partie['heure'] ?? '—:—') ?> - Niveau <? echo $niveauTexte; ?>
            </div>
            <div class="teams">Partie <?= htmlspecialchars($partie['partie'] ?? '-') ?></div>

            <?php if ($isPlayed): ?>
                <div class="score"><?= htmlspecialchars($partie['score']) ?></div>
                <div class="status">✅ Terminée</div>

            <?php elseif ($isLive): ?>
                <div class="score">
                    <?= (int)($partie['live_score_A'] ?? 0) ?> : <?= (int)($partie['live_score_B'] ?? 0) ?>
                </div>
                <div class="status">🔴 En direct</div>

            <?php elseif ($isUpcoming): ?>
                <div class="status">🕒 À venir</div>

            <?php else: ?>
                <!-- Heure manquante/invalide ou match hors fenêtre live sans score -->
                <div class="status">🕒 À venir</div>
            <?php endif; ?>
        </div>
</a>
    <?php endforeach; ?>
<?php endif; ?>


<!-- ======== Itinéraire vers le fronton ======== -->
<div id="itineraire-card" style="
  background:#fff; border:1px solid #e5e5ea; border-radius:16px; padding:16px; 
  margin:20px auto; max-width:600px; font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
  <h3 style="margin:0 0 12px 0; font-size:20px;color:black;">🧭 Itinéraire jusqu'au fronton</h3>

  <div id="itineraire-status" style="color:#666; font-size:14px;">Détection de ta position…</div>

  <div id="itineraire-results" style="display:none; margin-top:12px;">
    <div style="font-size:16px; margin-bottom:8px;color:black;">
      <strong>Distance :</strong> <span id="it-distance">—</span>
    </div>
    <div style="font-size:16px; margin-bottom:8px;color:black;">
      <strong>Durée estimée :</strong> <span id="it-duree">—</span>
    </div>
    <div style="font-size:16px;color:black;">
      <strong>Heure d’arrivée :</strong> <span id="it-arrivee">—</span>
    </div>
  </div>

  <div style="display:flex; gap:8px; margin-top:14px; flex-wrap:wrap;">
    <a id="btn-waze" href="#" target="_blank" rel="noopener" class="btn-link">Ouvrir dans Waze</a>
    <a id="btn-gmaps" href="#" target="_blank" rel="noopener" class="btn-link">Ouvrir dans Google Maps</a>
  </div>
</div>

<style>
  .btn-link{
    background:#1b73e8; color:#fff; text-decoration:none; padding:10px 14px; 
    border-radius:10px; font-weight:600; display:inline-block;
  }
  .btn-link:hover{ opacity:.9; }
</style>
                </div>

                <!-- Slide 2 : règles -->
                <div class="carousel-item">
                    <iframe src="./doc.php"></iframe>
                </div>
            </div>

            <!-- Contrôles discrets -->
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselTournoi" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"><</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselTournoi" data-bs-slide="next">
                <span class="carousel-control-next-icon">></span>
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<script>
(function(){
  // Coordonnées de destination depuis PHP
  const DEST_LAT = parseFloat(<?= json_encode($parametres['lieu_lat'] ?? '') ?>);
  const DEST_LON = parseFloat(<?= json_encode($parametres['lieu_lon'] ?? '') ?>);

  const statusEl  = document.getElementById('itineraire-status');
  const resultsEl = document.getElementById('itineraire-results');
  const distEl    = document.getElementById('it-distance');
  const dureeEl   = document.getElementById('it-duree');
  const arrEl     = document.getElementById('it-arrivee');
  const wazeBtn   = document.getElementById('btn-waze');
  const gmapsBtn  = document.getElementById('btn-gmaps');

  if (Number.isNaN(DEST_LAT) || Number.isNaN(DEST_LON)) {
    statusEl.textContent = "Coordonnées du fronton manquantes.";
    wazeBtn.style.display = 'none';
    gmapsBtn.style.display = 'none';
    return;
  }

  // Utilitaires
  function formatDistance(m){
    if (m < 1000) return `${Math.round(m)} m`;
    return `${(m/1000).toFixed(1)} km`;
  }
  function formatDuration(s){
    const h = Math.floor(s / 3600);
    const m = Math.round((s % 3600) / 60);
    if (h <= 0) return `${m} min`;
    return `${h} h ${m.toString().padStart(2,'0')} min`;
  }
  function arrivalTimeFromNow(seconds){
    const now = new Date();
    const arr = new Date(now.getTime() + seconds*1000);
    // Europe/Paris
    return arr.toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit', hour12:false, timeZone: 'Europe/Paris' });
  }
  function buildLinks(origin){
    // Waze (web) + schéma app
    const wazeWeb = `https://www.waze.com/ul?ll=${DEST_LAT}%2C${DEST_LON}&navigate=yes`;
    const wazeApp = `waze://?ll=${DEST_LAT}%2C${DEST_LON}&navigate=yes`;
    wazeBtn.href = wazeWeb;
    wazeBtn.onclick = function(e){
      // Essai ouverture app puis fallback web
      window.location.href = wazeApp;
      setTimeout(()=>window.open(wazeWeb,'_blank'), 250);
    };

    // Google Maps
    let gmaps = `https://www.google.com/maps/dir/?api=1&destination=${DEST_LAT},${DEST_LON}&travelmode=driving`;
    if (origin) gmaps += `&origin=${origin.lat},${origin.lon}`;
    gmapsBtn.href = gmaps;
  }

  // Détection position
  if (!('geolocation' in navigator)) {
    statusEl.textContent = "Géolocalisation non disponible sur cet appareil.";
    buildLinks(null);
    return;
  }

  navigator.geolocation.getCurrentPosition(async (pos)=>{
    const orig = {
      lat: pos.coords.latitude,
      lon: pos.coords.longitude
    };
    statusEl.textContent = "Calcul de l'itinéraire…";

    // OSRM (gratuit). Format: lon,lat;lon,lat
    const url = `https://router.project-osrm.org/route/v1/driving/${orig.lon},${orig.lat};${DEST_LON},${DEST_LAT}?overview=false`;
    try{
      const r = await fetch(url);
      const j = await r.json();
      if(!j || !j.routes || !j.routes[0]){
        throw new Error('Aucun itinéraire');
      }
      const route = j.routes[0];
      const dist = route.distance;   // mètres
      const dur  = route.duration;   // secondes

      distEl.textContent  = formatDistance(dist);
      dureeEl.textContent = formatDuration(dur);
      arrEl.textContent   = arrivalTimeFromNow(dur);
      resultsEl.style.display = 'block';
      statusEl.textContent = "Itinéraire prêt.";

      buildLinks(orig);
    }catch(e){
      statusEl.textContent = "Impossible de calculer l'itinéraire (service indisponible).";
      // On fournit quand même les liens de navigation sans origine => l'app utilisera la position actuelle.
      buildLinks(null);
    }
  }, (err)=>{
    statusEl.textContent = "Position non autorisée. Tu peux ouvrir Waze/Maps directement.";
    buildLinks(null);
  }, { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 });
})();
</script>
</body>
</html>
