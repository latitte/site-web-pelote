<?php
// Vérifie si le cookie 'identifiant_organisateur' existe
if (isset($_COOKIE['identifiant_organisateur'])) {
    // Utilisateur connecté
    $identifiant = htmlspecialchars($_COOKIE['identifiant_organisateur']);
    //echo "Bienvenue, Organisateur : " . $identifiant . "<br>";
} else {
    // Si le cookie n'existe pas, redirige vers la page de connexion
    header("Location: ../login/");
    exit();
}

// Connexion à la base
include("./assets/conn_bdd.php");



// Créer la connexion ici à partir des variables de conn_bdd.php
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit;
}

// Rechercher l'utilisateur dans la base de données
$sql = "SELECT * FROM user_admin WHERE identifiant = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$identifiant]);
$user = $stmt->fetch();

if ($user) {
    // Stocker les droits et informations dans la session
    $_SESSION['admin'] = [
        'id' => $user['id'],
        'identifiant' => $user['identifiant'],
        'nom' => $user['nom'],
        'prenom' => $user['prenom'],
        'droits' => [
            'accueil' => $user['droit_accueil'],
            'newsletter' => $user['droit_newsletter'],
            'sms' => $user['droit_sms'],
            'activite' => $user['droit_activite'],
            'paiement' => $user['droit_paiement'],
            'inscriptions' => $user['droit_inscriptions'],
            'poules' => $user['droit_poules'],
            'calendrier' => $user['droit_calendrier'],
            'classement' => $user['droit_classement'],
            'partie_implacees' => $user['droit_partie_implacees'],
            'partie_attente' => $user['droit_parite_attente'], // Correction clé ici
            'gestion_tournoi' => $user['droit_gestion_tournoi'],
            'gestion_finales' => $user['droit_gestion_finales'],
            'arbre_tournoi' => $user['droit_arbre_tournoi'],
            'score_poules' => $user['droit_score_poules'],
            'score_finales' => $user['droit_score_finales'],
            'creation_auto_poules' => $user['droit_creation_auto_poules'],
            'edit_poules' => $user['droit_edit_poules'],
            'creation_auto_calendrier' => $user['droit_creation auto_calendrier'], // Correction clé ici
            'barrages' => $user['droit_barrages'],
            'finales_start' => $user['droit_finales-start'], // Correction clé ici
            'app' => $user['droit_app'],
            'facturation' => $user['droit_facturation'],
            'facturation_admin' => $user['droit_facturation_admin'],
            'gestion_arbitrage' => $user['droit_gestion_arbitrage']
        ]
    ];
} else {
    echo "Erreur : utilisateur non trouvé.";
    exit;
}
?>




<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu avec Notifications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .top-menu {
            display: flex;
            align-items: center;
            color: white;
            padding: 10px;
        }
        .top-menu .menu-item {
            margin-right: 20px;
            cursor: pointer;
            position: relative;
        }
        .top-menu .menu-item .fa {
            font-size: 20px;
        }
        .notification-popup {
            display: none;
            position: absolute;
            top: 40px;
            left: 0;
            background-color: white;
            color: black;
            padding: 10px;
            border: 1px solid #ccc;
            width: 300px;
            z-index: 1000;
        }
        .notification-popup.active {
            display: block;
        }
        .notification-popup ul {
            list-style-type: none;
            padding: 0;
        }
        .notification-popup ul li {
            margin-bottom: 10px;
        }
        .notification {
            position: relative;
            display: inline-block;
            padding: 10px 20px;
            color: #ffffff;
            border-radius: 4px;
            font-family: 'Roboto', sans-serif;
            font-size: 16px;
        }
        .notification .badge {
            position: absolute;
            top: -10px;
            right: -10px;
            padding: 5px 10px;
            border-radius: 50%;
            background-color: red;
            color: white;
            font-size: 14px;
            font-weight: bold;
        }
        #start {
            background-color: red;
            border-radius: 9px;
        }
        #affich {
            background-color: green;
            border-radius: 9px;
        }
        #score {
            background-color: blue;
            border-radius: 9px;
        }
        #gestion {
            background-color: yellow;
            border-radius: 9px;
        }
        #create {
            background-color: pink;
            border-radius: 9px;
        }
        #end {
            background-color: maroon;
            border-radius: 9px;
        }


