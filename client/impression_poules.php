<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Liste des équipes – Impression</title>
<style>
    /* STYLE GLOBAL APPLE MODERNE */
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        background: #f5f5f7;
        color: #1d1d1f;
        margin: 0;
        padding: 40px;
    }

    h1 {
        text-align: center;
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 50px;
        color: #000;
        letter-spacing: -0.02em;
    }

    h2 {
        font-size: 24px;
        font-weight: 600;
        margin-top: 40px;
        margin-bottom: 10px;
        color: #1d1d1f;
        letter-spacing: -0.01em;
    }

    h3 {
        font-size: 17px;
        font-weight: 500;
        opacity: 0.65;
        margin-top: 20px;
        margin-bottom: 8px;
    }

    .section {
        background: #ffffff;
        padding: 25px 30px;
        margin-bottom: 30px;
        border-radius: 22px;
        box-shadow: 0 5px 18px rgba(0,0,0,0.07);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    thead th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        opacity: 0.55;
        padding-bottom: 8px;
        border-bottom: 2px solid #e5e5e7;
    }

    td {
        padding: 12px 4px;
        font-size: 15px;
        border-bottom: 1px solid #efefef;
    }

    tr:last-child td {
        border-bottom: none;
    }

    .forfait {
        text-decoration: line-through;
        opacity: 0.35;
    }

    /* VERSION IMPRIMABLE */
    @media print {
        body {
            padding: 0;
            background: #ffffff;
        }
        .section {
            border-radius: 0;
            box-shadow: none;
            page-break-inside: avoid;
        }
        h1 {
            margin-top: 0;
        }
    }
</style>
</head>
<body>
    <h1>Liste des équipes inscrites</h1>

    <?php
include("../logiciel/assets/extract_parametre.php");
$series = explode(",", $parametres['series']);
$lang_code = isset($_GET['lang']) ? $_GET['lang'] : 'fr';
if ($lang_code == 'eus') { include("./assets/lang/lang_eus.php"); } else { include("./assets/lang/lang_fr.php"); }
?>
<?php
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    foreach ($series as $serie) {
        echo '<div class="section">';
        echo '<h2>' . htmlspecialchars($serie) . '</h2>';
        $stmt_poules = $conn->prepare("SELECT DISTINCT poule FROM inscriptions WHERE serie = :serie ORDER BY poule");
        $stmt_poules->bindParam(':serie', $serie);
        $stmt_poules->execute();
        $poules = $stmt_poules->fetchAll(PDO::FETCH_COLUMN);
        foreach ($poules as $poule) {
            echo '<h3>' . $lang['equipes_poules'] . ' ' . htmlspecialchars($poule) . '</h3>';
            $stmt = $conn->prepare("SELECT * FROM inscriptions WHERE serie = :serie AND poule = :poule");
            $stmt->bindParam(':serie', $serie);
            $stmt->bindParam(':poule', $poule);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                echo '<table><thead><tr><th>#</th><th>' . $lang['equipes_joueurs'] . '</th><th>' . $lang['equipes_telephone'] . '</th></tr></thead><tbody>';
                foreach ($rows as $insc) {
                    $cls = $insc['forfait'] == 1 ? 'forfait' : '';
                    echo '<tr>';
                    echo '<td class="' . $cls . '">' . htmlspecialchars($insc['id']) . '</td>';
                    echo '<td class="' . $cls . '">' . htmlspecialchars($insc['Joueur 1']) . ' & ' . htmlspecialchars($insc['Joueur 2']) . '</td>';
                    echo '<td class="' . $cls . '">' . htmlspecialchars($insc['telephone']) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            }
        }
        echo '</div>';
    }
} catch (PDOException $e) { echo 'Erreur : ' . $e->getMessage(); }
?>

</body>
</html>
