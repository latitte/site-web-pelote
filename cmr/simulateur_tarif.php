<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Simulateur Tarif Tournoi</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      background-color: #f2f2f7;
      padding: 20px;
      margin: 0;
    }
    .container {
      max-width: 600px;
      background: #fff;
      margin: auto;
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      font-weight: 600;
      margin-bottom: 30px;
    }
    .form-group {
      margin-bottom: 20px;
    }
    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
    }
    input, textarea {
      width: 100%;
      padding: 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 10px;
      box-sizing: border-box;
    }
    button {
      background-color: #007aff;
      color: white;
      padding: 14px;
      font-size: 16px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      width: 100%;
      margin-top: 20px;
    }
    button:hover {
      background-color: #005bb5;
    }
    .result {
      margin-top: 25px;
      font-size: 20px;
      font-weight: 600;
      text-align: center;
      color: #333;
    }
    .subinfo {
      font-size: 15px;
      text-align: center;
      color: #666;
      margin-top: 8px;
    }
    .cost {
      margin-top: 12px;
      font-size: 17px;
      font-weight: 600;
      text-align: center;
      color: #007aff;
      display: none; /* caché par défaut */
    }
    .url-result {
      margin-top: 25px;
      font-size: 18px;
      font-weight: 500;
      text-align: center;
      color: #007aff;
    }
  </style>
</head>
<body>

<div class="container">
  <a href="javascript:history.back()" style="display:block; text-align:center; margin-top:20px; text-decoration:none;">
    <button type="button" style="background-color:#ccc; color:#333; border:none; padding:12px 20px; border-radius:10px; font-size:16px; cursor:pointer;">
      ⬅ Retour
    </button>
  </a>

  <h2>Simulateur Tarif Tournoi</h2>

  <div class="form-group">
    <label for="tournamentName">Nom du tournoi</label>
    <input type="text" id="tournamentName" placeholder="Entrez le nom de votre tournoi" oninput="generateURL(); updatePrice();">
  </div>
  <div class="form-group">
    <label for="players">Nombre de joueurs</label>
    <input type="number" id="players" min="2" value="120" oninput="updatePrice()">
  </div>
  <div class="form-group">
    <label for="poules">Nombre de poules</label>
    <input type="number" id="poules" min="1" value="10" oninput="updatePrice()">
  </div>
  <div class="form-group">
    <label for="series">Nombre de séries</label>
    <input type="number" id="series" min="1" value="3" oninput="updatePrice()">
  </div>
  <div class="form-group">
    <label for="months">Durée du tournoi (en mois)</label>
    <input type="number" id="months" min="1" value="3" oninput="updatePrice()">
  </div>
  <div class="form-group">
    <label for="entryFee">Prix d'inscription par joueur (€)</label>
    <input type="number" id="entryFee" min="1" value="20" oninput="updatePrice()">
  </div>
  <div class="form-group">
    <label for="referees">Nombre d'arbitres</label>
    <input type="number" id="referees" min="1" value="3" oninput="updatePrice()">
  </div>
  <div class="form-group">
    <label for="admins">Nombre de comptes administrateurs</label>
    <input type="number" id="admins" min="1" value="2" oninput="updatePrice()">
  </div>

  <div class="result" id="priceResult">Prix total : €</div>
  <div class="cost" id="costResult">Coût réel (sans marge) : €</div>
  <div class="subinfo" id="detailsResult"></div>
  <div class="url-result" id="urlResult">URL du tournoi : </div>
</div>

