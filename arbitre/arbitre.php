<?php

// Connexion à la base de données
include("../logiciel/assets/extract_parametre.php");
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fonction pour obtenir les arbitres pour un jour donné
function getArbitres($conn, $date) {
    $sql = "SELECT tel, permanence FROM arbitre";
    $result = $conn->query($sql);
    $arbitres = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            if (!empty($row["permanence"])) {
                $permanences = explode(', ', $row["permanence"]);
                if (in_array($date, $permanences)) {
                    $arbitres[] = $row["tel"];
                }
            }
        }
    }

    return $arbitres;
}

// Fonction pour obtenir les parties pour un jour donné
function getParties($conn, $date) {
    $sql = "SELECT heure, partie FROM calendrier WHERE jours = '$date' ORDER BY heure ASC";
    $result = $conn->query($sql);
    $parties = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $parties[] = "  - " . $row["heure"] . " -> " . $row["partie"] . "<br>";
        }
    }

    return $parties;
}

// Récupérer demain et après-demain
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$dayAfterTomorrow = date('Y-m-d', strtotime('+2 days'));

// Jours en français
$dayMapping = ['Mon' => 'Lun', 'Tue' => 'Mar', 'Wed' => 'Mer', 'Thu' => 'Jeu', 'Fri' => 'Ven', 'Sat' => 'Sam', 'Sun' => 'Dim'];
$tomorrowDayInFrench = $dayMapping[date('D', strtotime('+1 day'))];
$dayAfterTomorrowDayInFrench = $dayMapping[date('D', strtotime('+2 days'))];

// Paramètres de jours disponibles
$jours_dispo_bdd = explode(", ", $parametres['jours_dispo']);

// Traitement des paramètres GET
$response = [];
if (isset($_GET['demain'])) {
    if (!in_array($tomorrowDayInFrench, $jours_dispo_bdd)) {
        echo "0";
        exit;
    }

    $arbitresTomorrow = getArbitres($conn, $tomorrow);
    $partiesTomorrow = getParties($conn, $tomorrow);

    if (!empty($arbitresTomorrow)) {
        $partiesText = !empty($partiesTomorrow) ? "Arbitre<br><br>Bonsoir, partie demain :<br>" . implode("", $partiesTomorrow) . "<br>Bonne soirée<br><br>Plus de renseignement sur https://ilharre.tournoi-pelote.com<br>NE PAS REPONDRE" : "Arbitre<br><br>Bonsoir, pas de parties demain.<br>Bonne soirée<br><br>Plus de renseignement sur https://ilharre.tournoi-pelote.com<br>NE PAS REPONDRE";
        foreach ($arbitresTomorrow as $tel) {
            $response[] = ["numero" => $tel, "texte" => $partiesText];
        }
        echo json_encode($response);
    } else {
        echo "0";
    }
} elseif (isset($_GET['apresdemain'])) {
    if (!in_array($dayAfterTomorrowDayInFrench, $jours_dispo_bdd)) {
        echo "non";
        exit;
    }

    $arbitresDayAfterTomorrow = getArbitres($conn, $dayAfterTomorrow);
    echo !empty($arbitresDayAfterTomorrow) ? "oui" : "Bonsoir, aucun arbitre pour le " . date('d/m', strtotime('+2 days')) . "<br>Merci de vous inscrire";
} else {
    echo "Paramètre incorrect. Utilisez 'demain' ou 'apresdemain'.";
}

// Fermeture de la connexion
$conn->close();

?>
