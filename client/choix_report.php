<?php
include("../logiciel/assets/conn_bdd.php");
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$token = $_GET['token'] ?? null;

if (!$token) {
    die("Lien invalide.");
}

// Vérifier que le token existe
$stmt = $conn->prepare("SELECT * FROM demandes_report WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$demande = $result->fetch_assoc();

function formatDateFr($date)
{
    setlocale(LC_TIME, 'fr_FR.utf8', 'fra'); // essaie différentes locales
    return ucfirst(strftime('%A %e %B', strtotime($date)));
}


if (!$demande) {
    die("Cette demande n'existe pas.");
}

// Récupération des infos de la partie dans le calendrier
$partie_id = $demande['partie_id'];
$stmt_cal = $conn->prepare("SELECT * FROM calendrier WHERE id = ?");
$stmt_cal->bind_param("i", $partie_id);
$stmt_cal->execute();
$result_cal = $stmt_cal->get_result();
$partie = $result_cal->fetch_assoc();

$host = $_SERVER['HTTP_HOST'];
$lien_accept = "./valider_report.php?token=$token&action=accepte";
$lien_refuse = "./valider_report.php?token=$token&action=refuse";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Répondre à la demande</title>
    <style>
        :root {
            --bg-light: #f2f2f7;
            --bg-white: #ffffff;
            --primary: #007aff;
            --green: #28a745;
            --red: #dc3545;
            --text-main: #1c1c1e;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
        }

        .container {
            max-width: 700px;
            margin: 3rem auto;
            padding: 2rem;
            background-color: var(--bg-white);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        h2 {
            text-align: center;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        p {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .actions {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            color: white;
            transition: background-color 0.2s ease;
        }

        .accept {
            background-color: var(--green);
        }

        .accept:hover {
            background-color: #218838;
        }

        .refuse {
            background-color: var(--red);
        }

        .refuse:hover {
            background-color: #c82333;
        }

        details {
            margin-top: 2rem;
            background-color: #e9ecef;
            border-radius: 12px;
            overflow: hidden;
        }

        summary {
            padding: 1rem;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            background-color: #dfe4ea;
            transition: background-color 0.2s ease;
        }

        summary:hover {
            background-color: #cdd3da;
        }

        .partie-info {
            padding: 1rem 1.25rem;
            background-color: #fff;
            border-left: 4px solid var(--primary);
        }

        .partie-info p {
            margin: 0.5rem 0;
        }

        @media (max-width: 600px) {
            .container {
                margin: 1rem;
                padding: 1.25rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            .btn {
                width: 100%;
                text-align: center;
            }

            summary {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Demande de report</h2>
    <p>
        L'équipe <strong><?= htmlspecialchars($demande['equipe_demande']) ?></strong> souhaite reporter la partie 
        <strong><?= htmlspecialchars($partie['partie']) ?></strong> au 
        <strong><?= formatDateFr($demande['jour']) ?></strong> à 
        <strong><?= htmlspecialchars($demande['heure']) ?></strong>.
    </p>

    <?php if ($partie): ?>
        <details>
            <summary>📋 Détails de la partie</summary>
            <div class="partie-info">
                <p><strong>📅 Date initiale :</strong> <?= ($partie['jours'] != "0000-00-00") ? date('d/m/Y', strtotime($partie['jours'])) : 'Non définie' ?></p>
                <p><strong>🕘 Heure initiale :</strong> <?= htmlspecialchars($partie['heure']) ?></p>
                <p><strong>🆚 Équipes :</strong> <?= htmlspecialchars($partie['partie']) ?></p>
                <p><strong>🎯 Niveau :</strong> <?= $partie['niveau'] ?? 'Non spécifié' ?></p>
            </div>
        </details>
    <?php endif; ?>

    <div class="actions">
        <a href="<?= $lien_accept ?>" class="btn accept">✅ Accepter</a>
        <a href="<?= $lien_refuse ?>" class="btn refuse">❌ Refuser</a>
    </div>
</div>

</body>
</html>
