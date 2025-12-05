<?php
include("./extract_parametre.php");

$heures_dispo_bdd = $parametres['heures_dispo'];
$heure1 = explode(", ", $heures_dispo_bdd);

include("./conn_bdd.php");
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? $_GET['id'] : null;
$jours = isset($_GET['jour']) ? $_GET['jour'] : "";
$heure = isset($_GET['heure']) ? $_GET['heure'] : "";

$partie = "";
$score = "";
$niveau = "";
$commentaire = "";

if ($id) {
    $sql = "SELECT * FROM calendrier WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $jours = $row['jours'];
        $heure = $row['heure'];
        $partie = $row['partie'];
        $score = $row['score'];
        $niveau = $row['niveau'];
        $commentaire = $row['commentaire'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier la Partie</title>
    <style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 100%;
        max-width: 650px;
        margin: 30px auto;
        background: #fff;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        box-sizing: border-box;
    }

    h2 {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 24px;
        text-align: center;
        color: #333;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 18px;
        color: #222;
    }

    .form-group input[type="text"],
    .form-group input[type="date"],
    .form-group input[type="time"],
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 14px;
        border: 1px solid #ccc;
        border-radius: 10px;
        font-size: 17px;
        box-sizing: border-box;
        background-color: #fdfdfd;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .form-group input[type="submit"],
    .form-group input[type="button"] {
        display: block;
        width: 100%;
        background-color: #007aff;
        color: #fff;
        padding: 14px;
        border: none;
        border-radius: 10px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .form-group input[type="submit"]:hover,
    .form-group input[type="button"]:hover {
        background-color: #005bb5;
    }

    .btn-back {
        display: block;
        width: 100%;
        margin-top: 16px;
        padding: 14px;
        background-color: #dcdcdc;
        color: #333;
        text-align: center;
        text-decoration: none;
        border-radius: 10px;
        font-size: 17px;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }

    .btn-back:hover {
        background-color: #bbb;
    }

    .form-group .btn-delete {
        background-color: #ff3b30;
    }

    .form-group .btn-delete:hover {
        background-color: #c82333;
    }

    @media (max-width: 480px) {
        h2 {
            font-size: 22px;
        }

        .container {
            padding: 18px;
        }

        .form-group input[type="submit"],
        .form-group input[type="button"],
        .btn-back {
            font-size: 16px;
            padding: 12px;
        }
    }

.btn-primary {
    display: block;
    width: 100%;
    background-color: #007aff;
    color: #fff;
    padding: 14px;
    border: none;
    border-radius: 10px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #005bb5;
}

.btn-primary:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
}


</style>
<script>
function toggleTextarea() {
    var checkbox = document.getElementById("en_attente");
    var commentaireDiv = document.getElementById("commentaire_div");
    var textarea = document.getElementById("commentaire");

    if (checkbox.checked) {
        commentaireDiv.style.display = "block";
        textarea.required = true;
    } else {
        commentaireDiv.style.display = "none";
        textarea.required = false;
    }
}
</script>
</head>
<body>
<div class="container">
    <h2>Modifier la Partie</h2>
    <form action="update_calendrier.php" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <div class="form-group">
            <label for="jours">Jour:</label>
            <input type="date" id="jours" name="jours" value="<?php echo htmlspecialchars($jours ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

<div class="form-group">
    <label for="heure">Heure:</label>
    <select id="heure" name="heure_select" required>
        <?php
            $heure_existe = in_array($heure, $heure1);
            foreach ($heure1 as $h) {
                $selected = ($heure === $h) ? 'selected' : '';
                echo "<option value=\"$h\" $selected>$h</option>";
            }
        ?>
        <option value="autre" <?php echo !$heure_existe && $heure ? 'selected' : ''; ?>>Autre horaire</option>
    </select>
<input type="text" id="heure_autre" placeholder="Saisir un horaire - Format : 11h00" style="margin-top:10px; display:<?php echo (!$heure_existe && $heure) ? 'block' : 'none'; ?>;" value="<?php echo !$heure_existe ? htmlspecialchars($heure) : ''; ?>">
<input type="hidden" name="heure" id="heure_final" value="<?php echo htmlspecialchars($heure ?? '', ENT_QUOTES, 'UTF-8'); ?>">

</div>


        <div class="form-group">
            <label for="partie">Partie:</label>
            <div style="display: flex;gap: 10px;flex-direction: column;">
                <input type="text" id="partie" name="partie" value="<?php echo htmlspecialchars($partie ?? '', ENT_QUOTES, 'UTF-8'); ?>" required style="flex: 1;">
                <button type="button" onclick="chercherCreneaux()" class="btn-primary" style="flex-shrink: 0;">Chercher un créneau disponible</button>
            </div>
            <div id="resultats-creneaux" style="margin-top: 15px; font-size: 16px; color: #333;"></div>
        </div>


        <div class="form-group">
            <label for="score">Score:</label>
            <input type="text" id="score" name="score" value="<?php echo htmlspecialchars($score ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="form-group">
            <label for="niveau">Niveau:</label>
            <input type="text" id="niveau" name="niveau" placeholder="1: qualification | 2: barrage" value="<?php echo htmlspecialchars($niveau ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="form-group">
            <label for="en_attente">
                <input type="checkbox" id="en_attente" name="en_attente" value="1" onclick="toggleTextarea()"> Mettre la partie en attente
            </label>
        </div>

        <div class="form-group" id="commentaire_div" style="display:none;">
            <label for="commentaire">Commentaire:</label>
            <textarea id="commentaire" name="commentaire" rows="4" cols="50"><?php echo htmlspecialchars($commentaire ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <div class="form-group">
            <button type="submit" id="submit-btn" class="btn-primary" disabled>Enregistrer</button>
            <p id="check-msg" style="margin-top:10px;"></p>
        </div>

    </form>

    <?php if ($id): ?>
    <form action="delete_calendrier.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette partie ?');">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="form-group">
            <input type="submit" value="Supprimer" class="btn-delete">
        </div>
    </form>
    <?php endif; ?>

    <a href="../calendrier.php" class="btn-back">Retour</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('jours');
    const heureSelect = document.getElementById('heure');
    const heureAutreInput = document.getElementById('heure_autre');
    const heureFinalInput = document.getElementById('heure_final');
    const submitBtn = document.getElementById('submit-btn');
    const msg = document.getElementById('check-msg');
    const partId = "<?php echo $id ?? ''; ?>";

    function getHeureValue() {
        return heureSelect.value === 'autre' ? heureAutreInput.value : heureSelect.value;
    }

    function toggleHeureAutre() {
        if (heureSelect.value === 'autre') {
            heureAutreInput.style.display = 'block';
            heureAutreInput.required = true;
        } else {
            heureAutreInput.style.display = 'none';
            heureAutreInput.required = false;
            heureAutreInput.value = '';
        }

        // Mettre à jour le champ caché
        heureFinalInput.value = getHeureValue();
    }

    async function checkDispo() {
        const jour = dateInput.value;
        const heureValue = getHeureValue();

        heureFinalInput.value = heureValue; // met à jour avant envoi

        if (!jour || !heureValue) {
            submitBtn.disabled = true;
            msg.textContent = "";
            return;
        }

        msg.textContent = "Vérification du créneau...";
        submitBtn.disabled = true;

        const response = await fetch('verifier_creneau.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `jours=${encodeURIComponent(jour)}&heure=${encodeURIComponent(heureValue)}&id=${encodeURIComponent(partId)}`
        });

        const result = await response.text();
        console.log("Réponse serveur:", result);

        if (result.trim() === "libre") {
            msg.textContent = "✅ Créneau libre";
            submitBtn.disabled = false;
        } else {
            msg.textContent = "❌ Créneau déjà réservé";
            submitBtn.disabled = true;
        }
    }

    heureSelect.addEventListener('change', () => {
        toggleHeureAutre();
        checkDispo();
    });

    dateInput.addEventListener('change', checkDispo);
    heureAutreInput.addEventListener('input', checkDispo);

    // Initialisation
    toggleHeureAutre();
    checkDispo();
});
</script>



