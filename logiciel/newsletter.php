<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Menu vertical -->
<?php
include("./assets/menu.php");
?>

        <!-- Contenu principal -->
        <main role="main" class="container">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Newsletter</h1>
            </div>

            <h2>Newsletter</h2>

            



            <?php
            
include './assets/conn_bdd.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur connexion : " . $conn->connect_error);
}

// Supprimer un message
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM newsletter WHERE id = $id");
    echo '<script>window.location.href = "./newsletter.php";</script>';
    exit;
}

// Ajouter un message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $conn->real_escape_string($_POST['user']);
    $message = $conn->real_escape_string($_POST['message']);
    $conn->query("INSERT INTO newsletter (user, message, date) VALUES ('$user', '$message', NOW())");
    echo '<script>window.location.href = "./newsletter.php";</script>';
    exit;
}

// Récupération des messages
$result = $conn->query("SELECT * FROM newsletter ORDER BY date DESC");
?>


    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f5f5f7;
            margin: 0;
            padding: 40px;
            color: #333;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 30px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 40px;
        }

        input[type="text"],
        textarea {
            font-size: 16px;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 12px;
            resize: vertical;
        }

        button {
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 12px;
            background-color: #1DAA61;
            color: white;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        button:hover {
            background-color: #17964f;
        }

        .message {
            background-color: #f2f2f7;
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 15px;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        .message p {
            margin: 0;
            font-size: 15px;
        }

        .delete-btn {
            background: none;
            border: none;
            color: #ff3b30;
            font-weight: bold;
            cursor: pointer;
        }

        .delete-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>📬 Gestion des messages</h1>

    <form method="POST">
    <input 
    value="<?php echo ucfirst(strtolower($user['nom'])) . ' ' . ucfirst(strtolower($user['prenom'])); ?>" type="text" name="user" placeholder="Nom de l’auteur" required readonly>

        <textarea name="message" rows="4" placeholder="Votre message..." required></textarea>
        <button type="submit">➕ Ajouter le message</button>
    </form>

    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="message">
            <div class="message-header">
                <span><strong><?= htmlspecialchars($row['user']) ?></strong> • <?= date("d/m/Y H:i", strtotime($row['date'])) ?></span>
                <a class="delete-btn" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Supprimer ce message ?');">Supprimer</a>
            </div>
            <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>
        </div>
    <?php endwhile; ?>
</div>
</body>
</html>
















        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
