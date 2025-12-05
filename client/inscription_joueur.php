<?php
// Détection de la langue à partir de l'URL, sinon défaut en français
$lang_code = isset($_GET['lang']) ? $_GET['lang'] : 'fr';

// Chargement du fichier de langue approprié
if ($lang_code == 'eus') {
    include("./assets/lang/lang_eus.php");
} else {
    include("./assets/lang/lang_fr.php");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8334034573564615"
     crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['title_inscription']; ?></title>
    <link rel="stylesheet" href="./assets/style.css">
    <link rel="icon" type="image/x-icon" href="./assets/tournoi-pelote.ico">


<style>

.content input {
    width: auto!important;
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
    <?php
    include("../logiciel/assets/extract_parametre.php");

    $ouverture_form = $parametres['openRegistration'];

    $jours_dispo_bdd = $parametres['jours_dispo'];
    $jours_disponibles = explode(", ", $jours_dispo_bdd);

    $heures_dispo_bdd = $parametres['heures_dispo'];
    $heures_dispo = explode(", ", $heures_dispo_bdd);

    $series = explode(",", $parametres['series']);
    $quota_series = explode(",", $parametres['quota_series']);


    try {
        // Connexion à la base de données avec PDO
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Requête SQL pour compter les inscriptions par série
        $sql = "SELECT serie, COUNT(*) AS nombre_inscriptions
                FROM inscriptions
                GROUP BY serie";
        $stmt = $pdo->query($sql);

        // Initialisation du tableau pour stocker les inscriptions par série
        $nbr_inscriptions_par_serie = [];

        // Récupération des résultats et affectation au tableau
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $serie = $row['serie'];
            $nombre_inscriptions = $row['nombre_inscriptions'];
            $nbr_inscriptions_par_serie[$serie] = $nombre_inscriptions;
        }
    } catch (PDOException $e) {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
    }
    ?>

    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Formulaire d'inscription</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                margin: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                /* background-color: #f8f9fa; */
            }
            .container {
                width: 100%;
                max-width: 600px;
                background: #fff;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                box-sizing: border-box;
                margin-left: 25%;
            }
            h2 {
                margin-bottom: 20px;
                font-size: 24px;
                color: #333;
                text-align: center;
            }
            label {
                display: block;
                margin-bottom: 8px;
                font-weight: bold;
                color: #555;
            }
            input[type="text"],
            input[type="tel"],
            select,
            input[type="date"] {
                width: 100% !important;
                padding: 10px;
                margin-bottom: 20px;
                border: 1px solid #ced4da;
                border-radius: 5px;
                font-size: 16px;
                background-color: #f8f9fa;
            }
            fieldset {
                border: 1px solid #ced4da;
                border-radius: 5px;
                padding: 20px;
                margin-bottom: 20px;
                margin-top: 20px;
            }
            legend {
                font-weight: bold;
                padding: 0 10px;
                color: #333;
            }
            .checkbox-group {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                margin-bottom: 10px;
            }
            .checkbox-group label {
                margin: 0;
                margin-right: 10px;
            }
            input[type="checkbox"] {
                margin-right: 5px;
            }
            .periode-wrapper {
                margin-bottom: 20px;
            }
            .periode-item {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }
            .periode-item input[type="date"] {
                width: 45%;
                margin-right: 10px;
            }
            .periode-item button {
                background-color: #dc3545;
                color: white;
                border: none;
                padding: 8px 12px;
                cursor: pointer;
                border-radius: 5px;
            }
            .periode-item button:hover {
                background-color: #c82333;
            }
            input[type="submit"] {
                background: #007bff;
                color: #fff;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                width: 100%;
                margin-top: 20px;
            }
            input[type="submit"]:hover {
                background: #0056b3;
            }

            @media (max-width: 768px) {
                .container{
                    margin-left: 0%;
                }
                .checkbox-group {
                    flex-direction: column;
                }
                .periode-item {
                    flex-direction: column;
                }
                .periode-item input[type="date"] {
                    width: 100%;
                    margin-right: 0;
                    margin-bottom: 10px;
                }
                .periode-item button {
                    width: 100%;
                    margin-top: 10px;
                }
            }
        </style>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var addPeriodeButton = document.getElementById('addPeriodeButton');
                var periodesWrapper = document.getElementById('periodesWrapper');
                var index = 1;

                addPeriodeButton.addEventListener('click', function() {
                    index++;
                    var newPeriodeItem = document.createElement('div');
                    newPeriodeItem.classList.add('periode-item');
                    newPeriodeItem.innerHTML = `
                        <input type="date" name="periode_indispo_debut_${index}">
                        <input type="date" name="periode_indispo_fin_${index}">
                        <button type="button" class="remove-periode">Supprimer</button>
                    `;
                    periodesWrapper.appendChild(newPeriodeItem);
                });

                periodesWrapper.addEventListener('click', function(event) {
                    if (event.target.classList.contains('remove-periode')) {
                        event.target.parentElement.remove();
                    }
                });
            });

            function validateForm() {
                // Ajoutez ici des validations supplémentaires si nécessaire
                return true;
            }
        </script>
    </head>
    <body>
        <div class="container" style="margin-top: 30px;">
        <?php
        if ($ouverture_form == 1) { ?>






            <form action="./add_inscription.php" method="post" onsubmit="return validateForm()">

<!-- ======== Encadré pub Titoan Lalanne ======== -->
<link rel="stylesheet" href="https://titoanlalanne.fr/pub/style.css">
<div class="card pub-card">
  <h3>💼 Développement web & solutions numériques locales</h3>
  <p>
Création de sites vitrines et boutiques en ligne, conception d’identité visuelle, installation réseau et automatisation de tâches.
Accompagnement des particuliers, associations et entreprises dans leur transition numérique avec des solutions modernes, personnalisées et accessibles.
  </p>
  <a href="https://latitte.titoanlalanne.fr" target="_blank" class="btn-pub">
    Découvrir mes services →
  </a>
</div>



<div style="margin-bottom: 20px;">
    <label for="recherche-joueur">🔍 Vous avez déjà participé ? Recherchez votre nom :</label>
    <input type="text" id="recherche-joueur" placeholder="Tapez votre nom..." style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc; margin-top:5px;">
    <div id="suggestions" style="background:white; border:1px solid #ccc; display:none; position:relative; z-index:1000;"></div>
</div>




                <h2><?php echo $lang['formulaire_inscription']; ?></h2>

                <label for="joueur1"><?php echo $lang['joueur1']; ?></label>
                <input placeholder="Nom prénom" type="text" id="joueur1" name="joueur1" required>

                <label for="joueur2"><?php echo $lang['joueur2']; ?></label>
                <input placeholder="Nom prénom" type="text" id="joueur2" name="joueur2" required>

                <label for="telephone"><?php echo $lang['equipes_telephone']; ?></label>
                <input placeholder="0123456789" type="tel" id="telephone" name="telephone" pattern="^0[67][0-9]{8}$" maxlength="10" inputmode="numeric" required title="Veuillez entrer un numéro de téléphone valide commençant par 06 ou 07, au format 0677757420">



                <label for="serie"><?php echo $lang['serie']; ?></label>
                <select id="serie" name="serie" required>
                    <?php
                    // Initialisation de la vérifie s'il reste des places dans les séries
                    $nbr_serie_open = 0;
                    foreach ($series as $index => $serie) {
                        $serie = trim($serie);
                        $quota = isset($quota_series[$index]) ? (int)$quota_series[$index] : 0;
                        $nombre_inscriptions = isset($nbr_inscriptions_par_serie[$serie]) ? $nbr_inscriptions_par_serie[$serie] : 0;

                        if ($nombre_inscriptions < $quota) {
                            echo "<option value=\"$serie\">$serie</option>";
                            $nbr_serie_open += 1;
                        }
                    }

                    if ($nbr_serie_open == 0) {
                        echo '<option value="">Aucune série disponible</option>';
                    }

                    ?>
                </select>

        <?php
        // Définition du tableau des jours complets
        $joursComplet = [
            "Lun" => $lang['lundi'],
            "Mar" => $lang['mardi'],
            "Mer" => $lang['mercredi'],
            "Jeu" => $lang['jeudi'],
            "Ven" => $lang['vendredi'],
            "Sam" => $lang['samedi'],
            "Dim" => $lang['dimanche']
        ];
        ?>

<style>
/* Styles existants */
fieldset {
    border: 1px solid #ddd;
    padding: 20px;
    border-radius: 10px;
    background-color: #ffffff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    max-width: 800px; /* Limite la largeur du champ pour ne pas qu'il soit trop large */
    margin: 0 auto; /* Centre le fieldset horizontalement */
    margin-top: 20px;
}

.day-group {
    margin-bottom: 20px;
}

.label {
    font-weight: bold;
    margin-bottom: 10px;
    display: block;
}

.time-slot-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center; /* Centre les créneaux dans le conteneur */
}

