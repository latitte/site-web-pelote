<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Menu vertical -->
<?php
include("./assets/menu.php");
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoyer un SMS et voir les messages envoyés</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f5f5f7;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            padding: 1rem;
        }

        .container {
            background-color: #fff;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 600px;
        }

        h2 {
            text-align: center;
            color: #1d1d1f;
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #3c3c43;
            font-weight: 500;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 0.75rem;
            border-radius: 12px;
            border: 1px solid #ccc;
            font-size: 1rem;
            margin-bottom: 1rem;
            transition: border 0.3s ease;
        }

        input[type="text"]:focus,
        textarea:focus {
            border-color: #007aff;
            outline: none;
        }

        input[type="submit"] {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 12px;
            background-color: #007aff;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #005fd1;
        }

        .message {
            text-align: center;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .message.success {
            color: #28a745;
        }

        .message.error {
            color: #dc3545;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        th, td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }

        tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>
<body>



<?php

// URL de l'API
$url = 'https://api.topmessage.fr/v1/balances/97d9cbf7-9a33-4404-b317-f5f55a5cf4f8';
$apiKey = "cbf56aaebc95077b03b8c21160f42691"; // Remplacez par votre vraie clé API
// Préparation de la requête cURL
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-TopMessage-Key: $apiKey",
    "Content-Type: application/json"
]);

// Exécution de la requête
$response = curl_exec($ch);

// Vérifie s'il y a une erreur cURL
if (curl_errno($ch)) {
    echo 'Erreur cURL : ' . curl_error($ch);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Décodage de la réponse JSON
$data = json_decode($response, true);

// Recherche du crédit SMS_LOCAL
$smsLocalCredit = null;
if (isset($data['data']) && is_array($data['data'])) {
    foreach ($data['data'] as $credit) {
        if ($credit['credit_type'] === 'SMS_LOCAL') {
            $smsLocalCredit = $credit['credit_count'];
            break;
        }
    }
}

// Affichage du résultat
if ($smsLocalCredit !== null) {
    //echo "Crédits SMS_LOCAL restants : " . $smsLocalCredit;
} else {
    // echo "Crédit SMS_LOCAL non trouvé.";
    $smsLocalCredit = "Aucune donnée";
}
?>


    <div class="container">
        <h2>Envoyer un SMS<br><?php echo $smsLocalCredit;?> SMS restants</h2>

        <?php if (isset($_GET['success']) && $_GET['success'] === '1'): ?>
            <div class="message success">✅ SMS envoyé avec succès !</div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="message error">❌ Erreur : <?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <form action="envoyer_sms.php" method="POST">
            <label for="numero">Numéro de téléphone</label>
            <input type="text" id="numero" name="numero" placeholder="Ex: 0678123456" required>

            <label for="message">Message</label>
            <textarea id="message" name="message" rows="5" placeholder="Votre message ici..." required></textarea>

            <input type="submit" value="Envoyer le SMS">
        </form>
        
        <?php
        $apiUrl = "https://api.topmessage.fr/v1/messages";


        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-TopMessage-Key: $apiKey",
            "Accept: application/json"
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            echo "<table>";
            echo "<thead><tr>
                    <th>Expéditeur</th>
                    <th>Destinataire</th>
                    <th>Message</th>
                    <th>Statut</th>
                    <th>Date</th>
                  </tr></thead><tbody>";

            foreach ($data["data"] as $msg) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($msg["from"]) . "</td>";
                echo "<td>" . htmlspecialchars($msg["to"]) . "</td>";
                echo "<td>" . nl2br(htmlspecialchars($msg["text"])) . "</td>";
                echo "<td>" . htmlspecialchars($msg["status"]) . "</td>";
                echo "<td>" . htmlspecialchars(date("d/m/Y H:i", strtotime($msg["create_date"]))) . "</td>";
                echo "</tr>";
            }

            echo "</tbody></table>";
        } else {
            echo "<div class='message error'>❌ Erreur de récupération des messages : HTTP $httpCode</div>";
        }
        ?>
    </div>
</body>
</html>


















    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
