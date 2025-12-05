<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire Interactif</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 10vh;
            margin: 0;
        }

        #form-container {
            position: relative;
            width: 400px;
            max-width: 100%;
        }

        .popup {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            /* width: 100%; */
            padding: 40px;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            text-align: center;
            transition: opacity 0.3s ease, transform 0.3s ease;
            opacity: 0;
            transform: translateY(-10px);
        }

        .popup.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .popup h2 {
            margin-top: 0;
            color: #1d1d1f;
            font-weight: 600;
        }

        .popup p {
            color: #6e6e73;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .popup input[type="text"],
        .popup input[type="date"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        .player-button {
            background-color: white;
            color: #000000;
            border: 2px solid #000000;
            padding: 12px 24px;
            cursor: pointer;
            border-radius: 12px;
            font-size: 16px;
            transition: background-color 0.3s ease, color 0.3s ease;
            margin: 10px;
        }

        .player-button:hover {
            background-color: #000000;
            color: white;
        }

        button {
            background-color: #007aff;
            color: white;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            border-radius: 12px;
            font-size: 16px;
            transition: background-color 0.3s ease;
            margin: 10px;
        }

        button:hover {
            background-color: #005bb5;
        }
    </style>
    <!-- Inclure jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div id="form-container">
        <form id="interactive-form" onsubmit="return false;">
            <!-- Popup 1 -->
            <div class="popup" id="popup1">
                <h2>Je dois vous identifier 😁</h2>
                <p>Quel est votre code d'équipe ?</p>
                <input type="text" id="teamCode" name="teamCode" required>
                <button type="button" onclick="fetchTeamData()">Suivant</button>
            </div>

            <!-- Popup 2 -->
            <div class="popup" id="popup2">
                <button type="button" id="btnJoueur3">Numéro équipe</button>
                <h2>Informations de l'équipe</h2>
                <p id="teamData">Qui es-tu ?</p>
                <button type="button" id="btnJoueur1" class="player-button" onclick="fetchParties('joueur1')">Joueur 1</button>
                <button type="button" id="btnJoueur2" class="player-button" onclick="fetchParties('joueur2')">Joueur 2</button>
            </div>

            <!-- Popup 3 -->
            <div class="popup" id="popup3">
                <h2>Choisissez une partie</h2>
                <p>Choisissez une partie de votre équipe :</p>
                <div id="partiesContainer"></div>
            </div>

            <!-- Popup 4 -->
            <div class="popup" id="popup4">
                <h2>Détails de la partie</h2>
                <div id="detailsPartie"></div>
                <label for="availableSlots">Créneaux disponibles :</label>
                <select id="availableSlots" name="availableSlots"></select>

                <button type="button" onclick="submitForm()">Soumettre</button>
            </div>

        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Affiche le premier popup initialement
            document.getElementById("popup1").classList.add("active");
        });

        function showNextPopup(nextPopupId) {
            // Cache tous les popups
            const popups = document.querySelectorAll(".popup");
            popups.forEach(popup => {
                popup.classList.remove("active");
            });

            // Affiche le prochain popup
            document.getElementById(nextPopupId).classList.add("active");
        }

        function fetchTeamData() {
            const teamCode = document.getElementById('teamCode').value;
            if (teamCode) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'fetch_team_data.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            document.getElementById('btnJoueur1').textContent = response.joueur1;
                            document.getElementById('btnJoueur2').textContent = response.joueur2;
                            document.getElementById('btnJoueur3').textContent = "Equipe " + response.numequipe;
                            showNextPopup('popup2');
                        } else {
                            alert(response.message);
                        }
                    }
                };
                xhr.send('teamCode=' + encodeURIComponent(teamCode));
            }
        }

        function fetchParties(playerId) {
    // Récupère le numéro d'équipe à partir du bouton 'Numéro équipe'
    const teamNumber = document.getElementById('btnJoueur3').textContent.replace('Equipe ', '');

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'fetch_parties.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                displayParties(response.parties);
            } else {
                alert(response.message);
            }
        }
    };

    // Envoyer à la fois numequipe et joueur
    xhr.send('numequipe=' + encodeURIComponent(teamNumber) + '&joueur=' + encodeURIComponent(playerId));
}



