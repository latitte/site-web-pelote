<?php
use PhpOffice\PhpSpreadsheet\IOFactory;

require 'vendor/autoload.php';

// Définir la locale en français
setlocale(LC_TIME, 'fr_FR.UTF-8');

// Fonction pour déterminer le numéro de la semaine en juillet
function numero_semaine_juillet($date) {
    $debut_juillet = strtotime(date('Y').'-07-01');
    $delta = strtotime($date) - $debut_juillet;
    return floor($delta / (60 * 60 * 24 * 7)) + 1;
}

// Fonction pour récupérer les numéros de téléphone et les stocker
function get_phone_numbers_and_store($numeros_equipe, $heure, $date, $conn) {
    try {
        if (!$numeros_equipe || $numeros_equipe == "pas de partie") {
            echo "Partie à $heure: pas de partie\n";
            return;
        }

        if (is_array($numeros_equipe)) {
            $query = "SELECT `Numéro Equipe`, Telephone FROM equipe WHERE `Numéro Equipe` IN (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $numeros_equipe["equipe1"], $numeros_equipe["equipe2"]);
            $stmt->execute();
            $results = $stmt->get_result();

            $insert_query = "INSERT INTO partie_de_demain (`Numéro Equipe`, Telephone, Heure, Date) VALUES (?, ?, ?, ?)";
            while ($row = $results->fetch_assoc()) {
                $equipe_numero = $row['Numéro Equipe'];
                $telephone = $row['Telephone'];
                $stmt_insert = $conn->prepare($insert_query);
                $stmt_insert->bind_param("ssss", $equipe_numero, $telephone, $heure, $date);
                $stmt_insert->execute();
            }
        } else {
            echo "Partie à $heure n'est pas un dictionnaire valide.\n";
        }

    } catch (Exception $e) {
        echo "Erreur: " . $e->getMessage();
    }
}

try {
    // Connexion à la base de données MySQL
    $config = array(
        'user' => 'root',
        'password' => '',
        'host' => 'localhost',
        'database' => 'pelote_ilharre'
    );

    $conn = new mysqli($config['host'], $config['user'], $config['password'], $config['database']);
    if ($conn->connect_error) {
        die("Connexion échouée: " . $conn->connect_error);
    }

    // Exemple d'utilisation
    $date_actuelle = date('Y-m-d H:i:s');
    $semaine = numero_semaine_juillet(date('Y-m-d'));

    // Traduction des jours de la semaine en français
    $jours_fr = array(
        'Monday' => 'lundi',
        'Tuesday' => 'mardi',
        'Wednesday' => 'mercredi',
        'Thursday' => 'jeudi',
        'Friday' => 'vendredi',
        'Saturday' => 'samedi',
        'Sunday' => 'dimanche'
    );

    $aujourdhui = $jours_fr[date('l')];

    echo "Semaine: $semaine\n";
    echo "Aujourd'hui: $aujourdhui\n";


    $semaine = "2";
    $aujourdhui = "vendredi";

    // Chemin absolu vers le fichier Excel
    $file_path = 'C:\\wamp64\\www\\pelote_ilharre\\script\\agenda_data.xlsx';

    // Vérifier si le fichier existe avant de l'ouvrir
    if (file_exists($file_path)) {
        try {
            // Charger le fichier Excel
            $spreadsheet = IOFactory::load($file_path);

            // Sélectionner les parties à différentes heures
            $worksheet = $spreadsheet->getSheetByName('Agenda');
            $partie_18h30 = $worksheet->getCell('A1')->getValue();
            $partie_19h15 = $worksheet->getCell('A2')->getValue();
            $partie_20h = $worksheet->getCell('A3')->getValue();

            // Appel de la fonction pour chaque partie avec la date spécifiée
            if (is_string($partie_18h30) && strpos($partie_18h30, '/') !== false) {
                $equipes = explode('/', $partie_18h30);
                $numeros_equipe = array("equipe1" => trim($equipes[0]), "equipe2" => trim($equipes[1]));
                get_phone_numbers_and_store($numeros_equipe, "18:30", $date_actuelle, $conn);
            }

            if (is_string($partie_19h15) && strpos($partie_19h15, '/') !== false) {
                $equipes = explode('/', $partie_19h15);
                $numeros_equipe = array("equipe1" => trim($equipes[0]), "equipe2" => trim($equipes[1]));
                get_phone_numbers_and_store($numeros_equipe, "19:15", $date_actuelle, $conn);
            }

            if (is_string($partie_20h) && strpos($partie_20h, '/') !== false) {
                $equipes = explode('/', $partie_20h);
                $numeros_equipe = array("equipe1" => trim($equipes[0]), "equipe2" => trim($equipes[1]));
                get_phone_numbers_and_store($numeros_equipe, "20:00", $date_actuelle, $conn);
            }

        } catch (Exception $e) {
            echo "Erreur: " . $e->getMessage();
        }
    } else {
        echo "Le fichier Excel n'existe pas à l'emplacement spécifié.";
    }

    // Fermer la connexion à la base de données
    $conn->close();

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