.time-slot {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100px;
    height: 40px;
    background-color: #4CAF50;
    color: white;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.time-slot.indisponible {
    background-color: #a0a0a0;
    color: #ffffff;
}

.time-slot:hover {
    background-color: #45a049;
}

/* Styles pour le format mobile */
@media (max-width: 768px) {
    .time-slot-container {
        flex-direction: column;
        gap: 5px;
    }

    .time-slot {
        width: 100%; /* Chaque créneau prend toute la largeur */
        height: 50px; /* Augmentez la hauteur pour les appareils mobiles */
    }
}


</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const timeSlots = document.querySelectorAll('.time-slot');
    const recapLink = document.getElementById('recapIndisponibilites');

    // Créneaux à bloquer par défaut
    const defaultIndispos = [
        { day: 'Mer', time: '18h15' },
        { day: 'Ven', time: '18h15' },
        { day: 'Ven', time: '19h30' }
    ];

    function updateRecap() {
        let recapText = 'Indisponibilités sélectionnées : \n';
        const selectedSlots = [];

        timeSlots.forEach(slot => {
            const day = slot.getAttribute('data-day');
            const time = slot.getAttribute('data-time');
            const checkbox = document.querySelector(`#indispo_${day}_${time}`);

            if (checkbox && checkbox.checked) {
                selectedSlots.push(`${day} à ${time}`);
            }
        });

        recapText += selectedSlots.length > 0 ? selectedSlots.join(', ') : 'Aucune';
        if (recapLink) recapLink.innerText = recapText;
    }

    timeSlots.forEach(slot => {
        const day = slot.getAttribute('data-day');
        const time = slot.getAttribute('data-time');
        const checkbox = document.querySelector(`#indispo_${day}_${time}`);

        // Bloquer par défaut si dans la liste
        const isDefault = defaultIndispos.some(x => x.day === day && x.time === time);
        if (isDefault) {
            slot.classList.add('indisponible');
            slot.innerText = 'Indisponible';
            slot.style.cursor = 'not-allowed';
            slot.dataset.locked = 'true';
            if (checkbox) checkbox.checked = true;
        }

        // Clic uniquement si non verrouillé
        slot.addEventListener('click', function() {
            if (this.dataset.locked === 'true') return;

            if (checkbox && checkbox.checked) {
                checkbox.checked = false;
                this.classList.remove('indisponible');
                this.innerText = time;
            } else if (checkbox) {
                checkbox.checked = true;
                this.classList.add('indisponible');
                this.innerText = 'Indisponible';
            }
            updateRecap();
        });
    });

    updateRecap();
});
</script>


        <fieldset>
            <legend>Indisponibilités</legend>
            <?php foreach ($jours_disponibles as $jour_abrege): ?>
                <div class="day-group">
                    <label><?php echo $joursComplet[$jour_abrege]; ?>:</label>
                    <div class="time-slot-container">
                        <?php foreach ($heures_dispo as $heure): ?>
                            <div class="time-slot" data-day="<?php echo $jour_abrege; ?>" data-time="<?php echo $heure; ?>">
                                <?php echo $heure; ?>
                            </div>
                            <input type="checkbox" id="indispo_<?php echo $jour_abrege . "_" . $heure; ?>" name="indispo_<?php echo $jour_abrege; ?>[]" value="<?php echo $heure; ?>" style="display: none;">
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>


            <a style="text-decoration: none; color: black;" id="recapIndisponibilites" href="#"></a>

        </fieldset>

                <fieldset>
                    <legend><?php echo $lang['periode_indispo']; ?></legend>
                    <div id="periodesWrapper">
                        <div class="periode-item">
                            <input type="date" name="periode_indispo_debut_1">
                            <input type="date" name="periode_indispo_fin_1">
                            <button type="button" class="remove-periode">Supprimer</button>
                        </div>
                    </div>
                    <button type="button" id="addPeriodeButton">Ajouter une période</button>
                </fieldset>


                <p>Les inscriptions doivent être payées avant la première partie par virement bancaire<br><a href="./assets/RIB_sorginen_txapelketa.pdf">Information</a></p>


                <?php
                if($nbr_serie_open != 0){
                ?>
                    <input type="submit" value="S'inscrire">
                <?php
                }else{
                ?>
                    <input style="margin-top: 20px;" type="button" value="Plus de places disponibles">
                <?php
                }
                ?>
            </form>
        <?php
        } else { ?>
            <h2><?php echo $lang['inscriptions_close']; ?></h2>
        <?php
        }
        ?>
        </div>