function displayParties(parties) {
    const partiesContainer = document.getElementById('partiesContainer');
    partiesContainer.innerHTML = '';

    parties.forEach(partie => {
        const button = document.createElement('button');
        button.textContent = `${partie.jours} à ${partie.heure} - ${partie.partie}`;
        button.classList.add('player-button');
        button.addEventListener('click', function() {
            document.getElementById('detailsPartie').textContent = `Détails de la partie sélectionnée : ${partie.jours} à ${partie.heure} - ${partie.partie} (ID: ${partie.id})`;
            fetchAvailableSlots(partie.jours);
            showNextPopup('popup4');
        });
        partiesContainer.appendChild(button);
    });

    showNextPopup('popup3'); // Affiche le popup des parties après le chargement des boutons
}


        function fetchAvailableSlots(date) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'fetch_available_slots.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        populateAvailableSlots(response.slots);
                    } else {
                        alert(response.message);
                    }
                }
            };
            xhr.send('date=' + encodeURIComponent(date));
        }

        function populateAvailableSlots(slots) {
    const slotsDropdown = document.getElementById('availableSlots');
    slotsDropdown.innerHTML = '';

    slots.forEach(slot => {
        const option = document.createElement('option');
        option.value = `${slot.date} à ${slot.heure}`; // Utilisation de 'date' et 'heure' comme valeur de l'option
        option.textContent = `${slot.date} à ${slot.heure}`;
        option.setAttribute('data-partie-id', slot.partieId); // Ajoute l'attribut data-partie-id
        slotsDropdown.appendChild(option);
    });
}

function extractPartieId(partieDetails) {
    const regex = /Détails de la partie sélectionnée : (\d+)/; // Regex pour récupérer l'ID de la partie
    const match = regex.exec(partieDetails);
    if (match && match.length > 1) {
        return match[1];
    } else {
        return null; // Gestion d'erreur ou retourner l'ID correctement
    }
}

function submitForm() {
    // Récupérer l'ID de la partie
    const partieDetails = document.getElementById('detailsPartie').textContent;
    const partieId = extractPartieId(partieDetails); // Utilisation de la fonction extractPartieId

    // Récupérer la date et l'heure sélectionnées dans le menu déroulant
    const selectElement = document.getElementById('availableSlots');
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const dateTimeString = selectedOption.textContent;

    // Séparation de la date et de l'heure à partir de la chaîne formatée "YYYY-MM-DD à HH:mm"
    const dateTimeParts = dateTimeString.split(' à ');
    const newDate = dateTimeParts[0];
    const newHeure = dateTimeParts[1];

    // Envoi des données vers le script PHP via AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_partie_date.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                alert('Date et heure mises à jour avec succès !');
                // Actions supplémentaires après la mise à jour réussie
            } else {
                alert('Erreur lors de la mise à jour de la date et heure : ' + response.message);
            }
        }
    };
    xhr.send('partieId=' + encodeURIComponent(partieId) + '&newDate=' + encodeURIComponent(newDate) + '&newHeure=' + encodeURIComponent(newHeure));
}









function extractPartieId(partieDetails) {
    const regex = /Détails de la partie sélectionnée : .* \(ID: (\d+)\)/; // Regex pour extraire l'ID
    const match = regex.exec(partieDetails);
    if (match && match.length > 1) {
        return match[1]; // Retourne le premier groupe capturé (l'ID de la partie)
    } else {
        return null; // Retourne null si l'ID n'est pas trouvé ou si le format ne correspond pas
    }
}


    </script>
</body>
</html>
