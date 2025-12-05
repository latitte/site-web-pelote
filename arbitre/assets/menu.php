
<!DOCTYPE html>
<html lang=fr>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="./assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Menu</title>
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

    <script>
        // Inclure le contenu du menu dynamiquement
        document.getElementById('custom-menu-container').innerHTML = `
            <div class="custom-menu">
                <a href="./accueil.php" id="custom-menu-tournoi"><i class="fa fa-trophy" aria-hidden="true"></i> Accueil</a>
                <a href="./remplissage_score.php" id="custom-menu-equipes"><i class="fa fa-user" aria-hidden="true"></i> Score</a>
                <a href="./live_score.php" id="custom-menu-live-score"><i class="fa fa-exchange" aria-hidden="true"></i> Live Score</a>
                <a href="./calendrier.php" id="custom-menu-calendar"><i class="fa fa-calendar" aria-hidden="true"></i> Calendrier</a>
                <a href="./add_inscription.php" id="custom-menu-arbitrage"><i class="fa fa-calendar" aria-hidden="true"></i> Arbitrage</a>
                <a href="./tableau_paiements.php" id="custom-menu-paiements"><i class="fa fa-money-bill" aria-hidden="true"></i> Paiements Joueurs</a>
                <a href="./tableau_paiements_equipe.php" id="custom-menu-paiements"><i class="fa fa-money-bill" aria-hidden="true"></i> Paiements Equipes</a>
                <!--<a href="../client/" id="custom-menu-client"><i class="fa fa-user" aria-hidden="true"></i> Interface Client</a>-->
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
                "accueil.php": "custom-menu-tournoi",
                "remplissage_score.php": "custom-menu-equipes",
                "live_score.php": "custom-menu-report",
                "calendrier.php": "custom-menu-calendar",
                "": "custom-menu-calendrier",
                "tableau_paiements": "custom-menu-paiements",
                "tableau_paiements_equipes": "custom-menu-paiements",
            };

            if (menuItems[page]) {
                document.getElementById(menuItems[page]).classList.add("custom-selected");
            }
        });
    </script>
</body>
</html>