<?php
// Liste des noms de tournois (titre à afficher => sous-domaine)
$tournois = [
    "Ilharre Hiver 2024" => "ilharre-hiver-2024r",
    "Ilharre Été Sorginen Txapelketa 2025" => "ilharre-ete-2025",
    "Ilharre Hiver 2025" => "ilharre",

];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Choisissez votre tournoi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            padding: 40px 20px;
            text-align: center;
        }

        header h1 {
            font-size: 2.5rem;
            font-weight: 600;
            color: #111;
        }

        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
        }

        .tournament-list {
            display: grid;
            gap: 15px;
        }

        .tournament-item {
            padding: 20px;
            border-radius: 16px;
            background: white;
            box-shadow: 0 6px 16px rgba(0,0,0,0.06);
            transition: transform 0.2s, box-shadow 0.2s;
            text-align: center;
            font-size: 1.2rem;
            cursor: pointer;
            text-decoration: none;
            color: #111;
        }

        .tournament-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.1);
        }

        footer {
            margin-top: auto;
            text-align: center;
            padding: 20px;
            font-size: 0.9rem;
            color: #aaa;
        }
    </style>
</head>
<body>

<header>
    <h1>Bienvenue sur Tournoi-Pelote.com</h1>
    <p>Sélectionnez votre tournoi :</p>
</header>

<div class="container">
    <div class="tournament-list">
        <?php foreach ($tournois as $nomAffiche => $sousDomaine): ?>
            <a class="tournament-item" href="https://<?= htmlspecialchars($sousDomaine) ?>.tournoi-pelote.com">
                <?= htmlspecialchars($nomAffiche) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<footer>
    © <?= date('Y') ?> Tournoi Pelote. Tous droits réservés.
</footer>

</body>
</html>
