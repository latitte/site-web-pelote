
<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['id'])) {
    $team_id = $_SESSION['id'];
    // echo "Utilisateur connecté, ID : " . htmlspecialchars($team_id);
} else {
    echo "Aucune session active. Utilisateur non connecté.";
}
?>



<?php


include("../logiciel/assets/extract_parametre.php");

// Table de correspondance des jours abrégés vers les jours complets
$jours_dispo_bdd = $parametres['jours_dispo'];
$jours_disponibles = explode(", ", $jours_dispo_bdd);

$heures_dispo_bdd = $parametres['heures_dispo'];
$heures_dispo = explode(", ", $heures_dispo_bdd);

$jours_complets = [
    'Lun' => 'lundi',
    'Mar' => 'mardi',
    'Mer' => 'mercredi',
    'Jeu' => 'jeudi',
    'Ven' => 'vendredi',
    'Sam' => 'samedi',
    'Dim' => 'dimanche'
];

$jours_complets_list = array_map(function($jour) use ($jours_complets) {
    return $jours_complets[$jour] ?? 'Inconnu'; // Utiliser "Inconnu" si la clé n'existe pas
}, $jours_disponibles);


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
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['title']; ?></title>
    <link rel="stylesheet" href="./assets/style.css">
    <link rel="manifest" href="./assets/manifest.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="icon" type="image/x-icon" href="./assets/tournoi-pelote.ico">
    <script src="./assets/app.js" defer></script>

</head>
<body>
    <div class="popup">
        <div class="header">
            <h1 style="text-align:center;"><?php echo $lang['tournament']; ?></h1>
        </div>
        <div class="menu">
        <?php include("./assets/menu.php"); ?>
        </div>

        



<?php



// ------------------------------------ PARTIE DE L'EQUIPE ---------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------


?>

 
<div class="popup">

        <div class="content">
            <div class="container">
                <div class="team-card">