/* Style du bouton pour ouvrir le menu */
.menu-button {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1001;
    background-color: #333;
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    border-radius: 5px;
    font-size: 20px;
}


/* Style du menu latéral */
.sidebar {
    height: 100%;
    width: 250px;
    position: fixed;
    top: 0;
    /*left: -250px; /* Masquer le menu au début */
    background-color: #f8f9fa;
    overflow-x: hidden;
    transition: left 0.3s;
    z-index: 1000;
}

/* Lorsque le menu est actif */
.sidebar.active {
    left: 0; /* Faire glisser le menu */
}

/* Lorsque le menu est ouvert, le corps du site peut être déplacé */
body.sidebar-active {
    margin-left: 250px; /* Pousser le contenu à droite lorsque le menu est ouvert */
}
@media (max-width: 768px) {
    .sidebar {
        left: -250px;
    }

    .sidebar.active {
        left: 0;
    }

    .container.active{
        margin-left: 270px;
    }

    body.sidebar-active {
        margin-left: 0; /* Pas de décalage sur mobile */
    }

    .menu-button {
        display: block;
    }
    .container{
    margin-left: 0px;

    
}
}

@media (min-width: 769px) {
    .menu-button {
        display: none;
    }

    .sidebar {
        left: 0;
    }
}

    </style>
</head>






<body>

<?php
include("./assets/conn_bdd.php");

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Requête pour récupérer toutes les parties possibles
$sql_matches = "SELECT t1.id AS team1_id, t2.id AS team2_id, t1.serie, t1.poule
                FROM inscriptions t1
                JOIN inscriptions t2 ON t1.serie = t2.serie 
                                     AND t1.poule = t2.poule 
                                     AND t1.id < t2.id
                WHERE t1.forfait = '0' AND t2.forfait = '0'
                ORDER BY t1.serie, t1.poule, t1.id, t2.id";

$result_matches = $conn->query($sql_matches);

$unplaced_matches = [];

if ($result_matches->num_rows > 0) {
    while ($row = $result_matches->fetch_assoc()) {
        $team1_id = $row['team1_id'];
        $team2_id = $row['team2_id'];
        $serie = $row['serie'];
        $poule = $row['poule'];
        $partie = $team1_id . "/" . $team2_id;

        $sql_check = "SELECT * FROM calendrier WHERE partie = '$partie'";
        $result_check = $conn->query($sql_check);

        if ($result_check->num_rows == 0) {
            $unplaced_matches[] = $row;
        }
    }
}

$partie_implacee_number = count($unplaced_matches);

$sql_total = "SELECT COUNT(*) as total 
              FROM calendrier 
              WHERE score IS NULL 
                AND niveau = 1 
                AND jours < CURDATE() AND jours != '0000-00-00'";

$result_total = $conn->query($sql_total);
$totalParties = $result_total->fetch_assoc()['total'];

$conn->close();



$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Requête pour récupérer toutes les parties possibles
$sql_matches = "SELECT id FROM calendrier WHERE jours = '0000-00-00'";

$result_matches = $conn->query($sql_matches);

$partie_en_attente = 0;

if ($result_matches->num_rows > 0) {
    while ($row = $result_matches->fetch_assoc()) {

        $partie_en_attente += 1;
    }
}

$partie_implacee_number = count($unplaced_matches);


$conn->close();
?>


<button class="menu-button" id="menuButton">
    <i class="fas fa-bars"></i>
</button> 


