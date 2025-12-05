<?php
    // Inclure le fichier de connexion à la base de données
    include("./assets/extract_parametre.php");

    // Activer le rapport d'erreurs pour le débogage
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Vérifier si les variables nécessaires sont définies
    if (!isset($servername) || !isset($username) || !isset($password) || !isset($dbname)) {
        die("Les informations de connexion à la base de données ne sont pas définies correctement.");
    }

    // Créer une connexion
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Vérifier la connexion
    if ($conn->connect_error) {
        die("Échec de la connexion à la base de données: " . $conn->connect_error);
    }

    // Initialisation des variables
    $text_accueil = "";
    $img_accueil = "";

    // Récupérer les valeurs actuelles de text_accueil et img_accueil
    $query = "SELECT parametre, valeur FROM parametre WHERE parametre IN ('text_accueil', 'img_accueil')";
    $res = mysqli_query($conn, $query);

    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            if ($row['parametre'] == 'text_accueil') {
                $text_accueil = $row['valeur'];
            }
            if ($row['parametre'] == 'img_accueil') {
                $img_accueil = $row['valeur'];
            }
        }
    } else {
        echo "Erreur de récupération des données: " . mysqli_error($conn);
    }

    mysqli_close($conn); // Fermer la connexion à la base de données
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mettre à jour l'accueil</title>
    <script src="https://cdn.tiny.cloud/1/2ksy7ftlwgl48ji5mj98pgkk57kjn87pdiy9fk2kfs2k3sw5/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
    

        .form-horizontal {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            font-size: 16px;
            color: #333;
            margin-bottom: 10px;
            display: block;
        }

        .form-group textarea {
            width: 100%;
            height: 300px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
            box-sizing: border-box;
            resize: vertical;
        }

        .form-group input[type="file"] {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
        }

        .form-group img {
            display: block;
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-top: 10px;
            width: 100%;
        }

        .btn {
            display: inline-block;
            font-size: 16px;
            font-weight: 600;
            color: #ffffff;
            background-color: #007aff; /* Apple blue */
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .btn:hover {
            background-color: #0051a2; /* Darker shade of blue */
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
    </style>
    <script>
    tinymce.init({
        selector: '#text_accueil',
        setup: function (editor) {
            editor.on('init', function () {
                console.log('TinyMCE initialized');
            });
            editor.on('change', function () {
                console.log('Content changed');
            });
        },
        width: '100%',
        height: '500px',
        plugins: 'advlist autolink link image lists charmap preview anchor pagebreak',
        toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullscreen | forecolor backcolor emoticons | help',
        menubar: 'favs file edit view insert format tools table help',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size:16px; color: #333; }'
    });
    </script>
</head>
<body>

<div class="form-bg">
    <div class="container">
        <h3 class="title">Mettre à jour l'accueil</h3>
        
        <form class="form-horizontal" action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="text_accueil">Texte d'accueil</label>
                <textarea name="text_accueil" id="text_accueil" required><?= htmlspecialchars($text_accueil); ?></textarea>
            </div>
            <div class="form-group">
                <label for="img_accueil">Image d'accueil (370x420)</label>
                <?php if (!empty($img_accueil)): ?>
                    <div>
                        <img src="./assets/img/<?= htmlspecialchars($img_accueil); ?>" alt="Image d'accueil actuelle">
                    </div>
                <?php endif; ?>
                <input type="file" name="img_accueil" id="img_accueil">
            </div>
            <button type="submit" class="btn">Mettre à jour</button>
        </form>
    </div>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    // Créer une connexion
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Vérifier la connexion
    if ($conn->connect_error) {
        die("Échec de la connexion à la base de données: " . $conn->connect_error);
    }

    // Traitement du texte d'accueil
    $text_accueil = stripslashes($_POST['text_accueil']);
    $text_accueil = mysqli_real_escape_string($conn, $text_accueil);

    // Traitement de l'image d'accueil
    if (isset($_FILES['img_accueil']) && $_FILES['img_accueil']['error'] == UPLOAD_ERR_OK) {
        $img_name = $_FILES['img_accueil']['name'];
        $hasard = rand(1, 10);
        $img_accueil = $hasard . "_" . $img_name;
        $currentLocation = $_FILES['img_accueil']['tmp_name'];
        $newLocation = './assets/img/' . $img_accueil;
        $moved = move_uploaded_file($currentLocation, $newLocation);

        if ($moved) {
            echo "Image téléchargée avec succès<br>";
        } else {
            echo "Erreur lors du téléchargement de l'image<br>";
        }
    } else {
        // Conserver l'ancienne image si aucune nouvelle image n'est téléchargée
        $query = "SELECT valeur FROM parametre WHERE parametre = 'img_accueil'";
        $res = mysqli_query($conn, $query);
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            $img_accueil = $row['valeur'];
        }
    }

    // Mise à jour de la table parametre
    $query = "UPDATE parametre SET valeur = CASE 
                 WHEN parametre = 'text_accueil' THEN '$text_accueil'
                 WHEN parametre = 'img_accueil' THEN '$img_accueil'
              END
              WHERE parametre IN ('text_accueil', 'img_accueil')";

    $res = mysqli_query($conn, $query);

    if ($res) {
        echo "Mise à jour réussie";
    } else {
        echo "Erreur SQL : " . mysqli_error($conn);
    }

    mysqli_close($conn); // Fermer la connexion à la base de données
}
?>
</body>
</html>
