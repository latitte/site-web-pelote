<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partie Ilharre</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            /* background-color: #f5f5f7; */
            color: #1d1d1f;
            margin: 0;
            padding: 0;
        }
        .apple-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 20px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .apple-card {
            width: 100%;
            max-width: 600px;
            margin: 10px 0;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease-in-out;
        }
        .apple-card:hover {
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }
        .apple-card-body {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .apple-card-title {
            font-size: 1.5em;
            margin-bottom: 10px;
            color: #0071e3;
        }
        .apple-card-text {
            font-size: 1em;
            margin: 5px 0;
            color: #1d1d1f;
        }
        .apple-card-green {
            border-left: 5px solid #28a745;
        }
        .apple-card-blue {
            border-left: 5px solid #007bff;
        }
        .apple-no-results {
            font-size: 1.2em;
            color: #6e6e73;
        }
        .apple-card-button {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            font-size: 1em;
            color: #ffffff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
        }
        .apple-card-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<?php
$lang_code = isset($_GET['lang']) ? $_GET['lang'] : 'fr';

// Chargement du fichier de langue approprié
if ($lang_code == 'eus') {
    include("./lang/lang_eus.php");
} else {
    include("./lang/lang_fr.php");
}


$teamNumber = isset($_GET['team']) ? intval($_GET['team']) : 0;

// Connexion à la base de données
include("../../logiciel/assets/conn_bdd.php");

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Construire la requête pour rechercher exactement l'équipe spécifiée
$sql = "SELECT * FROM calendrier WHERE partie LIKE '$teamNumber/%' OR partie LIKE '%/$teamNumber' ORDER BY jours ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<div class='apple-container'>";
    while($row = $result->fetch_assoc()) {
        $scoreClass = $row["score"] ? "apple-card-green" : "apple-card-blue";
        echo "<div class='apple-card $scoreClass'>";
        echo "<div class='apple-card-body'>";
        echo "<h5 class='apple-card-title'>" . $lang['partie'] . " " . $row["partie"] . "</h5>";

        // Convertir l'heure au format 24 heures
        $heure = str_replace('h', ':', $row["heure"]); // Exemple: '20h00' -> '20:00'


        if($row["jours"] == "0000-00-00"){
        echo "<p class='apple-card-text'>Partie en attente</p>";
        }else{
        echo "<p class='apple-card-text'>" . $lang['jour'] . " " . $row["jours"] . "</p>";
        echo "<p class='apple-card-text'>" . $lang['heure'] . " " . $heure . "</p>";
        echo "<p class='apple-card-text'>" . $lang['score'] . " " . $row["score"] . "</p>";
        }





        // Extraire la partie numérique uniquement
        $niveau_numeric = preg_replace('/\D/', '', $row["niveau"]);
        $partie_niveau = "";
        if ($niveau_numeric !== null) {
            if (in_array($niveau_numeric, ['1'])) {
                $partie_niveau = "Qualifications";
            } elseif (in_array($niveau_numeric, ['2'])) {
                $partie_niveau = "Barrage";
            } elseif (in_array($niveau_numeric, ['31', '32', '33', '34', '35', '36', '37', '38'])) {
                $partie_niveau = "1/8 de finales";
            } elseif (in_array($niveau_numeric, ['41', '42', '43', '44'])) {
                $partie_niveau = "1/4 de finales";
            } elseif (in_array($niveau_numeric, ['51', '52'])) {
                $partie_niveau = "1/2 finales";
            } elseif ($niveau_numeric == '60') {
                $partie_niveau = "Finale";
            }
        }

        // Afficher le créneau si défini
        if (isset($partie_niveau)) {
            echo "<p class='apple-card-text'>" . $lang['niveau'] . " " . $partie_niveau . "</p>";
        } else {
            echo "<p class='apple-card-text'>Niveau non déterminé</p>";
        }

        // Générer les dates au format iCalendar
        $dateTimeStart = date('Ymd\THis', strtotime($row["jours"] . ' ' . $heure)); // Format: 20240804T120000
        $dateTimeEnd = date('Ymd\THis', strtotime($row["jours"] . ' ' . $heure . ' +1 hour')); // Suppose durée 1 heure
        $eventTitle = "Partie Ilharre";
        $eventDescription = "Partie " . $row["partie"] . "\nJour " . $row["jours"] . "\nHeure " . $heure . "\nNiveau " . $partie_niveau;
        $eventLocation = "Ilharre";

        // Créer le contenu du fichier .ics
        $icsContent = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//Your Company//NONSGML Event//EN\n";
        $icsContent .= "BEGIN:VEVENT\nSUMMARY:" . $eventTitle . "\n";
        $icsContent .= "DESCRIPTION:" . $eventDescription . "\n";
        $icsContent .= "LOCATION:" . $eventLocation . "\n";
        $icsContent .= "DTSTART:" . $dateTimeStart . "\n";
        $icsContent .= "DTEND:" . $dateTimeEnd . "\n";
        $icsContent .= "END:VEVENT\nEND:VCALENDAR";

        $icsFile = 'data:text/calendar;charset=utf8,' . rawurlencode($icsContent);

        // Créer les URLs pour Google Calendar et Outlook
        $googleCalendarUrl = "https://www.google.com/calendar/render?action=TEMPLATE";
        $googleCalendarUrl .= "&text=" . urlencode($eventTitle);
        $googleCalendarUrl .= "&dates=" . urlencode($dateTimeStart . "/" . $dateTimeEnd);
        $googleCalendarUrl .= "&details=" . urlencode($eventDescription);
        $googleCalendarUrl .= "&location=" . urlencode($eventLocation);

        $outlookCalendarUrl = "https://outlook.live.com/owa/?path=/calendar/action/compose";
        $outlookCalendarUrl .= "&subject=" . urlencode($eventTitle);
        $outlookCalendarUrl .= "&startdt=" . urlencode($dateTimeStart);
        $outlookCalendarUrl .= "&enddt=" . urlencode($dateTimeEnd);
        $outlookCalendarUrl .= "&body=" . urlencode($eventDescription);
        $outlookCalendarUrl .= "&location=" . urlencode($eventLocation);

        // Afficher les boutons
        echo "<a href='$icsFile' download='event.ics' class='apple-card-button'>Ajouter au calendrier (iPhone)</a>";
        echo "<a href='$googleCalendarUrl' target='_blank' class='apple-card-button'>Ajouter à Google Calendar</a>";
        echo "<a href='$outlookCalendarUrl' target='_blank' class='apple-card-button'>Ajouter à Outlook Calendar</a>";

        echo "</div>";
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<div class='apple-container'><p class='apple-no-results'>0 résultats</p></div>";
}
$conn->close();
?>

</body>
</html>
