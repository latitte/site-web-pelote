<?php
include '../logiciel/assets/conn_bdd.php';

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifiez la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = $_POST['identifiant'];
    $pass = $_POST['pass'];

    // Préparation et exécution de la requête SQL
    $stmt = $conn->prepare("SELECT id FROM user_admin WHERE identifiant = ? AND pass = ?");
    $stmt->bind_param("ss", $identifiant, $pass);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Connexion réussie

                // Écrire dans le fichier notifs.txt
        $notif = "Connexion reussie pour " . $identifiant . " le " . date('Y-m-d H:i:s') . "\n";
        //file_put_contents('../logiciel/assets/notifs.txt', $notif, FILE_APPEND);


        //$identifiant = 'exemple_identifiant';  // Remplacez par l'identifiant réel
        setcookie('identifiant_organisateur', $identifiant, time() + 360000000, "/");  // Cookie valable 1 heure
        
        header("Location: ../logiciel"); // Redirection vers une page sécurisée après connexion
        exit();
    } else {
        $error = 'Identifiant ou mot de passe incorrect';
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../logiciel/assets/style.css">
    <title>Connexion</title>
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
        .popup {
            background-color: #ffffff;
            padding: 2em;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 360px;
            text-align: center;
            position: relative;
        }
        .popup h1 {
            font-weight: 600;
            margin-bottom: 1.5em;
            color: #333333;
        }
        .input-group {
            margin-bottom: 1em;
            text-align: left;
        }
        .input-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5em;
            color: #333333;
        }
        .input-group input {
            width: 93%;
            padding: 0.75em;
            border: 1px solid #d2d2d7;
            border-radius: 6px;
            font-size: 16px;
            color: #333333;
        }
        .input-group input:focus {
            border-color: #0071e3;
            outline: none;
        }
        .error {
            color: #ff3b30;
            margin-bottom: 1em;
        }
        .login-button {
            background-color: #0071e3;
            color: white;
            padding: 0.75em;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        .login-button:hover {
            background-color: #005bb5;
        }
    </style>
</head>
<body>
    <div class="popup">
        <h1>Connexion <?php echo $var_tournoi; ?></h1>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="input-group">
                <label for="identifiant">Identifiant</label>
                <input type="text" id="identifiant" name="identifiant" required>
            </div>
            <div class="input-group">
                <label for="pass">Mot de passe</label>
                <input type="password" id="pass" name="pass" required>
            </div>
            <button type="submit" class="login-button">Se connecter</button>
        </form>
    </div>
</body>
</html>
