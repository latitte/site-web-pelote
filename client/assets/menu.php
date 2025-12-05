
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Menu</title>


    <?php
include("../logiciel/assets/conn_bdd.php");  // Connexion à la base de données

// Connexion à la base de données
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Requête SQL pour récupérer les valeurs des couleurs
$query = "SELECT * FROM parametre WHERE parametre IN ('color-site-menu', 'color-site-fond', 'openRegistration')";
$result = mysqli_query($conn, $query);

// Vérification du résultat de la requête
if ($result) {
    // Stockage des couleurs dans un tableau associatif
    $colors = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $colors[$row['parametre']] = $row['valeur'];
    }
} else {
    echo "Erreur de requête: " . mysqli_error($conn);
}

// Injection des couleurs dans le style CSS
?>
<style>
    :root {
        --main-color: <?php echo $colors['color-site-menu']; ?>;
        --back-color: <?php echo $colors['color-site-fond']; ?>;
    }
</style>




    <style>


        .language-selector {
            position: relative;
            display: inline-block;
            margin-left: 10px;
        }

        .language-selector .selected-language {
            padding: 10px;
            /* border: 1px solid #ccc; */
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .language-selector img {
            width: 24px;
            height: 24px;
            margin-right: 8px;
        }

        .language-selector .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }

        .language-selector .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .language-selector .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .language-selector:hover .dropdown-content {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Bouton pour dérouler le menu -->
    <button id="custom-menu-toggle" class="custom-menu-toggle">
        <i class="fa fa-bars" aria-hidden="true"></i>
    </button>
    <!-- Inclusion du menu -->
    <div id="custom-menu-container"></div>

    <div class="language-selector">
        <div class="selected-language">
            <img src="./assets/lang/<?php echo $lang_code; ?>.svg" alt="Language Flag"> 
        </div>
        <div class="dropdown-content">
            <a href="?lang=fr">
                <img src="./assets/lang/fr.svg" alt="French Flag"> Français
            <a href="?lang=eus">
                <img src="./assets/lang/eus.svg" alt="Euskara Flag"> Euskara
            </a>
        </div>
    </div>

    <script>
        // Inclure le contenu du menu dynamiquement
        document.getElementById('custom-menu-container').innerHTML = `
            <div class="custom-menu">
                <a href="./index.php?lang=<?php echo $lang_code; ?>" id="custom-menu-tournoi"><i class="fa fa-trophy" aria-hidden="true"></i> <?php echo $lang['menu-accueil']; ?></a>
                <a href="./newsletter.php?lang=<?php echo $lang_code; ?>" id="custom-menu-newsletter"><i class="fa fa-trophy" aria-hidden="true"></i> <?php echo $lang['menu-newsletter']; ?></a>
                <a href="./equipes.php?lang=<?php echo $lang_code; ?>" id="custom-menu-equipes"><i class="fa fa-user" aria-hidden="true"></i> <?php echo $lang['menu-equipes']; ?></a>
            
                <a href="./calendrier.php?lang=<?php echo $lang_code; ?>" id="custom-menu-calendrier"><i class="fa fa-calendar" aria-hidden="true"></i> <?php echo $lang['menu-calendrier']; ?></a>
                <a href="./arbre_tournoi.php?lang=<?php echo $lang_code; ?>" id="custom-menu-arbre"><i class="fa fa-tree" aria-hidden="true"></i> <?php echo $lang['menu-classement']; ?></a>
                <?php
                if ($colors['openRegistration'] == 1){
                ?>
                <a href="./inscription_joueur.php?lang=<?php echo $lang_code; ?>" id="custom-menu-inscription"><i class="fa fa-address-book" aria-hidden="true"></i> <?php echo $lang['menu-inscription']; ?></a>
                <?php
                }else{

                }
                ?>
                <a href="./connexion.php?lang=<?php echo $lang_code; ?>" id="custom-menu-connexion"><i class="fa fa-sign-in" aria-hidden="true"></i> <?php echo $lang['menu-connexion']; ?></a>

                <?php 
                if(isset($team_id)){ ?>
                <a href="./logout.php" id="custom-menu-connexion"><i class="fa fa-sign-out" aria-hidden="true"></i></a>     
                <?php } ?>
            </div>
        `;

        document.addEventListener("DOMContentLoaded", function() {
            // Toggle menu visibility on button click
            document.getElementById('custom-menu-toggle').addEventListener('click', function() {
                document.querySelector('.custom-menu').classList.toggle('custom-menu-open');
            });

            // Ajoutez la classe 'selected' à l'élément de menu correspondant
            var path = window.location.pathname;
            var page = path.split("/").pop();

            var menuItems = {
                "index.php": "custom-menu-tournoi",
                "newsletter.php": "custom-menu-newsletter",
                "equipes.php": "custom-menu-equipes",
                "report_partie.php": "custom-menu-report",
                "calendrier.php": "custom-menu-calendrier",
                "inscription_joueur.php": "custom-menu-inscription",
                "arbre_tournoi.php": "custom-menu-arbre",
                "connexion.php": "custom-menu-connexion"
            };

            if (menuItems[page]) {
                document.getElementById(menuItems[page]).classList.add("custom-selected");
            }
        });
    </script>
</body>
</html>