<?php
session_start();


// ✅ Si déjà connecté, on redirige immédiatement
if (isset($_SESSION['id'])) {
    header("Location: ./compte-index.php");
    exit();
}



// Détection de la langue à partir de l'URL, sinon défaut en français
$lang_code = isset($_GET['lang']) ? $_GET['lang'] : 'fr';
if ($lang_code == 'eus') {
    include("./assets/lang/lang_eus.php");
} else {
    include("./assets/lang/lang_fr.php");
}

// Connexion à la BDD
include '../logiciel/assets/conn_bdd.php';

$error = '';



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $code = $_POST['code'];

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connexion échouée : " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM inscriptions WHERE id = ? AND code = ?");
    $stmt->bind_param("is", $id, $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['id'] = $id;
        header("Location: ./compte-index.php"); // ✅ REDIRECTION AVANT AFFICHAGE
        exit();
    } else {
        $error = "Identifiant ou mot de passe incorrect.";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $lang['title']; ?> - Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="stylesheet" href="./assets/style.css">
    <link rel="manifest" href="./assets/manifest.json">
    <link rel="icon" type="image/x-icon" href="./assets/tournoi-pelote.ico">
    <script src="./assets/app.js" defer></script>
    <style>
        * {
            box-sizing: border-box;
        }


        .login-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-box {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 14px 18px;
            margin-bottom: 20px;
            border: 1px solid #d1d1d6;
            border-radius: 14px;
            background-color: #f9f9f9;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #000000;
            background-color: #fff;
        }

        input[type="submit"] {
            width: 100%;
            padding: 14px;
            background-color: #000000;
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #005ecb;
        }

        .error {
            color: #ff3b30;
            text-align: center;
            margin-top: 20px;
        }

        .header h1 {
            font-size: 28px;
            margin: 20px 0;
            text-align: center;
            color: #1c1c1e;
        }
    </style>
</head>
<body>
    <div class="popup">
        <div class="header">
            <h1 style="text-align:center;"><?php echo $lang['tournament']; ?></h1>
        </div>
        <div class="menu">
            <?php include("./assets/menu.php"); ?>
        </div>
        <div class="content">
            <div class="login-container">
                <h2>Connexion</h2>
                <form method="post">
                    <input type="text" name="id" placeholder="Numéro d'équipe" required>
                    <input type="password" name="code" placeholder="Code secret" required>
                    <input type="submit" value="Se connecter">
                </form>
                <?php if ($error): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
