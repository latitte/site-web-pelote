<?php
include '../logiciel/assets/conn_bdd.php';
include("../logiciel/assets/extract_parametre.php");


// Détection de la langue à partir de l'URL, sinon défaut en français
$lang_code = isset($_GET['lang']) ? $_GET['lang'] : 'fr';

// Chargement du fichier de langue approprié
if ($lang_code == 'eus') {
    include("./assets/lang/lang_eus.php");
} else {
    include("./assets/lang/lang_fr.php");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>


<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lang['title']); ?></title>
    <link rel="stylesheet" href="./assets/style.css">


    
    <meta charset="UTF-8">
    <title>Détails de la Partie</title>
    <style>
        body {
            font-family: "Segoe UI", Roboto, sans-serif;
            background: #eef1f5;
            margin: 0;
            padding: 30px 15px;
            color: #1f1f1f;
        }
        .container {
            max-width: 860px;
            background: white;
            margin: auto;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }
        h2 {
            color: #111;
            font-size: 1.6rem;
            margin-bottom: 1rem;
        }
        .info-line {
            margin: 10px 0;
            font-size: 1.1rem;
        }
        .label {
            font-weight: 600;
            color: #555;
        }
        .value {
            font-weight: 600;
            color: #007aff;
        }
        .score {
            color: #28a745;
            font-size: 1.3rem;
            font-weight: bold;
            margin: 10px 0;
        }
        .error {
            color: #ff3b30;
            text-align: center;
            font-weight: 600;
        }
        .card {
            background: #f9fafc;
            padding: 20px;
            border-radius: 15px;
            margin: 15px 0;
            transition: 0.2s;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .card:hover {
            transform: scale(1.01);
        }
        .team-link {
            text-decoration: none;
            color: inherit;
        }
        .slot {
            background: #e8f0fe;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 10px;
            display: inline-block;
            font-weight: 500;
            color: #1a73e8;
        }
        .slot-group {
            margin-bottom: 20px;
        }
        .section-title {
            margin-top: 30px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 6px;
            font-size: 1.4rem;
            color: #444;
        }
    </style>
</head>
<body>

    <div class="popup">

        <div class="header">
            <h1><?php echo htmlspecialchars($lang['tournament']); ?></h1>
        </div>
        <div class="menu">
        <?php include("./assets/menu.php"); ?>
        </div>



<div class="container">
<?php
$partie = $_GET['partie'] ?? null;
if (!$partie) {
    echo "<p class='error'>⛔ Paramètre 'partie' manquant.</p></div></body></html>";
    exit;
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "<p class='error'>❌ Connexion échouée : " . $conn->connect_error . "</p></div></body></html>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM calendrier WHERE id = ?");
$stmt->bind_param("s", $partie);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $partie_data = $result->fetch_assoc();
    $date = DateTime::createFromFormat('Y-m-d', $partie_data['jours'])->format('d/m/Y');
    echo "<h2>📋 Informations sur la partie</h2>";
    echo "<p class='info-line'><span class='label'>Jour </span> <span class='value'>$date</span></p>";
    echo "<p class='info-line'><span class='label'>Heure </span> <span class='value'>" . htmlspecialchars($partie_data['heure']) . "</span></p>";
    echo "<p class='info-line'><span class='label'>Partie </span> <span class='value'>" . htmlspecialchars($partie_data['partie']) . "</span></p>";


    $niveauMap = [
        '1' => 'Qualification',
        '2' => 'Barrage',
        '3' => '1/8 de finale',
        '4' => '1/4 de finale',
        '5' => '1/2 finale',
        '6' => 'Finale'
    ];

    $niveauCode = (string)$partie_data['niveau'];
    $premierChiffre = substr($niveauCode, 0, 1);
    $niveauTexte = $niveauMap[$premierChiffre] ?? 'Niveau inconnu';

    echo "<p class='info-line'><span class='label'>Niveau </span> <span class='value'>" . htmlspecialchars($niveauTexte) . "</span></p>";


    if (!empty($partie_data['score'])) {
        echo "<p class='score'>✅ Score : " . htmlspecialchars($partie_data['score']) . "</p>";
    }

    list($equipe1_id, $equipe2_id) = explode('/', $partie_data['partie']);
    $stmt2 = $conn->prepare("SELECT * FROM inscriptions WHERE id = ? OR id = ?");
    $stmt2->bind_param("ii", $equipe1_id, $equipe2_id);
    $stmt2->execute();
    $result_teams = $stmt2->get_result();

    echo "<h2 class='section-title'>👥 Équipes concernées</h2>";
    while ($equipe = $result_teams->fetch_assoc()) {
        echo "<div class='card'><a class='team-link' href='./details_equipe.php?id=" . $equipe['id'] . "' target='_top'>";
        echo "<p><strong>Équipe n°</strong> " . htmlspecialchars($equipe['id']) . "</p>";
        echo "<p><strong>Joueur 1 :</strong> " . htmlspecialchars($equipe['Joueur 1']) . "</p>";
        echo "<p><strong>Joueur 2 :</strong> " . htmlspecialchars($equipe['Joueur 2']) . "</p>";
        echo "<p><strong>Téléphone :</strong> " . htmlspecialchars($equipe['telephone']) . "</p>";
        echo "</a></div>";
    }

    // API dispo
    $host = $_SERVER['HTTP_HOST'];
    $api_url = ($host == "127.0.0.1") 
        ? "$host/pelote/client/api_dispo_equipe.php"
        : $parametres['url_redirect'] . "/client/api_dispo_equipe.php";
    $request_url = $api_url . "?team1_id=$equipe1_id&team2_id=$equipe2_id";

    $ch = curl_init($request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = ($http_code === 200) ? json_decode($response, true) : [];

    if (!empty($data)) {
        $slots_by_day = [];
        foreach ($data as $slots) {
            foreach ($slots as $slot) {
                [$date, $hour] = explode(' ', $slot);
                $slots_by_day[$date][] = $hour;
            }
        }
        ksort($slots_by_day);

        echo "<h2 class='section-title'>🕒 Créneaux disponibles (Beta)</h2>";
        foreach ($slots_by_day as $date => $hours) {
            $date_obj = DateTime::createFromFormat('Y-m-d', $date);
            $jours = ['Monday'=>'Lundi','Tuesday'=>'Mardi','Wednesday'=>'Mercredi','Thursday'=>'Jeudi','Friday'=>'Vendredi','Saturday'=>'Samedi','Sunday'=>'Dimanche'];
            $mois = [1=>'janvier',2=>'février',3=>'mars',4=>'avril',5=>'mai',6=>'juin',7=>'juillet',8=>'août',9=>'septembre',10=>'octobre',11=>'novembre',12=>'décembre'];
            $jour = $jours[$date_obj->format('l')];
            $mois_txt = $mois[$date_obj->format('n')];
            $date_label = "$jour " . $date_obj->format('j') . " $mois_txt " . $date_obj->format('Y');

            echo "<div class='slot-group'><strong>$date_label :</strong><br>";
            foreach ($hours as $hour) {
                echo "<span class='slot'>" . htmlspecialchars($hour) . "</span> ";
            }
            echo "</div>";
        }
    } else {
        echo "<p class='error'>❗ Aucune disponibilité trouvée.</p>";
    }

} else {
    echo "<p class='error'>❌ Partie introuvable avec ID : $partie</p>";
}

$stmt->close();
if (isset($stmt2)) $stmt2->close();
$conn->close();
?>
</div>
</body>
</html>