<script>
document.addEventListener("DOMContentLoaded", () => {
    const input = document.getElementById("recherche-joueur");
    const suggestionsBox = document.getElementById("suggestions");

    input.addEventListener("input", () => {
        const query = input.value.trim();
        if (query.length < 2) {
            suggestionsBox.style.display = "none";
            suggestionsBox.innerHTML = "";
            return;
        }

        fetch(`./search_joueurs_list.php?query=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                suggestionsBox.innerHTML = "";
                if (data.length === 0) {
                    suggestionsBox.style.display = "none";
                    return;
                }

                data.forEach(joueur => {
                    const div = document.createElement("div");
                    div.textContent = joueur.nom + " " + joueur.prenom + " – " + joueur.serie;
                    div.style.padding = "8px";
                    div.style.cursor = "pointer";
                    div.style.borderBottom = "1px solid #eee";
                    div.addEventListener("click", () => {
                        document.getElementById("joueur1").value = joueur.nom + " " + joueur.prenom;
                        if (joueur.serie) {
                            document.getElementById("serie").value = joueur.serie.split(",")[0].trim();
                        }
                        suggestionsBox.innerHTML = "";
                        suggestionsBox.style.display = "none";
                        input.value = joueur.nom + " " + joueur.prenom;
                    });
                    suggestionsBox.appendChild(div);
                });

                suggestionsBox.style.display = "block";
            });
    });

    document.addEventListener("click", (e) => {
        if (!suggestionsBox.contains(e.target) && e.target !== input) {
            suggestionsBox.style.display = "none";
        }
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  // Utilitaire: crée une box de suggestions sous un input
  function createSuggestionBox(afterEl, id) {
    const box = document.createElement("div");
    box.id = id;
    box.style.background = "white";
    box.style.border = "1px solid #ccc";
    box.style.display = "none";
    box.style.position = "relative";
    box.style.zIndex = "1000";
    box.style.maxHeight = "220px";
    box.style.overflowY = "auto";
    afterEl.insertAdjacentElement("afterend", box);
    return box;
  }

  function wireAutocompleteForInput(inputEl, boxId, alsoSetSerie = true) {
    if (!inputEl) return;
    const box = createSuggestionBox(inputEl, boxId);

    inputEl.addEventListener("input", () => {
      const q = inputEl.value.trim();
      if (q.length < 2) {
        box.style.display = "none";
        box.innerHTML = "";
        return;
      }

      fetch(`./search_joueurs_list.php?query=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => {
          box.innerHTML = "";
          if (!Array.isArray(data) || data.length === 0) {
            box.style.display = "none";
            return;
          }

          data.forEach(j => {
            const div = document.createElement("div");
            div.textContent = `${j.nom} ${j.prenom} – ${j.serie ?? ''}`;
            div.style.padding = "8px";
            div.style.cursor = "pointer";
            div.style.borderBottom = "1px solid #eee";
            div.addEventListener("click", () => {
              inputEl.value = `${j.nom} ${j.prenom}`;

              // Met à jour la série si demandé et si disponible
              if (alsoSetSerie && j.serie) {
                const serieSelect = document.getElementById("serie");
                if (serieSelect) {
                  const serieValue = String(j.serie).split(",")[0].trim();
                  for (const opt of serieSelect.options) {
                    if (opt.value === serieValue) {
                      serieSelect.value = serieValue;
                      break;
                    }
                  }
                }
              }

              box.innerHTML = "";
              box.style.display = "none";
            });
            box.appendChild(div);
          });

          box.style.display = "block";
        })
        .catch(() => {
          box.style.display = "none";
          box.innerHTML = "";
        });
    });

    // Fermer si clic à l'extérieur
    document.addEventListener("click", (e) => {
      if (e.target !== inputEl && !box.contains(e.target)) {
        box.style.display = "none";
      }
    });
  }

  // Branche les deux champs
  wireAutocompleteForInput(document.getElementById("joueur1"), "suggestions_j1", true);
  wireAutocompleteForInput(document.getElementById("joueur2"), "suggestions_j2", true);
});
</script>




    </body>
    </html>





    <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message de Contact</title>
    <style>


        .container {
            display: flex;
            justify-content: center;
            align-items: center;

        }

        .message-box {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
            width: 300px;
            color: #333;
        }

        .message-box h2 {
            color: #007aff;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .message-box p {
            font-size: 16px;
            line-height: 1.5;
        }

        .message-box a {
            color: #007aff;
            text-decoration: none;
            font-weight: bold;
        }

        .message-box a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container" style="margin-top: 20px;">
        <div class="message-box">
            <h2>Problème ?</h2>
            <p>Si vous rencontrez un problème sur le site, veuillez contacter :</p>
            <p><a href="mailto:admin@tournoi-pelote.com">admin@tournoi-pelote.com</a></p>
        </div>
    </div>
</body>
</html>





<footer>

<?php include("./assets/footer.php"); ?>


</footer>

    </div>
    
</div>

</body>


</html>