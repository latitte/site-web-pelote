<?php
// Inclure les paramètres et autres configurations
include("../logiciel/assets/extract_parametre.php");

$date = $_GET['date'] ?? null;

if (!$date) {
    die("Date non spécifiée.");
}

// Connexion à MySQL avec gestion des erreurs
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Traiter les requêtes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $arbitre_id = intval($_POST['arbitre_id']);
    $date = $conn->real_escape_string($_POST['date']);
    
    // Vérifier que l'arbitre existe
    $sql_check_arbitre = "SELECT id FROM arbitre WHERE id = ?";
    $stmt_check = $conn->prepare($sql_check_arbitre);
    $stmt_check->bind_param('i', $arbitre_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        if ($action === 'add') {
            // Ajouter la date au nouvel arbitre
            $sql_add = "UPDATE arbitre SET permanence = CONCAT(permanence, ', ', ?) WHERE id = ?";
            $stmt = $conn->prepare($sql_add);
            $stmt->bind_param('si', $date, $arbitre_id);
            $stmt->execute();
            $stmt->close();
        } elseif ($action === 'remove') {
            // Supprimer la date de l'arbitre actuel
            $date_escaped = $conn->real_escape_string($date);
            $sql_remove = "UPDATE arbitre SET permanence = TRIM(BOTH ', ' FROM REPLACE(CONCAT(', ', permanence, ', '), ', $date_escaped, ', ',')) WHERE id = ?";
            $stmt = $conn->prepare($sql_remove);
            $stmt->bind_param('i', $arbitre_id);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        echo "<script>alert('L\'arbitre sélectionné n\'existe pas.');</script>";
    }
    $stmt_check->close();
}

// Requête pour récupérer les données des arbitres
$sql = "SELECT id, prenom, tel, permanence FROM arbitre";
$result = $conn->query($sql);

$arbitres = ['other' => []];
while ($row = $result->fetch_assoc()) {
    $permanences = explode(", ", $row['permanence']);
    if (in_array($date, $permanences)) {
        $arbitres[$date][] = $row;
    } else {
        $arbitres['other'][] = $row;
    }
}
$conn->close();
?>

<style>
#backToIndexBtn {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 10px 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    cursor: pointer;
    z-index: 1000;
}
#backToIndexBtn:hover {
    background-color: #0056b3;
}
</style>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<link rel="manifest" href="./assets/manifest.json">
    <title>Modifier Jour</title>
</head>
<body>

<button id="backToIndexBtn" onclick="window.location.href='arbitre_calendar.php';">Retour</button>

<div class="container">
    <h2>Modifier les arbitres pour le <?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?></h2>

    <h3>Arbitres actuels :</h3>
    <ul>
        <?php
        if (isset($arbitres[$date])) {
            foreach ($arbitres[$date] as $arbitre) {
                echo '<li>' . htmlspecialchars($arbitre['prenom'], ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars($arbitre['tel'], ENT_QUOTES, 'UTF-8') . ' <form method="post" style="display:inline;"><input type="hidden" name="arbitre_id" value="' . $arbitre['id'] . '"><input type="hidden" name="date" value="' . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . '"><input type="hidden" name="action" value="remove"><button type="submit" class="btn btn-danger btn-sm">Supprimer</button></form></li>';
            }
        } else {
            echo '<li>Aucun arbitre prévu.</li>';
        }
        ?>
    </ul>

    <h3>Ajouter un arbitre :</h3>
    <form id="addArbitreForm" method="post">
        <input type="hidden" name="date" value="<?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label for="arbitre_nom">Sélectionner un arbitre :</label>
            <input type="text" class="form-control" id="arbitre_nom" name="arbitre_nom">
            <input type="hidden" id="arbitre_id" name="arbitre_id">
            <span id="arbitre_error" style="color: red; display: none;">L'arbitre sélectionné n'existe pas. Veuillez en choisir un parmi la liste</span>
        </div>
        <button type="submit" class="btn btn-primary">Ajouter</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script>
$(function() {
    var arbitres = [
        <?php
        if (isset($arbitres['other'])) {
            $data = array_map(function($arbitre) {
                return '{ label: "' . addslashes($arbitre['prenom']) . '", value: "' . $arbitre['id'] . '" }';
            }, $arbitres['other']);
            echo implode(', ', $data);
        }
        ?>
    ];

    $("#arbitre_nom").autocomplete({
        source: arbitres,
        select: function(event, ui) {
            $("#arbitre_nom").val(ui.item.label);
            $("#arbitre_id").val(ui.item.value);
            $("#arbitre_error").hide();
            return false;
        }
    });

    $("#addArbitreForm").on('submit', function(event) {
        var arbitreId = $("#arbitre_id").val();
        if (arbitreId === '') {
            event.preventDefault();
            $("#arbitre_error").show();
        }
    });
});
</script>
</body>
</html>
<style>
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    background-color: #f4f7f6;
    color: #333;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

h2, h3 {
    color: #333;
}

button {
    background-color: #007aff;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

button:hover {
    background-color: #0051a1;
}

#backToIndexBtn {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #ffffff;
    color: #007aff;
    border: 2px solid #007aff;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 16px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

#backToIndexBtn:hover {
    background-color: #f1f1f1;
    color: #0051a1;
    border-color: #0051a1;
}

ul {
    list-style-type: none;
    padding: 0;
}

li {
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
}

form {
    margin-top: 20px;
}

.form-group {
    margin-bottom: 20px;
}

input[type="text"] {
    border-radius: 8px;
    padding: 10px;
    width: 100%;
    border: 1px solid #ddd;
    transition: border-color 0.3s ease;
}

input[type="text"]:focus {
    border-color: #007aff;
}

span {
    font-size: 14px;
    color: red;
    display: none;
}
</style>