<form action="enregistrer_demande.php" method="POST" class="container" style="margin-top:40px;">
  <h2>Demande de création de tournoi</h2>
  <div class="form-group"><label>Nom</label><input type="text" name="nom" required></div>
  <div class="form-group"><label>Prénom</label><input type="text" name="prenom" required></div>
  <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
  <div class="form-group"><label>Téléphone</label><input type="text" name="telephone" required></div>
  <div class="form-group"><label>Description du tournoi</label><textarea name="description" rows="4"></textarea></div>

  <input type="hidden" name="nom_tournoi" id="hiddenName">
  <input type="hidden" name="joueurs" id="hiddenPlayers">
  <input type="hidden" name="poules" id="hiddenPoules">
  <input type="hidden" name="series" id="hiddenSeries">
  <input type="hidden" name="duree_mois" id="hiddenMonths">
  <input type="hidden" name="prix_inscription" id="hiddenEntryFee">
  <input type="hidden" name="arbitres" id="hiddenReferees">
  <input type="hidden" name="admins" id="hiddenAdmins">
  <input type="hidden" name="prix_total" id="hiddenTotal">
  <input type="hidden" name="cout_reel" id="hiddenCost">
  <input type="hidden" name="url_tournoi" id="hiddenURL">
  <button type="submit">Envoyer ma demande</button>
</form>

<script>
const basePrice = 0;

function updatePrice() {
  const players = parseInt(document.getElementById('players').value);
  const poules = parseInt(document.getElementById('poules').value);
  const series = parseInt(document.getElementById('series').value);
  const months = parseInt(document.getElementById('months').value);
  const juges = parseInt(document.getElementById('referees').value);
  const nomTournoi = document.getElementById('tournamentName').value.trim().toLowerCase();

  // --- Calcul moyenne / parties / SMS ---
  const moyenne = players / poules;
  const partiesParPoule = (moyenne * (moyenne - 1)) / 2;
  const totalPartiesPoules = partiesParPoule * poules;
  const totalParties = totalPartiesPoules + (series * 14) + 50;
  const coefJoueurs = 1 / 2;
  const coefEquipes = 2;
  const nbSMS = totalParties * coefJoueurs * coefEquipes;

  // --- Coûts réels ---
  const coutSMS = nbSMS * 0.043;
  const coutSMSmessage = players * 0.043;
  const coutHebergement = months * 15;
  const coutDNS = 10;
  const arbitre = (months * 30) * 0.043;
  const coutReel = basePrice + coutSMS + coutHebergement + coutDNS + arbitre + coutSMSmessage;

  // --- Prix client (avec marge fictive ici x2 pour exemple) ---
  const price = coutReel * 2;

  // --- Affichage ---
  document.getElementById('priceResult').innerHTML = "Prix total : €" + price.toFixed(2);
  document.getElementById('detailsResult').innerHTML =
    `Moyenne : ${moyenne.toFixed(2)} joueurs/poule · ${Math.round(totalParties)} parties · ${Math.round(nbSMS)} SMS`;

  // --- Afficher le coût réel uniquement pour "admintitoan" ---
  const costEl = document.getElementById('costResult');
  if (nomTournoi === "admintitoan") {
    costEl.style.display = "block";
    costEl.innerHTML = "Coût réel (sans marge) : €" + coutReel.toFixed(2);
  } else {
    costEl.style.display = "none";
  }

  // --- Champs cachés ---
  updateHiddenFields(price, coutReel);
}

function updateHiddenFields(price, coutReel) {
  document.getElementById('hiddenName').value = document.getElementById('tournamentName').value;
  document.getElementById('hiddenPlayers').value = document.getElementById('players').value;
  document.getElementById('hiddenPoules').value = document.getElementById('poules').value;
  document.getElementById('hiddenSeries').value = document.getElementById('series').value;
  document.getElementById('hiddenMonths').value = document.getElementById('months').value;
  document.getElementById('hiddenTotal').value = price.toFixed(2);
  document.getElementById('hiddenCost').value = coutReel.toFixed(2);

  const urlText = document.getElementById('urlResult').textContent;
  const url = urlText.replace('URL du tournoi : ', '').trim();
  document.getElementById('hiddenURL').value = url;
}

function generateURL() {
  const tournamentName = document.getElementById('tournamentName').value.trim();
  if (tournamentName) {
    const formattedName = tournamentName.toLowerCase().replace(/\s+/g, '-');
    document.getElementById('urlResult').innerHTML = `URL du tournoi : ${formattedName}.tournoi-pelote.com`;
  } else {
    document.getElementById('urlResult').innerHTML = "URL du tournoi : ";
  }
}

updatePrice();
</script>
</body>
</html>