<!-- Sidebar -->
<nav class="bg-light sidebar">
    <div class="sidebar-sticky">
        <ul class="nav flex-column">

        <!-- Top Menu -->
        <!-- Top Menu -->
        <div class="top-menu">
            <div class="menu-item" id="notificationButton">
                <i style="color:black;" class="fas fa-bell"></i>
                <div class="notification">
                    <span class="badge" id="notificationCount"></span>
                </div>
            </div>
            <div class="menu-item">
                <a href="../login/logout.php"><i style="color:black;" class="fas fa-sign-out-alt"></i></a>
            </div>

            <div class="menu-item">
                <a href="../backup/save_backup.php"><i style="color:black;" class="fas fa-save"></i></a>
            </div>

        </div>

        <div class="notification-popup" id="notificationPopup">
            <ul id="notificationList"></ul>
        </div>

        <?php if ($_SESSION['admin']['droits']['accueil']) { ?>
            <div id="start">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Accueil</a>
                </li>
            </div>
        <?php } ?>

        <?php if ($_SESSION['admin']['droits']['newsletter']) { ?>
            <div id="start">
                <li class="nav-item">
                    <a class="nav-link" href="newsletter.php">Newsletter</a>
                </li>
            </div>
        <?php } ?>

        <?php if ($_SESSION['admin']['droits']['sms']) { ?>
            <div id="start">
                <li class="nav-item">
                    <a class="nav-link" href="sms.php">SMS</a>
                </li>
            </div>
        <?php } ?>

        <?php if ($_SESSION['admin']['droits']['activite']) { ?>
            <div id="start">
                <li class="nav-item">
                    <a class="nav-link" href="show_activite.php">Activité Joueur</a>
                </li>
            </div>
        <?php } ?>

        <?php if ($_SESSION['admin']['droits']['paiement']) { ?>
            <div id="start">
                <li class="nav-item">
                    <a class="nav-link" href="edit_paiement.php">Paiements Joueurs</a>
                </li>
            </div>
        <?php } ?>
        <br>

        <?php if ($_SESSION['admin']['droits']['inscriptions'] || $_SESSION['admin']['droits']['poules'] || $_SESSION['admin']['droits']['calendrier'] || $_SESSION['admin']['droits']['classement'] || $_SESSION['admin']['droits']['partie_implacees'] || $_SESSION['admin']['droits']['partie_attente']) { ?>
        <div id="affich">
            <?php if ($_SESSION['admin']['droits']['inscriptions']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="show_inscriptions.php">Inscriptions</a>
                </li>
            <?php } ?>

            <?php if ($_SESSION['admin']['droits']['poules']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="show_series.php">Séries / Poules</a>
                </li>
            <?php } ?>



            <?php if ($_SESSION['admin']['droits']['calendrier']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="calendrier.php">Calendrier</a>
                </li>
            <?php } ?>

            <?php if ($_SESSION['admin']['droits']['classement']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="classement_poule.php">Classement instantanné</a>
                </li>
            <?php } ?>

            <?php if ($_SESSION['admin']['droits']['partie_implacees']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="partie_non_placee.php">
                        Parties implacées <div class="notification"><span class="badge"><?php echo $partie_implacee_number; ?></span></div>
                    </a>
                </li>
            <?php } ?>

            <?php if ($_SESSION['admin']['droits']['partie_attente']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="partie_en_attente.php">
                        Parties en attente <div class="notification"><span class="badge"><?php echo $partie_en_attente; ?></span></div>
                    </a>
                </li>
            <?php } ?>
        </div>
        <?php } ?>

        <br>

        <?php if ($_SESSION['admin']['droits']['gestion_tournoi'] || $_SESSION['admin']['droits']['gestion_finales'] || $_SESSION['admin']['droits']['arbre_tournoi']) { ?>
        <div id="gestion">
            <?php if ($_SESSION['admin']['droits']['gestion_tournoi']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="gestion_tournoi.php">Gestion tournoi</a>
                </li>
            <?php } ?>

            <?php if ($_SESSION['admin']['droits']['gestion_finales']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="gestion_phase_finales.php">Gestion Phases Finales</a>
                </li>
            <?php } ?>


            <?php if ($_SESSION['admin']['droits']['arbre_tournoi']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="edit_arbre.php">Arbre tournoi</a>
                </li>
            <?php } ?>
        </div>
        <?php } ?>
        

        <br>

        <?php if ($_SESSION['admin']['droits']['score_poules'] || $_SESSION['admin']['droits']['score_finales']) { ?>
        <div id="score">
            <?php if ($_SESSION['admin']['droits']['score_poules']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="complete_score.php">
                        Score Poules <div class="notification"><span class="badge"><?php echo $totalParties; ?></span></div>
                    </a>
                </li>
            <?php } ?>

            <?php if ($_SESSION['admin']['droits']['score_finales']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="score_phases_finales.php">Score Phases Finales</a>
                </li>
            <?php } ?>
        </div>
        <?php } ?>

        <br>

        <?php if ($_SESSION['admin']['droits']['creation_auto_poules'] || $_SESSION['admin']['droits']['creation_auto_calendrier']) { ?>
        <div id="create">
            <?php if ($_SESSION['admin']['droits']['creation_auto_poules']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="confirm_create_poules.php">Création auto poules</a>
                </li>
            <?php } ?>


            <?php if ($_SESSION['admin']['droits']['creation_auto_calendrier']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="confirm_create_calendrier.php">Création auto calendrier</a>
                </li>
            <?php } ?>
        </div>
        <?php } ?>


            <?php if ($_SESSION['admin']['droits']['edit_poules']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="drop_and_drag_poule.php">Séries / Poules Manuel</a>
                </li>
            <?php } ?>

        <br>

        <?php if ($_SESSION['admin']['droits']['barrages'] || $_SESSION['admin']['droits']['finales_start']) { ?>
        <div id="end">
            <?php if ($_SESSION['admin']['droits']['barrages']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="confirm_create_barrage.php">Barrages</a>
                </li>
            <?php } ?>

            <?php if ($_SESSION['admin']['droits']['finales_start']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="confirm_create_finales.php">Phases finales start</a>
                </li>
            <?php } ?>
        </div>
        <?php } ?>

        <br>

        <?php if ($_SESSION['admin']['droits']['app']) { ?>
            <li class="nav-item">
                <a class="nav-link" href="app.php">App</a>
            </li>
        <?php } ?>

        <?php if ($_SESSION['admin']['droits']['facturation']) { ?>
            <li class="nav-item">
                <a class="nav-link" href="facturation.php">Facturation</a>
            </li>
        <?php } ?>

        <?php if ($_SESSION['admin']['droits']['facturation_admin']) { ?>
            <li class="nav-item">
                <a class="nav-link" href="facturation_admin.php">Facturation Admin</a>
            </li>
        <?php } ?>