<script>
async function chercherCreneaux() {
    const partie = document.getElementById("partie").value;
    const resultatDiv = document.getElementById("resultats-creneaux");
    resultatDiv.innerHTML = "🔄 Recherche des créneaux disponibles...";

    const match = partie.match(/^(\d+)\/(\d+)$/);
    if (!match) {
        resultatDiv.innerHTML = "❌ Format invalide. Format attendu : id1/id2.";
        return;
    }

    const [_, team1_id, team2_id] = match;

    try {
        const res = await fetch(`../../client/api_dispo_equipe.php?team1_id=${team1_id}&team2_id=${team2_id}`);
        const data = await res.json();

        if (data.error) {
            resultatDiv.innerHTML = "❌ Erreur : " + data.error;
            return;
        }

        if (data.length === 0) {
            resultatDiv.innerHTML = "❌ Aucun créneau commun disponible.";
            return;
        }

        let html = "<strong>Créneaux disponibles :</strong><ul>";
        data.flat().forEach(slot => {
            html += `<li>${slot}</li>`;
        });
        html += "</ul>";

        resultatDiv.innerHTML = html;
    } catch (e) {
        resultatDiv.innerHTML = "❌ Une erreur est survenue lors de la récupération des créneaux.";
        console.error(e);
    }
}
</script>


</body>
</html>

<?php $conn->close(); ?>
