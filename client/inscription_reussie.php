<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Réussie</title>

<style>
    body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
    background-color: #f5f5f7;
}

.container {
    text-align: center;
    background-color: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    max-width: 400px;
    width: 100%;
}

.checkmark svg {
    width: 80px;
    height: 80px;
    margin-bottom: 20px;
}

h1 {
    font-size: 24px;
    color: #333333;
    margin-bottom: 10px;
}

p {
    font-size: 16px;
    color: #666666;
    margin-bottom: 30px;
}

.button {
    display: inline-block;
    padding: 12px 25px;
    background-color: #0070c9;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 16px;
    transition: background-color 0.3s ease;
}

.button:hover {
    background-color: #005b9f;
}


</style>
</head>
<body>
    <div class="container">
        <div class="checkmark">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="#4CAF50" stroke-width="2"></circle>
                <path d="M7 12.5L10 15.5L17 8.5" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </div>
        <h1>Inscription réussie</h1>
        <p>Merci, votre inscription a bien été prise en compte.</p>
        <p>Les inscriptions doivent être payées avant la première partie par virement bancaire<br><a href="./assets/RIB_sorginen_txapelketa.pdf">Information</a></p>
        <a href="./" class="button">Retour à l'accueil</a>
    </div>
</body>
</html>
