<?php
// fichier : logiciel/facturation.php

include("./assets/conn_bdd.php");

// Connexion
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Erreur : " . $conn->connect_error);

$message = "";

// ------------ SUPPRESSION ------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_facture'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM facturation WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $message = "Facture supprimée.";
}

// ------------ AJOUT / EDITION ------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_facture'])) {
    $id          = (int)$_POST['id'];
    $libelle     = trim($_POST['libelle']);
    $montant     = (float)$_POST['montant_ttc'];
    $dateF       = $_POST['date_facture'];
    $statut      = $_POST['statut'];
    $resume      = trim($_POST['resume_court']);
    $detail      = trim($_POST['description_longue']);

    if ($id > 0) {
        // UPDATE
        $stmt = $conn->prepare("UPDATE facturation 
            SET libelle=?, montant_ttc=?, date_facture=?, statut=?, resume_court=?, description_longue=?
            WHERE id=?");
        $stmt->bind_param("sdssssi", $libelle, $montant, $dateF, $statut, $resume, $detail, $id);
        $stmt->execute();
        $message = "Facture mise à jour.";
    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO facturation 
            (libelle, montant_ttc, date_facture, statut, resume_court, description_longue)
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdssss", $libelle, $montant, $dateF, $statut, $resume, $detail);
        $stmt->execute();
        $message = "Facture ajoutée.";
    }
}

// ------------ MODE EDITION ------------
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit = [
    "id" => 0,
    "libelle" => "",
    "montant_ttc" => "",
    "date_facture" => date("Y-m-d"),
    "statut" => "en_attente",
    "resume_court" => "",
    "description_longue" => ""
];

if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM facturation WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $edit = $res->fetch_assoc();
}

// ------------ RECUPERATION LISTE ------------
$factures = $conn->query("SELECT * FROM facturation ORDER BY date_facture DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Facturation Admin</title>

<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">

<style>
.card-apple {border-radius:18px; padding:25px; background:#fff; border:1px solid #eee; box-shadow:0 8px 18px rgba(0,0,0,0.06);}
.apple-title {font-size:28px; font-weight:700; background:linear-gradient(90deg,#000,#666); -webkit-background-clip:text; color:transparent;}
.status-badge {padding:4px 10px; border-radius:30px; font-weight:600;}
.statut-payé{background:#d4ffda; color:#1b7d2b;}
.statut-en_attente{background:#fff3bf; color:#ad8b00;}
.statut-annulé{background:#ffd4d4; color:#952020;}
</style>
</head>
<body>

<div class="d-flex">

<?php include("./assets/menu.php"); ?>

<main class="container">

    <div class="border-bottom pt-3 pb-2 mb-3">
        <h1 class="apple-title">Facturation (Admin)</h1>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- FORMULAIRE -->
    <div class="card-apple mb-4">
        <h4><?php echo $edit["id"] ? "Modifier la facture" : "Ajouter une facture"; ?></h4>

        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $edit["id"]; ?>">

            <div class="form-group">
                <label>Libellé</label>
                <input type="text" name="libelle" class="form-control" required
                       value="<?php echo htmlspecialchars($edit["libelle"]); ?>">
            </div>

            <div class="form-group">
                <label>Montant TTC (€)</label>
                <input type="number" name="montant_ttc" step="0.01" class="form-control" required
                       value="<?php echo $edit["montant_ttc"]; ?>">
            </div>

            <div class="form-group">
                <label>Date de facturation</label>
                <input type="date" name="date_facture" class="form-control" required
                       value="<?php echo $edit["date_facture"]; ?>">
            </div>

            <div class="form-group">
                <label>Statut</label>
                <select class="form-control" name="statut">
                    <option value="payé"      <?php if($edit["statut"]=="payé") echo "selected"; ?>>Payé</option>
                    <option value="en_attente" <?php if($edit["statut"]=="en_attente") echo "selected"; ?>>En attente</option>
                    <option value="annulé"    <?php if($edit["statut"]=="annulé") echo "selected"; ?>>Annulé</option>
                </select>
            </div>

            <div class="form-group">
                <label>Résumé court</label>
                <input type="text" name="resume_court" class="form-control"
                       value="<?php echo htmlspecialchars($edit["resume_court"]); ?>">
            </div>

            <div class="form-group">
                <label>Description longue (détails)</label>
                <textarea name="description_longue" class="form-control" rows="6"><?php echo htmlspecialchars($edit["description_longue"]); ?></textarea>
            </div>

            <button name="save_facture" class="btn btn-dark">Enregistrer</button>
            <?php if ($edit["id"]): ?>
                <a href="facturation.php" class="btn btn-secondary ml-2">Annuler</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- LISTE -->
    <h4>📄 Factures enregistrées</h4>
    <table class="table table-hover mt-3">
        <thead class="thead-light">
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Libellé</th>
            <th>Montant</th>
            <th>Statut</th>
            <th>Résumé</th>
            <th></th>
        </tr>
        </thead>
        <tbody>

        <?php while ($f = $factures->fetch_assoc()): ?>
        <tr>
            <td><?php echo $f["id"]; ?></td>
            <td><?php echo date("d/m/Y", strtotime($f["date_facture"])); ?></td>
            <td><?php echo htmlspecialchars($f["libelle"]); ?></td>
            <td><strong><?php echo number_format($f["montant_ttc"],2,","," "); ?> €</strong></td>
            <td><span class="status-badge statut-<?php echo $f["statut"]; ?>">
                <?php echo str_replace("_"," ",$f["statut"]); ?>
            </span></td>
            <td><?php echo htmlspecialchars($f["resume_court"]); ?></td>
            <td>
                <a href="facturation.php?edit=<?php echo $f["id"]; ?>" class="btn btn-sm btn-primary">Éditer</a>

                <form method="POST" style="display:inline-block"
                      onsubmit="return confirm('Supprimer ?');">
                    <input type="hidden" name="id" value="<?php echo $f["id"]; ?>">
                    <button class="btn btn-sm btn-danger" name="delete_facture">X</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>

        </tbody>
    </table>

</main>
</div>

</body>
</html>