<?php
// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($team_id)) {
        // === TRAITEMENT DE L'UPLOAD DE LA PHOTO ===
        if (
            isset($_POST['upload_photo']) &&
            isset($_FILES['photo']) &&
            $_FILES['photo']['error'] === UPLOAD_ERR_OK
        ) {
            $uploadDir = './uploads/';
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png'];
            $maxSize = 2 * 1024 * 1024;

            if (in_array(strtolower($ext), $allowed) && $_FILES['photo']['size'] <= $maxSize) {
                $safeName = 'photo_team_' . $team_id . '.' . $ext;
                $uploadPath = $uploadDir . $safeName;

                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
                    $sql = "UPDATE inscriptions SET photo_profil = :photo WHERE id = :id";
                    $stmt = $conn->prepare($sql);

                    if (!$stmt) {
                        die("❌ Erreur préparation requête : " . implode(" | ", $conn->errorInfo()));
                    }

                    $stmt->bindValue(':photo', $safeName);
                    $stmt->bindValue(':id', $team_id);
                    $stmt->execute();
                    echo "<p style='color:green;'>✅ Photo mise à jour avec succès.</p>";
                } else {
                    echo "<p style='color:red;'>❌ Échec lors du déplacement du fichier.</p>";
                }
            } else {
                echo "<p style='color:red;'>❌ Fichier non valide (format JPG/PNG ou > 2MB).</p>";
            }
        }

        // === RÉCUPÉRATION DES INFOS ÉQUIPE ===
        $stmt = $conn->prepare("SELECT * FROM inscriptions WHERE id = :id");
        $stmt->bindValue(':id', $team_id, PDO::PARAM_INT);
        $stmt->execute();
        $equipe = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($equipe) {
            // === AFFICHAGE PHOTO DE PROFIL ===
            if (!empty($equipe['photo_profil'])) {
                echo '<img src="./uploads/' . htmlspecialchars($equipe['photo_profil']) . '" alt="Photo équipe" style="width:100px;border-radius:12px;margin-bottom:10px;"><br>';
            }

            // === FORMULAIRE UPLOAD ===
            echo '<form action="" method="post" enctype="multipart/form-data">
                    <h3 style="color:black;">Notre photo d\'équipe</h3>
                    <label style="color:black;" for="photo">Photo de profil (JPG/PNG, max 2MB):</label><br>
                    <input type="file" name="photo" id="photo" accept=".jpg,.jpeg,.png" required>
                    <button type="submit" name="upload_photo">Mettre à jour la photo</button>
                  </form><br>';
        }
    }
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
                <?php
                // Connexion à la base de données
                try {
                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    if (isset($team_id)) {
                        $id = $team_id;

                        // Préparer la requête pour récupérer les détails de l'équipe
                        $stmt = $conn->prepare("SELECT * FROM inscriptions WHERE id = :id");
                        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();

                        $equipe = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($equipe) {
                            echo '<div class="player-info-card">';

                            echo '<h2>' . htmlspecialchars($equipe['Joueur 1'] ?? 'Inconnu') . ' & ' . htmlspecialchars($equipe['Joueur 2'] ?? 'Inconnu') . '</h2>';

                            echo '<ul class="player-details">';
                            echo '<li><span>📞 Téléphone </span> ' . htmlspecialchars($equipe['telephone'] ?? 'Non disponible') . '</li>';
                            echo '<li><span>🎯 Série </span> ' . htmlspecialchars($equipe['serie'] ?? 'Non spécifiée') . '</li>';
                            echo '<li><span>📂 Poule </span> ' . htmlspecialchars($equipe['poule'] ?? 'Non spécifiée') . '</li>';
                            echo '</ul>';

          

                            echo '<h3 style="color:black;">Disponibilités</h3>';
                            
                            $jours = $jours_complets_list;
                            $horaires = $heures_dispo;
                            echo '<div class="carousel">';
                            echo '<div class="carousel-track" id="carouselTrack">';

                            foreach ($jours as $jour) {
                                echo '<div class="carousel-item">';
                            echo '<div class="card">';
                            echo '<h3>' . ucfirst($jour) . '</h3>';
                            $dispos = $equipe[$jour];
                            for ($i = 0; $i < strlen($dispos); $i++) {
                                $disponible = $dispos[$i] == '1' ? 'available' : 'unavailable';
                                $heure_label = $horaires[$i] ?? '';
                                echo '<div class="slot ' . $disponible . '">' . htmlspecialchars($heure_label) . '</div>';
                            }
                            echo '</div></div>';

                            }
                            echo '</div></div>';
                            echo '<div class="carousel-nav"><button onclick="moveCarousel(-1)">←</button><button onclick="moveCarousel(1)">→</button></div>';
                  echo '</div>';

                            if($equipe['paye'] == "1"){
                                echo '<p style="text-align:left; text-align:center; color:green;"><strong>Votre équipe à payé</strong></p>';
                            }else{
                                echo '<p style="text-align:left; text-align:center; color:red;"><strong>Votre équipe n\'a pas payé</strong></p>';
                            }
                            
echo '<h2 style="color:#1c1c1e; font-family:-apple-system, BlinkMacSystemFont, Roboto, sans-serif; font-weight:600; font-size:22px; text-align:center; margin-bottom:30px;">Vos messages</h2>';

// Récupérer les messages d'activité
$stmt2 = $conn->prepare("
    SELECT horodateur, type, equipeA, equipeB, detail 
    FROM activite 
    WHERE equipeA = :idA OR equipeB = :idB
");
$stmt2->bindValue(':idA', $id, PDO::PARAM_INT);
$stmt2->bindValue(':idB', $id, PDO::PARAM_INT);
$stmt2->execute();
$messages_activite = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les demandes de report
$stmt3 = $conn->prepare("
    SELECT horodatage AS horodateur, 'Demande de report' AS type, equipe_demande AS equipeA, equipe_adverse AS equipeB, 
           CONCAT('Demande de report pour le ', jour, ' à ', heure, ' - Statut : ', statut) AS detail
    FROM demandes_report
    WHERE equipe_demande = :id OR equipe_adverse = :id
");
$stmt3->bindValue(':id', $id, PDO::PARAM_INT);
$stmt3->execute();
$messages_reports = $stmt3->fetchAll(PDO::FETCH_ASSOC);

// Fusionner et trier les deux tableaux par date décroissante
$messages = array_merge($messages_activite, $messages_reports);
usort($messages, function ($a, $b) {
    return strtotime($b['horodateur']) - strtotime($a['horodateur']);
});

if ($messages) {
    echo '<div style="max-width: 600px; margin: 0 auto; font-family:-apple-system, BlinkMacSystemFont, Roboto, sans-serif;">';

    foreach ($messages as $msg) {
        echo '<div style="background:#fff; border-radius:18px; padding:16px 20px; margin-bottom:20px; box-shadow:0 2px 6px rgba(0,0,0,0.06); border:1px solid #e5e5ea;">';

        echo '<div style="font-size:13px; color:#8e8e93; margin-bottom:6px;">' 
            . date("d M Y à H:i", strtotime($msg['horodateur'])) . 
        '</div>';

        echo '<div style="font-size:16px; font-weight:600; color:#1c1c1e; margin-bottom:10px;">' 
            . htmlspecialchars($msg['type']) . 
        '</div>';

        echo '<div style="font-size:14px; color:#3c3c43; margin-bottom:8px;">';

        echo 'Équipe A : <strong>' . htmlspecialchars($msg['equipeA']);
        if ($msg['equipeA'] == $id) echo ' (vous)';
        echo '</strong>';

        if (!empty($msg['equipeB'])) {
            echo ' &nbsp;&nbsp;vs&nbsp;&nbsp; Équipe B : <strong>' . htmlspecialchars($msg['equipeB']);
            if ($msg['equipeB'] == $id) echo ' (vous)';
            echo '</strong>';
        }

        echo '</div>';

        echo '<div style="font-size:15px; color:#1c1c1e; line-height:1.5;">' 
            . nl2br(htmlspecialchars($msg['detail'])) . 
        '</div>';

        echo '</div>';
    }

    echo '</div>';
} else {
    echo '<p style="text-align:center; color:#8e8e93; font-family:-apple-system, BlinkMacSystemFont, Roboto, sans-serif; margin-top:40px;">Aucun message trouvé pour votre équipe.</p>';
}




                            // Rechercher les parties jouées par l'équipe
                            $teamNumber = htmlspecialchars($equipe['id']);
                            $stmt = $conn->prepare("SELECT * FROM calendrier WHERE partie LIKE :teamNumber1 OR partie LIKE :teamNumber2 ORDER BY jours ASC");
                            $teamNumber1 = $teamNumber . '/%';
                            $teamNumber2 = '%/' . $teamNumber;
                            $stmt->bindValue(':teamNumber1', $teamNumber1);
                            $stmt->bindValue(':teamNumber2', $teamNumber2);
                            $stmt->execute();
                            
                            $parties = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if ($parties) {
                                echo '<br><h2>Vos parties</h2>';
                                echo '<h4 style="color:black;">Cliquez sur la partie à modifier</h4>';
                                echo '<div class="parties-container">';

                                foreach ($parties as $partie) {
                                    $opponent = str_replace($teamNumber, '', $partie['partie']);
                                    $score = $partie['score'];
                                    $partie_pour_redirect = $partie['partie'];
                                    $id_partie = $partie['id'];
                                    $score = $score !== null ? htmlspecialchars($score) : 'Non terminé';

                                    if ($score === 'Non terminé') {
                                        $class = 'pending'; // Partie sans score
                                    } else {
                                        list($scoreEquipe1, $scoreEquipe2) = explode('/', $score);
                                        // Déterminer le résultat
                                        list($team1, $team2) = explode('/', $partie['partie']);
                                        $team1 = trim($team1);
                                        $team2 = trim($team2);

                                        if ($teamNumber == $team1) {
                                            $resultat = ($scoreEquipe1 > $scoreEquipe2) ? 'win' : 'loss';
                                        } elseif ($teamNumber == $team2) {
                                            $resultat = ($scoreEquipe2 > $scoreEquipe1) ? 'win' : 'loss';
                                        } else {
                                            $resultat = 'pending'; // Cas où l'équipe n'est pas dans la partie
                                        }

                                        $class = $resultat;
                                    }

                                    // echo '<div class="partie-card ' . $class . '"><a style="text-decoration: none;" href="./partie_replace.php?partie=' . $id_partie . '&equipe_id=' . $partie_pour_redirect . '">';
?>
                                <div class="partie-card <?= $class ?>" onclick="showPopup('<?= $id_partie ?>', '<?= $partie_pour_redirect ?>', '<?= $partie['jours'] ?>')">

    <!-- contenu de la carte -->



<?php
                                if ($partie['jours'] == "0000-00-00") {
                                    echo '<div><strong>📅</strong> <span style="color:#555;">Date en attente</span></div>';
                                } else {
                                    $date = date('d/m/Y', strtotime($partie['jours']));
                                    echo '<div><strong>📅</strong> ' . htmlspecialchars($date) . ' <strong>🕘</strong> ' . htmlspecialchars($partie['heure']) . '</div>';
                                }
                                


                                    $numero = $id; // Le numéro de l'équipe que tu veux mettre en gras

                                    // Remplacer dynamiquement le numéro d'équipe dans la partie
                                $partie_formattee = preg_replace('/\b' . preg_quote($id, '/') . '\b/', '<strong>' . $id . '</strong>', htmlspecialchars($partie['partie']));
                                echo '<div><strong>🆚</strong> ' . $partie_formattee . '</div>';

                                    echo '<div><strong>🏁</strong> ' . htmlspecialchars($score ?? 'Non spécifié') . '</div>';
                                    




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

                                        echo '<p><strong>Niveau</strong>: ' . htmlspecialchars($niveauTexte) . '</p>';



                                    echo '</div>';
                                }

                                echo '</a></div>';

?> 
</div>

<!-- Modale Apple-style -->
<div id="popup-modal" class="modal hidden">
    <div class="modal-content">
        <p class="modal-title">Que souhaitez-vous faire ?</p>
        <div class="modal-buttons">
            <button onclick="redirectTo('view')">Consulter</button>
            <button onclick="redirectTo('edit')">Modifier</button>
            <button onclick="redirectTo('propose')">Proposer un créneau aux adversaires</button>
            <button class="cancel" onclick="closePopup()">Annuler</button>
        </div>
    </div>
</div>

<style>
/* Apple-style modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    backdrop-filter: blur(10px);
    background: rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.modal.hidden {
    display: none;
}

.modal-content {
    background: white;
    border-radius: 20px;
    padding: 24px;
    width: 90%;
    max-width: 320px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
    text-align: center;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.modal-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
}

.modal-buttons button {
    display: block;
    width: 100%;
    padding: 12px;
    margin: 6px 0;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    background-color: #859fe3;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.modal-buttons button:hover {
    background-color: #e0e0e0;
}

.modal-buttons .cancel {
    background-color: #ff3b30;
    color: white;
}
</style>

<script>
let partieId = '';
let equipeId = '';

function showPopup(id, equipe, datePartie) {
    partieId = id;
    equipeId = equipe;

    const now = new Date();
    const heureActuelle = now.getHours();

    const dateDemain = new Date();
    dateDemain.setDate(now.getDate() + 1);
    const datePartieObj = new Date(datePartie);

    // Récupération de l'élément bouton Modifier
    const btns = document.querySelectorAll('.modal-buttons button');
    const btnModifier = btns[1];
    const btnProposer = btns[2];


    // Réinitialiser l'état du bouton Modifier
    btnModifier.style.display = 'block';
    btnProposer.style.display = 'block';

    // Trouver la carte correspondante
    const card = document.querySelector('.partie-card[onclick*="' + id + '"]');

    const scoreDivs = card.querySelectorAll('div');
    let scoreText = '';
    scoreDivs.forEach(div => {
        if (div.textContent.includes('🏁')) {
            scoreText = div.textContent;
        }
    });
    const scoreValue = scoreText.replace('🏁', '').trim();


    // Si la partie est demain après 18h ou si un score existe, cacher Modifier
    if (
        (datePartieObj.toDateString() === dateDemain.toDateString() && heureActuelle >= 18) ||
        (scoreValue !== 'Non terminé' && scoreValue !== 'Non spécifié')
    ) {
        btnModifier.style.display = 'none';
        btnProposer.style.display = 'none';
    }

    document.getElementById('popup-modal').classList.remove('hidden');
}


function closePopup() {
    document.getElementById('popup-modal').classList.add('hidden');
}

function redirectTo(action) {
    let url = './partie_replace.php?partie=' + partieId + '&equipe_id=' + equipeId;
    if (action === 'edit') {
        url += '&mode=edit';
    }
    if (action === 'propose') {
        if (!team_id) {
            alert("Erreur : vous devez être connecté.");
            return;
        }
        url = './cherche_creneau_a_propose.php?partie=' + partieId + '&equipe_id=' + equipeId;
    }
    if (action === 'view') {
        url = './details_partie.php?partie=' + partieId;
    }
    window.location.href = url;
}

</script>
<script>
const team_id = <?= json_encode($team_id ?? null) ?>;
</script>


<?php

                            } else {
                                echo '<p>Aucune partie trouvée pour cette équipe.</p>';
                            }
                        } else {
                            echo "<p>Aucune équipe trouvée avec cet ID.</p>";
                        }
                    } else {
                        echo "<p>Identifiant d'équipe manquant dans l'URL.</p>";
                    }
                } catch (PDOException $e) {
                    echo "Erreur: " . $e->getMessage();
                }

                $conn = null;



// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérification si l'ID de l'équipe est passé dans l'URL

        if (isset($team_id)) {
            $id = $team_id;

        // Préparer la requête pour récupérer l'historique des parties de l'équipe
        $stmt = $conn->prepare("SELECT * FROM calendrier WHERE partie LIKE :teamNumber1 OR partie LIKE :teamNumber2 ORDER BY jours ASC");
        $teamNumber1 = $id . '/%';
        $teamNumber2 = '%/' . $id;
        $stmt->bindValue(':teamNumber1', $teamNumber1);
        $stmt->bindValue(':teamNumber2', $teamNumber2);
        $stmt->execute();

        $parties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($parties && count(array_filter($parties, fn($partie) => $partie['score'] !== null && $partie['score'] !== ''))) {

            // echo '<h3>Résultats des Parties</h3>';
            echo '<div class="results-text">';

            // Initialisation des variables pour les calculs
            $victoires = 0;
            $defaites = 0;
            $goalAverageTotal = 0;
            $totalCoef = 0;
            $scores = [];

            foreach ($parties as $index => $partie) {
                $score = $partie['score'];

                // S'assurer que le score n'est pas nul ou vide
                if ($score !== null && $score !== '') {
                    list($scoreEquipe1, $scoreEquipe2) = explode('/', $score);
                    $team1 = trim(explode('/', $partie['partie'])[0]);
                    $team2 = trim(explode('/', $partie['partie'])[1]);

                    if ($id == $team1) {
                        $resultat = ($scoreEquipe1 > $scoreEquipe2) ? 'Gagné' : 'Perdu';
                        $goalDifference = $scoreEquipe1 - $scoreEquipe2;
                    } elseif ($id == $team2) {
                        $resultat = ($scoreEquipe2 > $scoreEquipe1) ? 'Gagné' : 'Perdu';
                        $goalDifference = $scoreEquipe2 - $scoreEquipe1;
                    } else {
                        $resultat = 'En attente';
                        $goalDifference = 0;
                    }

                    // Affichage du résultat de chaque partie sous forme de texte
                    $status = 'Résultat: ' . $resultat . ' | Score: ' . $score;

                    // Calculs pour le taux de victoires et goal average
                    if ($resultat === 'Gagné') {
                        $victoires++;
                    } else {
                        $defaites++;
                    }

                    // On garde la différence de buts et les coefficients pour les matchs récents
                    $coef = 1 / (pow(1.1, $index)); // Coefficient plus élevé pour les matchs récents
                    $goalAverageTotal += $goalDifference * $coef;
                    $totalCoef += $coef;
                    $scores[] = $goalDifference;

                    // echo '<p><strong>Partie:</strong> ' . htmlspecialchars($partie['partie']) . '</p>';
                    // echo '<p><strong>Date:</strong> ' . htmlspecialchars($partie['jours'] !== "0000-00-00" ? $partie['jours'] : 'Non définie') . '</p>';
                    // echo '<p><strong>Heure:</strong> ' . htmlspecialchars($partie['heure']) . '</p>';
                    // echo '<p><strong>Status:</strong> ' . $status . '</p>';
                    // echo '<hr>';
                }
            }

            // Calcul du taux de victoires
            $tauxVictoire = $victoires / count(array_filter($parties, fn($partie) => $partie['score'] !== null && $partie['score'] !== ''));

            // Calcul du goal average moyen
            $goalAverageMoyenne = $goalAverageTotal / $totalCoef;

            // Indice final combiné
            $indiceFinal = ($tauxVictoire * 0.7) + ($goalAverageMoyenne * 0.3);

            // Fonction pour déterminer l'emoji en fonction de l'indice
            function afficherEmoji($indice) {
                if ($indice >= 8) {
                    return "😁🎉"; // Très heureux
                } elseif ($indice >= 6) {
                    return "😃👌"; // Très content
                } elseif ($indice >= 4) {
                    return "🙂😊"; // Content
                } elseif ($indice >= 2) {
                    return "😐"; // Neutre
                } elseif ($indice >= 0) {
                    return "😒"; // Mécontent
                } elseif ($indice >= -3) {
                    return "☹️"; // Triste
                } elseif ($indice >= -5) {
                    return "😞💔"; // Très triste
                } else {
                    return "😭"; // Très très triste
                }
            }

            // Affichage de l'indicateur de forme avec l'emoji
            $emoji = afficherEmoji($indiceFinal);
            echo '<h4>Indice de Forme Actuelle</h4>';
            // echo '<p>Taux de victoires : ' . number_format($tauxVictoire * 100, 2) . '%</p>';
            // echo '<p>Goal Average moyen : ' . number_format($goalAverageMoyenne, 2) . '</p>';
            // echo '<p>Indice de forme : ' . number_format($indiceFinal, 2) . '</p>';
            echo '<p style="font-size: 60px;" >' . $emoji . '</p>';

            ?> <progress min="0" max="20" value="<?php echo $indiceFinal + 10; ?>"></progress></li> <?php
            echo '</div>';
        } else {
            // echo '<p>Aucune partie trouvée pour cette équipe.</p>';
        }
    } else {
        echo "<p>Identifiant d'équipe manquant dans l'URL.</p>";
    }
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}

$conn = null;
?>

</div>
            </div>





        </div>
    </div>
                
<?php
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------
// -----------------------------------------------------------------------------------------








?>





    </div>



</body>



</html>


<style>
    .partie-card {
    border-radius: 16px;
    padding: 20px;
    width: 280px;
    background-color: white;
    text-align: left;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: #333;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 8px;
}

.partie-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.1);
}

.partie-card.pending {
    background-color: #f8f8f8;
}

.partie-card.win {
    background-color: #e6f9ed;
    border-left: 6px solid #34c759;
}

.partie-card.loss {
    background-color: #ffeef0;
    border-left: 6px solid #ff3b30;
}
.player-info-card {
    background-color: #fff;
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
    padding: 24px;
    margin: 20px auto;
    max-width: 500px;
    text-align: center;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: #333;
}

.player-info-card h2 {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #222;
}

.player-details {
    list-style: none;
    padding: 0;
    margin: 0;
    text-align: left;
}

.player-details li {
    font-size: 16px;
    padding: 10px 0;

    display: flex;
    gap: 10px;
    align-items: center;
}

.player-details li:last-child {
    border-bottom: none;
}

.player-details span {
    font-weight: 500;
    color: #555;
    min-width: 110px;
}

</style>
    <style>
        h1, h2 {
            text-align: center;
            color: #333;
        }
        .team-card {
            /* background-color: #007aff; */
            border-radius: 8px;
            color: white;
            padding: 15px;
            /* width: 100%; */
            /* max-width: 600px; */
            margin: 0 auto;
            /* box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); */
        }
        .team-card h2 {
            margin: 0;
        }
        .availability-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .availability-table th, .availability-table td {
            /* border: 1px solid #ddd; */
            padding: 10px;
            text-align: center;
            vertical-align: middle;
            color: black;
            font-weight: bold;
        }
        .availability-table th {
            /* background-color: #007aff; */
            color: black;
        }
        .available {
            background-color: #4caf50;
            color: white;
        }
        .unavailable {
            background-color: #f44336;
            color: white;
        }
        .slot {
            width: 60px;
            height: 30px;
            display: inline-block;
            margin: 2px;
        }
        .partie-card {
            border-radius: 8px;
            color: white;
            padding: 15px;
            width: 200px;
            text-align: center;
            display: inline-block;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin: 10px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .partie-card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        .parties-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
        }
        .partie-card.win {
            background-color: #4caf50; /* Vert pour les victoires */
        }
        .partie-card.loss {
            background-color: #f44336; /* Rouge pour les défaites */
        }
        .partie-card.pending {
            background-color: #e0e0e0; /* Gris clair pour les parties sans score */
            color: #333;
        }

.carousel {
    width: 100%;
    overflow: hidden;
    position: relative;
    margin: 20px auto;
}

.carousel-track {
    display: flex;
    transition: transform 0.4s ease-in-out;
}

.carousel-item {
    min-width: 100%;
    box-sizing: border-box;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.08);
    padding: 30px;
    max-width: 400px;
    width: 90%;
    margin: auto;
    text-align: center;
    transition: all 0.3s ease;
}

.card h3 {
    margin-bottom: 20px;
    font-size: 1.5em;
    color: #333;
}

.slot {
    width: 70px;
    height: 36px;
    display: inline-block;
    margin: 4px;
    text-align: center;
    line-height: 36px;
    font-weight: bold;
    border-radius: 10px;
    color: white;
    font-size: 14px;
}

.available {
    background-color: #4caf50;
}

.unavailable {
    background-color: #f44336;
}

.carousel-nav {
    text-align: center;
    margin-top: 15px;
}

.carousel-nav button {
    background-color: #000000;
    border: none;
    border-radius: 10px;
    color: white;
    padding: 10px 20px;
    margin: 0 10px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.carousel-nav button:hover {
    background-color: #005bb5;
}

.availability-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 20px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
    background-color: #fff;
}

.availability-table th {
    background-color: #f2f3f5;
    color: #333;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 14px;
    border-bottom: 1px solid #e0e0e0;
}

.availability-table td {
    text-align: center;
    padding: 12px;
    font-size: 15px;
    border-bottom: 1px solid #f5f5f5;
    color: #444;
}

.slot {
    display: inline-block;
    padding: 8px 14px;
    margin: 4px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 10px;
    transition: background-color 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    user-select: none;
}

.available {
    background-color: #34c759; /* Apple Green */
    color: white;
}

.unavailable {
    background-color: #ff3b30; /* Apple Red */
    color: white;
}

    </style>


<script>
    let currentIndex = 0;
    const track = document.getElementById('carouselTrack');
    const items = document.querySelectorAll('.carousel-item');

    function moveCarousel(direction) {
        const maxIndex = items.length - 1;
        currentIndex += direction;
        if (currentIndex < 0) currentIndex = 0;
        if (currentIndex > maxIndex) currentIndex = maxIndex;
        const offset = -currentIndex * 100;
        track.style.transform = `translateX(${offset}%)`;
    }
</script>