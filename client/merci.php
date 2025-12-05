<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merci pour votre réponse</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: rgb(43 98 38 / 79%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .popup-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            padding: 20px;
            box-sizing: border-box;
            text-align: center;
        }
        .popup-container h1 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        .popup-container p {
            font-size: 16px;
            color: #333;
            margin: 15px 0;
        }
        .popup-container button {
            background-color: #007aff;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        .popup-container button:hover {
            background-color: #005bb5;
        }
    </style>
</head>
<body>
    <div class="popup-container">
        <h1>Merci pour votre réponse !</h1>
        <p>Votre réponse à l'enquête a été enregistrée avec succès.</p>
        <button onclick="window.location.href='index.php'">Retour au site</button>
    </div>
</body>
</html>