<br>

            <?php if ($_SESSION['admin']['droits']['gestion_arbitrage']) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="gestion_arbitrage.php">Gestion arbitrage</a>
                </li>
            <?php } ?>

        </ul>
    </div>
</nav>


<script>
    document.getElementById('notificationButton').addEventListener('click', function() {
        var popup = document.getElementById('notificationPopup');
        popup.classList.toggle('active');
    });

    document.addEventListener('click', function(event) {
        var isClickInside = document.getElementById('notificationButton').contains(event.target) || document.getElementById('notificationPopup').contains(event.target);
        if (!isClickInside) {
            document.getElementById('notificationPopup').classList.remove('active');
        }
    });

    window.onload = function() {
    // Fetch notifications with a unique timestamp to avoid caching
    fetch('assets/notifs.txt?timestamp=' + new Date().getTime())
        .then(response => response.text())
        .then(data => {
            const notifications = data.trim().split('\n').filter(line => line.trim() !== '');
            const notificationList = document.getElementById('notificationList');
            const notificationCount = document.getElementById('notificationCount');
            notificationCount.textContent = notifications.length;

            notificationList.innerHTML = '';  // Clear previous notifications
            notifications.forEach(notification => {
                const listItem = document.createElement('li');
                listItem.textContent = notification;
                notificationList.appendChild(listItem);
            });
        })
        .catch(error => console.error('Error fetching notifications:', error));
};


</script>

<script>
    document.getElementById('menuButton').addEventListener('click', function() {
        var sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('active');
        document.body.classList.toggle('sidebar-active');

        var container = document.querySelector('.container');
        container.classList.toggle('active');
        document.body.classList.toggle('container-active');
    });
</script>


</body>
</html>
